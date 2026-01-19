<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Main dashboard now redirects to learning dashboard
     * This keeps backward compatibility while focusing on learning
     */
    public function index()
    {
        $user = auth()->user();

        // Check if user has active enrollment
        $activeEnrollment = $user->enrollments()
            ->where('status', 'active')
            ->first();

        if ($activeEnrollment) {
            // Redirect to learning dashboard
            return redirect()->route('learner.learning.index');
        }

        // Check if user has pending enrollment (payment not completed)
        $pendingEnrollment = $user->enrollments()
            ->with(['program', 'payments'])
            ->where('status', 'pending')
            ->first();

        if ($pendingEnrollment) {
            return view('learner.dashboard.pending', compact('pendingEnrollment'));
        }

        // No enrollment, show programs
        return redirect()->route('learner.programs.index')
            ->with(['message' => 'Welcome! Choose a program to begin your learning journey.', 'alert-type' => 'info']);
    }
}