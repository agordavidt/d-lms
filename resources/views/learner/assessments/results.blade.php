@extends('layouts.admin')

@section('title', 'Assessment Results')

@push('styles')
<style>
    .results-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .results-header {
        background: #fff;
        padding: 48px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 32px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .results-title {
        font-size: 28px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .results-subtitle {
        font-size: 16px;
        color: #666;
        margin-bottom: 32px;
    }

    .score-display {
        margin-bottom: 32px;
    }

    .score-circle {
        width: 160px;
        height: 160px;
        margin: 0 auto 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        border: 8px solid;
    }

    .score-circle.passed {
        border-color: #4caf50;
        background: #f1f8f4;
    }

    .score-circle.failed {
        border-color: #f57c00;
        background: #fff8f0;
    }

    .score-percentage {
        font-size: 48px;
        font-weight: 700;
    }

    .score-percentage.passed {
        color: #4caf50;
    }

    .score-percentage.failed {
        color: #f57c00;
    }

    .score-label {
        font-size: 14px;
        color: #666;
        margin-top: 8px;
    }

    .status-message {
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 16px;
    }

    .status-message.passed {
        color: #4caf50;
    }

    .status-message.failed {
        color: #f57c00;
    }

    .score-breakdown {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
        margin-top: 32px;
    }

    .breakdown-item {
        text-align: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .breakdown-label {
        font-size: 13px;
        color: #666;
        margin-bottom: 8px;
    }

    .breakdown-value {
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .review-section {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 32px;
    }

    .question-review {
        padding: 32px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .question-review:last-child {
        border-bottom: none;
    }

    .question-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
    }

    .question-status {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .question-status.correct {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .question-status.incorrect {
        background: #fff3e0;
        color: #f57c00;
    }

    .question-content {
        flex: 1;
    }

    .question-number {
        font-size: 14px;
        color: #7571f9;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .question-text {
        font-size: 18px;
        font-weight: 500;
        color: #1a1a1a;
        margin-bottom: 16px;
        line-height: 1.6;
    }

    .answer-review {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 6px;
        margin-bottom: 16px;
    }

    .answer-label {
        font-size: 13px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .answer-text {
        font-size: 16px;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .answer-text.correct-answer {
        color: #2e7d32;
        font-weight: 500;
    }

    .answer-text.incorrect-answer {
        color: #f57c00;
    }

    .explanation {
        background: #e3f2fd;
        padding: 16px 20px;
        border-left: 4px solid #2196f3;
        border-radius: 4px;
        margin-top: 16px;
    }

    .explanation-label {
        font-size: 13px;
        color: #0d47a1;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .explanation-text {
        font-size: 15px;
        color: #1565c0;
        line-height: 1.6;
        margin: 0;
    }

    .points-earned {
        display: inline-block;
        padding: 4px 12px;
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 600;
    }

    .action-buttons {
        background: #fff;
        padding: 32px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .btn {
        padding: 14px 32px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
        margin: 0 8px;
    }

    .btn-primary {
        background: #7571f9;
        color: white;
    }

    .btn-primary:hover {
        background: #5f5bd1;
    }

    .btn-secondary {
        background: #fff;
        color: #666;
        border: 2px solid #e0e0e0;
    }

    .btn-secondary:hover {
        border-color: #7571f9;
        color: #7571f9;
    }

    .attempts-history {
        background: #fff;
        padding: 32px;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
    }

    .history-table th {
        text-align: left;
        padding: 12px;
        background: #f8f9fa;
        font-size: 13px;
        color: #666;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .history-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 15px;
        color: #333;
    }

    .history-table tr:last-child td {
        border-bottom: none;
    }

    .current-attempt {
        background: #f0f0ff;
    }
</style>
@endpush

@section('content')
<div class="results-container">
    <!-- Results Header -->
    <div class="results-header">
        <div class="results-title">{{ $assessment->title }}</div>
        <div class="results-subtitle">{{ $week->title }} • Week {{ $week->week_number }}</div>

        <div class="score-display">
            <div class="score-circle {{ $attempt->passed ? 'passed' : 'failed' }}">
                <div class="score-percentage {{ $attempt->passed ? 'passed' : 'failed' }}">
                    {{ number_format($attempt->percentage, 1) }}%
                </div>
                <div class="score-label">{{ $attempt->score_earned }}/{{ $attempt->total_points }} points</div>
            </div>

            <div class="status-message {{ $attempt->passed ? 'passed' : 'failed' }}">
                @if($attempt->passed)
                    Great work! You passed this assessment.
                @else
                    Keep learning. Score recorded.
                @endif
            </div>

            @if(!$attempt->passed && $weekProgress->assessment_attempts < $assessment->max_attempts)
            <p style="color: #666; font-size: 15px;">
                You can retake this assessment {{ $assessment->max_attempts - $weekProgress->assessment_attempts }} more {{ Str::plural('time', $assessment->max_attempts - $weekProgress->assessment_attempts) }}.
            </p>
            @endif
        </div>

        <div class="score-breakdown">
            <div class="breakdown-item">
                <div class="breakdown-label">Correct Answers</div>
                <div class="breakdown-value">
                    {{ collect($results)->where('is_correct', true)->count() }}/{{ count($results) }}
                </div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">Time Spent</div>
                <div class="breakdown-value">{{ $attempt->getFormattedTimeSpent() }}</div>
            </div>
            <div class="breakdown-item">
                <div class="breakdown-label">Attempt Number</div>
                <div class="breakdown-value">{{ $attempt->attempt_number }}</div>
            </div>
        </div>
    </div>

    <!-- Attempts History -->
    @if($allAttempts->count() > 1)
    <div class="attempts-history">
        <div class="section-title">Your Attempts</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Attempt</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allAttempts as $historyAttempt)
                <tr class="{{ $historyAttempt->id === $attempt->id ? 'current-attempt' : '' }}">
                    <td>
                        Attempt {{ $historyAttempt->attempt_number }}
                        @if($historyAttempt->id === $attempt->id)
                            <span style="color: #7571f9; font-weight: 600;">(Current)</span>
                        @endif
                    </td>
                    <td>{{ number_format($historyAttempt->percentage, 1) }}%</td>
                    <td>
                        @if($historyAttempt->passed)
                            <span style="color: #4caf50; font-weight: 500;">Passed</span>
                        @else
                            <span style="color: #f57c00; font-weight: 500;">Score Recorded</span>
                        @endif
                    </td>
                    <td>{{ $historyAttempt->submitted_at->format('M d, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Question Review -->
    @if($assessment->show_correct_answers)
    <div class="review-section">
        <div class="section-title">Answer Review</div>

        @foreach($results as $index => $result)
        <div class="question-review">
            <div class="question-header">
                <div class="question-status {{ $result['is_correct'] ? 'correct' : 'incorrect' }}">
                    @if($result['is_correct'])
                        ✓
                    @else
                        ✗
                    @endif
                </div>

                <div class="question-content">
                    <div class="question-number">Question {{ $index + 1 }}</div>
                    <div class="question-text">{{ $result['question']->question_text }}</div>

                    <div class="answer-review">
                        <div class="answer-label">Your Answer</div>
                        <div class="answer-text {{ $result['is_correct'] ? 'correct-answer' : 'incorrect-answer' }}">
                            @if($result['user_answer'])
                                @if(is_array($result['user_answer']))
                                    {{ implode(', ', array_map(fn($key) => $result['question']->options[$key] ?? $key, $result['user_answer'])) }}
                                @else
                                    {{ $result['question']->getOptionsForDisplay()[$result['user_answer']] ?? 'No answer selected' }}
                                @endif
                            @else
                                No answer selected
                            @endif
                        </div>
                    </div>

                    @if(!$result['is_correct'])
                    <div class="answer-review">
                        <div class="answer-label">Correct Answer</div>
                        <div class="answer-text correct-answer">
                            {{ $result['question']->getCorrectAnswerDisplay() }}
                        </div>
                    </div>
                    @endif

                    <div style="margin-top: 12px;">
                        <span class="points-earned">
                            {{ $result['points_earned'] }}/{{ $result['max_points'] }} points
                        </span>
                    </div>

                    @if($result['explanation'])
                    <div class="explanation">
                        <div class="explanation-label">Explanation</div>
                        <p class="explanation-text">{{ $result['explanation'] }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="action-buttons">
        @if($weekComplete)
            <a href="{{ route('learner.learning.index') }}" class="btn btn-primary">
                Continue to Next Week
            </a>
        @elseif($weekProgress->assessment_attempts < $assessment->max_attempts)
            <a href="{{ route('learner.assessments.start', $assessment->id) }}" class="btn btn-primary">
                Retake Assessment
            </a>
        @else
            <a href="{{ route('learner.learning.index') }}" class="btn btn-primary">
                Continue Learning
            </a>
        @endif

        <a href="{{ route('learner.curriculum') }}" class="btn btn-secondary">
            View Curriculum
        </a>
    </div>
</div>
@endsection