@extends('layouts.admin')
@section('title', 'Activity Detail')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.activity-log') }}">Activity Log</a></div>
        <h1>Activity Detail</h1>
    </div>
</div>

<div class="container section">
<div style="max-width: 800px;">

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div>
                    <div class="text-muted text-small" style="margin-bottom: 0.2rem;">Action</div>
                    @php
                    $action = $activity->action;
                    $badgeClass = match(true) {
                        $action === 'user_login'              => 'badge-green',
                        $action === 'user_logout'             => 'badge-gray',
                        str_contains($action,'unauthorized')  => 'badge-red',
                        str_contains($action,'deleted')       => 'badge-red',
                        str_contains($action,'created')       => 'badge-blue',
                        str_contains($action,'approved')      => 'badge-green',
                        str_contains($action,'rejected')      => 'badge-red',
                        str_contains($action,'updated')       => 'badge-yellow',
                        default                               => 'badge-gray',
                    };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $action)) }}</span>
                </div>
                <div>
                    <div class="text-muted text-small" style="margin-bottom: 0.2rem;">Time</div>
                    <div style="font-weight: 500; font-size: 0.875rem;">{{ $activity->created_at->format('F j, Y @ g:i A') }}</div>
                </div>
                <div>
                    <div class="text-muted text-small" style="margin-bottom: 0.2rem;">User</div>
                    @if($activity->user)
                    <div style="font-weight: 500; font-size: 0.875rem;">
                        {{ $activity->user->first_name }} {{ $activity->user->last_name }}
                        <span class="text-muted text-small">({{ ucfirst($activity->user->role) }})</span>
                    </div>
                    @else
                    <span class="text-muted text-small">System</span>
                    @endif
                </div>
                <div>
                    <div class="text-muted text-small" style="margin-bottom: 0.2rem;">IP Address</div>
                    <code style="font-size: 0.8rem; background: var(--bg); padding: 0.15rem 0.4rem; border-radius: 3px;">
                        {{ $activity->ip_address ?? '—' }}
                    </code>
                </div>
                <div style="grid-column: span 2;">
                    <div class="text-muted text-small" style="margin-bottom: 0.2rem;">Description</div>
                    <div style="font-size: 0.875rem;">{{ $activity->description }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($activity->old_values || $activity->new_values)
    <div style="display: grid; grid-template-columns: {{ ($activity->old_values && $activity->new_values) ? '1fr 1fr' : '1fr' }}; gap: 1rem;">

        @if($activity->old_values)
        <div class="card">
            <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--error);">
                Before
            </div>
            <div class="card-body" style="padding: 1rem;">
                <pre style="font-size: 0.78rem; line-height: 1.6; overflow: auto; margin: 0; color: var(--text);">{{ json_encode($activity->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif

        @if($activity->new_values)
        <div class="card">
            <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--success);">
                After
            </div>
            <div class="card-body" style="padding: 1rem;">
                <pre style="font-size: 0.78rem; line-height: 1.6; overflow: auto; margin: 0; color: var(--text);">{{ json_encode($activity->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif

    </div>
    @else
    <div class="card card-body" style="color: var(--muted); text-align: center;">
        No before/after data recorded for this action.
    </div>
    @endif

    <div style="margin-top: 1.5rem;">
        <a href="{{ route('admin.activity-log') }}" class="btn btn-ghost">← Back to Log</a>
    </div>

</div>
</div>
@endsection