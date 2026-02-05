@extends('layouts.admin')

@section('title', 'Edit Assessment')
@section('breadcrumb-parent', 'Assessments')
@section('breadcrumb-current', 'Edit')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Assessment Settings</h4>
            </div>
            <div class="card-body">
                <!-- Context Information -->
                <div class="alert alert-info mb-4">
                    <strong><i class="icon-info"></i> Editing assessment for:</strong><br>
                    <div class="mt-2">
                        <strong>Program:</strong> {{ $assessment->moduleWeek->programModule->program->name }}<br>
                        <strong>Module:</strong> {{ $assessment->moduleWeek->programModule->title }}<br>
                        <strong>Week:</strong> Week {{ $assessment->moduleWeek->week_number }} - {{ $assessment->moduleWeek->title }}
                    </div>
                </div>

                <form action="{{ route('admin.assessments.update', $assessment->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Assessment Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               name="title" 
                               value="{{ old('title', $assessment->title) }}" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Instructions for Learners</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  rows="3">{{ old('description', $assessment->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Pass Percentage <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('pass_percentage') is-invalid @enderror" 
                                           name="pass_percentage" 
                                           value="{{ old('pass_percentage', $assessment->pass_percentage) }}"
                                           min="1" 
                                           max="100" 
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                @error('pass_percentage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max Attempts <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('max_attempts') is-invalid @enderror" 
                                       name="max_attempts" 
                                       value="{{ old('max_attempts', $assessment->max_attempts) }}"
                                       min="1" 
                                       max="10" 
                                       required>
                                @error('max_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Time Limit (Optional)</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('time_limit_minutes') is-invalid @enderror" 
                                           name="time_limit_minutes" 
                                           value="{{ old('time_limit_minutes', $assessment->time_limit_minutes) }}"
                                           min="1" 
                                           placeholder="None">
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                                @error('time_limit_minutes')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Assessment Options</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="hidden" name="randomize_questions" value="0">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       name="randomize_questions" 
                                       id="randomizeQuestions"
                                       value="1"
                                       {{ old('randomize_questions', $assessment->randomize_questions) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="randomizeQuestions">
                                    Randomize Question Order
                                </label>
                            </div>

                            <div class="custom-control custom-checkbox mb-3">
                                <input type="hidden" name="randomize_options" value="0">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       name="randomize_options" 
                                       id="randomizeOptions"
                                       value="1"
                                       {{ old('randomize_options', $assessment->randomize_options) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="randomizeOptions">
                                    Randomize Answer Options
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="hidden" name="show_correct_answers" value="0">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       name="show_correct_answers" 
                                       id="showCorrectAnswers"
                                       value="1"
                                       {{ old('show_correct_answers', $assessment->show_correct_answers) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="showCorrectAnswers">
                                    Show Correct Answers After Submission
                                </label>
                            </div>

                            <div class="custom-control custom-checkbox mb-3">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       name="is_active" 
                                       id="isActive"
                                       value="1"
                                       {{ old('is_active', $assessment->is_active) ? 'checked' : '' }}
                                       @if($assessment->questions->count() === 0) disabled @endif>
                                <label class="custom-control-label" for="isActive">
                                    Assessment is Active
                                </label>
                                @if($assessment->questions->count() === 0)
                                    <small class="text-muted d-block">Add questions before activating</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    @if($assessment->attempts->count() > 0)
                    <div class="alert alert-warning">
                        <i class="icon-info"></i> <strong>Note:</strong> This assessment has {{ $assessment->attempts->count() }} learner attempt(s). 
                        Changing settings may affect ongoing assessments.
                    </div>
                    @endif

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-check"></i> Update Settings
                        </button>
                        <a href="{{ route('admin.assessments.questions.index', $assessment->id) }}" class="btn btn-secondary">
                            <i class="icon-close"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Assessment Info</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td><strong>Questions:</strong></td>
                        <td>{{ $assessment->questions->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Points:</strong></td>
                        <td>{{ $assessment->total_points }}</td>
                    </tr>
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
                    @if($assessment->attempts->count() > 0)
                    <tr>
                        <td><strong>Attempts:</strong></td>
                        <td>{{ $assessment->attempts->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Avg Score:</strong></td>
                        <td>{{ number_format($assessment->attempts->avg('percentage'), 1) }}%</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.assessments.questions.index', $assessment->id) }}" 
                   class="btn btn-primary btn-block mb-2">
                    <i class="icon-list"></i> Manage Questions
                </a>
                <a href="{{ route('admin.weeks.show', $assessment->module_week_id) }}" 
                   class="btn btn-secondary btn-block">
                    <i class="icon-arrow-left"></i> Back to Week
                </a>
            </div>
        </div>
    </div>
</div>
@endsection