<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get learner's enrolled cohorts
        $cohortIds = $user->enrollments()->pluck('cohort_id');
        
        // Get upcoming sessions for learner's cohorts
        $upcomingSessions = LiveSession::whereIn('cohort_id', $cohortIds)
            ->upcoming()
            ->limit(10)
            ->get();
        
        return view('learner.calendar', compact('upcomingSessions'));
    }
    
    public function getEvents(Request $request)
    {
        $user = auth()->user();
        
        // Get learner's enrolled cohorts
        $cohortIds = $user->enrollments()->pluck('cohort_id');
        
        $query = LiveSession::whereIn('cohort_id', $cohortIds);
        
        if ($request->start && $request->end) {
            $query->whereBetween('start_time', [$request->start, $request->end]);
        }
        
        $sessions = $query->with(['mentor', 'cohort'])->get();
        
        $events = $sessions->map(function($session) {
            return $session->calendar_event;
        });
        
        return response()->json($events);
    }
}