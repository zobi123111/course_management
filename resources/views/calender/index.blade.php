@extends('layout.app')

@section('title', 'Calendar')
@section('sub-title', 'Calendar')

@section('content')

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

                <label>Resource</label>
                <select id="resource" name="resource" class="form-control mb-2">
                    <option value="">Select Resource</option>
                    @foreach ($resources as $val)
                    <option value="{{ $val->id }}">{{ $val->name }}</option>
                    @endforeach
                </select>
                @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                @endif

                @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                <label>Select Student</label>
                <select id="student" name="student" class="form-control mb-2">
                    <option value="">Select Student</option>
                    @foreach ($students as $val)
                    <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                    @endforeach
                </select>
                @endif

                <label>Start Date & Time</label>
                <input type="text" id="booking_start" class="form-control mb-2" readonly>

                <label>End Date & Time</label>
                <input type="text" id="booking_end" class="form-control mb-2" readonly>

                <label>Booking Type</label>
                <select id="booking_type" name="booking_type" class="form-control mb-2">
                    <option value="1">Solo</option>
                    <option value="2">Lesson</option>
                    <option value="3">Standby</option>
                </select>

                <label>Resource Type</label>
                <select name="resource_type" class="form-control mb-2">
                    <option value="1">Plane</option>
                    <option value="2">Simulator</option>
                    <option value="3">Classroom</option>
                </select>

                <label>Instructor (optional)</label>
                <input type="text" name="instructor_id" class="form-control mb-2">

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

@endsection


@section('js_scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    $(function() {

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

            onChange: function(selectedDates, dateStr) {
                if (!dateStr) return;

                // Add 1 day using moment (keeps time intact)
                let endStr = moment(dateStr, "YYYY-MM-DD HH:mm")
                .add(1, 'days')
                .format("YYYY-MM-DD HH:mm");
                endPicker.setDate(endStr, true); 
            }
        });



        let endPicker = flatpickr("#booking_end", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
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
            selectOverlap: false,
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
                if (event.status === 'pending') element.css('background', '#f1c40f');
                if (event.status === 'approved') element.css('background', '#2ecc71');
                if (event.status === 'rejected') element.css('background', '#9b4747');
                if (event.status === 'standby') element.css('background', '#e67e22');
            },
            select: function(start) {
                $('#newBookingModal').modal('show');
                let startStr = moment(start).format("YYYY-MM-DD HH:mm");
                let endStr = moment(start).add(1, 'days').format("YYYY-MM-DD HH:mm");
                startPicker.setDate(startStr, true);
                endPicker.setDate(endStr, true);
            },
            eventClick: function(event) {
                var user_id =  {{ auth()->user()->id }};
               if(event.can_access == true){
                 $('#viewBookingModal').modal('show');
               }
                $('#booking_student').text(event.student);
                $('#booking_resource').text(event.resource);
                $('#start_date').text(moment(event.start).format("DD-MM-YYYY HH:mm"));
                $('#end_date').text(moment(event.end).format("DD-MM-YYYY HH:mm"));
                let typeText = '';
                if (event.booking_type == 1) {
                    typeText = 'Solo';
                } else if (event.booking_type == 2) {
                    typeText = 'Lesson';
                } else if (event.booking_type == 3) {
                    typeText = 'Standby';
                }

                $('#view_type').text(typeText);
                $('#view_status').text(event.status || '');

                $('#approve_booking_id').val(event.id);
                $('#reject_booking_id').val(event.id);

                if (!event.can_approve) {
                    // $('#approveBtn').hide();
                    // $('#rejectBtn').hide();
                } else {
                    $('#approveBtn').show();
                    $('#rejectBtn').show();
                }
            }

        });


$('#studentSearch, #resourceSearch').on('keyup', function () {
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
            var end = moment($('#booking_end').val(), "YYYY-MM-DD HH:mm")
                .add(1, 'days')
                .format("YYYY-MM-DD HH:mm");

            if (!resource_id) {
                toastr.error('Please select a resource');
                return;
            }
            if (!start || !end) {
                toastr.error('Please select start and end date/time');
                return;
            }

            $.post(SITEURL + "/booking/store", {
                resource_id: resource_id,
                start: start,
                end: end,
                organizationUnits: organizationUnits,
                group: group,
                booking_type: $("#booking_type").val(),
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
            $groupSelect.empty().append("<option value=''>Select Group</option>").trigger("change");
            $resourceSelect.empty().append("<option value=''>Select Resource</option>").trigger("change");
            $student.empty().append("<option value=''>Select Student</option>").trigger("change");


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
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });


    });
</script>

@endsection