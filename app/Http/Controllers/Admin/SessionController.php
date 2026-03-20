<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LiveSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Unified calendar page — shows all sessions across the platform.
     * Admin can create their own platform-wide or program-specific sessions.
     * Mentor sessions are visible but not editable by admin.
     */
    public function index()
    {
        $programs = Program::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $mentors  = User::where('role', 'mentor')->where('status', 'active')
                        ->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        // Upcoming list for the sidebar
        $upcoming = LiveSession::with(['program', 'mentor'])
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->take(20)
            ->get();

        return view('admin.sessions.index', compact('programs', 'mentors', 'upcoming'));
    }

    /**
     * JSON feed for FullCalendar — all sessions on the platform.
     */
    public function events(Request $request)
    {
        $query = LiveSession::with(['program', 'mentor'])
            ->when($request->start, fn ($q) => $q->where('start_time', '>=', $request->start))
            ->when($request->end,   fn ($q) => $q->where('start_time', '<=', $request->end))
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id));

        $events = $query->get()->map(fn ($s) => [
            'id'              => $s->id,
            'title'           => $s->title,
            'start'           => $s->start_time->toIso8601String(),
            'end'             => $s->end_time->toIso8601String(),
            'backgroundColor' => $s->mentor_id === null ? '#1a1a2e' : match($s->session_type) {
                'workshop' => '#10b981',
                'q_and_a'  => '#8b5cf6',
                default    => '#0056d2',
            },
            'borderColor'     => 'transparent',
            'extendedProps'   => [
                'program'     => $s->program?->name ?? 'Platform-wide',
                'mentor'      => $s->mentor
                                    ? $s->mentor->first_name . ' ' . $s->mentor->last_name
                                    : 'Admin',
                'meet_link'   => $s->meet_link,
                'status'      => $s->status,
                'is_admin'    => $s->mentor_id === null, // admin-created session
                'session_id'  => $s->id,
            ],
        ]);

        return response()->json($events);
    }

    /**
     * Create an admin session (platform-wide or program-specific).
     * mentor_id is left null — distinguishes admin sessions from mentor sessions.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'session_type' => 'required|in:live_class,workshop,q_and_a',
            'program_id'   => 'nullable|exists:programs,id',
            'start_time'   => 'required|date|after:now',
            'end_time'     => 'required|date|after:start_time',
            'meet_link'    => 'nullable|url',
            'notes'        => 'nullable|string|max:500',
        ]);

        $data['mentor_id']        = null;   // admin-owned session
        $data['duration_minutes'] = (int) round(
            (strtotime($data['end_time']) - strtotime($data['start_time'])) / 60
        );

        $session = LiveSession::create($data);

        AuditLog::log('session_created', auth()->user(), [
            'description' => 'Admin scheduled session: ' . $session->title,
            'model_type'  => LiveSession::class,
            'model_id'    => $session->id,
        ]);

        return response()->json(['success' => true, 'session' => $session]);
    }

    /**
     * Admin can only edit admin-created sessions (mentor_id = null).
     */
    public function update(Request $request, LiveSession $session)
    {
        abort_if($session->mentor_id !== null, 403, 'Mentor sessions cannot be edited by admin.');

        $data = $request->validate([
            'title'      => 'required|string|max:200',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
            'meet_link'  => 'nullable|url',
            'notes'      => 'nullable|string|max:500',
            'status'     => 'in:scheduled,cancelled',
        ]);

        $data['duration_minutes'] = (int) round(
            (strtotime($data['end_time']) - strtotime($data['start_time'])) / 60
        );

        $session->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(LiveSession $session)
    {
        abort_if($session->mentor_id !== null, 403, 'Mentor sessions cannot be deleted by admin.');

        AuditLog::log('session_deleted', auth()->user(), [
            'description' => 'Admin deleted session: ' . $session->title,
            'model_type'  => LiveSession::class,
            'model_id'    => $session->id,
        ]);

        $session->delete();

        return response()->json(['success' => true]);
    }
}