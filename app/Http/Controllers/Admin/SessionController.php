<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cohort;
use App\Models\LiveSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = LiveSession::with(['program', 'cohort', 'mentor'])
            ->latest('start_time')
            ->paginate(20);

        return view('admin.sessions.index', compact('sessions'));
    }

    public function calendar()
    {
        $programs = Program::active()->get();
        $cohorts = Cohort::active()->get();
        
        return view('admin.sessions.calendar', compact('programs', 'cohorts'));
    }

    public function getEvents(Request $request)
    {
        $query = LiveSession::query();

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
        $programs = Program::active()->get();
        $cohorts = Cohort::active()->get();
        $mentors = User::where('role', 'mentor')->where('status', 'active')->get();

        return view('admin.sessions.create', compact('programs', 'cohorts', 'mentors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'mentor_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_type' => 'required|in:live_class,workshop,q&a,assessment',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'meet_link' => 'nullable|url',
        ]);

        try {
            $session = LiveSession::create($request->all());

            AuditLog::log('session_created', auth()->user(), [
                'description' => 'Created live session: ' . $session->title,
                'model_type' => LiveSession::class,
                'model_id' => $session->id,
            ]);

            return redirect()->route('admin.sessions.calendar')
                ->with(['message' => 'Session scheduled successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create session: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function edit(LiveSession $session)
    {
        $programs = Program::active()->get();
        $cohorts = Cohort::active()->get();
        $mentors = User::where('role', 'mentor')->where('status', 'active')->get();

        return view('admin.sessions.edit', compact('session', 'programs', 'cohorts', 'mentors'));
    }

    public function update(Request $request, LiveSession $session)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'cohort_id' => 'required|exists:cohorts,id',
            'mentor_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'session_type' => 'required|in:live_class,workshop,q&a,assessment',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'meet_link' => 'nullable|url',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        try {
            $session->update($request->all());

            AuditLog::log('session_updated', auth()->user(), [
                'description' => 'Updated live session: ' . $session->title,
                'model_type' => LiveSession::class,
                'model_id' => $session->id,
            ]);

            return redirect()->route('admin.sessions.calendar')
                ->with(['message' => 'Session updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update session: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(LiveSession $session)
    {
        try {
            AuditLog::log('session_deleted', auth()->user(), [
                'description' => 'Deleted live session: ' . $session->title,
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
}