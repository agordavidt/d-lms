@extends('layouts.admin')

@section('title', 'Create Program')
@section('breadcrumb-parent', 'Programs')
@section('breadcrumb-current', 'Create')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Create New Program</h4>
                
                <form action="{{ route('admin.programs.store') }}" method="POST" class="form-valide">
                    @csrf
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Program Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label>Duration</label>
                            <input type="text" class="form-control @error('duration') is-invalid @enderror" 
                                name="duration" value="{{ old('duration') }}" placeholder="e.g., 12 Weeks" required>
                            @error('duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Price (₦)</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                name="price" value="{{ old('price') }}" step="0.01" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>Discount % (One-time Payment)</label>
                            <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" 
                                name="discount_percentage" value="{{ old('discount_percentage', 10) }}" step="0.01" max="100">
                            @error('discount_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                            name="description" rows="3" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Overview (Optional)</label>
                        <textarea class="form-control" name="overview" rows="3">{{ old('overview') }}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary gradient-1">Create Program</button>
                        <a href="{{ route('admin.programs.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection