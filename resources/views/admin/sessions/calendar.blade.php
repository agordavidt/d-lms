@extends('layouts.admin')

@section('title', 'Calendar')
@section('breadcrumb-parent', 'Dashboard')
@section('breadcrumb-current', 'Calendar')

@push('styles')
<link href="{{ asset('assets/plugins/fullcalendar/css/fullcalendar.min.css') }}" rel="stylesheet">
<style>
.fc-event {
    border-radius: 8px;
    padding: 5px;
    cursor: pointer;
}
.external-event {
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
    cursor: move;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex justify-content-between align-items-center">
                    <h4>Live Sessions Calendar</h4>
                    <a href="{{ route('admin.sessions.create') }}" class="btn btn-primary gradient-1">
                        Schedule New Session
                    </a>
                </div>
                
                <div class="row">
                    <div class="col-lg-3 mt-3">
                        <h5>Filters</h5>
                        
                        <div class="form-group">
                            <label>Program</label>
                            <select id="programFilter" class="form-control">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Cohort</label>
                            <select id="cohortFilter" class="form-control">
                                <option value="">All Cohorts</option>
                                @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5>Session Types</h5>
                        <div class="external-events">
                            <div class="external-event text-white" style="background: #7571f9;">
                                Live Class
                            </div>
                            <div class="external-event text-white" style="background: #4d7cff;">
                                Workshop
                            </div>
                            <div class="external-event text-white" style="background: #6fd96f;">
                                Q&A Session
                            </div>
                            <div class="external-event text-white" style="background: #f29d56;">
                                Assessment
                            </div>
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

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header gradient-1">
                <h5 class="modal-title text-white" id="eventTitle"></h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="eventDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a id="editEventBtn" href="#" class="btn btn-primary">Edit</a>
                <button id="deleteEventBtn" type="button" class="btn btn-danger">Delete</button>
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
    var calendar = $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        editable: false,
        droppable: false,
        eventLimit: true,
        selectable: true,
        selectHelper: true,
        
        events: function(start, end, timezone, callback) {
            var programId = $('#programFilter').val();
            var cohortId = $('#cohortFilter').val();
            
            $.ajax({
                url: '{{ route("admin.sessions.events") }}',
                data: {
                    start: start.format(),
                    end: end.format(),
                    program_id: programId,
                    cohort_id: cohortId
                },
                success: function(data) {
                    callback(data);
                }
            });
        },
        
        eventClick: function(event) {
            $('#eventTitle').text(event.title);
            
            var details = `
                <p><strong>Description:</strong> ${event.description || 'No description'}</p>
                <p><strong>Start:</strong> ${moment(event.start).format('MMM DD, YYYY h:mm A')}</p>
                <p><strong>End:</strong> ${moment(event.end).format('MMM DD, YYYY h:mm A')}</p>
                <p><strong>Mentor:</strong> ${event.mentor}</p>
                ${event.meet_link ? `<p><strong>Meet Link:</strong> <a href="${event.meet_link}" target="_blank">Join Meeting</a></p>` : ''}
            `;
            
            $('#eventDetails').html(details);
            $('#editEventBtn').attr('href', `/admin/sessions/${event.id}/edit`);
            $('#deleteEventBtn').data('id', event.id);
            $('#eventModal').modal('show');
        }
    });
    
    // Reload calendar on filter change
    $('#programFilter, #cohortFilter').on('change', function() {
        calendar.fullCalendar('refetchEvents');
    });
    
    // Delete event
    $(document).on('click', '#deleteEventBtn', function() {
        var eventId = $(this).data('id');
        
        if (confirm('Delete this session?')) {
            $.ajax({
                url: `/admin/sessions/${eventId}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    $('#eventModal').modal('hide');
                    calendar.fullCalendar('refetchEvents');
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to delete');
                }
            });
        }
    });
});
</script>
@endpush