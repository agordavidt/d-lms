@extends('layouts.admin')

@section('title', 'Program Modules')
@section('breadcrumb-parent', 'Curriculum')
@section('breadcrumb-current', 'Modules')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Program Modules</h4>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModuleModal">
                        Create New Module
                    </button>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-control" id="filterProgram" onchange="filterModules()">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control" id="filterStatus" onchange="filterModules()">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchModules" placeholder="Search modules..." 
                               value="{{ request('search') }}" onkeyup="debounceSearch()">
                    </div>
                </div>

                <!-- Modules Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Module Title</th>
                                <th>Program</th>
                                <th>Duration (Weeks)</th>
                                <th>Weeks</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modules as $module)
                            <tr>
                                <td>{{ $module->order }}</td>
                                <td>
                                    <strong>{{ $module->title }}</strong>
                                    @if($module->description)
                                        <br><small class="text-muted">{{ Str::limit($module->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>{{ $module->program->name }}</td>
                                <td>{{ $module->duration_weeks }}</td>
                                <td>{{ $module->total_weeks }}/{{ $module->duration_weeks }}</td>
                                <td>
                                    @if($module->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @elseif($module->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @else
                                        <span class="badge badge-secondary">Archived</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editModule({{ $module->id }})">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteModule({{ $module->id }})">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No modules found. Create your first module to get started.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $modules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Module Modal -->
<div class="modal fade" id="createModuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.modules.store') }}" method="POST" id="createModuleForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Module</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Program <span class="text-danger">*</span></label>
                        <select class="form-control" name="program_id" required>
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Module Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" 
                               placeholder="e.g., Module 1: Foundations of Data Analytics" 
                               value="{{ old('title') }}" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Brief description of what this module covers">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Duration (Weeks) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="duration_weeks" 
                                   min="1" value="{{ old('duration_weeks', 1) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Learning Objectives</label>
                        <small class="text-muted d-block mb-2">What will learners achieve in this module?</small>
                        <div id="objectives-container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="learning_objectives[]" 
                                       placeholder="e.g., Understand fundamental data concepts">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger" onclick="removeObjective(this)">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addObjective()">
                            Add Another Objective
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Module Modal -->
<div class="modal fade" id="editModuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editModuleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Module</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="editModuleContent">
                    <!-- Content loaded via AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Module</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimeout;

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterModules, 500);
}

function filterModules() {
    const programId = document.getElementById('filterProgram').value;
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchModules').value;
    
    const params = new URLSearchParams();
    if (programId) params.append('program_id', programId);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    window.location.href = '{{ route("admin.modules.index") }}?' + params.toString();
}

function addObjective() {
    const container = document.getElementById('objectives-container');
    const newObjective = document.createElement('div');
    newObjective.className = 'input-group mb-2';
    newObjective.innerHTML = `
        <input type="text" class="form-control" name="learning_objectives[]" 
               placeholder="Enter learning objective">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="removeObjective(this)">Remove</button>
        </div>
    `;
    container.appendChild(newObjective);
}

function removeObjective(btn) {
    const container = document.getElementById('objectives-container');
    if (container.children.length > 1) {
        btn.closest('.input-group').remove();
    }
}

function editModule(id) {
    $('#editModuleModal').modal('show');
    
    fetch(`/admin/modules/${id}/edit`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editModuleContent').innerHTML = html;
            document.getElementById('editModuleForm').action = `/admin/modules/${id}`;
        })
        .catch(error => {
            document.getElementById('editModuleContent').innerHTML = 
                '<div class="alert alert-danger">Failed to load module data. Please try again.</div>';
        });
}

function deleteModule(id) {
    if (confirm('Are you sure you want to delete this module? This action cannot be undone.')) {
        fetch(`/admin/modules/${id}`, {
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
        })
        .catch(error => {
            toastr.error('An error occurred. Please try again.');
        });
    }
}

// Show modal if there are validation errors
@if($errors->any())
    $('#createModuleModal').modal('show');
@endif
</script>
@endpush