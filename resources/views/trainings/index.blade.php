@section('title', 'Training Event')
@section('sub-title', 'Training Event')
@extends('layout.app')
@section('content')


@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

@if(checkAllowedModule('groups','group.store')->isNotEmpty())
<div class="create_btn">
    <button class="btn btn-primary create-button" id="createTrainingEvent" data-toggle="modal"
        data-target="#createTrainingEventModal">Create Training Event</button>
</div>
@endif
<br>

<div class="card pt-4">
        <div class="card-body">
    <table class="table table-hover" id="trainingEventTable">
    <thead>
        <tr>
            <th scope="col">Event</th>
            <th scope="col">Group</th>
            <th scope="col">Instructor</th>
            <th scope="col">Start Time</th>
            <th scope="col">End Time</th>
            <th scope="col">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trainingEvents as $event)
        <tr>
            <td class="eventName">{{ $event->course->course_name }}</td>
            <td>{{ $event->group->name }}</td>
            <td>{{ $event->instructor->fname }} {{ $event->instructor->lname }}</td>
            <td>{{ date('h:i A', strtotime($event->start_time)) }}</td>
            <td>{{ date('h:i A', strtotime($event->end_time)) }}</td>
            <td>
            @if(checkAllowedModule('training','training.edit')->isNotEmpty())
                <i class="fa fa-edit edit-event-icon" style="font-size:25px; cursor: pointer;"
                data-event-id="{{ encode_id($event->id) }}"></i>
            @endif
            @if(checkAllowedModule('training','training.delete')->isNotEmpty())
                <i class="fa-solid fa-trash delete-event-icon" style="font-size:25px; cursor: pointer;"
                data-event-id="{{ encode_id($event->id) }}" ></i>
            @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
</div>


<!-- Create Training Event-->
<div class="modal fade" id="createTrainingEventModal" tabindex="-1" role="dialog" aria-labelledby="groupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Create Training Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="trainingEventForm" method="POST" class="row g-3">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Select Course<span class="text-danger">*</span></label>
                        <select class="form-select" name="course_id" aria-label="Default select example" id="select_course">
                            <option value="">Select Course</option>
                            @foreach($course as $val)
                            <option value="{{ $val->id }}">{{ $val->course_name }}</option>
                            @endforeach
                        </select>
                        <div id="course_id_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Group<span class="text-danger">*</span></label>
                        <select class="form-select" name="group_id" aria-label="Default select example" id="select_group">
                            <option value="">Select Group</option>
                            @foreach($group as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_id_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Instructor<span class="text-danger">*</span></label>
                        <select class="form-select" name="instructor_id" aria-label="Default select example" id="select_instructor">
                            <option value="">Select Instructor</option>
                            @foreach($instructor as $val)
                            <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                            @endforeach
                        </select>
                        <div id="instructor_id_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Start Time<span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" >
                        <div id="start_time_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">End Time<span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" >
                        <div id="end_time_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitTrainingEvent" class="btn btn-primary sbt_btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Training Event-->

<!-- Edit Training Event-->
<div class="modal fade" id="editTrainingEventModal" tabindex="-1" role="dialog" aria-labelledby="groupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Edit Training Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="editTrainingEventForm" method="POST" class="row g-3">
                    @csrf
                    <input type="hidden" name="event_id" id="edit_event_id">
                    <div class="form-group">
                        <label for="email" class="form-label">Select Course<span class="text-danger">*</span></label>
                        <select class="form-select" name="course_id" aria-label="Default select example" id="edit_select_course">
                            <option value="">Select Course</option>
                            @foreach($course as $val)
                            <option value="{{ $val->id }}">{{ $val->course_name }}</option>
                            @endforeach
                        </select>
                        <div id="course_id_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Group<span class="text-danger">*</span></label>
                        <select class="form-select" name="group_id" aria-label="Default select example" id="edit_select_group">
                            <option value="">Select Group</option>
                            @foreach($group as $val)
                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_id_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Instructor<span class="text-danger">*</span></label>
                        <select class="form-select" name="instructor_id" aria-label="Default select example" id="edit_select_instructor">
                            <option value="">Select Instructor</option>
                            @foreach($instructor as $val)
                            <option value="{{ $val->id }}">{{ $val->fname }} {{ $val->lname }}</option>
                            @endforeach
                        </select>
                        <div id="instructor_id_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Start Time<span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" id="edit_start_time">
                        <div id="start_time_error_up" class="text-danger error_e"></div>            
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">End Time<span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" id="edit_end_time">
                        <div id="end_time_error_up" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateTrainingEvent" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Edit Training Event-->


<!-- Delete Group Modal -->
<form action="{{ url('training/delete') }}" id="deleteTrainingEventForm" method="POST">
    @csrf
    <div class="modal fade" id="deleteTrainingEvent" tabindex="-1" aria-labelledby="deleteEventLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTrainingEventLabel">Delete Training Event</h5>
                    <input type="hidden" name="event_id" id="eventId">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Training Event "<strong><span id="append_name"></span></strong>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="confirmDeleteTrainingEvent" class="btn btn-danger delete_group">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
    $('#groupTable').DataTable();

    // Initialize Select2 globally on all user selection dropdowns
    function initializeSelect2() {
        $('.users-select').select2({
            allowClear: true,
            dropdownParent: $('.modal:visible') // Fix for modals
        });
    }

    initializeSelect2(); // Call on page load

    $("#createTrainingEvent").on('click', function() {
        $(".error_e").html('');
        $("#trainingEventForm")[0].reset();
        $("#createTrainingEventModal").modal('show');

        initializeSelect2(); // Ensure Select2 is re-initialized
    })

    $("#submitTrainingEvent").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/training/create") }}',
            type: 'POST',
            data: $("#trainingEventForm").serialize(),
            success: function(response) {
                $('#createTrainingEventModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error').html(msg);
                })
            }
        });

    })

    // $('.edit-group-icon').click(function(e) {
    //     e.preventDefault();
    $(document).on('click', '.edit-event-icon', function() {
        $('.error_e').html('');
        var eventId = $(this).data('event-id');
        $.ajax({
            url: "{{ url('/training/edit') }}",
            type: 'GET',
            data: {
                eventId: eventId
            },
            success: function(response) {
                console.log(response)
                $('#edit_select_course').val(response.trainingEvent.course_id);
                $('#edit_select_group').val(response.trainingEvent.group_id);
                $('#edit_select_instructor').val(response.trainingEvent.instructor_id);
                $('#edit_start_time').val(response.trainingEvent.start_time);
                $('#edit_end_time').val(response.trainingEvent.end_time);
                $('#edit_event_id').val(response.trainingEvent.id);

                $('#editTrainingEventModal').modal('show');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#updateTrainingEvent').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ url('/training/update') }}",
            type: "POST",
            data: $("#editTrainingEventForm").serialize(),
            success: function(response) {
                $('#editTrainingEventForm').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error_up').html(msg);
                })
            }
        })
    })

    // Handle Delete Group Click
    // $('.delete-icon').click(function() {
    //     var groupId = $(this).data('group-id');
    //     var groupName = $(this).data('group-name');

    //     $('#delete_group_id').val(groupId);
    //     $('#delete_group_name').text(groupName);
    //     $('#deleteGroup').modal('show');
    // });

    // Delete Group
    $(document).on('click', '.delete-event-icon', function() {
        $('#deleteTrainingEvent').modal('show');
        var eventId = $(this).data('event-id');
        var eventName = $(this).closest('tr').find('.eventName').text();
        $('#append_name').html(eventName);
        $('#eventId').val(eventId);      
    });

    // Ensure Select2 works when modal is shown
    $('#createGroupModal, #editGroupModal').on('shown.bs.modal', function() {
        initializeSelect2();
    });

    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
    }, 2000);

});
</script>

@endsection