@extends('layout.app')

@section('title', 'Calendar')
@section('sub-title', 'Calendar')

@section('content')

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 110px;
        height: 30px;
    }

    .switch-input {
        display: none;
    }

    .switch-button {
        position: absolute;
        cursor: pointer;
        background-color: #dc3545; /* OFF - red */
        border-radius: 30px;
        inset: 0;
        transition: background-color 0.3s ease;
        overflow: hidden;
    }

    .switch-button-left,
    .switch-button-right {
        position: absolute;
        width: 70%;
        text-align: center;
        line-height: 30px;
        font-size: 11px;
        font-weight: bold;
        color: #fff;
        transition: all 0.3s ease;
    }

    /* OFF text */
    .switch-button-left {
        left: 30px;
    }

    /* ON text (hidden initially) */
    .switch-button-right {
        right: 30px;
        transform: translateX(100%);
        opacity: 0;
    }

    /* Knob */
    .switch-button::before {
        content: "";
        position: absolute;
        height: 26px;
        width: 26px;
        left: 2px;
        top: 2px;
        background-color: #fff;
        border-radius: 50%;
        transition: transform 0.3s ease;
    }

    /* ON state */
    .switch-input:checked + .switch-button {
        background-color: #28a745; /* green */
    }

    .switch-input:checked + .switch-button::before {
        transform: translateX(78px);
    }

    .switch-input:checked + .switch-button .switch-button-left {
        transform: translateX(-100%);
        opacity: 0;
    }

    .switch-input:checked + .switch-button .switch-button-right {
        transform: translateX(0);
        opacity: 1;
    }

    .email-switch {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .switch-text {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
    }


</style>

<div class="row mb-3">
    <div class="col-md-4">
        <input type="text" id="studentSearch"
            class="form-control"
            placeholder="Search by student name">
    </div>

    <div class="col-md-4">
        <input type="text" id="resourceSearch"
            class="form-control"
            placeholder="Search by resource">
    </div>
</div>


<div class="container mt-4">
    <div id="calendar"></div>
</div>


<!-- ====================================================== -->
<!-- NEW BOOKING MODAL -->
<!-- ====================================================== -->
<div class="modal fade" id="newBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Create Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <label>Select Org Unit</label>
                    <select id="organizationUnits" name="organizationUnits" class="form-control mb-2">
                        <option value="">Select Org Unit</option>
                        @foreach ($organizationUnits as $val)
                        <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                        @endforeach
                    </select>
                @endif
                @if(auth()->user()->is_admin == 1 && !empty(auth()->user()->ou_id))
                    <input type="hidden" name="organizationUnits" id="organizationUnits" value="{{ auth()->user()->ou_id }}">
                @endif

                <label>Start Date & Time</label>
                <input type="date" id="booking_start" class="form-control mb-2" readonly>

                <label>End Date & Time</label>
                <input type="date" id="booking_end" class="form-control mb-2" readonly>

                <label>Booking Type</label>
                <select id="booking_type" name="booking_type" class="form-control mb-2">
                    <option value="1">Solo</option>
                    <option value="2">Lesson</option>
                    <option value="3">Standby</option>
                </select>

                <label>Resource Type</label>
                <select name="resource_type" id="resource_type" class="form-control mb-2">
                    <option value="1">Plane</option>
                    <option value="2">Simulator</option>
                    <option value="3">Classroom</option>
                </select>

                @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                <label>Select Student</label>
                <select id="student" name="student" class="form-control mb-2">
                    <option value="">Select Student</option>
                    @foreach ($students as $val)
                    <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                    @endforeach
                </select>
                @endif

                <div id="create_resource_wrapper">
                    <label>Resource</label>
                    <select id="resource" name="resource" class="form-control mb-2">
                        <option value="">Select Resource</option>
                        @foreach ($resources as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="create_instructor_wrapper">
                    <label>Instructor</label>
                    <select id="booking_instructor" class="form-control mb-2">
                        <option value="">Select Instructor</option>
                    </select>
                </div>


                <!-- <div class="email-switch">
                    <label for="create_send_email" class="switch-text">
                        Send Email Notification
                    </label>

                    <label class="switch">
                        <input type="checkbox"
                            id="create_send_email"
                            class="switch-input"
                            name ="send_email"
                            checked>
                        <div class="switch-button">
                            <span class="switch-button-left">Do Not Send</span>
                            <span class="switch-button-right">Send Email</span>
                        </div>
                    </label>
                </div> -->

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="saveBookingBtn">Submit Booking</button>
            </div>

        </div>
    </div>
</div>

<!-- ====================================================== -->
<!-- VIEW BOOKING MODAL -->
<!-- ====================================================== -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p><strong>Student:</strong> <span id="booking_student"></span></p>
                <p><strong>Resource:</strong> <span id="booking_resource"></span></p>
                <p><strong>Start Date:</strong> <span id="start_date"></span></p>
                <p><strong>End Date:</strong> <span id="end_date"></span></p>
                <p><strong>Type:</strong> <span id="view_type"></span></p>
                <p><strong>Status:</strong> <span id="view_status"></span></p>
                <input type="hidden" id="approve_booking_id">
                <input type="hidden" id="reject_booking_id">

            </div>
            @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
            <div class="modal-footer">
                <button id="editBookingBtn" class="btn btn-primary">Edit</button>
                <button id="approveBtn" class="btn btn-success">Approve</button>
                <button id="rejectBtn" class="btn btn-danger">Reject</button>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- ====================================================== -->
<!-- END BOOKING MODAL -->
<!-- ====================================================== -->

<!-- ====================================================== -->
<!-- EDIT BOOKING MODAL -->
<!-- ====================================================== -->
<div class="modal fade" id="editBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit_booking_id">

                <label>Org Unit</label>
                <select id="edit_organizationUnits" class="form-control mb-2">
                    <option value="">Select Org Unit</option>
                    @foreach ($organizationUnits as $val)
                        <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                    @endforeach
                </select>

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



                <!-- <div class="email-switch">
                    <label for="edit_send_email" class="switch-text">
                        Send Email Notification
                    </label>

                    <label class="switch mt-2">
                        <input type="checkbox"
                            id="edit_send_email"
                            class="switch-input"
                            checked>
                        <div class="switch-button">
                            <span class="switch-button-left">Send Email</span>
                            <span class="switch-button-right">Not Send Email</span>
                        </div>
                    </label>
                </div> -->

                <!-- <label class="switch mt-2">
                    <input type="checkbox"
                        id="edit_send_email"
                        class="switch-input"
                        checked>
                    <div class="switch-button">
                        <span class="switch-button-left">Send Email</span>
                        <span class="switch-button-right">Not Send Email</span>
                    </div>
                </label> -->
            </div>

            <div class="modal-footer">
                <button id="updateBookingBtn" class="btn btn-success">Update</button>
            </div>

        </div>
    </div>
</div>

<!-- ====================================================== -->
<!-- END EDIT BOOKING MODAL -->
<!-- ====================================================== -->

@endsection


@section('js_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    $(function() {

        let selectedEvent = null;

        var SITEURL = "{{ url('/') }}";

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ---------------------------
        // Initialize datetimepickers
        // ---------------------------
        // Important: initialize after Tempus Dominus script is loaded
        let startPicker = flatpickr("#booking_start", {
            enableTime: true,
            dateFormat: "Y-m-d",
            time_24hr: true,
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
            dateFormat: "Y-m-d",
            time_24hr: true,
            minuteIncrement: 15
        });


        // ---------------------------
        // FullCalendar
        // ---------------------------
        $('#calendar').fullCalendar({
            editable: false,
            selectable: true,
            displayEventTime: false,
            //selectOverlap: false,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'basicDay,basicWeek,month'
            },
            defaultView: 'month',
            events: SITEURL + "/fullcalendar",
            events: function(start, end, timezone, callback) {
                $.ajax({
                    url: SITEURL + "/fullcalendar",
                    data: {
                        student: $('#studentSearch').val(),
                        resource: $('#resourceSearch').val()
                    },
                    success: function(data) {
                        callback(data);
                    }
                });
            },

            eventRender: function(event, element) {

                element.find('.fc-title').html(`
                    <div style="font-weight:600;">Resource: ${event.resource}</div>
                    <div style="font-size:14px;">Booked for: ${event.title}</div>
                `);

                if (event.status === 'pending') element.css('background', '#f1c40f');
                if (event.status === 'approved') element.css('background', '#2ecc71');
                if (event.status === 'rejected') element.css('background', '#9b4747');
                if (event.status === 'standby') element.css('background', '#e67e22');
            },
            select: function(start) {
                resetBookingForm();
                $('#newBookingModal').modal('show'); 

                let startStr = moment(start).format("YYYY-MM-DD HH:mm");

                // Let flatpickr onChange calculate end = 23:59
                startPicker.setDate(startStr, true);
            },


            eventClick: function(event) {
                selectedEvent = event;

                var user_id = {{ auth() -> user() -> id }};
                if (event.can_access == true) { 
                    $('#viewBookingModal').modal('show');
                }
                $('#booking_student').text(event.student);
                $('#booking_resource').text(event.resource);
                $('#start_date').text(moment(event.start).format("DD-MM-YYYY"));
                $('#end_date').text(moment(event.end).format("DD-MM-YYYY"));

                let typeText = '';
                if (event.booking_type == 1) {
                    typeText = 'Solo';
                } else if (event.booking_type == 2) {
                    typeText = 'Lesson';
                } else if (event.booking_type == 3) {
                    typeText = 'Standby';
                }

                $('#view_type').text(typeText);
                let status = event.status ?
                    event.status.charAt(0).toUpperCase() + event.status.slice(1) :
                    '';
                $('#view_status')
                    .removeClass('text-warning text-success text-danger')
                    .text(status);

                //$('#view_status').text(status);
                if (event.status === 'pending') {
                    $('#view_status').addClass('text-warning'); // yellow
                } else if (event.status === 'approved') {
                    $('#view_status').addClass('text-success'); // green
                } else if (event.status === 'rejected') {
                    $('#view_status').addClass('text-danger'); // red
                }

                $('#approve_booking_id').val(event.id);
                $('#reject_booking_id').val(event.id);

                if (event.status == "pending") {
                    $('#approveBtn').show();
                    $('#rejectBtn').show();
                } else {
                    $('#approveBtn').hide();
                    $('#rejectBtn').hide();
                }
            }

        });

        function resetBookingForm()
        {
            let $ou = $('#organizationUnits');

            if ($ou.is('select')) {
                $ou.val('');
            }

            $('#resource').val('');
            $('#student').val('');
            $('#booking_type').val('1');

            startPicker.clear();
            endPicker.clear();
        }


        function handleBookingType(
            bookingTypeSelector,
            resourceWrapper,
            resourceSelector,
            instructorWrapper,
            instructorSelector
        ) {
            let type = $(bookingTypeSelector).val();

            if (type == 1) { // SOLO
                $(instructorWrapper).hide();
                $(instructorSelector).val('').prop('required', false);

                $(resourceWrapper).show();
                $(resourceSelector).prop('required', true);

            } else if (type == 2) { // LESSON
                $(instructorWrapper).show();
                $(instructorSelector).prop('required', true);

                $(resourceWrapper).show();
                $(resourceSelector).prop('required', true);

            } else if (type == 3) { // STANDBY
                $(instructorWrapper).show();
                $(instructorSelector).prop('required', false);

                $(resourceWrapper).show();
                $(resourceSelector).prop('required', false);
            }
        }



        $('#studentSearch, #resourceSearch').on('keyup', function() {
            $('#calendar').fullCalendar('refetchEvents');
        });


        // ---------------------------
        // Save Booking (AJAX)
        // ---------------------------

        $("#saveBookingBtn").click(function(e) {
            e.preventDefault();

            // read values (from datetimepicker inputs)
            var resource_id = $("#resource").val();
            var organizationUnits = $("#organizationUnits").val();
            var group = $("#group").val();
            var start = $('#booking_start').val();
            var end = $('#booking_end').val();
            // var send_email = $("#create_send_email").is(":checked");

            let bookingType = $("#booking_type").val();
            let instructor  = $("#booking_instructor").val();

            if (!resource_id) {
                toastr.error('Please select a resource');
                return;
            }
            if (!start || !end) {
                toastr.error('Please select start and end date/time');
                return;
            }

            if ((bookingType == 2) && !instructor) {
                toastr.error('Instructor is required for Lesson booking');
                return;
            }

            $.post(SITEURL + "/booking/store", {
                resource_id: resource_id,
                start: start,
                end: end,
                organizationUnits: organizationUnits,
                group: group,
                // send_email: send_email,
                booking_type: $("#booking_type").val(),
                resource_type: $("#resource_type").val(),
                instructor: $("#booking_instructor").val(),
                student: $("#student").val(),
            }, function(response) {
                toastr.success("Booking Request Submitted");
                $('#newBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            }).fail(function(xhr) {
                toastr.error('Failed to save booking');
                console.error(xhr.responseText);
            });
        });

        // APPROVE BOOKING
        $("#approveBtn").click(function() {
            $.post(SITEURL + "/booking/approve", {
                id: $("#approve_booking_id").val()
            }, function() {
                toastr.success("Booking Approved");
                $('#viewBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            });
        });

        // REJECT BOOKING
        $("#rejectBtn").click(function() {
            $.post(SITEURL + "/booking/reject", {
                id: $("#reject_booking_id").val()
            }, function() {
                toastr.error("Booking Rejected");
                $('#viewBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            });
        });

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

        let editStartPicker = flatpickr("#edit_booking_start", {
            enableTime: true,
            dateFormat: "Y-m-d",
            time_24hr: true
        });

        let editEndPicker = flatpickr("#edit_booking_end", {
            enableTime: true,
            dateFormat: "Y-m-d",
            time_24hr: true
        });

        $("#editBookingBtn").click(function () {

            $('#viewBookingModal').modal('hide');

            $('#edit_booking_id').val(selectedEvent.id);

            $('#edit_organizationUnits')
                .val(selectedEvent.ou_id)
                .trigger('change');

            setTimeout(function () {
                $('#edit_resource').val(selectedEvent.resource_id);
                $('#edit_student').val(selectedEvent.std_id);
                $('#edit_instructor').val(selectedEvent.instructor_id);
            }, 300);

            $('#edit_booking_type').val(selectedEvent.booking_type);

            // $('#edit_send_email').prop('checked', selectedEvent.send_email == 1 ? true : false);

            editStartPicker.setDate(
                moment(selectedEvent.start).format("YYYY-MM-DD HH:mm"),
                true
            );

            editEndPicker.setDate(
                moment(selectedEvent.end).format("YYYY-MM-DD HH:mm"),
                true
            );

            $('#editBookingModal').modal('show');
        });

        $("#edit_organizationUnits").on('change', function () {
            let ou_id = $(this).val();

            let $resource = $("#edit_resource");
            let $student  = $("#edit_student");
            let $instructor = $("#edit_instructor");

            $resource.empty().append("<option value=''>Select Resource</option>");
            $student.empty().append("<option value=''>Select Student</option>");
            $instructor.empty().append("<option value=''>Select Instructor</option>");

            if (!ou_id) return;

            $.ajax({
                url: "/group/students/",
                type: "GET",
                data: { ou_id: ou_id },
                dataType: "json",
                success: function (response) {

                    if (response.org_resource) {
                        response.org_resource.forEach(function (r) {
                            $resource.append(
                                `<option value="${r.id}">${r.name}</option>`
                            );
                        });
                    }

                    if (response.students) {
                        response.students.forEach(function (s) {
                            $student.append(
                                `<option value="${s.id}">${s.fname} ${s.lname}</option>`
                            );
                        });
                    }
                    if (response.instructors) {
                        response.instructors.forEach(function (i) {
                            $instructor.append(
                                `<option value="${i.id}">${i.fname} ${i.lname}</option>`
                            );
                        });
                    }
                }
            });
        });

        $("#updateBookingBtn").click(function () {

            let editbookingType = $("#edit_booking_type").val();
            let editinstructor  = $("#edit_instructor").val();

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
            }, function () {
                toastr.success("Booking Updated");
                $('#editBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            });
        });

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

        // On change
        $('#booking_type').on('change', function () {
            toggleInstructorRequirement('#booking_type', '#booking_instructor');
            handleBookingType(
                '#booking_type',
                '#create_resource_wrapper',
                '#resource',
                '#create_instructor_wrapper',
                '#booking_instructor'
            );
        });

        handleBookingType(
            '#booking_type',
            '#create_resource_wrapper',
            '#resource',
            '#create_instructor_wrapper',
            '#booking_instructor'
        );

        $('#edit_booking_type').on('change', function () {
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



    });
</script>

@endsection