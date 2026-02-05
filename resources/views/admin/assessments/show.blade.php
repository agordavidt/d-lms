@extends('layouts.admin')

@section('title', 'Assessment Details')
@section('breadcrumb-parent', 'Assessments')
@section('breadcrumb-current', $assessment->title)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Assessment Overview -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">{{ $assessment->title }}</h4>
                    <small class="text-muted">
                        Week {{ $assessment->moduleWeek->week_number }}: {{ $assessment->moduleWeek->title }}
                    </small>
                </div>
                <div>
                    @if($assessment->is_active)
                        <span class="badge badge-success badge-lg">Active</span>
                    @else
                        <span class="badge badge-warning badge-lg">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Context Information -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Context</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Program</small>
                            <div class="font-weight-bold">{{ $assessment->moduleWeek->programModule->program->name }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Module</small>
                            <div class="font-weight-bold">{{ $assessment->moduleWeek->programModule->title }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Week</small>
                            <div class="font-weight-bold">Week {{ $assessment->moduleWeek->week_number }}</div>
                        </div>
                    </div>
                </div>

                @if($assessment->description)
                <div class="mb-4">
                    <h6 class="text-muted mb-2">Instructions</h6>
                    <p class="mb-0">{{ $assessment->description }}</p>
                </div>
                @endif

                <!-- Assessment Settings -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Settings</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small">Pass Percentage</div>
                                <h4 class="mb-0 text-primary">{{ $assessment->pass_percentage }}%</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small">Max Attempts</div>
                                <h4 class="mb-0 text-info">{{ $assessment->max_attempts }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small">Time Limit</div>
                                <h4 class="mb-0 text-warning">
                                    @if($assessment->time_limit_minutes)
                                        {{ $assessment->time_limit_minutes }}m
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small">Total Points</div>
                                <h4 class="mb-0 text-success">{{ $assessment->total_points }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2">
                                @if($assessment->randomize_questions)
                                    <span class="badge badge-secondary">
                                        <i class="icon-shuffle"></i> Randomize Questions
                                    </span>
                                @endif
                                @if($assessment->randomize_options)
                                    <span class="badge badge-secondary">
                                        <i class="icon-shuffle"></i> Randomize Options
                                    </span>
                                @endif
                                @if($assessment->show_correct_answers)
                                    <span class="badge badge-info">
                                        <i class="icon-eye"></i> Show Correct Answers
                                    </span>
                                @else
                                    <span class="badge badge-dark">
                                        <i class="icon-eye-off"></i> Hide Correct Answers
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions Summary -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Questions ({{ $assessment->questions->count() }})</h6>
                    
                    @if($assessment->questions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Question</th>
                                        <th style="width: 120px;">Type</th>
                                        <th style="width: 80px;" class="text-center">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assessment->questions as $question)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ Str::limit($question->question_text, 80) }}
                                            @if($question->question_image)
                                                <i class="icon-picture text-muted ml-1" title="Has image"></i>
                                            @endif
                                            @if($question->explanation)
                                                <i class="icon-info text-info ml-1" title="Has explanation"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light">
                                                {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $question->points }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Question Type Distribution -->
                        <div class="mt-3">
                            <small class="text-muted">Question Distribution:</small>
                            <div class="d-flex gap-2 mt-2">
                                @php
                                    $typeCount = $assessment->questions->groupBy('question_type')->map->count();
                                @endphp
                                @if($typeCount->get('multiple_choice', 0) > 0)
                                    <span class="badge badge-primary">
                                        Multiple Choice: {{ $typeCount->get('multiple_choice') }}
                                    </span>
                                @endif
                                @if($typeCount->get('true_false', 0) > 0)
                                    <span class="badge badge-success">
                                        True/False: {{ $typeCount->get('true_false') }}
                                    </span>
                                @endif
                                @if($typeCount->get('multiple_select', 0) > 0)
                                    <span class="badge badge-warning">
                                        Multiple Select: {{ $typeCount->get('multiple_select') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 bg-light rounded">
                            <p class="text-muted mb-0">No questions added yet</p>
                        </div>
                    @endif
                </div>

                <!-- Learner Attempts (if any) -->
                @if($assessment->attempts->count() > 0)
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Recent Attempts</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Learner</th>
                                    <th>Attempt</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assessment->attempts()->latest()->limit(10)->get() as $attempt)
                                <tr>
                                    <td>{{ $attempt->user->name }}</td>
                                    <td>{{ $attempt->attempt_number }}/{{ $assessment->max_attempts }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ $attempt->percentage }}%</span>
                                        <small class="text-muted">({{ $attempt->score_earned }}/{{ $attempt->total_points }})</small>
                                    </td>
                                    <td>
                                        @if($attempt->passed)
                                            <span class="badge badge-success">Passed</span>
                                        @else
                                            <span class="badge badge-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $attempt->submitted_at->format('M d, Y H:i') }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($assessment->attempts->count() > 10)
                        <div class="text-center mt-2">
                            <small class="text-muted">Showing 10 of {{ $assessment->attempts->count() }} attempts</small>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.weeks.show', $assessment->module_week_id) }}" 
                   class="btn btn-secondary btn-block mb-2">
                    <i class="icon-arrow-left"></i> Back to Week
                </a>

                <a href="{{ route('admin.assessments.questions.index', $assessment->id) }}" 
                   class="btn btn-primary btn-block mb-2">
                    <i class="icon-list"></i> Manage Questions
                </a>

                <a href="{{ route('admin.assessments.edit', $assessment->id) }}" 
                   class="btn btn-info btn-block mb-2">
                    <i class="icon-settings"></i> Edit Settings
                </a>

                @if($assessment->questions->count() > 0)
                    <button type="button" 
                            class="btn btn-{{ $assessment->is_active ? 'warning' : 'success' }} btn-block mb-2"
                            onclick="toggleAssessmentStatus()">
                        <i class="icon-power"></i> 
                        {{ $assessment->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                @endif

                <hr>

                <button type="button" 
                        class="btn btn-danger btn-block"
                        onclick="deleteAssessment()"
                        @if($assessment->attempts->count() > 0) disabled title="Cannot delete with learner attempts" @endif>
                    <i class="icon-trash"></i> Delete Assessment
                </button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Statistics</h4>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Questions</span>
                        <h4 class="mb-0 text-primary">{{ $assessment->questions->count() }}</h4>
                    </div>
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Total Attempts</span>
                        <h4 class="mb-0 text-info">{{ $assessment->attempts->count() }}</h4>
                    </div>
                </div>

                @if($assessment->attempts->count() > 0)
                    @php
                        $submittedAttempts = $assessment->attempts()->submitted()->get();
                        $passedAttempts = $submittedAttempts->where('passed', true)->count();
                        $passRate = $submittedAttempts->count() > 0 
                            ? ($passedAttempts / $submittedAttempts->count()) * 100 
                            : 0;
                    @endphp

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Pass Rate</span>
                            <h4 class="mb-0 text-success">{{ number_format($passRate, 1) }}%</h4>
                        </div>
                        <small class="text-muted">{{ $passedAttempts }}/{{ $submittedAttempts->count() }} passed</small>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Avg Score</span>
                            <h4 class="mb-0 text-warning">{{ number_format($submittedAttempts->avg('percentage'), 1) }}%</h4>
                        </div>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Unique Learners</span>
                            <h4 class="mb-0 text-secondary">{{ $assessment->attempts->unique('user_id')->count() }}</h4>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Avg Time</span>
                            <h4 class="mb-0 text-dark">
                                @php
                                    $avgSeconds = $submittedAttempts->avg('time_spent_seconds');
                                    $minutes = floor($avgSeconds / 60);
                                    $seconds = $avgSeconds % 60;
                                @endphp
                                {{ $minutes }}m {{ round($seconds) }}s
                            </h4>
                        </div>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="icon-info" style="font-size: 32px; color: #ccc;"></i>
                        <p class="text-muted mb-0 mt-2">No attempts yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance Insights -->
        @if($assessment->attempts->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Performance Insights</h4>
            </div>
            <div class="card-body">
                @php
                    $submittedAttempts = $assessment->attempts()->submitted()->get();
                    $highestScore = $submittedAttempts->max('percentage');
                    $lowestScore = $submittedAttempts->min('percentage');
                @endphp

                <div class="mb-3">
                    <small class="text-muted">Highest Score</small>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $highestScore }}%">
                            {{ number_format($highestScore, 1) }}%
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Average Score</small>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-info" 
                             role="progressbar" 
                             style="width: {{ $submittedAttempts->avg('percentage') }}%">
                            {{ number_format($submittedAttempts->avg('percentage'), 1) }}%
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted">Lowest Score</small>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-danger" 
                             role="progressbar" 
                             style="width: {{ $lowestScore }}%">
                            {{ number_format($lowestScore, 1) }}%
                        </div>
                    </div>
                </div>

                <hr>

                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">First Attempt Pass Rate:</span>
                        <strong>
                            @php
                                $firstAttempts = $submittedAttempts->where('attempt_number', 1);
                                $firstPassed = $firstAttempts->where('passed', true)->count();
                                $firstPassRate = $firstAttempts->count() > 0 
                                    ? ($firstPassed / $firstAttempts->count()) * 100 
                                    : 0;
                            @endphp
                            {{ number_format($firstPassRate, 1) }}%
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Retake Success Rate:</span>
                        <strong>
                            @php
                                $retakes = $submittedAttempts->where('attempt_number', '>', 1);
                                $retakePassed = $retakes->where('passed', true)->count();
                                $retakeRate = $retakes->count() > 0 
                                    ? ($retakePassed / $retakes->count()) * 100 
                                    : 0;
                            @endphp
                            {{ number_format($retakeRate, 1) }}%
                        </strong>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Status & Timestamps -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Information</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if($assessment->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-warning">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $assessment->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $assessment->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @if($assessment->creator)
                    <tr>
                        <td><strong>Created By:</strong></td>
                        <td>{{ $assessment->creator->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAssessmentStatus() {
    fetch('{{ route("admin.assessments.toggle-active", $assessment->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
    });
}

function deleteAssessment() {
    if (confirm('Are you sure you want to delete this assessment? This will delete all questions.')) {
        fetch('{{ route("admin.assessments.destroy", $assessment->id) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                setTimeout(() => {
                    window.location.href = '{{ route("admin.weeks.show", $assessment->module_week_id) }}';
                }, 1000);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('An error occurred. Please try again.');
        });
    }
}
</script>
@endpush

@push('styles')
<style>
.gap-2 {
    gap: 0.5rem;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}

.progress {
    background-color: #e9ecef;
}

.border {
    border: 1px solid #dee2e6 !important;
}
</style>
@endpush