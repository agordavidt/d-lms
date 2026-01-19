@extends('layouts.admin')

@section('title', 'Edit Content')
@section('breadcrumb-parent', 'Content')
@section('breadcrumb-current', 'Edit')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Edit Content</h4>

                <form action="{{ route('mentor.contents.update', $content->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Week Info (Read-only) -->
                    <div class="alert alert-info">
                        <strong>Program:</strong> {{ $content->moduleWeek->programModule->program->name }}<br>
                        <strong>Module:</strong> {{ $content->moduleWeek->programModule->title }}<br>
                        <strong>Week:</strong> Week {{ $content->moduleWeek->week_number }} - {{ $content->moduleWeek->title }}
                    </div>

                    <input type="hidden" name="module_week_id" value="{{ $content->module_week_id }}">

                    <!-- Content Type (Read-only) -->
                    <div class="form-group">
                        <label>Content Type</label>
                        <input type="text" class="form-control" value="{{ $content->type_display }}" readonly>
                        <input type="hidden" name="content_type" value="{{ $content->content_type }}">
                    </div>

                    <!-- Common Fields -->
                    <div class="form-group">
                        <label>Content Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title', $content->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description', $content->description) }}</textarea>
                    </div>

                    <!-- Type-Specific Fields -->
                    @if($content->content_type === 'video')
                        <div class="form-group">
                            <label>Video URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="video_url" 
                                   value="{{ old('video_url', $content->video_url) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Duration (minutes)</label>
                            <input type="number" class="form-control" name="video_duration_minutes" 
                                   value="{{ old('video_duration_minutes', $content->video_duration_minutes) }}" min="1">
                        </div>

                    @elseif($content->content_type === 'pdf')
                        <div class="form-group">
                            <label>Current File</label>
                            <p class="form-control-plaintext">
                                <a href="{{ $content->file_url }}" target="_blank">{{ $content->metadata['original_name'] ?? 'View PDF' }}</a>
                                ({{ $content->file_size }})
                            </p>
                        </div>
                        <div class="form-group">
                            <label>Upload New PDF (optional)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="file" accept=".pdf">
                                <label class="custom-file-label">Choose new file</label>
                            </div>
                            <small class="text-muted">Leave empty to keep current file. Max: 10MB</small>
                        </div>

                    @elseif($content->content_type === 'link')
                        <div class="form-group">
                            <label>External URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="external_url" 
                                   value="{{ old('external_url', $content->external_url) }}" required>
                        </div>

                    @else
                        <div class="form-group">
                            <label>Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="text_content" rows="12" 
                                      id="textEditor">{{ old('text_content', $content->text_content) }}</textarea>
                        </div>
                    @endif

                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isRequired" 
                                       name="is_required" {{ old('is_required', $content->is_required) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="isRequired">
                                    Required content
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="draft" {{ old('status', $content->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $content->status) == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Content</button>
                        <a href="{{ route('mentor.contents.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce/min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').html(fileName);
    });
    
    @if($content->content_type === 'text')
        tinymce.init({
            selector: '#textEditor',
            height: 400,
            menubar: false,
            plugins: 'lists link',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link'
        });
    @endif
});
</script>
@endpush