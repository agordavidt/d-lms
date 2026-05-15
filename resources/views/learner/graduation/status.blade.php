@extends('layouts.learner')
@section('title', 'Graduation Status')

@section('content')
@php
    $status   = $enrollment->graduation_status;
    $program  = $enrollment->program;
@endphp

<div style="max-width:640px;margin:0 auto;padding:48px 24px 80px;font-family:'DM Sans',sans-serif;">

    <div style="margin-bottom:28px;">
        <a href="{{ route('learner.my-learning') }}" style="font-size:13px;color:#64748b;text-decoration:none;">← My Learning</a>
        <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin:12px 0 4px;">Graduation Status</h1>
        <p style="font-size:.875rem;color:#64748b;margin:0;">{{ $program->name }}</p>
    </div>

    {{-- Status banner --}}
    @if($status === 'graduated')
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:14px;padding:24px;display:flex;align-items:center;gap:16px;margin-bottom:28px;">
        <div style="width:48px;height:48px;background:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        </div>
        <div>
            <p style="font-weight:800;font-size:1rem;color:#14532d;margin:0 0 4px;">Congratulations — You have graduated!</p>
            <p style="font-size:.82rem;color:#166534;margin:0;">Your certificate is available in the Certifications section.</p>
        </div>
    </div>
    <a href="{{ route('learner.certifications') }}" class="btn-begin-exam">View Certificate</a>

    @elseif($status === 'pending_review')
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;padding:24px;display:flex;align-items:center;gap:16px;margin-bottom:28px;">
        <div style="width:48px;height:48px;background:#3b82f6;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
        </div>
        <div>
            <p style="font-weight:800;font-size:1rem;color:#1e3a8a;margin:0 0 4px;">Certificate request submitted</p>
            <p style="font-size:.82rem;color:#1d4ed8;margin:0;">An administrator will issue your certificate shortly. Check your Certifications page.</p>
        </div>
    </div>

    @else
    {{-- active — show checklist --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;margin-bottom:24px;">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;">
            <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin:0;">Graduation Requirements</p>
        </div>

        @foreach([
            ['key' => 'all_content_complete',    'label' => 'All content completed'],
            ['key' => 'all_assessments_passed',  'label' => 'All assessments passed (including final examination)'],
            ['key' => 'meets_grade_requirement', 'label' => 'Minimum grade requirement met'],
        ] as $req)
        @php $ok = $eligibility[$req['key']]; @endphp
        <div style="padding:14px 20px;border-bottom:1px solid #f9fafb;display:flex;align-items:center;gap:12px;last:border-0;">
            <div style="width:24px;height:24px;border-radius:50%;background:{{ $ok ? '#f0fdf4' : '#f1f5f9' }};border:1.5px solid {{ $ok ? '#86efac' : '#e2e8f0' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if($ok)
                <svg width="13" height="13" viewBox="0 0 20 20" fill="#16a34a"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                @else
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"><circle cx="12" cy="12" r="10"/></svg>
                @endif
            </div>
            <span style="font-size:.875rem;color:{{ $ok ? '#166534' : '#374151' }};font-weight:{{ $ok ? '500' : '400' }};">{{ $req['label'] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Final exam status --}}
    @if($finalExam)
    <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:12px;padding:18px 20px;margin-bottom:24px;">
        <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#7c3aed;margin:0 0 8px;">Final Examination</p>
        @if($finalAttempt && $finalAttempt->passed)
        <p style="font-size:.875rem;color:#5b21b6;margin:0;">✓ Passed — {{ number_format($finalAttempt->percentage, 0) }}%</p>
        @elseif($finalAttempt && $finalAttempt->isOnCooldown())
        <p style="font-size:.875rem;color:#7c3aed;margin:0 0 4px;">Last attempt: {{ number_format($finalAttempt->percentage, 0) }}% (did not pass)</p>
        <p style="font-size:.82rem;color:#a78bfa;margin:0;">Retry available: <strong>{{ $finalAttempt->next_attempt_at->format('M d, Y \a\t g:i A') }}</strong></p>
        @elseif($finalAttempt)
        <p style="font-size:.875rem;color:#7c3aed;margin:0 0 6px;">Last attempt: {{ number_format($finalAttempt->percentage, 0) }}% — You may retry now.</p>
        <a href="{{ route('learner.learning.week', [$enrollment->id, $finalExam->moduleWeek->id]) }}"
           style="font-size:.82rem;color:#4f46e5;font-weight:700;text-decoration:none;">Go to Final Examination →</a>
        @else
        <p style="font-size:.875rem;color:#7c3aed;margin:0 0 6px;">Not yet attempted.</p>
        @if($eligibility['all_content_complete'])
        <a href="{{ route('learner.learning.week', [$enrollment->id, $finalExam->moduleWeek->id]) }}"
           style="font-size:.82rem;color:#4f46e5;font-weight:700;text-decoration:none;">Go to Final Examination →</a>
        @endif
        @endif
    </div>
    @endif

    @endif
</div>
@endsection