@extends('layouts.admin')

@section('title', 'Curriculum')
@section('breadcrumb-parent', 'Home')
@section('breadcrumb-current', 'Curriculum')

@push('styles')
<style>
    .curriculum-header {
        background: #fff;
        padding: 24px;
        border-radius: 8px;
        margin-bottom: 24px;
        border: 1px solid #e0e0e0;
    }
    
    .program-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    
    .program-meta {
        color: #666;
        font-size: 14px;
    }
    
    .overall-progress {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }
    
    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
        color: #666;
    }
    
    .progress-bar-container {
        background: #f5f5f5;
        height: 10px;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .progress-bar-fill {
        background: linear-gradient(90deg, #7571f9 0%, #9c98ff 100%);
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .module-section {
        background: #fff;
        border-radius: 8px;
        margin-bottom: 16px;
        border: 1px solid #e0e0e0;
        overflow: hidden;
    }
    
    .module-header {
        padding: 20px 24px;
        background: #f8f8ff;
        border-bottom: 1px solid #e0e0e0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.2s ease;
    }
    
    .module-header:hover {
        background: #f0f0ff;
    }
    
    .module-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .module-number {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #7571f9;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    
    .module-stats {
        font-size: 13px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .week-list {
        display: none;
    }
    
    .week-list.expanded {
        display: block;
    }
    
    .week-item {
        border-bottom: 1px solid #f5f5f5;
    }
    
    .week-header {
        padding: 16px 24px;
        background: #fafafa;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: background 0.2s ease;
    }
    
    .week-header:hover {
        background: #f5f5f5;
    }
    
    .week-title-area {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }
    
    .week-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e0e0e0;
        color: #666;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }
    
    .week-number.unlocked {
        background: #7571f9;
        color: white;
    }
    
    .week-title-text {
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    
    .week-status {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .status-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    
    .status-locked {
        background: #f5f5f5;
        color: #999;
    }
    
    .status-completed {
        background: #e8f5e9;
        color: #2e7d32;
    }
    
    .status-in-progress {
        background: #fff3e0;
        color: #f57c00;
    }
    
    .content-list {
        display: none;
        background: #fff;
    }
    
    .content-list.expanded {
        display: block;
    }
    
    .content-row {
        display: flex;
        align-items: center;
        padding: 12px 24px 12px 68px;
        border-bottom: 1px solid #f8f8f8;
        transition: background 0.2s ease;
    }
    
    .content-row:hover {
        background: #fafafa;
    }
    
    .content-row:last-child {
        border-bottom: none;
    }
    
    .content-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .icon-video { background: #e3f2fd; color: #1976d2; }
    .icon-reading { background: #f3e5f5; color: #7b1fa2; }
    .icon-assignment { background: #fff3e0; color: #f57c00; }
    
    .content-info {
        flex: 1;
        min-width: 0;
    }
    
    .content-name {
        font-size: 14px;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .content-type {
        font-size: 12px;
        color: #999;
        margin-top: 2px;
    }
    
    .content-progress {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: 16px;
    }
    
    .progress-percentage {
        font-size: 13px;
        color: #666;
        min-width: 40px;
        text-align: right;
    }
    
    .chevron {
        transition: transform 0.3s ease;
    }
    
    .chevron.rotated {
        transform: rotate(90deg);
    }
    
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #999;
    }
</style>
@endpush

@section('content')
<!-- Program Header -->
<div class="curriculum-header">
    <div class="program-title">{{ $enrollment->program->name }}</div>
    <div class="program-meta">
        {{ $enrollment->cohort->name }} • 
        Started {{ $enrollment->enrolled_at->format('M d, Y') }}
    </div>
    
    <div class="overall-progress">
        <div class="progress-label">
            <span>Overall Progress</span>
            <span><strong>{{ $completedContents }}</strong> of <strong>{{ $totalContents }}</strong> contents completed</span>
        </div>
        <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width: {{ $overallProgress }}%"></div>
        </div>
    </div>
</div>

<!-- Modules List -->
@forelse($modules as $index => $module)
    @php
        $moduleWeeks = $module->weeks;
        $totalWeekContents = 0;
        $completedWeekContents = 0;
        
        foreach ($moduleWeeks as $week) {
            foreach ($week->contents as $content) {
                $totalWeekContents++;
                if ($content->contentProgress->first()?->is_completed) {
                    $completedWeekContents++;
                }
            }
        }
        
        $moduleProgress = $totalWeekContents > 0 ? round(($completedWeekContents / $totalWeekContents) * 100) : 0;
    @endphp
    
    <div class="module-section">
        <div class="module-header" onclick="toggleModule({{ $module->id }})">
            <div class="module-title">
                <div class="module-number">{{ $index + 1 }}</div>
                <span>{{ $module->name }}</span>
            </div>
            <div class="module-stats">
                <span>{{ $moduleWeeks->count() }} Weeks</span>
                <span>{{ $moduleProgress }}% Complete</span>
                <i class="icon-arrow-right chevron" id="chevron-{{ $module->id }}"></i>
            </div>
        </div>
        
        <div class="week-list" id="module-{{ $module->id }}">
            @foreach($moduleWeeks as $week)
                @php
                    $weekProgress = $week->weekProgress->first();
                    $isUnlocked = $weekProgress && $weekProgress->is_unlocked;
                    $isCompleted = $weekProgress && $weekProgress->is_completed;
                    
                    $weekContentsCount = $week->contents->count();
                    $weekCompletedCount = $week->contents->filter(function($c) {
                        return $c->contentProgress->first()?->is_completed;
                    })->count();
                @endphp
                
                <div class="week-item">
                    <div class="week-header" onclick="toggleWeek({{ $week->id }})">
                        <div class="week-title-area">
                            <div class="week-number {{ $isUnlocked ? 'unlocked' : '' }}">
                                W{{ $week->week_number }}
                            </div>
                            <span class="week-title-text">{{ $week->title }}</span>
                        </div>
                        
                        <div class="week-status">
                            <span style="font-size: 13px; color: #666;">
                                {{ $weekCompletedCount }}/{{ $weekContentsCount }}
                            </span>
                            
                            <div class="status-icon {{ $isCompleted ? 'status-completed' : ($isUnlocked ? 'status-in-progress' : 'status-locked') }}">
                                @if($isCompleted)
                                    <i class="icon-check"></i>
                                @elseif($isUnlocked)
                                    <i class="icon-clock"></i>
                                @else
                                    <i class="icon-lock"></i>
                                @endif
                            </div>
                            
                            <i class="icon-arrow-right chevron" id="chevron-week-{{ $week->id }}"></i>
                        </div>
                    </div>
                    
                    <div class="content-list" id="week-{{ $week->id }}">
                        @foreach($week->contents as $content)
                            @php
                                $contentProgress = $content->contentProgress->first();
                                $progressPercent = $contentProgress ? $contentProgress->progress_percentage : 0;
                                $isContentCompleted = $contentProgress && $contentProgress->is_completed;
                                
                                $iconClass = 'icon-reading';
                                switch($content->content_type) {
                                    case 'video':
                                        $iconClass = 'icon-video';
                                        break;
                                    case 'assignment':
                                        $iconClass = 'icon-assignment';
                                        break;
                                }
                            @endphp
                            
                            <div class="content-row">
                                <div class="content-icon {{ $iconClass }}">
                                    @if($content->content_type === 'video')
                                        <i class="icon-control-play"></i>
                                    @elseif($content->content_type === 'assignment')
                                        <i class="icon-pencil"></i>
                                    @else
                                        <i class="icon-doc"></i>
                                    @endif
                                </div>
                                
                                <div class="content-info">
                                    <div class="content-name">{{ $content->title }}</div>
                                    <div class="content-type">
                                        {{ ucfirst($content->content_type) }}
                                        @if($content->duration_minutes)
                                            • {{ $content->duration_minutes }} min
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="content-progress">
                                    <span class="progress-percentage">
                                        @if($isContentCompleted)
                                            ✓ Done
                                        @elseif($progressPercent > 0)
                                            {{ $progressPercent }}%
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="empty-state">
        <i class="icon-layers" style="font-size: 48px; margin-bottom: 12px;"></i>
        <p>No curriculum available yet.</p>
    </div>
@endforelse
@endsection

@push('scripts')
<script>
function toggleModule(moduleId) {
    const moduleContent = document.getElementById('module-' + moduleId);
    const chevron = document.getElementById('chevron-' + moduleId);
    
    if (moduleContent.classList.contains('expanded')) {
        moduleContent.classList.remove('expanded');
        chevron.classList.remove('rotated');
    } else {
        moduleContent.classList.add('expanded');
        chevron.classList.add('rotated');
    }
}

function toggleWeek(weekId) {
    const weekContent = document.getElementById('week-' + weekId);
    const chevron = document.getElementById('chevron-week-' + weekId);
    
    if (weekContent.classList.contains('expanded')) {
        weekContent.classList.remove('expanded');
        chevron.classList.remove('rotated');
    } else {
        weekContent.classList.add('expanded');
        chevron.classList.add('rotated');
    }
}
</script>
@endpush