<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\Request;

class LearnerController extends Controller
{
    /**
     * Display all learners with backend filtering
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'learner')
            ->with(['enrollments' => function($q) {
                $q->whereIn('status', ['active', 'pending'])
                  ->with(['program', 'cohort.mentor'])
                  ->latest();
            }]);

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by program
        if ($request->filled('program_id')) {
            $query->whereHas('enrollments', function($q) use ($request) {
                $q->where('program_id', $request->program_id)
                  ->whereIn('status', ['active', 'pending']);
            });
        }

        $learners = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Get programs for filter
        $programs = Program::active()->orderBy('name')->get(['id', 'name']);

        return view('admin.learners.index', compact('learners', 'programs'));
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
            ])
            ->findOrFail($id);

        $enrollment = $learner->enrollments()
            ->whereIn('status', ['active', 'pending'])
            ->with(['program.modules.weeks.contents', 'cohort.mentor'])
            ->first();
        
        // Calculate progress statistics if enrolled
        $progressStats = null;
        if ($enrollment) {
            // Count total published content in the program
            $totalContent = 0;
            foreach ($enrollment->program->modules as $module) {
                foreach ($module->weeks as $week) {
                    $totalContent += $week->contents()->where('status', 'published')->count();
                }
            }

            // Count completed content for this learner
            $completedContent = $learner->contentProgress()
                ->where('is_completed', true)
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