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
        background-color: #dc3545;
        /* OFF - red */
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
    .switch-input:checked+.switch-button {
        background-color: #28a745;
        /* green */
    }

    .switch-input:checked+.switch-button::before {
        transform: translateX(78px);
    }

    .switch-input:checked+.switch-button .switch-button-left {
        transform: translateX(-100%);
        opacity: 0;
    }

    .switch-input:checked+.switch-button .switch-button-right {
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

<div class="create_btn d-flex justify-content-between align-items-center">
    <!-- Left end -->
    <div>
        <a class="btn btn-primary me-2 booking-button" id="create_booking">
            Create Booking
        </a>
    </div>

    <!-- Right end -->
    <div class="d-flex align-items-center">
        <label class="me-2 mb-0 text-nowrap"><b>Organization Unit</b></label>
        <select class="form-select" style="width: 200px;" id="change_organization_unit">
            <option value="">Select the organization unit</option>
            @foreach ($organizationUnits as $val)
            <option value="{{ $val->id }}">{{ $val-> org_unit_name}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="container-fluid mt-3">
    <div class="row">
        <!-- LEFT SIDE : RESOURCES -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Resources</strong>
                </div>

                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @foreach ($resources as $res)
                    <?php // dump($res); 
                    ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input resource-filter"
                            type="checkbox"
                            value="{{ $res->id }}"
                            id="resource_{{ $res->id }}" checked>

                        <label class="form-check-label" for="resource_{{ $res->id }}">
                            {{ $res->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE : CALENDAR -->
        <div class="col-md-9">
            <div id="calendar"></div>
        </div>

    </div>
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
                    <input type="date" name="start_date" id="booking_start" class="form-control mb-2">
                    <span class="text-danger error-text" id="error_start_date"></span>
                </div>

                <div class="form-group">
                    <label>End Date & Time</label>
                    <input type="date" name="end_date" id="booking_end" class="form-control mb-2" >
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
                        @foreach ($students as $val)
                        <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                        @endforeach
                    </select>
                    @endif
                    <span class="text-danger error-text" id="error_student"></span>
                </div>

                <div class="form-group">
                    <div id="create_resource_wrapper">
                        <label>Resource</label>
                        <select id="resource" name="resource" class="form-control mb-2">
                            <option value="">Select Resource</option>
                            @foreach ($resources as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="text-danger error-text" id="error_resource"></span>
                </div>

                <div class="form-group">
                    <div id="create_instructor_wrapper">
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

<!-- ====================================================== -->
<!-- VIEW BOOKING MODAL -->
<!-- ====================================================== -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <!-- <h5 class="modal-title">Booking Details</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded p-3 booking-card">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong>Single student</strong>
                        </div>
                        <div class="text-end text-success small">
                            <div id="booking_day"></div>
                            <div id="booking_time"></div>
                        </div>
                    </div>
                    <!-- Details -->
                    <ul class="list-unstyled small mb-3">
                        <li class="mb-1">
                            <i class="bi bi-bookmark-check text-success me-2"></i>
                            <span id="bookingType"></span>
                        </li>

                        <li class="mb-1">
                            <i class="bi bi-person-fill text-primary me-2"></i>
                            <span id="booking_student">CHAUN LDG65 - Naresh Chauhan</span>
                        </li>

                        <li class="mb-1">
                            <i class="bi bi-airplane text-secondary me-2"></i>
                            <span id="booking_resource"></span>
                        </li>

                        <li class="mb-1">
                            <i class="bi bi-journal-text me-2"></i>
                            <span id="booking_lesson">F18: Solo Circuit Consolidation</span>
                        </li>

                        <li class="mb-1">
                            <i class="bi bi-lock-fill text-danger me-2"></i>
                            <span id="booking_code">TK CCTS 1600Z-1645Z BOOKED</span>
                        </li>

                        <li class="mb-1">
                            <i class="bi bi-envelope-x text-muted me-2"></i>
                            Notify via email: <span id="mail_send"></span> 
                        </li>
                    </ul>
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
            </div>
            <!-- @if(auth()->user()->is_owner == 1 || Auth::user()->is_admin == 1)
                <div class="modal-footer">
                    <button id="editBookingBtn" class="btn btn-primary">Edit</button>
                    <button id="deleteBookingBtn" class="btn btn-danger">Delete</button>
                    @if(auth()->user()->is_owner == 1)
                        <button id="approveBtn" class="btn btn-success">Approve</button>
                        <button id="rejectBtn" class="btn btn-danger">Reject</button>
                    @endif
                </div>
            @endif -->
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

                <!-- <label>Org Unit</label>
                <select id="edit_organizationUnits" class="form-control mb-2">
                    <option value="">Select Org Unit</option>
                    @foreach ($organizationUnits as $val)
                        <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                    @endforeach
                </select> -->

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
            dateFormat: "Y-m-d H:i",
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
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            minuteIncrement: 15
        });
        $(document).on('change', '.resource-filter', function() {
            $('#calendar').fullCalendar('refetchEvents');
        });

        let ouChanged = false;
        $(document).on('change', '#change_organization_unit', function() {
            ouChanged = true;
            $('#calendar').fullCalendar('refetchEvents');
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

                let selectedResources = [];
                $('.resource-filter:checked').each(function() {
                    selectedResources.push($(this).val());
                });

                let data = {
                    resources: selectedResources
                };

                let ouId = $('#change_organization_unit').val();

                // ✅ Send ou_id ONLY when user selects one
                if (ouId) {
                    data.ou_id = ouId;
                }

                $.ajax({
                    url: SITEURL + "/fullcalendar",
                    data: data,
                    success: function(response) {
                        callback(response);
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
                //  $('#newBookingModal').modal('show'); 
                let startStr = moment(start).format("YYYY-MM-DD HH:mm");
                startPicker.setDate(startStr, true);
            },
            eventClick: function(event) {
                selectedEvent = event;
                var user_id = {{ auth()-> user()->id }};
             
                $('#booking_student').text(event.student);
                $('#booking_resource').text(event.resource);
                $('#start_date').text(moment(event.start).format('ddd MMM DD YYYY HH:mm:ss'));
                $('#end_date').text(moment(event.end).format('ddd MMM DD YYYY HH:mm:ss'));

                let typeText = '';
                if (event.booking_type == 1) {
                    typeText = 'Solo';
                } else if (event.booking_type == 2) {
                    typeText = 'Lesson';
                } else if (event.booking_type == 3) {
                    typeText = 'Standby';
                }
                $('#bookingType').text(typeText);

                let sendmail = '';
                if(event.send_mail == 0){
                    sendmail = "Disabled";
                }else if(event.send_mail == 1){
                   sendmail = "Enabled";
                }
                $('#mail_send').text(sendmail);

                let status = event.status ? event.status.charAt(0).toUpperCase() + event.status.slice(1) : '';
                $('#view_status').removeClass('text-warning text-success text-danger').text(status);

                //$('#view_status').text(status);
                if (event.status === 'pending') {
                    $('#view_status').addClass('text-warning'); // yellow
                } else if (event.status === 'approved') {
                    $('#view_status').addClass('text-success'); // green
                } else if (event.status === 'rejected') {
                    $('#view_status').addClass('text-danger'); // red
                }

                $('#approve_booking_id').val(event.id);
                $('#approve_organizationUnits').val(event.ou_id);
                $('#reject_booking_id').val(event.id);

                if (event.status == "pending") {
                    $('#approveBtn').show();
                    $('#rejectBtn').show();
                } else {
                    $('#approveBtn').hide();
                    $('#rejectBtn').hide();
                }
                   if (event.can_access == true) {
                    $('#booking_day').html(event.start.format('dddd, DD-MM-YYYY'));
                    $('#booking_student').html(event.student);
                    var startTime = event.start.format('HH:mm');
                    var endTime   = event.end.format('HH:mm');
                    $('#booking_time').html(startTime + ' - ' + endTime);
                    $('#viewBookingModal').modal('show');
                }
            }

        });

        function resetBookingForm() {
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
        $("#saveBookingBtn").on("click", function(e) {
            e.preventDefault();
            $(".loader").fadeIn();
            var formData = new FormData($('#booking_form')[0]); 
            $.ajax({
                url: '{{ url("/booking/store") }}', 
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) { 
                // $(".loader").fadeOut("slow");
                // $('#orgUnitModal').modal('hide');
                location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';
                        $('.error-text').text('');
                        $.each(errors, function(key, value) {
                            $('#error_' + key).text(value[0]);
                        });
                    } else {
                        alert('Something went wrong');
                    }
                }
            });

    })

        // $("#saveBookingBtn").click(function(e) {
        //     e.preventDefault();

        //     $.ajax({
        //         url: SITEURL + "/booking/store",
        //         type: "POST",
        //         data: {
        //             resource_id: $("#resource").val(),
        //             organizationUnits: $("#organizationUnits").val(),
        //             start: $('#booking_start').val(),
        //             end: $('#booking_end').val(),
        //             booking_type: $("#booking_type").val(),
        //             resource_type: $("#resource_type").val(),
        //             instructor: $("#booking_instructor").val(),
        //             student: $("#student").val(),
        //         },
        //         success: function(response) {
        //             $('#newBookingModal').modal('hide');
        //             $('#calendar').fullCalendar('refetchEvents'); 
        //             alert(response.message); // or custom UI
        //         },
        //         error: function(xhr) {
        //             if (xhr.status === 422) {
        //                 let errors = xhr.responseJSON.errors;
        //                 let errorMsg = '';
        //                 $('.error-text').text('');
        //                 $.each(errors, function(key, value) {
        //                     $('#error_' + key).text(value[0]);
        //                 });
        //             } else {
        //                 alert('Something went wrong');
        //             }
        //         }
        //     });
        // });


        // APPROVE BOOKING
        $("#approveBtn").click(function() {
            $.post(SITEURL + "/booking/approve", {
                id: $("#approve_booking_id").val(),
                organizationUnits: $("#approve_organizationUnits").val()
            }, function() {
                toastr.success("Booking Approved");
                $('#viewBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            });
        });

        $("#deleteBookingBtn").click(function() {
            if (!confirm('Cancel this booking?')) return;

            $.post(SITEURL + "/booking/delete", {
                id: selectedEvent.id
            }, function() {
                toastr.success("Booking Cancelled");
                $('#viewBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            });
        });


        // REJECT BOOKING
        $("#rejectBtn").click(function() {
            $.post(SITEURL + "/booking/reject", {
                id: $("#reject_booking_id").val(),
                organizationUnits: $("#approve_organizationUnits").val()
            }, function() {
                toastr.error("Booking Rejected");
                $('#viewBookingModal').modal('hide');
                $('#calendar').fullCalendar('refetchEvents');
            });
        });

        $(document).ready(function() {
            let ou = $("#organizationUnits");

            // If admin with fixed OU (hidden input)
            if (ou.length && ou.is('input[type="hidden"]') && ou.val()) {
                ou.trigger('change');
            }
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
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });

        let editEndPicker = flatpickr("#edit_booking_end", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true
        });

        $("#editBookingBtn").click(function() {
            $('#viewBookingModal').modal('hide');
            $('#edit_booking_id').val(selectedEvent.id);
            $('#edit_organizationUnits').val(selectedEvent.ou_id).trigger('change');

            setTimeout(function() {
                $('#edit_resource').val(selectedEvent.resource_id);
                $('#edit_student').val(selectedEvent.std_id);
                $('#edit_instructor').val(selectedEvent.instructor_id);
            }, 300);

            $('#edit_booking_type').val(selectedEvent.booking_type);

            // $('#edit_send_email').prop('checked', selectedEvent.send_email == 1 ? true : false);
            editStartPicker.setDate(moment(selectedEvent.start).format("YYYY-MM-DD HH:mm"), true);

            editEndPicker.setDate(
                moment(selectedEvent.end).format("YYYY-MM-DD HH:mm"),
                true
            );

            $('#editBookingModal').modal('show');
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

        handleBookingType(
            '#booking_type',
            '#create_resource_wrapper',
            '#resource',
            '#create_instructor_wrapper',
            '#booking_instructor'
        );

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
    });
</script>
@endsection