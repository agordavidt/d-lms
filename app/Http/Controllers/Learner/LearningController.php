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
     * Main learning page - shows current week's content only
     */
    public function index()
    {
        $user = auth()->user();

        // Get active enrollment
        $enrollment = $user->enrollments()
            ->with(['program', 'cohort'])
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return redirect()->route('learner.programs.index')
                ->with(['message' => 'Please enroll in a program to start learning.', 'alert-type' => 'info']);
        }

        // Get current week progress (unlocked but not completed)
        $currentWeekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('is_unlocked', true)
            ->where('is_completed', false)
            ->with('moduleWeek.programModule')
            ->orderBy('created_at')
            ->first();

        if (!$currentWeekProgress) {
            // Check if all weeks are completed
            $allCompleted = WeekProgress::where('enrollment_id', $enrollment->id)
                ->where('is_completed', true)
                ->count() === $enrollment->program->getPublishedWeeks()->count();

            if ($allCompleted) {
                return view('learner.learning.completed', compact('enrollment'));
            }

            return view('learner.learning.no-content', compact('enrollment'));
        }

        $currentWeek = $currentWeekProgress->moduleWeek;

        // Get week contents with progress
        $contents = $currentWeek->publishedContents()
            ->with(['contentProgress' => function($query) use ($user, $enrollment) {
                $query->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id);
            }])
            ->orderBy('order')
            ->get();

        // Prepare content data for JavaScript (moved from view)
        $contentsJson = $contents->map(function($content) {
            $progress = $content->contentProgress->first();
            
            return [
                'id' => $content->id,
                'title' => $content->title,
                'type' => $content->content_type,
                'description' => $content->description,
                'video_url' => $content->video_url,
                'video_duration' => $content->video_duration_minutes,
                'file_url' => $content->file_url,
                'external_url' => $content->external_url,
                'text_content' => $content->text_content,
                'is_completed' => $progress ? $progress->is_completed : false
            ];
        });

        // Calculate learning stats
        $stats = $this->calculateLearningStats($user, $enrollment);

        return view('learner.learning.index', compact(
            'enrollment',
            'currentWeek',
            'currentWeekProgress',
            'contents',
            'contentsJson',
            'stats'
        ));
    }

    /**
     * Show specific week content (if unlocked)
     */
    public function showWeek($weekId)
    {
        $user = auth()->user();
        $enrollment = $user->enrollments()->where('status', 'active')->first();

        if (!$enrollment) {
            return redirect()->route('learner.programs.index');
        }

        // Check if week is unlocked
        $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('module_week_id', $weekId)
            ->first();

        if (!$weekProgress || !$weekProgress->is_unlocked) {
            return redirect()->route('learner.learning.index')
                ->with(['message' => 'This week is not yet unlocked.', 'alert-type' => 'warning']);
        }

        $week = $weekProgress->moduleWeek()->with('programModule')->first();

        // Get week contents with progress
        $contents = $week->publishedContents()
            ->with(['contentProgress' => function($query) use ($user, $enrollment) {
                $query->where('user_id', $user->id)
                      ->where('enrollment_id', $enrollment->id);
            }])
            ->orderBy('order')
            ->get();

        // Get live sessions for this week
        $sessions = LiveSession::where('cohort_id', $enrollment->cohort_id)
            ->where('week_id', $week->id)
            ->orderBy('start_time')
            ->get();

        return view('learner.learning.week', compact(
            'enrollment',
            'week',
            'weekProgress',
            'contents',
            'sessions'
        ));
    }

    /**
     * Show specific content
     */
    public function showContent($contentId)
    {
        $user = auth()->user();
        $enrollment = $user->enrollments()->where('status', 'active')->first();

        if (!$enrollment) {
            return redirect()->route('learner.programs.index');
        }

        $content = WeekContent::with(['moduleWeek.programModule'])->findOrFail($contentId);

        // Check if week is unlocked
        $weekProgress = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('module_week_id', $content->module_week_id)
            ->first();

        if (!$weekProgress || !$weekProgress->is_unlocked) {
            return redirect()->route('learner.learning.index')
                ->with(['message' => 'This content is not yet available.', 'alert-type' => 'warning']);
        }

        // Get or create progress
        $progress = $content->getProgressFor($user, $enrollment);
        
        // Mark as started and update last accessed
        $progress->markAsStarted();

        return view('learner.learning.content', compact('enrollment', 'content', 'progress', 'weekProgress'));
    }

    /**
     * Mark content as completed
     */
    public function markContentComplete(Request $request, $contentId)
    {
        $user = auth()->user();
        $enrollment = $user->enrollments()->where('status', 'active')->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'No active enrollment'], 400);
        }

        $content = WeekContent::findOrFail($contentId);
        $progress = $content->getProgressFor($user, $enrollment);

        $progress->markAsCompleted();

        // Get updated week progress
        $weekProgress = $progress->weekContent->moduleWeek->getProgressFor($user, $enrollment);

        return response()->json([
            'success' => true,
            'message' => 'Content marked as complete!',
            'week_completion' => $weekProgress->completion_percentage
        ]);
    }

    /**
     * Update content progress (for videos)
     */
    public function updateContentProgress(Request $request, $contentId)
    {
        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        $user = auth()->user();
        $enrollment = $user->enrollments()->where('status', 'active')->first();

        if (!$enrollment) {
            return response()->json(['success' => false], 400);
        }

        $content = WeekContent::findOrFail($contentId);
        $progress = $content->getProgressFor($user, $enrollment);

        $progress->updateProgress($request->progress_percentage);
        
        if ($request->time_spent) {
            $progress->addTimeSpent($request->time_spent);
        }

        return response()->json([
            'success' => true,
            'is_completed' => $progress->is_completed,
        ]);
    }

    /**
     * Calculate learning stats
     */
    private function calculateLearningStats($user, $enrollment)
    {
        $totalWeeks = $enrollment->program->getPublishedWeeks()->count();
        $completedWeeks = WeekProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $totalContents = WeekContent::whereHas('moduleWeek.programModule', function($query) use ($enrollment) {
            $query->where('program_id', $enrollment->program_id);
        })
        ->where('status', 'published')
        ->where('is_required', true)
        ->count();

        $completedContents = ContentProgress::where('user_id', $user->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->whereHas('weekContent', function($query) {
                $query->where('is_required', true);
            })
            ->count();

        $cohortIds = collect([$enrollment->cohort_id]);
        $totalSessions = LiveSession::whereIn('cohort_id', $cohortIds)
            ->where('status', 'completed')
            ->count();

        $attendedSessions = LiveSession::whereIn('cohort_id', $cohortIds)
            ->where('status', 'completed')
            ->whereJsonContains('attendees', $user->id)
            ->count();

        return [
            'overall_progress' => $totalWeeks > 0 ? round(($completedWeeks / $totalWeeks) * 100, 1) : 0,
            'completed_weeks' => $completedWeeks,
            'total_weeks' => $totalWeeks,
            'completed_contents' => $completedContents,
            'total_contents' => $totalContents,
            'attended_sessions' => $attendedSessions,
            'total_sessions' => $totalSessions,
            'attendance_rate' => $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 1) : 0,
        ];
    }
}