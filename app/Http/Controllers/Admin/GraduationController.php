<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GraduationController extends Controller
{
    /**
     * Pending graduation approvals.
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['user', 'program', 'cohort'])
            ->where('graduation_status', 'pending_review')
            ->orderBy('graduation_requested_at');

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        $pendingGraduations = $query->paginate(20);
        $programs = Program::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        $stats = [
            'pending_count'        => Enrollment::where('graduation_status', 'pending_review')->count(),
            'graduated_this_month' => Enrollment::where('graduation_status', 'graduated')
                                        ->whereMonth('graduation_approved_at', now()->month)
                                        ->whereYear('graduation_approved_at', now()->year)
                                        ->count(),
        ];

        return view('admin.graduations.index', compact('pendingGraduations', 'programs', 'stats'));
    }

    /**
     * Review a single learner's graduation request.
     */
    public function review($enrollmentId)
    {
        $enrollment = Enrollment::with([
            'user', 'program', 'cohort', 'weekProgress.moduleWeek',
        ])->findOrFail($enrollmentId);

        $allWeeksComplete = $enrollment->hasCompletedAllWeeks();

        $finalAssessment = Assessment::whereHas('moduleWeek.programModule',
            fn ($q) => $q->where('program_id', $enrollment->program_id)
        )->where('is_final', true)->first();

        $finalExamAttempts = $finalAssessment
            ? $finalAssessment->attempts()
                ->where('user_id', $enrollment->user_id)
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'submitted')
                ->orderBy('attempt_number')
                ->get()
            : collect();

        $latestFinalAttempt = $finalExamAttempts->last();

        return view('admin.graduations.review', compact(
            'enrollment',
            'allWeeksComplete',
            'finalAssessment',
            'finalExamAttempts',
            'latestFinalAttempt'
        ));
    }

    /**
     * Approve and issue certificate.
     */
    public function approve(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);

        // Guard: must be pending and final exam must have been passed
        if ($enrollment->graduation_status !== 'pending_review') {
            return back()->with(['message' => 'This enrollment is not pending review.', 'alert-type' => 'error']);
        }

        if (! $enrollment->final_exam_score || $enrollment->final_exam_score < Assessment::FINAL_PASS_PERCENTAGE) {
            return back()->with([
                'message'    => 'Learner has not passed the final examination.',
                'alert-type' => 'error',
            ]);
        }

        DB::beginTransaction();
        try {
            $enrollment->update([
                'graduation_status'      => 'graduated',
                'graduation_approved_at' => now(),
                'approved_by'            => auth()->id(),
                'certificate_key'        => strtoupper(Str::random(12)),
                'certificate_issued_at'  => now(),
            ]);

            AuditLog::log('graduation_approved', auth()->user(), [
                'description' => 'Granted certificate to ' . $enrollment->user->full_name,
                'model_type'  => Enrollment::class,
                'model_id'    => $enrollment->id,
            ]);

            DB::commit();

            return redirect()->route('admin.graduations.index')
                ->with(['message' => 'Certificate granted to ' . $enrollment->user->first_name . '.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Reject and return learner to active state.
     */
    public function reject(Request $request, $enrollmentId)
    {
        $request->validate(['reason' => 'required|string|max:1000']);

        $enrollment = Enrollment::findOrFail($enrollmentId);

        DB::beginTransaction();
        try {
            $enrollment->update([
                'graduation_status'       => 'active',
                'graduation_requested_at' => null,
            ]);

            AuditLog::log('graduation_rejected', auth()->user(), [
                'description' => 'Rejected graduation for ' . $enrollment->user->full_name,
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

    /**
     * Bulk approve multiple pending graduations.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'enrollment_ids'   => 'required|array',
            'enrollment_ids.*' => 'exists:enrollments,id',
        ]);

        $approved = 0;
        $skipped  = 0;

        DB::beginTransaction();
        try {
            foreach ($request->enrollment_ids as $id) {
                $enrollment = Enrollment::find($id);

                if (
                    ! $enrollment
                    || $enrollment->graduation_status !== 'pending_review'
                    || ! $enrollment->final_exam_score
                    || $enrollment->final_exam_score < Assessment::FINAL_PASS_PERCENTAGE
                ) {
                    $skipped++;
                    continue;
                }

                $enrollment->update([
                    'graduation_status'      => 'graduated',
                    'graduation_approved_at' => now(),
                    'approved_by'            => auth()->id(),
                    'certificate_key'        => strtoupper(Str::random(12)),
                    'certificate_issued_at'  => now(),
                ]);

                $approved++;
            }

            DB::commit();

            $msg = "Granted {$approved} certificate(s).";
            if ($skipped) $msg .= " {$skipped} skipped (requirements not met).";

            return back()->with(['message' => $msg, 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Bulk approval failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Graduated learners list.
     */
    public function graduated(Request $request)
    {
        $query = Enrollment::with(['user', 'program', 'cohort'])
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