@section('title', 'Organizational Unit')
@section('sub-title', 'Organizational Unit')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="alertMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif
@if(session()->has('error'))
<div id="alertMessage" class="alert alert-danger fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif
<div class="main_cont_outer" >
    <div class="create_btn " >
        <button class="btn btn-primary create-button" id="createOrgUnit" data-toggle="modal"
            data-target="#orgUnitModal">Create Organizational Unit</button>
    </div>
    <br>
    <div id="update_success_msg"></div>
    <div class="card pt-4">
        <div class="card-body">
    <table class="table table-hover" id="orgUnitTable">
        <thead>
            <tr>
                <th scope="col">Org Unit Name</th>
                <th scope="col">Description</th>
                <th scope="col">Status</th>
                <!-- <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th> -->
                <th scope="col">Users Count</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
            </tr>
        </thead>
        {{-- <tbody> 
            @foreach($organizationUnitsData as $val)
            <tr>
                <td class="orgUnitName">{{ $val->org_unit_name}}</td>
                <td>{{ $val->description}}</td>
                <td>{{ ($val->status==1)? 'Active': 'Inactive' }}</td>
                <!-- <td>{{ $val->fname}}</td>
                <td>{{ $val->lname}}</td>
                <td>{{ $val->email}}</td> -->
                <td> <a href="#" class="get_org_users" data-ou-id="{{ encode_id($val->id) }}"> {{ $val->users_count }} </a></td>
                <td><i class="fa fa-edit edit-orgunit-icon" 
                        data-orgunit-id="{{ encode_id($val->id) }}"
                        data-user-id="{{ encode_id(optional($val->roleOneUsers)->id) }}">
                    </i>
                </td>
                <td><i class="fa-solid fa-trash delete-icon" 
                        data-orgunit-id="{{ encode_id($val->id) }}"
                        data-user-id="{{ encode_id(optional($val->roleOneUsers)->id) }}">
                    </i>
                </td>
            </tr>
            @endforeach
        </tbody> --}}
        <tbody></tbody>
    </table>
</div>
</div>
</div>

<!-- OU Users List Modal -->
<div class="modal fade" id="orgUnitUsersModal" tabindex="-1" role="dialog" aria-labelledby="orgUnitUsersModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orgUnitUsersModalLabel">OU Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <table class="table" id="orgUnitUsersTable">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                    </tr>
                </thead>
                <tbody id="tblBody">                    
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<!--End of OU Users List Modal-->

<!-- Create Organizational  Unit-->
<div class="modal fade" id="orgUnitModal" tabindex="-1" role="dialog" aria-labelledby="orgUnitModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" enctype="multipart/form-data">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orgUnitModalLabel">Create Organizational Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="orgUnit" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Org Unit Name<span
                                class="text-danger">*</span></label>
                        <input type="text" name="org_unit_name" class="form-control">
                        <div id="org_unit_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                        <div id="description_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="form-label">First Name<span class="text-danger"></span></label>
                        <input type="text" name="firstname" class="form-control">
                        <div id="firstname_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name<span class="text-danger"></span></label>
                        <input type="text" name="lastname" class="form-control">
                        <div id="lastname_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email<span class="text-danger"></span></label>
                        <input type="email" name="email" class="form-control">
                        <div id="email_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="organization_logo" class="form-control" accept="image/*">
                            <div id="organization_logo_error" class="text-danger error_e"></div>           
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-label">Password<span class="text-danger"></span></label>
                        <input type="password" name="password" class="form-control">
                        <div id="password_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmpassword" class="form-label">Confirm Password<span
                                class="text-danger"></span></label>
                        <input type="password" name="password_confirmation" class="form-control" id="confirmpassword">
                        <div id="password_confirmation_error" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer"> 
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="submitOrgUnit" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Organizational  Unit-->

<!-- Edit Organizational  Unit-->
<div class="modal fade" id="editOrgUnitModal" tabindex="-1" role="dialog" aria-labelledby="editOrgUnitModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" enctype="multipart/form-data">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrgUnitModalLabel">Edit Organizational Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editOrgUnit" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Org Unit Name<span
                                class="text-danger">*</span></label>
                        <input type="text" name="org_unit_name" class="form-control">
                        <input type="hidden" name="org_unit_id" class="form-control">
                        <div id="org_unit_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        <div id="description_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="edit_status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="form-label">First Name<span class="text-danger"></span></label>
                        <input type="text" name="edit_firstname" class="form-control">
                        <input type="hidden" name="user_id" id="user_id" class="form-control">
                        <div id="edit_firstname_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name<span class="text-danger"></span></label>
                        <input type="text" name="edit_lastname" class="form-control">
                        <div id="edit_lastname_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email<span class="text-danger"></span></label>
                        <input type="email" name="edit_email" class="form-control">
                        <div id="edit_email_error_up" class="text-danger error_e"></div>
                    </div>
                    <!-- <div class="form-group">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="org_logo" class="form-control" accept="image/*">
                            <div id="org_logo_error_up" class="text-danger error_e"></div>  
                            <img id="org_logo_preview" src="" alt="Organization Logo" style="max-width: 200px; display: none; margin-top: 10px;">    
                    </div> -->
                    <div class="form-group">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" name="org_logo" id="org_logo" class="form-control" accept="image/*">
                        <small id="org_logo_filename" class="text-muted"></small> <!-- Show file name here -->
                        <div id="org_logo_error_up" class="text-danger error_e"></div>  
                        <img id="org_logo_preview" src="" alt="Organization Logo" style="max-width: 200px; display: none; margin-top: 10px;">    
                    </div>

                    <input type="hidden" name="existing_org_logo" id="existing_org_logo"> <!-- Hidden input to store existing filename -->

                    <div class="create_org_admin" style="display: none;">
                        <div class="form-group">
                            <label for="password" class="form-label">Password<span class="text-danger"></span></label>
                            <input type="password" name="password" class="form-control">
                            <div id="password_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label for="confirmpassword" class="form-label">Confirm Password<span
                                    class="text-danger"></span></label>
                            <input type="password" name="password_confirmation" class="form-control" id="confirmpassword">
                            <div id="password_confirmation_error_up" class="text-danger error_e"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="updateOrgUnit" class="btn btn-primary sbt_btn">Update</a>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Organizational  Unit-->

<!--Organizational Unit Delete  Modal -->
<form action="{{ url('/orgunit/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteOrgUnitModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="org_id" id="orgUnitId" value="">
                    <input type="hidden" name="user_id" id="userId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Organizational Unit "<strong><span id="append_name">
                        </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary org_unit_delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End of Organizational Unit Delete Model -->

@endsection

@section('js_scripts')
<script>
$(document).ready(function() {


    $('#orgUnitTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('orgunit.data') }}",
            type: "GET",
        },
        columns: [
            { data: 'org_unit_name', name: 'org_unit_name', class: 'orgUnitName' },
            { data: 'description', name: 'description' },
            { data: 'status', name: 'status' },
            { data: 'users_count', name: 'users_count' },
            { data: 'edit', name: 'edit', orderable: false, searchable: false },
            { data: 'delete', name: 'delete', orderable: false, searchable: false },
        ]
    });

    $("#createOrgUnit").on('click', function() {
        $(".error_e").html('');
        $("#orgUnit")[0].reset();
        $("#orgUnitModal").modal('show');
    })

    $("#submitOrgUnit").on("click", function(e) {
        e.preventDefault();
        $(".loader").fadeIn();
        var formData = new FormData($('#orgUnit')[0]);
        $.ajax({
            url: '{{ url("/orgunit/save") }}', 
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) { 
                $(".loader").fadeOut("slow");
                $('#orgUnitModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                $(".loader").fadeOut("slow");
                var errorMessage = JSON.parse(xhr.responseText); 
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    console.log(key);
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error').html(msg);
                })
            }
        });

    })
    $(document).on('click', '.edit-orgunit-icon', function() {
        $('.error_e').html('');
        $("#editOrgUnit")[0].reset();
        var orgUnitId = $(this).data('orgunit-id');
        var userId = $(this).data('user-id');
        $.ajax({
            url: "{{ url('/orgunit/edit') }}",
            type: 'GET',
            data: {
                orgId: orgUnitId,
                userId: userId
            },
            success: function(response) {
                if (response.organizationUnit) {
                    $('input[name="org_unit_name"]').val(response.organizationUnit.org_unit_name || '');
                    $('input[name="org_unit_id"]').val(response.organizationUnit.id || '');
                    $('#edit_description').val(response.organizationUnit.description || '');
                    $('#edit_status').val(response.organizationUnit.status || '').trigger('change'); // Useful for select fields
                    $('#edit_org_logo').val(response.organizationUnit.org_logo || '');
                    if (response.organizationUnit.org_logo) {
                        let fileName = response.organizationUnit.org_logo;
                        let imagePath = '/storage/organization_logo/' + fileName; // Adjust the path as per your storage setup 
                        $('#org_logo_preview').attr('src', imagePath).show();
                        $('#org_logo_filename').text('Current File: ' + fileName);
                        $('#existing_org_logo').val(fileName);
                    }

                    // Show selected file name when a new file is chosen
                    $('#org_logo').on('change', function() {
                        let file = this.files[0];
                        if (file) {
                            $('#org_logo_filename').text('Selected File: ' + file.name);
                        }
                    });

                }
                if (response.user) {
                    $('input[name="edit_firstname"]').val(response.user.fname || '');
                    $('input[name="edit_lastname"]').val(response.user.lname || '');
                    $('input[name="edit_email"]').val(response.user.email || '');
                    $('input[name="user_id"]').val(response.user.id || '');
                    $(".create_org_admin").hide();
                } else {
                    $(".create_org_admin").show();
                }
                $('#editOrgUnitModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#orgUnitTable').on('click', '.get_org_users', function() {
    var ou_id = $(this).data('ou-id');

        $.ajax({               
            url: "{{ url('/orgunit/user_list') }}",
            type: 'GET',
            data: { ou_id: ou_id },
            success: function(response) {
                console.log(response);

                // Check if users exist
                if (!response.orgUnitUsers || response.orgUnitUsers.length === 0) {
                    alert('No users found for this Organizational Unit.');
                    return;
                }

                // Clear previous data
                $('#orgUnitUsersTable tbody').html('');

                // Append new data
                response.orgUnitUsers.forEach(user => {
                    var imageUrl = user.image 
                        ? "{{ asset('storage') }}/" + user.image 
                        : "{{ asset('assets/img/no_image.png') }}"; // Default image if none provided
                    
                    var row = `
                        <tr>
                            <td><img src="${imageUrl}" alt="Profile Image" width="40" height="40" class="rounded-circle"></td>
                            <td>${user.fname} ${user.lname}</td>
                            <td>${user.email}</td>
                        </tr>`;
                    $('#orgUnitUsersTable tbody').append(row);
                });

                // Show modal
                $('#orgUnitUsersModal').modal('show');
            },
            error: function(xhr, status, error) {
                try {
                    var response = JSON.parse(xhr.responseText); // Parse responseText to JSON
                    alert(response.error || 'An unknown error occurred.');
                } catch (e) {
                    alert('Failed to fetch users. Please try again.');
                }
            }
        });
    });

    $('#updateOrgUnit').on('click', function(e) { 
        e.preventDefault();
        $(".loader").fadeIn();
        var formData = new FormData($('#editOrgUnit')[0]);
        $.ajax({
            url: "{{ url('orgunit/update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#editOrgUnitModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                $(".loader").fadeOut('slow');
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error_up').html(msg);
                })
            }
        })
    })

    $(document).on('click', '.delete-icon', function(e) {
        e.preventDefault();
        $('#deleteOrgUnitModal').modal('show');
        var orgUnitId = $(this).data('orgunit-id');
        var userId = $(this).data('user-id');
        var orgUnitName = $(this).closest('tr').find('.orgUnitName').text();
        $('#append_name').html(orgUnitName);
        $('#orgUnitId').val(orgUnitId);
        $('#userId').val(userId);
    });

    setTimeout(function() {
        $('#alertMessage').fadeOut('slow');
    }, 2000);



});
</script>

@endsection