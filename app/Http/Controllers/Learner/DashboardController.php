<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Simplified dashboard - redirects based on enrollment status
     */
    public function index()
    {
        $user = auth()->user();

        // Check for active enrollment
        $activeEnrollment = $user->enrollments()
            ->where('status', 'active')
            ->first();

        if ($activeEnrollment) {
            // Has active enrollment - redirect to learning
            return redirect()->route('learner.learning.index');
        }

        // Check for pending enrollment (payment not completed)
        $pendingEnrollment = $user->enrollments()
            ->with(['program', 'payments'])
            ->where('status', 'pending')
            ->first();

        if ($pendingEnrollment) {
            return view('learner.dashboard.pending', compact('pendingEnrollment'));
        }

        // No enrollment - redirect to programs page
        return redirect()->route('learner.programs.index')
            ->with(['message' => 'Welcome! Choose a program to begin your learning journey.', 'alert-type' => 'info']);
    }
}