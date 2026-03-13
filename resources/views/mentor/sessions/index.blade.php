@extends('mentor.layouts.app')
@section('title', 'Sessions')

@section('content')
<div class="page-header">
    <div><h1>Sessions</h1></div>
    <button onclick="openScheduleModal()" class="btn btn-primary">Schedule Session</button>
</div>

<div class="container section">
<div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem; align-items: start;">

    {{-- Calendar --}}
    <div class="card">
        <div class="card-body" style="padding: 1rem;">
            <div id="calendar" style="min-height: 500px;"></div>
        </div>
    </div>

    {{-- Upcoming list --}}
    <div>
        <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem; margin-bottom: 1rem;">Upcoming</h2>

        @forelse($upcoming as $session)
        <div style="border-bottom: 1px solid var(--border); padding: 0.9rem 0;">
            <div style="font-weight: 500; margin-bottom: 0.2rem;">{{ $session->title }}</div>
            <div class="text-muted text-small">
                {{ $session->start_time->format('D, M j · g:i A') }} — {{ $session->end_time->format('g:i A') }}
            </div>
            <div class="text-muted text-small">{{ $session->program->name }}</div>
            @if($session->meet_link)
            <a href="{{ $session->meet_link }}" target="_blank" class="text-small" style="color: var(--blue);">Join link</a>
            @endif
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <button onclick="openEditModal({{ json_encode($session) }})" class="btn btn-sm btn-ghost">Edit</button>
                <button onclick="cancelSession({{ $session->id }})" class="btn btn-sm btn-danger">Cancel</button>
            </div>
        </div>
        @empty
        <div class="text-muted text-small">No upcoming sessions scheduled.</div>
        @endforelse
    </div>

</div>
</div>

{{-- Schedule / Edit modal --}}
<div class="modal-overlay" id="session-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('session-modal')">&#215;</button>
        <h2 id="session-modal-title">Schedule a Session</h2>
        <form id="session-form" onsubmit="saveSession(event)">
            <input type="hidden" id="session-id">

            <div class="form-group">
                <label class="form-label">Program</label>
                <select id="session-program" class="form-control" required>
                    <option value="">Select program</option>
                    @foreach($programs as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" id="session-title" class="form-control" required placeholder="e.g. Week 3 Live Class">
            </div>

            <div class="form-group">
                <label class="form-label">Session Type</label>
                <select id="session-type" class="form-control">
                    <option value="live_class">Live Class</option>
                    <option value="workshop">Workshop</option>
                    <option value="q_and_a">Q &amp; A</option>
                </select>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Start</label>
                    <input type="datetime-local" id="session-start" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End</label>
                    <input type="datetime-local" id="session-end" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Google Meet Link</label>
                <input type="url" id="session-meet" class="form-control" placeholder="https://meet.google.com/...">
            </div>

            <div class="form-group">
                <label class="form-label">Notes (optional)</label>
                <textarea id="session-notes" class="form-control" rows="2" maxlength="500"></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Save Session</button>
                <button type="button" onclick="closeModal('session-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.css">
<style>
.fc-toolbar-title { font-family: 'Source Serif 4', serif; font-size: 1rem !important; }
.fc-button        { font-size: 0.8rem !important; text-transform: capitalize !important; }
.fc-button-primary { background: var(--blue) !important; border-color: var(--blue) !important; }
.fc-event         { font-size: 0.8rem; cursor: pointer; border: none !important; padding: 2px 4px; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); }));

function openScheduleModal() {
    document.getElementById('session-modal-title').textContent = 'Schedule a Session';
    document.getElementById('session-id').value = '';
    document.getElementById('session-form').reset();
    openModal('session-modal');
}

function openEditModal(s) {
    document.getElementById('session-modal-title').textContent = 'Edit Session';
    document.getElementById('session-id').value = s.id;
    document.getElementById('session-program').value = s.program_id;
    document.getElementById('session-title').value   = s.title;
    document.getElementById('session-type').value    = s.session_type;
    document.getElementById('session-start').value   = s.start_time.replace(' ', 'T').slice(0, 16);
    document.getElementById('session-end').value     = s.end_time.replace(' ', 'T').slice(0, 16);
    document.getElementById('session-meet').value    = s.meet_link || '';
    document.getElementById('session-notes').value   = s.notes || '';
    openModal('session-modal');
}

async function saveSession(e) {
    e.preventDefault();
    const id   = document.getElementById('session-id').value;
    const body = {
        program_id:   document.getElementById('session-program').value,
        title:        document.getElementById('session-title').value,
        session_type: document.getElementById('session-type').value,
        start_time:   document.getElementById('session-start').value,
        end_time:     document.getElementById('session-end').value,
        meet_link:    document.getElementById('session-meet').value,
        notes:        document.getElementById('session-notes').value,
    };
    if (id) {
        await api('PUT', `/mentor/sessions/${id}`, body);
    } else {
        await api('POST', '/mentor/sessions', body);
    }
    closeModal('session-modal');
    location.reload();
}

async function cancelSession(id) {
    if (!confirm('Cancel this session?')) return;
    await api('DELETE', `/mentor/sessions/${id}`);
    location.reload();
}

// FullCalendar
document.addEventListener('DOMContentLoaded', function () {
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        firstDay: 1,
        events: '/mentor/sessions/events',
        eventClick: function(info) {
            const p = info.event.extendedProps;
            // Simple info popup via native — keep it simple
            const msg = `${info.event.title}\n${info.event.startStr}\nProgram: ${p.program}${p.meet_link ? '\n' + p.meet_link : ''}`;
            alert(msg);
        },
        eventDidMount: function(info) {
            info.el.title = info.event.title;
        },
    });
    calendar.render();
});

async function api(method, url, body = null) {
    const res = await fetch(url, {
        method: ['DELETE','PUT'].includes(method) ? 'POST' : method,
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: body ? JSON.stringify({ ...body, _method: method }) : null,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) { alert(data.message || 'An error occurred.'); throw new Error(); }
    return data;
}
</script>
@endpush