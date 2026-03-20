@extends('layouts.admin')
@section('title', 'Activity Log')

@section('content')
<div class="page-header">
    <div><h1>Activity Log</h1></div>
</div>

<div class="container section">

    {{-- Filters --}}
    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <input type="text" name="search" class="form-control" style="width: 220px;"
                   placeholder="Search description…" value="{{ request('search') }}">
        </div>
        <div>
            <select name="action" class="form-control" style="width: 200px;">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="user_id" class="form-control" style="width: 200px;">
                <option value="">All Users</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->first_name }} {{ $user->last_name }}
                </option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <input type="date" name="date_from" class="form-control" style="width: 145px;" value="{{ request('date_from') }}">
            <input type="date" name="date_to"   class="form-control" style="width: 145px;" value="{{ request('date_to') }}">
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        @if(request()->hasAny(['search','action','user_id','date_from','date_to']))
            <a href="{{ route('admin.activity-log') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 0.75rem;">
        {{ $activities->total() }} record{{ $activities->total() !== 1 ? 's' : '' }}
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 140px;">Time</th>
                    <th style="width: 180px;">User</th>
                    <th style="width: 160px;">Action</th>
                    <th>Description</th>
                    <th style="width: 120px;">IP</th>
                    <th style="width: 60px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                <tr>
                    <td>
                        <div style="font-weight: 500; font-size: 0.8rem;">{{ $activity->created_at->format('M j, Y') }}</div>
                        <div class="text-muted text-small">{{ $activity->created_at->format('g:i A') }}</div>
                    </td>
                    <td>
                        @if($activity->user)
                        <div style="font-weight: 500; font-size: 0.875rem;">
                            {{ $activity->user->first_name }} {{ $activity->user->last_name }}
                        </div>
                        <div class="text-muted text-small">{{ ucfirst($activity->user->role) }}</div>
                        @else
                        <span class="text-muted text-small">System</span>
                        @endif
                    </td>
                    <td>
                        @php
                        $action = $activity->action;
                        $badgeClass = match(true) {
                            $action === 'user_login'                  => 'badge-green',
                            $action === 'user_logout'                 => 'badge-gray',
                            str_contains($action, 'unauthorized')     => 'badge-red',
                            str_contains($action, 'deleted')          => 'badge-red',
                            str_contains($action, 'created')          => 'badge-blue',
                            str_contains($action, 'approved')         => 'badge-green',
                            str_contains($action, 'rejected')         => 'badge-red',
                            str_contains($action, 'updated')          => 'badge-yellow',
                            default                                   => 'badge-gray',
                        };
                        @endphp
                        <span class="badge {{ $badgeClass }}" style="font-size: 0.72rem; white-space: nowrap;">
                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                        </span>
                    </td>
                    <td style="font-size: 0.875rem; max-width: 320px;">
                        {{ Str::limit($activity->description, 80) }}
                    </td>
                    <td>
                        <code style="font-size: 0.72rem; background: var(--bg); padding: 0.15rem 0.35rem; border-radius: 3px;">
                            {{ $activity->ip_address ?? '—' }}
                        </code>
                    </td>
                    <td>
                        @if($activity->old_values || $activity->new_values)
                        <button onclick="openDetail({{ $activity->id }})" class="btn btn-sm btn-ghost" style="padding: 0.2rem 0.5rem;">
                            Detail
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 3rem;">
                        No activity found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div class="text-muted text-small">
            @if($activities->total() > 0)
                Showing {{ $activities->firstItem() }}–{{ $activities->lastItem() }} of {{ $activities->total() }}
            @endif
        </div>
        {{ $activities->withQueryString()->links() }}
    </div>

</div>

{{-- Detail modal --}}
<div class="modal-overlay" id="detail-modal">
    <div class="modal" style="max-width: 680px;">
        <button class="modal-close" onclick="closeDetail()">&#215;</button>
        <h2 id="detail-title" style="margin-bottom: 0.5rem;">Activity Detail</h2>
        <p id="detail-desc" class="text-muted text-small" style="margin-bottom: 1.5rem;"></p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;" id="detail-values">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.8rem; color: var(--muted); border-top: 1px solid var(--border); padding-top: 1rem;">
            <div><strong style="color: var(--text);">IP Address</strong><br><span id="detail-ip"></span></div>
            <div><strong style="color: var(--text);">Timestamp</strong><br><span id="detail-time"></span></div>
        </div>
    </div>
</div>

{{-- Embed activity data for JS --}}
<script>
const ACTIVITY_DATA = {
    @foreach($activities as $activity)
    {{ $activity->id }}: {
        description: @json($activity->description),
        old_values:  @json($activity->old_values),
        new_values:  @json($activity->new_values),
        ip_address:  @json($activity->ip_address),
        created_at:  @json($activity->created_at->format('F j, Y @ g:i A')),
    },
    @endforeach
};

function openDetail(id) {
    const d = ACTIVITY_DATA[id];
    if (!d) return;

    document.getElementById('detail-title').textContent   = 'Activity Detail';
    document.getElementById('detail-desc').textContent    = d.description || '';
    document.getElementById('detail-ip').textContent      = d.ip_address  || '—';
    document.getElementById('detail-time').textContent    = d.created_at  || '—';

    let valuesHtml = '';
    if (d.old_values) {
        valuesHtml += `
            <div>
                <div style="font-size:0.75rem;font-weight:600;color:var(--error);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:0.4rem;">Before</div>
                <pre style="background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:0.75rem;font-size:0.75rem;overflow:auto;max-height:220px;margin:0;">${JSON.stringify(d.old_values, null, 2)}</pre>
            </div>`;
    }
    if (d.new_values) {
        valuesHtml += `
            <div>
                <div style="font-size:0.75rem;font-weight:600;color:var(--success);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:0.4rem;">After</div>
                <pre style="background:var(--bg);border:1px solid var(--border);border-radius:6px;padding:0.75rem;font-size:0.75rem;overflow:auto;max-height:220px;margin:0;">${JSON.stringify(d.new_values, null, 2)}</pre>
            </div>`;
    }
    // If only one panel, span full width
    if ((d.old_values && !d.new_values) || (!d.old_values && d.new_values)) {
        document.getElementById('detail-values').style.gridTemplateColumns = '1fr';
    } else {
        document.getElementById('detail-values').style.gridTemplateColumns = '1fr 1fr';
    }
    document.getElementById('detail-values').innerHTML = valuesHtml;

    document.getElementById('detail-modal').classList.add('open');
}

function closeDetail() {
    document.getElementById('detail-modal').classList.remove('open');
}

document.getElementById('detail-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDetail();
});
</script>

@endsection