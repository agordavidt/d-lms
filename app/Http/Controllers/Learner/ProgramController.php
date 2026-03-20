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
        // No payment_plan validation — plan is chosen after this check
        
        if (!$program->isEnrollable()) {
            return response()->json([
                'success' => false,
                'message' => 'This program is not currently accepting enrollments.',
            ], 422);
        }

        $existing = Enrollment::where('user_id', auth()->id())
            ->where('program_id', $program->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this program.',
            ], 422);
        }

        // No cohort lookup — cohort is auto-assigned on payment confirmation
        return response()->json([
            'success'             => true,
            'program_id'          => $program->id,
            'price'               => $program->price,
            'discounted_price'    => $program->discounted_price,
            'installment_amount'  => $program->installment_amount,
            'discount_percentage' => $program->discount_percentage,
        ]);
    }
}