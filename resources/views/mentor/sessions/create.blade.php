@extends('layouts.admin')

@section('title', 'Schedule New Session')
@section('breadcrumb-parent', 'Sessions')
@section('breadcrumb-current', 'Create')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="icon-calendar text-primary mr-2"></i>Schedule New Session
                    </h5>
                    <a href="{{ route('mentor.sessions.calendar') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="icon-arrow-left mr-1"></i>Back to Calendar
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('mentor.sessions.store') }}" method="POST">
                    @csrf

                    <!-- Program Selection -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Program <span class="text-danger">*</span></label>
                        <select name="program_id" 
                            class="form-control @error('program_id') is-invalid @enderror" 
                            required 
                            id="programSelect">
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('program_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cohort Selection -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Cohort <span class="text-danger">*</span></label>
                        <select name="cohort_id" 
                            class="form-control @error('cohort_id') is-invalid @enderror" 
                            required
                            id="cohortSelect">
                            <option value="">Select Cohort</option>
                            @foreach($cohorts as $cohort)
                            <option value="{{ $cohort->id }}" 
                                data-program="{{ $cohort->program_id }}"
                                {{ old('cohort_id') == $cohort->id ? 'selected' : '' }}>
                                {{ $cohort->name }} ({{ $cohort->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('cohort_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Session Title -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Session Title <span class="text-danger">*</span></label>
                        <input type="text" 
                            name="title" 
                            class="form-control @error('title') is-invalid @enderror" 
                            value="{{ old('title') }}"
                            placeholder="e.g., Module 3: Advanced CSS Techniques"
                            required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Session Type -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Session Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                        id="typeLiveClass" 
                                        name="session_type" 
                                        class="custom-control-input" 
                                        value="live_class"
                                        {{ old('session_type', 'live_class') == 'live_class' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeLiveClass">
                                        <i class="icon-video text-primary mr-1"></i>Live Class
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                        id="typeWorkshop" 
                                        name="session_type" 
                                        class="custom-control-input" 
                                        value="workshop"
                                        {{ old('session_type') == 'workshop' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeWorkshop">
                                        <i class="icon-wrench text-info mr-1"></i>Workshop
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                        id="typeQA" 
                                        name="session_type" 
                                        class="custom-control-input" 
                                        value="q&a"
                                        {{ old('session_type') == 'q&a' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeQA">
                                        <i class="icon-question text-success mr-1"></i>Q&A
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                        id="typeAssessment" 
                                        name="session_type" 
                                        class="custom-control-input" 
                                        value="assessment"
                                        {{ old('session_type') == 'assessment' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeAssessment">
                                        <i class="icon-doc text-warning mr-1"></i>Assessment
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('session_type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Date and Time -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-semibold mb-2">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                name="start_time" 
                                class="form-control @error('start_time') is-invalid @enderror" 
                                value="{{ old('start_time') }}"
                                required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label class="font-weight-semibold mb-2">End Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                name="end_time" 
                                class="form-control @error('end_time') is-invalid @enderror" 
                                value="{{ old('end_time') }}"
                                required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Google Meet Link -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Google Meet Link</label>
                        <input type="url" 
                            name="meet_link" 
                            class="form-control @error('meet_link') is-invalid @enderror" 
                            value="{{ old('meet_link') }}"
                            placeholder="https://meet.google.com/xxx-xxxx-xxx">
                        <small class="text-muted">
                            <i class="icon-info mr-1"></i>
                            Create a meeting at <a href="https://meet.google.com" target="_blank">meet.google.com</a> and paste the link here
                        </small>
                        @error('meet_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Description</label>
                        <textarea name="description" 
                            class="form-control @error('description') is-invalid @enderror" 
                            rows="4"
                            placeholder="What will students learn in this session?">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('mentor.sessions.calendar') }}" class="btn btn-outline-secondary">
                            <i class="icon-close mr-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="icon-check mr-2"></i>Schedule Session
                        </button>
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
    const programSelect = document.getElementById('programSelect');
    const cohortSelect = document.getElementById('cohortSelect');
    const cohortOptions = Array.from(cohortSelect.options);

    programSelect.addEventListener('change', function() {
        const selectedProgram = this.value;
        
        // Reset cohort select
        cohortSelect.innerHTML = '<option value="">Select Cohort</option>';
        
        if (selectedProgram) {
            // Filter cohorts by selected program
            cohortOptions.forEach(option => {
                if (option.dataset.program === selectedProgram) {
                    cohortSelect.appendChild(option.cloneNode(true));
                }
            });
        } else {
            // Show all cohorts if no program selected
            cohortOptions.forEach(option => {
                if (option.value) {
                    cohortSelect.appendChild(option.cloneNode(true));
                }
            });
        }
    });

    // Set minimum datetime to now
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const minDateTime = now.toISOString().slice(0, 16);
    
    document.querySelector('input[name="start_time"]').min = minDateTime;
    document.querySelector('input[name="end_time"]').min = minDateTime;

    // Auto-update end time when start time changes (add 2 hours)
    document.querySelector('input[name="start_time"]').addEventListener('change', function() {
        const startTime = new Date(this.value);
        startTime.setHours(startTime.getHours() + 2);
        
        const endTimeInput = document.querySelector('input[name="end_time"]');
        endTimeInput.min = this.value;
        
        if (!endTimeInput.value || new Date(endTimeInput.value) <= new Date(this.value)) {
            endTimeInput.value = startTime.toISOString().slice(0, 16);
        }
    });
});
</script>
@endpush