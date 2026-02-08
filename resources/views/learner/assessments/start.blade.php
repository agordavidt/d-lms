@extends('layouts.admin')

@section('title', 'Assessment')

@push('styles')
<style>
    .assessment-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .assessment-header {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .assessment-title {
        font-size: 28px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .assessment-subtitle {
        font-size: 16px;
        color: #666;
        margin-bottom: 32px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .info-item {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .info-label {
        font-size: 13px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .info-value {
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .instructions-section {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
    }

    .instructions-text {
        font-size: 16px;
        line-height: 1.7;
        color: #333;
        margin-bottom: 24px;
    }

    .instructions-list {
        list-style: none;
        padding: 0;
        margin: 0 0 24px 0;
    }

    .instructions-list li {
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 15px;
        color: #333;
    }

    .instructions-list li:last-child {
        border-bottom: none;
    }

    .attempts-status {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .score-display {
        text-align: center;
        padding: 24px;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 24px;
    }

    .score-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 8px;
    }

    .score-value {
        font-size: 36px;
        font-weight: 700;
        color: #4caf50;
    }

    .action-section {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .btn-primary-large {
        background: #7571f9;
        color: white;
        padding: 16px 48px;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary-large:hover {
        background: #5f5bd1;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(117, 113, 249, 0.3);
    }

    .btn-secondary-large {
        background: #fff;
        color: #666;
        padding: 16px 48px;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 500;
        border: 2px solid #e0e0e0;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary-large:hover {
        border-color: #7571f9;
        color: #7571f9;
    }

    .warning-message {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 16px 20px;
        margin-bottom: 24px;
        border-radius: 4px;
    }

    .warning-message p {
        margin: 0;
        color: #856404;
        font-size: 15px;
    }

    .continue-message {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 16px 20px;
        margin-bottom: 24px;
        border-radius: 4px;
    }

    .continue-message p {
        margin: 0;
        color: #0d47a1;
        font-size: 15px;
    }
</style>
@endpush

@section('content')
<div class="assessment-container">
    <!-- Header -->
    <div class="assessment-header">
        <div class="assessment-title">{{ $assessment->title }}</div>
        <div class="assessment-subtitle">{{ $week->title }} • Week {{ $week->week_number }}</div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Questions</div>
                <div class="info-value">{{ $assessment->questions->count() }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Points</div>
                <div class="info-value">{{ $assessment->total_points }}</div>
            </div>
            @if($assessment->time_limit_minutes)
            <div class="info-item">
                <div class="info-label">Time Limit</div>
                <div class="info-value">{{ $assessment->time_limit_minutes }} min</div>
            </div>
            @endif
            <div class="info-item">
                <div class="info-label">Attempts</div>
                <div class="info-value">{{ $attemptsUsed }}/{{ $assessment->max_attempts }}</div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    @if($assessment->description)
    <div class="instructions-section">
        <div class="section-title">Instructions</div>
        <div class="instructions-text">{{ $assessment->description }}</div>
    </div>
    @endif

    <!-- Previous Attempts -->
    @if($bestScore !== null)
    <div class="attempts-status">
        <div class="section-title">Your Best Score</div>
        <div class="score-display">
            <div class="score-label">Score</div>
            <div class="score-value">{{ number_format($bestScore, 1) }}%</div>
        </div>
        
        @if($remainingAttempts > 0)
        <p style="text-align: center; color: #666; margin: 0;">
            You have {{ $remainingAttempts }} {{ Str::plural('attempt', $remainingAttempts) }} remaining to improve your score.
        </p>
        @endif
    </div>
    @endif

    <!-- In Progress Attempt -->
    @if($inProgressAttempt)
    <div class="continue-message">
        <p>You have an assessment in progress. Click below to continue where you left off.</p>
    </div>
    @endif

    <!-- Warnings -->
    @if($attemptsUsed >= $assessment->max_attempts)
    <div class="warning-message">
        <p>You have used all {{ $assessment->max_attempts }} attempts. Your best score of {{ number_format($bestScore, 1) }}% has been recorded.</p>
    </div>
    @endif

    <!-- Action -->
    <div class="action-section">
        @if($inProgressAttempt)
            <a href="{{ route('learner.attempts.show', $inProgressAttempt->id) }}" 
               class="btn-primary-large">
                Continue Assessment
            </a>
        @elseif($attemptsUsed < $assessment->max_attempts)
            <button type="button" class="btn-primary-large" onclick="startAssessment()">
                @if($attemptsUsed > 0)
                    Retake Assessment
                @else
                    Begin Assessment
                @endif
            </button>
        @endif

        <div style="margin-top: 24px;">
            <a href="{{ route('learner.learning.index') }}" class="btn-secondary-large">
                Back to Learning
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function startAssessment() {
    fetch('{{ route("learner.assessments.attempt", $assessment->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            toastr.error(data.message || 'Failed to start assessment');
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
    });
}
</script>
@endpush