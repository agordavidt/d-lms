<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class GraduationController extends Controller
{
    
    public function request(Request $request, Enrollment $enrollment)
    {
        $user = auth()->user();

        // Security — enrollment must belong to this user
        if ($enrollment->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Already in the graduation pipeline
        if (in_array($enrollment->graduation_status, ['pending_review', 'graduated'])) {
            $message = $enrollment->graduation_status === 'graduated'
                ? 'You have already graduated!'
                : 'Your graduation request is already under review.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with(['message' => $message, 'alert-type' => 'info']);
        }

        // Try to request graduation
        $requested = $enrollment->requestGraduation();

        if (!$requested) {
            // Build a helpful message explaining which criteria aren't met yet
            $reasons = [];

            if (!$enrollment->hasCompletedAllContent()) {
                $reasons[] = 'not all course content is completed';
            }
            if (!$enrollment->hasPassedAllAssessments()) {
                $reasons[] = 'not all weekly assessments have been attempted';
            }
            if (!$enrollment->meetsMinimumGradeRequirement()) {
                $avg     = $enrollment->final_grade_avg ?? 0;
                $min     = $enrollment->program->min_passing_average ?? 0;
                $reasons[] = "your current average ({$avg}%) is below the required {$min}%";
            }

            $detail  = count($reasons) ? ' Reason: ' . implode('; ', $reasons) . '.' : '';
            $message = 'You are not yet eligible for graduation.' . $detail;

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with(['message' => $message, 'alert-type' => 'warning']);
        }

        $successMsg = 'Your graduation request has been submitted! Our team will review it shortly.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return back()->with(['message' => $successMsg, 'alert-type' => 'success']);
    }

    /**
     * Show graduation status page for a specific enrollment.
     *
     * Route: GET /learner/graduation/{enrollment}
     * Name:  learner.graduation.status
     */
    public function status(Enrollment $enrollment)
    {
        $user = auth()->user();

        if ($enrollment->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $enrollment->load(['program', 'cohort', 'weekProgress']);

        $eligibility = [
            'all_content_complete'   => $enrollment->hasCompletedAllContent(),
            'all_assessments_taken'  => $enrollment->hasPassedAllAssessments(),
            'meets_grade_requirement' => $enrollment->meetsMinimumGradeRequirement(),
        ];

        return view('learner.graduation.status', compact('enrollment', 'eligibility'));
    }
}