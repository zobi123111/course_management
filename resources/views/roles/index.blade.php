@section('title', 'Roles')
@section('sub-title', 'Roles')
@extends('layout.app')
@section('content')
<div class="main_cont_outer">
    <div class="create_btn">
        <button class="btn btn-primary create-button" id="createRole" data-toggle="modal"
            data-target="#roleModal">Create Role</button>
    </div>
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif
    <div id="update_success_msg"></div>
    <table class="table" id="user_table">
        <thead>
            <tr>
                <th scope="col">Role Name</th>
                <th scope="col">Status</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $val)
            <tr>
                <td class="fname">{{ $val->role_name }}</td>
                <td>{{ $val->status }}</td>
                <td><i class="fa fa-edit edit-role-icon" style="font-size:18px; cursor: pointer;"
                        data-role-id="{{ $val->id }}"></i></td>
                <td><i class="fa-solid fa-trash delete-icon" style="font-size:18px; cursor: pointer;"
                        data-role-id="{{ $val->id }}"></i></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Create User -->
<div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalLabel">Create Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="roleForm" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Role Name<span class="text-danger">*</span></label>
                        <input type="text" name="role_name" class="form-control">
                        <div id="role_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="saveRole" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of create user-->

<!-- Edit user -->
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="Create_user3" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Role Name<span class="text-danger">*</span></label>
                        <input type="text" name="role_name" class="form-control">
                        <input type="hidden" name="role_id" id="role_id" class="form-control">
                        <div id="role_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="edit_status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="updateForm" class="btn btn-primary sbt_btn">Update</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Edit user-->

<!--Delete  Modal -->
<form action="{{ url('/users/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="id" id="userid" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary user_delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End of Delete Model -->
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
    $('#user_table').DataTable();

    $('#createRole').on('click', function() {
        $('.error_e').html('');
        $('.alert-danger').css('display', 'none');
        $('#roleModal').modal('show');
    });

    $('#saveRole').click(function(e) {
        e.preventDefault();
        $('.error_e').html('');
        $.ajax({
            url: '{{ url("role/create") }}',
            type: 'POST',
            data: $('#roleForm').serialize(),
            success: function(response) {
                $('#roleModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var html1 = '<p>' + value + '</p>';
                    $('#' + key + '_error').html(html1);
                });
            }
        });
    });

    $('.edit-role-icon').click(function(e) {
        e.preventDefault();
        $('.error_ee').html('');
        var roleId = $(this).data('role-id');
        // vdata = {
        //     id: roleId,
        //     "_token": "{{ csrf_token() }}",
        // };
        $.ajax({
            type: 'post',
            url: "{{ url('role/edit') }}",
            data: {roleId: roleId},
            success: function(response) {
                $('input[name="role_name"]').val(response.user.fname);
                $('#edit_status').val();
                $('#editRoleModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
    

    // Update user  form 
    // Use event delegation for update form button
    $(document).on('click', '#updateForm', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'post',
            url: "/users/update",
            data: {
                'fname': $("input[name=edit_firstname]").val(),
                'lname': $("input[name=edit_lastname]").val(),
                'email': $("input[name=edit_email]").val(),
                'role': $("select[name=edit_role_name]").val(),
                'edit_form_id': $("input[name=edit_form_id]").val(),
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $('#editUserDataModal').modal('hide');
                $('#update_success_msg').html(`
                <div class="alert alert-success fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i>
                    ${response.message}
                </div>
                `).stop(true, true).fadeIn();
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var html = '<p>' + value + '</p>';
                    $('#' + key + '_error_up').html(html);
                });
            }
        });
    });

    $('.delete-icon').click(function(e) {
        e.preventDefault();
        $('#deleteUserModal').modal('show');
        var userId = $(this).data('user-id');
        var fname = $(this).closest('tr').find('.fname').text();
        var lname = $(this).closest('tr').find('.lname').text();
        var name = fname + ' ' + lname;
        $('#append_name').html(name);
        $('#userid').val(userId);

    });

    setTimeout(function() {
        $('#successMessage').fadeOut('fast');
    }, 2000);

});
</script>

@endsection