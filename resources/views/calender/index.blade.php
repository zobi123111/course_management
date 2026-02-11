@extends('layout.app')

@section('title', 'Calendar')
@section('sub-title', 'Calendar')

@section('content')
<style>
     #calendar {
        height: calc(100vh - 160px);
    }

    .fc-timeline-event {
        border-radius: 6px;
        padding: 4px 8px;
        font-weight: 500;
    }

    .fc-timeline-slot-cushion {
        padding: 6px 0;
    }

    .fc-weekday {
        font-size: 11px;
        color: #0b3ca7;
        font-weight: 500;
    }

    .fc-day-number {
        font-size: 15px;
        font-weight: 600;
    }
    /* While dragging selection */
    .fc-highlight {
      background: rgba(158, 5, 158, 0.25) !important; 
    }

    /* Timeline selection */
    .fc-timeline-selection {
        background: rgba(16, 185, 129, 0.35) !important; /* green */
    }

    .booking-meta {
    margin: 0;
}
.booking_day {
    color: #000;
    font-size: 20px !important;
    font-weight: 600 !important;
}

.booking_time {
    color: #000;
    font-size: 16px;
    font-weight: 500;
}

.booking-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 4px;
    font-size: 15px;
    border-radius: 6px;
}

.booking-item:hover {
    background: #f8fafc; /* subtle hover */
}

.booking-icon {
    font-size: 15px;
    width: 20px;
    text-align: center;
    cursor: default;
}

.fc-button {
    font-weight: 600 !important;
    border: none !important;
    margin-right: 2px !important;
    display: flex !important;
    align-items: center;
    font-size: 16px !important;
}
 .viewBookingModal .modal-body {
    padding: 0 !important;
}
.fc-button .fc-icon::before {
    font-size: 20px !important;
}
    .filters_by {
    display: flex;
    font-size: 16px;
    gap: 10px;
    align-items: center;
    margin-top: 15px;
}
.filters_by label {
    display: flex;
    gap: 5px;
    align-items: center;
}

.filters_by h4 {
    font-size: 18px;
    margin: 0;
    line-height: 23px;
    font-weight: 700;
}


    

</style>
<div class="container-fluid mt-3">
    <div style="margin-bottom: 10px;">
          <a class="btn btn-primary me-2 booking-button" id="create_booking">
            Create Booking
          </a>
    </div>
    <div class="mb-3 filters_by">
        <h4>Filter by:- </h4>
    <label class="me-3">
        <input type="checkbox" id="by_resource" checked> Resource
    </label>

    <label class="me-3">
        <input type="checkbox" id="by_instructor"> Instructor
    </label>

    <label>
        <input type="checkbox" id="by_student"> Student
    </label>
</div>

    <div class="row">
        <div class="col-12">
            <div id="calendar"></div>
        </div>
    </div>
</div>


<!-----------------------------Create Booking------------------------------------------------------------------------>
<div class="modal fade" id="newBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="booking_form">
            <div class="modal-body">
                <div class="form-group">
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <label>Select Org Unit</label>
                    <select id="organizationUnits" name="organizationUnits" class="form-control mb-2">
                        <option value="">Select Org Unit</option>
                        @foreach ($organizationUnits as $val)
                        <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                        @endforeach
                    </select>
                    @endif
                    <span class="text-danger error-text" id="error_organizationUnits"></span>
                </div>


                @if(auth()->user()->is_admin == 1 && !empty(auth()->user()->ou_id))
                <input type="hidden" name="organizationUnits" id="organizationUnits" value="{{ auth()->user()->ou_id }}">
                @endif

                @if(auth()->user()->is_admin == 0 && auth()->user()->is_owner == 0 && !empty(auth()->user()->ou_id))
                <input type="hidden" name="organizationUnits" id="organizationUnits" value="{{ auth()->user()->ou_id }}">
                @endif

                <div class="form-group">
                    <label>Start Date & Time</label>
                    <input type="date" name="start_date" id="booking_start" class="form-control mb-2" autocomplete="off">
                    <span class="text-danger error-text" id="error_start_date"></span>
                </div>

                <div class="form-group">
                    <label>End Date & Time</label>
                    <input type="date" name="end_date" id="booking_end" class="form-control mb-2" autocomplete="off">
                    <span class="text-danger error-text" id="error_end_date"></span>
                </div>


                <div class="form-group">
                    <label>Booking Type</label>
                    <select id="booking_type" name="booking_type" class="form-control mb-2">
                        <option value="1">Solo</option>
                        <option value="2">Lesson</option>
                        <option value="3">Standby</option>
                    </select>
                </div>


                <div class="form-group">
                    <label>Resource Type</label>
                    <select name="resource_type" id="resource_type" class="form-control mb-2">
                        <option value="1">Aircraft</option>
                        <option value="2">Simulator</option>
                        <option value="3">Classroom</option>
                    </select>
                </div>


                <div class="form-group">
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <label>Select Student</label>
                    <select id="student" name="student" class="form-control mb-2">
                        <option value="">Select Student</option>
                    </select>
                    @endif
                    <span class="text-danger error-text" id="error_student"></span>
                </div>

                <div class="form-group">
                    <div id="create_resource_wrapper">
                        <label>Resource</label>
                        <select id="resource" name="resource" class="form-control mb-2">
                            <option value="">Select Resource</option>
                        </select>
                    </div>
                    <span class="text-danger error-text" id="error_resource"></span>
                </div>

                <div class="form-group" >
                    <div id="create_instructor_wrapper" style="display:none">
                        <label>Instructor</label>
                        <select name="instructor" id="booking_instructor" class="form-control mb-2">
                            <option value="">Select Instructor</option>
                        </select>
                    </div>
                    <span class="text-danger error-text" id="error_instructor"></span>
                </div>

            </div>
            </form>

            <div class="modal-footer">
                <button class="btn btn-primary" id="saveBookingBtn">Submit Booking</button>
            </div>

        </div>
    </div>
</div>
<!-- //-----------------------------------------End create booking---------------------------------------------------->

<!-- //-------------------------------View Booking Model-------------------------------------------------------------->
<div class="modal  fade viewBookingModal" id="viewBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <!-- <h5 class="modal-title">Booking Details</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded p-3 booking-card">
                    <div class="row">
                            <div class="col-md-8">
                                <ul class="list-unstyled small mb-3 booking-meta">
                                    <li class="booking-item">
                                        <i class="fa-solid fa-plane booking-icon text-secondary"
                                        data-bs-toggle="tooltip"
                                        title="Resource"></i>
                                        <span id="booking_resource"></span>
                                    </li>

                                    <li class="booking-item">
                                    <i class="fa-solid fa-person-chalkboard booking-icon text-primary"
                                            data-bs-toggle="tooltip"
                                            title="Student"></i>
                                        <span id="booking_student"></span>
                                    </li>

                                    <li class="booking-item" id="bookingInstructor_li" style="display:none">
                                    <i class="fa-solid fa-user-graduate booking-icon text-primary"
                                            data-bs-toggle="tooltip"
                                            title="Instructor"></i>
                                        <span id="bookingInstructor"></span>
                                    </li>

                                    <li class="booking-item">
                                        <i class="bi bi-journal-text booking-icon text-info"
                                        data-bs-toggle="tooltip"
                                        title="Lesson"></i>
                                        <span id="booking_lesson">F18: Solo Circuit Consolidation  (Static data)</span>
                                    </li>

                                    <li class="booking-item">
                                        <i class="bi bi-lock-fill booking-icon text-danger"
                                        data-bs-toggle="tooltip"
                                        title="Course"></i>
                                        <span id="booking_code">TK CCTS 1600Z–1645Z BOOKED (Static data)</span>
                                    </li>

                                    <li class="booking-item">
                                        <i class="bi bi-envelope-x booking-icon text-muted"
                                        data-bs-toggle="tooltip"
                                        title="Email Notification"></i>
                                        <span>Notify via email:</span>
                                        <span id="mail_send"></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <!-- Header -->
                                <div class="mb-2">
                                    <div class="text-end text-success small">
                                        <div id="booking_day" class="booking_day"></div>
                                        <div id="booking_time" class="booking_time"></div>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <!-- Footer -->
                    <div class="border-top pt-2 d-flex justify-content-between align-items-center">
                        <span class="small">
                            <strong>Status:</strong>
                            <span id="view_status" class="text-success">Scheduled</span>
                        </span>
                        <i class="bi bi-calendar-event"></i>
                    </div>
                </div>

                <input type="hidden" id="approve_booking_id">
                <input type="hidden" id="approve_organizationUnits">
                <input type="hidden" id="reject_booking_id">
                <input type="hidden" id="delete_booking_id">
            </div>
     @if(auth()->user()->is_owner == 1 || Auth::user()->is_admin == 1)
<div class="modal-footer">

    <button id="editBookingBtn" class="btn btn-primary">Edit</button>
    <button id="deleteBookingBtn" class="btn btn-danger">Delete</button>

    @if(auth()->user()->is_owner == 1)

        <!-- ACTION BUTTONS -->
        <div id="actionButtons" class="d-flex gap-2">
            <button id="approveBtn" class="btn btn-success">
                Approve
            </button>
            <button id="rejectBtn" class="btn btn-danger">
                 Reject
            </button>
        </div>

        <!-- APPROVED STATUS -->
        <div id="approvedStatus" class="alert alert-success d-flex align-items-center mb-0 d-none" style="padding: 8px;">
            <i class="fa fa-circle-check me-2 fs-5"></i>
            <strong>Approved</strong>
            <span class="ms-2 text-muted">(This booking has been approved)</span>
        </div>

        <!-- REJECTED STATUS -->
        <div id="rejectedStatus" class="alert alert-danger d-flex align-items-center mb-0 d-none" style="padding: 8px;">
            <i class="fa fa-circle-xmark me-2 fs-5"></i>
            <strong>Rejected</strong>
            <span class="ms-2 text-muted">(This booking has been rejected)</span>
        </div>

    @endif
</div>
@endif

        </div>
    </div>
</div>
<!-- //-------------------------------End Booking Model--------------------------------------------------------------->

<!-- //-------------------------------Edit Booking Model-------------------------------------------------------------->
 <div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit_booking_id">

                @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                <label>Select Org Unit</label>
                <select id="edit_organizationUnits" class="form-control mb-2">
                    <option value="">Select Org Unit</option>
                    @foreach ($organizationUnits as $val)
                    <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                    @endforeach
                </select>
                @endif
                @if(auth()->user()->is_admin == 1 && !empty(auth()->user()->ou_id))
                <input type="hidden" name="organizationUnits" id="edit_organizationUnits" value="{{ auth()->user()->ou_id }}">
                @endif

                @if(auth()->user()->is_admin == 0 && auth()->user()->is_owner == 0 && !empty(auth()->user()->ou_id))
                <input type="hidden" name="organizationUnits" id="edit_organizationUnits" value="{{ auth()->user()->ou_id }}">
                @endif

                <label>Start Date & Time</label>
                <input type="text" id="edit_booking_start" class="form-control mb-2">

                <label>End Date & Time</label>
                <input type="text" id="edit_booking_end" class="form-control mb-2">

                <label>Booking Type</label>
                <select id="edit_booking_type" class="form-control mb-2">
                    <option value="1">Solo</option>
                    <option value="2">Lesson</option>
                    <option value="3">Standby</option>
                </select>

                <label>Student</label>
                <select id="edit_student" class="form-control mb-2">
                    @foreach ($students as $val)
                    <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                    @endforeach
                </select>


                <div id="edit_resource_wrapper">
                    <label>Resource</label>
                    <select id="edit_resource" class="form-control mb-2">
                        @foreach ($resources as $val)
                        <option value="{{ $val->id }}">{{ $val->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="edit_instructor_wrapper">
                    <label>Instructor</label>
                    <select id="edit_instructor" class="form-control mb-2">
                        <option value="">Select Instructor</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button id="updateBookingBtn" class="btn btn-success">Update</button>
            </div>

        </div>
    </div>
</div>

<!-- //-------------------------------End Edit Booking Model----------------------------------------------------------->
@endsection

@section('js_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- FullCalendar Scheduler v5 -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.11.3/main.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.11.3/main.min.js"></script>



<script>
document.addEventListener('DOMContentLoaded', function () {

    let SITEURL = "{{ url('/') }}";
    let calendar = null;
    let currentMode = 'resource'; // resource | instructor | student

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* ----------------------------------------------------
       INIT / REINIT CALENDAR
    ---------------------------------------------------- */
    function initCalendar() { 

        if (calendar) {
            calendar.destroy();
        }

        /* ✅ Dynamic resource header title */
        let headerTitle = 'Resources';
        if (currentMode === 'instructor') headerTitle = 'Instructor';
        if (currentMode === 'student') headerTitle = 'Student';

        calendar = new FullCalendar.Calendar(
            document.getElementById('calendar'),
            {
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                initialView: 'resourceTimelineDay',
                height: 'auto',
                editable: false,
                selectable: true,
                resourceAreaWidth: '18%',
                slotMinWidth: 90,

                /* ✅ HEADER TEXT CHANGED HERE */
                resourceAreaHeaderContent: headerTitle,

                /* ✅ DAY / WEEK / MONTH ALL ENABLED */
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'resourceTimelineMonth,resourceTimelineWeek,resourceTimelineDay'
                },

                /* ✅ DEFINE ALL VIEWS */
                views: {
                    resourceTimelineMonth: {
                        slotLabelFormat: [
                            { day: 'numeric' },
                            { weekday: 'short' }
                        ]
                    },
                    resourceTimelineWeek: {
                        slotDuration: { days: 1 },
                        slotLabelContent: function (arg) {
                            let d = arg.date;
                            return {
                                html: `
                                    <div style="text-align:center">
                                        <div class="fc-day-number">${d.getDate()}</div>
                                        <div class="fc-weekday">
                                            ${d.toLocaleDateString('en-US', { weekday: 'short' })}
                                        </div>
                                    </div>
                                `
                            };
                        }
                    },
                    resourceTimelineDay: {
                        slotDuration: { hours: 1 },
                        slotLabelFormat: {
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: false
                        }
                    }
                },

                /* ---------------- RESOURCES ---------------- */
                resources: function (fetchInfo, successCallback, failureCallback) {
                    fetch(SITEURL + '/calendar/resources?mode=' + currentMode)
                        .then(res => res.json())
                        .then(data => successCallback(data))
                        .catch(err => failureCallback(err));
                },

                /* ---------------- EVENTS ---------------- */
                events: function (info, successCallback, failureCallback) {
                    $.ajax({
                        url: SITEURL + '/calendar/events',
                        data: { mode: currentMode },
                        success: function (res) {
                            successCallback(res);
                        },
                        error: function (err) {
                            failureCallback(err);
                        }
                    });
                },

                /* ---------------- EVENT UI ---------------- */
        eventContent: function (arg) {
                        let e = arg.event.extendedProps;
                        let viewType = arg.view.type;

                        let typeBadge = '';
                        if (e.booking_type === 'Solo') {
                            typeBadge = `<span style="font-size:11px;opacity:.8">(Solo)</span>`;
                        } else if (e.booking_type === 'Standby') {
                            typeBadge = `<span style="font-size:11px;opacity:.8">(Standby)</span>`;
                        }

                        // DAY VIEW → compact
                        if (viewType === 'resourceTimelineDay') {
                            return {
                                html: `
                                    <div style="font-size: 16px;font-weight: 500;white-space:nowrap;text-align: center;padding: 6px;">
                                        ${arg.event.title}
                                    </div>
                                `
                            };
                        }

                        // WEEK / MONTH → full content
                        return {
                            html: `
                                <div style="font-weight:600">
                                    ${arg.event.title}
                                </div>
                               
                            `
                        };
                    },


                /* ---------------- EVENT CLICK ---------------- */
                eventClick: function (info) {
                    let e = info.event.extendedProps;
                    selectedEvent = e;
                   let mailText = (e.send_mail == 1) ? 'Enabled' : 'Disabled';
                    $('#mail_send').text(mailText);
                    $('#resource_registration').text(e.registration);
                    $('#booking_student').text(e.student);
                    $('#booking_resource').text(e.resource);
                    let statusText = e.status ? e.status.charAt(0).toUpperCase() + e.status.slice(1): 'Scheduled';
                    $('#view_status').text(statusText);

                    $('#approve_booking_id').val(selectedEvent.id);
                    $('#reject_booking_id').val(selectedEvent.id);
                    $('#delete_booking_id').val(selectedEvent.id);
                  
                    // Reset UI
                    $("#editBookingBtn, #deleteBookingBtn").addClass("d-none");
                    $("#actionButtons, #approvedStatus, #rejectedStatus").addClass("d-none");

                    if (e.status === "approved") {

                        $("#approvedStatus").removeClass("d-none");

                    } else if (e.status === "rejected") {

                        $("#rejectedStatus").removeClass("d-none");

                    } else {
                        // pending / scheduled
                        $("#editBookingBtn, #deleteBookingBtn").removeClass("d-none");
                        $("#actionButtons").removeClass("d-none");
                    }
                     
                    if(e.instructor != null){
                        $('#bookingInstructor_li').show();
                       $('#bookingInstructor').text(e.instructor);
                    }else{
                        $('#bookingInstructor_li').hide();
                       $('#bookingInstructor').text('');
                    }
                   
                    $('#booking_day').text(
                        moment(info.event.start).format('ddd, MMM DD YYYY')
                    );

                    $('#booking_time').text(
                        moment(info.event.start).format('HH:mm') +
                        ' - ' +
                        moment(info.event.end).format('HH:mm')
                    );

                    $('#viewBookingModal').modal('show');
                },

                /* ---------------- SELECT SLOT ---------------- */
                select: function (info) {
                    resetBookingForm();

                    startPicker.setDate(
                        moment(info.start).format('YYYY-MM-DD HH:mm'),
                        true
                    );
                    endPicker.setDate(
                        moment(info.end).format('YYYY-MM-DD HH:mm'),
                        true
                    );
                    $('#newBookingModal').modal('show');
                }
            }
        );

        calendar.render();
    }

    /* ----------------------------------------------------
       CHECKBOX HANDLING (ONLY ONE ACTIVE)
    ---------------------------------------------------- */
    function activateOnly(id) {
        $('#by_resource, #by_instructor, #by_student').prop('checked', false);
        $('#' + id).prop('checked', true);
    }

    $('#by_resource').on('change', function () {
        activateOnly('by_resource');
        currentMode = 'resource';
        initCalendar();
    });

    $('#by_instructor').on('change', function () {
        activateOnly('by_instructor');
        currentMode = 'instructor';
        initCalendar();
    });

    $('#by_student').on('change', function () {
        activateOnly('by_student');
        currentMode = 'student';
        initCalendar();
    });

    /* ----------------------------------------------------
       FLATPICKR
    ---------------------------------------------------- */
    let startPicker = flatpickr("#booking_start", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            allowInput: true,
            minuteIncrement: 15,
            onChange: function(selectedDates) {
                if (!selectedDates.length) return;
                let start = moment(selectedDates[0]);
                let end = start.clone().set({
                    hour: 23,
                    minute: 59
                });
                endPicker.setDate(end.format("YYYY-MM-DD HH:mm"), true);
            }

        });

        let endPicker = flatpickr("#booking_end", {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    time_24hr: true,
                    minuteIncrement: 15,
                    allowInput: true,
        });

    function resetBookingForm() {
        $('#booking_form')[0].reset();
        startPicker.clear();
        endPicker.clear();
    }

    $('#create_booking').on('click', function () {
        resetBookingForm();
        $('#newBookingModal').modal('show');
    });

    $('#saveBookingBtn').on('click', function (e) {
        e.preventDefault();
        let formData = new FormData($('#booking_form')[0]);
        $.ajax({
            url: SITEURL + "/booking/store",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                $('#newBookingModal').modal('hide');
                calendar.refetchEvents();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $('.error-text').text('');
                    $.each(xhr.responseJSON.errors, function (k, v) {
                        $('#error_' + k).text(v[0]);
                    });
                } else {
                    alert('Something went wrong');
                }
            }
        });
    });

    initCalendar();
      $("#organizationUnits").on('change', function(e) {
            let ou_id = $(this).val();

            var $groupSelect = $("#group");
            var $resourceSelect = $("#resource");
            var $student = $("#student");
            let $instructor = $("#booking_instructor");

            $groupSelect.empty().append("<option value=''>Select Group</option>").trigger("change");
            $resourceSelect.empty().append("<option value=''>Select Resource</option>").trigger("change");
            $student.empty().append("<option value=''>Select Student</option>").trigger("change");
            $instructor.empty().append("<option value=''>Select Instructor</option>");

            $.ajax({
                url: "/group/students/",
                type: "GET",
                data: {
                    'ou_id': ou_id
                },
                dataType: "json", // Ensures response is treated as JSON
                success: function(response) {
                    if (response.students && Array.isArray(response.students)) { 
                        var options = "<option value=''>Select Student</option>";
                        response.students.forEach(function(value) {
                            options += "<option value='" + value.id + "'>" + value.fname + ' ' + value.lname +
                                "</option>";
                        });
                        $student.html(options);
                        $student.trigger("change");
                    }

                    if (response.org_resource && Array.isArray(response.org_resource)) {
                        var options = "<option value=''>Select Resource </option>";
                        response.org_resource.forEach(function(value) {
                            options += "<option value='" + value.id + "'>" + value.name +
                                "</option>";
                        });
                        $resourceSelect.html(options);
                        $resourceSelect.trigger("change");
                    }
                    if (response.instructors) {
                        response.instructors.forEach(i => {
                            $instructor.append(
                                `<option value="${i.id}">${i.fname} ${i.lname}</option>`
                            );
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
              // On change
        $('#booking_type').on('change', function() { 
            toggleInstructorRequirement('#booking_type', '#booking_instructor');
            handleBookingType(
                '#booking_type',
                '#create_resource_wrapper',
                '#resource',
                '#create_instructor_wrapper',
                '#booking_instructor'
            );
        });

     

        $('#edit_booking_type').on('change', function() {
            handleBookingType(
                '#edit_booking_type',
                '#edit_resource_wrapper',
                '#edit_resource',
                '#edit_instructor_wrapper',
                '#edit_instructor'
            );
        });
        // On page load
        toggleInstructorRequirement('#booking_type', '#booking_instructor');
        $(document).on("click", "#create_booking", function() {
            resetBookingForm();
            // 2️⃣ If Org Unit is prefilled (hidden input), trigger change
            let $ou = $("#organizationUnits");
            if ($ou.length && $ou.val()) {
                $ou.trigger("change");
            }
            // 3️⃣ Default booking type handling
            handleBookingType(
                '#booking_type',
                '#create_resource_wrapper',
                '#resource',
                '#create_instructor_wrapper',
                '#booking_instructor'
            );
            // 4️⃣ Open modal
            $('#newBookingModal').modal('show');
        });

    function handleBookingType(
            bookingTypeSelector,
            resourceWrapper,
            resourceSelector,
            instructorWrapper,
            instructorSelector
        ) {
            let type = $(bookingTypeSelector).val();


            // Always reset first
            $(instructorWrapper).hide();
            $(instructorSelector).val('').prop('required', false);

            if (type == 1) { 
                // SOLO
                $(resourceWrapper).show();
                $(resourceSelector).prop('required', true);

            } else if (type == 2) { 
                // LESSON
                $(instructorWrapper).show();
                $(instructorSelector).prop('required', true);

                $(resourceWrapper).show();
                $(resourceSelector).prop('required', true);

            } else if (type == 3) { 
                // STANDBY
                $(instructorWrapper).show();
                $(instructorSelector).prop('required', false);

                $(resourceWrapper).show();
                $(resourceSelector).prop('required', false);
            }
        }

    function toggleInstructorRequirement(bookingTypeSelector, instructorSelector) {
            let bookingType = $(bookingTypeSelector).val();

            if (bookingType == 1) { // Solo
                $(instructorSelector)
                    .prop('required', false)
                    .val('')
                    .closest('.form-group').removeClass('required');
            } else { // Lesson or Standby
                $(instructorSelector)
                    .prop('required', true)
                    .closest('.form-group').addClass('required');
            }
        }

        let editStartPicker = flatpickr("#edit_booking_start", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
             allowInput: true,
        });

        let editEndPicker = flatpickr("#edit_booking_end", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
             allowInput: true,
        });

        // $("#editBookingBtn").click(function() { 
        //     $('#viewBookingModal').modal('hide');
        //     $('#edit_booking_id').val(selectedEvent.id);
        //     $('#edit_organizationUnits').val(selectedEvent.ou_id).trigger('change');
        //     setTimeout(function() {
        //         $('#edit_resource').val(selectedEvent.resource_id);
        //         $('#edit_student').val(selectedEvent.std_id);
        //         $('#edit_instructor').val(selectedEvent.instructor_id);
        //     }, 300);

        //     $('#edit_booking_type').val(selectedEvent.booking_type_numValue);
        //     console.log(selectedEvent.booking_type_numValue);
        //     if(selectedEvent.booking_type_numValue == 1){
        //        $('#edit_instructor_wrapper').hide();
        //     }else{
        //        $('#edit_instructor_wrapper').show();
        //     }
        //     editStartPicker.setDate(moment(selectedEvent.start).format("YYYY-MM-DD HH:mm"), true);
        //     editEndPicker.setDate(
        //         moment(selectedEvent.end).format("YYYY-MM-DD HH:mm"),
        //         true
        //     );
        //     $('#editBookingModal').modal('show');
        // });
         $("#editBookingBtn").on("click", function () {
            $("#viewBookingModal").modal("hide");
             $('#edit_booking_id').val(selectedEvent.id);
            var booking_id  = $('#edit_booking_id').val();
           
            $.ajax({
                url: SITEURL + "/calendar/edit",
                type: "POST",
                data: {
                    id: booking_id
                },
                success: function (data) {
                  
                    var response = data.response[0];
                 $("#edit_organizationUnits").val(response.ou_id).trigger('change');
                       // Organization Unit
                  

                    // Basic fields
                    $("#edit_booking_id").val(response.id);
                    $("#edit_booking_type").val(response.booking_type);
                    console.log(response.resource);
                    setTimeout(function () {
                                $("#edit_resource").val(String(response.resource)).trigger("change");
                                $("#edit_student").val(response.std_id);
                    $("#edit_instructor").val(response.instructor_id);
                        }, 300);
                    

                    // Instructor toggle
                    if (response.booking_type == 1) {
                        $("#edit_instructor_wrapper").hide();
                    } else {
                        $("#edit_instructor_wrapper").show();
                    }

                    // Date pickers
                    editStartPicker.setDate(
                        moment(response.start).format("YYYY-MM-DD HH:mm"),
                        true
                    );

                    editEndPicker.setDate(
                        moment(response.end).format("YYYY-MM-DD HH:mm"),
                        true
                    );

                    $("#editBookingModal").modal("show");
                },
                error: function (xhr) {
                    toastr.error("Unable to load booking details.");
                    console.error(xhr.responseText);
                }
            });
});


         $("#edit_organizationUnits").on('change', function() {
            let ou_id = $(this).val();
         

            let $resource = $("#edit_resource");
            let $student = $("#edit_student");
            let $instructor = $("#edit_instructor");

            $resource.empty().append("<option value=''>Select Resource</option>");
            $student.empty().append("<option value=''>Select Student</option>");
            $instructor.empty().append("<option value=''>Select Instructor</option>");

            if (!ou_id) return;

            $.ajax({
                url: "/group/students/",
                type: "GET",
                data: {
                    ou_id: ou_id
                },
                dataType: "json",
                success: function(response) {

                    if (response.org_resource) {
                        response.org_resource.forEach(function(r) {
                            $resource.append(
                                `<option value="${r.id}">${r.name}</option>`
                            );
                        });
                    }

                    if (response.students) {
                        response.students.forEach(function(s) {
                            $student.append(
                                `<option value="${s.id}">${s.fname} ${s.lname}</option>`
                            );
                        });
                    }
                    if (response.instructors) {
                        response.instructors.forEach(function(i) {
                            $instructor.append(
                                `<option value="${i.id}">${i.fname} ${i.lname}</option>`
                            );
                        });
                    }
                }
            });
        });


        $("#updateBookingBtn").click(function() {
            let editbookingType = $("#edit_booking_type").val();
            let editinstructor = $("#edit_instructor").val();

            if ((editbookingType == 2 || editbookingType == 3) && !editinstructor) {
                toastr.error('Instructor is required for Lesson or Standby booking');
                return;
            }

            $.post(SITEURL + "/booking/update", {
                id: $('#edit_booking_id').val(),
                organizationUnits: $('#edit_organizationUnits').val(),
                resource_id: $('#edit_resource').val(),
                student: $('#edit_student').val(),
                booking_type: $('#edit_booking_type').val(),
                start: $('#edit_booking_start').val(),
                end: $('#edit_booking_end').val(),
                instructor_id: $('#edit_instructor').val(),
                // send_email: $('#edit_send_email').is(':checked') ? 1 : 0
            }, function() {
                toastr.success("Booking Updated");
                $('#editBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            });
             initCalendar();
        });

        // APPROVE BOOKING
        $("#approveBtn").on("click", function () {
            if (!confirm("Are you sure you want to approve this booking ?")) {
                return;
            }

            $.ajax({
                url: SITEURL + "/booking/approve",
                type: "POST",
                data: {
                    id: $("#approve_booking_id").val(),
                    organizationUnits: $("#approve_organizationUnits").val()
                },
                success: function (response) {
                    toastr.success("Booking Approved");
                    $('#viewBookingModal').modal('hide');
                    $('#calendar').fullCalendar('refetchEvents');
                    initCalendar();
                },
                error: function (xhr) {
                    toastr.error("Something went wrong. Please try again.");
                    console.error(xhr.responseText);
                }
            });

        });

        $("#rejectBtn").on("click", function () {
            if (!confirm("Are you sure you want to reject this booking ?")) {
                return;
            }
            $.ajax({
                url: SITEURL + "/booking/reject",
                type: "POST",
                data: {
                    id: $("#reject_booking_id").val(),
                    organizationUnits: $("#approve_organizationUnits").val()
                },
                success: function () {
                    toastr.error("Booking Rejected");
                    $('#viewBookingModal').modal('hide');
                    $('#calendar').fullCalendar('refetchEvents');
                    initCalendar();
                },
                error: function (xhr) {
                    toastr.error("Something went wrong. Please try again.");
                    console.error(xhr.responseText);
                }
            });
        });

        
    $("#deleteBookingBtn").on("click", function () {
        if (!confirm("Are you sure you want to delete this booking ?")) return;
        $.ajax({
            url: SITEURL + "/booking/delete",
            type: "POST",
            data: {
                id: $("#delete_booking_id").val(),
            },
            success: function (response) {
                toastr.success("Booking Deleted");
                $("#viewBookingModal").modal("hide");
                $("#calendar").fullCalendar("refetchEvents");
            },
            error: function (xhr) {
                toastr.error("Unable to cancel booking. Please try again.");
                console.error(xhr.responseText);
            }
        });

    });




});
</script>
@endsection
