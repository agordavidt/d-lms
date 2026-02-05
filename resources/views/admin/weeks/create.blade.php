@extends('layouts.admin')

@section('title', 'Add Week')
@section('breadcrumb-parent', 'Weeks')
@section('breadcrumb-current', 'Create')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Add Week to Module</h4>
            </div>
            <div class="card-body">
                <!-- Context Information -->
                <div class="alert alert-info mb-4">
                    <strong><i class="icon-info"></i> Adding week to:</strong><br>
                    <div class="mt-2">
                        <strong>Program:</strong> {{ $module->program->name }}<br>
                        <strong>Module:</strong> {{ $module->title }}
                    </div>
                </div>

                <form action="{{ route('admin.weeks.store') }}" method="POST">
                    @csrf
                    
                    <!-- Hidden field - no dropdown needed -->
                    <input type="hidden" name="program_module_id" value="{{ $module->id }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Week Number <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('week_number') is-invalid @enderror" 
                                       name="week_number" 
                                       value="{{ old('week_number', $suggestedWeekNumber) }}" 
                                       min="1" 
                                       placeholder="e.g., 1" 
                                       required>
                                <small class="text-muted">Suggested: Week {{ $suggestedWeekNumber }}</small>
                                @error('week_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" 
                                        name="status" 
                                        required>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Week Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               name="title" 
                               value="{{ old('title') }}"
                               placeholder="e.g., Introduction to Data Analytics" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Brief description of what will be covered this week">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" 
                                   class="custom-control-input" 
                                   name="has_assessment" 
                                   id="hasAssessment"
                                   {{ old('has_assessment') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="hasAssessment">
                                This week has an assessment
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="assessmentPassPercentage" style="display: {{ old('has_assessment') ? 'block' : 'none' }};">
                        <label>Assessment Pass Percentage</label>
                        <input type="number" 
                               class="form-control" 
                               name="assessment_pass_percentage" 
                               value="{{ old('assessment_pass_percentage', 70) }}"
                               min="0" 
                               max="100">
                        <small class="text-muted">Minimum percentage required to pass (default: 70%)</small>
                    </div>

                    <div class="form-group">
                        <label>Learning Outcomes</label>
                        <small class="text-muted d-block mb-2">What will learners achieve by the end of this week?</small>
                        <div id="outcomes-container">
                            @if(old('learning_outcomes'))
                                @foreach(old('learning_outcomes') as $outcome)
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                               class="form-control" 
                                               name="learning_outcomes[]" 
                                               value="{{ $outcome }}"
                                               placeholder="Enter learning outcome">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-danger" onclick="removeOutcome(this)">
                                                <i class="icon-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="input-group mb-2">
                                    <input type="text" 
                                           class="form-control" 
                                           name="learning_outcomes[]" 
                                           placeholder="e.g., Understand basic data types and structures">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger" onclick="removeOutcome(this)">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addOutcome()">
                            <i class="icon-plus"></i> Add Another Outcome
                        </button>
                    </div>

                    <hr class="my-4">

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                             Create Week
                        </button>
                        <a href="{{ route('admin.modules.show', $module->id) }}" class="btn btn-secondary">
                             Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const assessmentCheckbox = document.getElementById('hasAssessment');
    const assessmentPercentage = document.getElementById('assessmentPassPercentage');
    
    assessmentCheckbox.addEventListener('change', function() {
        assessmentPercentage.style.display = this.checked ? 'block' : 'none';
    });
});

function addOutcome() {
    const container = document.getElementById('outcomes-container');
    const newOutcome = document.createElement('div');
    newOutcome.className = 'input-group mb-2';
    newOutcome.innerHTML = `
        <input type="text" 
               class="form-control" 
               name="learning_outcomes[]" 
               placeholder="Enter learning outcome">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="removeOutcome(this)">
                <i class="icon-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(newOutcome);
}

function removeOutcome(btn) {
    const container = document.getElementById('outcomes-container');
    if (container.children.length > 1) {
        btn.closest('.input-group').remove();
    } else {
        toastr.warning('At least one learning outcome is recommended');
    }
}
</script>
@endpush