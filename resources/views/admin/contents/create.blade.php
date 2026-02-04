@extends('layouts.admin')

@section('title', 'Add Content')
@section('breadcrumb-parent', 'Contents')
@section('breadcrumb-current', 'Create')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Add Content to Week</h4>
            </div>
            <div class="card-body">
                <!-- Context Information -->
                <div class="alert alert-info mb-4">
                    <strong><i class="icon-info"></i> Adding content to:</strong><br>
                    <div class="mt-2">
                        <strong>Program:</strong> {{ $week->programModule->program->name }}<br>
                        <strong>Module:</strong> {{ $week->programModule->title }}<br>
                        <strong>Week:</strong> Week {{ $week->week_number }} - {{ $week->title }}
                    </div>
                </div>

                <form action="{{ route('admin.contents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Hidden field - no dropdown needed -->
                    <input type="hidden" name="module_week_id" value="{{ $week->id }}">

                    <!-- Content Type Selection -->
                    <div class="form-group">
                        <label>Content Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           class="custom-control-input" 
                                           id="typeVideo" 
                                           name="content_type" 
                                           value="video" 
                                           {{ old('content_type') == 'video' ? 'checked' : '' }}
                                           onchange="toggleContentFields()"
                                           required>
                                    <label class="custom-control-label" for="typeVideo">
                                        📹 Video
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           class="custom-control-input" 
                                           id="typePDF" 
                                           name="content_type" 
                                           value="pdf"
                                           {{ old('content_type') == 'pdf' ? 'checked' : '' }}
                                           onchange="toggleContentFields()"
                                           required>
                                    <label class="custom-control-label" for="typePDF">
                                        📄 PDF Document
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           class="custom-control-input" 
                                           id="typeLink" 
                                           name="content_type" 
                                           value="link"
                                           {{ old('content_type') == 'link' ? 'checked' : '' }}
                                           onchange="toggleContentFields()"
                                           required>
                                    <label class="custom-control-label" for="typeLink">
                                        🔗 External Link
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           class="custom-control-input" 
                                           id="typeText" 
                                           name="content_type" 
                                           value="text"
                                           {{ old('content_type') == 'text' ? 'checked' : '' }}
                                           onchange="toggleContentFields()"
                                           required>
                                    <label class="custom-control-label" for="typeText">
                                        📝 Article/Text
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('content_type')
                            <div class="text-danger mt-1"><small>{{ $message }}</small></div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Common Fields -->
                    <div class="form-group">
                        <label>Content Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               name="title" 
                               value="{{ old('title') }}"
                               placeholder="e.g., Introduction to Variables"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  rows="2"
                                  placeholder="Brief description of this content">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type-Specific Fields -->
                    <div id="videoFields" style="display:none;">
                        <h5 class="mb-3">Video Details</h5>
                        <div class="form-group">
                            <label>Video URL <span class="text-danger">*</span></label>
                            <input type="url" 
                                   class="form-control @error('video_url') is-invalid @enderror" 
                                   name="video_url" 
                                   value="{{ old('video_url') }}"
                                   placeholder="https://youtube.com/watch?v=...">
                            <small class="text-muted">YouTube, Vimeo, or other video platform URL</small>
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Duration (minutes)</label>
                            <input type="number" 
                                   class="form-control @error('video_duration_minutes') is-invalid @enderror" 
                                   name="video_duration_minutes" 
                                   value="{{ old('video_duration_minutes') }}"
                                   min="1"
                                   placeholder="e.g., 15">
                            @error('video_duration_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="pdfFields" style="display:none;">
                        <h5 class="mb-3">PDF Document</h5>
                        <div class="form-group">
                            <label>Upload PDF File <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input @error('file') is-invalid @enderror" 
                                       name="file" 
                                       accept=".pdf"
                                       id="pdfFile">
                                <label class="custom-file-label" for="pdfFile">Choose PDF file</label>
                            </div>
                            <small class="text-muted">Maximum file size: 10MB</small>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="linkFields" style="display:none;">
                        <h5 class="mb-3">External Resource</h5>
                        <div class="form-group">
                            <label>External URL <span class="text-danger">*</span></label>
                            <input type="url" 
                                   class="form-control @error('external_url') is-invalid @enderror" 
                                   name="external_url" 
                                   value="{{ old('external_url') }}"
                                   placeholder="https://example.com/resource">
                            <small class="text-muted">Link to external website, article, or resource</small>
                            @error('external_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="textFields" style="display:none;">
                        <h5 class="mb-3">Article Content</h5>
                        <div class="form-group">
                            <label>Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('text_content') is-invalid @enderror" 
                                      name="text_content" 
                                      rows="10"
                                      id="textContentEditor">{{ old('text_content') }}</textarea>
                            <small class="text-muted">Write or paste your article content here</small>
                            @error('text_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Settings -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="isRequired" 
                                           name="is_required"
                                           {{ old('is_required', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="isRequired">
                                        <strong>Required content</strong>
                                        <br><small class="text-muted">Learners must complete this to unlock next week</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" 
                                        name="status" 
                                        required>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg">
                             Add Content
                        </button>
                        <a href="{{ route('admin.weeks.show', $week->id) }}" class="btn btn-secondary btn-lg">
                             Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
<script>
// File input label update
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('pdfFile');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Choose PDF file';
            this.nextElementSibling.textContent = fileName;
        });
    }
    
    // Show correct fields on page load if type was selected
    toggleContentFields();
});

function toggleContentFields() {
    const contentType = document.querySelector('input[name="content_type"]:checked');
    
    // Hide all
    document.getElementById('videoFields').style.display = 'none';
    document.getElementById('pdfFields').style.display = 'none';
    document.getElementById('linkFields').style.display = 'none';
    document.getElementById('textFields').style.display = 'none';
    
    if (!contentType) return;
    
    // Show selected
    const typeValue = contentType.value;
    if (typeValue === 'video') {
        document.getElementById('videoFields').style.display = 'block';
    } else if (typeValue === 'pdf') {
        document.getElementById('pdfFields').style.display = 'block';
    } else if (typeValue === 'link') {
        document.getElementById('linkFields').style.display = 'block';
    } else if (typeValue === 'text') {
        document.getElementById('textFields').style.display = 'block';
        initTinyMCE();
    }
}

function initTinyMCE() {
    if (tinymce.get('textContentEditor')) {
        return; // Already initialized
    }
    
    tinymce.init({
        selector: '#textContentEditor',
        height: 400,
        menubar: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code'
    });
}
</script>
@endpush