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
        background: rgba(16, 185, 129, 0.35) !important;
        /* green */
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
        background: #f8fafc;
        /* subtle hover */
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

    span#utc_offset {
        color: #000;
        font-weight: 700 !important;

    }

    span.offset_flag {
        font-size: large;
        font-weight: 600;
    }

    .tooltip-inner {
        padding: 15px;
        color: #000;
        text-align: left;
        background-color: #ffffff;
        border-radius: .25rem;
        border: 2px solid #a9a8a8;
        min-width: 380px;
        width: 100%;
        font-size: 14px;
    }

    .calendar-tooltip .tooltip-row {
        margin-bottom: 10px;
        color: #000 !important;
    }

    .no-change {
        pointer-events: none;
        background-color: #e9ecef;
    }
</style>

<div class="container-fluid mt-3">
    <div style="margin-bottom: 10px;">
        <a class="btn btn-primary me-2 booking-button" id="create_booking">
            Create Booking
        </a>
    </div>
    <div class="mb-3 filters_by">
        <h4>Filter by:- </h4>Create Booking

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
                        <div class="col-md-7">
                            <ul class="list-unstyled small mb-3 booking-meta">
                                <li class="booking-item">
                                    <i class="fa-solid fa-plane booking-icon text-secondary"
                                        data-bs-toggle="tooltip"
                                        title="Resource"></i>
                                    <a id="booking_resource"></a>
                                </li>

                                <li class="booking-item">
                                    <i class="fa-solid fa-person-chalkboard booking-icon text-primary"
                                        data-bs-toggle="tooltip"
                                        title="Student"></i>
                                    <a id="booking_student"></a>
                                </li>

                                <li class="booking-item" id="bookingInstructor_li" style="display:none">
                                    <i class="fa-solid fa-user-graduate booking-icon text-primary"
                                        data-bs-toggle="tooltip"
                                        title="Instructor"></i>
                                    <a id="bookingInstructor"></a>
                                </li>

                                <li class="booking-item" id="booking_course_li">
                                    <i class="bi bi-journal-text booking-icon text-info"
                                        data-bs-toggle="tooltip"
                                        title="Lesson"></i>
                                    <a id="booking_course"></a>
                                </li>

                                <li class="booking-item" id="booking_lesson_li">
                                    <i class="bi bi-lock-fill booking-icon text-danger"
                                        data-bs-toggle="tooltip"
                                        title="Course"></i>
                                    <a id="booking_lesson"></a>
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
                        <div class="col-md-5">
                            <div class="text-end text-success">
                                <div id="booking_day_div">
                                    <span id="booking_day" class="booking_day"></span>
                                    <span id="utc_offset" class="utc_offset small text-muted ms-2"></span>
                                </div>
                                <div id="booking_time" class="booking_time"></div>
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
            @if(auth()->user()->is_owner == 1 || Auth::user()->is_admin == 1 || auth()->user()->role == 3)
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
<!-- //-------------------------------End view Booking Model--------------------------------------------------------------->

<!-- //-------------------------------Edit Booking Model-------------------------------------------------------------->
<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="edit_booking_form">
                <div class="modal-body">
                    <div class="text-end">
                        <div class="form-check d-inline-block">
                            <input type="checkbox" class="form-check-input" id="instructor_training">
                            <label for="instructor_training" class="form-check-label">Instructor Training</label>
                        </div>
                    </div>
                    <input type="hidden" id="edit_booking_id">
                    <div class="row">
                        @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                        <div class="col-md-6 form-group">
                            <label>Select Org Unit</label>
                            <select id="edit_organizationUnits" name="organizationUnits" class="form-control mb-2">
                                <option value="">Select Org Unit</option>
                                @foreach ($organizationUnits as $val)
                                <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <input type="hidden" name="event_id" id="event_id" />

                        <div class="col-md-6 form-group">
                            <label>Select Student</label>
                            <select id="edit_student" name="student" class="form-control mb-2">
                            </select>
                        </div>
                    </div>

                    @if(auth()->user()->is_admin == 1 && !empty(auth()->user()->ou_id))
                    <input type="hidden" id="edit_organizationUnits" value="{{ auth()->user()->ou_id }}">
                    @endif

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="edit_booking_start" class="form-control mb-2">
                            <span class="text-danger edit-error-text" id="editerror_start_date"></span>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>End Date</label>
                            <input type="date" name="end_date" id="edit_booking_end" class="form-control mb-2">
                            <span class="text-danger edit-error-text" id="editerror_end_date"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Resource Type</label>
                            <select name="resource_type" id="resource_type" class="form-control mb-2">
                                <option value="1">Aircraft</option>
                                <option value="2">Simulator</option>
                                <option value="3">Classroom</option>
                            </select>
                            <span class="text-danger edit-error-text" id="editerror_resource_type"></span>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Booking Type</label>
                            <select id="edit_booking_type" name="booking_type" class="form-control mb-2">
                                <option value="1">Resource</option>
                                <option value="2">Lesson</option>
                                <option value="3">Standby</option>
                            </select>
                            <span class="text-danger edit-error-text" id="editerror_booking_type"></span>
                        </div>
                    </div>

                    <!-- Training Event Section -->
                    <div id="edit_trainingevent_div" style="display:none">
                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Courses</label>
                                <select name="course" id="edit_course_booking" class="form-control mb-2"></select>
                                <span class="text-danger edit-error-text" id="editerror_course"></span>
                            </div>

                            <div class="col-md-6">
                                <label>Lesson</label>
                                <select name="lesson" id="edit_lesson" class="form-control mb-2"></select>
                                <span class="text-danger edit-error-text" id="editerror_lesson"></span>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Rank</label>
                                <select name="rank" id="edit_rank" class="form-control mb-2">
                                    <option value="1">Captain</option>
                                    <option value="2">First Officer</option>
                                    <option value="3">Second Officer</option>
                                </select>
                                <span class="text-danger edit-error-text" id="editerror_rank"></span>
                            </div>

                            <!-- <div class="col-md-6">
                                <label>Course Start Date</label>
                                <input type="date" name="course_date" id="edit_course_date" class="form-control mb-2">
                                <span class="text-danger edit-error-text" id="editerror_course_date"></span>
                            </div> -->
                        </div>

                        <div class="row">
                            <!-- <div class="col-md-6">
                                <label>Lesson Date</label>
                                <input type="date" name="lesson_date" id="edit_lesson_date" class="form-control mb-2">
                                <span class="text-danger edit-error-text" id="editerror_lesson_date"></span>
                            </div> -->

                            <div class="col-md-6">
                                <label>Instructor Licence Number</label>
                                <input type="text" name="licence_number" id="edit_licence_number" class="form-control mb-2" readonly>
                                <span class="text-danger edit-error-text" id="editerror_licence_number"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Departure Airfield</label>
                                <input type="text" name="departure_airfield" id="edit_departure_airfield" class="form-control">
                                <span class="text-danger edit-error-text" id="editerror_departure_airfield"></span>

                            </div>

                            <div class="col-md-6">
                                <label>Destination Airfield</label>
                                <input type="text" name="destination_airfield" id="edit_destination_airfield" class="form-control">
                                <span class="text-danger edit-error-text" id="editerror_destination_airfield"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Operation</label>
                                <select name="operation" id="edit_operation" class="form-control mb-2">
                                    <option value="">Select Operation</option>
                                    <option value="1">PF in LHS</option>
                                    <option value="2">PM in LHS</option>
                                    <option value="3">PF in RHS</option>
                                    <option value="4">PM in RHS</option>
                                </select>
                                <span class="text-danger edit-error-text" id="editerror_operation"></span>
                            </div>

                            <div class="col-md-6">
                                <label>Role</label>
                                <select name="role" id="edit_role" class="form-control mb-2">
                                    <option value="">Select Role</option>
                                    <option value="1">PF-Pilot Flying</option>
                                    <option value="2">PM-Pilot Monitoring</option>
                                </select>
                                <span class="text-danger edit-error-text" id="editerror_role"></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Student Licence Number</label>
                                    <input type="text" name="student_licence" id="edit_studentLicence_number" class="form-control mb-2" autocomplete="off" readonly>
                                    <span class="text-danger edit-error-text" id="editerror_student_licence"></span>
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Total Time (hh:mm)*</label>
                                    <input type="text" name="total_time" id="edit_total_time" class="form-control mb-2" autocomplete="off" readonly>
                                    <span class="text-danger edit-error-text" id="editerror_total_time"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resource & Instructor -->
                    <div class="row">
                        <div class="col-md-6">
                            <label>Resource</label>
                            <select name="resource" id="edit_resource" class="form-control mb-2"></select>
                            <span class="text-danger edit-error-text" id="editerror_resource"></span>
                        </div>

                        <div class="col-md-6">
                            <div id="edit_instructor_wrapper" style="display:none">
                                <label>Instructor</label>
                                <select name="instructor" id="edit_instructor" class="form-control mb-2"></select>
                                <span class="text-danger edit-error-text" id="editerror_instructor"></span>

                            </div>
                        </div>
                    </div>

                    <!-- Time Section -->
                    <div class="row" id="edit_time_div" style="display:none">
                        <div class="col-md-6">
                            <label>Start Time</label>
                            <input type="time" name="start_time" id="edit_start_time" class="form-control">
                            <span class="text-danger edit-error-text" id="editerror_start_time"></span>
                        </div>

                        <div class="col-md-6">
                            <label>End Time</label>
                            <input type="time" name="end_time" id="edit_end_time" class="form-control">
                            <span class="text-danger edit-error-text" id="editerror_end_time"></span>
                        </div>
                    </div>

                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <strong>Note:</strong> If you want to change the course and lesson, click
                        <a id="changecourse_lesson" href="{{ url('/training') }}" class="text-primary fw-semibold">
                            Change Course
                        </a>
                    </small>
                    <button type="button" id="updateBookingBtn" class="btn btn-success">
                        Update Booking
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<!-- //-------------------------------End Edit Booking Model----------------------------------------------------------->


<!-----------------------------Create Booking------------------------------------------------------------------------>
<div class="modal fade" id="newBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="booking_form">
                <div class="modal-body">
                    <div style="text-align: right;">
                        <label class="form-check-label">
                            <input type="checkbox" name="add_instructor_training" value="1" class="form-check-input" id="add_instructor_training">
                            Instructor Training
                        </label>
                    </div>
                    <div class="row">
                        @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                        <div class="col-md-6 form-group">

                            <label>Select Org Unit</label>
                            <select id="organizationUnits" name="organizationUnits" class="form-control mb-2">
                                <option value="">Select Org Unit</option>
                                @foreach ($organizationUnits as $val)
                                <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                @endforeach
                            </select>

                            <span class="text-danger error-text" id="error_organizationUnits"></span>
                        </div>
                        @endif

                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                @if(auth()->user()->is_owner == 1 || auth()->user()->is_admin == 1)
                                <label id="student_label_name">Select Student</label>
                                <select id="add_student" name="student" class="form-control mb-2">
                                    <option value="">Select Student</option>
                                    @foreach ($students as $val)
                                    <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                                    @endforeach
                                </select>
                                @endif
                                <span class="text-danger error-text" id="error_student"></span>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->is_admin == 1 && !empty(auth()->user()->ou_id))
                    <input type="hidden" name="organizationUnits" id="organizationUnits" value="{{ auth()->user()->ou_id }}">
                    @endif

                    @if(auth()->user()->is_admin == 0 && auth()->user()->is_owner == 0 && !empty(auth()->user()->ou_id))
                    <input type="hidden" name="organizationUnits" id="organizationUnits" value="{{ auth()->user()->ou_id }}">
                    @endif

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <label>Start Date & Time</label>
                                <input type="date" name="start_date" id="booking_start" class="form-control mb-2" autocomplete="off">
                                <span class="text-danger error-text" id="error_start_date"></span>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <label>End Date & Time</label>
                                <input type="date" name="end_date" id="booking_end" class="form-control mb-2" autocomplete="off">
                                <span class="text-danger error-text" id="error_end_date"></span>
                            </div>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <label>Resource Type</label>
                                <select name="resource_type" id="resource_type" class="form-control mb-2">
                                    <option value="1">Aircraft</option>
                                    <option value="2">Simulator</option>
                                    <option value="3">Classroom</option>
                                </select>
                            </div>

                        </div>
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <label>Booking Type</label>
                                <select id="booking_type" name="booking_type" class="form-control mb-2">
                                    <option value="1">Resource</option>
                                    <option value="2">Lesson</option>
                                    <option value="3">Standby</option>
                                </select>
                            </div>

                        </div>
                    </div>


                    <div id="create_trainingevent_div" style="display:none">
                        <hr>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Courses</label>
                                    <select name="course" id="course" class="form-control mb-2">
                                        <option value="">Select Courses</option>
                                    </select>
                                    <span class="text-danger error-text" id="error_course"></span>

                                </div>

                            </div>
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Lesson</label>
                                    <select name="lesson" id="lesson" class="form-control mb-2">
                                        <option value="">Select Lesson</option>
                                    </select>
                                    <span class="text-danger error-text" id="error_lesson"></span>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Rank</label>
                                    <select name="rank" id="add_rank" class="form-control mb-2">
                                        <option value="1">Captain</option>
                                        <option value="2">First Officer</option>
                                        <option value="3">Second Officer</option>
                                    </select>
                                    <span class="text-danger error-text" id="error_rank"></span>
                                </div>

                            </div>

                            <!-- <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Course Start Date</label>
                                    <input type="date" name="course_date" id="course_date" class="form-control mb-2" autocomplete="off">
                                    <span class="text-danger error-text" id="error_course_date"></span>
                                </div>
                            </div> -->
                        </div>

                        <div class="row">
                            <!-- <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Lesson Date</label>
                                    <input type="date" name="lesson_date" id="lesson_date" class="form-control mb-2" autocomplete="off">
                                    <span class="text-danger error-text" id="error_lesson_date"></span>
                                </div>
                            </div> -->
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Instructor Licence Number</label>
                                    <input type="text" name="licence_number" id="licence_number" class="form-control mb-2" autocomplete="off" readonly>
                                    <span class="text-danger error-text" id="error_licence_number"></span>
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Departure Airfield</label>
                                    <input type="text" name="departure_airfield" class="form-control">
                                    <span class="text-danger error-text" id="error_departure_airfield"></span>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Destination Airfield</label>
                                    <input type="text" name="destination_airfield" class="form-control">
                                    <span class="text-danger error-text" id="error_destination_airfield"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Operation</label>
                                    <select name="operation" id="operation" class="form-control mb-2">
                                        <option value="">Select Operation</option>
                                        <option value="1">PF in LHS</option>
                                        <option value="2">PM in LHS</option>
                                        <option value="3">PF in RHS</option>
                                        <option value="4">PM in RHS</option>

                                    </select>

                                    <span class="text-danger error-text" id="error_operation"></span>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" id="role" class="form-control mb-2">
                                        <option value="">Select Role</option>
                                        <option value="1">PF-Pilot Flying</option>
                                        <option value="2">PM-Pilot Monitoring</option>

                                    </select>
                                    <span class="text-danger error-text" id="error_role"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Student Licence Number</label>
                                    <input type="text" name="student_licence" id="studentLicence_number" class="form-control mb-2" autocomplete="off" readonly>
                                    <span class="text-danger error-text" id="error_student_licence"></span>
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <div class="form-group">
                                    <label>Total Time (hh:mm)*</label>
                                    <input type="text" name="total_time" id="total_time" class="form-control mb-2" autocomplete="off" readonly>
                                    <span class="text-danger error-text" id="error_total_time"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <div id="create_resource_wrapper">
                                    <label>Resource</label>
                                    <select id="resource" name="resource" class="form-control mb-2 add_resource">
                                        <option value="">Select Resource</option>
                                    </select>
                                </div>
                                <span class="text-danger error-text" id="error_resource"></span>
                            </div>

                        </div>
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <div id="create_instructor_wrapper" style="display:none">
                                    <label>Instructor</label>
                                    <select name="instructor" id="booking_instructor" class="form-control mb-2">
                                        <option value="">Select Instructor</option>
                                    </select>
                                </div>
                                <span class="text-danger error-text" id="error_instructor"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="time_div" style="display:none">
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <label>Start Time</label>
                                <input type="time" name="start_time" class="form-control lesson-start-time">
                                <span class="text-danger error-text" id="error_lesson_date"></span>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <div class="form-group">
                                <label>End Time</label>
                                <input type="time" name="end_time" class="form-control lesson-end-time">
                                <span class="text-danger error-text" id="error_licence_number"></span>
                            </div>
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






@endsection

@section('js_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- FullCalendar Scheduler v5 -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.11.3/main.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.11.3/main.min.js"></script>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('by_resource').checked = true;
        document.getElementById('by_instructor').checked = false;
        document.getElementById('by_student').checked = false;

        let SITEURL = "{{ url('/') }}";
        let calendar = null;
        let currentMode = 'resource';
        let editFormLoading = false;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* ----------------------------------------------------
        INIT / REINIT CALENDAR
        ---------------------------------------------------- */
        let headerExtraInfo = '';

        function initCalendar() {

            if (calendar) {
                calendar.destroy();
            }

            /* ✅ Dynamic resource header title */
            let headerTitle = 'Resources';
            if (currentMode === 'instructor') headerTitle = 'Instructor';
            if (currentMode === 'student') headerTitle = 'Student';

            calendar = new FullCalendar.Calendar(
                document.getElementById('calendar'), {
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
                            slotLabelFormat: [{
                                    day: 'numeric'
                                },
                                {
                                    weekday: 'short'
                                }
                            ]
                        },
                        resourceTimelineWeek: {
                            slotDuration: {
                                days: 1
                            },
                            slotLabelContent: function(arg) {
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
                            slotDuration: {
                                hours: 1
                            },
                            slotLabelFormat: {
                                hour: 'numeric',
                                minute: '2-digit',
                                hour12: false
                            }
                        }
                    },

                    /* ---------------- RESOURCES ---------------- */
                    resources: function(fetchInfo, successCallback, failureCallback) {
                        fetch(SITEURL + '/calendar/resources?mode=' + currentMode)
                            .then(res => res.json())
                            .then(data => successCallback(data))
                            .catch(err => failureCallback(err));
                    },

                    /* ---------------- EVENTS ---------------- */
                    events: function(info, successCallback, failureCallback) {
                        $.ajax({
                            url: SITEURL + '/calendar/events',
                            data: {
                                mode: currentMode
                            },
                            success: function(res) {
                                successCallback(res);

                                if (res.length > 0) {
                                    let headerExtraInfo = res[0].utc_offset;
                                    updateCalendarTitle(headerExtraInfo);
                                }
                            },
                            error: function(err) {
                                failureCallback(err);
                            }
                        });
                    },

                    /* ---------------- EVENT UI ---------------- */
                    eventContent: function(arg) {

                        let e = arg.event.extendedProps;
                        let viewType = arg.view.type;

                        let trainingEventHtml = '';

                        if (e.course) {
                            trainingEventHtml = `
                                <div style="
                                    font-size:12px; 
                                    opacity:.9;
                                    padding:4px 6px;
                                    background:#E5E7EB; 
                                    color:black;
                                    border-top:1px solid rgba(0,0,0,0.1);
                                ">
                                    ${e.course}
                                </div>
                            `;
                        }

                        let typeBadge = '';
                        if (e.booking_type === 'Solo') {
                            typeBadge = `<span style="font-size:11px;opacity:.8">(Solo)</span>`;
                        } else if (e.booking_type === 'Standby') {
                            typeBadge = `<span style="font-size:11px;opacity:.8">(Standby)</span>`;
                        }

                        /* ---------------- DAY VIEW → compact ---------------- */
                        if (viewType === 'resourceTimelineDay') {
                            return {
                                html: `
                                        <div style="border-radius:4px;overflow:hidden;">
                                            <div style="
                                                font-size:16px;
                                                font-weight:500;
                                                white-space:nowrap;
                                                text-align:center;
                                                padding:6px;
                                            ">
                                                ${arg.event.title} ${typeBadge}
                                            </div>

                                       

                                        </div>
                                           <div style="border-radius:4px;overflow:hidden;">
                                             ${trainingEventHtml}
                                         </div>
                                      
                                    `
                            };
                        }

                        /* ---------------- WEEK / MONTH → full content ---------------- */
                        return {
                            html: `
                                <div style="border-radius:4px;overflow:hidden;">
                                    
                                    <div style="
                                        font-weight:600;
                                        padding:6px;
                                    ">
                                        ${arg.event.title} ${typeBadge}
                                    </div>

                                    ${trainingEventHtml}

                                </div>
                            `
                        };
                    },


                    /* ---------------- EVENT CLICK ---------------- */
                    eventClick: function(info) {
                        let e = info.event.extendedProps;
                        selectedEvent = e;
                        let mailText = (e.send_mail == 1) ? 'Enabled' : 'Disabled';
                        $('#mail_send').text(mailText);
                        $('#resource_registration').text(e.registration);
                        $('#booking_student').text(e.student || '').attr('href', SITEURL + '/users/show/' + e.encode_std_id);
                        $('#booking_resource').text(e.resource || '').attr('href', SITEURL + '/resource/show/' + e.resource_id);


                        if (e.course != '') {
                            $('#booking_course_li').show();
                            $('#booking_lesson_li').show();
                            $('#booking_course').text(e.course || '').attr('href', SITEURL + '/course/show/' + e.course_id);
                            $('#booking_lesson').text(e.lesson_title || '').attr('href', `${SITEURL}/lesson-grade?lesson_id=${e.trainingEventLesson_id}&event_id=${e.event_id}`);

                        } else {
                            $('#booking_course_li').hide();
                            $('#booking_lesson_li').hide();
                        }



                        let statusText = e.status ? e.status.charAt(0).toUpperCase() + e.status.slice(1) : 'Scheduled';
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
                            $("#editBookingBtn, #deleteBookingBtn").removeClass("d-none");
                            $("#actionButtons").removeClass("d-none");
                        }

                        if (e.instructor != null) {
                            $('#bookingInstructor_li').show();
                            $('#bookingInstructor').text(e.instructor).attr('href', SITEURL + '/users/show/' + e.encode_instructor_id);
                        } else {
                            $('#bookingInstructor_li').hide();
                            $('#bookingInstructor').text('');
                        }

                        let date = moment(info.event.start);

                        let utcOffset = info.event.extendedProps.utc_offset || '';

                        $('#booking_day').text(date.format('ddd, MMM DD YYYY'));
                        $('#utc_offset').text(' (' + utcOffset + ')');



                        $('#booking_time').text(
                            moment(info.event.start).format('HH:mm') +
                            ' - ' +
                            moment(info.event.end).format('HH:mm')
                        );

                        $('#viewBookingModal').modal('show');
                    },

                    /* ---------------- SELECT SLOT ---------------- */
                    select: function(info) {
                        resetBookingForm();
                        let start = moment(info.start);
                        let end = moment(info.end).subtract(1, 'hour'); // ✅ FIX

                        startPicker.setDate(
                            start.format('YYYY-MM-DD HH:mm'),
                            true
                        );

                        endPicker.setDate(
                            end.format('YYYY-MM-DD HH:mm'),
                            true
                        );
                        let $ou = $("#organizationUnits");
                        if ($ou.length && $ou.val()) {
                            $ou.trigger("change");
                        }
                        if (info.resource) {
                            let resourceId = info.resource.id;

                            // wait until resource dropdown is loaded
                            setTimeout(function() {
                                $("#resource").val(resourceId).trigger("change");
                            }, 300);
                        }
                        $('#newBookingModal').modal('show');
                    },
                    eventDidMount: function(info) {

                        let e = info.event.extendedProps;
                        let utc = e.utc_offset ? ` (${e.utc_offset})` : '';
                        let start = moment(info.event.start).format('DD MMM YYYY HH:mm');
                        let end = moment(info.event.end).format('DD MMM YYYY HH:mm');

                        let tooltipContent = `<div class="calendar-tooltip">`;

                        if (e.resource) {
                            tooltipContent += `
                                <div class="tooltip-row">
                                    <i class="fa fa-plane text-primary me-2"></i>
                                    <b>Resource:</b>
                                    <a href="${SITEURL}/resource/show/${e.resource_id}" target="_blank">${e.resource}</a>
                                </div>`;
                        }

                        if (e.student) {
                            tooltipContent += `
                            <div class="tooltip-row">
                                <i class="fa fa-user text-primary me-2"></i>
                                <b>Student:</b>
                                <a href="${SITEURL}/users/show/${e.encode_std_id}" target="_blank">${e.student}</a>
                            </div>`;
                        }

                        if (e.instructor) {
                            tooltipContent += `
                        <div class="tooltip-row">
                            <i class="fa fa-user-tie text-primary me-2"></i>
                            <b>Instructor:</b>
                            <a href="${SITEURL}/users/show/${e.encode_instructor_id}" target="_blank">${e.instructor}</a>
                        </div>`;
                        }

                        if (e.course) {
                            tooltipContent += `
                                <div class="tooltip-row">
                                    <i class="fa fa-book text-primary me-2"></i>
                                    <b>Course:</b>
                                    <a href="${SITEURL}/course/show/${e.course_id}" target="_blank">${e.course}</a>
                                </div>`;
                        }

                        if (e.lesson_title) {
                            tooltipContent += `
                            <div class="tooltip-row">
                                <i class="fa fa-list text-primary me-2"></i>
                                <b>Lesson:</b>
                                <a href="${SITEURL}/lesson-grade?lesson_id=${e.trainingEventLesson_id}&event_id=${e.event_id}" target="_blank">
                                    ${e.lesson_title}
                                </a>
                            </div>`;
                        }

                        if (start) {
                            tooltipContent += `
                            <div class="tooltip-row">
                                <i class="fa fa-list text-primary me-2"></i>
                                <b>Start Date:</b>
                                <a> ${start} ${utc} </a>
                            </div>`;
                        }
                        if (end) {
                            tooltipContent += `
                                    <div class="tooltip-row">
                                        <i class="fa fa-list text-primary me-2"></i>
                                        <b>End Date:</b>
                                        <a> ${end} ${utc}</a>
                                    </div>`;
                        }

                        if (e.status) {
                            tooltipContent += `
                                    <hr>
                                    <div class="tooltip-row">
                                        <i class="fa fa-info-circle text-primary me-2"></i>
                                        <b>Status:</b>  ${e.status ? e.status.charAt(0).toUpperCase() + e.status.slice(1) : ''}
                                    </div>`;
                        }

                        if (e.send_mail !== null && e.send_mail !== undefined) {
                            tooltipContent += `
                                <div class="tooltip-row">
                                    <i class="fa fa-envelope text-primary me-2"></i>
                                    <b>Email:</b> ${e.send_mail == 1 ? 'Enabled' : 'Disabled'}
                                </div>`;
                        }

                        tooltipContent += `</div>`;

                        let tooltip = new bootstrap.Tooltip(info.el, {
                            title: tooltipContent,
                            html: true,
                            placement: 'top',
                            container: 'body',
                            trigger: 'manual'
                        });

                        let hideTimeout;

                        info.el.addEventListener("mouseenter", function() {

                            clearTimeout(hideTimeout);
                            tooltip.show();

                            setTimeout(() => {

                                let tooltipEl = document.querySelector('.tooltip');

                                if (tooltipEl) {

                                    tooltipEl.addEventListener('mouseenter', function() {
                                        clearTimeout(hideTimeout);
                                    });

                                    tooltipEl.addEventListener('mouseleave', function() {
                                        tooltip.hide();
                                    });

                                }

                            }, 100);

                        });

                        info.el.addEventListener("mouseleave", function() {

                            hideTimeout = setTimeout(function() {
                                tooltip.hide();
                            }, 400);

                        });

                    },
                    datesSet: function() {
                        updateCalendarTitle(headerExtraInfo);
                    },
                });

            function updateCalendarTitle(headerExtraInfo) {
                let title = calendar.view.title;

                let headerHtml = title;

                if (headerExtraInfo) {
                    headerHtml += '<span class="offset_flag">' + "(" + headerExtraInfo + ")" + '</span>';
                }

                document.querySelector('.fc-toolbar-title').innerHTML = headerHtml;
            }


            calendar.render();
        }

        /* ----------------------------------------------------
        CHECKBOX HANDLING (ONLY ONE ACTIVE)
        ---------------------------------------------------- */
        function activateOnly(id) {
            $('#by_resource, #by_instructor, #by_student').prop('checked', false);
            $('#' + id).prop('checked', true);
        }

        $('#by_resource').on('change', function() {
            activateOnly('by_resource');
            currentMode = 'resource';
            initCalendar();
        });

        $('#by_instructor').on('change', function() {
            activateOnly('by_instructor');
            currentMode = 'instructor';
            initCalendar();
        });

        $('#by_student').on('change', function() {
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
            $('#create_trainingevent_div').hide();
            $('.add_resource').val('').trigger('change');
            $('#time_div').hide();
            $('#organizationUnits').val('').trigger('change');
            $('#add_student').val('').trigger('change');
            $('#course').val('').trigger('change');
            $('#operation').val('').trigger('change');


        }

        $('#create_booking').on('click', function() {
            //  resetBookingForm();
            $('#newBookingModal')
            $('#newBookingModal').modal('show');
        });

        $('#saveBookingBtn').on('click', function(e) {
            e.preventDefault();
            let formData = new FormData($('#booking_form')[0]);
            $.ajax({
                url: SITEURL + "/booking/store",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#newBookingModal').modal('hide');
                    calendar.refetchEvents();
                    alert(res.message);
                    location.reload()

                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $('.error-text').text('');
                        $.each(xhr.responseJSON.errors, function(k, v) {
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
            //  let ou_id = $(this).val();

            var $groupSelect = $("#group");
            var $resourceSelect = $("#resource");
            var $student = $("#add_student");
            let $instructor = $("#booking_instructor");
          
            let $instructor_checkbox = $("#add_instructor_training");
            let instructor_checkbox = 0; // ✅ Declare variable

            if ($instructor_checkbox.is(":checked")) {
                instructor_checkbox = 1;
            } else {
                instructor_checkbox = 0;
            }
            //  let $courses = $("#course");

            $groupSelect.empty().append("<option value=''>Select Group</option>").trigger("change");
            $resourceSelect.empty().append("<option value=''>Select Resource</option>");
            // $student.empty().append("<option value=''>Select Student</option>").trigger("change");
            $instructor.empty().append("<option value=''>Select Instructor</option>");
            //  $courses.empty().append("<option value=''>Select Courses</option>");

            var ou_id = $(this).val() ? $(this).val() : '{{ auth()->user()->ou_id }}';

            $.ajax({
                url: "/group/students/",
                type: "GET",
                data: {
                    'ou_id': ou_id,
                    'instructor_checkbox' :instructor_checkbox
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
                        // $student.trigger("change");
                    }


                    if (response.instructors) {
                        response.instructors.forEach(i => {
                            $instructor.append(
                                `<option value="${i.id}">${i.fname} ${i.lname}</option>`
                            );
                        });
                    }
                    // Add resources 
                    if (response.org_resource && Array.isArray(response.org_resource)) {
                        var options = "<option value=''>Select Resource </option>";
                        response.org_resource.forEach(function(value) {
                            options += "<option data-resource='" + value.name + "' value='" + value.id + "'>" + value.name + "</option>";
                        });
                        $resourceSelect.html(options);
                        // $resourceSelect.trigger("change");
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
            // alert(type);
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

            if (bookingType == 1) {
                $(instructorSelector)
                    .prop('required', false)
                    .val('')
                    .closest('.form-group').removeClass('required');
            } else {
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


        $("#editBookingBtn").on("click", function() {
            $("#viewBookingModal").modal("hide");
            $('#edit_booking_id').val(selectedEvent.id);
            var booking_id = $('#edit_booking_id').val();
            $('.edit-error-text').text('');
            editFormLoading = true;

            $.ajax({
                url: SITEURL + "/calendar/edit",
                type: "POST",
                data: {
                    id: booking_id
                },
                success: function(data) {
                    var response = data.response[0];
                    $("#edit_organizationUnits").val(response.ou_id).trigger('change').addClass("no-change");
                    $('#edit_departure_airfield').val(response.training_event_lesson?.departure_airfield ?? '');
                    $('#edit_destination_airfield').val(response.training_event_lesson?.destination_airfield ?? '');
                    $('#edit_lesson_date').val(response.training_event_lesson?.lesson_date ?? '');
                    $('#edit_rank').val(response.training_event?.rank ?? '');
                    $('#edit_course_date').val(response.training_event?.event_date ?? '');
                    $('#edit_operation').val(response.training_event_lesson?.operation1 ?? '');
                    $('#edit_role').val(response.training_event_lesson?.role1 ?? '');
                    $('#edit_studentLicence_number').val(response.training_event?.std_license_number ?? '');
                    $('#edit_total_time').val(response.training_event?.total_time ?? '');
                    $('#edit_start_time').val(response.training_event_lesson?.start_time ?? '');
                    $('#edit_end_time').val(response.training_event_lesson?.end_time ?? '');

                    $('#event_id').val(response.event_id);
                    if (response.training_event.entry_source == "instructor") { 
                        $("#instructor_training").prop("checked", true);
                    } else { 
                        $("#instructor_training").prop("checked", false);
                    }


                    // Basic fields
                    $("#edit_booking_id").val(response.id);
                    $("#edit_booking_type").val(response.booking_type);
                    // $('#edit_rank').val(response.course_id);
                    if (response.booking_type == 1) {
                        $('#edit_trainingevent_div').hide();
                    } else {
                        $('#edit_trainingevent_div').show();
                    }


                    setTimeout(function() {
                        $("#edit_course_booking").val(response.course_id).addClass("no-change");;
                        //  $("#edit_course_booking").trigger("change");

                    }, 700);

                    setTimeout(function() {
                        $("#edit_resource").val(String(response.resource)).trigger("change");
                        $("#edit_student").val(response.std_id).trigger('change').addClass("no-change");
                        $("#edit_lesson").val(response.lesson_id).addClass("no-change");
                        edit_instructor(response.course_id);


                       
                        setTimeout(function() {
                          $("#edit_course_booking").val(response.course_id);
                        //  $("#edit_course_booking").trigger("change");
                         
                        }, 700);

                        setTimeout(function() {
                            $("#edit_resource").val(String(response.resource)).trigger("change");
                            $("#edit_student").val(response.std_id).trigger('change').addClass("no-change");


                            console.log(response.lesson_id);    
                            $("#edit_lesson").val(response.lesson_id).addClass("no-change");
                             edit_instructor(response.course_id);

                           
                           setTimeout(function () {
                                $("#edit_instructor").val(response.instructor_id);
                                window.selectedEditCourseId = response.course_id;
                                window.selectedEditLessonId = response.lesson_id;

                                editFormLoading = false;

                                $("#edit_instructor").trigger("change");  // 🔵 Now course exists
                            }, 500);

                             window.selectedEditCourseId = response.course_id;
                             window.selectedEditLessonId = response.lesson_id;
                            window.resource = response.resource;
                        }, 600);

                        window.selectedEditCourseId = response.course_id;
                        window.selectedEditLessonId = response.lesson_id;
                        window.resource = response.resource;
                    }, 600);


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
                    setTimeout(function() {
                        editFormLoading = false;
                        //  alert("love");
                    }, 1000);
                },
                error: function(xhr) {
                    toastr.error("Unable to load booking details.");
                    console.error(xhr.responseText);
                }
            });
        });

        $("#edit_course_booking").on('change', function() {  console.log("asdas");
             console.log("second click");
            let course_id = $(this).val();
        
           // alert(course_id);
            let $lesson = $("#edit_lesson");
            var $resourceSelect = $("#edit_resource");
            var ou_id = $('#edit_organizationUnits').val() ?? "{{ auth()->user()->ou_id }}";
            var std_id = $('#edit_student').val();

            $lesson.empty().append("<option value=''>Select Lesson</option>");
            if (!course_id) return;

            $.ajax({
                url: "/course/lesson",
                type: "POST",
                data: {
                    course_id: course_id,
                    ou_id:ou_id,
                    std_id:std_id
                },
                dataType: "json",
                success: function(response) {

                    if (response.lessons) {
                        let usedLessons = response.usedLessonIds || [];
                        response.lessons.forEach(i => {
                            let disabled = usedLessons.includes(i.id) ? 'disabled' : '';
                            $lesson.append(
                                `<option value="${i.id}">${i.lesson_title}</option>`
                            );
                        });
                    }

                    /* ✅ SET SELECTED LESSON HERE (IMPORTANT) */
                    if (window.selectedEditLessonId) {
                        $lesson.val(window.selectedEditLessonId).trigger('change');
                    }

                    // Resources
                    if (response.resources && Array.isArray(response.resources)) {
                        var options = "<option value=''>Select Resource </option>";
                        response.resources.forEach(function(value) {
                            //  options += `<option value="${value.id}">${value.name}</option>`;
                            options += "<option data-resource='" + value.name + "' value='" + value.id + "'>" + value.name + "</option>";
                        });
                        $resourceSelect.html(options);
                    }
                    if (window.resource) {
                        $('#edit_resource').val(window.resource).trigger('change');
                    }
                }
            });
        });

        function edit_instructor(course_id) {
            $("#edit_instructor").on('change', function() {
                let instructorId = $(this).val();
                let selectedCourseId = course_id


                let licenseInput = $('#edit_licence_number');
                if (editFormLoading) {
                    return;
                }

                // 🔴 Validation — instructor required
                if (!instructorId) {
                    licenseInput.val('');
                    return;
                }


                // 🔴 Validation — course required
                if (!selectedCourseId) {
                    alert("Select the course first.");
                    $(this).val('');
                    licenseInput.val('');
                    return;
                }

                // 🔵 Disable while loading
                licenseInput.prop('readonly', true).val('Loading...');

                $.ajax({
                    url: "{{ url('/training/get_instructor_license_no') }}/" + instructorId + "/" + selectedCourseId,
                    type: 'GET',
                    dataType: 'json',

                    success: function(response) {

                        //  licenseInput.prop('readonly', false);

                        // 🔴 Response validation
                        if (!response || typeof response !== "object") {
                            licenseInput.val('');
                            alert("Invalid response received.");
                            return;
                        }

                        if (response.success === true) {
                            if (response.instructor_licence_number) {
                                licenseInput.val(response.instructor_licence_number);
                            } else {
                                licenseInput.val('');
                               // alert("Instructor licence number not found.");
                            }

                        } else {
                            licenseInput.val('');
                            alert(response.message || "Instructor not found.");
                        }
                    },

                    error: function(xhr) {
                        // licenseInput.prop('readonly', false).val('');
                        console.error(xhr);
                        alert("Server error while fetching licence number.");
                    }
                });
            });

        }  

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

        $("#updateBookingBtn").click(function(e) {
            e.preventDefault();

            let form = $('#edit_booking_form')[0];
            let formData = new FormData(form);

            // append booking id manually if needed
            formData.append('id', $('#edit_booking_id').val());

            $.ajax({
                url: SITEURL + "/booking/update",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {

                    toastr.success("Booking Updated");

                    $('#editBookingModal').modal('hide');

                    $('#calendar').fullCalendar('refetchEvents');

                    initCalendar();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        $('.edit-error-text').text('');
                        $.each(xhr.responseJSON.errors, function(k, v) {
                            $('#editerror_' + k).text(v[0]);

                        });
                    } else {
                        alert('Something went wrong');
                    }
                }
            });

        });



        // APPROVE BOOKING
        $("#approveBtn").on("click", function() {
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
                success: function(response) {
                    toastr.success("Booking Approved");
                    $('#viewBookingModal').modal('hide');
                    $('#calendar').fullCalendar('refetchEvents');
                    initCalendar();
                },
                error: function(xhr) {
                    toastr.error("Something went wrong. Please try again.");
                    console.error(xhr.responseText);
                }
            });

        });

        $("#rejectBtn").on("click", function() {
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
                success: function() {
                    toastr.error("Booking Rejected");
                    $('#viewBookingModal').modal('hide');
                    $('#calendar').fullCalendar('refetchEvents');
                    initCalendar();
                },
                error: function(xhr) {
                    toastr.error("Something went wrong. Please try again.");
                    console.error(xhr.responseText);
                }
            });
        });


        $("#deleteBookingBtn").on("click", function() {
            if (!confirm("Are you sure you want to delete this booking ?")) return;
            $.ajax({
                url: SITEURL + "/booking/delete",
                type: "POST",
                data: {
                    id: $("#delete_booking_id").val(),
                },
                success: function(response) {
                    toastr.success("Booking Deleted");
                    $("#viewBookingModal").modal("hide");
                    calendar.refetchEvents();
                },
                error: function(xhr) {
                    toastr.error("Unable to cancel booking. Please try again.");
                    console.error(xhr.responseText);
                }
            });
        });

        $("#course").on('change', function() {
            let course_id = $(this).val();
            let $lesson = $("#lesson");
            var $resourceSelect = $("#resource");
            let ou_val = $('#organizationUnits').val();
            let ou_id = ou_val ? ou_val : @json(auth()-> user()-> ou_id);
            var std_id = $('#add_student').val();

            $lesson.empty().append("<option value=''>Select Lesson</option>");
            if (!course_id) return;

            $.ajax({
                url: "/course/lesson",
                type: "POST",
                data: {
                    course_id: course_id,
                    ou_id: ou_id,
                    std_id: std_id
                },
                dataType: "json",
                success: function(response) {
                    if (response.lessons) {
                        let usedLessons = (response.usedLessonIds || []).map(Number);
                        response.lessons.forEach(i => {
                            let disabled = usedLessons.includes(Number(i.id)) ? 'disabled' : '';
                            $lesson.append(
                                `<option value="${i.id}" ${disabled}>${i.lesson_title}</option>`
                            );
                        });
                    }
                    let rankSelect = $("#add_rank");
                    if (response.enable_mp_lifus !== undefined) {
                        let enableValue = response.enable_mp_lifus;
                        // First show all options (reset)
                        rankSelect.find("option").show();
                        if (enableValue == 0 || enableValue == 1) {
                            rankSelect.find("option[value='2'], option[value='3']").hide();
                            rankSelect.val("1");
                        } else if (enableValue == 2 || enableValue == 3) {
                            rankSelect.find("option[value='2'], option[value='3']").show();
                        }
                    }

                    // Add resources 
                    if (response.resources && Array.isArray(response.resources)) {
                        var options = "<option value=''>Select Resource </option>";
                        response.resources.forEach(function(value) {
                            options += "<option data-resource='" + value.name + "' value='" + value.id + "'>" + value.name + "</option>";
                        });
                        $resourceSelect.html(options);
                        // $resourceSelect.trigger("change");
                    }
                }
            });
        });


        $("#booking_instructor").on('change', function() {

            let instructorId = $(this).val();
            let selectedCourseId = $('#course').val();
            let licenseInput = $('#licence_number');

            // 🔴 Validation — instructor required
            if (!instructorId) {
                licenseInput.val('');
                return;
            }

            // 🔴 Validation — course required
            if (!selectedCourseId) {
                alert("Please select course first.");
                $(this).val('');
                licenseInput.val('');
                return;
            }

            // 🔵 Disable while loading
            licenseInput.prop('readonly', true).val('Loading...');

            $.ajax({
                url: "{{ url('/training/get_instructor_license_no') }}/" + instructorId + "/" + selectedCourseId,
                type: 'GET',
                dataType: 'json',

                success: function(response) {

                    //  licenseInput.prop('readonly', false);

                    // 🔴 Response validation
                    if (!response || typeof response !== "object") {
                        licenseInput.val('');
                        alert("Invalid response received.");
                        return;
                    }

                    if (response.success === true) {
                        if (response.instructor_licence_number) {
                            licenseInput.val(response.instructor_licence_number);
                        } else {
                            licenseInput.val('');
                            alert("Instructor licence number not found.");
                        }

                    } else {
                        licenseInput.val('');
                        alert(response.message || "Instructor not found.");
                    }
                },

                error: function(xhr) {
                    // licenseInput.prop('readonly', false).val('');
                    console.error(xhr);
                    alert("Server error while fetching licence number.");
                }
            });
        });



        $(".add_resource").on('change', function() {
            let resource = $(this).find(':selected').data('resource');
            let booking_type = $('#booking_type').val();

            if (resource == "Classroom") {
                $('#time_div').hide();

            } else if (resource != "Classroom" && (booking_type == 2 || booking_type == 3)) {
                $('#time_div').show();
            }

        });

        $("#edit_resource").on('change', function() {
            let resource = $(this).find(':selected').data('resource');
            let booking_type = $('#edit_booking_type').val();

            if (resource == "Classroom") {
                $('#edit_time_div').hide();

            } else if (resource != "Classroom" && (booking_type == 2 || booking_type == 3)) {
                $('#edit_time_div').show();
            }

        });

        $(document).on('change', '#add_student', function() {
            var userId = $(this).val();
            // let ou_id = $('#organizationUnits').val();
            var licenceNumberField = $('#studentLicence_number');
            let $courses = $("#course");
            $courses.empty().append('<option value="">Select Course</option>');

            var ou_id = $('#organizationUnits').val() ?
                $('#organizationUnits').val() :
                '{{ auth()->user()->ou_id }}';
            if (userId) {
                $.ajax({
                    url: "{{ url('/training/get_licence_number_and_courses') }}/" + userId + '/' + ou_id,
                    type: "GET",
                    success: function(response) {

                        if (response.success) {
                            if (response.licence_number) {
                                licenceNumberField.val(response.licence_number);
                            } else {

                                licenceNumberField.val('');
                            }

                            //  Add Courses

                            if (response.courses && response.courses.length > 0) {
                                response.courses.forEach(i => {
                                    $courses.append(
                                        `<option value="${i.id}">${i.course_name}</option>`
                                    );
                                });
                            }

                        } else {
                            licenceNumberField.val('');
                            alert('Licence number not found!');
                            courseDropdown.html('<option value="">Select Course</option>'); // Clear courses
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                licenceNumberField.val('');
            }
        });

        $(document).on('change', '#edit_student', function() { console.log("append courses");
            var userId = $(this).val();

            var licenceNumberField = $('#studentLicence_number');
            let $courses = $("#edit_course_booking");

            $courses.empty().append('<option value="">Select Course</option>');

            var ou_id = $('#edit_organizationUnits').val() ?
                $('#edit_organizationUnits').val() :
                '{{ auth()->user()->ou_id }}';

            if (userId) {
                $.ajax({
                    url: "{{ url('/training/get_licence_number_and_courses') }}/" + userId + '/' + ou_id,
                    type: "GET",
                    success: function(response) {

                        if (response.success) {

                            if (response.licence_number) {
                                licenceNumberField.val(response.licence_number);
                            } else {
                                licenceNumberField.val('');
                            }

                            if (response.courses && response.courses.length > 0) {

                                response.courses.forEach(i => {
                                    $courses.append(
                                        `<option value="${i.id}">${i.course_name}</option>`
                                    );
                                });
                                if (window.selectedEditCourseId) {
                                    $courses.val(window.selectedEditCourseId).trigger('change');
                                }

                            }

                        } else {
                            licenceNumberField.val('');
                        }
                    }
                });
            } else {
                licenceNumberField.val('');
            }
        });

        $(document).on('change', '#booking_type', function() {
            var booking_type = $(this).val();
            if (booking_type == 1) {
                $('#create_trainingevent_div').hide();
                $('.add_resource').val('').trigger('change');
                $('#time_div').hide();


            } else {
                $('#create_trainingevent_div').show();

            }
        });

        $(document).on('focus', '#edit_booking_type', function() {
            previous_booking_type = $(this).val(); // store previous value
        });

        $(document).on('change', '#edit_booking_type', function() {
            var booking_type = $(this).val();
            var event_id = $('#event_id').val();
            if (event_id != '') {
                alert('Booking type cannot be changed because an event already exists.');
                $(this).val(previous_booking_type).trigger('change.select2'); // revert value
                return;
            }


            if (booking_type == 1) {
                $('#edit_trainingevent_div').hide();
                $('#edit_resource').val('').trigger('change');
                $('#time_div').hide();

            } else {
                $('#edit_trainingevent_div').show();

            }
        });

        $(document).on('change', 'input[name="start_time"], input[name="end_time"]', function() {
            calculateTotalTime($(this));
        });

        function calculateTotalTime(element) {

            let form = element.closest('form');
            let start = form.find('input[name="start_time"]').val();
            let end = form.find('input[name="end_time"]').val();

            if (start && end) {

                let startTime = moment(start, "HH:mm");
                let endTime = moment(end, "HH:mm");

                // handle next day case
                if (endTime.isBefore(startTime)) {
                    endTime.add(1, 'day');
                }

                let diffMinutes = endTime.diff(startTime, 'minutes');

                let hours = Math.floor(diffMinutes / 60);
                let minutes = diffMinutes % 60;

                let formatted =
                    String(hours).padStart(2, '0') + ":" +
                    String(minutes).padStart(2, '0');

                form.find('input[name="total_time"]').val(formatted);
            }
        }

        $('#add_instructor_training').click(function() {
                let isChecked = $(this).is(":checked");

                if (isChecked) {
                    $("#student_label_name").text("Select Instructor");
                    $("#add_student option[value='']").text("Select Instructor");
                } else {
                    $("#student_label_name").text("Select Student");
                    $("#add_student option[value='']").text("Select Student");
                }
                    
                        $("#organizationUnits").trigger("change");
                        
                    });
    });
</script>
@endsection