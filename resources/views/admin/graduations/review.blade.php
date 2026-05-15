@extends('layouts.app')
@section('title', 'Review — ' . $enrollment->user->full_name)

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('admin.graduations.index') }}">Graduation Approvals</a>
        </div>
        <h1>{{ $enrollment->user->full_name }}</h1>
        <p class="text-muted text-small">{{ $enrollment->program->name }} · {{ $enrollment->enrollment_number }}</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <form method="POST" action="{{ route('admin.graduations.approve', $enrollment->id) }}"
              onsubmit="return confirm('Grant certificate to {{ addslashes($enrollment->user->full_name) }}?')">
            @csrf
            <button type="submit" class="btn-grant" style="font-size:13px;padding:9px 20px;">
                Grant Certificate
            </button>
        </form>
        <button onclick="openModal('reject-modal')" class="btn btn-ghost btn-sm">Reject</button>
    </div>
</div>

<div style="max-width:900px;margin:0 auto;padding:24px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">

        {{-- Eligibility checklist --}}
        <div class="card">
            <div class="card-body">
                <p class="text-muted text-small" style="font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Eligibility</p>
                @foreach([
                    ['ok' => $eligibility['all_content_complete'],    'label' => 'All content completed'],
                    ['ok' => $eligibility['all_assessments_passed'],  'label' => 'All assessments passed'],
                    ['ok' => $eligibility['meets_grade_requirement'], 'label' => 'Grade requirement met'],
                ] as $item)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border-light);">
                    <span style="color:{{ $item['ok'] ? 'var(--success)' : 'var(--danger)' }};font-size:1rem;">
                        {{ $item['ok'] ? '✓' : '✗' }}
                    </span>
                    <span style="font-size:.875rem;">{{ $item['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Grade summary --}}
        <div class="card">
            <div class="card-body">
                <p class="text-muted text-small" style="font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Grade Summary</p>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:.875rem;">
                    <span class="text-muted">Weekly average</span>
                    <strong>{{ $enrollment->weekly_assessment_avg ? number_format($enrollment->weekly_assessment_avg, 1) . '%' : '—' }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:.875rem;">
                    <span class="text-muted">Final grade avg</span>
                    <strong>{{ $enrollment->final_grade_avg ? number_format($enrollment->final_grade_avg, 1) . '%' : '—' }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:.875rem;">
                    <span class="text-muted">Weeks completed</span>
                    <strong>{{ $completedWeeks }} / {{ $totalWeeks }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Final exam attempts --}}
    @if($finalExamAttempts && $finalExamAttempts->count())
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <p class="text-muted text-small" style="font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Final Examination Attempts</p>
            <table style="width:100%;font-size:.875rem;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);">
                        <th style="text-align:left;padding:6px 8px;color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;">Attempt</th>
                        <th style="text-align:left;padding:6px 8px;color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;">Score</th>
                        <th style="text-align:left;padding:6px 8px;color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;">Result</th>
                        <th style="text-align:left;padding:6px 8px;color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($finalExamAttempts as $attempt)
                    <tr style="border-bottom:1px solid var(--border-light);">
                        <td style="padding:8px;">#{{ $attempt->attempt_number }}</td>
                        <td style="padding:8px;">{{ number_format($attempt->percentage, 1) }}%</td>
                        <td style="padding:8px;">
                            <span style="font-size:12px;font-weight:700;padding:2px 8px;border-radius:999px;background:{{ $attempt->passed ? 'var(--success-bg)' : '#fef2f2' }};color:{{ $attempt->passed ? 'var(--success)' : '#dc2626' }};">
                                {{ $attempt->passed ? 'Passed' : 'Failed' }}
                            </span>
                        </td>
                        <td style="padding:8px;color:var(--muted);">{{ $attempt->submitted_at?->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Assessment breakdown --}}
    @if($assessmentBreakdown->count())
    <div class="card">
        <div class="card-body">
            <p class="text-muted text-small" style="font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Weekly Assessment Scores</p>
            @foreach($assessmentBreakdown as $wp)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border-light);font-size:.875rem;">
                <span>Week {{ $wp->moduleWeek->week_number }}: {{ $wp->moduleWeek->title }}</span>
                <span class="score-badge {{ ($wp->assessment_score ?? 0) >= 80 ? 'high' : 'medium' }}">
                    {{ number_format($wp->assessment_score, 0) }}%
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Reject modal --}}
<div class="modal-overlay" id="reject-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('reject-modal')">&#215;</button>
        <h2>Reject Graduation Request</h2>
        <form method="POST" action="{{ route('admin.graduations.reject', $enrollment->id) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason</label>
                <textarea name="reason" class="form-control" rows="3" required maxlength="1000" placeholder="Reason for rejection…"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
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
</script>
@endpush