@extends('layouts.admin')

@section('title', 'Add Content')
@section('breadcrumb-parent', 'Content')
@section('breadcrumb-current', 'Add New')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">Add New Content</h4>

                <form action="{{ route('mentor.contents.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Program/Module/Week Selection -->
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Program <span class="text-danger">*</span></label>
                            <select class="form-control" id="programSelect" onchange="loadModules()" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id', $programId) == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Module <span class="text-danger">*</span></label>
                            <select class="form-control" id="moduleSelect" onchange="loadWeeks()" required>
                                <option value="">Select Program First</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id', $moduleId) == $module->id ? 'selected' : '' }}>
                                        {{ $module->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Week <span class="text-danger">*</span></label>
                            <select class="form-control" name="module_week_id" id="weekSelect" required>
                                <option value="">Select Module First</option>
                                @foreach($weeks as $week)
                                    <option value="{{ $week->id }}" {{ old('module_week_id', $weekId) == $week->id ? 'selected' : '' }}>
                                        Week {{ $week->week_number }}: {{ $week->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- Content Type Selection -->
                    <div class="form-group">
                        <label>Content Type <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typeVideo" 
                                           name="content_type" value="video" onchange="toggleFields()" 
                                           {{ old('content_type') == 'video' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeVideo">📹 Video</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typePDF" 
                                           name="content_type" value="pdf" onchange="toggleFields()"
                                           {{ old('content_type') == 'pdf' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typePDF">📄 PDF</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typeLink" 
                                           name="content_type" value="link" onchange="toggleFields()"
                                           {{ old('content_type') == 'link' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeLink">🔗 Link</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" id="typeText" 
                                           name="content_type" value="text" onchange="toggleFields()"
                                           {{ old('content_type') == 'text' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="typeText">📝 Text</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Common Fields -->
                    <div class="form-group">
                        <label>Content Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               name="title" value="{{ old('title') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <!-- Type-Specific Fields -->
                    <div id="videoFields" style="display:none;">
                        <div class="form-group">
                            <label>Video URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" name="video_url" 
                                   value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                        </div>
                        <div class="form-group">
                            <label>Duration (minutes)</label>
                            <input type="number" class="form-control" name="video_duration_minutes" 
                                   value="{{ old('video_duration_minutes') }}" min="1">
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
                                   value="{{ old('external_url') }}" placeholder="https://example.com/resource">
                        </div>
                    </div>

                    <div id="textFields" style="display:none;">
                        <div class="form-group">
                            <label>Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="text_content" rows="12" 
                                      id="textEditor">{{ old('text_content') }}</textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="isRequired" 
                                       name="is_required" {{ old('is_required', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="isRequired">
                                    Required content
                                </label>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Add Content</button>
                        <a href="{{ route('mentor.contents.index') }}" class="btn btn-secondary">Cancel</a>
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
document.addEventListener('DOMContentLoaded', function() {
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').html(fileName);
    });
    
    const selected = document.querySelector('input[name="content_type"]:checked');
    if (selected) toggleFields();
});

function toggleFields() {
    const type = document.querySelector('input[name="content_type"]:checked')?.value;
    
    document.getElementById('videoFields').style.display = 'none';
    document.getElementById('pdfFields').style.display = 'none';
    document.getElementById('linkFields').style.display = 'none';
    document.getElementById('textFields').style.display = 'none';
    
    if (type === 'video') document.getElementById('videoFields').style.display = 'block';
    else if (type === 'pdf') document.getElementById('pdfFields').style.display = 'block';
    else if (type === 'link') document.getElementById('linkFields').style.display = 'block';
    else if (type === 'text') {
        document.getElementById('textFields').style.display = 'block';
        initEditor();
    }
}

function initEditor() {
    if (tinymce.get('textEditor')) return;
    tinymce.init({
        selector: '#textEditor',
        height: 400,
        menubar: false,
        plugins: 'lists link',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link'
    });
}

function loadModules() {
    const programId = document.getElementById('programSelect').value;
    fetch(`/admin/contents/weeks-by-module?program_id=${programId}`)
        .then(r => r.json())
        .then(modules => {
            const select = document.getElementById('moduleSelect');
            select.innerHTML = '<option value="">Select Module</option>';
            modules.forEach(m => {
                select.innerHTML += `<option value="${m.id}">${m.title}</option>`;
            });
        });
}

function loadWeeks() {
    const moduleId = document.getElementById('moduleSelect').value;
    fetch(`/admin/contents/weeks-by-module?module_id=${moduleId}`)
        .then(r => r.json())
        .then(weeks => {
            const select = document.getElementById('weekSelect');
            select.innerHTML = '<option value="">Select Week</option>';
            weeks.forEach(w => {
                select.innerHTML += `<option value="${w.id}">Week ${w.week_number}: ${w.title}</option>`;
            });
        });
}
</script>
@endpush