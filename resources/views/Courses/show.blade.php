
@section('sub-title', 'Course')
@extends('layout.app')
@section('content')

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
        <img src="{{  url('assets/img/card.jpg')  }}" class="img-fluid rounded-start" alt="...">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title">{{  $course->course_name  }}</h5>
                <p class="card-text">{{ $course->description }}</p>
                <p class="card-text"><button class="btn btn-success" id="createLesson" data-toggle="modal"
                data-target="#createLessonModal">Create Lesson</button></p>
            </div>
        </div>
    </div>
</div>
<!-- End Card with an image on left -->

 <!-- List group with Advanced Contents -->
<div class="list-group">
    @foreach($courseLesson as $val)
    <div class="list-group-item " aria-current="true">
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">{{ $val->lesson_title }}</h5>
            <!-- <small>3 days ago</small> -->
        </div>
        <p class="mb-1">{{ $val->description }}</p>
        <!-- <small>And some small print.</small> -->
</div>
    @endforeach
    <!-- <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">List group item heading</h5>
        <small class="text-muted">3 days ago</small>
        </div>
        <p class="mb-1">Some placeholder content in a paragraph.</p>
        <small class="text-muted">And some muted small print.</small>
    </a>
    <a href="#" class="list-group-item list-group-item-action">
        <div class="d-flex w-100 justify-content-between">
        <h5 class="mb-1">List group item heading</h5>
        <small class="text-muted">3 days ago</small>
        </div>
        <p class="mb-1">Some placeholder content in a paragraph.</p>
        <small class="text-muted">And some muted small print.</small>
    </a> -->
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
                        <div id="course_name_error" class="text-danger error_e"></div>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitLesson" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Courses-->

@endsection

@section('js_scripts')

<script>
$(document).ready(function() {

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

});
</script>

@endsection