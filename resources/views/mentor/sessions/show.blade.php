@extends('layouts.admin')

@section('title', 'Session Details')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4>{{ $session->title }}</h4>
            </div>
            <div class="card-body">
                <p><strong>Program:</strong> {{ $session->program->name }}</p>
                <p><strong>Cohort:</strong> {{ $session->cohort->name }}</p>
                <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $session->session_type)) }}</p>
                <p><strong>Start:</strong> {{ $session->start_time->format('F d, Y g:i A') }}</p>
                <p><strong>End:</strong> {{ $session->end_time->format('F d, Y g:i A') }}</p>
                <p><strong>Duration:</strong> {{ $session->duration_minutes }} minutes</p>
                <p><strong>Status:</strong> <span class="badge badge-{{ $session->status === 'completed' ? 'success' : 'primary' }}">{{ ucfirst($session->status) }}</span></p>

                @if($session->meet_link)
                <p><strong>Meet Link:</strong> <a href="{{ $session->meet_link }}" target="_blank">{{ $session->meet_link }}</a></p>
                @endif

                @if($session->description)
                <hr>
                <h5>Description</h5>
                <p>{{ $session->description }}</p>
                @endif

                @if($session->status === 'completed')
                <hr>
                <h5>Attendance ({{ $attendees->count() }} students)</h5>
                <ul>
                    @foreach($attendees as $attendee)
                    <li>{{ $attendee->name }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection