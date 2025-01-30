@section('title', 'Organizational Unit')
@section('sub-title', 'Organizational Unit')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
@endif
<div class="create_btn ">
    <button class="btn btn-primary create-button" id="createOrgUnit" data-toggle="modal"
    data-target="#orgUnitModal">Create Organizational Unit</button>
</div>
<br>
<div id="update_success_msg"></div>
<table class="table" id="orgUnitTable">
  <thead>
    <tr>
      <th scope="col">Org Unit Name</th>
      <th scope="col">Description</th>
      <th scope="col">Status</th>
      <th scope="col">First Name</th>
      <th scope="col">Last Name</th>
      <th scope="col">Email</th>
      <th scope="col">Edit</th>
      <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    @foreach($organizationUnitsData as $val)
        <tr>
        <td class="orgUnitName">{{ $val->org_unit_name}}</td>
        <td>{{ $val->description}}</td>
        <td>{{ ($val->status==1)? 'Active': 'Inactive' }}</td>
        <td>{{ $val->fname}}</td>
        <td>{{ $val->lname}}</td>
        <td>{{ $val->email}}</td>
        <td><i class="fa fa-edit edit-orgunit-icon" style="font-size:25px; cursor: pointer;" data-orgunit-id="{{ $val->id }}" data-user-id="{{ $val->user_id }}"></i></td>
        <td><i class="fa-solid fa-trash delete-icon" style="font-size:25px; cursor: pointer;" data-orgunit-id="{{ $val->id }}" data-user-id="{{ $val->user_id }}"></i></td>
        </tr>
    @endforeach
  </tbody>
</table>


<!-- Create Organizational  Unit-->
<div class="modal fade" id="orgUnitModal" tabindex="-1" role="dialog" aria-labelledby="orgUnitModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                        <label for="firstname" class="form-label">Org Unit Name<span class="text-danger">*</span></label>
                        <input type="text" name="org_unit_name" class="form-control">
                        <div id="org_unit_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description"  rows="3"></textarea>
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
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="submitOrgUnit" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Organizational  Unit-->

<!-- Edit Organizational  Unit-->
<div class="modal fade" id="editOrgUnitModal" tabindex="-1" role="dialog" aria-labelledby="editOrgUnitModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                        <label for="firstname" class="form-label">Org Unit Name<span class="text-danger">*</span></label>
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
                        <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
                        <input type="text" name="edit_firstname" class="form-control">
                        <input type="hidden" name="user_id" id="user_id" class="form-control">

                        <div id="edit_firstname_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                        <input type="text" name="edit_lastname" class="form-control">
                        <div id="edit_lastname_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" name="edit_email" class="form-control">
                        <div id="edit_email_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="updateOrgUnit" class="btn btn-primary sbt_btn">Update</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Organizational  Unit-->

<!--Organizational Unit Delete  Modal -->
<form action="{{ url('/org-unit-delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteOrgUnitModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="id" id="orgUnitId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Organizational Unit "<strong><span id="append_name"> </span></strong>" ?
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
$(document).ready(function(){

    $("#orgUnitTable").DataTable();

    $("#createOrgUnit").on('click', function(){
        $(".error_e").html('');
        $("#orgUnit")[0].reset();
        $("#orgUnitModal").modal('show');
    })

    $("#submitOrgUnit").on("click", function(e){
        e.preventDefault();
        $.ajax({
            url: '{{ url("/orgunit/save") }}',
            type: 'POST',
            data: $("#orgUnit").serialize(),
            success: function(response) {
                $('#orgUnitModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error){
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key,value){
                    var msg = '<p>'+value+'<p>';
                    $('#'+key+'_error').html(msg); 
                }) 
            }
        });

    })

    $('.edit-orgunit-icon').click(function(e) {
        e.preventDefault();

        $('.error_e').html('');
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
                console.log(response);
                $('input[name="org_unit_name"]').val(response.organizationUnit.org_unit_name);
                $('input[name="org_unit_id"]').val(response.organizationUnit.id);
                $('#edit_description').val(response.organizationUnit.description);
                $('#edit_status').val(response.organizationUnit.status);
                $('input[name="edit_firstname"]').val(response.user.fname);
                $('input[name="edit_lastname"]').val(response.user.lname);
                $('input[name="edit_email"]').val(response.user.email);
                $('input[name="user_id"]').val(response.user.id);


                $('#editOrgUnitModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#updateOrgUnit').on('click', function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ url('orgunit/update') }}",
            type: "POST",
            data: $("#editOrgUnit").serialize(),
            success: function(response){
                $('#editOrgUnitModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error){
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key,value){
                    var msg = '<p>'+value+'<p>';
                    $('#'+key+'_error_up').html(msg); 
                }) 
            }
        })
    })

    $('.delete-icon').click(function(e) {
    e.preventDefault();
        $('#deleteOrgUnitModal').modal('show');
        var orgUnitId = $(this).data('orgunit-id');
        var orgUnitName = $(this).closest('tr').find('.orgUnitName').text();
        $('#append_name').html(orgUnitName);
        $('#orgUnitId').val(orgUnitId);
      
    });



});
</script>

@endsection