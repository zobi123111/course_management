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
            <th scope="col">Time</th>
            @if(checkAllowedModule('training','training.edit')->isNotEmpty())
            <th scope="col">Edit</th>
            @endif
            @if(checkAllowedModule('training','training.delete')->isNotEmpty())
            <th scope="col">Delete</th>
            @endif
        </tr>
    </thead>
    <tbody>
      
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
                <form action="" id="training_event_form" method="POST" class="row g-3">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Select Course<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example" id="select_org_unit">
                            <option value="">Select Course</option>
                        </select>
                        <div id="name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Group<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example" id="select_org_unit">
                            <option value="">Select Group</option>
                        </select>
                        <div id="name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Instructor<span class="text-danger">*</span></label>
                        <select class="form-select" name="instructor_id" aria-label="Default select example" id="instructor">
                            <option value="">Select Instructor</option>
                        </select>
                        <div id="name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Start Time<span class="text-danger">*</span></label>
                        <input type="time" class="form-control" >
                        <div id="strat_time_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">End Time<span class="text-danger">*</span></label>
                        <input type="time" class="form-control" >
                        <div id="end_time_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitGroup" class="btn btn-primary sbt_btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Training Event-->


<!-- Delete Group Modal -->
<form action="{{ url('group/delete') }}" id="deleteGroupForm" method="POST">
    @csrf
    <div class="modal fade" id="deleteGroup" tabindex="-1" aria-labelledby="deleteGroupLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGroupLabel">Delete Group</h5>
                    <input type="hidden" name="group_id" id="groupId">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Group "<strong><span id="append_name"></span></strong>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="confirmDeleteGroup" class="btn btn-danger delete_group">Delete</button>
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
        $("#training_event_form")[0].reset();
        $("#createTrainingEventModal").modal('show');

        initializeSelect2(); // Ensure Select2 is re-initialized
    })

    $("#submitGroup").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/group/create") }}',
            type: 'POST',
            data: $("#groups").serialize(),
            success: function(response) {
                $('#createGroupModal').modal('hide');
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
    $('#groupTable').on('click', '.edit-group-icon', function() {

        $('.error_e').html('');
        var groupId = $(this).data('group-id');
        $.ajax({
            url: "{{ url('/group/edit') }}",
            type: 'GET',
            data: {
                id: groupId
            },
            success: function(response) {
                console.log(response)
                $('#edit_name').val(response.group.name);
                $('#edit_group_id').val(response.group.id);
                $('#edit_ou_id').val(response.group.ou_id);
                $('#edit_status').val(response.group.status);

                let selectedUsers = response.group.user_ids ? response.group.user_ids.map(String) : [];

                console.log(selectedUsers);
                $('#edit_users').val(selectedUsers).trigger('change');

                $('#editGroupModal').modal('show');

                initializeSelect2();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#updateGroup').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ url('/group/update') }}",
            type: "POST",
            data: $("#editGroupForm").serialize(),
            success: function(response) {
                $('#editGroupModal').modal('hide');
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
    $(document).on('click', '.delete-group-icon', function() {
        $('#deleteGroup').modal('show');
        var groupId = $(this).data('group-id');
        var groupName = $(this).closest('tr').find('.groupName').text();
        $('#append_name').html(groupName);
        $('#groupId').val(groupId);
      
    });

    $(document).on("change", "#select_org_unit", function(){
        var ou_id = $(this).val();
        var $selectUser = $("#usersDropdown"); // Target dropdown

        $.ajax({
            url: "/group/get_ou_user/",
            type: "GET",
            data: { 'ou_id': ou_id },
            dataType: "json",  // Ensures response is treated as JSON
            success: function(response){
                console.log(response);

                if (response.orguser && Array.isArray(response.orguser)) { // Access `orguser` array
                    var options = "<option value=''>Select User</option>"; // Default option
                    
                    response.orguser.forEach(function(value){ // Iterate over `orguser` array
                        options += "<option value='" + value.id + "'>" + value.fname + " " + value.lname + "</option>";
                    });

                    $selectUser.html(options); // Replace existing options
                } else {
                    console.error("Invalid response format:", response);
                }
            },
            error: function(xhr, status, error){
                console.error(xhr.responseText);
            } 
        });
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