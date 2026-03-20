@extends('layouts.admin')
@section('title', 'Learners')

@section('content')
<div class="page-header">
    <div><h1>Learners</h1></div>
</div>

<div class="container section">

    {{-- Filters --}}
    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <input type="text" name="search" class="form-control" style="width: 240px;"
                   placeholder="Name or email…" value="{{ request('search') }}">
        </div>
        <div>
            <select name="program_id" class="form-control" style="width: 220px;">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="status" class="form-control" style="width: 140px;">
                <option value="">Any Status</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        @if(request()->hasAny(['search', 'program_id', 'status']))
            <a href="{{ route('admin.learners.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    {{-- Results count --}}
    <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 0.75rem;">
        {{ $learners->total() }} learner{{ $learners->total() !== 1 ? 's' : '' }}
        @if(request()->hasAny(['search','program_id','status'])) matching filters @endif
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Learner</th>
                    <th>Enrollment(s)</th>
                    <th>Progress</th>
                    <th>Joined</th>
                    <th>Account</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($learners as $learner)
                @php
                    $activeEnrollment = $learner->enrollments->first();
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $learner->first_name }} {{ $learner->last_name }}</div>
                        <div class="text-muted text-small">{{ $learner->email }}</div>
                        @if($learner->phone)
                        <div class="text-muted text-small">{{ $learner->phone }}</div>
                        @endif
                    </td>
                    <td>
                        @if($learner->enrollments->isNotEmpty())
                            @foreach($learner->enrollments->take(2) as $enrollment)
                            <div style="font-size: 0.8rem; margin-bottom: 0.15rem;">
                                {{ $enrollment->program->name ?? '—' }}
                                <span class="badge {{ $enrollment->status === 'active' ? 'badge-green' : 'badge-gray' }}" style="font-size: 0.65rem; padding: 0.1rem 0.4rem;">
                                    {{ $enrollment->status }}
                                </span>
                            </div>
                            @endforeach
                            @if($learner->enrollments->count() > 2)
                            <div class="text-muted text-small">+{{ $learner->enrollments->count() - 2 }} more</div>
                            @endif
                        @else
                            <span class="text-muted text-small">Not enrolled</span>
                        @endif
                    </td>
                    <td style="min-width: 120px;">
                        @if($activeEnrollment)
                        <div class="progress-bar-track" style="margin-bottom: 0.25rem;">
                            <div class="progress-bar-fill" style="width: {{ $activeEnrollment->progress_percentage }}%"></div>
                        </div>
                        <span class="text-muted text-small">{{ number_format($activeEnrollment->progress_percentage, 0) }}%</span>
                        @else
                        <span class="text-muted text-small">—</span>
                        @endif
                    </td>
                    <td class="text-muted text-small">{{ $learner->created_at->format('M j, Y') }}</td>
                    <td>
                        <span class="badge {{ match($learner->status) {
                            'active'    => 'badge-green',
                            'suspended' => 'badge-red',
                            default     => 'badge-gray',
                        } }}">{{ ucfirst($learner->status) }}</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.35rem;">
                            <a href="{{ route('admin.learners.show', $learner->id) }}" class="btn btn-sm btn-ghost">View</a>
                            <button onclick="toggleStatus({{ $learner->id }}, '{{ $learner->status }}')"
                                    class="btn btn-sm btn-ghost" style="color: var(--muted);">
                                {{ $learner->status === 'active' ? 'Suspend' : 'Activate' }}
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 3rem;">
                        No learners found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div class="text-muted text-small">
            Showing {{ $learners->firstItem() }}–{{ $learners->lastItem() }} of {{ $learners->total() }}
        </div>
        {{ $learners->withQueryString()->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
async function toggleStatus(id, current) {
    const next   = current === 'active' ? 'suspended' : 'active';
    const label  = current === 'active' ? 'suspend' : 'activate';
    if (!confirm(`${label.charAt(0).toUpperCase() + label.slice(1)} this learner?`)) return;

    const res = await fetch(`/admin/learners/${id}/status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: next }),
    });

    if (res.ok) location.reload();
    else alert('Failed to update status.');
}
</script>
@endpush