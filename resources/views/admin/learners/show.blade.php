@extends('layouts.admin')
@section('title', $learner->first_name . ' ' . $learner->last_name)

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.learners.index') }}">Learners</a></div>
        <h1>{{ $learner->first_name }} {{ $learner->last_name }}</h1>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="badge {{ match($learner->status) {
            'active'    => 'badge-green',
            'suspended' => 'badge-red',
            default     => 'badge-gray',
        } }}" style="padding: 0.35rem 0.9rem;">{{ ucfirst($learner->status) }}</span>

        @if($learner->status === 'active')
            <button onclick="setStatus('suspended')" class="btn btn-ghost btn-sm">Suspend</button>
        @else
            <button onclick="setStatus('active')" class="btn btn-outline btn-sm">Activate</button>
        @endif
    </div>
</div>

<div class="container section">

    @php $tab = request('tab', 'progress'); @endphp

    {{-- Tab navigation --}}
    <div class="tabs">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'progress']) }}"
           class="tab-link {{ $tab === 'progress' ? 'active' : '' }}">Progress</a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'assessments']) }}"
           class="tab-link {{ $tab === 'assessments' ? 'active' : '' }}">Assessments</a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'payments']) }}"
           class="tab-link {{ $tab === 'payments' ? 'active' : '' }}">Payments</a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'info']) }}"
           class="tab-link {{ $tab === 'info' ? 'active' : '' }}">Account Info</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 260px; gap: 2rem; align-items: start;">

    {{-- ══ Main content ══ --}}
    <div>

    {{-- ════ PROGRESS TAB ════ --}}
    @if($tab === 'progress')

        @if(!$enrollment)
        <div class="card card-body" style="color: var(--muted); text-align: center; padding: 3rem;">
            This learner has not enrolled in any program yet.
        </div>
        @else

        {{-- Enrollment selector if multiple --}}
        @if($learner->enrollments->count() > 1)
        <div style="margin-bottom: 1rem;">
            <select onchange="window.location = this.value" class="form-control" style="max-width: 280px;">
                @foreach($learner->enrollments as $e)
                <option value="{{ request()->fullUrlWithQuery(['enrollment_id' => $e->id]) }}"
                        {{ $enrollment->id === $e->id ? 'selected' : '' }}>
                    {{ $e->program->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem; margin-bottom: 1rem;">
            {{ $enrollment->program->name }}
        </h2>

        @foreach($enrollment->program->modules as $module)
        <div class="card" style="margin-bottom: 1rem;">
            <div style="padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.875rem;">
                {{ $module->title }}
            </div>

            @foreach($module->weeks as $week)
            @php $wp = $weekProgress[$week->id] ?? null; @endphp
            <div style="padding: 0.8rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                <div style="min-width: 0;">
                    <div style="font-size: 0.72rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em;">Week {{ $week->week_number }}</div>
                    <div style="font-weight: 500; font-size: 0.875rem;">{{ $week->title }}</div>
                    @if($wp && $wp->is_unlocked)
                    <div class="progress-bar-track" style="margin-top: 0.4rem; max-width: 200px;">
                        <div class="progress-bar-fill" style="width: {{ $wp->progress_percentage }}%"></div>
                    </div>
                    @endif
                </div>

                <div style="display: flex; gap: 1.5rem; flex-shrink: 0; text-align: center;">
                    {{-- Content --}}
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;">
                            {{ $wp ? $wp->contents_completed . '/' . $wp->total_contents : '—' }}
                        </div>
                        <div class="text-muted text-small">Content</div>
                    </div>

                    {{-- Assessment --}}
                    @if($week->has_assessment)
                    <div>
                        <div style="font-weight: 600; font-size: 0.875rem;
                             color: {{ ($wp && $wp->assessment_passed) ? 'var(--success)' : (($wp && $wp->assessment_attempts > 0) ? 'var(--error)' : 'var(--muted)') }}">
                            @if($wp && $wp->assessment_passed)
                                {{ number_format($wp->assessment_score, 0) }}% ✓
                            @elseif($wp && $wp->assessment_attempts > 0)
                                {{ number_format($wp->assessment_score, 0) }}%
                            @else
                                —
                            @endif
                        </div>
                        <div class="text-muted text-small">Assessment</div>
                    </div>
                    @endif

                    {{-- Status --}}
                    <div>
                        @if(!$wp || !$wp->is_unlocked)
                            <span class="badge badge-gray" style="font-size: 0.7rem;">Locked</span>
                        @elseif($wp->is_completed)
                            <span class="badge badge-green" style="font-size: 0.7rem;">Done</span>
                        @else
                            <span class="badge badge-blue" style="font-size: 0.7rem;">In Progress</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach

        @endif

    @endif

    {{-- ════ ASSESSMENTS TAB ════ --}}
    @if($tab === 'assessments')

        @if(!$enrollment)
        <div class="card card-body" style="color: var(--muted); text-align: center; padding: 3rem;">Not enrolled.</div>
        @else

        {{-- Summary stats --}}
        @if($assessmentStats)
        <div class="stats-row" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 1.5rem;">
            <div class="stat-box">
                <div class="stat-value">{{ $assessmentStats['total_assessments'] }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" style="color: var(--success);">{{ $assessmentStats['passed_assessments'] }}</div>
                <div class="stat-label">Passed</div>
            </div>
            <div class="stat-box">
                <div class="stat-value" style="color: var(--error);">{{ $assessmentStats['failed_assessments'] }}</div>
                <div class="stat-label">Failed</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $assessmentStats['average_score'] ? number_format($assessmentStats['average_score'], 1) . '%' : '—' }}</div>
                <div class="stat-label">Average</div>
            </div>
        </div>
        @endif

        {{-- Per-week breakdown --}}
        @forelse($weeklyBreakdown as $row)
        <div class="card" style="margin-bottom: 0.75rem;">
            <div class="card-body" style="padding: 1rem 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                    <div>
                        <div style="font-size: 0.72rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.2rem;">
                            Week {{ $row['week']->week_number }} · {{ $row['module']->title ?? '' }}
                        </div>
                        <div style="font-weight: 500; margin-bottom: 0.5rem;">{{ $row['assessment']->title }}</div>
                        <div style="font-size: 0.78rem; color: var(--muted);">
                            {{ $row['attempts_count'] }} attempt{{ $row['attempts_count'] !== 1 ? 's' : '' }}
                            @if($row['best_score'] !== null)
                                · Best score: <strong style="color: var(--text);">{{ number_format($row['best_score'], 0) }}%</strong>
                            @endif
                        </div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <span class="badge {{ $row['passed'] ? 'badge-green' : ($row['attempts_count'] > 0 ? 'badge-red' : 'badge-gray') }}">
                            {{ $row['passed'] ? 'Passed' : ($row['attempts_count'] > 0 ? 'Failed' : 'Not attempted') }}
                        </span>
                        @if($row['attempts_count'] > 0)
                        <div style="margin-top: 0.5rem;">
                            <a href="{{ route('admin.learners.assessment-attempt', [$learner->id, $row['attempts']->last()->id ?? 0]) }}"
                               class="text-small" style="color: var(--blue);">View latest attempt</a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Attempt scores --}}
                @if($row['attempts']->isNotEmpty())
                <div style="display: flex; gap: 0.4rem; margin-top: 0.75rem; flex-wrap: wrap;">
                    @foreach($row['attempts'] as $attempt)
                    <span style="font-size: 0.75rem; padding: 0.2rem 0.55rem; border-radius: 20px; font-weight: 600;
                         background: {{ $attempt->passed ? '#f0fdf4' : '#fef2f2' }};
                         color: {{ $attempt->passed ? '#166534' : '#991b1b' }};">
                        #{{ $attempt->attempt_number }}: {{ number_format($attempt->percentage, 0) }}%
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card card-body" style="color: var(--muted); text-align: center; padding: 2rem;">
            No assessments attempted yet.
        </div>
        @endforelse

        @endif

    @endif

    {{-- ════ PAYMENTS TAB ════ --}}
    @if($tab === 'payments')
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Program</th>
                    <th>Amount</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($learner->enrollments->flatMap->payments->sortByDesc('created_at') as $payment)
                <tr>
                    <td><code style="font-size: 0.75rem;">{{ $payment->reference }}</code></td>
                    <td class="text-small">{{ $payment->program->name ?? '—' }}</td>
                    <td style="font-weight: 600; font-size: 0.875rem;">₦{{ number_format($payment->final_amount, 0) }}</td>
                    <td class="text-small">{{ ucfirst($payment->payment_plan) }}{{ $payment->installment_number ? ' #' . $payment->installment_number : '' }}</td>
                    <td>
                        <span class="badge {{ match($payment->status) {
                            'successful' => 'badge-green',
                            'failed'     => 'badge-red',
                            'pending'    => 'badge-yellow',
                            default      => 'badge-gray',
                        } }}">{{ ucfirst($payment->status) }}</span>
                    </td>
                    <td class="text-muted text-small">{{ $payment->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 2rem;">No payment records.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════ ACCOUNT INFO TAB ════ --}}
    @if($tab === 'info')
    <div class="card card-body" style="max-width: 480px;">
        <div style="display: grid; gap: 1rem;">
            @foreach([
                'First Name'  => $learner->first_name,
                'Last Name'   => $learner->last_name,
                'Email'       => $learner->email,
                'Phone'       => $learner->phone ?? '—',
                'Joined'      => $learner->created_at->format('M j, Y'),
                'Email Verified' => $learner->email_verified_at ? $learner->email_verified_at->format('M j, Y') : 'Not verified',
            ] as $label => $value)
            <div style="display: flex; justify-content: space-between; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border);">
                <span class="text-muted text-small">{{ $label }}</span>
                <span style="font-size: 0.875rem; font-weight: 500;">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    </div>{{-- end main --}}

    {{-- ══ Sidebar ══ --}}
    <div>
        {{-- Enrollment summary --}}
        @if($enrollment)
        <div class="card card-body" style="margin-bottom: 1rem;">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif; font-size: 0.95rem;">Enrollment</div>
            <div style="display: grid; gap: 0.6rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Program</span>
                    <span style="font-weight: 500; text-align: right; max-width: 140px;">{{ $enrollment->program->name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Status</span>
                    <span class="badge {{ $enrollment->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($enrollment->status) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Progress</span>
                    <span style="font-weight: 600;">{{ number_format($enrollment->progress_percentage, 0) }}%</span>
                </div>
                <div class="progress-bar-track">
                    <div class="progress-bar-fill" style="width: {{ $enrollment->progress_percentage }}%"></div>
                </div>
                @if($enrollment->weekly_assessment_avg !== null)
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Assessment avg</span>
                    <span>{{ number_format($enrollment->weekly_assessment_avg, 1) }}%</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Graduated</span>
                    <span>{{ match($enrollment->graduation_status) {
                        'graduated'      => 'Yes ✓',
                        'pending_review' => 'Pending',
                        default          => 'No',
                    } }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Enrolled</span>
                    <span class="text-small">{{ \Carbon\Carbon::parse($enrollment->enrolled_at)->format('M j, Y') }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- All enrollments --}}
        @if($learner->enrollments->count() > 1)
        <div class="card card-body">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif; font-size: 0.95rem;">All Enrollments</div>
            @foreach($learner->enrollments as $e)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.8rem;">
                <span>{{ $e->program->name }}</span>
                <span class="badge {{ $e->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($e->status) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    </div>{{-- end grid --}}
</div>
@endsection

@push('scripts')
<script>
async function setStatus(status) {
    if (!confirm(`Set learner status to "${status}"?`)) return;
    const res = await fetch(`/admin/learners/{{ $learner->id }}/status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status }),
    });
    if (res.ok) location.reload();
    else alert('Failed to update status.');
}
</script>
@endpush