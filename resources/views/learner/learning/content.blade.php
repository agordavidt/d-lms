@extends('layouts.admin')

@section('title', $content->title)
@section('breadcrumb-parent', 'Learning')
@section('breadcrumb-current', $content->title)

@push('styles')
<style>
.content-viewer {
    background-color: #fff;
}
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 ratio */
    height: 0;
    overflow: hidden;
    background-color: #000;
}
.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
.pdf-viewer {
    height: 600px;
    border: 1px solid #dee2e6;
}
.text-content {
    line-height: 1.8;
    font-size: 16px;
}
.text-content h1, .text-content h2, .text-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    color: #333;
}
.text-content p {
    margin-bottom: 1rem;
}
.text-content ul, .text-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-9">
        <!-- Content Header -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="mb-2">
                            <span class="badge badge-light">{{ $content->type_display }}</span>
                            @if($content->is_required)
                                <span class="badge badge-primary">Required</span>
                            @endif
                            @if($progress->is_completed)
                                <span class="badge badge-success">✓ Completed</span>
                            @endif
                        </div>
                        <h3 class="mb-2">{{ $content->title }}</h3>
                        <p class="text-muted mb-0">
                            Week {{ $content->moduleWeek->week_number }}: {{ $content->moduleWeek->title }}
                        </p>
                    </div>
                    @if(!$progress->is_completed)
                        <button type="button" class="btn btn-success" onclick="markAsComplete()">
                            Mark as Complete
                        </button>
                    @endif
                </div>

                @if($content->description)
                    <p class="mt-3 mb-0">{{ $content->description }}</p>
                @endif
            </div>
        </div>

        <!-- Content Viewer -->
        <div class="card mt-3 content-viewer">
            <div class="card-body p-0">
                @if($content->content_type === 'video')
                    <!-- Video Player -->
                    <div class="video-container">
                        @php
                            $embedUrl = '';
                            if (str_contains($content->video_url, 'youtube.com') || str_contains($content->video_url, 'youtu.be')) {
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/', $content->video_url, $matches);
                                $videoId = $matches[1] ?? '';
                                $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                            } elseif (str_contains($content->video_url, 'vimeo.com')) {
                                preg_match('/vimeo\.com\/(\d+)/', $content->video_url, $matches);
                                $videoId = $matches[1] ?? '';
                                $embedUrl = "https://player.vimeo.com/video/{$videoId}";
                            }
                        @endphp
                        @if($embedUrl)
                            <iframe src="{{ $embedUrl }}" frameborder="0" allowfullscreen></iframe>
                        @else
                            <div class="p-4 text-center">
                                <p class="text-muted">Video player not available for this URL format.</p>
                                <a href="{{ $content->video_url }}" target="_blank" class="btn btn-primary">
                                    Watch on External Site
                                </a>
                            </div>
                        @endif
                    </div>
                    @if($content->video_duration_minutes)
                        <div class="p-3 bg-light border-top">
                            <small class="text-muted">Duration: {{ $content->video_duration_minutes }} minutes</small>
                        </div>
                    @endif

                @elseif($content->content_type === 'pdf')
                    <!-- PDF Viewer -->
                    <div class="p-3">
                        <iframe src="{{ $content->file_url }}" class="pdf-viewer w-100"></iframe>
                        <div class="mt-3 text-center">
                            <a href="{{ $content->file_url }}" download class="btn btn-primary">
                                Download PDF
                            </a>
                            @if($content->file_size)
                                <span class="text-muted ml-2">{{ $content->file_size }}</span>
                            @endif
                        </div>
                    </div>

                @elseif($content->content_type === 'link')
                    <!-- External Link -->
                    <div class="p-5 text-center">
                        <div class="mb-4">
                            <span style="font-size: 64px;">🔗</span>
                        </div>
                        <h5 class="mb-3">External Resource</h5>
                        <p class="text-muted mb-4">This content is hosted on an external website.</p>
                        <a href="{{ $content->external_url }}" target="_blank" class="btn btn-primary btn-lg">
                            Open Resource
                        </a>
                        <div class="mt-3">
                            <small class="text-muted d-block">{{ $content->external_url }}</small>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="btn btn-success" onclick="markAsComplete()">
                                I've Reviewed This Resource
                            </button>
                        </div>
                    </div>

                @else
                    <!-- Text Content -->
                    <div class="p-4 text-content">
                        {!! $content->text_content !!}
                    </div>
                @endif
            </div>
        </div>

        <!-- Navigation -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('learner.learning.index') }}" class="btn btn-outline-primary">
                        ← Back to Week
                    </a>
                    @if(!$progress->is_completed)
                        <button type="button" class="btn btn-success" onclick="markAsComplete()">
                            Mark as Complete
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-3">
        <!-- Progress Card -->
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-3">YOUR PROGRESS</h6>
                
                @if($progress->is_completed)
                    <div class="text-center mb-3">
                        <span style="font-size: 48px; color: #28a745;">✓</span>
                        <p class="text-success mb-0"><strong>Completed</strong></p>
                        <small class="text-muted">{{ $progress->completed_at->diffForHumans() }}</small>
                    </div>
                @else
                    <div class="text-center mb-3">
                        <h2 style="color: #7571f9;">{{ $progress->progress_percentage }}%</h2>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $progress->progress_percentage }}%; background-color: #7571f9;">
                            </div>
                        </div>
                    </div>
                @endif

                <hr>

                <div class="mb-2">
                    <small class="text-muted">Views</small>
                    <p class="mb-0"><strong>{{ $progress->view_count }}</strong></p>
                </div>

                @if($progress->time_spent_seconds > 0)
                <div class="mb-2">
                    <small class="text-muted">Time Spent</small>
                    <p class="mb-0"><strong>{{ gmdate('H:i:s', $progress->time_spent_seconds) }}</strong></p>
                </div>
                @endif

                @if($progress->started_at)
                <div class="mb-0">
                    <small class="text-muted">Started</small>
                    <p class="mb-0"><strong>{{ $progress->started_at->format('M d, Y') }}</strong></p>
                </div>
                @endif
            </div>
        </div>

        <!-- Week Progress -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-muted mb-3">WEEK PROGRESS</h6>
                <div class="text-center mb-3">
                    <h3 style="color: #7571f9;">{{ $weekProgress->progress_percentage }}%</h3>
                    <p class="text-muted mb-0">
                        {{ $weekProgress->contents_completed }} of {{ $weekProgress->total_contents }} complete
                    </p>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar" role="progressbar" 
                         style="width: {{ $weekProgress->progress_percentage }}%; background-color: #7571f9;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="text-muted mb-3">QUICK LINKS</h6>
                <a href="{{ route('learner.learning.index') }}" class="btn btn-outline-primary btn-block btn-sm mb-2">
                    Back to Week
                </a>
                <a href="{{ route('learner.curriculum') }}" class="btn btn-outline-primary btn-block btn-sm">
                    View Curriculum
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let startTime = Date.now();
let progressUpdateInterval;

// Track time spent
document.addEventListener('DOMContentLoaded', function() {
    // Update time spent every 30 seconds
    progressUpdateInterval = setInterval(updateTimeSpent, 30000);
    
    // Update on page unload
    window.addEventListener('beforeunload', updateTimeSpent);
});

function updateTimeSpent() {
    const timeSpent = Math.floor((Date.now() - startTime) / 1000);
    
    fetch('{{ route("learner.learning.content.progress", $content->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            progress_percentage: {{ $progress->progress_percentage }},
            time_spent: timeSpent
        })
    });
    
    startTime = Date.now(); // Reset counter
}

function markAsComplete() {
    if (confirm('Mark this content as complete?')) {
        fetch('{{ route("learner.learning.content.complete", $content->id) }}', {
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
                toastr.error('Failed to mark as complete. Please try again.');
            }
        })
        .catch(error => {
            toastr.error('An error occurred. Please try again.');
        });
    }
}

// Clear interval on page unload
window.addEventListener('beforeunload', function() {
    clearInterval(progressUpdateInterval);
});
</script>
@endpush
