@extends('layouts.learner')

@section('title', 'Results — ' . $assessment->title)

@push('styles')
<style>
    body { background: #f8fafc; }

    /* Grade banner */
    .grade-banner { background: #fff0f0; border-bottom: 1px solid #fecaca; padding: 28px 0; }
    .grade-banner.passed { background: #f0fdf4; border-color: #bbf7d0; }

    /* Option feedback rows */
    .feedback-option {
        margin-bottom: 8px;
        border-radius: 8px;
        overflow: hidden;
        border: 1.5px solid #e2e8f0;
    }
    .feedback-option-header {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 18px;
        background: #fff;
    }
    .feedback-option-header input {
        flex-shrink: 0; margin-top: 3px; width: 17px; height: 17px; accent-color: #6366f1;
    }
    .feedback-body {
        padding: 12px 18px 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        line-height: 1.55;
        font-weight: 600;
    }
    .feedback-body.correct { background: #f0fdf4; color: #166534; }
    .feedback-body.incorrect { background: #fef2f2; color: #991b1b; }

    /* Attempts table */
    .attempts-row { display: grid; grid-template-columns: 80px 1fr 1fr 1fr; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; align-items: center; font-size: 14px; }
    .attempts-row:last-child { border-bottom: none; }
    .attempts-header { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; }
</style>
@endpush

@section('content')
<div style="font-family:'DM Sans',sans-serif; min-height:100vh; background:#f8fafc;">

    {{-- Top nav --}}
    <div style="background:#fff; border-bottom:1px solid #e2e8f0; padding:14px 0;">
        <div style="max-width:860px; margin:0 auto; padding:0 32px; display:flex; align-items:center; justify-content:space-between; gap:16px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <a href="{{ route('learner.learning.index', $enrollment->id) }}"
                   style="display:flex; align-items:center; gap:6px; color:#64748b; font-size:14px; font-weight:600; text-decoration:none;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
                <div style="width:1px; height:20px; background:#e2e8f0;"></div>
                <span style="font-size:15px; font-weight:700; color:#0f172a;">{{ $assessment->title }}</span>
                <span style="font-size:13px; color:#94a3b8;">Practice Assignment • {{ $assessment->time_limit_minutes ? $assessment->time_limit_minutes . ' min' : 'No time limit' }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
                    <path stroke-linecap="round" stroke-width="1.5" d="M12 8v4l3 3"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Grade banner --}}
    <div class="grade-banner {{ $attempt->passed ? 'passed' : '' }}">
        <div style="max-width:860px; margin:0 auto; padding:0 32px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div>
                <p style="font-size:22px; font-weight:800; color:{{ $attempt->passed ? '#15803d' : '#dc2626' }}; margin:0 0 6px;">
                    Your grade: {{ number_format($attempt->percentage, 0) }}%
                </p>
                <p style="font-size:14px; color:#64748b; margin:0;">
                    Your latest: <strong>{{ number_format($attempt->percentage, 0) }}%</strong>
                    &nbsp;•&nbsp;
                    Your highest: <strong>{{ number_format($allAttempts->max('percentage'), 0) }}%</strong>
                    &nbsp;•&nbsp;
                    To pass you need at least <strong>{{ $assessment->passing_score ?? 80 }}%</strong>.
                    We keep your highest score.
                </p>
            </div>

            @if($weekProgress->assessment_attempts < $assessment->max_attempts && !$attempt->passed)
            <a href="{{ route('learner.assessments.start', ['assessment' => $assessment->id, 'enrollment' => $enrollment->id]) }}"
               style="display:inline-flex; align-items:center; gap:8px; background:#4f46e5; color:#fff; padding:12px 24px; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none; box-shadow:0 4px 12px rgba(79,70,229,.2);">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Retry
            </a>
            @endif
        </div>
    </div>

    {{-- Main content --}}
    <div style="max-width:860px; margin:0 auto; padding:32px 32px 80px;">

        {{-- Score stats --}}
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:32px;">
            @php
                $correctCount = collect($results)->where('is_correct', true)->count();
            @endphp
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px 24px;">
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin:0 0 6px;">Score</p>
                <p style="font-size:24px; font-weight:800; color:{{ $attempt->passed ? '#15803d' : '#dc2626' }}; margin:0;">{{ number_format($attempt->percentage, 1) }}%</p>
            </div>
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px 24px;">
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin:0 0 6px;">Points</p>
                <p style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">{{ $attempt->score_earned }}<span style="font-size:14px; color:#94a3b8;">/{{ $attempt->total_points }}</span></p>
            </div>
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px 24px;">
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin:0 0 6px;">Correct</p>
                <p style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">{{ $correctCount }}<span style="font-size:14px; color:#94a3b8;">/{{ count($results) }}</span></p>
            </div>
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px 24px;">
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin:0 0 6px;">Time Spent</p>
                <p style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">{{ $attempt->getFormattedTimeSpent() }}</p>
            </div>
        </div>

        {{-- Attempts history --}}
        @if($allAttempts->count() > 1)
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px 28px; margin-bottom:28px;">
            <h3 style="font-size:15px; font-weight:800; color:#0f172a; margin:0 0 16px;">All Attempts</h3>
            <div class="attempts-row attempts-header">
                <span>Attempt</span><span>Score</span><span>Status</span><span>Date</span>
            </div>
            @foreach($allAttempts as $h)
            <div class="attempts-row" style="{{ $h->id === $attempt->id ? 'font-weight:700;' : '' }}">
                <span style="color:#6366f1; font-weight:700;">
                    #{{ $h->attempt_number }}
                    @if($h->id === $attempt->id)
                    <span style="font-size:11px; background:#eef2ff; color:#6366f1; padding:2px 8px; border-radius:100px; margin-left:4px;">Current</span>
                    @endif
                </span>
                <span>{{ number_format($h->percentage, 1) }}%</span>
                <span style="color:{{ $h->passed ? '#15803d' : '#92400e' }}; font-weight:600;">{{ $h->passed ? 'Passed' : 'Recorded' }}</span>
                <span style="color:#94a3b8;">{{ $h->submitted_at->format('M d, Y H:i') }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Answer review --}}
        @if($assessment->show_correct_answers)
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
            <div style="padding:24px 28px; border-bottom:1px solid #f1f5f9;">
                <h3 style="font-size:15px; font-weight:800; color:#0f172a; margin:0;">Answer Review</h3>
            </div>

            <div style="padding:0 28px;">
                @foreach($results as $index => $result)
                @php
                    $q = $result['question'];
                    $userAnswer = $result['user_answer'];
                    $userAnswerArr = is_array($userAnswer) ? $userAnswer : ($userAnswer ? [$userAnswer] : []);
                    $options = $q->getOptionsForDisplay();
                @endphp

                <div style="padding:36px 0; {{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">

                    {{-- Question header --}}
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px;">
                        <span style="font-size:12px; font-weight:700; color:#94a3b8;">Question {{ $index + 1 }}</span>
                        <span style="font-size:12px; font-weight:700; color:{{ $result['is_correct'] ? '#15803d' : '#dc2626' }}; background:{{ $result['is_correct'] ? '#f0fdf4' : '#fef2f2' }}; padding:3px 10px; border-radius:100px;">
                            {{ $result['points_earned'] }} / {{ $result['max_points'] }} pts
                        </span>
                    </div>

                    <p style="font-size:17px; font-weight:500; color:#0f172a; line-height:1.65; margin:8px 0 20px;">{{ $q->question_text }}</p>

                    {{-- Options with inline feedback --}}
                    @foreach($options as $key => $optionText)
                    @php
                        $wasSelected = in_array((string)$key, array_map('strval', $userAnswerArr));
                        $isCorrectOption = false;
                        // Determine if this option is a correct answer
                        if (isset($q->correct_answer)) {
                            $correctAns = $q->correct_answer;
                            if (is_array($correctAns)) {
                                $isCorrectOption = in_array((string)$key, array_map('strval', $correctAns));
                            } else {
                                $isCorrectOption = (string)$key === (string)$correctAns;
                            }
                        }

                        // Determine feedback state
                        $showFeedback = false;
                        $feedbackType = '';
                        $feedbackMsg = '';

                        if ($wasSelected && $isCorrectOption) {
                            $showFeedback = true;
                            $feedbackType = 'correct';
                            $feedbackMsg = 'Correct';
                        } elseif ($wasSelected && !$isCorrectOption) {
                            $showFeedback = true;
                            $feedbackType = 'incorrect';
                            $feedbackMsg = 'This should not be selected';
                        } elseif (!$wasSelected && $isCorrectOption && !$result['is_correct']) {
                            $showFeedback = true;
                            $feedbackType = 'correct';
                            $feedbackMsg = 'This should have been selected';
                        }
                    @endphp

                    <div class="feedback-option">
                        <div class="feedback-option-header">
                            @if($q->question_type === 'multiple_select')
                            <input type="checkbox" disabled {{ $wasSelected ? 'checked' : '' }}>
                            @else
                            <input type="radio" disabled {{ $wasSelected ? 'checked' : '' }}>
                            @endif
                            <span style="font-size:15px; color:#334155; line-height:1.6;">{{ $optionText }}</span>
                        </div>

                        @if($showFeedback)
                        <div class="feedback-body {{ $feedbackType }}">
                            @if($feedbackType === 'correct')
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @else
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:1px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @endif
                            <span>
                                <strong>{{ $feedbackMsg }}.</strong>
                                @if($result['explanation'] && $showFeedback && $loop->last)
                                    {{ $result['explanation'] }}
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>
                    @endforeach

                    {{-- Explanation (shown once at bottom if not shown inline) --}}
                    @if($result['explanation'] && !$result['is_correct'])
                    <div style="background:#eff6ff; border-radius:8px; padding:14px 18px; margin-top:12px; display:flex; gap:10px; align-items:flex-start;">
                        <svg width="16" height="16" fill="none" stroke="#3b82f6" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:2px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p style="font-size:13px; color:#1e40af; margin:0; line-height:1.6;"><strong>Explanation:</strong> {{ $result['explanation'] }}</p>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>

        </div>
        @endif

        {{-- Action buttons --}}
        <div style="display:flex; gap:16px; margin-top:28px; flex-wrap:wrap;">
            @if($weekComplete)
                <a href="{{ route('learner.learning.index', $enrollment->id) }}"
                   style="display:inline-flex; align-items:center; gap:8px; background:#4f46e5; color:#fff; padding:14px 28px; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none; box-shadow:0 4px 14px rgba(79,70,229,.2);">
                    Continue to Next Week
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @elseif($weekProgress->assessment_attempts < $assessment->max_attempts)
                <a href="{{ route('learner.assessments.start', ['assessment' => $assessment->id, 'enrollment' => $enrollment->id]) }}"
                   style="display:inline-flex; align-items:center; gap:8px; background:#4f46e5; color:#fff; padding:14px 28px; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none; box-shadow:0 4px 14px rgba(79,70,229,.2);">
                    Retake Assessment
                </a>
            @else
                <a href="{{ route('learner.learning.index', $enrollment->id) }}"
                   style="display:inline-flex; align-items:center; gap:8px; background:#4f46e5; color:#fff; padding:14px 28px; border-radius:10px; font-size:15px; font-weight:700; text-decoration:none;">
                    Continue Learning
                </a>
            @endif

            <a href="{{ route('learner.curriculum', $enrollment->id) }}"
               style="display:inline-flex; align-items:center; padding:14px 24px; border-radius:10px; border:2px solid #e2e8f0; background:transparent; color:#475569; font-size:15px; font-weight:700; text-decoration:none;">
                View Curriculum
            </a>
        </div>

    </div>

</div>
@endsection