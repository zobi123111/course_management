@extends('layout.app')

@section('title', 'View Topic')
@section('sub-title', 'Topic Details')

@section('content')

<style>
    .action-btn {
        padding: 0px 10px;
    }
    .custom-btn {
        background-color: transparent;
        border: none;
        padding: 5px 10px;
        margin-left: 10px;
    }

    .custom-btn:hover {
        background-color: White;
    }

    .question_delete {
        color: rgba(253, 13, 13, 1);
        cursor: pointer;
        font-size: 18px;
    }

</style>

    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif

    <div class="card mb-3 shadow-sm">
        <div class="row g-0">
            <div class="col-md-4 mt-3">
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Topic Title</th>
                            <td>{{ $topic->title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $topic->description ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>OU</th>
                            <td>{{ $topic->organizationUnit->org_unit_name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card pt-4 mt-5">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="m-0">Topic Questions</h2>
                <div>
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fa fa-upload"></i> Import CSV
                    </button>

                    <a href="{{ url('/export-csv') }}" class="btn btn-warning">
                        <i class="fa fa-download"></i>Download Sample CSV
                    </a>
                </div>
            </div>

            <table class="table table-hover mt-3" id="questionTable">
                <thead>
                    <tr>
                    <th>Question Text</th>
                    <th>Question Type</th>
                    <th>Options A</th>
                    <th>Options B</th>
                    <th>Options C</th>
                    <th>Options D</th>
                    <th>Correct Option</th>
                    <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topicQuestions as $question)
                        <tr>
                            <td class="quizTitle">{{ $question->question_text }}</td>
                            <!-- <td>{{ $question->question_type }}</td> -->
                             <td>
                                @switch($question->question_type)
                                    @case('single_choice')
                                        Single Choice
                                        @break

                                    @case('multiple_choice')
                                        Multiple Choice
                                        @break

                                    @case('sequence')
                                        Sequence
                                        @break

                                    @case('text')
                                        Text
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $question->option_A ? $question->option_A : '-' }}</td>
                            <td>{{ $question->option_B ? $question->option_B : '-' }}</td>
                            <td>{{ $question->option_C ? $question->option_C : '-' }}</td>
                            <td>{{ $question->option_D ? $question->option_D : '-' }}</td>
                            <td>{{ $question->correct_option ? $question->correct_option : '-' }}</td>
                            <td>
                                <i class="fa fa-edit edit-quiz-icon action-btn" style="font-size:25px; cursor: pointer;" data-quiz-id="{{ encode_id($question->id) }}"></i>
                                <i class="fa-solid fa-trash delete-quiz-icon action-btn" style="font-size:25px; cursor: pointer;" data-quiz-id="{{ encode_id($topic->id) }}" data-quiz-name="{{ $question->title }}" data-question-id="{{ encode_id($question->id) }}"></i>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="x_content">
        <form action="{{ route('question.create') }}" method="post" id="questionForm" onsubmit="showLoader()" enctype="multipart/form-data">
            @csrf
            <div id="questions-container"></div>

            <input type="hidden" name="topic_id" value="{{ $topic->id }}" required>

            <div class="question-btn">
                <button type="button" class="btn btn-primary mt-3" onclick="addQuestion('text')">Add Text Question</button>
                <button type="button" class="btn btn-primary mt-3" onclick="addQuestion('single_choice')">Add Single Choice Question</button>
                <button type="button" class="btn btn-primary mt-3" onclick="addQuestion('multiple_choice')">Add Multiple Choice Question</button>
                <button type="button" class="btn btn-primary mt-3" onclick="addQuestion('sequence')">Add Sequence Question</button>
            </div>

            <div class="loader" id="custom_loader" style="display: none;"></div>
            
            <button type="submit" class="btn btn-primary mt-5" style="display: block;" title="Submit">Submit</button>
        </form>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ url('/import-csv') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Choose CSV File</label>
                            <input type="hidden" name="topic_id" value="{{ $topic->id }}">
                            <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
     <!-- Edit Question Modal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editQuestionLabel">Edit Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editQuestionForm" class="row g-3 needs-validation">
                        @csrf
                        <input type="hidden" name="question_id" id="edit_question_id">
                        <div class="form-group">
                            <label class="form-label">Question Text</label>
                            <input type="text" name="question_text" id="edit_question_text" class="form-control">
                            <div id="question_text_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Option A</label>
                            <input type="text" name="option_A" id="edit_option_A" class="form-control">
                            <div id="option_A_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Option B</label>
                            <input type="text" name="option_B" id="edit_option_B" class="form-control">
                            <div id="option_B_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Option C</label>
                            <input type="text" name="option_C" id="edit_option_C" class="form-control">
                            <div id="option_C_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Option D</label>
                            <input type="text" name="option_D" id="edit_option_D" class="form-control">
                            <div id="option_D_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correct Answer</label>
                            <input type="text" name="correct_option" id="edit_correct_option" class="form-control">
                            <div id="correct_option_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="updateQuestion" class="btn btn-primary sbt_btn">Update</button>
                        </div>
                        <div class="loader" style="display:none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Quiz Modal -->
    <form action="{{ route('question.destroy') }}" id="deleteQuizForm" method="POST">
        @csrf
        <div class="modal fade" id="deleteQuiz" tabindex="-1" aria-labelledby="deleteQuizLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteQuizLabel">Delete Quiz</h5>
                        <input type="hidden" name="question_id" id="questionId">
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

@endsection

@section('js_scripts')
    <script>
        setTimeout(function() {
            $('#successMessage').fadeOut('slow');
        }, 2000);

        $(document).ready(function() {
            $('#questionTable').DataTable({
                "order": []
            });

            $('#questionTable').on('click', '.edit-quiz-icon', function() {
                $('.error_e').html('');
                var quizId = $(this).data('quiz-id');
                $(".loader").fadeIn();

                $.ajax({
                    url: "{{ url('/question/edit') }}",
                    type: 'GET',
                    data: { id: quizId },
                    success: function(response) {
                        $('#edit_question_id').val(response.question.id);
                        $('#edit_question_text').val(response.question.question_text);
                        $('#edit_option_A').val(response.question.option_A);
                        $('#edit_option_B').val(response.question.option_B);
                        $('#edit_option_C').val(response.question.option_C);
                        $('#edit_option_D').val(response.question.option_D);
                        $('#edit_correct_option').val(response.question.correct_option);

                        if (response.question.question_type === 'text') {
                            $('#edit_option_A').closest('.form-group').hide();
                            $('#edit_option_B').closest('.form-group').hide();
                            $('#edit_option_C').closest('.form-group').hide();
                            $('#edit_option_D').closest('.form-group').hide();
                            $('#edit_correct_option').closest('.form-group').hide();
                        } else {
                            $('#edit_option_A').closest('.form-group').show();
                            $('#edit_option_B').closest('.form-group').show();
                            $('#edit_option_C').closest('.form-group').show();
                            $('#edit_option_D').closest('.form-group').show();
                            $('#edit_correct_option').closest('.form-group').show();
                        }
                        
                        $('#editQuestionModal').modal('show');
                        $(".loader").fadeOut("slow");
                    },
                    error: function(xhr) {
                        console.log(xhr.responseJSON);
                        alert("Error fetching question details!");
                        $(".loader").fadeOut("slow");
                    }
                });
            });

            $('#updateQuestion').on('click', function(e) {
                e.preventDefault();

                if ($('#editQuestionForm')[0].checkValidity() === false) {
                    e.stopPropagation();
                    return;
                }

                $(".loader").fadeIn();

                $.ajax({
                    url: "{{ url('/question/update') }}",
                    type: "POST",
                    data: $("#editQuestionForm").serialize(),
                    success: function(response) {
                        $('#editQuestionModal').modal('hide');
                        
                        location.reload();
                        $(".loader").fadeOut("slow");
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#' + key + '_error_up').html('<p>' + value + '</p>');
                        });
                        $(".loader").fadeOut("slow");
                    }
                });
            });

            $(document).on('click', '.delete-quiz-icon', function() {
                $('#deleteQuiz').modal('show');
                var questionId = $(this).data('question-id');
                var quizId = $(this).data('quiz-id');

                console.log("Question ID:", questionId);
                console.log("Quiz ID:", quizId);
                var quizName = $(this).closest('tr').find('.quizTitle').text();
                $('#append_title').html(quizName);
                $('#questionId').val(questionId);
                $('#quizId').val(quizId);
            });

            setTimeout(function() {
                $('#successMessage').fadeOut('slow');
            }, 2000);
        });

        // function addQuestion(questionType) {
        //     let questionCount = document.querySelectorAll('#questions-container .form-group').length;
        //     questionCount++;
        //     const questionContainer = document.getElementById('questions-container');
        //     const newQuestionDiv = document.createElement('div');
        //     newQuestionDiv.classList.add('form-group', 'mb-3');
        //     newQuestionDiv.innerHTML = `
        //         <label for="question">Question ${questionCount}</label> <button type="button" class="btn custom-btn" onclick="removeQuestion(this)"><i class="fa fa-trash" aria-hidden="true"></i></button>
        //         <input type="text" name="questions[${questionCount}][question]" class="form-control mb-2 mt-3" placeholder="Enter text" required>
        //         <input type="hidden" name="questions[${questionCount}][type]" value="${questionType}" required>
        //     `;
            
        //     if (questionType === 'single_choice' || questionType === 'multiple_choice' || questionType === 'sequence') {
        //         newQuestionDiv.innerHTML += `
        //             <div class="options-container">
        //                 <label for="options_${questionCount}">Options:</label>
        //                 <input type="text" name="questions[${questionCount}][options][]" class="form-control mb-2" placeholder="Option 1" required>
        //                 <input type="text" name="questions[${questionCount}][options][]" class="form-control mb-2" placeholder="Option 2" required>
        //                 <div id="additionalOptions_${questionCount}"></div>
        //                 <button type="button" class="btn btn-secondary mt-2" onclick="addOption(${questionCount})" id="addOptionButton_${questionCount}">Add Option</button>
        //             </div>

        //             <div class="options-container mt-3">
        //                 <label for="options_${questionCount}">Correct Answer: (add correct answer like A,B,C)</label>
        //                 <input type="text" name="questions[${questionCount}][correct_answer]" class="form-control mb-2" placeholder="Correct Answer" required>
        //             </div>
        //         `;
        //     }

        //     questionContainer.appendChild(newQuestionDiv);
        // }
        function addQuestion(questionType) {
            let questionCount = document.querySelectorAll('#questions-container .form-group').length;
            questionCount++;

            // readable label
            let typeLabel = '';
            if (questionType === 'text') typeLabel = 'Text';
            if (questionType === 'single_choice') typeLabel = 'Single Choice';
            if (questionType === 'multiple_choice') typeLabel = 'Multiple Choice';
            if (questionType === 'sequence') typeLabel = 'Sequence';

            const questionContainer = document.getElementById('questions-container');
            const newQuestionDiv = document.createElement('div');
            newQuestionDiv.classList.add('form-group', 'mb-3');
            
            newQuestionDiv.innerHTML = `
                <label for="question">Question ${questionCount} (${typeLabel})</label>
                <button type="button" class="btn custom-btn" onclick="removeQuestion(this)">
                    <i class="fa fa-trash question_delete" aria-hidden="true"></i>
                </button>

                <input type="text" name="questions[${questionCount}][question]" class="form-control mb-2 mt-3" placeholder="Enter text" required>
                <input type="hidden" name="questions[${questionCount}][type]" value="${questionType}" required>
            `;
            
            // if (questionType === 'single_choice' || questionType === 'multiple_choice' || questionType === 'sequence') {
            //     newQuestionDiv.innerHTML += `
            //         <div class="options-container">
            //             <label>Options:</label>
            //             <input type="text" name="questions[${questionCount}][options][]" class="form-control mb-2" placeholder="Option 1" required>
            //             <input type="text" name="questions[${questionCount}][options][]" class="form-control mb-2" placeholder="Option 2" required>
            //             <div id="additionalOptions_${questionCount}"></div>
            //             <button type="button" class="btn btn-secondary mt-2" onclick="addOption(${questionCount})" id="addOptionButton_${questionCount}">Add Option</button>
            //         </div>

            //         <div class="options-container mt-3">
            //             <label>Correct Answer: (A,B,C)</label>
            //             <input type="text" name="questions[${questionCount}][correct_answer]" class="form-control mb-2" placeholder="Correct Answer" required>
            //         </div>
            //     `;
            // }

           if (
                questionType === 'single_choice' ||
                questionType === 'multiple_choice' ||
                questionType === 'sequence'
            ) {
                let html = `
                    <div class="options-container">
                        <label>Options:</label>

                        <div class="mb-2">
                            <label>A</label>
                            <input type="text" name="questions[${questionCount}][options][]" class="form-control" placeholder="Option A" required>
                        </div>

                        <div class="mb-2">
                            <label>B</label>
                            <input type="text" name="questions[${questionCount}][options][]" class="form-control" placeholder="Option B" required>
                        </div>

                        <div id="additionalOptions_${questionCount}"></div>

                        <button type="button" class="btn btn-secondary mt-2" onclick="addOption(${questionCount})" id="addOptionButton_${questionCount}">
                            Add Option
                        </button>
                    </div>
                `;

                // Add correct answer section based on type
                if (questionType === 'single_choice') {
                    html += `
                        <div class="options-container mt-3">
                            <label>Correct Answer (e.g., A)</label>
                            <input type="text" name="questions[${questionCount}][correct_answer]" class="form-control mb-2" placeholder="Correct Answer" required>
                        </div>
                    `;
                }

                if (questionType === 'multiple_choice' || questionType === 'sequence') {
                    html += `
                        <div class="options-container mt-3">
                            <label>Correct Answers (e.g., A,B,C)</label>
                            <input type="text" name="questions[${questionCount}][correct_answer]" class="form-control mb-2" placeholder="Correct Answers" required>
                        </div>
                    `;
                }

                newQuestionDiv.innerHTML += html;
            }

            questionContainer.appendChild(newQuestionDiv);
        }

        // function addOption(questionCount) {
        //     const additionalOptionsContainer = document.getElementById(`additionalOptions_${questionCount}`);
        //     const addOptionButton = document.getElementById(`addOptionButton_${questionCount}`);
            
        //     const currentAdditionalCount = additionalOptionsContainer.querySelectorAll('input').length;

        //     if (currentAdditionalCount < 2) {
        //         const newOptionInput = document.createElement('input');
        //         newOptionInput.type = 'text';
        //         newOptionInput.name = `questions[${questionCount}][options][]`;
        //         newOptionInput.className = 'form-control mb-2';
        //         newOptionInput.placeholder = `Option ${currentAdditionalCount + 3}`;
        //         newOptionInput.required = true;
        //         additionalOptionsContainer.appendChild(newOptionInput);

        //         if (currentAdditionalCount + 1 === 2) {
        //             addOptionButton.style.display = 'none';
        //         }
        //     }
        // }

        function addOption(questionCount) {
            const additionalOptionsContainer = document.getElementById(`additionalOptions_${questionCount}`);
            const addOptionButton = document.getElementById(`addOptionButton_${questionCount}`);

            const existing = additionalOptionsContainer.querySelectorAll('.option-field').length;

            const optionLabels = ['C', 'D'];
            
            if (existing < optionLabels.length) {
                const label = optionLabels[existing];

                const newOption = document.createElement('div');
                newOption.className = 'mb-2 option-field';
                newOption.innerHTML = `
                    <label>${label}</label>
                    <input type="text" name="questions[${questionCount}][options][]" class="form-control" placeholder="Option ${label}" required>
                `;
                
                additionalOptionsContainer.appendChild(newOption);

                if (existing + 1 === optionLabels.length) {
                    addOptionButton.style.display = 'none';
                }
            }
        }

        function removeQuestion(button) {
            const questionDiv = button.parentElement;
            questionDiv.remove();
        }

        $('#questionForm').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    location.reload();
                },
                error: function(response) {
                    console.log(response.errors);
                }
            });
        });



    </script>
@endsection
