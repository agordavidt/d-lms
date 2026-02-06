<?php


namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Enrollment;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    /**
     * Simple programs listing - payment focused
     * Navigation already ensures only non-enrolled users see this
     */
    public function index()
    {
        // Get active programs with available cohorts
        $programs = Program::active()
            ->whereHas('cohorts', function($query) {
                $query->active()
                      ->where('enrolled_count', '<', DB::raw('max_students'));
            })
            ->with(['cohorts' => function($query) {
                $query->active()
                      ->where('enrolled_count', '<', DB::raw('max_students'))
                      ->orderBy('start_date')
                      ->limit(1); // Only get the earliest cohort
            }])
            ->get();

        return view('learner.programs.index', compact('programs'));
    }

    /**
     * Process enrollment request via AJAX
     * Just validates - actual enrollment created by PaymentController
     * No need to check for existing enrollment - navigation already prevents access
     */
    public function enroll(Request $request, Program $program)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'payment_plan' => 'required|in:one-time,installment'
            ]);

            // Get the earliest available cohort
            $cohort = $program->cohorts()
                ->active()
                ->where('enrolled_count', '<', DB::raw('max_students'))
                ->orderBy('start_date')
                ->first();

            if (!$cohort) {
                return response()->json([
                    'success' => false,
                    'message' => 'No available cohorts for this program.'
                ], 400);
            }

            // Return data for payment form submission
            // Enrollment will be created by PaymentController
            return response()->json([
                'success' => true,
                'message' => 'Proceeding to payment...',
                'program_id' => $program->id,
                'cohort_id' => $cohort->id,
                'payment_plan' => $validated['payment_plan']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}