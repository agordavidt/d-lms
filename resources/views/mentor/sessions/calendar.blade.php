@extends('layouts.admin')

@section('title', 'My Classes Calendar')
@section('breadcrumb-parent', 'Sessions')
@section('breadcrumb-current', 'Calendar')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    .fc {
        background: white;
        border-radius: 12px;
        padding: 20px;
    }
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
    }
    .fc-button {
        background-color: #7571f9 !important;
        border: none !important;
        border-radius: 6px !important;
        padding: 8px 16px !important;
    }
    .fc-button:hover {
        background-color: #5f5bd7 !important;
    }
    .fc-event {
        border-radius: 4px;
        padding: 4px;
        cursor: pointer;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="filter-card">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="font-weight-semibold mb-2 small">Filter by Program</label>
                    <select id="programFilter" class="form-control">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="font-weight-semibold mb-2 small">Filter by Cohort</label>
                    <select id="cohortFilter" class="form-control">
                        <option value="">All Cohorts</option>
                        @foreach($cohorts as $cohort)
                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <a href="{{ route('mentor.sessions.create') }}" class="btn btn-primary btn-block">
                        <i class="icon-plus mr-2"></i>Schedule New Session
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar -->
<div class="row">
    <div class="col-lg-9 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Quick Stats & Legend -->
    <div class="col-lg-3 mb-4">
        <!-- Stats -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Quick Stats</h6>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">This Week</span>
                        <span class="font-weight-bold" id="statsThisWeek">-</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-primary" style="width: 0%" id="progressThisWeek"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">This Month</span>
                        <span class="font-weight-bold" id="statsThisMonth">-</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 0%" id="progressThisMonth"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted">Total Sessions</span>
                        <span class="font-weight-bold" id="statsTotal">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="font-weight-bold mb-3">Session Types</h6>
                <div class="mb-2">
                    <div class="d-flex align-items-center">
                        <div style="width: 16px; height: 16px; background: #7571f9; border-radius: 3px; margin-right: 8px;"></div>
                        <span class="small">Live Class</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex align-items-center">
                        <div style="width: 16px; height: 16px; background: #4d7cff; border-radius: 3px; margin-right: 8px;"></div>
                        <span class="small">Workshop</span>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="d-flex align-items-center">
                        <div style="width: 16px; height: 16px; background: #6fd96f; border-radius: 3px; margin-right: 8px;"></div>
                        <span class="small">Q&A Session</span>
                    </div>
                </div>
                <div>
                    <div class="d-flex align-items-center">
                        <div style="width: 16px; height: 16px; background: #f29d56; border-radius: 3px; margin-right: 8px;"></div>
                        <span class="small">Assessment</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Detail Modal -->
<div class="modal fade" id="sessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="sessionModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="sessionModalBody">
                <!-- Content loaded via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" id="editSessionBtn" class="btn btn-primary">
                    <i class="icon-pencil mr-1"></i>Edit Session
                </a>
                <a href="#" id="joinSessionBtn" class="btn btn-success" target="_blank" style="display: none;">
                    <i class="icon-video mr-1"></i>Join Meeting
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var programFilter = document.getElementById('programFilter');
    var cohortFilter = document.getElementById('cohortFilter');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            var url = '{{ route("mentor.sessions.events") }}';
            var params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr
            });

            if (programFilter.value) {
                params.append('program_id', programFilter.value);
            }

            if (cohortFilter.value) {
                params.append('cohort_id', cohortFilter.value);
            }

            fetch(url + '?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                    updateStats(data);
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showSessionDetails(info.event);
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        }
    });

    calendar.render();

    // Filter change handlers
    programFilter.addEventListener('change', function() {
        calendar.refetchEvents();
    });

    cohortFilter.addEventListener('change', function() {
        calendar.refetchEvents();
    });

    function showSessionDetails(event) {
        var extendedProps = event.extendedProps;
        
        document.getElementById('sessionModalTitle').textContent = event.title;
        
        var html = `
            <div class="mb-3">
                <p class="mb-2"><strong>Description:</strong></p>
                <p class="text-muted">${extendedProps.description || 'No description provided'}</p>
            </div>
            <div class="mb-2">
                <strong>Start:</strong> ${event.start.toLocaleString()}
            </div>
            <div class="mb-2">
                <strong>End:</strong> ${event.end.toLocaleString()}
            </div>
            ${extendedProps.mentor ? `<div class="mb-2"><strong>Mentor:</strong> ${extendedProps.mentor}</div>` : ''}
        `;
        
        document.getElementById('sessionModalBody').innerHTML = html;
        document.getElementById('editSessionBtn').href = '/mentor/sessions/' + event.id + '/edit';
        
        var joinBtn = document.getElementById('joinSessionBtn');
        if (extendedProps.meet_link) {
            joinBtn.href = extendedProps.meet_link;
            joinBtn.style.display = 'inline-block';
        } else {
            joinBtn.style.display = 'none';
        }
        
        $('#sessionModal').modal('show');
    }

    function updateStats(events) {
        var now = new Date();
        var startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
        var endOfWeek = new Date(now.setDate(now.getDate() - now.getDay() + 6));
        var startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
        var endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);

        var thisWeek = events.filter(e => {
            var eventDate = new Date(e.start);
            return eventDate >= startOfWeek && eventDate <= endOfWeek;
        }).length;

        var thisMonth = events.filter(e => {
            var eventDate = new Date(e.start);
            return eventDate >= startOfMonth && eventDate <= endOfMonth;
        }).length;

        document.getElementById('statsThisWeek').textContent = thisWeek;
        document.getElementById('statsThisMonth').textContent = thisMonth;
        document.getElementById('statsTotal').textContent = events.length;

        // Update progress bars (max 10 for visual)
        document.getElementById('progressThisWeek').style.width = Math.min((thisWeek / 10) * 100, 100) + '%';
        document.getElementById('progressThisMonth').style.width = Math.min((thisMonth / 30) * 100, 100) + '%';
    }
});
</script>
@endpush