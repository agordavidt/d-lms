@extends('layouts.app')
@section('title', 'Edit Program')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('mentor.programs.index') }}">Course Management</a> /
            <a href="{{ route('mentor.programs.show', $program) }}">{{ $program->name }}</a>
        </div>
        <h1>Edit Program</h1>
    </div>
</div>

<div class="container section">
<div style="max-width: 640px;">

    <form method="POST" action="{{ route('mentor.programs.update', $program) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Program Name <span style="color:var(--error)">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $program->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description <span style="color:var(--error)">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="4" maxlength="1000">{{ old('description', $program->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <!-- Redesigned Grid: Combined into an optimized 3-column layouts row -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Duration <span style="color:var(--error)">*</span></label>
                        <input type="text" name="duration" class="form-control @error('duration') is-invalid @enderror"
                               value="{{ old('duration', $program->duration) }}" required>
                        @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price (₦) <span style="color:var(--error)">*</span></label>
                        <input type="number" name="price" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price', $program->price) }}" required>
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" name="discount_percentage" step="0.01" min="0" max="100"
                               class="form-control @error('discount_percentage') is-invalid @enderror" 
                               value="{{ old('discount_percentage', $program->discount_percentage) }}">
                        @error('discount_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group" style="margin-top: 0.5rem;">
                    <label class="form-label">Cover Image</label>
                    @if($program->cover_image)
                    <div style="margin-bottom: 0.75rem;">
                        <img src="{{ asset('storage/' . $program->cover_image) }}"
                             style="height: 100px; border-radius: 6px; object-fit: cover;" alt="Program Cover">
                        <div class="form-hint">Upload a new image to replace this one</div>
                    </div>
                    @endif
                    <input type="file" name="cover_image" accept="image/jpg,image/jpeg,image/png,image/webp" 
                           class="form-control @error('cover_image') is-invalid @enderror">
                    @error('cover_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('mentor.programs.show', $program) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>

</div>
</div>
@endsection