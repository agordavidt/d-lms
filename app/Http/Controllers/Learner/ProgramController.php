<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Browse all available programs
     */
    public function index()
    {
        $user = auth()->user();

        // Check if user already has an active or pending enrollment
        $existingEnrollment = $user->enrollments()
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('learner.dashboard')
                ->with(['message' => 'You already have an active enrollment. Complete your current program first.', 'alert-type' => 'info']);
        }

        $programs = Program::active()
            ->withCount('enrollments')
            ->with(['cohorts' => function($query) {
                $query->active()->orderBy('start_date');
            }])
            ->paginate(9);

        return view('learner.programs.index', compact('programs'));
    }

    /**
     * Show program details with enrollment form
     */
    public function show($slug)
    {
        $user = auth()->user();

        // Check if user already has an active or pending enrollment
        $existingEnrollment = $user->enrollments()
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('learner.dashboard')
                ->with(['message' => 'You already have an active enrollment.', 'alert-type' => 'info']);
        }

        $program = Program::where('slug', $slug)
            ->where('status', 'active')
            ->with(['cohorts' => function($query) {
                $query->active()
                      ->where('enrolled_count', '<', \DB::raw('max_students'))
                      ->orderBy('start_date');
            }])
            ->firstOrFail();

        // Check if program has available cohorts
        if ($program->cohorts->isEmpty()) {
            return view('learner.programs.no-cohorts', compact('program'));
        }

        return view('learner.programs.show', compact('program'));
    }
}