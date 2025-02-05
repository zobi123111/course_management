
@section('title', 'Documents')
@section('sub-title', 'Documents')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
  <i class="bi bi-check-circle me-1"></i>
  {{ session()->get('message') }}
</div>
@endif

@if(checkAllowedModule('courses','course.store')->isNotEmpty())
<div class="create_btn">
    <button class="btn btn-primary create-button" id="createDocument" data-toggle="modal"
    data-target="#createDocumentModal">Create Document</button>
</div>
@endif
<br>
<table class="table" id="documentTable">
  <thead>
    <tr>
      <th scope="col">Version Number</th>
      <th scope="col">Issue date</th>
      <th scope="col">Expiry Date</th>
      <th scope="col">Document</th>
      @if(checkAllowedModule('courses','course.edit')->isNotEmpty())
      <th scope="col">Edit</th>
      @endif
      @if(checkAllowedModule('courses','course.delete')->isNotEmpty())
      <th scope="col">Delete</th>
      @endif
    </tr>
  </thead>
  <tbody>
    @foreach($documents as $val)
            <tr>
                <td class="courseName">{{ $val->version_no}}</td>
                <td>{{ $val->issue_date}}</td>
                <td>{{ $val->expiry_date}}</td>
                @if(checkAllowedModule('courses','course.edit')->isNotEmpty())
                    <td><i class="fa fa-edit edit-course-icon" style="font-size:25px; cursor: pointer;" data-course-id="{{ encode_id($val->id) }}" ></i></td>
                @endif
                @if(checkAllowedModule('courses','course.delete')->isNotEmpty())
                    <td><i class="fa-solid fa-trash delete-icon" style="font-size:25px; cursor: pointer;" data-course-id="{{ encode_id($val->id) }}" ></i></td>
                @endif
            </tr> 
    @endforeach
  </tbody>
</table>

<!-- Create Courses-->
<div class="modal fade" id="createDocumentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Create Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="courses" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Version Number<span class="text-danger">*</span></label>
                        <input type="text" name="version_no" class="form-control">
                        <div id="course_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Issue Date<span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control">
                        <div id="issue_date_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Expiry Date<span class="text-danger">*</span></label>
                        <input type="date" name="expiry_date" class="form-control">
                        <div id="expiry_date_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Upload Document<span class="text-danger">*</span></label>
                        <input type="file" name="document" class="form-control">
                        <div id="status_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitDocument" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Courses-->

<!-- Edit Courses -->
<div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseModalLabel">Edit Courses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCourse" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Course Name<span class="text-danger">*</span></label>
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
<!--End Edit Courses-->

<!--Courses Delete  Modal -->
<form action="{{ url('course/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteCourse" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="course_id" id="courseId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Course "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary delete_course">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Courses Delete Model -->
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
    $('#courseTable').DataTable();

    $("#createCourse").on('click', function(){
        $(".error_e").html('');
        $("#courses")[0].reset();
        $("#createCourseModal").modal('show');
    })

    $("#submitCourse").on("click", function(e){
        e.preventDefault();
        $.ajax({
            url: '{{ url("/course/create") }}',
            type: 'POST',
            data: $("#courses").serialize(),
            success: function(response) {
                $('#createCourseModal').modal('hide');
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

    $('.edit-course-icon').click(function(e) {
        e.preventDefault();

        $('.error_e').html('');
        var courseId = $(this).data('course-id');
        $.ajax({
            url: "{{ url('/course/edit') }}", 
            type: 'GET',
            data: { id: courseId },
            success: function(response) {
                console.log(response);
                $('input[name="course_name"]').val(response.course.course_name);
                $('input[name="course_id"]').val(response.course.id);
                $('#edit_description').val(response.course.description);
                $('#edit_status').val(response.course.status);

                $('#editCourseModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#updateCourse').on('click', function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ url('course/update') }}",
            type: "POST",
            data: $("#editCourse").serialize(),
            success: function(response){
                $('#editCourseModal').modal('hide');
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
        $('#deleteCourse').modal('show');
        var courseId = $(this).data('course-id');
        var courseName = $(this).closest('tr').find('.courseName').text();
        $('#append_name').html(courseName);
        $('#courseId').val(courseId);
      
    });

});
</script>

@endsection