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
     * Main learning view for a specific enrollment.
     *
     * CHANGED: publishedContents() → contents() (no status column on week_contents)
     *          publishedModules()  → modules()   (no status column on program_modules)
     */
    public function index($enrollmentId)
    {
        try {
            $user       = auth()->user();
            $enrollment = $this->resolveEnrollment($user, $enrollmentId);

            // ── Current week (first unlocked, not yet completed) ───────────
            $currentWeekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('is_unlocked', true)
                ->where('is_completed', false)
                ->with('moduleWeek.programModule')
                ->orderBy('created_at')
                ->first();

            if (!$currentWeekProgress) {
                $totalWeeks     = $enrollment->program->getPublishedWeeks()->count();
                $completedCount = WeekProgress::where('enrollment_id', $enrollment->id)
                    ->where('is_completed', true)
                    ->count();

                if ($totalWeeks > 0 && $completedCount >= $totalWeeks) {
                    return view('learner.learning.completed', compact('enrollment'));
                }

                return view('learner.learning.no-content', compact('enrollment'));
            }

            $currentWeek = $currentWeekProgress->moduleWeek;

            $currentWeek->load([
                'assessment.questions',
                'assessment.attempts' => function ($q) use ($user, $enrollment) {
                    $q->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id)
                      ->where('status', 'submitted');
                },
            ]);

            // ── Contents for the current week ─────────────────────────────
            // CHANGED: publishedContents() → contents()->orderBy('order')
            $contents = $currentWeek->contents()
                ->with(['contentProgress' => function ($q) use ($user, $enrollment) {
                    $q->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id);
                }])
                ->orderBy('order')
                ->get();

            $contentsJson = $contents->map(function ($content) {
                $progress = $content->contentProgress->first();
                return [
                    'id'             => $content->id,
                    'title'          => $content->title,
                    'type'           => $content->content_type,
                    'video_url'      => $content->video_url,
                    'video_duration' => $content->video_duration_minutes,
                    'file_url'       => $content->file_url,
                    'external_url'   => $content->external_url,
                    'text_content'   => $content->text_content,
                    'is_completed'   => $progress ? $progress->is_completed : false,
                ];
            });

            // ── All week progress for sidebar navigation ──────────────────
            $allWeekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->with('moduleWeek.programModule')
                ->orderBy('created_at')
                ->get();

            // ── Stats ─────────────────────────────────────────────────────
            $stats = $this->calculateLearningStats($user, $enrollment);

            return view('learner.learning.index', compact(
                'enrollment',
                'currentWeek',
                'currentWeekProgress',
                'contents',
                'contentsJson',
                'allWeekProgress',
                'stats'
            ));

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('learner.my-learning')
                ->with(['message' => 'Access denied.', 'alert-type' => 'error']);
        } catch (\Exception $e) {
            return redirect()->route('learner.my-learning')
                ->with(['message' => 'Could not load course. Please try again.', 'alert-type' => 'error']);
        }
    }

    /**
     * Show a specific week's content (if unlocked).
     *
     * CHANGED: publishedContents() → contents()
     *          Sessions query: was cohort_id + week_id → now program_id only
     *          (week_id column removed from live_sessions; sessions are program-level)
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
                    ->with(['message' => 'This week is not yet unlocked.', 'alert-type' => 'warning']);
            }

            $week = $weekProgress->moduleWeek()->with('programModule')->firstOrFail();

            // CHANGED: publishedContents() → contents()
            $contents = $week->contents()
                ->with(['contentProgress' => function ($q) use ($user, $enrollment) {
                    $q->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id);
                }])
                ->orderBy('order')
                ->get();

            // CHANGED: sessions now belong to the program, not a cohort + week
            $sessions = LiveSession::where('program_id', $enrollment->program_id)
                ->upcoming()
                ->orderBy('start_time')
                ->get();

            return view('learner.learning.week', compact(
                'enrollment', 'week', 'weekProgress', 'contents', 'sessions'
            ));

        } catch (\Exception $e) {
            return redirect()->route('learner.learning.index', $enrollmentId)
                ->with(['message' => 'Could not load week content.', 'alert-type' => 'error']);
        }
    }

    /**
     * Show a specific content item.
     */
    public function showContent($contentId)
    {
        try {
            $user      = auth()->user();
            $content   = WeekContent::with(['moduleWeek.programModule'])->findOrFail($contentId);
            $programId = $content->moduleWeek->programModule->program_id;

            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('program_id', $programId)
                ->whereIn('status', ['active', 'completed'])
                ->firstOrFail();

            $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('module_week_id', $content->module_week_id)
                ->firstOrFail();

            if (!$weekProgress->is_unlocked) {
                return redirect()->route('learner.learning.index', $enrollment->id)
                    ->with(['message' => 'This content is not yet available.', 'alert-type' => 'warning']);
            }

            $progress = $content->getProgressFor($user, $enrollment);
            $progress->update(['last_accessed_at' => now()]);

            return view('learner.learning.content', compact(
                'enrollment', 'content', 'progress', 'weekProgress'
            ));

        } catch (\Exception $e) {
            return redirect()->route('learner.my-learning')
                ->with(['message' => 'Content not available.', 'alert-type' => 'error']);
        }
    }

    /**
     * Mark a content item as complete (AJAX).
     */
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
                'message'         => 'Marked as complete!',
                'week_completion' => $weekProgress->progress_percentage,
                'week_completed'  => $weekProgress->is_completed,
            ]);
 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not mark complete. Please try again.',
            ], 500);
        }
    }
 

    /**
     * Update video progress (AJAX — called periodically during video playback).
     */
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

            return response()->json([
                'success'      => true,
                'is_completed' => $progress->is_completed,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * AJAX — week contents for the inline player sidebar.
     *
     * CHANGED: publishedContents() → contents()
     *          description removed from map (no description column any more)
     *          passing_score → pass_percentage (correct DB column name)
     */
    public function getWeekContents($enrollmentId, $weekId)
    {
        try {
            $user       = auth()->user();
            $enrollment = $this->resolveEnrollment($user, $enrollmentId);

            $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('module_week_id', $weekId)
                ->firstOrFail();

            if (!$weekProgress->is_unlocked) {
                return response()->json([
                    'success' => false,
                    'message' => 'This week is not yet unlocked.',
                ], 403);
            }

            $week = $weekProgress->moduleWeek()
                ->with(['programModule', 'assessment.questions'])
                ->firstOrFail();

            // CHANGED: publishedContents() → contents()
            $contents = $week->contents()
                ->with(['contentProgress' => function ($q) use ($user, $enrollment) {
                    $q->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id);
                }])
                ->orderBy('order')
                ->get()
                ->map(function ($content) {
                    $progress = $content->contentProgress->first();
                    return [
                        'id'             => $content->id,
                        'title'          => $content->title,
                        'type'           => $content->content_type,
                        'video_url'      => $content->video_url,
                        'video_duration' => $content->video_duration_minutes,
                        'file_url'       => $content->file_url,
                        'external_url'   => $content->external_url,
                        'text_content'   => $content->text_content,
                        'is_completed'   => $progress ? $progress->is_completed : false,
                    ];
                });

            // Assessment summary — CHANGED: passing_score → pass_percentage
            $assessment = null;
            if ($week->assessment) {
                $submittedAttempt = $week->assessment->attempts()
                    ->where('user_id', $user->id)
                    ->where('enrollment_id', $enrollment->id)
                    ->where('status', 'submitted')
                    ->latest()
                    ->first();

                $assessment = [
                    'id'             => $week->assessment->id,
                    'title'          => $week->assessment->title,
                    'question_count' => $week->assessment->questions->count(),
                    'is_submitted'   => (bool) $submittedAttempt,
                    'score'          => $submittedAttempt?->percentage,    // correct column
                    'passing_score'  => $week->assessment->pass_percentage, // correct column
                ];
            }

            return response()->json([
                'success'    => true,
                'contents'   => $contents,
                'assessment' => $assessment,
                'week'       => [
                    'id'     => $week->id,
                    'title'  => $week->title,
                    'module' => $week->programModule->title ?? '',
                    'number' => $week->week_number,
                ],
            ]);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not load week content.'], 500);
        }
    }

    /**
     * AJAX — full assessment data for the inline player.
     *
     * CHANGED: description removed from response (no description column on assessments)
     *          pass_percentage used throughout (correct DB column)
     */
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
                return response()->json(['success' => false, 'message' => 'Assessment not yet available.'], 403);
            }

            // Latest submitted attempt
            $latestAttempt = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'submitted')
                ->latest()
                ->first();

            // In-progress attempt (not yet submitted)
            $inProgressAttempt = $assessment->attempts()
                ->where('user_id', $user->id)
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'in_progress')
                ->latest()
                ->first();

            $questions = $assessment->questions->map(fn ($q) => [
                'id'      => $q->id,
                'text'    => $q->question_text,
                'type'    => $q->question_type,
                'options' => array_values($q->options ?? []),
                'points'  => $q->points ?? 1,
            ]);

            $passMark = $assessment->pass_percentage ?? 70;

            return response()->json([
                'success'    => true,
                'assessment' => [
                    'id'            => $assessment->id,
                    'title'         => $assessment->title,
                    'passing_score' => $passMark,
                    'time_limit'    => $assessment->time_limit_minutes,
                ],
                'questions'            => $questions,
                'latest_attempt'       => $latestAttempt ? [
                    'id'           => $latestAttempt->id,
                    'score'        => (float) $latestAttempt->percentage,
                    'passed'       => (float) $latestAttempt->percentage >= $passMark,
                    'submitted_at' => $latestAttempt->submitted_at?->diffForHumans(),
                ] : null,
                'in_progress_attempt'  => $inProgressAttempt ? [
                    'id' => $inProgressAttempt->id,
                ] : null,
                'enrollment_id'        => $enrollment->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Could not load assessment.'], 500);
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Resolve and authorise an enrollment for the current user.
     */
    private function resolveEnrollment($user, $enrollmentId): Enrollment
    {
        $enrollment = Enrollment::with(['program', 'cohort'])
            ->findOrFail($enrollmentId);

        if ($enrollment->user_id !== $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Access denied.');
        }

        if (!in_array($enrollment->status, ['active', 'completed'])) {
            throw new \RuntimeException('Enrollment is not active.');
        }

        return $enrollment;
    }

    /**
     * Find the active enrollment for the program that contains a given content item.
     */
    private function resolveEnrollmentForContent($user, WeekContent $content): Enrollment
    {
        $programId = $content->moduleWeek->programModule->program_id;

        return Enrollment::where('user_id', $user->id)
            ->where('program_id', $programId)
            ->whereIn('status', ['active', 'completed'])
            ->firstOrFail();
    }

    /**
     * Summary learning stats for the player header.
     *
     * CHANGED: removed status=published filters on modules/weeks/contents
     *          (no status columns in new schema — program status is the single gate)
     *          sessions now joined through program_id, not cohort_id
     */
    private function calculateLearningStats($user, Enrollment $enrollment): array
    {
        // CHANGED: modules() has no status filter; all modules visible when program is active
        $totalWeeks = $enrollment->program->modules()
            ->withCount('weeks')
            ->get()
            ->sum('weeks_count');

        $completedWeeks = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        // CHANGED: removed ->where('status', 'published') on both queries
        $totalContents = WeekContent::whereHas('moduleWeek.programModule', fn ($q) =>
            $q->where('program_id', $enrollment->program_id)
        )->where('is_required', true)->count();

        $completedContents = ContentProgress::where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->whereHas('weekContent', fn ($q) => $q->where('is_required', true))
            ->count();

        // CHANGED: where('program_id', ...) instead of where('cohort_id', ...)
        $totalSessions = LiveSession::where('program_id', $enrollment->program_id)
            ->where('status', 'completed')
            ->count();

        $attendedSessions = LiveSession::where('program_id', $enrollment->program_id)
            ->where('status', 'completed')
            ->whereJsonContains('attendees', $user->id)
            ->count();

        return [
            'overall_progress'   => $totalWeeks > 0
                                    ? round(($completedWeeks / $totalWeeks) * 100, 1) : 0,
            'completed_weeks'    => $completedWeeks,
            'total_weeks'        => $totalWeeks,
            'completed_contents' => $completedContents,
            'total_contents'     => $totalContents,
            'attended_sessions'  => $attendedSessions,
            'total_sessions'     => $totalSessions,
            'attendance_rate'    => $totalSessions > 0
                                    ? round(($attendedSessions / $totalSessions) * 100, 1) : 0,
        ];
    }
}