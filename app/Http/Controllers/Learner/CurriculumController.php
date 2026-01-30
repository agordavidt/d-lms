<?php


namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentProgress;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    /**
     * Display full program curriculum with progress tracking
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
                ->with(['message' => 'Please enroll in a program first.', 'alert-type' => 'info']);
        }

        // Get all modules with weeks and content
        $modules = $enrollment->program->publishedModules()
            ->with(['weeks' => function($query) use ($user, $enrollment) {
                $query->published()
                      ->with([
                          'weekProgress' => function($q) use ($user, $enrollment) {
                              $q->where('user_id', $user->id)
                                ->where('enrollment_id', $enrollment->id);
                          },
                          'contents' => function($q) use ($user, $enrollment) {
                              $q->published()
                                ->with(['contentProgress' => function($cp) use ($user, $enrollment) {
                                    $cp->where('user_id', $user->id)
                                       ->where('enrollment_id', $enrollment->id);
                                }])
                                ->orderBy('order');
                          }
                      ])
                      ->orderBy('week_number');
            }])
            ->get();

        // Calculate overall progress
        $totalContents = 0;
        $completedContents = 0;

        foreach ($modules as $module) {
            foreach ($module->weeks as $week) {
                foreach ($week->contents as $content) {
                    $totalContents++;
                    if ($content->contentProgress->first()?->is_completed) {
                        $completedContents++;
                    }
                }
            }
        }

        $overallProgress = $totalContents > 0 ? round(($completedContents / $totalContents) * 100, 1) : 0;

        return view('learner.curriculum.index', compact(
            'enrollment',
            'modules',
            'overallProgress',
            'totalContents',
            'completedContents'
        ));
    }
}