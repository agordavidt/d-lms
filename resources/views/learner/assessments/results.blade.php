@extends('layouts.admin')

@section('title', 'Assessment Results')

@section('content')
<div class="container" style="max-width: 900px; margin: 2


0px auto; padding: 0 20px;">
    
    <!-- Simple Header -->
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="mb-1">{{ $assessment->title }}</h4>
            <p class="text-muted mb-0">{{ $week->title }} • Week {{ $week->week_number }}</p>
        </div>
    </div>

    <!-- Score Summary -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-6 mb-3 mb-md-0">
                    <small class="text-muted d-block">Score</small>
                    <h3 class="mb-0 {{ $attempt->passed ? 'text-success' : 'text-warning' }}">
                        {{ number_format($attempt->percentage, 1) }}%
                    </h3>
                </div>
                <div class="col-md-3 col-6 mb-3 mb-md-0">
                    <small class="text-muted d-block">Points</small>
                    <h3 class="mb-0">{{ $attempt->score_earned }}/{{ $attempt->total_points }}</h3>
                </div>
                <div class="col-md-3 col-6 mb-3 mb-md-0">
                    <small class="text-muted d-block">Correct</small>
                    <h3 class="mb-0">{{ collect($results)->where('is_correct', true)->count() }}/{{ count($results) }}</h3>
                </div>
                <div class="col-md-3 col-6 mb-3 mb-md-0">
                    <small class="text-muted d-block">Time</small>
                    <h3 class="mb-0">{{ $attempt->getFormattedTimeSpent() }}</h3>
                </div>
            </div>

            @if($attempt->passed)
                <div class="alert alert-success mt-3 mb-0">
                    You passed this assessment.
                </div>
            @else
                <div class="alert alert-warning mt-3 mb-0">
                    @if($weekProgress->assessment_attempts < $assessment->max_attempts)
                        You can retake this assessment {{ $assessment->max_attempts - $weekProgress->assessment_attempts }} more {{ Str::plural('time', $assessment->max_attempts - $weekProgress->assessment_attempts) }}.
                    @else
                        Your score has been recorded.
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Attempts History -->
    @if($allAttempts->count() > 1)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Your Attempts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
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
                        <tr class="{{ $historyAttempt->id === $attempt->id ? 'table-active' : '' }}">
                            <td>
                                #{{ $historyAttempt->attempt_number }}
                                @if($historyAttempt->id === $attempt->id)
                                    <span class="badge badge-primary">Current</span>
                                @endif
                            </td>
                            <td>{{ number_format($historyAttempt->percentage, 1) }}%</td>
                            <td>
                                @if($historyAttempt->passed)
                                    <span class="text-success">Passed</span>
                                @else
                                    <span class="text-warning">Recorded</span>
                                @endif
                            </td>
                            <td>{{ $historyAttempt->submitted_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Answer Review -->
    @if($assessment->show_correct_answers)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Answer Review</h5>
        </div>
        <div class="card-body">
            @foreach($results as $index => $result)
            <div class="mb-4 {{ $loop->last ? '' : 'pb-4 border-bottom' }}">
                <div class="d-flex align-items-start mb-3">
                    <span class="badge badge-{{ $result['is_correct'] ? 'success' : 'warning' }} mr-2" style="margin-top: 2px;">
                        @if($result['is_correct']) ✓ @else ✗ @endif
                    </span>
                    <div class="flex-grow-1">
                        <small class="text-muted">Question {{ $index + 1 }}</small>
                        <h6 class="mb-3">{{ $result['question']->question_text }}</h6>

                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td width="120" class="text-muted">Your Answer</td>
                                <td class="{{ $result['is_correct'] ? 'text-success' : 'text-warning' }}">
                                    @if($result['user_answer'])
                                        @if(is_array($result['user_answer']))
                                            {{ implode(', ', array_map(fn($key) => $result['question']->options[$key] ?? $key, $result['user_answer'])) }}
                                        @else
                                            {{ $result['question']->getOptionsForDisplay()[$result['user_answer']] ?? 'No answer' }}
                                        @endif
                                    @else
                                        No answer
                                    @endif
                                </td>
                            </tr>
                            @if(!$result['is_correct'])
                            <tr>
                                <td class="text-muted">Correct Answer</td>
                                <td class="text-success">{{ $result['question']->getCorrectAnswerDisplay() }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Points</td>
                                <td>{{ $result['points_earned'] }}/{{ $result['max_points'] }}</td>
                            </tr>
                        </table>

                        @if($result['explanation'])
                        <div class="alert alert-info mt-3 mb-0">
                            <small class="font-weight-bold d-block mb-1">Explanation</small>
                            <small>{{ $result['explanation'] }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="card">
        <div class="card-body text-center">
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

            <a href="{{ route('learner.curriculum') }}" class="btn btn-outline-secondary ml-2">
                View Curriculum
            </a>
        </div>
    </div>

</div>
@endsection