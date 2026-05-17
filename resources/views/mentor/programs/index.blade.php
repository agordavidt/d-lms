@extends('layouts.app')
@section('title', 'My Programs')

@section('content')
<div class="page-header">
    <div>
        <h1>Course Management</h1>
    </div>
    <a href="{{ route('mentor.programs.create') }}" class="btn btn-primary">New Program</a>
</div>

<div class="container section">

    @if($programs->isEmpty() && !request('status'))
    <div class="card card-body" style="text-align:center;padding:4rem 2rem;color:var(--muted);">
        <p style="font-size:1.05rem;margin-bottom:0.5rem;">You haven't created any programs yet.</p>
        <p style="font-size:0.875rem;margin-bottom:1.5rem;">Build your first program and submit it for review when it's ready.</p>
        <a href="{{ route('mentor.programs.create') }}" class="btn btn-primary">Create a Program</a>
    </div>
    @else

    @php
        $currentStatus = request('status', '');
        $tabs = [
            ''             => 'All',
            'draft'        => 'Draft',
            'under_review' => 'Under Review',
            'active'       => 'Live',
            'inactive'     => 'Offline',
        ];
    @endphp

    <div style="display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:1.5rem;">
        @foreach($tabs as $val => $label)
        @php
            $isActive = $currentStatus === $val;
            $params   = $val !== '' ? ['status' => $val] : [];
        @endphp
        <a href="{{ route('mentor.programs.index', $params) }}"
           style="padding:0.6rem 1rem;font-size:0.875rem;font-weight:500;text-decoration:none;
                  color:{{ $isActive ? 'var(--blue)' : 'var(--muted)' }};
                  border-bottom:2px solid {{ $isActive ? 'var(--blue)' : 'transparent' }};
                  margin-bottom:-1px;white-space:nowrap;">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($programs->isEmpty())
    <div class="card card-body" style="text-align:center;padding:3rem 2rem;color:var(--muted);">
        <p>No {{ $tabs[$currentStatus] ?? '' }} programs found.</p>
        @if($currentStatus !== '')
        <a href="{{ route('mentor.programs.index') }}" class="btn btn-ghost" style="margin-top:1rem;">View all</a>
        @endif
    </div>
    @else

    <div style="display:grid;gap:0.75rem;">
        @foreach($programs as $program)
        <div class="card">
            <div class="card-body" style="display:flex;gap:1.25rem;align-items:center;">

                <div style="width:72px;height:72px;flex-shrink:0;border-radius:6px;overflow:hidden;background:var(--blue-light);">
                    @if($program->cover_image)
                        <img src="{{ asset('storage/'.$program->cover_image) }}"
                             style="width:100%;height:100%;object-fit:cover;" alt="">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--blue);font-weight:600;font-size:1.1rem;">
                            {{ strtoupper(substr($program->name,0,2)) }}
                        </div>
                    @endif
                </div>

                <div style="flex:1;min-width:0;">
                    <div style="font-weight:500;margin-bottom:0.2rem;">
                        <a href="{{ route('mentor.programs.show', $program) }}"
                           style="color:var(--text);text-decoration:none;">{{ $program->name }}</a>
                    </div>
                    <div class="text-muted text-small">
                        {{ $program->duration }}
                        &middot; {{ $program->modules_count }} module{{ $program->modules_count !== 1 ? 's' : '' }}
                        &middot; {{ $program->enrollments_count }} learner{{ $program->enrollments_count !== 1 ? 's' : '' }}
                    </div>
                    @if($program->review_notes && in_array($program->status, ['draft','inactive']))
                    <div class="text-small" style="color:var(--warning);margin-top:0.3rem;">
                        Admin note: {{ Str::limit($program->review_notes, 100) }}
                    </div>
                    @endif
                </div>

                <span class="badge {{ match($program->status) {
                    'active'       => 'badge-green',
                    'under_review' => 'badge-yellow',
                    default        => 'badge-gray',
                } }}">
                    {{ match($program->status) {
                        'active'       => 'Live',
                        'under_review' => 'Under Review',
                        'inactive'     => 'Offline',
                        default        => 'Draft',
                    } }}
                </span>

                <div style="display:flex;gap:0.5rem;flex-shrink:0;">
                    <a href="{{ route('mentor.programs.show', $program) }}" class="btn btn-sm btn-outline">Manage</a>
                    @if($program->status === 'draft')
                    <a href="{{ route('mentor.programs.edit', $program) }}" class="btn btn-sm btn-ghost">Edit</a>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top:1.5rem;">
        {{ $programs->withQueryString()->links() }}
    </div>

    @endif
    @endif

</div>
@endsection