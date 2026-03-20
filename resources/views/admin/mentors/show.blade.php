@extends('layouts.admin')
@section('title', $mentor->first_name . ' ' . $mentor->last_name)

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.mentors.index') }}">Mentors</a></div>
        <h1>{{ $mentor->first_name }} {{ $mentor->last_name }}</h1>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.mentors.edit', $mentor->id) }}" class="btn btn-ghost btn-sm">Edit</a>
        @if($mentor->status === 'active')
            <button onclick="setStatus('inactive')" class="btn btn-ghost btn-sm">Deactivate</button>
        @else
            <button onclick="setStatus('active')" class="btn btn-outline btn-sm">Activate</button>
        @endif
    </div>
</div>

{{-- Stats bar --}}
<div style="background: var(--white); border-bottom: 1px solid var(--border); padding: 0 2rem;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; gap: 2.5rem; padding: 0.9rem 0;">
        @foreach(['programs' => 'Programs', 'active' => 'Live', 'under_review' => 'Under Review', 'drafts' => 'Drafts', 'learners' => 'Learners'] as $key => $label)
        <div style="text-align: center;">
            <div style="font-weight: 600; font-size: 1.1rem;">{{ $stats[$key] }}</div>
            <div class="text-muted text-small">{{ $label }}</div>
        </div>
        @endforeach
        <div style="text-align: center;">
            <div style="font-weight: 600; font-size: 1.1rem;">
                <span class="badge {{ $mentor->status === 'active' ? 'badge-green' : 'badge-gray' }}">
                    {{ ucfirst($mentor->status) }}
                </span>
            </div>
            <div class="text-muted text-small">Account</div>
        </div>
    </div>
</div>

<div class="container section">

    {{-- Tab navigation --}}
    @php $tab = request('tab', 'overview'); @endphp
    <div class="tabs">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'overview']) }}"
           class="tab-link {{ $tab === 'overview' ? 'active' : '' }}">Overview</a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'programs']) }}"
           class="tab-link {{ $tab === 'programs' ? 'active' : '' }}">
            Programs ({{ $programs->count() }})
        </a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'learners']) }}"
           class="tab-link {{ $tab === 'learners' ? 'active' : '' }}">
            Learners ({{ $enrollments->count() }})
        </a>
    </div>

    {{-- ════ OVERVIEW ════ --}}
    @if($tab === 'overview')
    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem; align-items: start;">
        <div>
            <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem; margin-bottom: 1rem;">Program Breakdown</h2>

            @forelse($programs->take(5) as $program)
            <div class="card" style="margin-bottom: 0.6rem;">
                <div class="card-body" style="padding: 0.9rem 1.1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div>
                        <div style="font-weight: 500; font-size: 0.875rem;">{{ $program->name }}</div>
                        <div class="text-muted text-small">{{ $program->enrollments_count }} learner{{ $program->enrollments_count !== 1 ? 's' : '' }} · {{ $program->duration }}</div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span class="badge {{ match($program->status) {
                            'active'       => 'badge-green',
                            'under_review' => 'badge-yellow',
                            'inactive'     => 'badge-gray',
                            default        => 'badge-gray',
                        } }}">{{ match($program->status) { 'active' => 'Live', 'under_review' => 'Review', 'inactive' => 'Offline', default => 'Draft' } }}</span>
                        <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-sm btn-ghost">View</a>
                    </div>
                </div>
            </div>
            @empty
            <div class="card card-body" style="color: var(--muted); text-align: center;">No programs yet.</div>
            @endforelse

            @if($programs->count() > 5)
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'programs']) }}" class="text-small" style="color: var(--blue);">View all {{ $programs->count() }} programs</a>
            @endif
        </div>

        <div>
            <div class="card card-body">
                <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif;">Contact</div>
                <div class="text-small">{{ $mentor->email }}</div>
                @if($mentor->phone)
                <div class="text-small" style="margin-top: 0.25rem;">{{ $mentor->phone }}</div>
                @endif
                <div class="text-muted text-small" style="margin-top: 0.5rem;">Joined {{ $mentor->created_at->format('M j, Y') }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ════ PROGRAMS ════ --}}
    @if($tab === 'programs')
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Learners</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $program->name }}</div>
                        <div class="text-muted text-small">{{ $program->duration }}</div>
                    </td>
                    <td>
                        <span class="badge {{ match($program->status) {
                            'active'       => 'badge-green',
                            'under_review' => 'badge-yellow',
                            'inactive'     => 'badge-gray',
                            default        => 'badge-gray',
                        } }}">{{ match($program->status) { 'active' => 'Live', 'under_review' => 'Under Review', 'inactive' => 'Offline', default => 'Draft' } }}</span>
                    </td>
                    <td class="text-small">{{ $program->enrollments_count }}</td>
                    <td class="text-muted text-small">{{ $program->submitted_at?->format('M j, Y') ?? '—' }}</td>
                    <td><a href="{{ route('admin.programs.show', $program) }}" class="btn btn-sm btn-ghost">View</a></td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted); padding: 2rem;">No programs.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════ LEARNERS ════ --}}
    @if($tab === 'learners')
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Learner</th>
                    <th>Program</th>
                    <th>Progress</th>
                    <th>Enrolled</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enrollment)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</div>
                        <div class="text-muted text-small">{{ $enrollment->user->email }}</div>
                    </td>
                    <td class="text-muted text-small">{{ $enrollment->program->name }}</td>
                    <td style="min-width: 140px;">
                        <div class="progress-bar-track" style="margin-bottom: 0.2rem;">
                            <div class="progress-bar-fill" style="width: {{ $enrollment->progress_percentage }}%"></div>
                        </div>
                        <span class="text-muted text-small">{{ number_format($enrollment->progress_percentage, 0) }}%</span>
                    </td>
                    <td class="text-muted text-small">{{ \Carbon\Carbon::parse($enrollment->enrolled_at)->format('M j, Y') }}</td>
                    <td><a href="{{ route('admin.learners.show', $enrollment->user_id) }}" class="btn btn-sm btn-ghost">View</a></td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted); padding: 2rem;">No learners enrolled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
async function setStatus(status) {
    if (!confirm(`Set mentor status to "${status}"?`)) return;
    const res = await fetch(`/admin/mentors/{{ $mentor->id }}/status`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
        body: JSON.stringify({ status }),
    });
    if (res.ok) location.reload();
}
</script>
@endpush