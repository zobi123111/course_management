@section('sub-title', 'Lesson')
@extends('layout.app')
@section('content')

<style>
    .active-link a {
       color: #0d6efd !important; /* Ensures the description takes available space */
    }
</style>


<style>
    .course-image {
        height: 200px;
        object-fit: cover;
        width: 100%;
    }

    .sublesson_card {
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .sublesson_card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 16px rgba(0, 0, 0, 0.2);
    }

    .card-body {
        flex-grow: 1;
        /* min-height: 200px; */
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        background-color: #f8f9fa;
    }

    .card-text {
        flex-grow: 1;
    }

    .course-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

    /* Button hover effect */
    .card-footer .btn {
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .card-footer .btn:hover {
        background-color: #e2e6ea;
        transform: translateY(-2px);
    }

    .status-label {
        position: absolute;
        top: 10px;
        right: 10px;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.9em;
    }
</style>
<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      @foreach($breadcrumbs as $breadcrumb)
        @if($breadcrumb['url']) 
          <li class="breadcrumb-item active-link"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
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
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title">{{  $lesson->lesson_title  }}</h5>
                <p class="card-text">{{ $lesson->description }}</p>
                @if(checkAllowedModule('courses', 'sublesson.store')->isNotEmpty())
                    <p class="card-text">
                        <button class="btn btn-success" id="createSubLessonBtn" data-bs-toggle="modal"
                        data-bs-target="#createSubLessonModal">Create Task</button>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- End Card with an image on left -->


 <!-- List group with Advanced Contents -->
 <div class="card pt-4">
    <div class="card-body">
        <div class="list-group">
            <div class="container-fluid">
                <h3>Tasks</h3>
                <div class="row">
                    @foreach($lesson->subLessons as $val)
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                            <div class="sublesson_card course-card">
            
                                <div class="course-image-container" style="position: relative;">
                                    <span class="status-label" style="position: absolute; top: 10px; right: 10px; background-color: {{ $val->status == 1 ? 'green' : 'red' }}; color: white; padding: 5px 10px; border-radius: 5px;">
                                        {{ ($val->status == 1) ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
            
                                <div class="card-body">
                                    <h5 class="card-title SublessonName" data-sublesson-title="{{ $val->title }}">{{ $val->title}}</h5>
            
                                    <p class="card-text">
                                        {{ \Illuminate\Support\Str::words($val->description, 50, '...') }}
                                    </p>
                                </div>
            
                                <div class="card-footer d-flex justify-content-between">
            
                                    {{-- @if(checkAllowedModule('courses', 'lesson.show')->isNotEmpty())
                                        <a href="javascript:void(0)" class="btn btn-light show-lesson-icon" data-lesson-id="{{ encode_id($val->id) }}">
                                            <i class="fa fa-edit"></i> Show
                                        </a>
                                    @endif --}}
            
                                    @if(checkAllowedModule('courses', 'sublesson.edit')->isNotEmpty())
                                        <a href="javascript:void(0)" class="btn btn-light edit-lesson-icon" data-lesson-id="{{ encode_id($val->id) }}">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    @endif
            
                                    @if(checkAllowedModule('courses', 'sublesson.delete')->isNotEmpty())
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
        </div>
    </div>
</div>
@if ($lesson->prerequisites->count() > 0)
    <div class="card pt-4">
        <div class="card-body">
            <h3>Prerequisites</h3>
            <form action="{{ route('lesson.prerequisites.store', ['course' => $lesson->course_id, 'lesson' => $lesson->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="list-group">
                    <div class="container-fluid">
                        <div class="row">
                            @foreach ($lesson->prerequisites as $index => $prerequisite)
                                @php
                                    // Get saved prerequisite for the logged-in user
                                    $savedPrerequisite = $lesson->prerequisiteDetails()
                                        ->where('created_by', auth()->id())
                                        ->where('prerequisite_type', $prerequisite->prerequisite_type)
                                        ->first();
                                @endphp

                                <div class="col-md-6 mb-3">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h5 class="card-title">Prerequisite {{ $index + 1 }}</h5>

                                            <label for="prerequisite_{{ $index }}">
                                                <strong>{{ $prerequisite->prerequisite_detail }}</strong>
                                            </label>

                                            @if ($prerequisite->prerequisite_type == 'number')
                                                <input type="number" 
                                                       class="form-control" 
                                                       name="prerequisite_details[{{ $index }}]" 
                                                       value="{{ old('prerequisite_details.' . $index, $savedPrerequisite->prerequisite_detail ?? '') }}"
                                                       placeholder="Enter number">
                                            @elseif ($prerequisite->prerequisite_type == 'text')
                                                <input type="text" 
                                                       class="form-control" 
                                                       name="prerequisite_details[{{ $index }}]" 
                                                       value="{{ old('prerequisite_details.' . $index, $savedPrerequisite->prerequisite_detail ?? '') }}"
                                                       placeholder="Enter text">
                                            @elseif ($prerequisite->prerequisite_type == 'file')
                                                <input type="file" 
                                                       class="form-control" 
                                                       name="prerequisite_details[{{ $index }}]">
                                                
                                                @if (!empty($savedPrerequisite->file_path))
                                                    <p class="mt-2">
                                                        <strong>Existing File:</strong> 
                                                        <a href="{{ asset('storage/' . $savedPrerequisite->file_path) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            View File
                                                        </a>
                                                    </p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div> <!-- end row -->
                    </div> <!-- end container-fluid -->
                </div> <!-- end list-group -->

                <button type="submit" class="btn btn-primary mt-3">Save Prerequisites</button>
            </form>
        </div> <!-- end card-body -->
    </div> <!-- end card -->
@endif

<!-- End List group Advanced Content -->

<!-- Create Sub-Lesson Modal -->
<div class="modal fade" id="createSubLessonModal" tabindex="-1" aria-labelledby="createSubLessonModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSubLessonModalLabel">Create Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="subLessonForm" method="POST" class="row g-3 ">
                    @csrf
                    <div class="form-group">
                        <label for="sub_lesson_title" class="form-label">Task Title<span class="text-danger">*</span></label>
                        <input type="text" name="sub_lesson_title" class="form-control">
                        <input type="hidden" name="lesson_id" class="form-control" value="{{ $lesson->id }}">
                        <div id="sub_lesson_title_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="sub_description" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="sub_description" rows="3"></textarea>
                        <div id="sub_description_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="sub_status" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="sub_status" aria-label="Select status">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="sub_status_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="submitSubLesson" class="btn btn-primary sbt_btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Create Sub-Lesson Modal -->

<!-- Edit Sub-Lesson Modal -->
<div class="modal fade" id="editSubLessonModal" tabindex="-1" aria-labelledby="editSubLessonModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSubLessonModalLabel">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSubLessonForm" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="edit_sub_lesson_title" class="form-label">Task Title<span class="text-danger">*</span></label>
                        <input type="text" name="edit_sub_lesson_title" class="form-control">
                        <input type="hidden" name="edit_sub_lesson_id" class="form-control">
                        <div id="edit_sub_lesson_title_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_sub_description" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="edit_sub_description" id="edit_sub_description" rows="3"></textarea>
                        <div id="edit_sub_description_error" class="text-danger error_e"></div>
                    </div>     
                    <div class="form-group">
                        <label for="edit_sub_status" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="edit_sub_status" id="edit_sub_status">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="edit_sub_status_error" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="updateSubLesson" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Edit Sub-Lesson Modal -->

<!-- Sub-Lesson Delete Modal -->
<form action="{{ url('sub-lesson/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteSubLessonModal" tabindex="-1" aria-labelledby="deleteSubLessonModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSubLessonModalLabel">Delete Sub-Lesson</h5>
                    <input type="hidden" name="sub_lesson_id" id="subLessonId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Task "<strong><span id="append_sub_lesson_name"></span></strong>" ?
                    {{-- <strong><span id="append_sub_lesson_name"></span></strong> --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Sub-Lesson Delete Modal -->

@endsection

@section('js_scripts')

<script>
    // Show modal for creating sub-lesson
    $("#createSubLessonBtn").on('click', function(){
        $(".error_e").html('');
        $("#subLessonForm")[0].reset();
        $("#createSubLessonModal").modal('show');
    });

    // Handle form submission for creating sub-lesson
    $("#subLessonForm").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/sub-lesson/create") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#createSubLessonModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>'+value+'<p>';
                    $('#'+key+'_error').html(msg); 
                });
            }
        });
    });

    // Show modal for editing sub-lesson
    $('body').on('click', '.edit-lesson-icon', function() {
        var subLessonId = $(this).data('lesson-id');
        $.ajax({
            url: "{{ url('/sub-lesson/edit') }}",
            type: 'GET',
            data: { id: subLessonId },
            success: function(response) {
                // Ensure the response is correct and populate the form
                $('input[name="edit_sub_lesson_title"]').val(response.subLesson.title);
                $('input[name="edit_sub_lesson_id"]').val(response.subLesson.id);
                $('#edit_sub_description').val(response.subLesson.description);
                $('#edit_sub_status').val(response.subLesson.status);
                $('#editSubLessonModal').modal('show');
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Handle form submission for updating sub-lesson
    $("#editSubLessonForm").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/sub-lesson/update") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#editSubLessonModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '</p>';
                    $('#' + key + '_error').html(msg);
                });
            }
        });
    });

    // Show modal for deleting sub-lesson
    // $('body').on('click', '.delete-lesson-icon', function() {
    //     var subLessonId = $(this).data('lesson-id');
    //     var subLessonTitle = $(this).data('lesson-title');
    //     $('#subLessonId').val(subLessonId);
    //     $('#append_sub_lesson_name').html(subLessonTitle);
    //     $('#deleteSubLessonModal').modal('show');
    // });

    $('body').on('click', '.delete-lesson-icon', function(e) {
        e.preventDefault();

        $('#deleteSubLessonModal').modal('show');

        var subLessonId = $(this).data('lesson-id');
        var subLessonTitle = $(this).closest('.sublesson_card').find('.SublessonName').text();

        if (!subLessonTitle) {
            subLessonTitle = "Unknown Sub-Lesson";
        }

        $('#append_sub_lesson_name').html(subLessonTitle);
        $('#subLessonId').val(subLessonId);
        
        console.log("Sub-Lesson Title: " + subLessonTitle);
    });

    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
    }, 2000);


</script>


@endsection
