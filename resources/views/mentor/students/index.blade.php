@extends('mentor.layouts.app')
@section('title', 'My Learners')

@section('content')
<div class="page-header">
    <div><h1>My Learners</h1></div>
</div>

<div class="container section">

    {{-- Filters --}}
    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" style="max-width: 260px;"
               placeholder="Search by name or email" value="{{ request('search') }}">
        <select name="program_id" class="form-control" style="max-width: 240px;">
            <option value="">All Programs</option>
            @foreach($programs as $p)
            <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        @if(request()->hasAny(['search', 'program_id']))
        <a href="{{ route('mentor.students.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    @if($enrollments->isEmpty())
    <div class="card card-body" style="text-align: center; color: var(--muted); padding: 3rem;">
        No learners found.
    </div>
    @else
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Learner</th>
                    <th>Program</th>
                    <th>Progress</th>
                    <th>Enrolled</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $enrollment)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</div>
                        <div class="text-muted text-small">{{ $enrollment->user->email }}</div>
                    </td>
                    <td style="color: var(--muted); font-size: 0.875rem;">{{ $enrollment->program->name }}</td>
                    <td style="min-width: 140px;">
                        <div class="progress-bar-track" style="margin-bottom: 0.25rem;">
                            <div class="progress-bar-fill" style="width: {{ $enrollment->progress_percentage }}%"></div>
                        </div>
                        <span class="text-muted text-small">{{ number_format($enrollment->progress_percentage, 0) }}%</span>
                    </td>
                    <td class="text-muted text-small">{{ \Carbon\Carbon::parse($enrollment->enrolled_at)->format('M j, Y') }}</td>
                    <td>
                        <a href="{{ route('mentor.students.show', $enrollment) }}" class="btn btn-sm btn-ghost">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.25rem;">{{ $enrollments->links() }}</div>
    @endif

</div>
@endsection