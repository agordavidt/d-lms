@extends('layouts.admin')
@section('title', 'Sessions')

@section('content')
<div class="page-header">
    <div><h1>Sessions</h1></div>
    <button onclick="openModal('session-modal')" class="btn btn-primary">Schedule Session</button>
</div>

<div class="container section">
<div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem; align-items: start;">

    {{-- Calendar --}}
    <div class="card">
        <div class="card-body" style="padding: 1rem;">
            {{-- Filter bar --}}
            <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center;">
                <select id="filter-program" class="form-control" style="max-width: 240px;">
                    <option value="">All Programs</option>
                    @foreach($programs as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <button onclick="refreshCalendar()" class="btn btn-ghost btn-sm">Filter</button>
            </div>

            <div id="calendar" style="min-height: 540px;"></div>

            {{-- Legend --}}
            <div style="display: flex; gap: 1.25rem; margin-top: 1rem; font-size: 0.78rem; color: var(--muted);">
                <span><span style="display:inline-block;width:10px;height:10px;background:#1a1a2e;border-radius:2px;margin-right:4px;"></span>Admin</span>
                <span><span style="display:inline-block;width:10px;height:10px;background:#0056d2;border-radius:2px;margin-right:4px;"></span>Live Class</span>
                <span><span style="display:inline-block;width:10px;height:10px;background:#10b981;border-radius:2px;margin-right:4px;"></span>Workshop</span>
                <span><span style="display:inline-block;width:10px;height:10px;background:#8b5cf6;border-radius:2px;margin-right:4px;"></span>Q&amp;A</span>
            </div>
        </div>
    </div>

    {{-- Upcoming sidebar --}}
    <div>
        <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem; margin-bottom: 1rem;">Upcoming</h2>

        @forelse($upcoming as $session)
        <div style="border-bottom: 1px solid var(--border); padding: 0.9rem 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                <div style="min-width: 0;">
                    <div style="font-weight: 500; font-size: 0.875rem;">{{ $session->title }}</div>
                    <div class="text-muted text-small">{{ $session->start_time->format('D, M j · g:i A') }}</div>
                    <div class="text-muted text-small">
                        {{ $session->program?->name ?? 'Platform-wide' }}
                        · {{ $session->mentor ? $session->mentor->first_name : 'Admin' }}
                    </div>
                    @if($session->meet_link)
                    <a href="{{ $session->meet_link }}" target="_blank" class="text-small" style="color: var(--blue);">Join link</a>
                    @endif
                </div>
                @if(!$session->mentor_id)
                {{-- Only admin sessions are editable --}}
                <div style="display: flex; gap: 0.35rem; flex-shrink: 0;">
                    <button onclick="openEditModal({{ json_encode($session) }})" class="btn btn-sm btn-ghost">Edit</button>
                    <button onclick="deleteSession({{ $session->id }})" class="btn btn-sm btn-danger">Cancel</button>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="text-muted text-small" style="padding: 1rem 0;">No upcoming sessions.</div>
        @endforelse
    </div>

</div>
</div>

{{-- Schedule modal --}}
<div class="modal-overlay" id="session-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('session-modal')">&#215;</button>
        <h2 id="session-modal-title">Schedule Session</h2>
        <form id="session-form" onsubmit="saveSession(event)">
            <input type="hidden" id="session-id">

            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" id="session-title" class="form-control" required placeholder="e.g. Monthly Orientation">
            </div>

            <div class="form-group">
                <label class="form-label">Type</label>
                <select id="session-type" class="form-control">
                    <option value="live_class">Live Class</option>
                    <option value="workshop">Workshop</option>
                    <option value="q_and_a">Q &amp; A</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Program (optional — leave blank for platform-wide)</label>
                <select id="session-program" class="form-control">
                    <option value="">Platform-wide</option>
                    @foreach($programs as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
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
                <label class="form-label">Meet Link</label>
                <input type="url" id="session-meet" class="form-control" placeholder="https://meet.google.com/...">
            </div>

            <div class="form-group">
                <label class="form-label">Notes (optional)</label>
                <textarea id="session-notes" class="form-control" rows="2" maxlength="500"></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Save</button>
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
.fc-event         { font-size: 0.78rem; cursor: pointer; border: none !important; padding: 2px 5px; }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/index.global.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let calendar;

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); }));

function openEditModal(s) {
    document.getElementById('session-modal-title').textContent = 'Edit Session';
    document.getElementById('session-id').value    = s.id;
    document.getElementById('session-title').value = s.title;
    document.getElementById('session-type').value  = s.session_type;
    document.getElementById('session-program').value = s.program_id || '';
    document.getElementById('session-start').value = s.start_time.replace(' ', 'T').slice(0, 16);
    document.getElementById('session-end').value   = s.end_time.replace(' ', 'T').slice(0, 16);
    document.getElementById('session-meet').value  = s.meet_link || '';
    document.getElementById('session-notes').value = s.notes || '';
    openModal('session-modal');
}

function resetModal() {
    document.getElementById('session-modal-title').textContent = 'Schedule Session';
    document.getElementById('session-id').value = '';
    document.getElementById('session-form').reset();
}
document.querySelector('[onclick="openModal(\'session-modal\')"]')
    ?.addEventListener('click', resetModal);

async function saveSession(e) {
    e.preventDefault();
    const id   = document.getElementById('session-id').value;
    const body = {
        title:        document.getElementById('session-title').value,
        session_type: document.getElementById('session-type').value,
        program_id:   document.getElementById('session-program').value || null,
        start_time:   document.getElementById('session-start').value,
        end_time:     document.getElementById('session-end').value,
        meet_link:    document.getElementById('session-meet').value,
        notes:        document.getElementById('session-notes').value,
    };

    const url    = id ? `/admin/sessions/${id}` : '/admin/sessions';
    const method = id ? 'PUT' : 'POST';
    await api(method, url, body);
    closeModal('session-modal');
    location.reload();
}

async function deleteSession(id) {
    if (!confirm('Cancel this session?')) return;
    await api('DELETE', `/admin/sessions/${id}`);
    location.reload();
}

function refreshCalendar() {
    if (calendar) calendar.refetchEvents();
}

document.addEventListener('DOMContentLoaded', function () {
    calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        firstDay: 1,
        events: function(fetchInfo, success) {
            const programId = document.getElementById('filter-program').value;
            const url = `/admin/sessions/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}${programId ? '&program_id=' + programId : ''}`;
            fetch(url).then(r => r.json()).then(success);
        },
        eventClick: function(info) {
            const p = info.event.extendedProps;
            const owned = p.is_admin;
            const msg = [
                info.event.title,
                info.event.startStr.slice(0, 16).replace('T', ' '),
                `Program: ${p.program}`,
                `By: ${p.mentor}`,
                p.meet_link ? `Link: ${p.meet_link}` : '',
                owned ? '\n(Click Edit to modify)' : '\n(Mentor session — read only)',
            ].filter(Boolean).join('\n');
            alert(msg);
        },
    });
    calendar.render();
});

async function api(method, url, body) {
    const res = await fetch(url, {
        method: ['DELETE','PUT'].includes(method) ? 'POST' : method,
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ ...body, _method: method }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) { alert(data.message || 'An error occurred.'); throw new Error(); }
    return data;
}
</script>
@endpush