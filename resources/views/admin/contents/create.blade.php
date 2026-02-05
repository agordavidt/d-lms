@extends('layouts.admin')

@section('title', 'Add Content')
@section('breadcrumb-parent', 'Contents')
@section('breadcrumb-current', 'Create')

@push('styles')
<!-- Quill Editor Stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    /* Custom Quill Styling */
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
    
    /* Content Type Cards */
    .content-type-card {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
    }
    .content-type-card:hover {
        border-color: #4f46e5;
        background-color: #f8fafc;
    }
    .content-type-card.active {
        border-color: #4f46e5;
        background-color: #eef2ff;
    }
    .content-type-card .icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    .content-type-card .type-name {
        font-weight: 600;
        color: #1e293b;
        margin-top: 8px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Add Content to Week</h4>
                <a href="{{ route('admin.weeks.show', $week->id) }}" class="btn btn-secondary">
                    Back to Week
                </a>
            </div>
            <div class="card-body">
                <!-- Context Information -->
                <div class="alert alert-info mb-4">
                    <strong>Adding content to:</strong><br>
                    <strong>Program:</strong> {{ $week->programModule->program->name }}<br>
                    <strong>Module:</strong> {{ $week->programModule->title }}<br>
                    <strong>Week:</strong> Week {{ $week->week_number }} - {{ $week->title }}
                </div>

                <form action="{{ route('admin.contents.store') }}" method="POST" enctype="multipart/form-data" id="contentForm">
                    @csrf
                    
                    <!-- Hidden field - week is pre-selected -->
                    <input type="hidden" name="module_week_id" value="{{ $week->id }}">

                    <!-- Content Type Selection -->
                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Select Content Type <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="content-type-card" onclick="selectContentType('video')" id="card-video">
                                    <input type="radio" class="d-none" id="typeVideo" name="content_type" value="video" 
                                           {{ old('content_type') == 'video' ? 'checked' : '' }} required>
                                    <div class="icon">📹</div>
                                    <div class="type-name">Video</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="content-type-card" onclick="selectContentType('pdf')" id="card-pdf">
                                    <input type="radio" class="d-none" id="typePDF" name="content_type" value="pdf"
                                           {{ old('content_type') == 'pdf' ? 'checked' : '' }} required>
                                    <div class="icon">📄</div>
                                    <div class="type-name">PDF Document</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="content-type-card" onclick="selectContentType('link')" id="card-link">
                                    <input type="radio" class="d-none" id="typeLink" name="content_type" value="link"
                                           {{ old('content_type') == 'link' ? 'checked' : '' }} required>
                                    <div class="icon">🔗</div>
                                    <div class="type-name">External Link</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="content-type-card" onclick="selectContentType('text')" id="card-text">
                                    <input type="radio" class="d-none" id="typeText" name="content_type" value="text"
                                           {{ old('content_type') == 'text' ? 'checked' : '' }} required>
                                    <div class="icon">📝</div>
                                    <div class="type-name">Article</div>
                                </div>
                            </div>
                        </div>
                        @error('content_type')
                            <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Common Fields -->
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Content Title <span class="text-danger">*</span></label>
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

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" 
                                  rows="2"
                                  placeholder="Brief description of this content">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type-Specific Fields -->
                    
                    <!-- Video Fields -->
                    <div id="videoFields" class="content-fields" style="display:none;">
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
                                           value="{{ old('video_url') }}"
                                           placeholder="https://youtube.com/watch?v=...">
                                    @error('video_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold">Duration (minutes)</label>
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
                        </div>
                    </div>

                    <!-- PDF Fields -->
                    <div id="pdfFields" class="content-fields" style="display:none;">
                        <div class="card border-danger mb-4">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0">PDF Document</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold">Upload PDF File <span class="text-danger">*</span></label>
                                    <input type="file" 
                                           class="form-control @error('file') is-invalid @enderror" 
                                           name="file" 
                                           accept=".pdf"
                                           id="pdfFile">
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Link Fields -->
                    <div id="linkFields" class="content-fields" style="display:none;">
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
                                           value="{{ old('external_url') }}"
                                           placeholder="https://example.com/resource">
                                    @error('external_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Text/Article Fields with Quill Editor -->
                    <div id="textFields" class="content-fields" style="display:none;">
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
                                    <input type="hidden" name="text_content" id="textContentHidden">
                                    
                                    @error('text_content')
                                        <div class="text-danger mt-2"><small>{{ $message }}</small></div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Settings -->
                    {{-- <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="isRequired" 
                                           name="is_required"
                                           {{ old('is_required', true) ? 'checked' : '' }}>
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
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div> --}}

                    <hr class="my-4">

                    <!-- Submit Buttons -->
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
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
<!-- Quill Editor JavaScript -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
// ============================================================================
// FIXED VERSION - All Bugs Resolved
// ============================================================================

// Global Quill instance
let quillEditor = null;
let formChanged = false;

// Content type selection
function selectContentType(type) {
    // Update radio button
    document.querySelectorAll('input[name="content_type"]').forEach(radio => {
        radio.checked = radio.value === type;
    });
    
    // Update card styling
    document.querySelectorAll('.content-type-card').forEach(card => {
        card.classList.remove('active');
    });
    document.getElementById('card-' + type).classList.add('active');
    
    // Show/hide fields
    toggleContentFields();
    
    // Mark form as changed
    formChanged = true;
}

function toggleContentFields() {
    const contentType = document.querySelector('input[name="content_type"]:checked');
    
    // Hide all content fields
    document.querySelectorAll('.content-fields').forEach(field => {
        field.style.display = 'none';
    });
    
    if (!contentType) return;
    
    // Show selected content field
    const typeValue = contentType.value;
    const fieldId = typeValue + 'Fields';
    const fieldElement = document.getElementById(fieldId);
    
    if (fieldElement) {
        fieldElement.style.display = 'block';
        
        // Initialize Quill editor if text type is selected
        // FIX: Removed setTimeout - initialize immediately
        if (typeValue === 'text' && !quillEditor) {
            initQuillEditor();
        }
    }
}

function initQuillEditor() {
    if (quillEditor) {
        return; // Already initialized
    }
    
    // Check if Quill library is loaded
    if (typeof Quill === 'undefined') {
        console.error('Quill library not loaded');
        alert('Editor library failed to load. Please refresh the page.');
        return;
    }
    
    // FIX: Removed imageHandler from modules configuration
    // Initialize Quill with correct configuration
    try {
        quillEditor = new Quill('#quillEditor', {
            theme: 'snow',
            placeholder: 'Start writing your article content here...',
            modules: {
                toolbar: [
                    // Headers
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    
                    // Font styling
                    ['bold', 'italic', 'underline', 'strike'],
                    
                    // Text color and background
                    [{ 'color': [] }, { 'background': [] }],
                    
                    // Lists
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    
                    // Alignment
                    [{ 'align': [] }],
                    
                    // Links and media
                    ['link', 'image', 'video'],
                    
                    // Code block
                    ['blockquote', 'code-block'],
                    
                    // Clear formatting
                    ['clean']
                ]
            }
        });
        
        // FIX: Register custom image handler AFTER initialization
        const toolbar = quillEditor.getModule('toolbar');
        toolbar.addHandler('image', imageHandler);
        
        // Load old content if validation failed
        const oldContent = @json(old('text_content', ''));
        if (oldContent) {
            quillEditor.root.innerHTML = oldContent;
        }
        
        // Sync Quill content to hidden input on any change
        quillEditor.on('text-change', function() {
            document.getElementById('textContentHidden').value = quillEditor.root.innerHTML;
            formChanged = true;
        });
        
        // Also sync immediately after initialization
        document.getElementById('textContentHidden').value = quillEditor.root.innerHTML;
        
        console.log('Quill editor initialized successfully');
        
    } catch (error) {
        console.error('Failed to initialize Quill:', error);
        alert('Failed to initialize text editor. Please refresh the page and try again.');
    }
}

// FIX: Improved image handler with better error handling
function imageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
        const file = input.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file');
            return;
        }

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size should not exceed 2MB');
            return;
        }

        // Show loading state
        const range = quillEditor.getSelection(true);
        quillEditor.insertText(range.index, 'Uploading image...');
        
        // Upload image
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('{{ route("admin.contents.upload-image") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Remove loading text
            quillEditor.deleteText(range.index, 'Uploading image...'.length);

            if (!response.ok) {
                throw new Error(`Upload failed with status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.location) {
                // Insert uploaded image
                quillEditor.insertEmbed(range.index, 'image', result.location);
                quillEditor.setSelection(range.index + 1);
            } else {
                throw new Error(result.error || 'Upload failed');
            }
            
        } catch (error) {
            console.error('Upload error:', error);
            // Ensure loading text is removed
            const currentText = quillEditor.getText(range.index, 20);
            if (currentText.includes('Uploading image...')) {
                quillEditor.deleteText(range.index, 'Uploading image...'.length);
            }
            alert('Failed to upload image: ' + error.message);
        }
    };
}

// FIX: Improved form submission validation
document.getElementById('contentForm').addEventListener('submit', function(e) {
    const selectedType = document.querySelector('input[name="content_type"]:checked');
    const submitBtn = document.getElementById('submitBtn');
    
    // Validate content type is selected
    if (!selectedType) {
        e.preventDefault();
        alert('Please select a content type.');
        return false;
    }
    
    // Special validation for text content
    if (selectedType.value === 'text') {
        // Check if editor is initialized
        if (!quillEditor) {
            e.preventDefault();
            alert('Text editor not initialized. Please select the text content type again.');
            return false;
        }
        
        // Get content from editor
        const content = quillEditor.root.innerHTML;
        const textOnly = quillEditor.getText().trim();
        
        // Update hidden input
        document.getElementById('textContentHidden').value = content;
        
        // Validate that content is not empty
        if (textOnly.length === 0) {
            e.preventDefault();
            alert('Please add some content to the article.');
            return false;
        }
    }
    
    // Disable submit button to prevent double-submission
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
    
    // Reset form changed flag
    formChanged = false;
    
    // Allow form to submit
    return true;
});

// Client-side validation for PDF file size
const pdfFileInput = document.getElementById('pdfFile');
if (pdfFileInput) {
    pdfFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.size > 10 * 1024 * 1024) { // 10MB
            alert('PDF file size should not exceed 10MB');
            this.value = '';
        }
    });
}

// Track form changes
document.getElementById('contentForm').addEventListener('input', function() {
    formChanged = true;
});

// Warn before leaving with unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set active card if type was previously selected
    const selectedType = document.querySelector('input[name="content_type"]:checked');
    if (selectedType) {
        document.getElementById('card-' + selectedType.value).classList.add('active');
    }
    
    // Show correct fields on page load
    toggleContentFields();
});
</script>
@endpush