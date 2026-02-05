@extends('layouts.admin')

@section('title', 'Create Assessment')
@section('breadcrumb-parent', 'Assessments')
@section('breadcrumb-current', 'Create')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Create Assessment</h4>
            </div>
            <div class="card-body">
                <!-- Context Information -->
                <div class="alert alert-info mb-4">
                    <strong><i class="icon-info"></i> Creating assessment for:</strong><br>
                    <div class="mt-2">
                        <strong>Program:</strong> {{ $week->programModule->program->name }}<br>
                        <strong>Module:</strong> {{ $week->programModule->title }}<br>
                        <strong>Week:</strong> Week {{ $week->week_number }} - {{ $week->title }}
                    </div>
                </div>

                <form action="{{ route('admin.assessments.store') }}" method="POST">
                    @csrf
                    
                    <input type="hidden" name="module_week_id" value="{{ $week->id }}">

                    <div class="form-group">
                        <label>Assessment Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               name="title" 
                               value="{{ old('title', 'Week ' . $week->week_number . ' Assessment') }}" 
                               placeholder="e.g., Week 1 Assessment" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Instructions for Learners</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Provide instructions or context for this assessment">{{ old('description') }}</textarea>
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
                                           value="{{ old('pass_percentage', $week->assessment_pass_percentage ?? 70) }}"
                                           min="1" 
                                           max="100" 
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <small class="text-muted">Minimum score to pass</small>
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
                                       value="{{ old('max_attempts', 3) }}"
                                       min="1" 
                                       max="10" 
                                       required>
                                <small class="text-muted">How many tries allowed</small>
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
                                           value="{{ old('time_limit_minutes') }}"
                                           min="1" 
                                           placeholder="None">
                                    <div class="input-group-append">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                                <small class="text-muted">Leave empty for no limit</small>
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
                                       {{ old('randomize_questions') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="randomizeQuestions">
                                    Randomize Question Order
                                </label>
                                <small class="text-muted d-block">Questions appear in different order for each attempt</small>
                            </div>

                            <div class="custom-control custom-checkbox mb-3">
                                <input type="hidden" name="randomize_options" value="0">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       name="randomize_options" 
                                       id="randomizeOptions"
                                       value="1"
                                       {{ old('randomize_options') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="randomizeOptions">
                                    Randomize Answer Options
                                </label>
                                <small class="text-muted d-block">Shuffle A, B, C, D options for multiple choice questions</small>
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
                                       {{ old('show_correct_answers', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="showCorrectAnswers">
                                    Show Correct Answers After Submission
                                </label>
                                <small class="text-muted d-block">Learners can review correct answers and explanations</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="alert alert-warning">
                        <i class="icon-info"></i> <strong>Note:</strong> Assessment will be created as inactive. 
                        Add questions first, then activate to make it visible to learners.
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                             Create Assessment & Add Questions
                        </button>
                        <a href="{{ route('admin.weeks.show', $week->id) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Tips</h4>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Set pass percentage based on difficulty (70-80% recommended)</li>
                    <li class="mb-2">Allow 2-3 attempts so learners can learn from mistakes</li>
                    <li class="mb-2">Use time limits for timed assessments (optional)</li>
                    <li class="mb-2">Randomization prevents cheating but makes grading consistent</li>
                    <li>Showing answers helps learning but may reduce motivation to try again</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Week Context</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td><strong>Week:</strong></td>
                        <td>{{ $week->week_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Contents:</strong></td>
                        <td>{{ $week->contents->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Required:</strong></td>
                        <td>{{ $week->contents->where('is_required', true)->count() }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection