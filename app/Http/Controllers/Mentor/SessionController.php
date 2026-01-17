<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cohort;
use App\Models\LiveSession;
use App\Models\Program;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index()
    {
        $mentor = auth()->user();
        
        $sessions = LiveSession::where('mentor_id', $mentor->id)
            ->with(['program', 'cohort'])
            ->latest('start_time')
            ->paginate(15);

        return view('mentor.sessions.index', compact('sessions'));
    }

    public function calendar()
    {
        $mentor = auth()->user();
        
        // Get programs where mentor is teaching
        $programIds = LiveSession::where('mentor_id', $mentor->id)
            ->distinct()
            ->pluck('program_id');
            
        $programs = Program::whereIn('id', $programIds)->get();
        
        // Get cohorts where mentor is teaching
        $cohortIds = LiveSession::where('mentor_id', $mentor->id)
            ->distinct()
            ->pluck('cohort_id');
            
        $cohorts = Cohort::whereIn('id', $cohortIds)->get();

        return view('mentor.sessions.calendar', compact('programs', 'cohorts'));
    }

    public function getEvents(Request $request)
    {
        $mentor = auth()->user();
        
        $query = LiveSession::where('mentor_id', $mentor->id);

        // Filter by cohort if provided
        if ($request->cohort_id) {
            $query->where('cohort_id', $request->cohort_id);
        }

        // Filter by program if provided
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        // Get events for calendar date range
        if ($request->start && $request->end) {
            $query->whereBetween('start_time', [$request->start, $request->end]);
        }

        $sessions = $query->with(['mentor', 'cohort'])->get();

        $events = $sessions->map(function($session) {
            return $session->calendar_event;
        });

        return response()->json($events);
    }

    public function create()
    {
        $mentor = auth()->user();
        
        // Get all programs (mentors can teach any program)
        $programs = Program::active()->get();
        
        // Get all active cohorts
        $cohorts = Cohort::active()->get();

        return view('mentor.sessions.create', compact('programs', 'cohorts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_type' => 'required|in:live_class,workshop,q&a,assessment',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'meet_link' => 'nullable|url',
        ]);

        try {
            $session = LiveSession::create([
                'program_id' => $request->program_id,
                'cohort_id' => $request->cohort_id,
                'mentor_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'session_type' => $request->session_type,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'meet_link' => $request->meet_link,
                'status' => 'scheduled',
            ]);

            AuditLog::log('session_created', auth()->user(), [
                'description' => 'Mentor scheduled session: ' . $session->title,
                'model_type' => LiveSession::class,
                'model_id' => $session->id,
            ]);

            return redirect()->route('mentor.sessions.calendar')
                ->with(['message' => 'Session scheduled successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to schedule session: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function show(LiveSession $session)
    {
        // Ensure mentor can only view their own sessions
        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        $session->load(['program', 'cohort', 'mentor']);
        
        // Get attendees details
        $attendeeIds = $session->attendees ?? [];
        $attendees = \App\Models\User::whereIn('id', $attendeeIds)->get();

        return view('mentor.sessions.show', compact('session', 'attendees'));
    }

    public function edit(LiveSession $session)
    {
        // Ensure mentor can only edit their own sessions
        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        $programs = Program::active()->get();
        $cohorts = Cohort::active()->get();

        return view('mentor.sessions.edit', compact('session', 'programs', 'cohorts'));
    }

    public function update(Request $request, LiveSession $session)
    {
        // Ensure mentor can only update their own sessions
        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_type' => 'required|in:live_class,workshop,q&a,assessment',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'meet_link' => 'nullable|url',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
            'recording_link' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        try {
            $session->update($request->all());

            AuditLog::log('session_updated', auth()->user(), [
                'description' => 'Mentor updated session: ' . $session->title,
                'model_type' => LiveSession::class,
                'model_id' => $session->id,
            ]);

            return redirect()->route('mentor.sessions.calendar')
                ->with(['message' => 'Session updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update session: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(LiveSession $session)
    {
        // Ensure mentor can only delete their own sessions
        if ($session->mentor_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            AuditLog::log('session_deleted', auth()->user(), [
                'description' => 'Mentor deleted session: ' . $session->title,
                'model_type' => LiveSession::class,
                'model_id' => $session->id,
            ]);

            $session->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete session.'
            ], 500);
        }
    }

    /**
     * Mark attendance for a session
     */
    public function markAttendance(Request $request, LiveSession $session)
    {
        // Ensure mentor can only mark attendance for their own sessions
        if ($session->mentor_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        try {
            $session->update([
                'attendees' => $request->user_ids,
                'total_attendees' => count($request->user_ids),
                'status' => 'completed'
            ]);

            AuditLog::log('attendance_marked', auth()->user(), [
                'description' => 'Mentor marked attendance for session: ' . $session->title,
                'model_type' => LiveSession::class,
                'model_id' => $session->id,
                'attendees_count' => count($request->user_ids)
            ]);

            return back()->with([
                'message' => 'Attendance recorded successfully!',
                'alert-type' => 'success'
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'message' => 'Failed to record attendance: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}