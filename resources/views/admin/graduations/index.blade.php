@extends('layouts.app')
@section('title', 'Graduation Approvals')

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
        <h1>Graduation Approvals</h1>
    </div>
    <a href="{{ route('admin.graduations.graduated') }}" class="btn btn-ghost btn-sm">View Graduates</a>
</div>

{{-- Stats --}}
<div class="grad-stats" style="max-width:1100px;margin:0 auto 28px;padding:0 24px;">
    <div class="grad-stat-card">
        <div class="grad-stat-label">Pending Approval</div>
        <div class="grad-stat-value" style="color:#0056d2;">{{ $stats['pending_count'] }}</div>
    </div>
    <div class="grad-stat-card">
        <div class="grad-stat-label">Graduated This Month</div>
        <div class="grad-stat-value" style="color:#1a9048;">{{ $stats['graduated_this_month'] }}</div>
    </div>
    <div class="grad-stat-card">
        <div class="grad-stat-label">Avg Final Grade</div>
        <div class="grad-stat-value">{{ $stats['avg_grade'] }}%</div>
    </div>
</div>

<div class="grad-page" style="padding-top:0;">

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.graduations.index') }}"
          style="display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap;">
        <select name="program_id" class="form-control" style="width:220px;" onchange="this.form.submit()">
            <option value="">All Programs</option>
            @foreach($programs as $p)
            <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        @if(request('program_id'))
        <a href="{{ route('admin.graduations.index') }}" class="btn btn-ghost btn-sm">Clear</a>
        @endif
    </form>

    @if($pendingGraduations->isEmpty())
    <div class="card card-body" style="text-align:center;padding:56px;color:var(--muted);">
        <p style="font-size:1.1rem;font-weight:600;margin-bottom:8px;">No pending approvals</p>
        <p>Learners who pass the final examination and meet all requirements will appear here.</p>
    </div>
    @else

    {{-- Bulk approve --}}
    <form id="bulk-form" method="POST" action="{{ route('admin.graduations.bulk-approve') }}">
        @csrf
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:.875rem;color:var(--muted);cursor:pointer;">
                <input type="checkbox" id="select-all" style="width:16px;height:16px;" onchange="toggleAll(this)">
                Select all on this page
            </label>
            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Approve all selected?')">
                Grant Selected Certificates
            </button>
        </div>

        <table class="grant-table">
            <thead>
                <tr>
                    <th style="width:36px;"></th>
                    <th>Learner</th>
                    <th>Program</th>
                    <th>Final Exam Score</th>
                    <th>Course Average</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingGraduations as $enrollment)
                @php
                    $finalScore = null;
                    $finalAssessment = \App\Models\Assessment::whereHas('moduleWeek.programModule', fn($q) =>
                        $q->where('program_id', $enrollment->program_id)
                    )->where('is_final', true)->first();

                    if ($finalAssessment) {
                        $finalAttempt = $finalAssessment->attempts()
                            ->where('user_id', $enrollment->user_id)
                            ->where('passed', true)
                            ->latest()
                            ->first();
                        $finalScore = $finalAttempt?->percentage;
                    }
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}"
                               class="row-check" style="width:16px;height:16px;">
                    </td>
                    <td>
                        <div style="font-weight:600;">{{ $enrollment->user->full_name }}</div>
                        <div style="font-size:12px;color:var(--muted);">{{ $enrollment->user->email }}</div>
                    </td>
                    <td>
                        <div>{{ $enrollment->program->name }}</div>
                        <div style="font-size:12px;color:var(--muted);">{{ $enrollment->cohort->name ?? '' }}</div>
                    </td>
                    <td>
                        @if($finalScore !== null)
                        <span class="score-badge {{ $finalScore >= 80 ? 'high' : 'medium' }}">
                            {{ number_format($finalScore, 0) }}%
                        </span>
                        @else
                        <span style="font-size:12px;color:var(--muted);">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($enrollment->final_grade_avg)
                        <span class="score-badge {{ $enrollment->final_grade_avg >= 80 ? 'high' : 'medium' }}">
                            {{ number_format($enrollment->final_grade_avg, 1) }}%
                        </span>
                        @else
                        <span style="font-size:12px;color:var(--muted);">—</span>
                        @endif
                    </td>
                    <td style="font-size:13px;color:var(--muted);">
                        {{ $enrollment->graduation_requested_at?->format('M d, Y') ?? '—' }}
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;align-items:center;">
                            {{-- Quick grant --}}
                            <form method="POST" action="{{ route('admin.graduations.approve', $enrollment->id) }}"
                                  onsubmit="return confirm('Grant certificate to {{ addslashes($enrollment->user->full_name) }}?')">
                                @csrf
                                <button type="submit" class="btn-grant">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Grant
                                </button>
                            </form>
                            {{-- Reject --}}
                            <button onclick="openReject({{ $enrollment->id }}, '{{ addslashes($enrollment->user->full_name) }}')"
                                    class="btn btn-sm btn-ghost" style="color:var(--muted);">Reject</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </form>

    <div style="margin-top:16px;">
        {{ $pendingGraduations->appends(request()->query())->links() }}
    </div>
    @endif
</div>

{{-- Reject modal --}}
<div class="modal-overlay" id="reject-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('reject-modal')">&#215;</button>
        <h2>Reject Graduation Request</h2>
        <p class="text-muted text-small" id="reject-modal-name" style="margin-bottom:1rem;"></p>
        <form id="reject-form" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason <span class="text-muted">(required)</span></label>
                <textarea name="reason" class="form-control" rows="3" placeholder="Explain why the request is being rejected…" required maxlength="1000"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                <button type="button" onclick="closeModal('reject-modal')" class="btn btn-ghost btn-sm">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); }));

function openReject(enrollmentId, name) {
    document.getElementById('reject-modal-name').textContent = name;
    document.getElementById('reject-form').action = '/admin/graduations/' + enrollmentId + '/reject';
    openModal('reject-modal');
}

function toggleAll(master) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
}
</script>
@endpush