@extends('layouts.admin')

@section('title', 'Learning Contents')
@section('breadcrumb-parent', 'Curriculum')
@section('breadcrumb-current', 'Contents')

@push('styles')
<style>
.content-icon {
    font-size: 24px;
    width: 40px;
    text-align: center;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Learning Contents</h4>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createContentModal">
                        Add New Content
                    </button>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <select class="form-control" id="filterProgram" onchange="loadModulesFilter()">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" id="filterModule" onchange="loadWeeksFilter()">
                            <option value="">All Modules</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" id="filterWeek" onchange="filterContents()">
                            <option value="">All Weeks</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" id="filterType" onchange="filterContents()">
                            <option value="">All Types</option>
                            <option value="video" {{ request('content_type') == 'video' ? 'selected' : '' }}>Video</option>
                            <option value="pdf" {{ request('content_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="link" {{ request('content_type') == 'link' ? 'selected' : '' }}>Link</option>
                            <option value="text" {{ request('content_type') == 'text' ? 'selected' : '' }}>Text</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" id="filterStatus" onchange="filterContents()">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" id="searchContents" 
                               placeholder="Search..." value="{{ request('search') }}" 
                               onkeyup="debounceSearch()">
                    </div>
                </div>

                <!-- Contents Table -->
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Title</th>
                                <th>Week</th>
                                <th>Module</th>
                                <th>Type</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contents as $content)
                            <tr>
                                <td class="content-icon">{{ $content->icon }}</td>
                                <td>
                                    <strong>{{ $content->title }}</strong>
                                    @if($content->is_required)
                                        <span class="badge badge-primary">Required</span>
                                    @endif
                                    @if($content->description)
                                        <br><small class="text-muted">{{ Str::limit($content->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>
                                    Week {{ $content->moduleWeek->week_number }}<br>
                                    <small class="text-muted">{{ Str::limit($content->moduleWeek->title, 30) }}</small>
                                </td>
                                <td>{{ Str::limit($content->moduleWeek->programModule->title, 30) }}</td>
                                <td>
                                    <span class="badge badge-light">{{ $content->type_display }}</span>
                                    @if($content->content_type === 'video' && $content->video_duration_minutes)
                                        <br><small class="text-muted">{{ $content->video_duration_minutes }} min</small>
                                    @elseif($content->content_type === 'pdf' && $content->file_size)
                                        <br><small class="text-muted">{{ $content->file_size }}</small>
                                    @endif
                                </td>
                                <td>{{ $content->creator ? $content->creator->name : 'N/A' }}</td>
                                <td>
                                    @if($content->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editContent({{ $content->id }})">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteContent({{ $content->id }})">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No contents found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $contents->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Content Modal -->
<div class="modal fade" id="createContentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.contents.store') }}" method="POST" enctype="multipart/form-data" id="createContentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Content</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <!-- Program/Module/Week Selection -->
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Program <span class="text-danger">*</span></label>
                            <select class="form-control" id="createProgram" onchange="loadModulesForCreate()" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Module <span class="text-danger">*</span></label>
                            <select class="form-control" id="createModule" onchange="loadWeeksForCreate()" required>
                                <option value="">Select Program First</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Week <span class="text-danger">*</span></label>
                            <select class="form-control" name="module_week_id" id="createWeek" required>
                                <option value="">Select Module First</option>
                            </select>
                        </div>
                    </div>

                    <!-- Content Type Selection -->
                    <div class="form-group">
                        <label>Content Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typeVideo" 
                                           name="content_type" value="video" onchange="toggleContentFields()">
                                    <label class="custom-control-label" for="typeVideo">📹 Video</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typePDF" 
                                           name="content_type" value="pdf" onchange="toggleContentFields()">
                                    <label class="custom-control-label" for="typePDF">📄 PDF</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typeLink" 
                                           name="content_type" value="link" onchange="toggleContentFields()">
                                    <label class="custom-control-label" for="typeLink">🔗 Link</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typeText" 
                                           name="content_type" value="text" onchange="toggleContentFields()">
                                    <label class="custom-control-label" for="typeText">📝 Text</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Common Fields -->
                    <div class="form-group">
                        <label>Content Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>

                    <!-- Type-Specific Fields -->
                    <div id="videoFields" style="display:none;">
                        <div class="form-group">
                            <label>Video URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="video_url" 
                                   placeholder="https://youtube.com/watch?v=...">
                            <small class="text-muted">YouTube, Vimeo, or other video platform URL</small>
                        </div>
                        <div class="form-group">
                            <label>Duration (minutes)</label>
                            <input type="number" class="form-control" name="video_duration_minutes" min="1">
                        </div>
                    </div>

                    <div id="pdfFields" style="display:none;">
                        <div class="form-group">
                            <label>Upload PDF File <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="file" accept=".pdf">
                                <label class="custom-file-label">Choose file</label>
                            </div>
                            <small class="text-muted">Max file size: 10MB</small>
                        </div>
                    </div>

                    <div id="linkFields" style="display:none;">
                        <div class="form-group">
                            <label>External URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="external_url" 
                                   placeholder="https://example.com/resource">
                        </div>
                    </div>

                    <div id="textFields" style="display:none;">
                        <div class="form-group">
                            <label>Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="text_content" rows="8" 
                                      id="textContentEditor"></textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isRequired" 
                                       name="is_required" checked>
                                <label class="custom-control-label" for="isRequired">
                                    Required content (must complete to unlock next week)
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Content</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
<script>
let searchTimeout;

// File input label update
document.addEventListener('DOMContentLoaded', function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
});

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterContents, 500);
}

function toggleContentFields() {
    const contentType = document.querySelector('input[name="content_type"]:checked').value;
    
    // Hide all type-specific fields
    document.getElementById('videoFields').style.display = 'none';
    document.getElementById('pdfFields').style.display = 'none';
    document.getElementById('linkFields').style.display = 'none';
    document.getElementById('textFields').style.display = 'none';
    
    // Show selected type fields
    if (contentType === 'video') {
        document.getElementById('videoFields').style.display = 'block';
    } else if (contentType === 'pdf') {
        document.getElementById('pdfFields').style.display = 'block';
    } else if (contentType === 'link') {
        document.getElementById('linkFields').style.display = 'block';
    } else if (contentType === 'text') {
        document.getElementById('textFields').style.display = 'block';
        initTinyMCE();
    }
}

function initTinyMCE() {
    if (tinymce.get('textContentEditor')) {
        tinymce.get('textContentEditor').remove();
    }
    tinymce.init({
        selector: '#textContentEditor',
        height: 300,
        menubar: false,
        plugins: 'lists link',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link'
    });
}

function loadModulesForCreate() {
    const programId = document.getElementById('createProgram').value;
    const moduleSelect = document.getElementById('createModule');
    
    if (programId) {
        fetch(`{{ route('admin.contents.weeks-by-module') }}?program_id=${programId}`)
            .then(response => response.json())
            .then(modules => {
                moduleSelect.innerHTML = '<option value="">Select Module</option>';
                modules.forEach(module => {
                    moduleSelect.innerHTML += `<option value="${module.id}">${module.title}</option>`;
                });
            });
    }
}

function loadWeeksForCreate() {
    const moduleId = document.getElementById('createModule').value;
    const weekSelect = document.getElementById('createWeek');
    
    if (moduleId) {
        fetch(`{{ route('admin.contents.weeks-by-module') }}?module_id=${moduleId}`)
            .then(response => response.json())
            .then(weeks => {
                weekSelect.innerHTML = '<option value="">Select Week</option>';
                weeks.forEach(week => {
                    weekSelect.innerHTML += `<option value="${week.id}">Week ${week.week_number}: ${week.title}</option>`;
                });
            });
    }
}

function filterContents() {
    const programId = document.getElementById('filterProgram').value;
    const moduleId = document.getElementById('filterModule').value;
    const weekId = document.getElementById('filterWeek').value;
    const contentType = document.getElementById('filterType').value;
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchContents').value;
    
    const params = new URLSearchParams();
    if (programId) params.append('program_id', programId);
    if (moduleId) params.append('module_id', moduleId);
    if (weekId) params.append('week_id', weekId);
    if (contentType) params.append('content_type', contentType);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    window.location.href = '{{ route("admin.contents.index") }}?' + params.toString();
}

function deleteContent(id) {
    if (confirm('Are you sure you want to delete this content?')) {
        fetch(`/admin/contents/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message);
            }
        });
    }
}

@if($errors->any())
    $('#createContentModal').modal('show');
@endif
</script>
@endpush