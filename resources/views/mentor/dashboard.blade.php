@extends('mentor.layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb">Mentor Portal</div>
        <h1>Good {{ date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->first_name }}</h1>
    </div>
    <a href="{{ route('mentor.programs.create') }}" class="btn btn-primary">New Program</a>
</div>

<div class="container section">

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-value">{{ $stats['programs'] }}</div>
            <div class="stat-label">Programs</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['learners'] }}</div>
            <div class="stat-label">Active Learners</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['drafts'] }}</div>
            <div class="stat-label">Drafts</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['reviews'] }}</div>
            <div class="stat-label">Awaiting Review</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem; align-items: start;">

        {{-- Recent programs --}}
        <div>
            <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.1rem; margin-bottom: 1rem;">Your Programs</h2>

            @forelse($recentPrograms as $program)
            <div class="card" style="margin-bottom: 0.75rem;">
                <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div style="flex: 1; min-width: 0;">
                        <a href="{{ route('mentor.programs.show', $program) }}"
                           style="font-weight: 500; color: var(--text); text-decoration: none; display: block; margin-bottom: 0.2rem;">
                            {{ $program->name }}
                        </a>
                        <span class="text-muted text-small">{{ $program->duration }} &middot; {{ $program->enrollments_count }} learner{{ $program->enrollments_count !== 1 ? 's' : '' }}</span>
                    </div>
                    <span class="badge {{ match($program->status) {
                        'active'       => 'badge-green',
                        'under_review' => 'badge-yellow',
                        'inactive'     => 'badge-gray',
                        default        => 'badge-gray',
                    } }}">
                        {{ ucfirst(str_replace('_', ' ', $program->status)) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="card card-body" style="text-align: center; color: var(--muted); padding: 2.5rem;">
                No programs yet.
                <a href="{{ route('mentor.programs.create') }}" style="color: var(--blue); display: block; margin-top: 0.5rem;">Create your first program</a>
            </div>
            @endforelse

            @if($recentPrograms->count() >= 5)
            <a href="{{ route('mentor.programs.index') }}" class="text-small" style="color: var(--blue);">View all programs</a>
            @endif
        </div>

        {{-- Upcoming sessions --}}
        <div>
            <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.1rem; margin-bottom: 1rem;">Upcoming Sessions</h2>

            @forelse($upcomingSessions as $session)
            <div style="border-bottom: 1px solid var(--border); padding: 0.85rem 0;">
                <div style="font-weight: 500; font-size: 0.875rem;">{{ $session->title }}</div>
                <div class="text-muted text-small">{{ $session->start_time->format('D, M j · g:i A') }}</div>
                <div class="text-muted text-small">{{ $session->program->name }}</div>
                @if($session->meet_link)
                <a href="{{ $session->meet_link }}" target="_blank" class="text-small" style="color: var(--blue);">Join link</a>
                @endif
            </div>
            @empty
            <div style="color: var(--muted); font-size: 0.875rem; padding: 1rem 0;">
                No upcoming sessions.
                <a href="{{ route('mentor.sessions.index') }}" style="color: var(--blue); display: block; margin-top: 0.3rem;">Schedule one</a>
            </div>
            @endforelse
        </div>

    </div>
</div>
@endsection