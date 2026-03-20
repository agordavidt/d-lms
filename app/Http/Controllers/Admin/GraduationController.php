<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\ModuleWeek;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraduationController extends Controller
{
    public function index(Request $request)
    {
        $query = Enrollment::with(['user', 'program'])
            ->where('graduation_status', 'pending_review')
            ->orderBy('graduation_requested_at');

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        $pendingGraduations = $query->paginate(20);

        $programs = Program::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $stats = [
            'pending_count'         => Enrollment::where('graduation_status', 'pending_review')->count(),
            'graduated_this_month'  => Enrollment::where('graduation_status', 'graduated')
                                           ->whereMonth('graduation_approved_at', now()->month)
                                           ->whereYear('graduation_approved_at', now()->year)
                                           ->count(),
            'avg_grade'             => round(
                                           Enrollment::where('graduation_status', 'graduated')
                                               ->avg('final_grade_avg') ?? 0, 1
                                       ),
        ];

        return view('admin.graduations.index', compact('pendingGraduations', 'programs', 'stats'));
    }

    public function review($enrollmentId)
    {
        $enrollment = Enrollment::with([
            'user', 'program',
            'weekProgress.moduleWeek',
            'assessmentAttempts.assessment',
        ])->findOrFail($enrollmentId);

        $eligibility = [
            'all_content_complete'    => $enrollment->hasCompletedAllContent(),
            'all_assessments_passed'  => $enrollment->hasPassedAllAssessments(),
            'meets_grade_requirement' => $enrollment->meetsMinimumGradeRequirement(),
        ];

        // FIXED: removed ->where('status', 'published') — no status column in new schema
        $totalWeeks = ModuleWeek::whereHas('programModule', fn ($q) =>
            $q->where('program_id', $enrollment->program_id)
        )->count();

        $completedWeeks = $enrollment->weekProgress()
            ->where('is_completed', true)->count();

        $assessmentBreakdown = $enrollment->weekProgress()
            ->whereNotNull('assessment_score')
            ->where('assessment_attempts', '>', 0)
            ->with('moduleWeek')
            ->get();

        return view('admin.graduations.review', compact(
            'enrollment', 'eligibility',
            'assessmentBreakdown', 'totalWeeks', 'completedWeeks'
        ));
    }

    public function approve(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);

        if (!$enrollment->isEligibleForGraduation()) {
            return back()->with([
                'message'    => 'This learner does not meet graduation requirements.',
                'alert-type' => 'error',
            ]);
        }

        DB::beginTransaction();
        try {
            $enrollment->approveGraduation(auth()->user());

            AuditLog::log('graduation_approved', auth()->user(), [
                'description' => 'Approved graduation for ' . $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                'model_type'  => Enrollment::class,
                'model_id'    => $enrollment->id,
            ]);

            DB::commit();

            return redirect()->route('admin.graduations.index')
                ->with(['message' => 'Graduation approved. Certificate key generated.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function reject(Request $request, $enrollmentId)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        DB::beginTransaction();
        try {
            $enrollment->update([
                'graduation_status'        => 'active',
                'graduation_requested_at'  => null,
            ]);

            AuditLog::log('graduation_rejected', auth()->user(), [
                'description' => 'Rejected graduation for ' . $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                'model_type'  => Enrollment::class,
                'model_id'    => $enrollment->id,
                'reason'      => $request->reason,
            ]);

            DB::commit();

            return redirect()->route('admin.graduations.index')
                ->with(['message' => 'Graduation request rejected.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'enrollment_ids'   => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id',
        ]);

        $approved = 0;
        $failed   = 0;

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $id) {
                $enrollment = Enrollment::find($id);
                if ($enrollment && $enrollment->isEligibleForGraduation()) {
                    $enrollment->approveGraduation(auth()->user());
                    $approved++;
                } else {
                    $failed++;
                }
            }
            DB::commit();

            $msg = "Approved {$approved} graduation(s).";
            if ($failed) $msg .= " {$failed} skipped (not eligible).";

            return back()->with(['message' => $msg, 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Bulk approval failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function graduated(Request $request)
    {
        $query = Enrollment::with(['user', 'program'])
            ->where('graduation_status', 'graduated')
            ->orderByDesc('graduation_approved_at');

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('graduation_approved_at', $request->month)
                  ->whereYear('graduation_approved_at', $request->year ?? now()->year);
        }

        $graduates = $query->paginate(20);
        $programs  = Program::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.graduations.graduated', compact('graduates', 'programs'));
    }
}