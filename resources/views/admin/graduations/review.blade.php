@extends('layouts.app')
@section('title', 'Review — ' . $enrollment->user->full_name)

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.graduations.index') }}">Graduation Approvals</a></div>
        <h1>{{ $enrollment->user->full_name }}</h1>
        <p class="text-muted text-small">{{ $enrollment->program->name }} · {{ $enrollment->enrollment_number }}</p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        <form method="POST" action="{{ route('admin.graduations.approve', $enrollment->id) }}"
              onsubmit="return confirm('Grant certificate to {{ addslashes($enrollment->user->full_name) }}?')">
            @csrf
            <button type="submit"
                    style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;border:none;background:#f0fdf4;color:#166534;font-size:.875rem;font-weight:700;cursor:pointer;border:1px solid #bbf7d0;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Grant Certificate
            </button>
        </form>
        <button onclick="openModal('reject-modal')" class="btn btn-ghost btn-sm">Reject</button>
    </div>
</div>

<div style="max-width:900px;margin:0 auto;padding:1.5rem 2rem 3rem;">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">

        {{-- Eligibility checklist --}}
        {{-- Gate: all weeks complete + final exam passed (stored as final_exam_score on enrollment) --}}
        <div class="card card-body">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.75rem;">
                Eligibility
            </p>
            @php
                $weeksOk    = $allWeeksComplete;
                $finalOk    = $enrollment->final_exam_score !== null
                              && $enrollment->final_exam_score >= \App\Models\Assessment::FINAL_PASS_PERCENTAGE;
            @endphp
            @foreach([
                ['ok' => $weeksOk, 'label' => 'All course modules completed'],
                ['ok' => $finalOk, 'label' => 'Final examination passed (≥ ' . \App\Models\Assessment::FINAL_PASS_PERCENTAGE . '%)'],
            ] as $item)
            <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--border);">
                <div style="width:22px;height:22px;border-radius:50%;background:{{ $item['ok'] ? '#f0fdf4' : '#fef2f2' }};border:1.5px solid {{ $item['ok'] ? '#86efac' : '#fca5a5' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    @if($item['ok'])
                    <svg width="11" height="11" viewBox="0 0 20 20" fill="#16a34a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @else
                    <svg width="11" height="11" viewBox="0 0 20 20" fill="#dc2626"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <span style="font-size:.875rem;color:{{ $item['ok'] ? '#166534' : '#991b1b' }};">{{ $item['label'] }}</span>
            </div>
            @endforeach

            {{-- Overall gate result --}}
            <div style="margin-top:.75rem;padding:.65rem .85rem;border-radius:8px;background:{{ ($weeksOk && $finalOk) ? '#f0fdf4' : '#fef2f2' }};font-size:.82rem;font-weight:700;color:{{ ($weeksOk && $finalOk) ? '#166534' : '#991b1b' }};">
                {{ ($weeksOk && $finalOk) ? '✓ Eligible for graduation' : '✗ Requirements not yet met' }}
            </div>
        </div>

        {{-- Score summary --}}
        <div class="card card-body">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:.75rem;">
                Score Summary
            </p>
            <div style="display:flex;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--border);font-size:.875rem;">
                <span class="text-muted">Final exam score</span>
                <strong style="color:{{ $finalOk ? 'var(--success)' : 'var(--error)' }};">
                    {{ $enrollment->final_exam_score !== null ? number_format($enrollment->final_exam_score, 0).'%' : '—' }}
                </strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--border);font-size:.875rem;">
                <span class="text-muted">Weeks completed</span>
                <strong>{{ $enrollment->weekProgress()->where('is_completed', true)->count() }} / {{ $enrollment->program->getPublishedWeeks()->filter(fn($w) => !($w->assessment?->is_final))->count() }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--border);font-size:.875rem;">
                <span class="text-muted">Final exam attempts</span>
                <strong>{{ $finalExamAttempts->count() }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.55rem 0;font-size:.875rem;">
                <span class="text-muted">Requested</span>
                <span>{{ $enrollment->graduation_requested_at?->format('M d, Y') ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- Final exam attempts --}}
    @if($finalExamAttempts->count())
    <div class="card" style="margin-bottom:1.25rem;">
        <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin:0;">
                Final Examination — All Attempts
            </p>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Result</th>
                    <th>Time Spent</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($finalExamAttempts as $attempt)
                <tr style="{{ $attempt->id === $latestFinalAttempt?->id ? 'font-weight:600;' : '' }}">
                    <td>#{{ $attempt->attempt_number }}
                        @if($attempt->id === $latestFinalAttempt?->id)
                        <span style="font-size:.7rem;background:#eff6ff;color:#2563eb;padding:1px 7px;border-radius:99px;margin-left:4px;font-weight:700;">Latest</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:700;color:{{ $attempt->passed ? 'var(--success)' : 'var(--error)' }};">
                            {{ number_format($attempt->percentage, 0) }}%
                        </span>
                    </td>
                    <td>
                        <span style="font-size:.75rem;font-weight:700;padding:2px 8px;border-radius:99px;
                                     background:{{ $attempt->passed ? '#f0fdf4' : '#fef2f2' }};
                                     color:{{ $attempt->passed ? '#166534' : '#dc2626' }};">
                            {{ $attempt->passed ? 'Passed' : 'Failed' }}
                        </span>
                    </td>
                    <td class="text-muted text-small">
                        @php
                            $secs = $attempt->time_spent_seconds;
                            echo $secs >= 3600
                                ? floor($secs/3600).'h '.floor(($secs%3600)/60).'m'
                                : floor($secs/60).'m '.($secs%60).'s';
                        @endphp
                    </td>
                    <td class="text-muted text-small">{{ $attempt->submitted_at?->format('M d, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Week-by-week completion --}}
    <div class="card">
        <div style="padding:.9rem 1.25rem;border-bottom:1px solid var(--border);">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin:0;">
                Course Completion by Week
            </p>
        </div>
        @php
            $courseWeeks = $enrollment->program->getPublishedWeeks()
                ->filter(fn($w) => !($w->assessment?->is_final));
        @endphp
        @foreach($courseWeeks as $week)
        @php
            $wp = $enrollment->weekProgress->firstWhere('module_week_id', $week->id);
        @endphp
        <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">Week {{ $week->week_number }}</div>
                <div style="font-size:.875rem;font-weight:500;">{{ $week->title }}</div>
            </div>
            <div style="display:flex;gap:1.5rem;align-items:center;flex-shrink:0;">
                {{-- Content --}}
                <div style="text-align:center;">
                    <div style="font-size:.82rem;font-weight:600;">
                        {{ $wp ? $wp->contents_completed.'/'.$wp->total_contents : '—' }}
                    </div>
                    <div class="text-muted text-small">Content</div>
                </div>
                {{-- Quiz --}}
                @if($week->has_assessment && !($week->assessment?->is_final))
                <div style="text-align:center;">
                    <div style="font-size:.82rem;font-weight:700;color:{{ ($wp && $wp->assessment_passed) ? 'var(--success)' : 'var(--muted)' }};">
                        {{ ($wp && $wp->assessment_passed) ? '100% ✓' : ($wp && $wp->assessment_attempts > 0 ? 'Failed' : '—') }}
                    </div>
                    <div class="text-muted text-small">Quiz</div>
                </div>
                @endif
                {{-- Status badge --}}
                <div>
                    @if(!$wp || !$wp->is_unlocked)
                        <span class="badge badge-gray" style="font-size:.7rem;">Locked</span>
                    @elseif($wp->is_completed)
                        <span class="badge badge-green" style="font-size:.7rem;">Complete</span>
                    @else
                        <span class="badge badge-blue" style="font-size:.7rem;">In Progress</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>

{{-- Reject modal --}}
<div class="modal-overlay" id="reject-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal('reject-modal')">&#215;</button>
        <h2>Reject Graduation Request</h2>
        <p class="text-muted text-small" style="margin-bottom:1rem;">{{ $enrollment->user->full_name }}</p>
        <form method="POST" action="{{ route('admin.graduations.reject', $enrollment->id) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason <span style="color:var(--error)">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required maxlength="1000"
                          placeholder="Reason for rejection…"></textarea>
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
</script>
@endpush