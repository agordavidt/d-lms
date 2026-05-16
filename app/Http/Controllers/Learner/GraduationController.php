<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Enrollment;

class GraduationController extends Controller
{
    /**
     * Graduation status page.
     * Shows: course completion state, final exam state, graduation/certificate status.
     */
    public function status(Enrollment $enrollment)
    {
        $user = auth()->user();
        if ($enrollment->user_id !== $user->id) abort(403);

        $enrollment->load(['program', 'cohort']);

        $allWeeksComplete = $enrollment->hasCompletedAllWeeks();

        $finalAssessment = Assessment::whereHas('moduleWeek.programModule',
            fn ($q) => $q->where('program_id', $enrollment->program_id)
        )->where('is_final', true)->first();

        $finalAttempt    = null;
        $onCooldown      = false;
        $cooldownEnd     = null;
        $finalExamPassed = false;

        if ($finalAssessment) {
            $finalAttempt    = $finalAssessment->getLatestAttempt($user, $enrollment->id);
            $finalExamPassed = $finalAttempt && $finalAttempt->passed;
            $onCooldown      = $finalAssessment->isOnCooldownFor($user, $enrollment->id);
            $cooldownEnd     = $finalAssessment->cooldownEndsAt($user, $enrollment->id);
        }

        return view('learner.graduation.status', compact(
            'enrollment',
            'allWeeksComplete',
            'finalAssessment',
            'finalAttempt',
            'finalExamPassed',
            'onCooldown',
            'cooldownEnd'
        ));
    }

    /**
     * Score-only result page shown immediately after a final exam submission.
     * Redirected to from AssessmentAttemptController after IS_FINAL submit.
     */
    public function finalResult(AssessmentAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id) abort(403);
        if (! $attempt->assessment->is_final) abort(404);
        if ($attempt->status !== 'submitted') {
            return redirect()->route('learner.attempts.show', $attempt->id);
        }

        $enrollment      = $attempt->enrollment;
        $assessment      = $attempt->assessment;
        $onCooldown      = $assessment->isOnCooldownFor($user, $enrollment->id);
        $cooldownEnd     = $assessment->cooldownEndsAt($user, $enrollment->id);

        return view('learner.assessments.final_result', compact(
            'attempt', 'assessment', 'enrollment', 'onCooldown', 'cooldownEnd'
        ));
    }
}