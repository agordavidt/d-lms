<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use App\Models\Program;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    private function mentorPrograms()
    {
        return Program::where('mentor_id', auth()->id())->pluck('id');
    }

    public function index()
    {
        $programs = Program::where('mentor_id', auth()->id())
            ->whereIn('status', ['active', 'inactive'])
            ->orderBy('name')
            ->get();

        $upcoming = LiveSession::whereIn('program_id', $programs->pluck('id'))
            ->upcoming()
            ->with('program')
            ->orderBy('start_time')
            ->get();

        return view('mentor.sessions.index', compact('programs', 'upcoming'));
    }

    /** JSON events for FullCalendar */
    public function events(Request $request)
    {
        $sessions = LiveSession::whereIn('program_id', $this->mentorPrograms())
            ->with('program')
            ->when($request->start, fn ($q) => $q->where('start_time', '>=', $request->start))
            ->when($request->end,   fn ($q) => $q->where('start_time', '<=', $request->end))
            ->get()
            ->map(fn ($s) => [
                'id'              => $s->id,
                'title'           => $s->title,
                'start'           => $s->start_time->toIso8601String(),
                'end'             => $s->end_time->toIso8601String(),
                'backgroundColor' => match($s->session_type) {
                    'workshop'   => '#10b981',
                    'q_and_a'    => '#8b5cf6',
                    default      => '#0056d2',
                },
                'extendedProps'   => [
                    'program'   => $s->program->name,
                    'meet_link' => $s->meet_link,
                    'status'    => $s->status,
                ],
            ]);

        return response()->json($sessions);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'program_id'   => 'required|integer',
            'title'        => 'required|string|max:200',
            'session_type' => 'required|in:live_class,workshop,q_and_a',
            'start_time'   => 'required|date|after:now',
            'end_time'     => 'required|date|after:start_time',
            'meet_link'    => 'nullable|url',
            'notes'        => 'nullable|string|max:500',
        ]);

        // Ensure mentor owns this program
        $program = Program::where('id', $data['program_id'])
            ->where('mentor_id', auth()->id())
            ->firstOrFail();

        $data['mentor_id']         = auth()->id();
        $data['duration_minutes']  = (int) round(
            (strtotime($data['end_time']) - strtotime($data['start_time'])) / 60
        );

        $session = LiveSession::create($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'session' => $session->load('program')]);
        }

        return back()->with(['message' => 'Session scheduled.', 'alert-type' => 'success']);
    }

    public function update(Request $request, LiveSession $session)
    {
        abort_if(!in_array($session->program_id, $this->mentorPrograms()->toArray()), 403);

        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'start_time'  => 'required|date',
            'end_time'    => 'required|date|after:start_time',
            'meet_link'   => 'nullable|url',
            'notes'       => 'nullable|string|max:500',
            'status'      => 'in:scheduled,cancelled',
        ]);

        $data['duration_minutes'] = (int) round(
            (strtotime($data['end_time']) - strtotime($data['start_time'])) / 60
        );

        $session->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(LiveSession $session)
    {
        abort_if(!in_array($session->program_id, $this->mentorPrograms()->toArray()), 403);
        $session->delete();

        return response()->json(['success' => true]);
    }
}