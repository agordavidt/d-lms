<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class GraduationController extends Controller
{
    public function request(Request $request, Enrollment $enrollment)
    {
        $user = auth()->user();
        if ($enrollment->user_id !== $user->id) abort(403);

        if (in_array($enrollment->graduation_status, ['pending_review', 'graduated'])) {
            $message = $enrollment->graduation_status === 'graduated'
                ? 'You have already graduated!'
                : 'Your graduation request is already under review.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with(['message' => $message, 'alert-type' => 'info']);
        }

        $requested = $enrollment->requestGraduation();

        if (!$requested) {
            $reasons = [];
            if (!$enrollment->hasCompletedAllContent())    $reasons[] = 'not all course content is completed';
            if (!$enrollment->hasPassedAllAssessments())   $reasons[] = 'not all assessments (including the final examination) have been passed';
            if (!$enrollment->meetsMinimumGradeRequirement()) {
                $avg = $enrollment->final_grade_avg ?? 0;
                $min = $enrollment->program->min_passing_average ?? 0;
                $reasons[] = "your average ({$avg}%) is below the required {$min}%";
            }

            $message = 'You are not yet eligible for graduation.'
                . (count($reasons) ? ' Reason: ' . implode('; ', $reasons) . '.' : '');

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with(['message' => $message, 'alert-type' => 'warning']);
        }

        $msg = 'Your completion has been submitted. An administrator will issue your certificate shortly.';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $msg])
            : back()->with(['message' => $msg, 'alert-type' => 'success']);
    }

    public function status(Enrollment $enrollment)
    {
        $user = auth()->user();
        if ($enrollment->user_id !== $user->id) abort(403);

        $enrollment->load(['program', 'cohort', 'weekProgress']);

        $eligibility = [
            'all_content_complete'    => $enrollment->hasCompletedAllContent(),
            'all_assessments_passed'  => $enrollment->hasPassedAllAssessments(),
            'meets_grade_requirement' => $enrollment->meetsMinimumGradeRequirement(),
        ];

        // Final exam attempt info if program has one
        $finalExam = null;
        $finalAttempt = null;

        $finalAssessment = \App\Models\Assessment::whereHas('moduleWeek.programModule', fn($q) =>
            $q->where('program_id', $enrollment->program_id)
        )->where('is_final', true)->first();

        if ($finalAssessment) {
            $finalExam    = $finalAssessment;
            $finalAttempt = $finalAssessment->getLatestAttempt($user, $enrollment->id);
        }

        return view('learner.graduation.status', compact(
            'enrollment', 'eligibility', 'finalExam', 'finalAttempt'
        ));
    }

    /**
     * Score-only result page for the final examination.
     * Route: GET /learner/attempts/{attempt}/final-result
     */
    public function finalResult(AssessmentAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);
        if (!$attempt->assessment->is_final) abort(404);
        if ($attempt->status !== 'submitted') {
            return redirect()->route('learner.attempts.show', $attempt->id);
        }

        return view('learner.assessments.final_result', compact('attempt'));
    }
}