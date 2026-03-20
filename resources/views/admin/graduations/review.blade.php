@extends('layouts.admin')
@section('title', 'Review Graduation')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.graduations.index') }}">Graduations</a></div>
        <h1>{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</h1>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button onclick="openModal('approve-modal')" class="btn btn-primary">Approve</button>
        <button onclick="openModal('reject-modal')" class="btn btn-danger">Reject</button>
    </div>
</div>

<div class="container section">
<div style="display: grid; grid-template-columns: 1fr 280px; gap: 2rem; align-items: start;">

    {{-- Progress breakdown --}}
    <div>
        <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem; margin-bottom: 1rem;">Assessment Breakdown</h2>

        @forelse($assessmentBreakdown as $wp)
        <div class="card" style="margin-bottom: 0.6rem;">
            <div class="card-body" style="padding: 0.85rem 1.1rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em;">Week {{ $wp->moduleWeek->week_number }}</div>
                    <div style="font-weight: 500; font-size: 0.875rem;">{{ $wp->moduleWeek->title }}</div>
                </div>
                <div style="display: flex; gap: 1.5rem; text-align: center; flex-shrink: 0;">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ number_format($wp->assessment_score, 0) }}%</div>
                        <div class="text-muted text-small">Best score</div>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ $wp->assessment_attempts }}</div>
                        <div class="text-muted text-small">Attempts</div>
                    </div>
                    <div>
                        <span class="badge {{ $wp->assessment_passed ? 'badge-green' : 'badge-red' }}">
                            {{ $wp->assessment_passed ? 'Passed' : 'Failed' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card card-body" style="color: var(--muted); text-align: center;">No assessment data.</div>
        @endforelse
    </div>

    {{-- Summary card --}}
    <div>
        <div class="card card-body" style="margin-bottom: 1rem;">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif;">Eligibility</div>
            <div style="display: grid; gap: 0.75rem; font-size: 0.875rem;">
                @foreach([
                    'all_content_complete'    => 'All content completed',
                    'all_assessments_passed'  => 'All assessments passed',
                    'meets_grade_requirement' => 'Meets grade requirement',
                ] as $key => $label)
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ $label }}</span>
                    <span style="color: {{ $eligibility[$key] ? 'var(--success)' : 'var(--error)' }}; font-weight: 600;">
                        {{ $eligibility[$key] ? '✓' : '✗' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card card-body">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif;">Summary</div>
            <div style="display: grid; gap: 0.6rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Weeks completed</span>
                    <span>{{ $completedWeeks }} / {{ $totalWeeks }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Grade average</span>
                    <span style="font-weight: 600;">{{ $enrollment->final_grade_avg ? number_format($enrollment->final_grade_avg, 1) . '%' : '—' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Assessment avg</span>
                    <span>{{ $enrollment->weekly_assessment_avg ? number_format($enrollment->weekly_assessment_avg, 1) . '%' : '—' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Program</span>
                    <span>{{ $enrollment->program->name }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

{{-- Approve modal --}}
<div class="modal-overlay" id="approve-modal">
    <div class="modal" style="max-width: 440px;">
        <button class="modal-close" onclick="closeModal('approve-modal')">&#215;</button>
        <h2>Approve Graduation</h2>
        <p style="font-size: 0.875rem; color: var(--muted); margin-bottom: 1.25rem;">
            A certificate key will be generated for {{ $enrollment->user->first_name }}.
        </p>
        <form method="POST" action="{{ route('admin.graduations.approve', $enrollment->id) }}">
            @csrf
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Confirm Approval</button>
                <button type="button" onclick="closeModal('approve-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Reject modal --}}
<div class="modal-overlay" id="reject-modal">
    <div class="modal" style="max-width: 440px;">
        <button class="modal-close" onclick="closeModal('reject-modal')">&#215;</button>
        <h2>Reject Graduation</h2>
        <form method="POST" action="{{ route('admin.graduations.reject', $enrollment->id) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason <span style="color:var(--error)">*</span></label>
                <textarea name="reason" class="form-control" rows="4" required
                          placeholder="Explain why this request is being rejected..."></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-danger">Reject</button>
                <button type="button" onclick="closeModal('reject-modal')" class="btn btn-ghost">Cancel</button>
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
</script>
@endpush