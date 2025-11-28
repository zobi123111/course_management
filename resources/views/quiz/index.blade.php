@extends('layout.app')
@section('title', 'Quiz Section')
@section('sub-title', 'Quiz Section')
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

    @if(checkAllowedModule('courses','course.store')->isNotEmpty())
        <div class="create_btn">
            <button class="btn btn-primary create-button" id="createQuiz" data-toggle="modal"
                data-target="#createQuizModal">Create Quiz</button>
        </div>
    @endif

    <br>

    <div class="card pt-4">
        <div class="card-body">
            <table class="table table-hover" id="quizTable">
                <thead>
                    <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Course</th>
                        <th scope="col">Lesson</th>
                        <th scope="col">Duration</th>
                        <th scope="col">Passing Score</th>
                        <th scope="col">Type</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizs as $quiz)
                    <tr>
                        <td class="quizTitle">{{ $quiz->title }}</td>
                        <td>{{ $quiz->course->course_name ?? 'N/A' }}</td>
                        <td>{{ $quiz->lesson->lesson_title ?? 'N/A' }}</td>
                        <td>{{ $quiz->duration }} mins</td>
                        <td>{{ $quiz->passing_score }}%</td>
                        <td>{{ ucfirst($quiz->quiz_type) }}</td>
                        <!-- <td>{{ ucfirst($quiz->status) }}</td> -->
                        @if(get_user_role(auth()->user()->role) == 'administrator')  
                            <td>
                                <label class="switch">
                                    <input type="checkbox" 
                                        class="switch-input toggle-status" 
                                        data-id="{{ $quiz->id }}"
                                        {{ $quiz->status == 'published' ? 'checked' : '' }}>
                                    <div class="switch-button">
                                        <span class="switch-button-left">Draft</span>
                                        <span class="switch-button-right">Published</span>
                                    </div>
                                </label>
                            </td>
                        @else
                            <td>{{ ucfirst($quiz->status) }}</td>
                        @endif
                        <td>
                            @if(checkAllowedModule('quiz','quiz.view')->isNotEmpty())
                                <i class="fa fa-eye action-btn" style="font-size:25px; cursor: pointer;" onclick="window.location.href='{{ route('quiz.view', ['id' => encode_id($quiz->id)]) }}'"></i>
                            @endif
                            
                            @if(checkAllowedModule('quiz','quiz.edit')->isNotEmpty())
                                <i class="fa fa-edit edit-quiz-icon action-btn" style="font-size:25px; cursor: pointer;" data-quiz-id="{{ encode_id($quiz->id) }}"></i>
                            @endif

                            @if(checkAllowedModule('quiz','quiz.destroy')->isNotEmpty())
                                <i class="fa-solid fa-trash delete-quiz-icon action-btn" style="font-size:25px; cursor: pointer;" data-quiz-id="{{ encode_id($quiz->id) }}" data-quiz-name="{{ $quiz->title }}"></i>
                            @endif
                            
                            @if(checkAllowedModule('quiz','quiz.start')->isNotEmpty())
                                @if(auth()->user()->role == 3)
                                    @if($quiz->topics->isNotEmpty())
                                        @if($quiz->quizAttempts->contains('student_id', auth()->user()->id))
                                            <button class="start-quiz-btn action-btn view-result-icon btn btn-primary" style="cursor: pointer; color: white;" 
                                                data-quiz-id="{{ encode_id($quiz->id) }}" data-quiz-name="{{ $quiz->title }}"> View
                                            </button>
                                        @else
                                            <button class="start-quiz-btn action-btn start-quiz-icon" style="cursor: pointer; background: #198754; color: white; border-radius: .25rem; border: none;" 
                                                data-quiz-id="{{ encode_id($quiz->id) }}" data-quiz-name="{{ $quiz->title }}" data-duration="{{ $quiz->duration }}"> Start Quiz
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-danger">You can't started yet</span>
                                    @endif
                                @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Quiz Modal -->
    <div class="modal fade" id="createQuizModal" tabindex="-1" role="dialog" aria-labelledby="createQuizLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createQuizLabel">Create New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quizForm" method="POST" class="row g-3 needs-validation">
                        @csrf
                        <div class="form-group">
                            <label for="title" class="form-label">Quiz Title<span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control">
                            <div id="title_error" class="text-danger error_e"></div>
                        </div>
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
                            <label for="lesson_id" class="form-label">Lesson<span class="text-danger">*</span></label>
                            <select name="lesson_id" id="lesson_id_create" class="form-select">
                                <option value="">Select Lesson</option>
                            </select>
                            <div id="lesson_id_error" class="text-danger error_e"></div>
                        </div>

                        <div class="form-group">
                            <label for="duration" class="form-label">Duration (minutes)<span class="text-danger">*</span></label>
                            <input type="number" name="duration" class="form-control">
                            <div id="duration_error" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label for="passing_score" class="form-label">Passing Score (%)<span class="text-danger">*</span></label>
                            <input type="number" name="passing_score" class="form-control">
                            <div id="passing_score_error" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label for="quiz_type" class="form-label">Quiz Type<span class="text-danger">*</span></label>
                            <select name="quiz_type" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="training">Training</option>
                            </select>
                            <div id="quiz_type_error" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                            <div id="status_error" class="text-danger error_e"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="submitQuiz" class="btn btn-primary sbt_btn">Save</button>
                        </div>
                        <div class="loader" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Quiz Modal -->
    <div class="modal fade" id="editQuizModal" tabindex="-1" aria-labelledby="editQuizLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editQuizLabel">Edit Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editQuizForm" class="row g-3 needs-validation">
                        @csrf
                        <input type="hidden" name="quiz_id" id="edit_quiz_id">
                        <div class="form-group">
                            <label class="form-label">Quiz Title</label>
                            <input type="text" name="title" id="edit_title" class="form-control">
                            <div id="title_error_up" class="text-danger error_e"></div>
                        </div>
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

                        <div class="form-group">
                            <label for="lesson_id" class="form-label">Lesson</label>
                            <select name="lesson_id" id="edit_lesson_id" class="form-select">
                                <option value="">Select Lesson</option>
                            </select>
                            <div id="lesson_id_error_up" class="text-danger error_e"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Duration</label>
                            <input type="number" name="duration" id="edit_duration" class="form-control">
                            <div id="duration_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Passing Score</label>
                            <input type="number" name="passing_score" id="edit_passing_score" class="form-control">
                            <div id="passing_score_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quiz Type</label>
                            <select name="quiz_type" id="edit_quiz_type" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="training">Training</option>
                            </select>
                            <div id="quiz_type_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                            <div id="status_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="updateQuiz" class="btn btn-primary sbt_btn">Update</button>
                        </div>
                        <div class="loader" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Quiz Modal -->
    <form action="{{ url('quiz/delete') }}" id="deleteQuizForm" method="POST">
        @csrf
        <div class="modal fade" id="deleteQuiz" tabindex="-1" aria-labelledby="deleteQuizLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteQuizLabel">Delete Quiz</h5>
                        <input type="hidden" name="quiz_id" id="quizId">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this quiz "<strong><span id="append_title"></span></strong>"?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="confirmDeleteQuiz" class="btn btn-danger delete_quiz">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- Start Quiz Instructions Modal -->
    <div class="modal fade" id="startQuizModal" tabindex="-1" aria-labelledby="startQuizLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="startQuizLabel">Quiz Instructions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="startQuizTitle" class="mb-2"></h6>
                    <p id="startQuizDetails" class="mb-2"></p>
                    <ul>
                        <li id="startQuizDurationLine"></li>
                        <li>Your quiz will start when you click <strong>Start</strong>.</li>
                        <li>If you close the tab or the browser while taking the quiz, your answers will be automatically submitted.</li>
                    </ul>
                    <p class="text-muted small">Make sure you have a stable connection before starting.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmStartQuiz" class="btn btn-primary">Start Quiz</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_scripts')
    <script>
        $(document).ready(function() {
            $('#quizTable').DataTable();

            $("#createQuiz").on('click', function() {
                $(".error_e").html('');
                $("#quizForm")[0].reset();
                $("#createQuizModal").modal('show');
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


            $("#submitQuiz").on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ url("/quiz/create") }}',
                    type: 'POST',
                    data: $("#quizForm").serialize(),
                    success: function(response) {
                        $('#createQuizModal').modal('hide');
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

            $('#quizTable').on('click', '.edit-quiz-icon', function() {
                $('.error_e').html('');
                var quizId = $(this).data('quiz-id');
                $(".loader").fadeIn();
                $.ajax({
                    url: "{{ url('/quiz/edit') }}",
                    type: 'GET',
                    data: { id: quizId },
                    success: function(response) {
                        $('#edit_quiz_id').val(response.quiz.id);
                        $('#edit_title').val(response.quiz.title);
                        $('#edit_course_id').val(response.quiz.course_id);
                        $('#edit_duration').val(response.quiz.duration);
                        $('#edit_passing_score').val(response.quiz.passing_score);
                        $('#edit_quiz_type').val(response.quiz.quiz_type);
                        $('#edit_status').val(response.quiz.status);

                        $.ajax({
                            url: "{{ route('lessons.byCourse') }}",
                            type: 'GET',
                            data: { course_id: response.quiz.course_id },
                            success: function(res) {
                                let options = '<option value="">Select Lesson</option>';
                                res.forEach(lesson => {
                                    options += `<option value="${lesson.id}" ${lesson.id == response.quiz.lesson_id ? 'selected' : ''}>${lesson.lesson_title}</option>`;
                                });
                                $('#edit_lesson_id').html(options);
                            }
                        });

                        $('#editQuizModal').modal('show');
                        $(".loader").fadeOut("slow");
                    }
                });
            });

            $('#updateQuiz').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ url('/quiz/update') }}",
                    type: "POST",
                    data: $("#editQuizForm").serialize(),
                    success: function(response) {
                        $('#editQuizModal').modal('hide');
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

            $(document).on('click', '.delete-quiz-icon', function() {
                $('#deleteQuiz').modal('show');
                var quizId = $(this).data('quiz-id');
                var quizName = $(this).closest('tr').find('.quizTitle').text();
                $('#append_title').html(quizName);
                $('#quizId').val(quizId);
            });

            $(document).on('click', '.start-quiz-icon', function () {
                let quizId = $(this).data('quiz-id');
                let quizName = $(this).data('quiz-name') || '';
                let duration = $(this).data('duration');
                $('#startQuizTitle').text(quizName);
                $('#startQuizDetails').text('You are about to start this quiz. Please review the details and ensure you have a stable connection.');
                if (duration && duration > 0) {
                    $('#startQuizDurationLine').text('Duration: ' + duration + ' minutes');
                } else {
                    $('#startQuizDurationLine').text('Duration: Not specified');
                }
                $('#confirmStartQuiz').data('quiz-id', quizId);
                $('#startQuizModal').modal('show');
            });

            $('#confirmStartQuiz').on('click', function () {
                let quizId = $(this).data('quiz-id');
                window.location.href = `/quiz/start/${quizId}`;
            });

            $(document).on('click', '.view-result-icon', function () {
                let quizId = $(this).data('quiz-id');
                window.location.href = `/quiz/view-result/${quizId}`;
            });

            document.querySelectorAll('.toggle-status').forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    let quizId = this.getAttribute('data-id');
                    let status = this.checked ? 1 : 0;

                    fetch('{{ route('quiz.updateStatus') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: quizId,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log(data.message);
                        } else {
                            console.error('Failed to update');
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                    });
                });
            });

            setTimeout(function() {
                $('#successMessage').fadeOut('slow');
            }, 2000);
        });
    </script>
@endsection
