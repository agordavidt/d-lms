<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\WeekContent;
use App\Models\WeekProgress;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    /**
     * Redirect to the learner's current week.
     * The actual rendering lives in showWeek().
     */
    public function index($enrollmentId)
    {
        try {
            $user       = auth()->user();
            $enrollment = $this->resolveEnrollment($user, $enrollmentId);

            // First unlocked, incomplete week
            $currentWeekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('is_unlocked', true)
                ->where('is_completed', false)
                ->with('moduleWeek')
                ->orderBy('created_at')
                ->first();

            if (!$currentWeekProgress) {
                // All complete — land on the last unlocked week
                $last = WeekProgress::where('enrollment_id', $enrollment->id)
                    ->where('is_unlocked', true)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($last) {
                    return redirect()->route('learner.learning.week', [$enrollmentId, $last->module_week_id]);
                }

                return view('learner.learning.no-content', compact('enrollment'));
            }

            return redirect()->route('learner.learning.week', [
                $enrollmentId,
                $currentWeekProgress->module_week_id,
            ]);

        } catch (\Exception $e) {
            return redirect()->route('learner.my-learning')
                ->with(['message' => 'Could not load course. Please try again.', 'alert-type' => 'error']);
        }
    }

    /**
     * Full scrollable week page — the primary learning view.
     *
     * Renders every content item for the week on one continuous page,
     * followed by the inline assessment (if the week has one).
     */
    public function showWeek($enrollmentId, $weekId)
    {
        try {
            $user       = auth()->user();
            $enrollment = $this->resolveEnrollment($user, $enrollmentId);

            $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('module_week_id', $weekId)
                ->firstOrFail();

            if (!$weekProgress->is_unlocked) {
                return redirect()->route('learner.learning.index', $enrollmentId)
                    ->with(['message' => 'This week is not yet available.', 'alert-type' => 'warning']);
            }

            $week = $weekProgress->moduleWeek;
            $week->load(['programModule', 'assessment.questions']);

            // Contents with per-item progress
            $contents = $week->contents()
                ->with(['contentProgress' => fn ($q) =>
                    $q->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id)])
                ->orderBy('order')
                ->get();

            // Sidebar: all week progress for this enrollment, ordered
            $allWeekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->with('moduleWeek.programModule')
                ->get()
                ->sortBy(fn ($wp) => [
                    $wp->moduleWeek->programModule->order ?? 0,
                    $wp->moduleWeek->week_number,
                ])
                ->values();

            // Prev / Next week IDs for navigation
            $allWeeks   = $enrollment->program->getPublishedWeeks();
            $currentIdx = $allWeeks->search(fn ($w) => $w->id == $weekId);
            $prevWeekId = $currentIdx > 0 ? $allWeeks[$currentIdx - 1]->id : null;
            $nextWeekId = ($currentIdx !== false && $currentIdx < $allWeeks->count() - 1)
                ? $allWeeks[$currentIdx + 1]->id
                : null;

            // Has the learner already passed this week's assessment?
            $assessmentPassed = false;
            $latestAttempt    = null;
            if ($week->assessment) {
                $latestAttempt = $week->assessment->attempts()
                    ->where('user_id', $user->id)
                    ->where('enrollment_id', $enrollment->id)
                    ->where('status', 'submitted')
                    ->latest()
                    ->first();

                $assessmentPassed = $latestAttempt &&
                    (float) $latestAttempt->percentage >= (float) $week->assessment->pass_percentage;
            }

            $stats = $this->calculateLearningStats($user, $enrollment);

            return view('learner.learning.index', compact(
                'enrollment',
                'week',
                'weekProgress',
                'contents',
                'allWeekProgress',
                'stats',
                'assessmentPassed',
                'latestAttempt',
                'prevWeekId',
                'nextWeekId',
                'enrollmentId'
            ));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('learner.my-learning')
                ->with(['message' => 'Access denied.', 'alert-type' => 'error']);
        } catch (\Exception $e) {
            return redirect()->route('learner.my-learning')
                ->with(['message' => 'Could not load week. Please try again.', 'alert-type' => 'error']);
        }
    }

    // ── AJAX: mark content complete ────────────────────────────────────────────

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

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    // ── AJAX: video progress ping ──────────────────────────────────────────────

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

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    // ── AJAX: week contents (kept for backward-compat) ─────────────────────────

    public function getWeekContents($enrollmentId, $weekId)
    {
        try {
            $user       = auth()->user();
            $enrollment = $this->resolveEnrollment($user, $enrollmentId);

            $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('module_week_id', $weekId)
                ->firstOrFail();

            if (!$weekProgress->is_unlocked) {
                return response()->json(['success' => false, 'message' => 'Week not yet unlocked.'], 403);
            }

            $week = $weekProgress->moduleWeek()
                ->with(['programModule', 'assessment.questions'])
                ->firstOrFail();

            $contents = $week->contents()
                ->with(['contentProgress' => fn ($q) =>
                    $q->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id)])
                ->orderBy('order')
                ->get()
                ->map(fn ($c) => [
                    'id'             => $c->id,
                    'title'          => $c->title,
                    'type'           => $c->content_type,
                    'video_url'      => $c->video_url,
                    'video_duration' => $c->video_duration_minutes,
                    'file_url'       => $c->file_url,
                    'external_url'   => $c->external_url,
                    'text_content'   => $c->text_content,
                    'is_completed'   => $c->contentProgress->first()?->is_completed ?? false,
                ]);

            $assessment = null;
            if ($week->assessment) {
                $attempt = $week->assessment->attempts()
                    ->where('user_id', $user->id)
                    ->where('enrollment_id', $enrollment->id)
                    ->where('status', 'submitted')
                    ->latest()->first();

                $assessment = [
                    'id'             => $week->assessment->id,
                    'title'          => $week->assessment->title,
                    'question_count' => $week->assessment->questions->count(),
                    'is_submitted'   => (bool) $attempt,
                    'score'          => $attempt?->percentage,
                    'passing_score'  => $week->assessment->pass_percentage,
                ];
            }

            return response()->json([
                'success'    => true,
                'contents'   => $contents,
                'assessment' => $assessment,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not load content.'], 500);
        }
    }

    // ── AJAX: assessment data (kept for backward-compat) ──────────────────────

    public function getAssessmentData($enrollmentId, $assessmentId)
    {
        try {
            $user       = auth()->user();
            $enrollment = $this->resolveEnrollment($user, $enrollmentId);
            $assessment = \App\Models\Assessment::with('questions')->findOrFail($assessmentId);

            $week         = $assessment->moduleWeek;
            $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('module_week_id', $week->id)
                ->firstOrFail();

            if (!$weekProgress->is_unlocked) {
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

            $passMark = $assessment->pass_percentage ?? 70;

            return response()->json([
                'success'             => true,
                'assessment'          => [
                    'id'            => $assessment->id,
                    'title'         => $assessment->title,
                    'passing_score' => $passMark,
                    'time_limit'    => $assessment->time_limit_minutes,
                ],
                'questions'           => $questions,
                'latest_attempt'      => $latest ? [
                    'id'           => $latest->id,
                    'score'        => (float) $latest->percentage,
                    'passed'       => (float) $latest->percentage >= $passMark,
                    'submitted_at' => $latest->submitted_at?->diffForHumans(),
                ] : null,
                'in_progress_attempt' => $inProgress ? ['id' => $inProgress->id] : null,
                'enrollment_id'       => $enrollment->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not load assessment.'], 500);
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resolveEnrollment($user, $enrollmentId): Enrollment
    {
        $enrollment = Enrollment::with(['program', 'cohort'])->findOrFail($enrollmentId);

        if ($enrollment->user_id !== $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException();
        }

        if (!in_array($enrollment->status, ['active', 'completed'])) {
            throw new \RuntimeException('Enrollment is not active.');
        }

        return $enrollment;
    }

    private function resolveEnrollmentForContent($user, WeekContent $content): Enrollment
    {
        $programId = $content->moduleWeek->programModule->program_id;

        return Enrollment::where('user_id', $user->id)
            ->where('program_id', $programId)
            ->whereIn('status', ['active', 'completed'])
            ->firstOrFail();
    }

    private function calculateLearningStats($user, Enrollment $enrollment): array
    {
        $totalWeeks = $enrollment->program->modules()
            ->withCount('weeks')->get()->sum('weeks_count');

        $completedWeeks = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)->count();

        $totalContents = WeekContent::whereHas('moduleWeek.programModule',
            fn ($q) => $q->where('program_id', $enrollment->program_id))
            ->where('is_required', true)->count();

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