@extends('layouts.admin')

@section('title', 'Edit Program')
@section('breadcrumb-parent', 'Programs')
@section('breadcrumb-current', 'Edit')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Program: {{ $program->name }}</h4>
                
                <form action="{{ route('admin.programs.update', $program) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Program Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name', $program->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>Duration</label>
                            <input type="text" class="form-control @error('duration') is-invalid @enderror" 
                                name="duration" value="{{ old('duration', $program->duration) }}" required>
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Price (₦)</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                name="price" value="{{ old('price', $program->price) }}" step="0.01" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>Discount %</label>
                            <input type="number" class="form-control" 
                                name="discount_percentage" value="{{ old('discount_percentage', $program->discount_percentage) }}">
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select class="form-control" name="status" required>
                                <option value="draft" {{ $program->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ $program->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $program->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" required>{{ old('description', $program->description) }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Overview</label>
                        <textarea class="form-control" name="overview" rows="3">{{ old('overview', $program->overview) }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary gradient-1">Update Program</button>
                        <a href="{{ route('admin.programs.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection