@extends('layout.app')

@section('title', 'Calendar')
@section('sub-title', 'Calendar')

@section('content')

<div class="container mt-4">
    <div id="calendar"></div>
</div>

@endsection

@section('js_scripts')

<!-- FullCalendar v3 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>

<!-- toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
$(document).ready(function () {

    var SITEURL = "{{ url('/') }}";

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var calendar = $('#calendar').fullCalendar({
        editable: true,
        events: SITEURL + "/fullcalendar",
        displayEventTime: false,
        selectable: true,
        selectHelper: true,

        select: function (start, end, allDay) {
            // var title = prompt('Event Title:');

            // if (title) {
            //     var start = $.fullCalendar.formatDate(start, "Y-MM-DD");
            //     var end = $.fullCalendar.formatDate(end, "Y-MM-DD");

            //     $.ajax({
            //         url: SITEURL + "/fullcalendarAjax",
            //         type: "POST",
            //         data: {
            //             title: title,
            //             start: start,
            //             end: end,
            //             type: 'add'
            //         },
            //         success: function (data) {
            //             $('#calendar').fullCalendar('renderEvent', {
            //                 id: data.id,
            //                 title: title,
            //                 start: start,
            //                 end: end,
            //                 allDay: allDay
            //             }, true);

            //             $('#calendar').fullCalendar('unselect');
            //             toastr.success("Event Created Successfully");
            //         }
            //     });
            // }
        },

        eventDrop: function (event) {
            var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
            var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD");

            $.ajax({
                url: SITEURL + '/fullcalendarAjax',
                type: "POST",
                data: {
                    id: event.id,
                    title: event.title,
                    start: start,
                    end: end,
                    type: 'update'
                },
                success: function () {
                    toastr.success("Event Updated Successfully");
                }
            });
        },

        eventClick: function (event) {
            if (confirm("Do you want to delete this event?")) {
                $.ajax({
                    url: SITEURL + '/fullcalendarAjax',
                    type: "POST",
                    data: {
                        id: event.id,
                        type: 'delete'
                    },
                    success: function () {
                        $('#calendar').fullCalendar('removeEvents', event.id);
                        toastr.success("Event Deleted Successfully");
                    }
                });
            }
        }
    });

});
</script>

@endsection
