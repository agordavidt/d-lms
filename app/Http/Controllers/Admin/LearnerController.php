<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class LearnerController extends Controller
{
    /**
     * Display all learners
     */
    public function index()
    {
        return view('admin.learners.index');
    }

    /**
     * Get learners data for DataTables
     */
    public function getData(Request $request)
    {
        $query = User::where('role', 'learner')
            ->with(['enrollments.program', 'enrollments.cohort', 'enrollments.cohort.mentor']);

        // Search
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by program
        if ($request->has('program') && $request->program != '') {
            $query->whereHas('enrollments', function($q) use ($request) {
                $q->where('program_id', $request->program);
            });
        }

        $totalRecords = User::where('role', 'learner')->count();
        $filteredRecords = $query->count();

        // Sorting
        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $columnName = $request->columns[$columnIndex]['data'];
            $sortDirection = $request->order[0]['dir'];
            
            if (in_array($columnName, ['first_name', 'email', 'status', 'created_at'])) {
                $query->orderBy($columnName, $sortDirection);
            }
        } else {
            $query->latest();
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $learners = $query->skip($start)->take($length)->get();

        $data = $learners->map(function($learner) {
            $enrollment = $learner->enrollments()->whereIn('status', ['active', 'pending'])->first();
            
            return [
                'id' => $learner->id,
                'name' => $learner->name,
                'email' => $learner->email,
                'phone' => $learner->phone ?? 'N/A',
                'avatar_url' => $learner->avatar_url,
                'program' => $enrollment ? $enrollment->program->name : 'Not Enrolled',
                'cohort' => $enrollment ? $enrollment->cohort->name : 'N/A',
                'mentor' => $enrollment && $enrollment->cohort->mentor ? $enrollment->cohort->mentor->name : 'N/A',
                'enrollment_status' => $enrollment ? $enrollment->status : 'none',
                'status' => $learner->status,
                'joined_at' => $learner->created_at->format('M d, Y'),
                'actions' => $learner->id
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Show learner details
     */
    public function show($id)
    {
        $learner = User::where('role', 'learner')
            ->with([
                'enrollments.program',
                'enrollments.cohort.mentor',
                'enrollments.payments',
                'contentProgress.content'
            ])
            ->findOrFail($id);

        $enrollment = $learner->enrollments()->whereIn('status', ['active', 'pending'])->first();
        
        // Calculate progress statistics if enrolled
        $progressStats = null;
        if ($enrollment) {
            $totalContent = $enrollment->program->modules()
                ->with('weeks.contents')
                ->get()
                ->sum(function($module) {
                    return $module->weeks->sum(function($week) {
                        return $week->contents->count();
                    });
                });

            $completedContent = $learner->contentProgress()
                ->where('status', 'completed')
                ->count();

            $progressStats = [
                'total_content' => $totalContent,
                'completed_content' => $completedContent,
                'completion_percentage' => $totalContent > 0 ? round(($completedContent / $totalContent) * 100, 2) : 0
            ];
        }

        return view('admin.learners.show', compact('learner', 'enrollment', 'progressStats'));
    }

    /**
     * Update learner status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $learner = User::where('role', 'learner')->findOrFail($id);

            $request->validate([
                'status' => 'required|in:active,suspended,inactive'
            ]);

            $oldStatus = $learner->status;
            $learner->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Learner status updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }
}