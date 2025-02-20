@section('sub-title', 'Lesson')
@extends('layout.app')
@section('content')

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      @foreach($breadcrumbs as $breadcrumb)
        @if($breadcrumb['url']) 
          <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
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
                        data-bs-target="#createSubLessonModal">Create Sub-Lesson</button>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- End Card with an image on left -->


 <!-- List group with Advanced Contents -->
 <div class="list-group">
    @foreach($lesson->subLessons as $val)
        <div class="list-group-item " aria-current="true">
            <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1" data-sublesson-title="{{ $val->title }}">{{ $val->title }}</h5>

                <span>                 
                @if(checkAllowedModule('courses', 'sublesson.edit')->isNotEmpty())
                    <i class="fa fa-edit edit-lesson-icon" style="font-size:18px; cursor: pointer; margin-right: 5px;" data-lesson-id="{{ encode_id($val->id) }}"></i>
                @endif
                @if(checkAllowedModule('courses', 'sublesson.delete')->isNotEmpty())
                    <i class="fa-solid fa-trash delete-lesson-icon" style="font-size:18px; cursor: pointer;"
                    data-lesson-id="{{ encode_id($val->id) }}"></i>
                @endif
                </span>

            </div>
            <p class="mb-1">{{ $val->description }}</p>
        </div>
    @endforeach
</div>

<!-- End List group Advanced Content -->

<!-- Create Sub-Lesson Modal -->
<div class="modal fade" id="createSubLessonModal" tabindex="-1" aria-labelledby="createSubLessonModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createSubLessonModalLabel">Create Sub-Lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="subLessonForm" method="POST" class="row g-3 ">
                    @csrf
                    <div class="form-group">
                        <label for="sub_lesson_title" class="form-label">Sub-Lesson Title<span class="text-danger">*</span></label>
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
                <h5 class="modal-title" id="editSubLessonModalLabel">Edit Sub-Lesson</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSubLessonForm" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="edit_sub_lesson_title" class="form-label">Sub-Lesson Title<span class="text-danger">*</span></label>
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
                    Are you sure you want to delete this Sub-Lesson "<strong><span id="append_sub_lesson_name"></span></strong>" ?
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

{{-- <script>
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
                    var msg = '<p>' + value + '</p>';
                    $('#' + key + '_error').html(msg);
                });
            }
        });
    });

    // Show modal for editing sub-lesson
    $('.edit-sub-lesson-icon').on('click', function() {
        var subLessonId = $(this).data('sub-lesson-id');
        $.ajax({
            url: "{{ url('/sub-lesson/edit') }}",
            type: 'GET',
            data: { id: subLessonId },
            success: function(response) {
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
    $('.delete-sub-lesson-icon').on('click', function() {
        var subLessonId = $(this).data('sub-lesson-id');
        var subLessonTitle = $(this).data('sub-lesson-title');
        $('#subLessonId').val(subLessonId);
        $('#append_sub_lesson_name').html(subLessonTitle);
        $('#deleteSubLessonModal').modal('show');
    });
</script> --}}

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
        var subLessonTitle = $(this).closest('.list-group-item').find('[data-sublesson-title]').data('sublessonTitle'); 

        if (!subLessonTitle) {
            subLessonTitle = "Unknown Sub-Lesson";
        }

        $('#append_sub_lesson_name').html(subLessonTitle);
        $('#subLessonId').val(subLessonId);
        
        console.log("Sub-Lesson Title: " + subLessonTitle);
    });


</script>


@endsection
