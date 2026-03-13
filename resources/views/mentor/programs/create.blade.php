@extends('mentor.layouts.app')
@section('title', 'New Program')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('mentor.programs.index') }}">Course Management</a></div>
        <h1>New Program</h1>
    </div>
</div>

<div class="container section">
<div style="max-width: 640px;">

    <form method="POST" action="{{ route('mentor.programs.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-body">

                <div class="form-group">
                    <label class="form-label">Program Name <span style="color:var(--error)">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" placeholder="e.g. Full-Stack Web Development" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description <span style="color:var(--error)">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                              rows="4" maxlength="1000" placeholder="What will learners achieve in this program?">{{ old('description') }}</textarea>
                    <div class="form-hint">Max 1000 characters</div>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Duration <span style="color:var(--error)">*</span></label>
                        <input type="text" name="duration" class="form-control @error('duration') is-invalid @enderror"
                               value="{{ old('duration') }}" placeholder="e.g. 8 Weeks" required>
                        @error('duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price (₦) <span style="color:var(--error)">*</span></label>
                        <input type="number" name="price" step="0.01" min="0"
                               class="form-control @error('price') is-invalid @enderror"
                               value="{{ old('price') }}" placeholder="50000" required>
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Discount (%)</label>
                        <input type="number" name="discount_percentage" step="0.01" min="0" max="100"
                               class="form-control" value="{{ old('discount_percentage', 0) }}" placeholder="0">
                        <div class="form-hint">Leave 0 for no discount</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Minimum Passing Average (%)</label>
                        <input type="number" name="min_passing_average" min="0" max="100"
                               class="form-control" value="{{ old('min_passing_average', 70) }}">
                        <div class="form-hint">Required average to graduate</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Cover Image</label>
                    <input type="file" name="cover_image" accept="image/jpg,image/jpeg,image/png,image/webp"
                           class="form-control @error('cover_image') is-invalid @enderror">
                    <div class="form-hint">JPG, PNG or WebP · Max 2MB · Recommended 1280×720px</div>
                    @error('cover_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem;">
            <button type="submit" class="btn btn-primary">Create Program</button>
            <a href="{{ route('mentor.programs.index') }}" class="btn btn-ghost">Cancel</a>
        </div>

    </form>
</div>
</div>
@endsection