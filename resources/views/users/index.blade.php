@section('title', 'Users')
@section('sub-title', 'Users')
@extends('layout.app')
@section('content')
<div class="main_cont_outer">
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif

    @if(checkAllowedModule('users','user.store')->isNotEmpty())
    <div class="create_btn">
        <a href="#" class="btn btn-primary create-button" id="createUser" data-toggle="modal"
            data-target="#userModal">Create User</a>
    </div>
    @endif
    <div id="update_success_msg"></div>
    <table class="table" id="user_table">
        <thead>
            <tr>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
                @if(auth()->user()->ou_id == null)
                <th scope="col">OU</th>
                @endif
                @if(auth()->user()->ou_id)
                <th scope="col">Position</th>
                @endif

                <th scope="col">Status</th>
                @if(checkAllowedModule('users','user.get')->isNotEmpty())
                <th scope="col">Edit</th>
                @endif   
                @if(checkAllowedModule('users','user.destroy')->isNotEmpty())
                <th scope="col">Delete</th>
                @endif  
            </tr>
        </thead>
        <tbody>
            @foreach($users as $val)
            <tr>
                <td scope="row" class="fname">{{ $val->fname }}</td>
                <td scope="row" class="lname">{{ $val->lname }}</td>
                <td>{{ $val->email }}</td>
                @if(auth()->user()->ou_id == null)
                <td>{{ $val->organization ? $val->organization->org_unit_name : '--' }}</td>
                @endif
                @if(auth()->user()->ou_id)
                    <td>{{ $val->roles ? $val->roles->role_name : '--' }}</td>
                @endif
                <td>{{ ($val->status==1)? 'Active': 'Inactive' }}</td>
                @if(checkAllowedModule('users','user.get')->isNotEmpty())
                <td><i class="fa fa-edit edit-user-icon" style="font-size:18px; cursor: pointer;"
                    data-user-id="{{ encode_id($val->id) }}"></i></td>
                @endif    
                @if(checkAllowedModule('users','user.destroy')->isNotEmpty())
                <td><i class="fa-solid fa-trash delete-icon" style="font-size:18px; cursor: pointer;"
                data-user-id="{{ encode_id($val->id) }}"></i></td>
                @endif 
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Create User -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Create Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="Create_user" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control">
                        <div id="firstname_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control">
                        <div id="lastname_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control">
                        <div id="email_error" class="text-danger error_e"></div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control">
                        <div id="password_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmpassword" class="form-label">Confirm Password<span
                                class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" id="confirmpassword">
                        <div id="password_confirmation_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
                        <select name="role_name" class="form-select" id="role">
                            @foreach($roles as $val)
                            <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                            @endforeach

                        </select>
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
                        <a href="#" type="button" id="saveuser" class="btn btn-primary sbt_btn">Save </a>
                    </div>

                    {{-- <button id="loader" style="display:none" class="btn btn-primary" type="button" disabled="">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                  </button> --}}

                  <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of create user-->

<!-- Edit user -->
<div class="modal fade" id="editUserDataModal" tabindex="-1" role="dialog" aria-labelledby="editUserDataModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserDataModalLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="Create_user3" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
                        <input type="text" name="edit_firstname" class="form-control">
                        <input type="hidden" name="edit_form_id" id="edit_form_id" class="form-control">

                        <div id="fname_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                        <input type="text" name="edit_lastname" class="form-control">
                        <div id="lname_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" name="edit_email" class="form-control">
                        <div id="email_error_up" class="text-danger error_e"></div>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
                        <select name="edit_role_name" class="form-select" id="edit_role">
                            @foreach($roles as $val)
                            <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                            @endforeach

                        </select>
                        <div id="edit_role_name_error_up" class="text-danger error_e"></div>
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

    $('#createUser').on('click', function() {
        $('.error_e').html('');
        $('.alert-danger').css('display', 'none');
        $('#userModal').modal('show');        
    });

    $('#saveuser').click(function(e) {
        e.preventDefault();
        // $('#loader').show();
        $(".loader").fadeIn();

        
        $('.error_e').html('');
        $.ajax({
            url: '{{ url("/users/save") }}',
            type: 'POST',
            data: $('#Create_user').serialize(),
            success: function(response) {
                // $('#loader').hide();
                $(window).load(function() {
                    $(".loader").fadeOut("slow");
                })
            console.log(response);
                $('#userModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                // $('#loader').hide();
                $(".loader").fadeOut("slow");
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var html1 = '<p>' + value + '</p>';
                    $('#' + key + '_error').html(html1);
                });
            }
        });
    });

    $('.edit-user-icon').click(function(e) {
        e.preventDefault();
        $('.error_ee').html('');
        var userId = $(this).data('user-id');
        vdata = {
            id: userId,
            "_token": "{{ csrf_token() }}",
        };
        $.ajax({
            type: 'post',
            url: "{{ url('users/edit') }}",
            data: vdata,
            success: function(response) {
                $('input[name="edit_firstname"]').val(response.user.fname);
                $('input[name="edit_lastname"]').val(response.user.lname);
                $('input[name="edit_email"]').val(response.user.email);
                $('input[name="edit_form_id"]').val(response.user.id);
                $('#edit_status').val(response.user.status);

                // Primary role
                var userRoleId = response.user.role;
                $('#role_id option').removeAttr('selected');
                $('#edit_role option[value="' + userRoleId + '"]').attr('selected',
                    'selected');

                //Secondary role
                var secondary_role = response.user.role_id1;
                //  $('#secondary_role').val('');
                $('#secondary_role option').removeAttr('selected');
                $('#secondary_role option[value="' + secondary_role + '"]').attr('selected',
                    'selected');
                $('#editUserDataModal').modal('show');
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
                'status': $("#edit_status").val(),
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