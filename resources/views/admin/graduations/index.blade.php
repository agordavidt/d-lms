{{-- resources/views/admin/graduations/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Graduations')

@section('content')
<div class="page-header">
    <div>
        <h1>Graduation Requests</h1>
    </div>
    <a href="{{ route('admin.graduations.graduated') }}" class="btn btn-ghost btn-sm">View Graduates</a>
</div>

<div class="container section">

    <div class="stats-row" style="grid-template-columns: repeat(3, 1fr); max-width: 500px;">
        <div class="stat-box {{ $stats['pending_count'] > 0 ? 'alert' : '' }}">
            <div class="stat-value">{{ $stats['pending_count'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['graduated_this_month'] }}</div>
            <div class="stat-label">Graduated this month</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['avg_grade'] > 0 ? $stats['avg_grade'] . '%' : '—' }}</div>
            <div class="stat-label">Average grade</div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem;">
        <select name="program_id" class="form-control" style="max-width: 260px;">
            <option value="">All Programs</option>
            @foreach($programs as $p)
            <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>

        @if($pendingGraduations->count() > 1)
        <form method="POST" action="{{ route('admin.graduations.bulk-approve') }}" id="bulk-form" style="margin: 0;">
            @csrf
            <input type="hidden" name="enrollment_ids" id="bulk-ids" value="">
            <button type="button" onclick="bulkApprove()" class="btn btn-primary btn-sm">Approve All Eligible</button>
        </form>
        @endif
    </form>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Learner</th>
                    <th>Program</th>
                    <th>Grade Avg</th>
                    <th>Requested</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingGraduations as $enrollment)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</div>
                        <div class="text-muted text-small">{{ $enrollment->user->email }}</div>
                    </td>
                    <td class="text-small">{{ $enrollment->program->name }}</td>
                    <td style="font-weight: 600; font-size: 0.875rem;">
                        {{ $enrollment->final_grade_avg ? number_format($enrollment->final_grade_avg, 1) . '%' : '—' }}
                    </td>
                    <td class="text-muted text-small">
                        {{ $enrollment->graduation_requested_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td>
                        <a href="{{ route('admin.graduations.review', $enrollment->id) }}" class="btn btn-sm btn-outline">Review</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted); padding: 2.5rem;">No pending graduation requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.25rem;">{{ $pendingGraduations->links() }}</div>

</div>
@endsection

@push('scripts')
<script>
function bulkApprove() {
    const ids = @json($pendingGraduations->pluck('id'));
    if (!confirm(`Approve all ${ids.length} eligible graduation requests?`)) return;
    document.getElementById('bulk-ids').value = JSON.stringify(ids);
    document.getElementById('bulk-form').submit();
}
</script>
@endpush