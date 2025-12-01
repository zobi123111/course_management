@extends('layout.app')
@section('title', 'Topic Section')
@section('sub-title', 'Topic Section')
@section('content')

<div class="container">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</div>

<style>
    .action-btn {
        padding: 0px 10px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 100px;
        height: 30px;
    }

    .switch-input {
        display: none;
    }

    .switch-button {
        position: absolute;
        cursor: pointer;
        background-color: #dc3545; /* red for OFF */
        border-radius: 30px;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        transition: background-color 0.3s ease;
        overflow: hidden;
    }

    .switch-button-left,
    .switch-button-right {
        position: absolute;
        width: 60%;
        text-align: center;
        line-height: 30px;
        font-size: 12px;
        font-weight: bold;
        color: #fff;
        transition: all 0.3s ease;
    }

    /* Left side (OFF) */
    .switch-button-left {
        left: 25px;
    }

    /* Right side (ON) */
    .switch-button-right {
        right: 34px;
        transform: translateX(100%);
        opacity: 0;
    }

    /* Knob */
    .switch-button::before {
        content: "";
        position: absolute;
        height: 26px;
        width: 26px;
        left: 2px;
        top: 2px;
        background-color: white;
        border-radius: 50%;
        transition: transform 0.3s ease;
    }

    /* When checked (ON) */
    .switch-input:checked + .switch-button {
        background-color: #28a745; /* green for ON */
    }

    .switch-input:checked + .switch-button::before {
        transform: translateX(68px);
    }

    .switch-input:checked + .switch-button .switch-button-left {
        transform: translateX(-100%);
        opacity: 0;
    }

    .switch-input:checked + .switch-button .switch-button-right {
        transform: translateX(0);
        opacity: 1;
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
                        <th scope="col">Title</th>
                        @if(auth()->user()->is_admin != 1)
                            <th scope="col">Organizational Unit</th>
                        @endif
                        <th scope="col">Description</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topics as $topic)
                    <tr>
                        <td class="topicTitle">{{ $topic->title }}</td>
                        @if(auth()->user()->is_admin != 1)
                            <td>{{ $topic->organizationUnit->org_unit_name ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $topic->description ?? 'N/A' }}</td>
                        <td>
                            <i class="fa fa-eye action-btn" style="font-size:25px; cursor: pointer;" onclick="window.location.href='{{ route('topic.view', ['id' => encode_id($topic->id)]) }}'"></i>
                            
                            <i class="fa fa-edit edit-topic-icon action-btn" style="font-size:25px; cursor: pointer;" data-topic-id="{{ encode_id($topic->id) }}"></i>

                            <i class="fa-solid fa-trash delete-topic-icon action-btn" style="font-size:25px; cursor: pointer;" data-topic-id="{{ encode_id($topic->id) }}" data-topic-name="{{ $topic->title }}"></i>
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
                        <div class="form-group">
                            <label for="title" class="form-label">Topic Title<span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control">
                            <div id="title_error" class="text-danger error_e"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description" class="form-label">Topic Description<span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control">
                            <div id="description_error" class="text-danger error_e"></div>
                        </div>

                        @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                            <div class="form-group">
                                <label for="ou_id" class="form-label">Select Org Unit<span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="ou_id" id="ou_id"
                                    aria-label="Default select example">
                                    <option value="">Select Org Unit</option>
                                    @foreach($organizationUnits as $val)
                                    <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                    @endforeach
                                </select>
                                <div id="ou_id_error_up" class="text-danger error_e"></div>
                            </div>
                        @endif
                        
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
                        <input type="hidden" name="topic_id" id="edit_topic_id">
                        <div class="form-group">
                            <label class="form-label">Topic Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control">
                            <div id="title_error_up" class="text-danger error_e"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Topic Description</label>
                            <input type="text" name="description" id="edit_description" class="form-control">
                            <div id="description_error_up" class="text-danger error_e"></div>
                        </div>
                        
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

    <!-- Delete topic Modal -->
    <form action="{{ url('topic/delete') }}" id="deletetopicForm" method="POST">
        @csrf
        <div class="modal fade" id="deletetopic" tabindex="-1" aria-labelledby="deletetopicLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deletetopicLabel">Delete topic</h5>
                        <input type="hidden" name="topic_id" id="topicId">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this Topic "<strong><span id="append_title"></span></strong>"?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="confirmDeletetopic" class="btn btn-danger delete_topic">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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

            $('#topicTable').on('click', '.edit-topic-icon', function() {
                $('.error_e').html('');
                var topicId = $(this).data('topic-id');
                $(".loader").fadeIn();
                $.ajax({
                    url: "{{ url('/topic/edit') }}",
                    type: 'GET',
                    data: { id: topicId },
                    success: function(response) {
                        $('#edit_topic_id').val(response.topic.id);
                        $('#edit_title').val(response.topic.title);
                        $('#edit_description').val(response.topic.description);
                        $('#edit_ou_id').val(response.topic.ou_id);
                        $('#edittopicModal').modal('show');
                        $(".loader").fadeOut("slow");
                    }
                });
            });

            $('#updatetopic').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ url('/topic/update') }}",
                    type: "POST",
                    data: $("#edittopicForm").serialize(),
                    success: function(response) {
                        $('#edittopicModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#' + key + '_error_up').html('<p>' + value + '</p>');
                        });
                    }
                });
            });

            $(document).on('click', '.delete-topic-icon', function() {
                $('#deletetopic').modal('show');
                var topicId = $(this).data('topic-id');
                var topicName = $(this).closest('tr').find('.topicTitle').text();
                $('#append_title').html(topicName);
                $('#topicId').val(topicId);
            });

            $(document).on('click', '.start-topic-icon', function () {
                let topicId = $(this).data('topic-id');
                window.location.href = `/topic/start/${topicId}`;
            });

            $(document).on('click', '.view-result-icon', function () {
                let topicId = $(this).data('topic-id');
                window.location.href = `/topic/view-result/${topicId}`;
            });

            setTimeout(function() {
                $('#successMessage').fadeOut('slow');
            }, 2000);
        });
    </script>
@endsection
