@extends('layouts.admin')

@section('title', 'My Schedule')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'My Schedule')

@push('styles')
<link href="{{ asset('assets/plugins/fullcalendar/css/fullcalendar.min.css') }}" rel="stylesheet">
<style>
.fc-event {
    border-radius: 8px;
    padding: 5px;
    cursor: pointer;
}
.upcoming-session {
    border-left: 4px solid #7571f9;
    padding: 15px;
    margin-bottom: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">My Class Schedule</h4>
                
                <div class="row">
                    <div class="col-lg-3 mt-3">
                        <h5>Upcoming Sessions</h5>
                        <div id="upcomingSessions">
                            @forelse($upcomingSessions as $session)
                            <div class="upcoming-session">
                                <h6 class="mb-1">{{ $session->title }}</h6>
                                <p class="mb-1 small">
                                    <i class="icon-calendar"></i> {{ $session->start_time->format('M d, Y') }}<br>
                                    <i class="icon-clock"></i> {{ $session->start_time->format('h:i A') }} - {{ $session->end_time->format('h:i A') }}
                                </p>
                                <p class="mb-1 small">
                                    <i class="icon-user"></i> {{ $session->mentor ? $session->mentor->name : 'TBA' }}
                                </p>
                                @if($session->meet_link)
                                <a href="{{ $session->meet_link }}" target="_blank" class="btn btn-sm btn-primary gradient-1 mt-2">
                                    Join Meeting
                                </a>
                                @endif
                            </div>
                            @empty
                            <p class="text-muted">No upcoming sessions</p>
                            @endforelse
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5>Legend</h5>
                        <div class="mb-2">
                            <span style="display: inline-block; width: 20px; height: 20px; background: #7571f9; border-radius: 4px;"></span>
                            <small class="ml-2">Live Class</small>
                        </div>
                        <div class="mb-2">
                            <span style="display: inline-block; width: 20px; height: 20px; background: #4d7cff; border-radius: 4px;"></span>
                            <small class="ml-2">Workshop</small>
                        </div>
                        <div class="mb-2">
                            <span style="display: inline-block; width: 20px; height: 20px; background: #6fd96f; border-radius: 4px;"></span>
                            <small class="ml-2">Q&A Session</small>
                        </div>
                        <div class="mb-2">
                            <span style="display: inline-block; width: 20px; height: 20px; background: #f29d56; border-radius: 4px;"></span>
                            <small class="ml-2">Assessment</small>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Detail Modal -->
<div class="modal fade" id="sessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header gradient-1">
                <h5 class="modal-title text-white" id="sessionTitle"></h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="sessionDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a id="joinMeetingBtn" href="#" target="_blank" class="btn btn-primary gradient-1" style="display: none;">
                    Join Meeting
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/jqueryui/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/fullcalendar/js/fullcalendar.min.js') }}"></script>

<script>
$(document).ready(function() {
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: false,
        droppable: false,
        eventLimit: true,
        
        events: '{{ route("learner.sessions.events") }}',
        
        eventClick: function(event) {
            $('#sessionTitle').text(event.title);
            
            var details = `
                <div class="mb-3">
                    <h6>Description</h6>
                    <p>${event.description || 'No description available'}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Schedule</h6>
                    <p>
                        <strong>Start:</strong> ${moment(event.start).format('MMM DD, YYYY h:mm A')}<br>
                        <strong>End:</strong> ${moment(event.end).format('MMM DD, YYYY h:mm A')}<br>
                        <strong>Duration:</strong> ${moment(event.end).diff(moment(event.start), 'minutes')} minutes
                    </p>
                </div>
                
                <div class="mb-3">
                    <h6>Instructor</h6>
                    <p>${event.mentor}</p>
                </div>
            `;
            
            $('#sessionDetails').html(details);
            
            if (event.meet_link) {
                $('#joinMeetingBtn').attr('href', event.meet_link).show();
            } else {
                $('#joinMeetingBtn').hide();
            }
            
            $('#sessionModal').modal('show');
        }
    });
});
</script>
@endpush