@extends('layouts.admin')

@section('title', 'Week Details')
@section('breadcrumb-parent', 'Weeks')
@section('breadcrumb-current', $week->title)

@section('content')
<div class="row">
    <!-- Week Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Week {{ $week->week_number }}: {{ $week->title }}</h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Week Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Program:</strong></td>
                                <td>{{ $week->programModule->program->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Module:</strong></td>
                                <td>{{ $week->programModule->title }}</td>
                            </tr>
                            <tr>
                                <td><strong>Week Number:</strong></td>
                                <td>{{ $week->week_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($week->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @elseif($week->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @else
                                        <span class="badge badge-secondary">Archived</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-muted mb-3">Statistics</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Total Contents:</strong></td>
                                <td><span class="badge badge-info">{{ $week->contents->count() }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Required Contents:</strong></td>
                                <td><span class="badge badge-primary">{{ $week->contents->where('is_required', true)->count() }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Has Assessment:</strong></td>
                                <td>
                                    @if($week->has_assessment)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            @if($week->has_assessment && $week->assessment)
                            <tr>
                                <td><strong>Assessment Status:</strong></td>
                                <td>
                                    @if($week->assessment->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-warning">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                @if($week->description)
                <div class="mb-4">
                    <h5 class="text-muted">Description</h5>
                    <p>{{ $week->description }}</p>
                </div>
                @endif

                @if($week->learning_outcomes && count($week->learning_outcomes) > 0)
                <div class="mb-4">
                    <h5 class="text-muted">Learning Outcomes</h5>
                    <ul>
                        @foreach($week->learning_outcomes as $outcome)
                            <li>{{ $outcome }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <!-- Week Contents -->
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">📚 Learning Contents</h4>
                <a href="{{ route('admin.contents.create', ['week_id' => $week->id]) }}" 
                   class="btn btn-sm btn-primary">
                    <i class="icon-plus"></i> Add Content
                </a>
            </div>
            <div class="card-body">
                @if($week->contents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 50px;">Order</th>
                                    <th style="width: 40px;"></th>
                                    <th>Title</th>
                                    <th style="width: 100px;">Type</th>
                                    <th style="width: 80px;">Status</th>
                                    <th style="width: 100px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($week->contents->sortBy('order') as $content)
                                <tr>
                                    <td>{{ $content->order }}</td>
                                    <td style="font-size: 20px;">{{ $content->icon }}</td>
                                    <td>
                                        <strong>{{ $content->title }}</strong>
                                        @if($content->is_required)
                                            <span class="badge badge-primary badge-sm ml-1">Required</span>
                                        @endif
                                        @if($content->description)
                                            <br><small class="text-muted">{{ Str::limit($content->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ $content->type_display }}</span>
                                    </td>
                                    <td>
                                        @if($content->status === 'published')
                                            <span class="badge badge-success badge-sm">Published</span>
                                        @else
                                            <span class="badge badge-warning badge-sm">Draft</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.contents.edit', $content->id) }}" 
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="icon-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteContent({{ $content->id }})" title="Delete">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="icon-layers" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-3 mb-3">No contents added yet</p>
                        <a href="{{ route('admin.contents.create', ['week_id' => $week->id]) }}" 
                           class="btn btn-primary">
                            <i class="icon-plus"></i> Add First Content
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Assessment Section -->
        @if($week->has_assessment)
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">📝 Week Assessment</h4>
                @if($week->assessment && $week->assessment->is_active)
                    <span class="badge badge-success">Active</span>
                @elseif($week->assessment)
                    <span class="badge badge-warning">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                @if(!$week->assessment)
                    {{-- No Assessment Created Yet --}}
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="icon-note" style="font-size: 48px; color: #6c757d;"></i>
                        </div>
                        <h5 class="text-muted">No Assessment Created</h5>
                        <p class="text-muted mb-4">This week is marked as having an assessment, but none has been created yet.</p>
                        <a href="{{ route('admin.assessments.create', ['week_id' => $week->id]) }}" 
                           class="btn btn-primary">
                            <i class="icon-plus"></i> Create Assessment
                        </a>
                    </div>
                @else
                    {{-- Assessment Exists --}}
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Assessment Details</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $week->assessment->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Questions:</strong></td>
                                    <td>
                                        <span class="badge badge-info">{{ $week->assessment->questions->count() }}</span>
                                        <small class="text-muted">({{ $week->assessment->total_points }} points)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Pass Mark:</strong></td>
                                    <td>{{ $week->assessment->pass_percentage }}%</td>
                                </tr>
                                <tr>
                                    <td><strong>Max Attempts:</strong></td>
                                    <td>{{ $week->assessment->max_attempts }}</td>
                                </tr>
                                @if($week->assessment->time_limit_minutes)
                                <tr>
                                    <td><strong>Time Limit:</strong></td>
                                    <td>{{ $week->assessment->time_limit_minutes }} minutes</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Settings</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($week->assessment->is_active)
                                            <span class="badge badge-success">Active</span>
                                            <small class="text-muted d-block">Visible to learners</small>
                                        @else
                                            <span class="badge badge-warning">Inactive</span>
                                            <small class="text-muted d-block">Hidden from learners</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Randomize:</strong></td>
                                    <td>
                                        @if($week->assessment->randomize_questions)
                                            <span class="badge badge-secondary">Questions</span>
                                        @endif
                                        @if($week->assessment->randomize_options)
                                            <span class="badge badge-secondary">Options</span>
                                        @endif
                                        @if(!$week->assessment->randomize_questions && !$week->assessment->randomize_options)
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Show Answers:</strong></td>
                                    <td>
                                        @if($week->assessment->show_correct_answers)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($week->assessment->attempts->count() > 0)
                                <tr>
                                    <td><strong>Learner Attempts:</strong></td>
                                    <td>
                                        <span class="badge badge-info">{{ $week->assessment->attempts->count() }}</span>
                                        <small class="text-muted d-block">
                                            Avg: {{ number_format($week->assessment->attempts->avg('percentage'), 1) }}%
                                        </small>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($week->assessment->description)
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="text-muted">Instructions</h6>
                        <p class="mb-0">{{ $week->assessment->description }}</p>
                    </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('admin.assessments.questions.index', $week->assessment->id) }}" 
                           class="btn btn-primary">
                           Manage Questions ({{ $week->assessment->questions->count() }})
                        </a>
                        
                        <a href="{{ route('admin.assessments.edit', $week->assessment->id) }}" 
                           class="btn btn-secondary">
                           Edit Settings
                        </a>

                        @if($week->assessment->questions->count() > 0)
                            <button type="button" 
                                    class="btn btn-{{ $week->assessment->is_active ? 'warning' : 'success' }}"
                                    onclick="toggleAssessmentStatus({{ $week->assessment->id }})">
                                
                                {{ $week->assessment->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        @else
                            <button type="button" class="btn btn-success" disabled title="Add questions first">
                                Activate
                            </button>
                        @endif

                        <button type="button" 
                                class="btn btn-danger float-right"
                                onclick="deleteAssessment({{ $week->assessment->id }})"
                                @if($week->assessment->attempts->count() > 0) disabled title="Cannot delete assessment with learner attempts" @endif>
                            Delete Assessment
                        </button>
                    </div>

                    {{-- Warning Messages --}}
                    @if($week->assessment->questions->count() === 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="icon-info"></i> <strong>No questions added yet.</strong> 
                        Add at least 3 questions before activating the assessment.
                    </div>
                    @elseif(!$week->assessment->is_active)
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="icon-info"></i> <strong>Assessment is inactive.</strong> 
                        Learners cannot see this assessment until you activate it.
                    </div>
                    @endif
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.modules.show', $week->program_module_id) }}" 
                   class="btn btn-secondary btn-block mb-2">
                     Back to Module
                </a>
                
                <a href="{{ route('admin.weeks.edit', $week->id) }}" 
                   class="btn btn-primary btn-block mb-2">
                    Edit Week
                </a>

                <a href="{{ route('admin.contents.create', ['week_id' => $week->id]) }}" 
                   class="btn btn-success btn-block mb-2">
                    Add Content
                </a>

                @if($week->has_assessment)
                    @if(!$week->assessment)
                        <a href="{{ route('admin.assessments.create', ['week_id' => $week->id]) }}" 
                           class="btn btn-info btn-block mb-2">
                             Create Assessment
                        </a>
                    @else
                        <a href="{{ route('admin.assessments.questions.index', $week->assessment->id) }}" 
                           class="btn btn-info btn-block mb-2">
                             Manage Questions
                        </a>
                    @endif
                @endif

                <hr>

                <button type="button" class="btn btn-danger btn-block" onclick="deleteWeek()">
                     Delete Week
                </button>
            </div>
        </div>

        <!-- Week Summary -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Summary</h4>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Contents</span>
                        <h4 class="mb-0 text-info">{{ $week->contents->count() }}</h4>
                    </div>
                </div>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Required</span>
                        <h4 class="mb-0 text-primary">{{ $week->contents->where('is_required', true)->count() }}</h4>
                    </div>
                </div>
                @if($week->has_assessment && $week->assessment)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Questions</span>
                        <h4 class="mb-0 text-success">{{ $week->assessment->questions->count() }}</h4>
                    </div>
                </div>
                @endif
                <div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status</span>
                        @if($week->status === 'published')
                            <span class="badge badge-success">Published</span>
                        @elseif($week->status === 'draft')
                            <span class="badge badge-warning">Draft</span>
                        @else
                            <span class="badge badge-secondary">Archived</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Week Timeline -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Timestamps</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $week->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Updated:</strong></td>
                        <td>{{ $week->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
        })
        .catch(error => {
            toastr.error('An error occurred. Please try again.');
        });
    }
}

function deleteWeek() {
    if (confirm('Are you sure you want to delete this week? This will also delete all contents and assessment in this week.')) {
        fetch(`/admin/weeks/{{ $week->id }}`, {
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
                setTimeout(() => {
                    window.location.href = '{{ route("admin.modules.show", $week->program_module_id) }}';
                }, 1000);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('An error occurred. Please try again.');
        });
    }
}

function toggleAssessmentStatus(assessmentId) {
    fetch(`/admin/assessments/${assessmentId}/toggle-active`, {
        method: 'POST',
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

function deleteAssessment(assessmentId) {
    if (confirm('Are you sure you want to delete this assessment? This will delete all questions.')) {
        fetch(`/admin/assessments/${assessmentId}`, {
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
</script>
@endpush