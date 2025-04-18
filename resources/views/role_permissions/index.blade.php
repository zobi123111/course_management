@section('title', 'Roles')
@section('sub-title', 'Roles')
@extends('layout.app')
@section('content')
<div class="main_cont_outer">
    @if(checkAllowedModule('roles', 'roles.create')->isNotEmpty())
    <div class="create_btn">
        <a href="{{ route('roles.create') }}" class="btn btn-primary create-button btn_primary_color"
            id="createrole">Create Role</a>
    </div>
    @endif
    <div id="successMessagea" class="alert alert-success" style="display: none;" role="alert">
        <i class="bi bi-check-circle me-1"></i>
    </div>

    {{-- @if(session()->has('error'))
        <div id="ErrorMessage" class="alert alert-danger fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            {{ session()->get('error') }}
        </div>
    @endif --}}
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif
    <div class="card pt-4">
        <div class="card-body">
    <table class="table table-hover" id="role_table" style="padding-top: 10px;">
        <thead>
            <tr>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
                @if(checkAllowedModule('roles', 'roles.edit')->isNotEmpty())
                <th scope="col">Edit</th>
                @endif
                @if(checkAllowedModule('roles', 'roles.destroy')->isNotEmpty())
                <th scope="col">Delete</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
            <tr>
                <td scope="row" class="fname">{{ $role->role_name }}</td>
                <td>{{ ($role->status == 1) ? 'Active' : 'Inactive' }}</td>
                @if(checkAllowedModule('roles', 'roles.edit')->isNotEmpty())
                <td><a href="{{ route('roles.edit', ['role' => encode_id($role->id)]) }}"><i
                            class="fa fa-edit edit-user-icon table_icon_style blue_icon_color"
                            data-user-id="{{ $role->id }}"></i></a></td>
                @endif
                @if(checkAllowedModule('roles', 'roles.destroy')->isNotEmpty())
                    <td>                       
                            <i class="fa-solid fa-trash delete-icon table_icon_style blue_icon_color"
                                data-role-id="{{ encode_id($role->id) }}" data-users-count="{{ $role->users_count }}"></i>
                    </td>
                @endif
            </tr>
            @endforeach
            
        </tbody>
    </table>
</div>
</div>

<form method="POST" id="deleteRoleFormId" >
    @csrf
    @method('DELETE')
    <div class="modal fade" id="deleteRoleForm" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span class="show_users_count" style="display: none;">Cannot delete this role. It is assigned to <strong class="users_count"></strong> users</span>
                    <span class="show_delete_user" style="display: none;">             
                        Are you sure you want to delete this role "<strong><span id="append_name"> </span></strong>" ? 
                    </span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
<!-- End of Delete Model -->
@endsection

@section('js_scripts')

<script>

$(document).ready(function() {
    @if(session('role_with_users'))
    $('.delete-icon').each(function() {
        var roleId = $(this).data('role-id');
        var fname = $(this).closest('tr').find('.fname').text();
        $('#append_name').html(fname);
        $(this).closest('td').find('.btn-danger').removeClass('disabled');
    });
    @endif

    $('#role_table').DataTable();

    $(document).on('click', '.delete-icon', function(e) {
    e.preventDefault();
    
    var roleId = $(this).data('role-id');
    var usersCount = parseInt($(this).data('users-count')) || 0; // Convert to integer
    var fname = $(this).closest('tr').find('.fname').text();

    console.log("Users Count:", usersCount); // Debugging

    // Hide both sections before showing the correct one
    $('.show_users_count, .show_delete_user').hide();

    if (usersCount > 0) { 
        $('.show_users_count').show();
        $('.users_count').html(usersCount);
        $('.delete-btn').prop('disabled', true);
    } else {
        $('.show_delete_user').show();
        $('#append_name').html(fname);
        $('#deleteRoleFormId').attr('action', '/roles/' + roleId);
        $('.delete-btn').prop('disabled', false);
    }

    $('#deleteRoleForm').modal('show'); // Ensure this is the correct modal ID
});
});



</script>

@endsection