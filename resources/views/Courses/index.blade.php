@section('title', 'Courses')
@section('sub-title', 'Courses')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif
<div class="main_cont_outer">
    <div class="create_btn">
        <button class="btn btn-primary create-button" id="createCourse" data-toggle="modal"
            data-target="#createCourseModal">Create Course</button>
    </div>
    <br>
    <table class="table" id="orgUnitTable">
        <thead>
            <tr>
                <th scope="col">Org Unit Name</th>
                <th scope="col">Description</th>
                <th scope="col">Status</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $val)
            <tr>
                <td class="orgUnitName">{{ $val->course_name}}</td>
                <td>{{ $val->description}}</td>
                <td>{{ ($val->status==1)? 'Active': 'Inactive' }}</td>
                <td><i class="fa fa-edit edit-course-icon" style="font-size:25px; cursor: pointer;"
                        data-orgunit-id="{{ $val->id }}"></i></td>
                <td><i class="fa-solid fa-trash delete-icon" style="font-size:25px; cursor: pointer;"
                        data-orgunit-id="{{ $val->id }}"></i></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!-- Create Courses-->
<div class="modal fade" id="createCourseModal" tabindex="-1" role="dialog" aria-labelledby="courseModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel">Create Organizational Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="courses" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Course Name<span class="text-danger">*</span></label>
                        <input type="text" name="course_name" class="form-control">
                        <div id="course_name_error" class="text-danger error_e"></div>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitCourse" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Courses-->

<!-- Edit Organizational  Unit-->
<div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Courses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editOrgUnit" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Org Unit Name<span
                                class="text-danger">*</span></label>
                        <input type="text" name="course_name" class="form-control">
                        <input type="hidden" name="course_id" class="form-control">
                        <div id="course_name_error_up" class="text-danger error_e"></div>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateCourse" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Organizational  Unit-->
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
    $('#user_table').DataTable();

    $("#createCourse").on('click', function() {
        $(".error_e").html('');
        $("#courses")[0].reset();
        $("#createCourseModal").modal('show');
    })

    $("#submitCourse").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/course/create") }}',
            type: 'POST',
            data: $("#courses").serialize(),
            success: function(response) {
                $('#createCourseModal').modal('hide');
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

    $('.edit-course-icon').click(function(e) {
        e.preventDefault();

        $('.error_e').html('');
        var courseId = $(this).data('course-id');
        $.ajax({
            url: "{{ url('/orgunit/edit') }}",
            type: 'GET',
            data: {
                id: courseId
            },
            success: function(response) {
                console.log(response);
                // $('input[name="org_unit_name"]').val(response.organizationUnit.org_unit_name);
                // $('input[name="org_unit_id"]').val(response.organizationUnit.id);
                // $('#edit_description').val(response.organizationUnit.description);
                // $('#edit_status').val(response.organizationUnit.status);
                // $('input[name="edit_firstname"]').val(response.user.fname);
                // $('input[name="edit_lastname"]').val(response.user.lname);
                // $('input[name="edit_email"]').val(response.user.email);
                // $('input[name="user_id"]').val(response.user.id);


                $('#editCourseModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

});
</script>

@endsection