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
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:0 2rem;">
    <div style="max-width:1100px;margin:0 auto;display:flex;gap:2.5rem;padding:.85rem 0;">
        <div style="text-align:center;">
            <div style="font-weight:700;font-size:1.4rem;color:#0056d2;">{{ $stats['pending_count'] }}</div>
            <div class="text-muted text-small">Pending Approval</div>
        </div>
        <div style="text-align:center;">
            <div style="font-weight:700;font-size:1.4rem;color:var(--success);">{{ $stats['graduated_this_month'] }}</div>
            <div class="text-muted text-small">Graduated This Month</div>
        </div>
    </div>
</div>

<div class="container section" style="padding-top:1.5rem;">

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.graduations.index') }}"
          style="display:flex;gap:.75rem;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;">
        <select name="program_id" class="form-control" style="width:240px;" onchange="this.form.submit()">
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
    <div class="card card-body" style="text-align:center;padding:3.5rem;color:var(--muted);">
        <p style="font-size:1rem;font-weight:600;margin-bottom:.5rem;">No pending approvals</p>
        <p style="font-size:.875rem;">Learners who pass the final examination will appear here for certificate issuance.</p>
    </div>
    @else

    {{-- Bulk approve --}}
    <form id="bulk-form" method="POST" action="{{ route('admin.graduations.bulk-approve') }}">
        @csrf
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
            <label style="display:flex;align-items:center;gap:8px;font-size:.875rem;color:var(--muted);cursor:pointer;">
                <input type="checkbox" id="select-all" style="width:16px;height:16px;" onchange="toggleAll(this)">
                Select all on this page
            </label>
            <button type="submit" class="btn btn-primary btn-sm"
                    onclick="return confirm('Grant certificates to all selected learners?')">
                Grant Selected Certificates
            </button>
        </div>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:36px;"></th>
                        <th>Learner</th>
                        <th>Program</th>
                        <th>Final Exam Score</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingGraduations as $enrollment)
                    <tr>
                        <td>
                            <input type="checkbox" name="enrollment_ids[]" value="{{ $enrollment->id }}"
                                   class="row-check" style="width:16px;height:16px;">
                        </td>
                        <td>
                            <div style="font-weight:600;">{{ $enrollment->user->full_name }}</div>
                            <div class="text-muted text-small">{{ $enrollment->user->email }}</div>
                        </td>
                        <td>
                            <div>{{ $enrollment->program->name }}</div>
                            <div class="text-muted text-small">{{ $enrollment->cohort->name ?? '' }}</div>
                        </td>
                        <td>
                            {{-- final_exam_score is stored on enrollment after recordFinalExamPass() --}}
                            @if($enrollment->final_exam_score !== null)
                            <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:.78rem;font-weight:700;
                                         background:{{ $enrollment->final_exam_score >= 80 ? '#f0fdf4' : '#fffbeb' }};
                                         color:{{ $enrollment->final_exam_score >= 80 ? '#166534' : '#92400e' }};">
                                {{ number_format($enrollment->final_exam_score, 0) }}%
                            </span>
                            @else
                            <span class="text-muted text-small">—</span>
                            @endif
                        </td>
                        <td class="text-muted text-small">
                            {{ $enrollment->graduation_requested_at?->format('M d, Y') ?? '—' }}
                        </td>
                        <td>
                            <div style="display:flex;gap:.5rem;align-items:center;">
                                <a href="{{ route('admin.graduations.review', $enrollment->id) }}"
                                   class="btn btn-sm btn-ghost">Review</a>
                                <form method="POST"
                                      action="{{ route('admin.graduations.approve', $enrollment->id) }}"
                                      onsubmit="return confirm('Grant certificate to {{ addslashes($enrollment->user->full_name) }}?')"
                                      style="display:inline;">
                                    @csrf
                                    <button type="submit"
                                            style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:6px;border:none;background:#f0fdf4;color:#166534;font-size:.78rem;font-weight:700;cursor:pointer;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        Grant
                                    </button>
                                </form>
                                <button onclick="openReject({{ $enrollment->id }}, '{{ addslashes($enrollment->user->full_name) }}')"
                                        class="btn btn-sm btn-ghost" style="color:var(--muted);">Reject</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>

    <div style="margin-top:1.25rem;">
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
                <textarea name="reason" class="form-control" rows="3" required maxlength="1000"
                          placeholder="Explain why this request is being rejected…"></textarea>
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