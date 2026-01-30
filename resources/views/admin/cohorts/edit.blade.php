@extends('layouts.admin')

@section('title', 'Edit Cohort')
@section('breadcrumb-parent', 'Cohorts')
@section('breadcrumb-current', 'Edit')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Cohort: {{ $cohort->name }}</h4>
                
                <form action="{{ route('admin.cohorts.update', $cohort) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Program</label>
                            <select class="form-control @error('program_id') is-invalid @enderror" name="program_id" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('program_id', $cohort->program_id) == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>Cohort Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name', $cohort->name) }}" placeholder="e.g., October 2026 Cohort" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Cohort Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                name="code" value="{{ old('code', $cohort->code) }}" placeholder="e.g., FSW-OCT-2026" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>Max Students</label>
                            <input type="number" class="form-control @error('max_students') is-invalid @enderror" 
                                name="max_students" value="{{ old('max_students', $cohort->max_students) }}" required>
                            @error('max_students')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                                <option value="upcoming" {{ old('status', $cohort->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="ongoing" {{ old('status', $cohort->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="completed" {{ old('status', $cohort->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $cohort->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Start Date</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                name="start_date" value="{{ old('start_date', $cohort->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                name="end_date" value="{{ old('end_date', $cohort->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>WhatsApp Link (Optional)</label>
                        <input type="url" class="form-control" name="whatsapp_link" value="{{ old('whatsapp_link', $cohort->whatsapp_link) }}">
                    </div>
                    
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3">{{ old('notes', $cohort->notes) }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary gradient-4">Update Cohort</button>
                        <a href="{{ route('admin.cohorts.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection