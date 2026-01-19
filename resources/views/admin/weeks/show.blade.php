@extends('layouts.admin')

@section('title', 'Week Details')
@section('breadcrumb-parent', 'Weeks')
@section('breadcrumb-current', $week->title)

@section('content')
<div class="row">
    <!-- Week Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Week {{ $week->week_number }}</h4>
                <h5 class="mb-3">{{ $week->title }}</h5>
                
                <div class="mb-3">
                    @if($week->status === 'published')
                        <span class="badge badge-success">Published</span>
                    @elseif($week->status === 'draft')
                        <span class="badge badge-warning">Draft</span>
                    @else
                        <span class="badge badge-secondary">Archived</span>
                    @endif
                </div>

                <hr>

                <p class="text-muted mb-2"><strong>Program:</strong></p>
                <p>{{ $week->programModule->program->name }}</p>

                <p class="text-muted mb-2"><strong>Module:</strong></p>
                <p>{{ $week->programModule->title }}</p>

                @if($week->description)
                    <p class="text-muted mb-2"><strong>Description:</strong></p>
                    <p>{{ $week->description }}</p>
                @endif

                @if($week->learning_outcomes && count($week->learning_outcomes) > 0)
                    <p class="text-muted mb-2"><strong>Learning Outcomes:</strong></p>
                    <ul class="pl-3">
                        @foreach($week->learning_outcomes as $outcome)
                            <li>{{ $outcome }}</li>
                        @endforeach
                    </ul>
                @endif

                @if($week->has_assessment)
                    <div class="alert alert-info mt-3">
                        <strong>Assessment Required</strong><br>
                        Pass percentage: {{ $week->assessment_pass_percentage }}%
                    </div>
                @endif

                <div class="mt-4">
                    <button type="button" class="btn btn-primary btn-block" 
                            onclick="window.location.href='{{ route('admin.weeks.edit', $week->id) }}'">
                        Edit Week
                    </button>
                    <a href="{{ route('admin.weeks.index') }}" class="btn btn-secondary btn-block">
                        Back to Weeks
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Week Contents -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Week Contents</h4>
                    <a href="{{ route('admin.contents.create', ['week_id' => $week->id]) }}" 
                       class="btn btn-primary">
                        Add Content
                    </a>
                </div>

                @if($contents->count() > 0)
                    <div class="list-group">
                        @foreach($contents as $content)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" style="font-size: 20px;">
                                                @if($content->content_type === 'video')
                                                    📹
                                                @elseif($content->content_type === 'pdf')
                                                    📄
                                                @elseif($content->content_type === 'link')
                                                    🔗
                                                @else
                                                    📝
                                                @endif
                                            </span>
                                            <div>
                                                <h6 class="mb-1">{{ $content->title }}</h6>
                                                <div>
                                                    <span class="badge badge-light">{{ $content->type_display }}</span>
                                                    @if($content->is_required)
                                                        <span class="badge badge-primary">Required</span>
                                                    @endif
                                                    @if($content->status === 'published')
                                                        <span class="badge badge-success">Published</span>
                                                    @else
                                                        <span class="badge badge-warning">Draft</span>
                                                    @endif
                                                </div>
                                                @if($content->description)
                                                    <small class="text-muted d-block mt-1">
                                                        {{ Str::limit($content->description, 80) }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <a href="{{ route('admin.contents.edit', $content->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteContent({{ $content->id }})">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted mb-3">No content added to this week yet.</p>
                        <a href="{{ route('admin.contents.create', ['week_id' => $week->id]) }}" 
                           class="btn btn-primary">
                            Add First Content
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Live Sessions for this Week -->
        @if($week->liveSessions && $week->liveSessions->count() > 0)
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title mb-4">Scheduled Live Sessions</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date & Time</th>
                                <th>Mentor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($week->liveSessions as $session)
                            <tr>
                                <td>{{ $session->title }}</td>
                                <td>{{ $session->start_time->format('M d, Y - g:i A') }}</td>
                                <td>{{ $session->mentor ? $session->mentor->name : 'TBA' }}</td>
                                <td>
                                    @if($session->status === 'scheduled')
                                        <span class="badge badge-info">Scheduled</span>
                                    @elseif($session->status === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($session->status === 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteContent(id) {
    if (confirm('Are you sure you want to delete this content? This action cannot be undone.')) {
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
</script>
@endpush