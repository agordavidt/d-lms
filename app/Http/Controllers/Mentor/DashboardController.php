<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Enrollment;
use App\Models\LiveSession;

class DashboardController extends Controller
{
    public function index()
    {
        $mentor     = auth()->user();
        $programIds = Program::where('mentor_id', $mentor->id)->pluck('id');

        $stats = [
            'programs'  => $programIds->count(),
            'learners'  => Enrollment::whereIn('program_id', $programIds)->where('status', 'active')->count(),
            'drafts'    => Program::where('mentor_id', $mentor->id)->where('status', 'draft')->count(),
            'reviews'   => Program::where('mentor_id', $mentor->id)->where('status', 'under_review')->count(),
        ];

        $recentPrograms = Program::where('mentor_id', $mentor->id)
            ->withCount('enrollments')
            ->latest()
            ->take(5)
            ->get();

        $upcomingSessions = LiveSession::whereIn('program_id', $programIds)
            ->upcoming()
            ->with('program')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return view('mentor.dashboard', compact('stats', 'recentPrograms', 'upcomingSessions'));
    }
}