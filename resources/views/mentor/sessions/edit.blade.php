@extends('layouts.admin')

@section('title', 'Edit Session')
@section('breadcrumb-parent', 'Sessions')
@section('breadcrumb-current', 'Edit')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="icon-pencil text-primary mr-2"></i>Edit Session
                    </h5>
                    <a href="{{ route('mentor.sessions.calendar') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="icon-arrow-left mr-1"></i>Back
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('mentor.sessions.update', $session) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Program Selection -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Program <span class="text-danger">*</span></label>
                        <select name="program_id" class="form-control @error('program_id') is-invalid @enderror" required>
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id', $session->program_id) == $program->id ? 'selected' : '' }}>
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
                        <select name="cohort_id" class="form-control @error('cohort_id') is-invalid @enderror" required>
                            <option value="">Select Cohort</option>
                            @foreach($cohorts as $cohort)
                            <option value="{{ $cohort->id }}" {{ old('cohort_id', $session->cohort_id) == $cohort->id ? 'selected' : '' }}>
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
                            value="{{ old('title', $session->title) }}"
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
                                    <input type="radio" id="typeLiveClass" name="session_type" class="custom-control-input" value="live_class" {{ old('session_type', $session->session_type) == 'live_class' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeLiveClass">Live Class</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeWorkshop" name="session_type" class="custom-control-input" value="workshop" {{ old('session_type', $session->session_type) == 'workshop' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeWorkshop">Workshop</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeQA" name="session_type" class="custom-control-input" value="q&a" {{ old('session_type', $session->session_type) == 'q&a' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeQA">Q&A</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="typeAssessment" name="session_type" class="custom-control-input" value="assessment" {{ old('session_type', $session->session_type) == 'assessment' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeAssessment">Assessment</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date and Time -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="font-weight-semibold mb-2">Start Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" 
                                name="start_time" 
                                class="form-control @error('start_time') is-invalid @enderror" 
                                value="{{ old('start_time', $session->start_time->format('Y-m-d\TH:i')) }}"
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
                                value="{{ old('end_time', $session->end_time->format('Y-m-d\TH:i')) }}"
                                required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Session Status -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="scheduled" {{ old('status', $session->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="ongoing" {{ old('status', $session->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status', $session->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $session->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Google Meet Link -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Google Meet Link</label>
                        <input type="url" 
                            name="meet_link" 
                            class="form-control @error('meet_link') is-invalid @enderror" 
                            value="{{ old('meet_link', $session->meet_link) }}"
                            placeholder="https://meet.google.com/xxx-xxxx-xxx">
                        @error('meet_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Recording Link -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Recording Link</label>
                        <input type="url" 
                            name="recording_link" 
                            class="form-control @error('recording_link') is-invalid @enderror" 
                            value="{{ old('recording_link', $session->recording_link) }}"
                            placeholder="https://drive.google.com/file/d/...">
                        <small class="text-muted">Add the session recording URL after the class</small>
                        @error('recording_link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Description</label>
                        <textarea name="description" 
                            class="form-control @error('description') is-invalid @enderror" 
                            rows="4">{{ old('description', $session->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Session Notes -->
                    <div class="mb-4">
                        <label class="font-weight-semibold mb-2">Session Notes</label>
                        <textarea name="notes" 
                            class="form-control @error('notes') is-invalid @enderror" 
                            rows="4"
                            placeholder="Add notes about what was covered, homework assigned, etc.">{{ old('notes', $session->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="icon-trash mr-1"></i>Delete Session
                        </button>
                        <div>
                            <a href="{{ route('mentor.sessions.calendar') }}" class="btn btn-outline-secondary mr-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="icon-check mr-2"></i>Update Session
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Delete Form (Hidden) -->
                <form id="deleteForm" action="{{ route('mentor.sessions.destroy', $session) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this session? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush