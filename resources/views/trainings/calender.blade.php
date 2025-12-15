@extends('layout.app')

@section('title', 'Calendar')
@section('sub-title', 'Calendar')

@section('content')

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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <label>Resource</label>
        <select id="booking_title" class="form-control mb-2">
            <option value="">Select Resource</option>
            @foreach ($resources as $val)
                <option value="{{ $val->id }}">{{ $val->name }}</option>
            @endforeach
        </select>

      <label>Start Date</label>
        <div class="input-group date mb-2" id="startPicker" data-target-input="nearest">
            <input type="text" id="booking_start" class="form-control datetimepicker-input"
                data-target="#startPicker" readonly/>
            <div class="input-group-append" data-target="#startPicker" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>

            <label>End Date</label>
        <div class="input-group date mb-2" id="endPicker" data-target-input="nearest">
            <input type="text" id="booking_end" class="form-control datetimepicker-input"
                data-target="#endPicker" readonly/>
            <div class="input-group-append" data-target="#endPicker" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>

        <label>Booking Type</label>
        <select id="booking_type" class="form-control mb-2">
            <option value="solo">Solo</option>
            <option value="lesson">Lesson</option>
            <option value="standby">Standby</option>
        </select>

        <label>Resource Type</label>
        <select id="booking_resource" class="form-control mb-2">
            <option value="plane">Plane</option>
            <option value="simulator">Simulator</option>
            <option value="classroom">Classroom</option>
        </select>

        <label>Instructor (optional)</label>
        <input type="text" id="booking_instructor" class="form-control mb-2">

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
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <p><strong>Title:</strong> <span id="view_title"></span></p>
        <p><strong>Date:</strong> <span id="view_date"></span></p>
        <p><strong>Type:</strong> <span id="view_type"></span></p>
        <p><strong>Status:</strong> <span id="view_status"></span></p>

        <input type="hidden" id="approve_booking_id">
        <input type="hidden" id="reject_booking_id">

      </div>

      <div class="modal-footer">
        <button id="approveBtn" class="btn btn-success">Approve</button>
        <button id="rejectBtn" class="btn btn-danger">Reject</button>
      </div>

    </div>
  </div>
</div>

@endsection


@section('js_scripts')

<!-- jQuery (required) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Moment.js (single import, used by FullCalendar and Tempus Dominus) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- Popper (for Bootstrap tooltips/modals) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>

<!-- Bootstrap CSS & JS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Font Awesome (calendar icon) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

<!-- FullCalendar v3 (relies on moment) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js"></script>

<!-- Tempus Dominus Bootstrap 4 (datetimepicker) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

<!-- Toastr -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
$(function () {

    var SITEURL = "{{ url('/') }}";

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ---------------------------
    // Initialize datetimepickers
    // ---------------------------
    // Important: initialize after Tempus Dominus script is loaded
$('#startPicker').datetimepicker({
    format: 'YYYY-MM-DD',
    allowInputToggle: true,
    minDate: false   // <-- allow all future dates
});

$('#endPicker').datetimepicker({
    format: 'YYYY-MM-DD',
    allowInputToggle: true,
    useCurrent: false,
    icons: {
        date: 'fa fa-calendar',
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        close: 'fa fa-times'
    }
});

    // Link start and end so end cannot be before start
//   $("#startPicker").on("change.datetimepicker", function (e) {
//     $('#endPicker').datetimepicker('minDate', e.date);
// });
//   $("#endPicker").on("change.datetimepicker", function (e) {
//     $('#startPicker').datetimepicker('maxDate', e.date);
// });

    // ---------------------------
    // FullCalendar
    // ---------------------------
    $('#calendar').fullCalendar({
        editable: false,
        selectable: true,
        displayEventTime: true,
        events: SITEURL + "/fullcalendar",

        eventRender: function(event, element) {
            if (event.status === 'pending') element.css('background', '#f1c40f');
            if (event.status === 'approved') element.css('background', '#3498db');
            if (event.status === 'confirmed') element.css('background', '#2ecc71');
            if (event.status === 'standby') element.css('background', '#e67e22');
        },

        select: function(start, end) {
            // open modal and set pickers
            $('#newBookingModal').modal('show');

            // set values in datetimepickers
            var s = moment(start).format('YYYY-MM-DD HH:mm');
            var e = moment(end).format('YYYY-MM-DD HH:mm');

            // use datetimepicker('date', ...) to set the widget value
            $('#startPicker').datetimepicker('date', moment(s, 'YYYY-MM-DD HH:mm'));
            $('#endPicker').datetimepicker('date', moment(e, 'YYYY-MM-DD HH:mm'));
        },

        eventClick: function(event) {
            $('#viewBookingModal').modal('show');

            $('#view_title').text(event.title);
            $('#view_date').text(moment(event.start).format("YYYY-MM-DD HH:mm"));
            $('#view_type').text(event.booking_type || '');
            $('#view_status').text(event.status || '');

            $('#approve_booking_id').val(event.id);
            $('#reject_booking_id').val(event.id);

            if (!event.can_approve) {
                $('#approveBtn').hide();
                $('#rejectBtn').hide();
            } else {
                $('#approveBtn').show();
                $('#rejectBtn').show();
            }
        }

    });

    // ---------------------------
    // Save Booking (AJAX)
    // ---------------------------
    $("#saveBookingBtn").click(function(e){
        e.preventDefault();

        // read values (from datetimepicker inputs)
        var title = $("#booking_title option:selected").text();
        var resource_id = $("#booking_title").val();
        var start = $('#startPicker').datetimepicker('date') ? $('#startPicker').datetimepicker('date').format('YYYY-MM-DD HH:mm') : $('#booking_start').val();
        var end = $('#endPicker').datetimepicker('date') ? $('#endPicker').datetimepicker('date').format('YYYY-MM-DD HH:mm') : $('#booking_end').val();

        if (!resource_id) {
            toastr.error('Please select a resource');
            return;
        }
        if (!start || !end) {
            toastr.error('Please select start and end date/time');
            return;
        }

        $.post(SITEURL + "/booking/store", {
            title: title,
            resource_id: resource_id,
            start: start,
            end: end,
            booking_type: $("#booking_type").val(),
            resource: $("#booking_resource").val(),
            instructor: $("#booking_instructor").val()
        }, function(response){
            toastr.success("Booking Request Submitted");
            $('#newBookingModal').modal('hide');
            $('#calendar').fullCalendar('refetchEvents');
        }).fail(function(xhr){
            toastr.error('Failed to save booking');
            console.error(xhr.responseText);
        });
    });

    // APPROVE BOOKING
    $("#approveBtn").click(function(){
        $.post(SITEURL + "/booking/approve", {
            id: $("#approve_booking_id").val()
        }, function(){
            toastr.success("Booking Approved");
            $('#viewBookingModal').modal('hide');
            $('#calendar').fullCalendar('refetchEvents');
        });
    });

    // REJECT BOOKING
    $("#rejectBtn").click(function(){
        $.post(SITEURL + "/booking/reject", {
            id: $("#reject_booking_id").val()
        }, function(){
            toastr.error("Booking Rejected");
            $('#viewBookingModal').modal('hide');
            $('#calendar').fullCalendar('refetchEvents');
        });
    });

});
</script>

@endsection
