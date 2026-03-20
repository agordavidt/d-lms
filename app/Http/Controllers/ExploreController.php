<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExploreController extends Controller
{
    /**
     * Public program catalog.
     * Available to guests and authenticated learners.
     * Enrolled programs are flagged so the UI can reflect correct CTA.
     */
    public function index(Request $request)
    {
        try {
            // Active programs that have at least one available cohort
            $programs = Program::where('status', 'active')
            ->with('mentor')
            ->withCount('enrollments')
            ->orderBy('name')
            ->get();

            // If user is logged in, find which programs they're already enrolled in
            $enrolledProgramIds = collect();

            if (auth()->check()) {
                $enrolledProgramIds = Enrollment::where('user_id', auth()->id())
                    ->whereIn('status', ['active', 'pending', 'completed'])
                    ->pluck('program_id');
            }

            return view('explore', compact('programs', 'enrolledProgramIds'));

        } catch (\Exception $e) {
            return view('explore', [
                'programs'           => collect(),
                'enrolledProgramIds' => collect(),
            ])->with([
                'message'    => 'Could not load programs. Please try again.',
                'alert-type' => 'error',
            ]);
        }
    }
}