<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Browse all available programs
     */
    public function index()
    {
        $programs = Program::active()
            ->withCount('enrollments')
            ->with(['cohorts' => function($query) {
                $query->active()->orderBy('start_date');
            }])
            ->paginate(9);

        // Get user's enrolled program IDs
        $enrolledProgramIds = auth()->user()->enrollments()
            ->whereIn('status', ['active', 'pending'])
            ->pluck('program_id')
            ->toArray();

        return view('learner.programs.index', compact('programs', 'enrolledProgramIds'));
    }

    /**
     * Show program details
     */
    public function show($slug)
    {
        $program = Program::where('slug', $slug)
            ->where('status', 'active')
            ->with(['cohorts' => function($query) {
                $query->active()->orderBy('start_date');
            }])
            ->firstOrFail();

        // Check if user is already enrolled
        $enrollment = auth()->user()->enrollments()
            ->where('program_id', $program->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        return view('learner.programs.show', compact('program', 'enrollment'));
    }

    /**
     * Show enrollment form
     */
    public function enroll($slug)
    {
        $program = Program::where('slug', $slug)
            ->where('status', 'active')
            ->with(['cohorts' => function($query) {
                $query->active()->where('enrolled_count', '<', \DB::raw('max_students'));
            }])
            ->firstOrFail();

        // Check if already enrolled
        $existingEnrollment = auth()->user()->enrollments()
            ->where('program_id', $program->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingEnrollment) {
            return redirect()->route('learner.programs.show', $program->slug)
                ->with([
                    'message' => 'You are already enrolled in this program!',
                    'alert-type' => 'info'
                ]);
        }

        return view('learner.programs.enroll', compact('program'));
    }
}