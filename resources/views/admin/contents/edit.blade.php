@extends('layouts.admin')

@section('title', 'Edit Content')
@section('breadcrumb-parent', 'Contents')
@section('breadcrumb-current', 'Edit')

@push('styles')
<!-- Quill Editor Stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-container {
        font-family: 'Inter', sans-serif;
        font-size: 14px;
    }
    .ql-editor {
        min-height: 400px;
    }
    .ql-editor.ql-blank::before {
        font-style: normal;
        color: #94a3b8;
    }
    
    .content-type-badge {
        font-size: 1rem;
        padding: 8px 16px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Edit Content</h4>
                <div>
                    <a href="{{ route('admin.weeks.show', $content->module_week_id) }}" class="btn btn-secondary">
                        Back to Week
                    </a>
                    <a href="{{ route('admin.contents.index') }}" class="btn btn-outline-secondary">
                        All Contents
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Context Information -->
                <div class="alert alert-info mb-4">
                    <strong>Editing content in:</strong><br>
                    <strong>Program:</strong> {{ $content->moduleWeek->programModule->program->name }}<br>
                    <strong>Module:</strong> {{ $content->moduleWeek->programModule->title }}<br>
                    <strong>Week:</strong> Week {{ $content->moduleWeek->week_number }} - {{ $content->moduleWeek->title }}
                </div>

                <!-- Content Type Display (Cannot be changed) -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Content Type</label>
                    <div>
                        <span class="badge content-type-badge 
                            {{ $content->content_type == 'video' ? 'bg-primary' : '' }}
                            {{ $content->content_type == 'pdf' ? 'bg-danger' : '' }}
                            {{ $content->content_type == 'link' ? 'bg-success' : '' }}
                            {{ $content->content_type == 'text' ? 'bg-info' : '' }}">
                            {{ $content->type_display }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('admin.contents.update', $content->id) }}" method="POST" enctype="multipart/form-data" id="contentForm">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" name="module_week_id" value="{{ $content->module_week_id }}">

                    <!-- Common Fields -->
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Content Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               name="title" 
                               value="{{ old('title', $content->title) }}"
                               placeholder="e.g., Introduction to Variables"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  rows="2"
                                  placeholder="Brief description of this content">{{ old('description', $content->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type-Specific Fields -->
                    
                    @if($content->content_type == 'video')
                    <!-- Video Fields -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Video Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Video URL <span class="text-danger">*</span></label>
                                <input type="url" 
                                       class="form-control @error('video_url') is-invalid @enderror" 
                                       name="video_url" 
                                       value="{{ old('video_url', $content->video_url) }}"
                                       placeholder="https://youtube.com/watch?v=..."
                                       required>
                                @error('video_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Duration (minutes)</label>
                                <input type="number" 
                                       class="form-control @error('video_duration_minutes') is-invalid @enderror" 
                                       name="video_duration_minutes" 
                                       value="{{ old('video_duration_minutes', $content->video_duration_minutes) }}"
                                       min="1"
                                       placeholder="e.g., 15">
                                @error('video_duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($content->content_type == 'pdf')
                    <!-- PDF Fields -->
                    <div class="card border-danger mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">PDF Document</h5>
                        </div>
                        <div class="card-body">
                            @if($content->file_path)
                            <div class="alert alert-secondary mb-3">
                                <strong>Current File:</strong> 
                                <a href="{{ $content->file_url }}" target="_blank">
                                    {{ $content->metadata['original_name'] ?? 'Download PDF' }}
                                </a>
                                ({{ $content->file_size }})
                            </div>
                            @endif
                            
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">
                                    {{ $content->file_path ? 'Replace PDF File' : 'Upload PDF File' }}
                                    @if(!$content->file_path) <span class="text-danger">*</span> @endif
                                </label>
                                <input type="file" 
                                       class="form-control @error('file') is-invalid @enderror" 
                                       name="file" 
                                       accept=".pdf"
                                       id="pdfFile"
                                       {{ !$content->file_path ? 'required' : '' }}>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($content->content_type == 'link')
                    <!-- Link Fields -->
                    <div class="card border-success mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">External Resource</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">External URL <span class="text-danger">*</span></label>
                                <input type="url" 
                                       class="form-control @error('external_url') is-invalid @enderror" 
                                       name="external_url" 
                                       value="{{ old('external_url', $content->external_url) }}"
                                       placeholder="https://example.com/resource"
                                       required>
                                @error('external_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($content->content_type == 'text')
                    <!-- Text/Article Fields with Quill Editor -->
                    <div class="card border-info mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Article Content</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Content <span class="text-danger">*</span></label>
                                
                                <!-- Quill Editor Container -->
                                <div id="quillEditor" style="background: white;"></div>
                                
                                <!-- Hidden input to store Quill content -->
                                <input type="hidden" name="text_content" id="textContentHidden" value="{{ old('text_content', $content->text_content) }}">
                                
                                @error('text_content')
                                    <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    <hr class="my-4">

                    <!-- Settings -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="isRequired" 
                                           name="is_required"
                                           {{ old('is_required', $content->is_required) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="isRequired">
                                        Required Content
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        name="status" 
                                        required>
                                    <option value="draft" {{ old('status', $content->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $content->status) == 'published' ? 'selected' : '' }}>Published</option>
                                    
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Metadata -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <small class="text-muted">
                                <strong>Created:</strong> {{ $content->created_at->format('M d, Y \a\t h:i A') }} by {{ $content->creator->name ?? 'Unknown' }}
                                @if($content->updated_at != $content->created_at)
                                <br><strong>Last Updated:</strong> {{ $content->updated_at->format('M d, Y \a\t h:i A') }}
                                @endif
                            </small>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Update Content
                        </button>
                        <a href="{{ route('admin.weeks.show', $content->module_week_id) }}" class="btn btn-secondary btn-lg">
                            Cancel
                        </a>
                        <button type="button" class="btn btn-danger btn-lg float-end" onclick="deleteContent()">
                            Delete Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Quill Editor JavaScript -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
let quillEditor = null;

@if($content->content_type == 'text')
// Initialize Quill editor for text content
document.addEventListener('DOMContentLoaded', function() {
    initQuillEditor();
});

function initQuillEditor() {
    if (quillEditor) {
        return; // Already initialized
    }
    
    // Initialize Quill
    quillEditor = new Quill('#quillEditor', {
        theme: 'snow',
        placeholder: 'Start writing your article content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    });
    
    // Load existing content
    const existingContent = document.getElementById('textContentHidden').value;
    if (existingContent) {
        quillEditor.root.innerHTML = existingContent;
    }
    
    // Sync content on change
    quillEditor.on('text-change', function() {
        document.getElementById('textContentHidden').value = quillEditor.root.innerHTML;
    });
    
    // Also sync immediately after initialization
    document.getElementById('textContentHidden').value = quillEditor.root.innerHTML;
    
    // Custom image handler
    const toolbar = quillEditor.getModule('toolbar');
    toolbar.addHandler('image', imageHandler);
}

// Custom image handler for Quill
function imageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
        const file = input.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('Image size should not exceed 2MB');
            return;
        }

        const range = quillEditor.getSelection(true);
        quillEditor.insertText(range.index, 'Uploading image...');
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('{{ route("admin.contents.upload-image") }}', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            quillEditor.deleteText(range.index, 'Uploading image...'.length);

            if (result.location) {
                quillEditor.insertEmbed(range.index, 'image', result.location);
                quillEditor.setSelection(range.index + 1);
            } else {
                alert('Image upload failed: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Upload error:', error);
            quillEditor.deleteText(range.index, 'Uploading image...'.length);
            alert('Failed to upload image. Please try again.');
        }
    };
}

// Form submission validation
document.getElementById('contentForm').addEventListener('submit', function(e) {
    if (quillEditor) {
        const content = quillEditor.root.innerHTML;
        document.getElementById('textContentHidden').value = content;
        
        const textOnly = quillEditor.getText().trim();
        if (textOnly.length === 0) {
            e.preventDefault();
            alert('Please add some content to the article.');
            return false;
        }
    }
    return true;
});
@endif

// Delete content function
function deleteContent() {
    if (confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
        fetch('{{ route("admin.contents.destroy", $content->id) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("admin.weeks.show", $content->module_week_id) }}';
            } else {
                alert('Failed to delete content: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the content.');
        });
    }
}
</script>
@endpush