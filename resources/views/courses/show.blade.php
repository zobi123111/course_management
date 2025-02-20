
@section('sub-title', 'Course')
@extends('layout.app')
@section('content')

<style>
    .course-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }

    .card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .card-body {
        flex-grow: 1; /* Ensures the description area expands to fill remaining space */
        min-height: 200px; /* Set the minimum height to ensure consistent card size */
        display: flex;
        flex-direction: column;
        justify-content: flex-start; /* Ensures content is aligned at the top */
    }

    .card-footer {
        display: flex;
        justify-content: space-between; /* Align buttons in a row with space in between */
        padding: 10px;
        background-color: #f8f9fa;
    }

    .card-footer .btn {
        /* flex: 1; */
        /* margin: 0 5px; */
    }

    .card-text {
        flex-grow: 1; /* Ensures the description takes available space */
    }


    .active-link a {
       color: #0d6efd !important; /* Ensures the description takes available space */
    }


</style>
<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach($breadcrumbs as $breadcrumb)
            @if($breadcrumb['url']) 
                <li class="breadcrumb-item active-link"  ><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['title'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
<!-- End Breadcrumb -->

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
  <i class="bi bi-check-circle me-1"></i>
  {{ session()->get('message') }}
</div>
@endif

<!-- Card with an image on left -->
<div class="card mb-3">
    <div class="row g-0">
        <div class="col-md-4">
            @if($course->image)
                <img src="{{ asset('storage/' . $course->image) }}" class="img-fluid rounded-start" alt="Course Image">
            @else
                <img src="{{  url('assets/img/card.jpg')  }}" class="img-fluid rounded-start" alt="...">
            @endif
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title">{{  $course->course_name  }}</h5>
                <p class="card-text">{{ $course->description }}</p>
                @if(checkAllowedModule('courses', 'lesson.store')->isNotEmpty())
                <p class="card-text"><button class="btn btn-success" id="createLesson" data-toggle="modal"
                    data-target="#createLessonModal">Create Lesson</button></p>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- End Card with an image on left -->

 <!-- List group with Advanced Contents -->
<div class="list-group">
    {{-- @foreach($course->courseLessons as $val)
        <div class="list-group-item " aria-current="true">
            <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1 lessontitle"> {{ $val->lesson_title }}</h5>
                <span>
            
                @if(checkAllowedModule('courses', 'lesson.show')->isNotEmpty())
                    <i class="fa fa-eye show-lesson-icon" style="font-size:18px; cursor: pointer; margin-right: 5px;" data-lesson-id="{{ encode_id($val->id) }}"></i>
                @endif
                @if(checkAllowedModule('courses', 'lesson.edit')->isNotEmpty())
                    <i class="fa fa-edit edit-lesson-icon" style="font-size:18px; cursor: pointer; margin-right: 5px;" data-lesson-id="{{ encode_id($val->id) }}"></i>
                @endif
                @if(checkAllowedModule('courses', 'lesson.delete')->isNotEmpty())
                    <i class="fa-solid fa-trash delete-lesson-icon" style="font-size:18px; cursor: pointer;"
                    data-lesson-id="{{ encode_id($val->id) }}"></i>
                @endif
                </span>

            </div>
            <p class="mb-1">{{ $val->description }}</p>
        </div>
    @endforeach --}}

    <div class="container-fluid">
        <div class="row">
            @foreach($course->courseLessons as $val)
                <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                    <div class="card course-card">
                        <div class="course-image-container" style="position: relative;">
                            <span class="status-label" style="position: absolute; top: 10px; right: 10px; background-color: {{ $val->status == 1 ? 'green' : 'red' }}; color: white; padding: 5px 10px; border-radius: 5px;">
                                {{ ($val->status == 1) ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
    
                        <div class="card-body">
                            <h5 class="card-title">{{ $val->lesson_title}}</h5>
    
                            <p class="card-text">
                                {{ \Illuminate\Support\Str::words($val->description, 50, '...') }}
                            </p>
                        </div>
    
                        <div class="card-footer d-flex justify-content-between">

                            @if(checkAllowedModule('courses', 'lesson.show')->isNotEmpty())
                                <a href="javascript:void(0)" class="btn btn-light show-lesson-icon" data-lesson-id="{{ encode_id($val->id) }}">
                                    <i class="fa fa-edit"></i> Show
                                </a>
                            @endif

                            @if(checkAllowedModule('courses', 'lesson.edit')->isNotEmpty())
                                <a href="javascript:void(0)" class="btn btn-light edit-lesson-icon" data-lesson-id="{{ encode_id($val->id) }}">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                            @endif

                            @if(checkAllowedModule('courses', 'lesson.delete')->isNotEmpty())
                                <a href="javascript:void(0)" class="btn btn-light delete-lesson-icon" data-lesson-id="{{ encode_id($val->id) }}">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </a>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div><!-- End List group Advanced Content -->


<!-- Create Lesson-->
<div class="modal fade" id="createLessonModal" tabindex="-1" role="dialog" aria-labelledby="lessonModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lessonModalLabel">Create Lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="lesson" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Lesson Title<span class="text-danger">*</span></label>
                        <input type="text" name="lesson_title" class="form-control">
                        <input type="hidden" name="course_id" class="form-control" value="{{ $course->id }}">
                        <div id="lesson_title_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description"  rows="3"></textarea>
                        <div id="description_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="comment_required" class="form-label">
                            Require Comment
                        </label>
                        <input type="checkbox" id="comment_required" name="comment_required">
                    </div>
                    <div class="form-group" id="comment_container" style="display: none;">
                        <label for="comment" class="form-label">Comment<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="comment" rows="3"></textarea>
                        <div id="comment_error" class="text-danger error_e"></div>
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
                        <label for="email" class="form-label">Task Based Grading
                            {{-- <span class="text-danger">*</span> --}}
                        </label>
                        <select class="form-select" name="task_grading" aria-label="Default select example">
                            <option value="" >Select Grading</option>
                            <option value="N/A" >N/A</option>
                            <option value="Passed">Passed</option>
                            <option value="Further Training Required">Further Training Required</option>
                            <option value="Defer">Defer</option>
                        </select>
                        <div id="status_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Competency Based Grading
                            {{-- <span class="text-danger">*</span> --}}
                        </label>
                        <select class="form-select" name="comp_grading" aria-label="Default select example">
                            <option value="" >Select Grading</option>
                            <option value="1" >1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        <div id="status_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitLesson" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Lesson-->

<!-- Edit Lesson -->
<div class="modal fade" id="editLessonModal" tabindex="-1" role="dialog" aria-labelledby="editLessonModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLessonModalLabel">Edit Lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLesson" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="firstname" class="form-label">Lesson Title<span class="text-danger">*</span></label>
                        <input type="text" name="edit_lesson_title" class="form-control">
                        <input type="hidden" name="lesson_id" class="form-control">
                        <div id="lesson_title_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="edit_description" id="edit_description" rows="3"></textarea>
                        <div id="description_error_up" class="text-danger error_e"></div>
                    </div>     
                    <div class="form-group">
                        <label for="comment_required" class="form-label">
                            Require Comment
                        </label>
                        <input type="checkbox" id="edit_comment_required" name="edit_comment_required">
                    </div>                             
                    <div class="form-group">
                        <label for="comment" class="form-label">Comment<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="edit_comment" id="edit_comment" rows="3"></textarea>
                        <div id="edit_comment_error_up" class="text-danger error_e"></div>
                    </div>                    
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="edit_status" id="edit_status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateLesson" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End Edit Lesson-->

<!--Lesson Delete  Modal -->
<form action="{{ url('lesson/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteLesson" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Lesson</h5>
                    <input type="hidden" name="lesson_id" id="lessonId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Lesson "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary delete_lesson">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Lesson Delete Model -->

@endsection

@section('js_scripts')

<script>
$(document).ready(function() {

    // $("#comment_required").on('change', function() {
    //     var commentContainer = $("#comment_container");
    //     if ($(this).is(":checked")) {
    //         commentContainer.show();
    //     } else {
    //         commentContainer.hide();
    //     }
    // })

    $("#comment_required").on('change', function() {
        var commentContainer = $("#comment_container");
        var commentField = $("textarea[name='comment']");
        if ($(this).is(":checked")) {
            commentContainer.show();
            commentField.prop("required", true);
        } else {
            commentContainer.hide();
            commentField.prop("required", false);
        }
    });

    $("#createLesson").on('click', function(){
        $(".error_e").html('');
        $("#lesson")[0].reset();
        $("#createLessonModal").modal('show');
    })

    $("#submitLesson").on("click", function(e){
        e.preventDefault();
        $.ajax({
            url: '{{ url("/lesson/create") }}',
            type: 'POST',
            data: $("#lesson").serialize(),
            success: function(response) {
                $('#createLessonModal').modal('hide');
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

    $('.edit-lesson-icon').click(function(e) {
        e.preventDefault();

        $('.error_e').html('');
        var lessonId = $(this).data('lesson-id');
        $.ajax({
            url: "{{ url('/lesson/edit') }}", 
            type: 'GET',
            data: { id: lessonId },
            success: function(response) {
                console.log(response);
                $('input[name="edit_lesson_title"]').val(response.lesson.lesson_title);
                $('input[name="lesson_id"]').val(response.lesson.id);
                $('#edit_description').val(response.lesson.description);
                $('#edit_status').val(response.lesson.status);
                
                if (response.lesson.comment) {
                    $('#edit_comment').val(response.lesson.comment);
                    $('#edit_comment').closest('.form-group').show();
                } else {
                    $('#edit_comment').val('');
                    $('#edit_comment').closest('.form-group').hide();
                }

                if (response.lesson.comment) {
                    $('#edit_comment_required').prop('checked', true);
                    $('#edit_comment').prop('required', true);
                } else {
                    $('#edit_comment_required').prop('checked', false);
                    $('#edit_comment').prop('required', false);
                }

                $('#editLessonModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#edit_comment_required').change(function() {
        if ($(this).prop('checked')) {
            $('#edit_comment').prop('required', true);
            $('#edit_comment').closest('.form-group').show();
        } else {
            $('#edit_comment').prop('required', false);
            $('#edit_comment').closest('.form-group').hide();
            $('#edit_comment').val(null);
        }
    });

    $('#updateLesson').on('click', function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ url('lesson/update') }}",
            type: "POST",
            data: $("#editLesson").serialize(),
            success: function(response){
                $('#editlessonModal').modal('hide');
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

    // $('.delete-lesson-icon').click(function(e) {
    //     e.preventDefault();
    //     $('#deleteLesson').modal('show');
    //     var lessonId = $(this).data('lesson-id');
    //     var courseName = $(this).closest('tr').find('.courseName').text();
    //     $('#append_name').html(courseName);
    //     $('#lessonId').val(lessonId);
      
    // });
    $('.delete-lesson-icon').click(function(e) {
        e.preventDefault();
        $('#deleteLesson').modal('show');
        var lessonId = $(this).data('lesson-id');
        
        var lessonTitle = $(this).closest('.list-group-item').find('.lessontitle').text().trim();
        
        console.log("Lesson Title: " + lessonTitle);
        
        if (!lessonTitle) {
            lessonTitle = "Unknown Lesson";
        }

        $('#append_name').html(lessonTitle);
        
        $('#lessonId').val(lessonId);
    });



    $('.show-lesson-icon').click(function(e) {
        e.preventDefault();
        
        var lessonId = $(this).data('lesson-id');
        window.location.href = "{{ url('lesson') }}/" + lessonId;
    });


});
</script>

@endsection