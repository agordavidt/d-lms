<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraduationController extends Controller
{
    /**
     * Display graduation queue
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['user', 'program', 'cohort'])
            ->where('graduation_status', 'pending_review')
            ->orderBy('graduation_requested_at', 'desc');

        // Filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('cohort_id')) {
            $query->where('cohort_id', $request->cohort_id);
        }

        $pendingGraduations = $query->paginate(20);

        // Get filter options
        $programs = \App\Models\Program::orderBy('name')->get();
        $cohorts = \App\Models\Cohort::orderBy('name')->get();

        // Statistics
        $stats = [
            'pending_count' => Enrollment::where('graduation_status', 'pending_review')->count(),
            'graduated_this_month' => Enrollment::where('graduation_status', 'graduated')
                ->whereMonth('graduation_approved_at', now()->month)
                ->count(),
            'avg_grade' => Enrollment::where('graduation_status', 'graduated')
                ->avg('final_grade_avg'),
        ];

        return view('admin.graduations.index', compact(
            'pendingGraduations',
            'programs',
            'cohorts',
            'stats'
        ));
    }

    /**
     * Review individual graduation request
     */
    public function review($enrollmentId)
    {
        $enrollment = Enrollment::with([
            'user',
            'program',
            'cohort',
            'weekProgress.moduleWeek',
            'assessmentAttempts.assessment'
        ])->findOrFail($enrollmentId);

        // Check eligibility details
        $eligibility = [
            'all_content_complete' => $enrollment->hasCompletedAllContent(),
            'all_assessments_taken' => $enrollment->hasAttemptedAllAssessments(),
            'meets_grade_requirement' => $enrollment->meetsMinimumGradeRequirement(),
        ];

        // Get week progress details
        $weekProgressDetails = $enrollment->weekProgress()
            ->with('moduleWeek')
            ->where('is_completed', true)
            ->get();

        // Get assessment breakdown
        $assessmentBreakdown = $enrollment->weekProgress()
            ->whereNotNull('assessment_score')
            ->where('assessment_attempts', '>', 0)
            ->with('moduleWeek')
            ->get();

        // Total weeks and completed
        $totalWeeks = \App\Models\ModuleWeek::whereHas('programModule', function($q) use ($enrollment) {
            $q->where('program_id', $enrollment->program_id);
        })
        ->where('status', 'published')
        ->count();

        $completedWeeks = $weekProgressDetails->count();

        return view('admin.graduations.review', compact(
            'enrollment',
            'eligibility',
            'weekProgressDetails',
            'assessmentBreakdown',
            'totalWeeks',
            'completedWeeks'
        ));
    }

    /**
     * Approve graduation
     */
    public function approve(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);

        // Verify eligibility
        if (!$enrollment->isEligibleForGraduation()) {
            return back()->with([
                'message' => 'This learner does not meet graduation requirements.',
                'alert-type' => 'error'
            ]);
        }

        DB::beginTransaction();
        try {
            // Approve graduation
            $enrollment->approveGraduation(auth()->user());

            // Log the action
            AuditLog::log('graduation_approved', auth()->user(), [
                'description' => 'Approved graduation for ' . $enrollment->user->name,
                'model_type' => get_class($enrollment),
                'model_id' => $enrollment->id,
                'old_values' => ['graduation_status' => 'pending_review'],
                'new_values' => ['graduation_status' => 'graduated']
            ]);

            // TODO: Send notification to learner
            // TODO: Queue certificate generation

            DB::commit();

            return redirect()->route('admin.graduations.index')->with([
                'message' => 'Graduation approved successfully! Certificate will be generated.',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with([
                'message' => 'Failed to approve graduation: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Reject graduation
     */
    public function reject(Request $request, $enrollmentId)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        DB::beginTransaction();
        try {
            // Update status back to active
            $enrollment->update([
                'graduation_status' => 'active',
                'graduation_requested_at' => null,
            ]);

            // Log rejection
            AuditLog::log('graduation_rejected', auth()->user(), [
                'description' => 'Rejected graduation for ' . $enrollment->user->name,
                'model_type' => get_class($enrollment),
                'model_id' => $enrollment->id,
                'old_values' => ['graduation_status' => 'pending_review'],
                'new_values' => [
                    'graduation_status' => 'active',
                    'rejection_reason' => $request->reason
                ]
            ]);

            // TODO: Send notification to learner with reason

            DB::commit();

            return redirect()->route('admin.graduations.index')->with([
                'message' => 'Graduation request rejected. Learner has been notified.',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with([
                'message' => 'Failed to reject graduation: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Bulk approve graduations
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id'
        ]);

        $approvedCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $enrollmentId) {
                $enrollment = Enrollment::find($enrollmentId);
                
                if ($enrollment && $enrollment->isEligibleForGraduation()) {
                    $enrollment->approveGraduation(auth()->user());
                    $approvedCount++;
                } else {
                    $failedCount++;
                }
            }

            DB::commit();

            $message = "Approved {$approvedCount} graduation(s).";
            if ($failedCount > 0) {
                $message .= " {$failedCount} failed (not eligible).";
            }

            return back()->with([
                'message' => $message,
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with([
                'message' => 'Bulk approval failed: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Show graduated learners
     */
    public function graduated(Request $request)
    {
        $query = Enrollment::with(['user', 'program', 'cohort', 'approver'])
            ->where('graduation_status', 'graduated')
            ->orderBy('graduation_approved_at', 'desc');

        // Filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('cohort_id')) {
            $query->where('cohort_id', $request->cohort_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('graduation_approved_at', $request->month);
        }

        $graduates = $query->paginate(20);

        // Get filter options
        $programs = \App\Models\Program::orderBy('name')->get();
        $cohorts = \App\Models\Cohort::orderBy('name')->get();

        return view('admin.graduations.graduated', compact('graduates', 'programs', 'cohorts'));
    }
}