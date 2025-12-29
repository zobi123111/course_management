@extends('layout.app')
@section('title', 'Course Topic Section')
@section('sub-title', 'Course Topic Section')
@section('content')

<div class="container">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</div>

<style>
    .action-btn {
        padding: 0px 10px;
    }
    .one-line {
        white-space: nowrap;
    }

    .actions-cell {
        white-space: nowrap;
    }

    .actions-cell i {
        display: inline-block;
        vertical-align: middle;
    }

    .tooltip-inner {
        max-width: 300px;
        text-align: left;
    }

</style>

    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif


    <div class="create_btn">
        <button class="btn btn-primary create-button" id="createtopic" data-toggle="modal"
            data-target="#createtopicModal">Create Topic</button>
    </div>

    <br>

    <div class="card pt-4">
        <div class="card-body">
            <table class="table table-hover" id="topicTable">
                <thead>
                    <tr>
                        <th scope="col">Course</th>
                        <th class="one-line" scope="col">Course Type</th>
                        <!-- <th class="one-line" scope="col">Description</th> -->
                        @if(auth()->user()->is_owner == 1)
                            <th class="one-line" scope="col">Organizational Unit</th>
                        @endif
                        <th class="one-line" scope="col">Topics Count</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coursestopics as $course)
                    <tr>
                        <td class="topicTitle">{{ $course->course_name }}</td>
                        <td class="one-line" >{{ $course->course_type ?? 'N/A' }}</td>
                        <!-- <td>{{ $course->description ?? 'N/A' }}</td> -->
                        @if(auth()->user()->is_owner == 1)
                        <td class="one-line" >{{ $course->organizationUnit->org_unit_name ?? 'N/A' }}</td>
                        @endif
                        <td class="topicTitle"
                            data-bs-toggle="tooltip"
                            data-bs-html="true"
                            title="
                                <ul class='mb-0 ps-3'>
                                    @foreach($course->topics as $topic)
                                        <li>{{ $topic->title }}</li>
                                    @endforeach
                                </ul>
                            ">
                            {{ $course->topics->count() }}
                        </td>

                        <td class="actions-cell">
                            <i class="fa fa-eye action-btn" style="font-size:25px; cursor: pointer;" onclick="window.location.href='{{ route('topic.view', ['id' => encode_id($course->id)]) }}'"></i>
                            
                            <!-- <i class="fa fa-edit edit-topic-icon action-btn" style="font-size:25px; cursor: pointer;" data-topic-id="{{ encode_id($course->id) }}"></i>

                            <i class="fa-solid fa-trash delete-topic-icon action-btn" style="font-size:25px; cursor: pointer;" data-topic-id="{{ encode_id($course->id) }}" data-topic-name="{{ $course->title }}"></i> -->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

     <!-- Create topic Modal -->
    <div class="modal fade" id="createtopicModal" tabindex="-1" role="dialog" aria-labelledby="createtopicLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createtopicLabel">Create New Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="topicForm" method="POST" class="row g-3 needs-validation">
                        @csrf

                        @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                            <div class="form-group">
                                <label for="ou_id" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                                <select class="form-select" name="ou_id" id="ou_id" aria-label="Default select example">
                                    <option value="">Select Org Unit</option>
                                    @foreach($organizationUnits as $val)
                                    <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                    @endforeach
                                </select>
                                <div id="ou_id_error_up" class="text-danger error_e"></div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="course_id" class="form-label">Course<span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select" id="course_id_create">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                @endforeach
                            </select>
                            <div id="course_id_error" class="text-danger error_e"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="title" class="form-label">Topic Title<span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control">
                            <div id="title_error" class="text-danger error_e"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Topic Description<span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control"></textarea>
                            <div id="description_error" class="text-danger error_e"></div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="submittopic" class="btn btn-primary sbt_btn">Save</button>
                        </div>
                        <div class="loader" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit topic Modal -->
    <div class="modal fade" id="edittopicModal" tabindex="-1" aria-labelledby="edittopicLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edittopicLabel">Edit topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edittopicForm" class="row g-3 needs-validation">
                        @csrf

                        @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                            <div class="form-group">
                                <label for="ou_id" class="form-label">Select Org Unit</label>
                                <select class="form-select" name="ou_id" id="edit_ou_id" aria-label="Default select example">
                                    <option value="">Select Org Unit</option>
                                    @foreach($organizationUnits as $val)
                                    <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                    @endforeach
                                </select>
                                <div id="ou_id_error_up" class="text-danger error_e"></div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="course_id" class="form-label">Course</label>
                            <select name="course_id" id="edit_course_id" class="form-select">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                                @endforeach
                            </select>
                            <div id="course_id_error_up" class="text-danger error_e"></div>
                        </div>

                        <div id="topicAssignedMsg" class="alert alert-warning mt-3" style="display:none;">
                            <i class="fa fa-info-circle me-1"></i>
                            You can't change Org Unit and Course fields because this topic is already assigned to quizzes.
                        </div>
                        
                        <input type="hidden" name="topic_id" id="edit_topic_id">
                        <div class="form-group">
                            <label class="form-label">Topic Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control">
                            <div id="title_error_up" class="text-danger error_e"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Topic Description</label>
                            <textarea name="description" id="edit_description" class="form-control"></textarea>
                            <div id="description_error_up" class="text-danger error_e"></div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="updatetopic" class="btn btn-primary sbt_btn">Update</button>
                        </div>
                        <div class="loader" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
@endsection

@section('js_scripts')

<script>
        $(document).ready(function() {
            $('#topicTable').DataTable({
                "ordering": false
            });

            $("#createtopic").on('click', function() {
                $(".error_e").html('');
                $("#topicForm")[0].reset();
                $("#createtopicModal").modal('show');
            });

            $('#ou_id').on('change', function() {
                let ouId = $(this).val();
                $('#course_id_create').html('<option value="">Loading...</option>');
                if (ouId) {
                    $.ajax({
                        url: "{{ route('courses.byOu') }}",
                        type: 'GET',
                        data: { ou_id: ouId },
                        success: function(res) {
                            let options = '<option value="">Select course</option>';
                            res.forEach(course => {
                                options += `<option value="${course.id}">${course.course_name}</option>`;
                            });
                            $('#course_id_create').html(options);
                        }
                    });
                } else {
                    $('#course_id_create').html('<option value="">Select course</option>');
                }
            });

            $('#course_id_create').on('change', function() {
                let courseId = $(this).val();
                $('#lesson_id_create').html('<option value="">Loading...</option>');
                if (courseId) {
                    $.ajax({
                        url: "{{ route('lessons.byCourse') }}",
                        type: 'GET',
                        data: { course_id: courseId },
                        success: function(res) {
                            let options = '<option value="">Select Lesson</option>';
                            res.forEach(lesson => {
                                options += `<option value="${lesson.id}">${lesson.lesson_title}</option>`;
                            });
                            $('#lesson_id_create').html(options);
                        }
                    });
                } else {
                    $('#lesson_id_create').html('<option value="">Select Lesson</option>');
                }
            });

            $('#edit_course_id').on('change', function() {
                let courseId = $(this).val();
                $('#edit_lesson_id').html('<option value="">Loading...</option>');
                if (courseId) {
                    $.ajax({
                        url: "{{ route('lessons.byCourse') }}",
                        type: 'GET',
                        data: { course_id: courseId },
                        success: function(res) {
                            let options = '<option value="">Select Lesson</option>';
                            res.forEach(lesson => {
                                options += `<option value="${lesson.id}">${lesson.lesson_title}</option>`;
                            });
                            $('#edit_lesson_id').html(options);
                        }
                    });
                } else {
                    $('#edit_lesson_id').html('<option value="">Select Lesson</option>');
                }
            });


            $("#submittopic").on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ url("/topic/create") }}',
                    type: 'POST',
                    data: $("#topicForm").serialize(),
                    success: function(response) {
                        $('#createtopicModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#' + key + '_error').html('<p>' + value + '</p>');
                        });
                    }
                });
            });

            setTimeout(function() {
                $('#successMessage').fadeOut('slow');
            }, 2000);

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

        });
    </script>

@endsection
