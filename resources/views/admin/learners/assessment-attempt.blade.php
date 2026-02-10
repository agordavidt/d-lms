@extends('layouts.admin')

@section('title', 'Assessment Attempt Details')
@section('breadcrumb-parent', 'Learners')
@section('breadcrumb-current', 'Attempt Details')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <a href="{{ route('admin.learners.show', $learner->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        Back to Learner Profile
    </a>

    <div class="row">
        <div class="col-lg-8">
            <!-- Attempt Summary -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ $attempt->assessment->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Learner:</strong></td>
                                    <td>{{ $learner->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Week:</strong></td>
                                    <td>{{ $attempt->assessment->moduleWeek->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Submitted:</strong></td>
                                    <td>{{ $attempt->submitted_at->format('M d, Y @ h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Time Spent:</strong></td>
                                    <td>{{ gmdate('i:s', $attempt->time_spent_seconds) }} minutes</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td width="40%"><strong>Score:</strong></td>
                                    <td>
                                        <strong class="text-{{ $attempt->passed ? 'success' : 'danger' }}">
                                            {{ $attempt->score_earned }}/{{ $attempt->total_points }} 
                                            ({{ number_format($attempt->percentage, 1) }}%)
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Result:</strong></td>
                                    <td>
                                        @if($attempt->passed)
                                            <span class="badge badge-success">Passed</span>
                                        @else
                                            <span class="badge badge-danger">Failed</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Pass Mark:</strong></td>
                                    <td>{{ $attempt->assessment->pass_percentage }}%</td>
                                </tr>
                                <tr>
                                    <td><strong>Questions:</strong></td>
                                    <td>{{ count($scoredAnswers) }} answered</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Question Review -->
                    <h5 class="font-weight-bold mb-3">Question Review</h5>
                    @foreach($attempt->assessment->questions as $question)
                        @php
                            $answer = collect($scoredAnswers)->firstWhere('question_id', $question->id);
                            $isCorrect = $answer['is_correct'] ?? false;
                            
                            // Decode JSON fields
                            $options = is_array($question->options) ? $question->options : json_decode($question->options, true);
                            $correctAnswers = is_array($question->correct_answer) ? $question->correct_answer : json_decode($question->correct_answer, true);
                            
                            // Ensure correctAnswers is always an array
                            if (!is_array($correctAnswers)) {
                                $correctAnswers = [$correctAnswers];
                            }
                        @endphp
                        <div class="card mb-3 border-{{ $isCorrect ? 'success' : 'danger' }}">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Question {{ $loop->iteration }}</strong>
                                        @if($isCorrect)
                                            <span class="badge badge-success ml-2">Correct</span>
                                        @else
                                            <span class="badge badge-danger ml-2">Incorrect</span>
                                        @endif
                                    </div>
                                    <div>
                                        <strong>{{ $answer['points_earned'] ?? 0 }}/{{ $question->points }} pts</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="mb-3"><strong>{{ $question->question_text }}</strong></p>

                                @if($question->question_image)
                                <div class="mb-3">
                                    <img src="{{ $question->question_image }}" alt="Question Image" 
                                         style="max-width: 100%; height: auto; border-radius: 4px;">
                                </div>
                                @endif

                                <div class="pl-3">
                                    @if($options)
                                        @foreach($options as $index => $option)
                                            @php
                                                $optionLetter = chr(65 + $loop->index);
                                                $userAnswers = is_array($answer['user_answer'] ?? []) ? ($answer['user_answer'] ?? []) : [$answer['user_answer'] ?? ''];
                                                $isUserAnswer = in_array($optionLetter, $userAnswers);
                                                $isCorrectAnswer = in_array($optionLetter, $correctAnswers);
                                            @endphp
                                            <div class="mb-2 p-2 rounded 
                                                {{ $isCorrectAnswer ? 'bg-success text-white' : '' }}
                                                {{ $isUserAnswer && !$isCorrectAnswer ? 'bg-danger text-white' : '' }}">
                                                <strong>{{ $optionLetter }}.</strong> {{ $option }}
                                                @if($isUserAnswer)
                                                    <span class="badge badge-light ml-2">Your Answer</span>
                                                @endif
                                                @if($isCorrectAnswer)
                                                    <span class="badge badge-light ml-2">Correct Answer</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                @if($question->explanation)
                                <div class="alert alert-info mt-3 mb-0">
                                    <strong>Explanation:</strong> {{ $question->explanation }}
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Score Display -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Performance</h4>
                </div>
                <div class="card-body text-center">
                    <div style="margin: 20px 0;">
                        <div style="width: 120px; height: 120px; margin: 0 auto; 
                                    border-radius: 50%; display: flex; align-items: center; 
                                    justify-content: center; flex-direction: column; 
                                    border: 6px solid {{ $attempt->passed ? '#28a745' : '#dc3545' }}; 
                                    background: {{ $attempt->passed ? '#f1f8f4' : '#ffebee' }};">
                            <div style="font-size: 36px; font-weight: 700; 
                                        color: {{ $attempt->passed ? '#2e7d32' : '#c62828' }};">
                                {{ number_format($attempt->percentage, 0) }}%
                            </div>
                            <small style="color: #666;">Score</small>
                        </div>
                    </div>
                    <p class="mb-0">
                        @if($attempt->passed)
                            <span class="text-success">Passed the assessment</span>
                        @else
                            <span class="text-danger">Did not pass</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">Statistics</h4>
                </div>
                <div class="card-body">
                    @php
                        $correct = collect($scoredAnswers)->where('is_correct', true)->count();
                        $incorrect = collect($scoredAnswers)->where('is_correct', false)->count();
                    @endphp
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Correct Answers</span>
                            <strong class="text-success">{{ $correct }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Incorrect Answers</span>
                            <strong class="text-danger">{{ $incorrect }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Questions</span>
                            <strong>{{ $attempt->assessment->questions->count() }}</strong>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Accuracy</span>
                            <strong>{{ count($scoredAnswers) > 0 ? number_format(($correct / count($scoredAnswers)) * 100, 1) : 0 }}%</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Attempts -->
            @php
                $otherAttempts = \App\Models\AssessmentAttempt::where('enrollment_id', $attempt->enrollment_id)
                    ->where('assessment_id', $attempt->assessment_id)
                    ->where('id', '!=', $attempt->id)
                    ->where('status', 'submitted')
                    ->orderBy('submitted_at', 'desc')
                    ->get();
            @endphp

            @if($otherAttempts->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">Other Attempts</h4>
                </div>
                <div class="card-body">
                    @foreach($otherAttempts as $other)
                    <div class="mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block">{{ $other->submitted_at->format('M d, Y') }}</small>
                            </div>
                            <div>
                                <a href="{{ route('admin.learners.assessment-attempt', [$learner->id, $other->id]) }}" 
                                   class="btn btn-sm btn-outline-secondary">
                                    {{ number_format($other->percentage, 1) }}%
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection