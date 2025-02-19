
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

@if(checkAllowedModule('courses','course.store')->isNotEmpty())
<div class="create_btn">
    <button class="btn btn-primary create-button" id="createCourse" data-toggle="modal"
    data-target="#createCourseModal">Create Course</button>
</div>
@endif
<br>
<table class="table" id="courseTable">
  <thead>
    <tr>
      <th scope="col">Course Name</th>
      <th scope="col">Description</th>
      <th scope="col">Image</th>
      <th scope="col">Status</th>
      @if(checkAllowedModule('courses','course.edit')->isNotEmpty())
      <th scope="col">Edit</th>
      @endif
      @if(checkAllowedModule('courses','course.delete')->isNotEmpty())
      <th scope="col">Delete</th>
      @endif
      @if(checkAllowedModule('courses','course.show')->isNotEmpty())
      <th scope="col">Lesson</th>
      @endif
    </tr>
  </thead>
  <tbody>
    @foreach($courses as $val)
            <tr>
                <td class="courseName">{{ $val->course_name}}</td>
                <td>{{ $val->description}}</td>
                <td>
                    @if($val->image)
                        <img src="{{ asset('storage/' . $val->image) }}" alt="Course Image" width="100px">
                    @else
                        <img src="{{ asset('/assets/img/profile-img.jpg') }}" alt="Course Image" width="100px">
                    @endif
                </td>               
                <td>{{ ($val->status==1)? 'Active': 'Inactive' }}</td>
                @if(checkAllowedModule('courses','course.edit')->isNotEmpty())
                    <td><i class="fa fa-edit edit-course-icon" style="font-size:25px; cursor: pointer;" data-course-id="{{ encode_id($val->id) }}" ></i></td>
                @endif
                @if(checkAllowedModule('courses','course.delete')->isNotEmpty())
                    <td><i class="fa-solid fa-trash delete-icon" style="font-size:25px; cursor: pointer;" data-course-id="{{ encode_id($val->id) }}" ></i></td>
                @endif  
                @if(checkAllowedModule('courses','course.show')->isNotEmpty())
                    <td><a href="{{ route('course.show', ['course_id' => encode_id($val->id)]) }}" class="btn btn-warning" id="viewCourse">View Course</a></td>
                @endif  
            </tr> 
    @endforeach
  </tbody>
</table>

<!-- Create Courses-->
<div class="modal fade" id="createCourseModal" tabindex="-1" role="dialog" aria-labelledby="courseModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courseModalLabel">Create Organizational Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="courses" method="POST" enctype="multipart/form-data" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Course Name<span class="text-danger">*</span></label>
                        <input type="text" name="course_name" class="form-control">
                        <div id="course_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description"  rows="3"></textarea>
                        <div id="description_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="image" class="form-label">Image<span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="image_error" class="text-danger error_e"></div>
                    </div>
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example">
                            <option value="">Select Org Unit</option>
                            @foreach($urganizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>            
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="groups" class="form-label">Select Groups<span class="text-danger"></span></label>
                        <select class="form-select groups-select" name="group_ids[]" multiple="multiple">
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_ids_error" class="text-danger error_e"></div>
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
                        <label for="lastname" class="form-label">Image<span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div id="image_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="edit_status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
                    </div>
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" id="edit_ou_id" aria-label="Default select example">
                            <option value="">Select Org Unit</option>
                            @foreach($urganizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>            
                    </div>
                    @endif

                    <div class="form-group">
                        <label for="groups" class="form-label">Select Groups<span class="text-danger"></span></label>
                        <select class="form-select groups-select" name="group_ids[]" multiple="multiple">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_ids_error_up" class="text-danger error_e"></div>
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

// function initializeSelect2() {
//     $('.groups-select').select2({
//         allowClear: true,
//         multiple: true,
//         dropdownParent: $('.modal:visible')
//     });
// }

// $(document).ready(function() {
//     $('#courseTable').DataTable();

//     initializeSelect2();

//     $("#createCourse").on('click', function(){
//         $(".error_e").html('');
//         $("#courses")[0].reset();
//         $(".groups-select").val(null).trigger("change");
//         $("#createCourseModal").modal('show');

//         $("#createCourseModal").modal('show');

//         $('#createCourseModal').on('shown.bs.modal', function () {
//             initializeSelect2();
//         });
//     })

//     $("#submitCourse").on("click", function(e){
//         e.preventDefault();
//         var formData = new FormData($('#courses')[0]);
        
//         $.ajax({
//             url: '{{ url("/course/create") }}',
//             type: 'POST',
//             // data: $("#courses").serialize(),
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function(response) {
//                 $('#createCourseModal').modal('hide');
//                 location.reload();
//             },
//             error: function(xhr, status, error){
//                 var errorMessage = JSON.parse(xhr.responseText);
//                 var validationErrors = errorMessage.errors;
//                 $.each(validationErrors, function(key,value){
//                     var msg = '<p>'+value+'<p>';
//                     $('#'+key+'_error').html(msg); 
//                 }) 
//             }
//         });

//     })

//     // $('.edit-course-icon').click(function(e) {
//     //     e.preventDefault();

//     //     $('.error_e').html('');
//     //     var courseId = $(this).data('course-id');
//     //     $.ajax({
//     //         url: "{{ url('/course/edit') }}", 
//     //         type: 'GET',
//     //         data: { id: courseId },
//     //         success: function(response) {
//     //             console.log(response);
//     //             $('input[name="course_name"]').val(response.course.course_name);
//     //             $('input[name="course_id"]').val(response.course.id);
//     //             $('#edit_description').val(response.course.description);
//     //             $('#edit_ou_id').val(response.course.ou_id);
//     //             $('#edit_status').val(response.course.status);

//     //             $('#editCourseModal').modal('show');
//     //         },
//     //         error: function(xhr, status, error) {
//     //             console.error(xhr.responseText);
//     //         }
//     //     });
//     // });

//     $('.edit-course-icon').click(function(e) {
//         e.preventDefault();

//         $('.error_e').html('');  // Clear previous errors
//         var courseId = $(this).data('course-id');  // Get course ID from button data attribute
        
//         // Make AJAX request to fetch course details
//         $.ajax({
//             url: "{{ url('/course/edit') }}",  // Make sure this URL is correct
//             type: 'GET',
//             data: { id: courseId },
//             success: function(response) {
//                 // Populate the modal fields with course data
//                 $('input[name="course_name"]').val(response.course.course_name);
//                 $('input[name="course_id"]').val(response.course.id);
//                 $('#edit_description').val(response.course.description);
//                 $('#edit_ou_id').val(response.course.ou_id);
//                 $('#edit_status').val(response.course.status);

//                 // Pre-select groups based on the course's existing groups
//                 var selectedGroups = response.course.groups.map(function(group) {
//                     return group.id;
//                 });

//                 // Dynamically set the selected groups in the dropdown
//                 $('.groups-select').val(selectedGroups).trigger('change');  // Select the values in the dropdown
                
//                 // Show the edit modal
//                 $('#editCourseModal').modal('show');
//             },
//             error: function(xhr, status, error) {
//                 console.error(xhr.responseText);  // Log errors to console if AJAX fails
//             }
//         });
//     });


//     $('#updateCourse').on('click', function(e){
//         e.preventDefault();
//         var formData = new FormData($('#editCourse')[0]);
//         $.ajax({
//             url: "{{ url('course/update') }}",
//             type: "POST",
//             data: formData,
//             processData: false,
//             contentType: false,
//             success: function(response){
//                 $('#editCourseModal').modal('hide');
//                 location.reload();
//             },
//             error: function(xhr, status, error){
//                 var errorMessage = JSON.parse(xhr.responseText);
//                 var validationErrors = errorMessage.errors;
//                 $.each(validationErrors, function(key,value){
//                     var msg = '<p>'+value+'<p>';
//                     $('#'+key+'_error_up').html(msg); 
//                 }) 
//             }
//         })
//     })

//     $('.delete-icon').click(function(e) {
//     e.preventDefault();
//         $('#deleteCourse').modal('show');
//         var courseId = $(this).data('course-id');
//         var courseName = $(this).closest('tr').find('.courseName').text();
//         $('#append_name').html(courseName);
//         $('#courseId').val(courseId);
      
//     });

// });



function initializeSelect2() {
    $('.groups-select').select2({
        allowClear: true,
        multiple: true,
        dropdownParent: $('.modal:visible'),
    });
}

$(document).ready(function() {
    $('#courseTable').DataTable();

    initializeSelect2();

    $("#createCourse").on('click', function() {
        $(".error_e").html('');
        $("#courses")[0].reset();
        $(".groups-select").val(null).trigger("change");
        $("#createCourseModal").modal('show');

        $('#createCourseModal').on('shown.bs.modal', function() {
            initializeSelect2();
        });
    });

    $("#submitCourse").on("click", function(e) {
        e.preventDefault();
        var formData = new FormData($('#courses')[0]);

        $.ajax({
            url: '{{ url("/course/create") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
                });
            }
        });
    });

    // Edit Course functionality
    // $('.edit-course-icon').click(function(e) {
    //     e.preventDefault();

    //     $('.error_e').html('');
    //     var courseId = $(this).data('course-id');
    //     $.ajax({
    //         url: "{{ url('/course/edit') }}",
    //         type: 'GET',
    //         data: { id: courseId },
    //         success: function(response) {
    //             $('input[name="course_name"]').val(response.course.course_name);
    //             $('input[name="course_id"]').val(response.course.id);
    //             $('#edit_description').val(response.course.description);
    //             $('#edit_ou_id').val(response.course.ou_id);
    //             $('#edit_status').val(response.course.status);

    //             // Pre-select groups based on the course's existing groups
    //             var selectedGroups = response.course.groups.map(function(group) {
    //                 return group.id;
    //             });

    //             // Dynamically set the selected groups in the dropdown
    //             $('.groups-select').val(selectedGroups).trigger('change');

    //             $('#editCourseModal').modal('show');
    //             $('#editCourseModal').on('shown.bs.modal', function () {
    //             initializeSelect2(); // Re-initialize select2 for groups select
    //         });
    //         },
    //         error: function(xhr, status, error) {
    //             console.error(xhr.responseText);
    //         }
    //     });
    // });


    $('.edit-course-icon').click(function(e) {
        e.preventDefault();

        $('.error_e').html('');
        var courseId = $(this).data('course-id');
        $.ajax({
            url: "{{ url('/course/edit') }}",
            type: 'GET',
            data: { id: courseId },
            success: function(response) {
                // Populate the modal fields with course data
                $('input[name="course_name"]').val(response.course.course_name);
                $('input[name="course_id"]').val(response.course.id);
                $('#edit_description').val(response.course.description);
                $('#edit_ou_id').val(response.course.ou_id);
                $('#edit_status').val(response.course.status);

                var selectedGroups = response.course.groups.map(function(group) {
                    return group.id;
                });

                $('.groups-select').val(selectedGroups).trigger('change');

                $('#editCourseModal').modal('show');

                $('#editCourseModal').on('shown.bs.modal', function () {
                    initializeSelect3();
                });
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    // Initialize select2
    function initializeSelect3() {
        $('.groups-select').select2({
            allowClear: true,
            multiple: true,
            dropdownParent: $('.modal:visible'),
            templateResult: function(state) {
                if (state.selected) {
                    return $(
                        // '<span style="display:none;">' + state.text + '</span>'
                    );
                }
                return state.text;
            }
        });
    }

    // Update Course functionality
    $('#updateCourse').on('click', function(e) {
        e.preventDefault();
        var formData = new FormData($('#editCourse')[0]);
        $.ajax({
            url: "{{ url('course/update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#editCourseModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error_up').html(msg);
                });
            }
        });
    });

    // Delete Course functionality
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