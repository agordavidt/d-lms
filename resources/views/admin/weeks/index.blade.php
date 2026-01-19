@extends('layouts.admin')

@section('title', 'Module Weeks')
@section('breadcrumb-parent', 'Curriculum')
@section('breadcrumb-current', 'Weeks')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Module Weeks</h4>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createWeekModal">
                        Create New Week
                    </button>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="filterProgram" onchange="loadModules()">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterModule" onchange="filterWeeks()">
                            <option value="">All Modules</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->id }}" {{ request('module_id') == $module->id ? 'selected' : '' }}>
                                    {{ $module->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterStatus" onchange="filterWeeks()">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="searchWeeks" placeholder="Search weeks..." 
                               value="{{ request('search') }}" onkeyup="debounceSearch()">
                    </div>
                </div>

                <!-- Weeks Table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Week #</th>
                                <th>Week Title</th>
                                <th>Module</th>
                                <th>Program</th>
                                <th>Contents</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weeks as $week)
                            <tr>
                                <td><strong>Week {{ $week->week_number }}</strong></td>
                                <td>
                                    <strong>{{ $week->title }}</strong>
                                    @if($week->description)
                                        <br><small class="text-muted">{{ Str::limit($week->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>{{ $week->programModule->title }}</td>
                                <td>{{ $week->programModule->program->name }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $week->total_contents_count }} total</span>
                                    <span class="badge badge-primary">{{ $week->required_contents_count }} required</span>
                                </td>
                                <td>
                                    @if($week->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @elseif($week->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @else
                                        <span class="badge badge-secondary">Archived</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.weeks.show', $week->id) }}" class="btn btn-sm btn-info">
                                        View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editWeek({{ $week->id }})">
                                        Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteWeek({{ $week->id }})">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">No weeks found. Create your first week to get started.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $weeks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Week Modal -->
<div class="modal fade" id="createWeekModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.weeks.store') }}" method="POST" id="createWeekForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create New Week</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Program <span class="text-danger">*</span></label>
                            <select class="form-control" id="createProgram" onchange="loadModulesForCreate()" required>
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Module <span class="text-danger">*</span></label>
                            <select class="form-control" name="program_module_id" id="createModule" required>
                                <option value="">Select Program First</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Week Number <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="week_number" 
                                   min="1" placeholder="e.g., 1" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Week Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" 
                               placeholder="e.g., Week 1: Introduction to Data" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="What will be covered this week?"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="has_assessment" id="hasAssessment">
                        <label class="form-check-label" for="hasAssessment">
                            This week has an assessment
                        </label>
                    </div>

                    <div class="form-group" id="assessmentPassPercentage" style="display: none;">
                        <label>Assessment Pass Percentage</label>
                        <input type="number" class="form-control" name="assessment_pass_percentage" 
                               min="0" max="100" value="70">
                    </div>

                    <div class="form-group">
                        <label>Learning Outcomes</label>
                        <small class="text-muted d-block mb-2">What will learners achieve this week?</small>
                        <div id="outcomes-container">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="learning_outcomes[]" 
                                       placeholder="e.g., Understand basic data types">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger" onclick="removeOutcome(this)">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addOutcome()">
                            Add Another Outcome
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Week</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Week Modal -->
<div class="modal fade" id="editWeekModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="editWeekForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Week</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="editWeekContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Week</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimeout;

// Toggle assessment fields
document.addEventListener('DOMContentLoaded', function() {
    const assessmentCheckbox = document.getElementById('hasAssessment');
    if (assessmentCheckbox) {
        assessmentCheckbox.addEventListener('change', function() {
            document.getElementById('assessmentPassPercentage').style.display = 
                this.checked ? 'block' : 'none';
        });
    }
});

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterWeeks, 500);
}

function loadModules() {
    const programId = document.getElementById('filterProgram').value;
    
    if (programId) {
        fetch(`{{ route('admin.weeks.modules-by-program') }}?program_id=${programId}`)
            .then(response => response.json())
            .then(modules => {
                const moduleSelect = document.getElementById('filterModule');
                moduleSelect.innerHTML = '<option value="">All Modules</option>';
                modules.forEach(module => {
                    moduleSelect.innerHTML += `<option value="${module.id}">${module.title}</option>`;
                });
                filterWeeks();
            });
    } else {
        filterWeeks();
    }
}

function loadModulesForCreate() {
    const programId = document.getElementById('createProgram').value;
    const moduleSelect = document.getElementById('createModule');
    
    moduleSelect.innerHTML = '<option value="">Loading...</option>';
    
    if (programId) {
        fetch(`{{ route('admin.weeks.modules-by-program') }}?program_id=${programId}`)
            .then(response => response.json())
            .then(modules => {
                moduleSelect.innerHTML = '<option value="">Select Module</option>';
                modules.forEach(module => {
                    moduleSelect.innerHTML += `<option value="${module.id}">${module.title}</option>`;
                });
            });
    } else {
        moduleSelect.innerHTML = '<option value="">Select Program First</option>';
    }
}

function filterWeeks() {
    const programId = document.getElementById('filterProgram').value;
    const moduleId = document.getElementById('filterModule').value;
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchWeeks').value;
    
    const params = new URLSearchParams();
    if (programId) params.append('program_id', programId);
    if (moduleId) params.append('module_id', moduleId);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    window.location.href = '{{ route("admin.weeks.index") }}?' + params.toString();
}

function addOutcome() {
    const container = document.getElementById('outcomes-container');
    const newOutcome = document.createElement('div');
    newOutcome.className = 'input-group mb-2';
    newOutcome.innerHTML = `
        <input type="text" class="form-control" name="learning_outcomes[]" 
               placeholder="Enter learning outcome">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="removeOutcome(this)">Remove</button>
        </div>
    `;
    container.appendChild(newOutcome);
}

function removeOutcome(btn) {
    const container = document.getElementById('outcomes-container');
    if (container.children.length > 1) {
        btn.closest('.input-group').remove();
    }
}

function editWeek(id) {
    $('#editWeekModal').modal('show');
    
    fetch(`/admin/weeks/${id}/edit`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('editWeekContent').innerHTML = html;
            document.getElementById('editWeekForm').action = `/admin/weeks/${id}`;
        })
        .catch(error => {
            document.getElementById('editWeekContent').innerHTML = 
                '<div class="alert alert-danger">Failed to load week data. Please try again.</div>';
        });
}

function deleteWeek(id) {
    if (confirm('Are you sure you want to delete this week? This action cannot be undone.')) {
        fetch(`/admin/weeks/${id}`, {
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

@if($errors->any())
    $('#createWeekModal').modal('show');
@endif
</script>
@endpush