<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\WeekContent;
use App\Models\WeekProgress;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    /**
     * Redirect to the learner's current active week.
     */
    public function index($enrollmentId)
    {
        try {
            $enrollment = $this->resolveEnrollment(auth()->user(), $enrollmentId);

            // First unlocked, incomplete week
            $current = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('is_unlocked', true)
                ->where('is_completed', false)
                ->with('moduleWeek.programModule')
                ->get()
                ->sortBy(fn ($wp) => [$wp->moduleWeek->programModule->order, $wp->moduleWeek->week_number])
                ->first();

            if ($current) {
                return redirect()->route('learner.learning.week', [$enrollmentId, $current->module_week_id]);
            }

            // All weeks complete — land on last week
            $last = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('is_unlocked', true)
                ->with('moduleWeek.programModule')
                ->get()
                ->sortBy(fn ($wp) => [$wp->moduleWeek->programModule->order, $wp->moduleWeek->week_number])
                ->last();

            if ($last) {
                return redirect()->route('learner.learning.week', [$enrollmentId, $last->module_week_id]);
            }

            return view('learner.learning.no-content', compact('enrollment'));

        } catch (\Exception $e) {
            return redirect()->route('learner.my-learning')
                ->with(['message' => 'Could not load course. Please try again.', 'alert-type' => 'error']);
        }
    }

    /**
     * Primary learning view — full week page with all content and inline assessment.
     */
public function showWeek($enrollmentId, $weekId)
{
    try {
        $user       = auth()->user();
        $enrollment = $this->resolveEnrollment($user, $enrollmentId);

        $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('module_week_id', $weekId)
            ->firstOrFail();

        if (! $weekProgress->is_unlocked) {
            return redirect()->route('learner.learning.index', $enrollmentId)
                ->with(['message' => 'This week is not yet available.', 'alert-type' => 'warning']);
        }

        $week = $weekProgress->moduleWeek;
        $week->load(['programModule', 'assessment.questions']);

        // Contents with per-item progress
        $contents = $week->contents()
            ->with(['contentProgress' => fn ($q) =>
                $q->where('user_id', $user->id)->where('enrollment_id', $enrollment->id)
            ])
            ->orderBy('order')
            ->get();

        // Sidebar — all week progress for this enrollment ordered correctly
        $allWeekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
            ->with('moduleWeek.programModule')
            ->get()
            ->sortBy(fn ($wp) => [
                $wp->moduleWeek->programModule->order ?? 0,
                $wp->moduleWeek->week_number,
            ])
            ->values();

        // Prev / Next week IDs
        $allWeeks   = $enrollment->program->getPublishedWeeks();
        $currentIdx = $allWeeks->search(fn ($w) => $w->id == $weekId);
        $prevWeekId = $currentIdx > 0 ? $allWeeks[$currentIdx - 1]->id : null;
        $nextWeekId = ($currentIdx !== false && $currentIdx < $allWeeks->count() - 1)
            ? $allWeeks[$currentIdx + 1]->id
            : null;

        // ── NEW: flag so the blade can render the correct CTA label ──────────
        $nextWeekIsFinalExam = false;
        if ($nextWeekId) {
            $nextWk              = $allWeeks->firstWhere('id', $nextWeekId);
            $nextWeekIsFinalExam = (bool) ($nextWk?->assessment?->is_final);
        }

        // Assessment state for this week
        $assessmentPassed = false;
        $latestAttempt    = null;

        if ($week->assessment) {
            $latestAttempt = $week->assessment->attempts()
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'submitted')
                ->latest()
                ->first();

            $assessmentPassed = $latestAttempt && $latestAttempt->passed;
        }

        // ── NEW: final exam gate — exclude the final exam week itself ─────────
        $allWeeksComplete = false;
        if ($week->assessment?->is_final) {
            $regularWeekIds = \App\Models\ModuleWeek::whereHas('programModule', fn ($q) =>
                    $q->where('program_id', $enrollment->program_id)
                )
                ->whereDoesntHave('assessment', fn ($q) => $q->where('is_final', true))
                ->pluck('id');

            $allWeeksComplete = $regularWeekIds->isNotEmpty()
                && WeekProgress::where('enrollment_id', $enrollment->id)
                    ->whereIn('module_week_id', $regularWeekIds)
                    ->where('is_completed', false)
                    ->doesntExist();
        }

        // Cooldown state for final exam
        $onCooldown  = false;
        $cooldownEnd = null;
        if ($week->assessment?->is_final) {
            $onCooldown  = $week->assessment->isOnCooldownFor($user, $enrollment->id);
            $cooldownEnd = $week->assessment->cooldownEndsAt($user, $enrollment->id);
        }

        $stats = $this->calculateStats($user, $enrollment);

        // ── Single return — all variables now defined above ───────────────────
        return view('learner.learning.index', compact(
            'enrollment', 'week', 'weekProgress', 'contents',
            'allWeekProgress', 'stats',
            'assessmentPassed', 'latestAttempt',
            'prevWeekId', 'nextWeekId', 'nextWeekIsFinalExam',
            'enrollmentId',
            'allWeeksComplete', 'onCooldown', 'cooldownEnd'
        ));

    } catch (\Illuminate\Auth\Access\AuthorizationException) {
        return redirect()->route('learner.my-learning')
            ->with(['message' => 'Access denied.', 'alert-type' => 'error']);
    } catch (\Exception) {
        return redirect()->route('learner.my-learning')
            ->with(['message' => 'Could not load week. Please try again.', 'alert-type' => 'error']);
    }
}
    // ── AJAX: mark content complete ───────────────────────────────────────────

    public function markContentComplete(Request $request, $contentId)
    {
        try {
            $user       = auth()->user();
            $content    = WeekContent::findOrFail($contentId);
            $enrollment = $this->resolveEnrollmentForContent($user, $content);

            $progress = $content->getProgressFor($user, $enrollment);
            $progress->markAsCompleted();

            $weekProgress = $content->moduleWeek->getProgressFor($user, $enrollment);
            $weekProgress->recalculateCompletion();
            $weekProgress->refresh();

            return response()->json([
                'success'         => true,
                'week_completion' => $weekProgress->progress_percentage,
                'week_completed'  => $weekProgress->is_completed,
            ]);

        } catch (\Exception) {
            return response()->json(['success' => false], 500);
        }
    }

    // ── AJAX: video progress ping ─────────────────────────────────────────────

    public function updateContentProgress(Request $request, $contentId)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'time_spent'          => 'nullable|integer|min:0',
        ]);

        try {
            $user       = auth()->user();
            $content    = WeekContent::findOrFail($contentId);
            $enrollment = $this->resolveEnrollmentForContent($user, $content);

            $progress = $content->getProgressFor($user, $enrollment);
            $progress->updateProgress($request->progress_percentage);

            if ($request->filled('time_spent')) {
                $progress->addTimeSpent($request->time_spent);
            }

            return response()->json(['success' => true, 'is_completed' => $progress->is_completed]);

        } catch (\Exception) {
            return response()->json(['success' => false], 500);
        }
    }

    // ── AJAX: assessment data for week ────────────────────────────────────────

    public function getAssessmentData($enrollmentId, $assessmentId)
    {
        try {
            $user        = auth()->user();
            $enrollment  = $this->resolveEnrollment($user, $enrollmentId);
            $assessment  = \App\Models\Assessment::with('questions')->findOrFail($assessmentId);
            $week        = $assessment->moduleWeek;

            $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('module_week_id', $week->id)
                ->firstOrFail();

            if (! $weekProgress->is_unlocked) {
                return response()->json(['success' => false, 'message' => 'Not yet available.'], 403);
            }

            $latest = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'submitted')
                ->latest()->first();

            $inProgress = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'in_progress')
                ->latest()->first();

            $questions = $assessment->questions->map(fn ($q) => [
                'id'      => $q->id,
                'text'    => $q->question_text,
                'type'    => $q->question_type,
                'options' => array_values($q->options ?? []),
                'points'  => $q->points ?? 1,
            ]);

            return response()->json([
                'success'             => true,
                'assessment'          => [
                    'id'            => $assessment->id,
                    'title'         => $assessment->title,
                    'passing_score' => $assessment->getEffectivePassPercentage(),
                    'time_limit'    => $assessment->time_limit_minutes,
                    'is_final'      => $assessment->is_final,
                ],
                'questions'           => $questions,
                'latest_attempt'      => $latest ? [
                    'id'      => $latest->id,
                    'score'   => (float) $latest->percentage,
                    'passed'  => $latest->passed,
                ] : null,
                'in_progress_attempt' => $inProgress ? ['id' => $inProgress->id] : null,
                'enrollment_id'       => $enrollment->id,
            ]);

        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'Could not load assessment.'], 500);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveEnrollment($user, $enrollmentId): Enrollment
    {
        $enrollment = Enrollment::with(['program', 'cohort'])->findOrFail($enrollmentId);

        if ($enrollment->user_id !== $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }

        if (! in_array($enrollment->status, ['active', 'completed'])) {
            throw new \RuntimeException('Enrollment is not active.');
        }

        return $enrollment;
    }

    private function resolveEnrollmentForContent($user, WeekContent $content): Enrollment
    {
        return Enrollment::where('user_id', $user->id)
            ->where('program_id', $content->moduleWeek->programModule->program_id)
            ->whereIn('status', ['active', 'completed'])
            ->firstOrFail();
    }

    private function calculateStats($user, Enrollment $enrollment): array
    {
        $allWeeks = $enrollment->program->getPublishedWeeks();

        // Exclude the final exam week from the week count shown to learners
        $courseWeeks = $allWeeks->filter(fn ($w) =>
            ! ($w->has_assessment && $w->assessment?->is_final)
        );

        $totalWeeks = $courseWeeks->count();

        $completedWeeks = WeekProgress::where('enrollment_id', $enrollment->id)
            ->whereIn('module_week_id', $courseWeeks->pluck('id'))
            ->where('is_completed', true)
            ->count();

        $totalContents = WeekContent::whereHas('moduleWeek.programModule',
            fn ($q) => $q->where('program_id', $enrollment->program_id)
        )->where('is_required', true)->count();

        $completedContents = ContentProgress::where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->whereHas('weekContent', fn ($q) => $q->where('is_required', true))
            ->count();

        return [
            'overall_progress'   => $totalWeeks > 0
                ? round(($completedWeeks / $totalWeeks) * 100, 1) : 0,
            'completed_weeks'    => $completedWeeks,
            'total_weeks'        => $totalWeeks,
            'completed_contents' => $completedContents,
            'total_contents'     => $totalContents,
        ];
    }
}