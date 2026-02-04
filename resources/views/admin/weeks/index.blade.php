@extends('layouts.admin')

@section('title', 'Module Weeks')
@section('breadcrumb-parent', 'Curriculum')
@section('breadcrumb-current', 'Weeks')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <h4 class="card-title mb-0">Module Weeks</h4>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <select class="form-control form-control-sm" id="filterProgram" onchange="filterWeeks()">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <select class="form-control form-control-sm" id="filterStatus" onchange="filterWeeks()">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-12 mb-2 mb-md-0">
                        <input type="text" class="form-control form-control-sm" id="searchWeeks" placeholder="Search weeks..." 
                               value="{{ request('search') }}" onkeyup="debounceSearch()">
                    </div>
                </div>

                <!-- Info Alert when filtered by program -->
                @if(request('program_id'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>Filtered by Program:</strong> {{ $programs->find(request('program_id'))->name ?? 'Selected Program' }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Weeks Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-nowrap">Week #</th>
                                <th>Week Title</th>
                                <th class="d-none d-lg-table-cell">Module</th>
                                <th class="d-none d-xl-table-cell">Program</th>
                                <th class="text-center d-none d-md-table-cell">Contents</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weeks as $week)
                            <tr>
                                <td class="font-weight-bold text-nowrap">Week {{ $week->week_number }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $week->title }}</div>
                                    @if($week->description)
                                        <small class="text-muted d-block">{{ Str::limit($week->description, 50) }}</small>
                                    @endif
                                    <!-- Show module/program on mobile -->
                                    <div class="d-lg-none mt-1">
                                        <small class="text-muted">
                                            {{ $week->programModule->title }}
                                            <span class="d-xl-none"> • {{ $week->programModule->program->name }}</span>
                                        </small>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <a href="{{ route('admin.modules.show', $week->programModule->id) }}" class="text-primary">
                                        {{ $week->programModule->title }}
                                    </a>
                                </td>
                                <td class="d-none d-xl-table-cell">{{ $week->programModule->program->name }}</td>
                                <td class="text-center d-none d-md-table-cell">
                                    <span class="badge badge-info">{{ $week->total_contents_count }}</span>
                                    @if($week->required_contents_count > 0)
                                        <span class="badge badge-primary">{{ $week->required_contents_count }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($week->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @elseif($week->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @else
                                        <span class="badge badge-secondary">Archived</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" 
                                                id="weekActions{{ $week->id }}" data-toggle="dropdown" 
                                                aria-haspopup="true" aria-expanded="false">
                                            Actions
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="weekActions{{ $week->id }}">
                                            <a class="dropdown-item" href="{{ route('admin.weeks.show', $week->id) }}">
                                                View Details
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.weeks.edit', $week->id) }}">
                                                Edit Week
                                            </a>
                                            <a class="dropdown-item" href="{{ route('admin.contents.create', ['week_id' => $week->id]) }}">
                                                Add Content
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="javascript:void(0);" 
                                               onclick="deleteWeek({{ $week->id }})">
                                                Delete Week
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <p class="mb-2">No weeks found</p>
                                        <small>Weeks are created from module pages</small>
                                        @if(request()->has('program_id') || request()->has('status') || request()->has('search'))
                                            <br>
                                            <a href="{{ route('admin.weeks.index') }}" class="btn btn-sm btn-link">Clear all filters</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($weeks->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $weeks->appends(request()->query())->links() }}
                </div>
                @endif

                <!-- Results Summary -->
                @if($weeks->total() > 0)
                <div class="mt-3 text-center text-muted">
                    <small>
                        Showing {{ $weeks->firstItem() }} to {{ $weeks->lastItem() }} of {{ $weeks->total() }} week(s)
                        @if(request()->has('program_id') || request()->has('status') || request()->has('search'))
                            <a href="{{ route('admin.weeks.index') }}" class="ml-2">Clear filters</a>
                        @endif
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimeout;

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(filterWeeks, 500);
}

function filterWeeks() {
    const programId = document.getElementById('filterProgram').value;
    const status = document.getElementById('filterStatus').value;
    const search = document.getElementById('searchWeeks').value;
    
    const params = new URLSearchParams();
    if (programId) params.append('program_id', programId);
    if (status) params.append('status', status);
    if (search) params.append('search', search);
    
    window.location.href = '{{ route("admin.weeks.index") }}?' + params.toString();
}

function deleteWeek(id) {
    if (confirm('Are you sure you want to delete this week? This will also delete all contents in this week.')) {
        const btn = event.target;
        const originalText = btn.textContent;
        btn.textContent = 'Deleting...';
        btn.disabled = true;
        
        fetch(`/admin/weeks/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to delete');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Failed to delete week');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred. Please try again.');
            btn.textContent = originalText;
            btn.disabled = false;
        });
    }
}
</script>
@endpush

@push('styles')
<style>
/* Responsive adjustments */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .card-title {
        font-size: 1.1rem;
    }
}

/* Dropdown menu improvements */
.dropdown-menu {
    min-width: 160px;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.dropdown-item:active {
    background-color: #007bff;
}

/* Table improvements */
.table th {
    font-weight: 600;
    font-size: 0.875rem;
    border-top: none;
}

.table td {
    vertical-align: middle;
}

.thead-light th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

/* Badge improvements */
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
    font-size: 0.8125rem;
}

/* Action button */
.btn-light.dropdown-toggle {
    border-color: #dee2e6;
    background-color: #fff;
}

.btn-light.dropdown-toggle:hover,
.btn-light.dropdown-toggle:focus {
    background-color: #f8f9fa;
    border-color: #adb5bd;
}

/* Alert improvements */
.alert {
    font-size: 0.875rem;
}

.alert strong {
    font-weight: 600;
}

/* Link styling in module column */
.text-primary {
    text-decoration: none;
}

.text-primary:hover {
    text-decoration: underline;
}
</style>
@endpush