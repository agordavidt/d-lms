@extends('layouts.admin')

@section('title', 'Learning')

@push('styles')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    .learning-container {
        display: flex;
        height: 100vh;
        background: #f8f9fa;
    }
    
    /* Left Panel - Content List */
    .content-sidebar {
        width: 350px;
        background: #fff;
        border-right: 1px solid #e0e0e0;
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
    }
    
    .week-header {
        padding: 24px 20px;
        background: #fff;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .week-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    
    .week-meta {
        font-size: 13px;
        color: #666;
        margin-bottom: 12px;
    }
    
    .week-progress {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .progress-bar {
        flex: 1;
        height: 6px;
        background: #f0f0f0;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #7571f9 0%, #9c98ff 100%);
        transition: width 0.3s ease;
    }
    
    .progress-text {
        font-size: 12px;
        font-weight: 600;
        color: #7571f9;
        white-space: nowrap;
    }
    
    .content-list {
        flex: 1;
        overflow-y: auto;
        padding: 12px 0;
    }
    
    .content-item {
        padding: 16px 20px;
        border-bottom: 1px solid #f5f5f5;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    
    .content-item:hover {
        background: #f8f8ff;
    }
    
    .content-item.active {
        background: #f8f8ff;
        border-left: 3px solid #7571f9;
    }
    
    .content-item.completed {
        opacity: 0.7;
    }
    
    .content-icon {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .icon-video { background: #e3f2fd; color: #1976d2; }
    .icon-reading { background: #f3e5f5; color: #7b1fa2; }
    .icon-link { background: #fff3e0; color: #f57c00; }
    
    .content-info {
        flex: 1;
        min-width: 0;
    }
    
    .content-name {
        font-size: 14px;
        font-weight: 500;
        color: #333;
        margin-bottom: 4px;
        line-height: 1.4;
    }
    
    .content-meta {
        font-size: 12px;
        color: #999;
    }
    
    .content-status {
        flex-shrink: 0;
        margin-top: 4px;
    }
    
    .status-check {
        width: 20px;
        height: 20px;
        border: 2px solid #e0e0e0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-size: 12px;
    }
    
    .content-item.completed .status-check {
        background: #4caf50;
        border-color: #4caf50;
        color: white;
    }
    
    /* Right Panel - Content Viewer */
    .content-viewer {
        flex: 1;
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
    }
    
    .viewer-header {
        padding: 24px 32px;
        background: #fff;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .viewer-title {
        font-size: 20px;
        font-weight: 600;
        color: #333;
    }
    
    .viewer-body {
        flex: 1;
        overflow-y: auto;
        background: #fff;
    }
    
    .content-display {
        padding: 48px;
        max-width: 900px;
        margin: 0 auto;
    }

    .content-description {
        color: #666;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .video-container {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        background: #000;
        border-radius: 8px;
        margin-bottom: 24px;
    }
    
    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .pdf-viewer {
        width: 100%;
        height: 700px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 24px;
    }
    
    .text-content {
        line-height: 1.8;
        font-size: 16px;
        color: #333;
        margin-bottom: 48px;
    }
    
    .text-content h1, .text-content h2, .text-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        color: #1a1a1a;
    }
    
    .text-content p {
        margin-bottom: 1rem;
    }

    .text-content ul, .text-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }

    .text-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
    }
    
    .external-link-display {
        text-align: center;
        padding: 80px 20px;
    }
    
    .external-link-display h3 {
        font-size: 20px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }
    
    .external-link-display p {
        color: #666;
        margin-bottom: 24px;
        font-size: 15px;
    }
    
    .btn-external {
        background: #7571f9;
        color: white;
        padding: 14px 32px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        font-weight: 500;
        font-size: 15px;
        transition: all 0.2s ease;
    }
    
    .btn-external:hover {
        background: #5f5bd1;
        color: white;
        transform: translateY(-1px);
    }

    /* Content Actions - At Bottom */
    .content-actions {
        margin-top: 48px;
        padding-top: 32px;
        border-top: 2px solid #e0e0e0;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }

    .btn {
        padding: 14px 28px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-complete {
        background: #4caf50;
        color: white;
    }
    
    .btn-complete:hover {
        background: #43a047;
        transform: translateY(-1px);
    }
    
    .btn-complete:disabled {
        background: #e0e0e0;
        color: #999;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-next {
        background: #7571f9;
        color: white;
    }
    
    .btn-next:hover {
        background: #5f5bd1;
        transform: translateY(-1px);
    }

    .btn-assessment {
        background: #7571f9;
        color: white;
        padding: 16px 40px;
        font-size: 16px;
    }

    .btn-assessment:hover {
        background: #5f5bd1;
        transform: translateY(-2px);
    }
    
    /* Completion State */
    .completion-message {
        text-align: center;
        padding: 80px 20px;
    }
    
    .completion-message h2 {
        font-size: 28px;
        font-weight: 600;
        color: #333;
        margin-bottom: 12px;
    }
    
    .completion-message p {
        font-size: 16px;
        color: #666;
        margin-bottom: 32px;
    }
    
    .completion-actions {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-secondary {
        background: #fff;
        color: #7571f9;
        border: 2px solid #7571f9;
    }

    .btn-secondary:hover {
        background: #f8f8ff;
    }
    
    /* Loading State */
    .loading {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 400px;
        color: #999;
        font-size: 15px;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #999;
    }
    
    .empty-state h3 {
        font-size: 20px;
        color: #666;
        margin-bottom: 8px;
    }

    .empty-state p {
        font-size: 15px;
    }

    /* Responsive */
    @media (max-width: 968px) {
        .learning-container {
            flex-direction: column;
        }
        
        .content-sidebar {
            width: 100%;
            height: 40vh;
            border-right: none;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .content-viewer {
            height: 60vh;
        }

        .content-display {
            padding: 24px;
        }

        .content-actions {
            flex-direction: column;
        }

        .content-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="learning-container">
    <!-- Left Panel - Content List -->
    <div class="content-sidebar">
        <div class="week-header">
            <div class="week-title">{{ $currentWeek->title }}</div>
            <div class="week-meta">
                {{ $currentWeek->programModule->name }} • Week {{ $currentWeek->week_number }}
            </div>
            <div class="week-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $currentWeekProgress->progress_percentage ?? 0 }}%"></div>
                </div>
                <div class="progress-text">{{ $currentWeekProgress->progress_percentage ?? 0 }}%</div>
            </div>
        </div>
        
        <div class="content-list" id="contentList">
            @forelse($contents as $content)
                @php
                    $progress = $content->contentProgress->first();
                    $isCompleted = $progress && $progress->is_completed;
                    
                    $iconClass = 'icon-reading';
                    $iconSymbol = '📄';
                    switch($content->content_type) {
                        case 'video':
                            $iconClass = 'icon-video';
                            $iconSymbol = '▶️';
                            break;
                        case 'link':
                            $iconClass = 'icon-link';
                            $iconSymbol = '🔗';
                            break;
                    }
                @endphp
                
                <div class="content-item {{ $loop->first ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}" 
                     data-content-id="{{ $content->id }}"
                     onclick="loadContent({{ $content->id }})">
                    <div class="content-icon {{ $iconClass }}">
                        {{ $iconSymbol }}
                    </div>
                    <div class="content-info">
                        <div class="content-name">{{ $content->title }}</div>
                        <div class="content-meta">
                            {{ ucfirst($content->content_type) }}
                            @if($content->video_duration_minutes)
                                • {{ $content->video_duration_minutes }} min
                            @endif
                        </div>
                    </div>
                    <div class="content-status">
                        <div class="status-check">
                            @if($isCompleted)
                                ✓
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="padding: 40px 20px;">
                    <p>No content available yet</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Right Panel - Content Viewer -->
    <div class="content-viewer">
        <div class="viewer-header">
            <div class="viewer-title" id="viewerTitle">
                @if($contents->isNotEmpty())
                    {{ $contents->first()->title }}
                @else
                    Select Content
                @endif
            </div>
        </div>
        
        <div class="viewer-body" id="viewerBody">
            @if($contents->isNotEmpty())
                <div class="loading">Loading content...</div>
            @else
                <div class="empty-state">
                    <h3>No Content This Week</h3>
                    <p>Check back later or view the calendar for upcoming sessions.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@if($contents->isNotEmpty())
<script>
    const contentsData = {!! json_encode($contentsJson) !!};
    
    const hasAssessment = {{ $currentWeek->has_assessment && $currentWeek->assessment && $currentWeek->assessment->is_active ? 'true' : 'false' }};
    @if($currentWeek->has_assessment && $currentWeek->assessment && $currentWeek->assessment->is_active)
    const assessmentUrl = '{{ route("learner.assessments.start", $currentWeek->assessment->id) }}';
    @endif
    
    let currentContentIndex = 0;
    let progressTrackingInterval = null;
    let contentStartTime = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        if (contentsData.length > 0) {
            loadContent(contentsData[0].id);
        }
    });
    
    function loadContent(contentId) {
        currentContentIndex = contentsData.findIndex(c => c.id === contentId);
        const content = contentsData[currentContentIndex];
        
        document.querySelectorAll('.content-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-content-id="${contentId}"]`).classList.add('active');
        
        document.getElementById('viewerTitle').textContent = content.title;
        
        renderContent(content);
        startProgressTracking(contentId);
    }
    
    function renderContent(content) {
        const viewerBody = document.getElementById('viewerBody');
        let html = '<div class="content-display">';
        
        if (content.description) {
            html += `<div class="content-description">${content.description}</div>`;
        }
        
        switch(content.type) {
            case 'video':
                html += renderVideo(content);
                break;
            case 'pdf':
                html += renderPDF(content);
                break;
            case 'link':
                html += renderLink(content);
                break;
            case 'text':
                html += renderText(content);
                break;
        }

        html += renderActions(content);
        html += '</div>';
        viewerBody.innerHTML = html;
    }

    function renderActions(content) {
        const isCompleted = content.is_completed;
        const isLastContent = currentContentIndex === contentsData.length - 1;
        const allComplete = contentsData.every(c => c.is_completed);

        let html = '<div class="content-actions">';
        
        if (!isCompleted) {
            html += `<button class="btn btn-complete" onclick="markComplete()">Mark Complete</button>`;
        }
        
        if (isLastContent && allComplete && hasAssessment) {
            html += `<a href="${assessmentUrl}" class="btn btn-assessment">Take Assessment</a>`;
        } else if (!isLastContent) {
            html += `<button class="btn btn-next" onclick="nextContent()">Next</button>`;
        }
        
        html += '</div>';
        return html;
    }
    
    function renderVideo(content) {
        let embedUrl = '';
        if (content.video_url && (content.video_url.includes('youtube.com') || content.video_url.includes('youtu.be'))) {
            const videoId = content.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/)?.[1];
            if (videoId) {
                embedUrl = `https://www.youtube.com/embed/${videoId}`;
            }
        } else if (content.video_url && content.video_url.includes('vimeo.com')) {
            const videoId = content.video_url.match(/vimeo\.com\/(\d+)/)?.[1];
            if (videoId) {
                embedUrl = `https://player.vimeo.com/video/${videoId}`;
            }
        }
        
        if (embedUrl) {
            return `
                <div class="video-container">
                    <iframe src="${embedUrl}" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                </div>
                ${content.video_duration ? `<p style="color: #999; font-size: 14px;">Duration: ${content.video_duration} minutes</p>` : ''}
            `;
        }
        
        return `<p style="color: #999;">Video format not supported. <a href="${content.video_url || '#'}" target="_blank">Watch on external site</a></p>`;
    }
    
    function renderPDF(content) {
        if (!content.file_url) {
            return '<p style="color: #999;">PDF file not available.</p>';
        }
        
        return `
            <iframe src="${content.file_url}" class="pdf-viewer"></iframe>
            <div style="text-align: center; margin-top: 16px;">
                <a href="${content.file_url}" download class="btn btn-next">Download PDF</a>
            </div>
        `;
    }
    
    function renderLink(content) {
        if (!content.external_url) {
            return '<p style="color: #999;">External link not available.</p>';
        }
        
        return `
            <div class="external-link-display">
                <h3>External Resource</h3>
                <p>This content is hosted on an external website.</p>
                <a href="${content.external_url}" target="_blank" rel="noopener noreferrer" class="btn-external">
                    Open Resource
                </a>
                <p style="margin-top: 16px; font-size: 13px; color: #999; word-break: break-all;">${content.external_url}</p>
            </div>
        `;
    }
    
    function renderText(content) {
        return `<div class="text-content">${content.text_content || '<p style="color: #999;">No content available.</p>'}</div>`;
    }
    
    function startProgressTracking(contentId) {
        if (progressTrackingInterval) {
            clearInterval(progressTrackingInterval);
        }
        
        contentStartTime = Date.now();
        
        progressTrackingInterval = setInterval(() => {
            updateProgress(contentId);
        }, 30000);
    }
    
    function updateProgress(contentId) {
        const timeSpent = Math.floor((Date.now() - contentStartTime) / 1000);
        
        fetch(`/learner/learning/content/${contentId}/progress`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                progress_percentage: 50,
                time_spent: timeSpent
            })
        }).catch(() => {});
        
        contentStartTime = Date.now();
    }
    
    function markComplete() {
        const content = contentsData[currentContentIndex];
        
        fetch(`/learner/learning/content/${content.id}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Content marked as complete!');
                
                content.is_completed = true;
                document.querySelector(`[data-content-id="${content.id}"]`).classList.add('completed');
                
                updateProgressBar(data.week_completion);
                
                renderContent(content);
                
                const isLastContent = currentContentIndex === contentsData.length - 1;
                if (!isLastContent) {
                    setTimeout(() => nextContent(), 1000);
                }
            }
        })
        .catch(error => {
            toastr.error('Failed to mark as complete');
            console.error(error);
        });
    }
    
    function nextContent() {
        if (currentContentIndex < contentsData.length - 1) {
            loadContent(contentsData[currentContentIndex + 1].id);
            document.getElementById('viewerBody').scrollTop = 0;
        }
    }
    
    function updateProgressBar(percentage) {
        document.querySelector('.progress-fill').style.width = percentage + '%';
        document.querySelector('.progress-text').textContent = percentage + '%';
    }
    
    window.addEventListener('beforeunload', function() {
        if (progressTrackingInterval) {
            clearInterval(progressTrackingInterval);
            if (contentsData[currentContentIndex]) {
                updateProgress(contentsData[currentContentIndex].id);
            }
        }
    });
</script>
@endif
@endsection