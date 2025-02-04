@section('title', 'Groups')
@section('sub-title', 'Groups')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

@if(auth()->check() && auth()->user()->role == 1 && !empty(auth()->user()->ou_id))
<div class="create_btn">
    <button class="btn btn-primary create-button" id="createGroup" data-toggle="modal"
        data-target="#createGroupModal">Create Group</button>
</div>
@endif
<br>
<table class="table" id="groupTable">
    <thead>
        <tr>
            <th scope="col">Group Name</th>
            <th scope="col">User Count</th>
            @if(auth()->check() && auth()->user()->role == 1)
            <th scope="col">Edit</th>
            <th scope="col">Delete</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($groups as $val)
        <tr>
            <td class="groupname">{{ $val->name }}</td>
            <td>{{ $val->user_count }}</td> <!-- Display user count -->
            @if(auth()->check() && auth()->user()->role == 1)
            <td>
                <i class="fa fa-edit edit-group-icon" style="font-size:25px; cursor: pointer;"
                    data-group-id="{{ $val->id }}" data-group-name="{{ $val->name }}"
                    data-group-users="{{ json_encode($val->user_ids) }}"></i>
            </td>
            <td>
                <i class="fa-solid fa-trash delete-icon" style="font-size:25px; cursor: pointer;"
                    data-group-id="{{ $val->id }}" data-group-name="{{ $val->name }}"></i>
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Create Groups-->
<div class="modal fade" id="createGroupModal" tabindex="-1" role="dialog" aria-labelledby="groupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Create New Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="groups" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="name" class="form-label">Group Name<span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control">
                        <div id="group_name_error" class="text-danger error_e"></div>
                    </div>

                    <div class="form-group">
                        <label for="users" class="form-label">Select Users<span class="text-danger">*</span></label>
                        <select class="form-select users-select" name="user_ids[]" multiple="multiple">
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->fname }} {{ $user->lname }}</option>
                            @endforeach
                        </select>
                        <div id="users_error" class="text-danger error_e"></div>
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
<!--End of Groups-->

<!-- Edit Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1" role="dialog" aria-labelledby="editGroupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editGroupModalLabel">Edit Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editGroupForm" class="row g-3 needs-validation">
                    @csrf
                    <input type="hidden" name="group_id" id="edit_group_id">
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Group Name<span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control">
                        <div id="group_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_users" class="form-label">Select Users<span
                                class="text-danger">*</span></label>
                        <select class="form-select users-select" name="user_ids[]" id="edit_users" multiple="multiple">
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->fname }} {{ $user->lname }}</option>
                            @endforeach
                        </select>
                        <div id="users_error" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateGroup" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Group Edit-->

<!-- Delete Group Modal -->
<form id="deleteGroupForm" method="POST">
    @csrf
    <div class="modal fade" id="deleteGroup" tabindex="-1" aria-labelledby="deleteGroupLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteGroupLabel">Delete Group</h5>
                    <input type="hidden" name="group_id" id="delete_group_id">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Group "<strong><span id="delete_group_name"></span></strong>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="confirmDeleteGroup" class="btn btn-danger delete_group">Delete</button>
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

    // $('.users-select').select2({
    //     placeholder: "Select Users",
    //     allowClear: true
    // });

    $("#createGroup").on('click', function() {
        $(".error_e").html('');
        $("#groups")[0].reset();
        $("#createGroupModal").modal('show');
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

    $('.edit-group-icon').click(function(e) {
        e.preventDefault();

        $('.error_e').html('');
        var groupId = $(this).data('group-id');
        $.ajax({
            url: "{{ url('/group/edit') }}",
            type: 'GET',
            data: {
                id: groupId
            },
            success: function(response) {
                $('#edit_group_id').val(response.group.id);
                $('#edit_name').val(response.group.name);
                $('#edit_users').val(response.group.user_ids.split(',')).trigger('change');

                $('#editGroupModal').modal('show');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#updateGroup').on('click', function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ url('group/update') }}",
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
    $('.delete-icon').click(function() {
        var groupId = $(this).data('group-id');
        var groupName = $(this).data('group-name');

        $('#delete_group_id').val(groupId);
        $('#delete_group_name').text(groupName);
        $('#deleteGroup').modal('show');
    });

    // Confirm Delete
    $('#confirmDeleteGroup').click(function() {
        var groupId = $('#delete_group_id').val();

        $.ajax({
            url: "/group/delete",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function() {
                alert("Error deleting group!");
            }
        });
    });

});
</script>

@endsection