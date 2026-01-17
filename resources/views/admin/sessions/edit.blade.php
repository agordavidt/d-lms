@extends('layouts.admin')

@section('title', 'Schedule Session')
@section('breadcrumb-parent', 'Sessions')
@section('breadcrumb-current', 'Edit')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Schedule New Live Session</h4>
                
                <form action="{{ route('admin.sessions.update') }}" method="PUT">
                    @csrf
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Program</label>
                            <select class="form-control @error('program_id') is-invalid @enderror" 
                                name="program_id" id="programSelect" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>Cohort</label>
                            <select class="form-control @error('cohort_id') is-invalid @enderror" 
                                name="cohort_id" id="cohortSelect" required>
                                <option value="">Select Cohort</option>
                                @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}" data-program="{{ $cohort->program_id }}">
                                    {{ $cohort->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('cohort_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Session Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>Session Type</label>
                            <select class="form-control @error('session_type') is-invalid @enderror" name="session_type" required>
                                <option value="live_class">Live Class</option>
                                <option value="workshop">Workshop</option>
                                <option value="q&a">Q&A Session</option>
                                <option value="assessment">Assessment</option>
                            </select>
                            @error('session_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Start Date & Time</label>
                            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" 
                                name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>End Date & Time</label>
                            <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" 
                                name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Mentor/Instructor</label>
                            <select class="form-control" name="mentor_id">
                                <option value="">Select Mentor</option>
                                @foreach($mentors as $mentor)
                                <option value="{{ $mentor->id }}">{{ $mentor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>Google Meet Link (Optional)</label>
                            <input type="url" class="form-control" name="meet_link" value="{{ old('meet_link') }}">
                            <small class="text-muted">Add meeting link or leave blank to generate later</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary gradient-1">Schedule Session</button>
                        <a href="{{ route('admin.sessions.calendar') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Filter cohorts by selected program
$('#programSelect').on('change', function() {
    var programId = $(this).val();
    $('#cohortSelect option').hide();
    $('#cohortSelect option[value=""]').show();
    
    if (programId) {
        $('#cohortSelect option[data-program="' + programId + '"]').show();
    } else {
        $('#cohortSelect option').show();
    }
    
    $('#cohortSelect').val('');
});
</script>
@endpush