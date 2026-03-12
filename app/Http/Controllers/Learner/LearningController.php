<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\WeekContent;
use App\Models\WeekProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LearningController extends Controller
{
    /**
     * Main learning view for a specific enrollment.
     * Called from My Learning page — enrollmentId is now explicit.
     */
    public function index($enrollmentId)
    {
        try {
            $user       = auth()->user();
            $enrollment = $this->resolveEnrollment($user, $enrollmentId);

            // ── Current week ─────────────────────────────────────────────
            $currentWeekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('is_unlocked', true)
                ->where('is_completed', false)
                ->with('moduleWeek.programModule')
                ->orderBy('created_at')
                ->first();

            if (!$currentWeekProgress) {
                $publishedWeekCount = $enrollment->program->getPublishedWeeks()->count();
                $completedCount     = WeekProgress::where('enrollment_id', $enrollment->id)
                    ->where('is_completed', true)
                    ->count();

                if ($publishedWeekCount > 0 && $completedCount >= $publishedWeekCount) {
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

            // ── Contents for current week ─────────────────────────────────
            $contents = $currentWeek->publishedContents()
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
                    'description'    => $content->description,
                    'video_url'      => $content->video_url,
                    'video_duration' => $content->video_duration_minutes,
                    'file_url'       => $content->file_url,
                    'external_url'   => $content->external_url,
                    'text_content'   => $content->text_content,
                    'is_completed'   => $progress ? $progress->is_completed : false,
                ];
            });

            // ── All week progress for sidebar navigation ─────────────────
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

            $contents = $week->publishedContents()
                ->with(['contentProgress' => function ($q) use ($user, $enrollment) {
                    $q->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id);
                }])
                ->orderBy('order')
                ->get();

            $sessions = LiveSession::where('cohort_id', $enrollment->cohort_id)
                ->where('week_id', $week->id)
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
            $user       = auth()->user();
            // Resolve from content — find the correct enrollment
            $content    = WeekContent::with(['moduleWeek.programModule'])->findOrFail($contentId);
            $programId  = $content->moduleWeek->programModule->program_id;

            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('program_id', $programId)
                ->where('status', 'active')
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

            return response()->json([
                'success'          => true,
                'message'          => 'Marked as complete!',
                'week_completion'  => $weekProgress->progress_percentage,
                'week_completed'   => $weekProgress->is_completed,
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

    // ── Private helpers ─────────────────────────────────────────────────────

    /**
     * Resolve and authorize an enrollment for the current user.
     * Throws AuthorizationException if the enrollment doesn't belong to the user.
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
     * Calculate summary learning statistics for a user's enrollment.
     */
    private function calculateLearningStats($user, Enrollment $enrollment): array
    {
        $totalWeeks = $enrollment->program->publishedModules()
            ->withCount(['weeks' => fn ($q) => $q->where('status', 'published')])
            ->get()
            ->sum('weeks_count');

        $completedWeeks = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $totalContents = WeekContent::whereHas('moduleWeek.programModule', fn ($q) =>
            $q->where('program_id', $enrollment->program_id)
        )->where('status', 'published')->where('is_required', true)->count();

        $completedContents = ContentProgress::where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->whereHas('weekContent', fn ($q) => $q->where('is_required', true))
            ->count();

        $totalSessions = LiveSession::where('cohort_id', $enrollment->cohort_id)
            ->where('status', 'completed')
            ->count();

        $attendedSessions = LiveSession::where('cohort_id', $enrollment->cohort_id)
            ->where('status', 'completed')
            ->whereJsonContains('attendees', $user->id)
            ->count();

        return [
            'overall_progress'  => $totalWeeks > 0 ? round(($completedWeeks / $totalWeeks) * 100, 1) : 0,
            'completed_weeks'   => $completedWeeks,
            'total_weeks'       => $totalWeeks,
            'completed_contents' => $completedContents,
            'total_contents'    => $totalContents,
            'attended_sessions' => $attendedSessions,
            'total_sessions'    => $totalSessions,
            'attendance_rate'   => $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 1) : 0,
        ];
    }

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

            $contents = $week->publishedContents()
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
                        'description'    => $content->description,
                        'video_url'      => $content->video_url,
                        'video_duration' => $content->video_duration_minutes,
                        'file_url'       => $content->file_url,
                        'external_url'   => $content->external_url,
                        'text_content'   => $content->text_content,
                        'is_completed'   => $progress ? $progress->is_completed : false,
                    ];
                });

            // Assessment summary for the sidebar item
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
                    'title'          => $week->assessment->title ?? 'Week Assessment',
                    'question_count' => $week->assessment->questions->count(),
                    'is_submitted'   => (bool) $submittedAttempt,
                    'score'          => $submittedAttempt?->score,
                    'passing_score'  => $week->assessment->passing_score ?? 70,
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
     * AJAX — return full assessment data for rendering inline in the player.
     * Called once when the learner clicks "Week Assessment" in the sidebar.
     *
     * Route: GET /learner/learning/{enrollmentId}/assessment/{assessmentId}
     * Name:  learner.learning.assessment-data
     */
     public function getAssessmentData($enrollmentId, $assessmentId)
        {
            try {
                $user       = auth()->user();
                $enrollment = $this->resolveEnrollment($user, $enrollmentId);
    
                $assessment = \App\Models\Assessment::with('questions')->findOrFail($assessmentId);
    
                $week = $assessment->moduleWeek;
                $weekProgress = \App\Models\WeekProgress::where('enrollment_id', $enrollment->id)
                    ->where('module_week_id', $week->id)
                    ->firstOrFail();
    
                if (!$weekProgress->is_unlocked) {
                    return response()->json(['success' => false, 'message' => 'Assessment not yet available.'], 403);
                }
    
                // Latest submitted attempt (if any)
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
    
                $questions = $assessment->questions->map(function ($q) {
                    return [
                        'id'      => $q->id,
                        'text'    => $q->question_text,
                        'type'    => $q->question_type,  // multiple_choice | true_false | short_answer
                        'options' => array_values($q->options ?? []),  // ensure indexed array for JS
                        'points'  => $q->points ?? 1,
                    ];
                });
    
                // FIX #4: use pass_percentage (the actual DB column), not passing_score
                $passMark = $assessment->pass_percentage ?? 70;
    
                return response()->json([
                    'success'    => true,
                    'assessment' => [
                        'id'            => $assessment->id,
                        'title'         => $assessment->title ?? 'Week Assessment',
                        'description'   => $assessment->description ?? '',
                        'passing_score' => $passMark,
                        'time_limit'    => $assessment->time_limit_minutes ?? null,
                    ],
                    'questions'           => $questions,
                    'latest_attempt'      => $latestAttempt ? [
                        'id'           => $latestAttempt->id,
                        // FIX: use ->percentage (the scored float column), not ->score
                        'score'        => (float) $latestAttempt->percentage,
                        'passed'       => (float) $latestAttempt->percentage >= $passMark,
                        'submitted_at' => $latestAttempt->submitted_at?->diffForHumans(),
                    ] : null,
                    'in_progress_attempt' => $inProgressAttempt ? [
                        'id' => $inProgressAttempt->id,
                    ] : null,
                    'enrollment_id'       => $enrollment->id,
                ]);
    
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Could not load assessment.'], 500);
            }
        }
}