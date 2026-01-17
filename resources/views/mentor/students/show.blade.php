@extends('layouts.admin')

@section('title', 'Student Details')

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $student->avatar_url }}" class="rounded-circle mb-3" width="120" height="120">
                <h4>{{ $student->name }}</h4>
                <p class="text-muted">{{ $student->email }}</p>
                @if($student->phone)
                <p class="text-muted">{{ $student->phone }}</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Statistics</h5>
            </div>
            <div class="card-body">
                <p><strong>Total Sessions:</strong> {{ $stats['total_sessions'] }}</p>
                <p><strong>Attended:</strong> {{ $stats['attended_sessions'] }}</p>
                <p><strong>Attendance Rate:</strong> {{ $stats['attendance_percentage'] }}%</p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Enrollments</h5>
            </div>
            <div class="card-body">
                @foreach($enrollments as $enrollment)
                <div class="mb-3 p-3 border rounded">
                    <h6>{{ $enrollment->program->name }}</h6>
                    <p class="mb-1"><strong>Cohort:</strong> {{ $enrollment->cohort->name }}</p>
                    <p class="mb-1"><strong>Progress:</strong> {{ round($enrollment->progress_percentage) }}%</p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5>Session Attendance</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Date</th>
                            <th>Attended</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions->where('status', 'completed') as $session)
                        <tr>
                            <td>{{ $session->title }}</td>
                            <td>{{ $session->start_time->format('M d, Y') }}</td>
                            <td>
                                @if($session->hasAttended($student->id))
                                <span class="badge badge-success">Yes</span>
                                @else
                                <span class="badge badge-danger">No</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection