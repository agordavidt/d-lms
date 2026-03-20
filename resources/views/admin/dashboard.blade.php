@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb">Admin Portal</div>
        <h1>Dashboard</h1>
    </div>
</div>

<div class="container section">

    {{-- Alert: programs waiting for review --}}
    @if($stats['pending_review'] > 0)
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <span style="color: #92400e; font-size: 0.875rem; font-weight: 500;">
            {{ $stats['pending_review'] }} program{{ $stats['pending_review'] !== 1 ? 's' : '' }} waiting for review
        </span>
        <a href="{{ route('admin.programs.index', ['status' => 'under_review']) }}" class="btn btn-sm btn-outline" style="color: #92400e; border-color: #fde68a;">Review now</a>
    </div>
    @endif

    @if($stats['pending_graduations'] > 0)
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.9rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <span style="color: #1e40af; font-size: 0.875rem; font-weight: 500;">
            {{ $stats['pending_graduations'] }} graduation{{ $stats['pending_graduations'] !== 1 ? 's' : '' }} awaiting approval
        </span>
        <a href="{{ route('admin.graduations.index') }}" class="btn btn-sm btn-outline">Review</a>
    </div>
    @endif

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-value">{{ $stats['active_programs'] }}</div>
            <div class="stat-label">Live Programs</div>
        </div>
        <div class="stat-box {{ $stats['pending_review'] > 0 ? 'alert' : '' }}">
            <div class="stat-value">{{ $stats['pending_review'] }}</div>
            <div class="stat-label">Under Review</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['total_mentors'] }}</div>
            <div class="stat-label">Mentors</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['active_enrollments'] }}</div>
            <div class="stat-label">Active Learners</div>
        </div>
        <div class="stat-box highlight">
            <div class="stat-value">₦{{ number_format($stats['revenue_this_month'], 0) }}</div>
            <div class="stat-label">Revenue this month</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $stats['upcoming_sessions'] }}</div>
            <div class="stat-label">Upcoming Sessions</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr 360px; gap: 2rem; align-items: start;">

        {{-- Programs pending review --}}
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem;">Pending Review</h2>
                <a href="{{ route('admin.programs.index', ['status' => 'under_review']) }}" class="text-small" style="color: var(--blue);">View all</a>
            </div>

            @forelse($pendingPrograms as $program)
            <div class="card" style="margin-bottom: 0.6rem;">
                <div class="card-body" style="padding: 0.9rem 1.1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div style="min-width: 0;">
                        <div style="font-weight: 500; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $program->name }}</div>
                        <div class="text-muted text-small">
                            by {{ $program->mentor?->first_name }} {{ $program->mentor?->last_name }}
                            · Submitted {{ $program->submitted_at?->diffForHumans() }}
                        </div>
                    </div>
                    <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-sm btn-outline" style="flex-shrink: 0;">Review</a>
                </div>
            </div>
            @empty
            <div class="card card-body" style="color: var(--muted); font-size: 0.875rem; text-align: center; padding: 2rem;">
                No programs pending review.
            </div>
            @endforelse
        </div>

        {{-- Upcoming sessions --}}
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem;">Upcoming Sessions</h2>
                <a href="{{ route('admin.sessions.index') }}" class="text-small" style="color: var(--blue);">View all</a>
            </div>

            @forelse($upcomingSessions as $session)
            <div style="border-bottom: 1px solid var(--border); padding: 0.8rem 0;">
                <div style="font-weight: 500; font-size: 0.875rem;">{{ $session->title }}</div>
                <div class="text-muted text-small">{{ $session->start_time->format('D, M j · g:i A') }}</div>
                <div class="text-muted text-small">
                    {{ $session->program?->name ?? 'Platform-wide' }}
                    · {{ $session->mentor ? $session->mentor->first_name . ' ' . $session->mentor->last_name : 'Admin' }}
                </div>
            </div>
            @empty
            <div class="text-muted text-small" style="padding: 1rem 0;">No sessions scheduled.</div>
            @endforelse
        </div>

        {{-- Recent payments --}}
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem;">Recent Payments</h2>
                <a href="{{ route('admin.payments.index') }}" class="text-small" style="color: var(--blue);">View all</a>
            </div>

            @forelse($recentPayments as $payment)
            <div style="border-bottom: 1px solid var(--border); padding: 0.75rem 0;">
                <div style="display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-weight: 500; font-size: 0.875rem;">{{ $payment->user->first_name }} {{ $payment->user->last_name }}</span>
                    <span style="font-weight: 600; font-size: 0.875rem; color: var(--success);">₦{{ number_format($payment->final_amount, 0) }}</span>
                </div>
                <div class="text-muted text-small">{{ $payment->program->name }}</div>
            </div>
            @empty
            <div class="text-muted text-small" style="padding: 1rem 0;">No payments yet.</div>
            @endforelse
        </div>

    </div>
</div>
@endsection