<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        try {
            $user = auth()->user();

            $program = Program::where('slug', $slug)
                ->where('status', 'active')
                ->with(['cohorts' => function($query) {
                    $query->active()
                          ->where('enrolled_count', '<', DB::raw('max_students'))
                          ->orderBy('start_date');
                }])
                ->firstOrFail();

            // Check if program has available cohorts
            if ($program->cohorts->isEmpty()) {
                return view('learner.programs.no-cohorts', compact('program'));
            }

            // Check if user is enrolled in THIS specific program
            $enrollment = $user->enrollments()
                ->where('program_id', $program->id)
                ->whereIn('status', ['active', 'pending'])
                ->first();

            // Pass both program and enrollment to the view
            return view('learner.programs.show', compact('program', 'enrollment'));

        } catch (\Exception $e) {
            return redirect()
                ->route('learner.programs.index')
                ->with(['message' => 'Program not found or unavailable.', 'alert-type' => 'error']);
        }
    }

    /**
     * Process enrollment request
     */
    public function enroll(Request $request, Program $program)
    {
        try {
            $user = auth()->user();

            // Check if user already has an active or pending enrollment in ANY program
            $existingEnrollment = $user->enrollments()
                ->whereIn('status', ['active', 'pending'])
                ->first();

            if ($existingEnrollment) {
                return redirect()->back()
                    ->with(['message' => 'You already have an active enrollment.', 'alert-type' => 'error']);
            }

            // Validate the request
            $validated = $request->validate([
                'cohort_id' => 'required|exists:cohorts,id',
                'payment_plan' => 'required|in:full,installment'
            ]);

            // Check if cohort is full
            $cohort = $program->cohorts()->findOrFail($validated['cohort_id']);
            
            if ($cohort->enrolled_count >= $cohort->max_students) {
                return redirect()->back()
                    ->with(['message' => 'This cohort is full. Please select another.', 'alert-type' => 'error']);
            }

            DB::beginTransaction();

            // Create enrollment
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'program_id' => $program->id,
                'cohort_id' => $cohort->id,
                'status' => 'pending',
                'payment_plan' => $validated['payment_plan'],
                'amount_due' => $program->price,
                'amount_paid' => 0
            ]);

            // Increment cohort enrolled count
            $cohort->increment('enrolled_count');

            DB::commit();

            // Redirect to payment
            return redirect()->route('payment.initiate', ['enrollment' => $enrollment->id])
                ->with(['message' => 'Enrollment created! Please complete payment.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with(['message' => 'Enrollment failed. Please try again.', 'alert-type' => 'error']);
        }
    }
}