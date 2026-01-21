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

    @if(session()->has('skipped') && count(session('skipped')) > 0)
        <div class="alert alert-warning alert-dismissible fade show skippedMessage" role="alert">
            <strong>Skipped Questions:</strong>
            <ul>
                @foreach(session('skipped') as $skip)
                    <li>
                        <strong>{{ $skip['question'] }}</strong> - {{ $skip['reason'] }}
                    </li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


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

    <br>

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
                            @if($question->option_type == 'text')
                                <td>{{ $question->option_A ? $question->option_A : '-' }}</td>
                                <td>{{ $question->option_B ? $question->option_B : '-' }}</td>
                                <td>{{ $question->option_C ? $question->option_C : '-' }}</td>
                                <td>{{ $question->option_D ? $question->option_D : '-' }}</td>
                            @else
                                <td>
                                    @if($question->option_A)
                                        <img src="{{ Storage::url($question->option_A) }}" alt="Option A" width="100">
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($question->option_B)
                                       <img src="{{ Storage::url($question->option_B) }}" alt="Option B" width="100">
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($question->option_C)
                                        <img src="{{ Storage::url($question->option_C) }}" alt="Option C" width="100">
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    @if($question->option_D)
                                        <img src="{{ Storage::url($question->option_D) }}" alt="Option D" width="100">
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif

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

        // setTimeout(function() {
        //     $('.skippedMessage').fadeOut('slow');
        // }, 3000);

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
                        let question = response.question;

                        $('#edit_question_id').val(question.id);
                        $('#edit_question_text').val(question.question_text);

                        if (question.question_type === 'text') {
                            $('#edit_option_A').closest('.form-group').hide();
                            $('#edit_option_B').closest('.form-group').hide();
                            $('#edit_option_C').closest('.form-group').hide();
                            $('#edit_option_D').closest('.form-group').hide();
                            $('#edit_correct_option').closest('.form-group').hide();
                        } else {
                            $('#edit_correct_option').closest('.form-group').show();

                            if(question.option_type === 'text') {
                                $('#edit_option_A').attr('type', 'text').val(question.option_A);
                                $('#edit_option_B').attr('type', 'text').val(question.option_B);
                                $('#edit_option_C').attr('type', 'text').val(question.option_C);
                                $('#edit_option_D').attr('type', 'text').val(question.option_D);
                            } else if(question.option_type === 'image') {
                                $('#edit_option_A').attr('type', 'file').val('');
                                $('#edit_option_B').attr('type', 'file').val('');
                                $('#edit_option_C').attr('type', 'file').val('');
                                $('#edit_option_D').attr('type', 'file').val('');

                                $('.option-preview').remove();
                                
                                const storageBaseUrl = "{{ asset('storage') }}/";


                                if (question.option_A) {
                                    $('#edit_option_A').after(
                                        `<img src="${storageBaseUrl}${question.option_A}" 
                                            alt="Option A" width="100" class="mt-2 mb-2 option-preview">`
                                    );
                                }

                                if (question.option_B) {
                                    $('#edit_option_B').after(
                                        `<img src="${storageBaseUrl}${question.option_B}" 
                                            alt="Option B" width="100" class="mt-2 mb-2 option-preview">`
                                    );
                                }

                                if (question.option_C) {
                                    $('#edit_option_C').after(
                                        `<img src="${storageBaseUrl}${question.option_C}" 
                                            alt="Option C" width="100" class="mt-2 mb-2 option-preview">`
                                    );
                                }

                                if (question.option_D) {
                                    $('#edit_option_D').after(
                                        `<img src="${storageBaseUrl}${question.option_D}" 
                                            alt="Option D" width="100" class="mt-2 mb-2 option-preview">`
                                    );
                                }

                                
                            }

                            $('#edit_option_A').closest('.form-group').show();
                            $('#edit_option_B').closest('.form-group').show();
                            $('#edit_option_C').closest('.form-group').show();
                            $('#edit_option_D').closest('.form-group').show();
                            $('#edit_correct_option').val(question.correct_option);
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

                var form = document.getElementById('editQuestionForm');
                var formData = new FormData(form);

                $(".loader").fadeIn();

                $.ajax({
                    url: "{{ url('/question/update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
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

        function addQuestion(questionType) {
            let questionCount = document.querySelectorAll('#questions-container .question-block').length + 1;

            const container = document.getElementById('questions-container');

            const block = document.createElement('div');
            block.classList.add('question-block', 'border', 'p-3', 'mb-3');
            block.setAttribute('id', `question_block_${questionCount}`);

            let optionsHTML = "";

            if (questionType !== "text") {
                optionsHTML = `
                    <label>Option Type</label>
                    <select name="questions[${questionCount}][option_type]" 
                            class="form-control mb-3"
                            onchange="toggleOptionType(${questionCount}, this.value)">
                        <option value="text">Text Options</option>
                        <option value="image">Image Options</option>
                    </select>

                    <!-- TEXT OPTIONS -->
                    <div id="text_options_${questionCount}" class="option-section">
                        <label class="mt-2">Options (Text):</label>

                        <div class="mb-2">
                            <label>A</label>
                            <input type="text" name="questions[${questionCount}][options_text][]" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>B</label>
                            <input type="text" name="questions[${questionCount}][options_text][]" class="form-control">
                        </div>
                        <div id="more_text_options_${questionCount}"></div>

                        <button type="button" class="btn btn-secondary mt-2" 
                                onclick="addTextOption(${questionCount})">
                            Add Option
                        </button>
                    </div>

                    <!-- IMAGE OPTIONS -->
                    <div id="image_options_${questionCount}" class="option-section" style="display:none;">
                        <label class="mt-2">Options (Images):</label>

                        <div class="mb-2">
                            <label>A</label>
                            <input type="file" name="questions[${questionCount}][options_image][]" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-2">
                            <label>B</label>
                            <input type="file" name="questions[${questionCount}][options_image][]" class="form-control" accept="image/*">
                        </div>
                        <div id="more_image_options_${questionCount}"></div>

                        <button type="button" class="btn btn-secondary mt-2" 
                                onclick="addImageOption(${questionCount})">
                            Add Option
                        </button>
                    </div>

                    <label class="mt-3">Correct Answer</label>
                    <input type="text" name="questions[${questionCount}][correct_answer]" 
                        class="form-control mb-2" placeholder="Correct Answer (e.g. A or A,B)">
                `;
            }

            block.innerHTML = `
                <div class="d-flex justify-content-between">
                    <h5>Question ${questionCount} (${questionType.replace('_',' ')})</h5>
                    <button type="button" class="btn custom-btn" onclick="removeQuestion(this)">
                        <i class="fa fa-trash question_delete"></i>
                    </button>
                </div>

                <label>Question Text</label>
                <input type="text" name="questions[${questionCount}][question]" 
                    class="form-control mb-2" placeholder="Enter question text">

                <label>Upload Question Image</label>
                <input type="file" name="questions[${questionCount}][question_image]" 
                    class="form-control mb-3" accept="image/*">

                <input type="hidden" name="questions[${questionCount}][type]" value="${questionType}">

                ${optionsHTML}
            `;


            container.appendChild(block);
        }

        function toggleOptionType(qNum, type) {
            if (type === 'text') {
                document.getElementById(`text_options_${qNum}`).style.display = 'block';
                document.getElementById(`image_options_${qNum}`).style.display = 'none';
            } else {
                document.getElementById(`text_options_${qNum}`).style.display = 'none';
                document.getElementById(`image_options_${qNum}`).style.display = 'block';
            }
        }

        function addTextOption(qNum) {
            const container = document.getElementById(`more_text_options_${qNum}`);
            const count = container.querySelectorAll('.option-field').length;

            const labels = ['C', 'D'];

            if (count < labels.length) {
                const label = labels[count];

                container.insertAdjacentHTML("beforeend", `
                    <div class="mb-2 option-field">
                        <label>${label}</label>
                        <input type="text" name="questions[${qNum}][options_text][]" class="form-control">
                    </div>
                `);
            }
        }

        function addImageOption(qNum) {
            const container = document.getElementById(`more_image_options_${qNum}`);
            const count = container.querySelectorAll('.option-field').length;

            const labels = ['C', 'D'];

            if (count < labels.length) {
                const label = labels[count];

                container.insertAdjacentHTML("beforeend", `
                    <div class="mb-2 option-field">
                        <label>${label}</label>
                        <input type="file" name="questions[${qNum}][options_image][]" class="form-control" accept="image/*">
                    </div>
                `);
            }
        }

        function removeQuestion(button) {
            const block = button.closest(".question-block");
            if (block) block.remove();
        }

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

        function createOrUpdatePreview(input) {
            const $input = $(input);
            let $preview = $input.siblings('.option-preview');

            if ($preview.length === 0) {
                $preview = $('<img class="option-preview mt-2 mb-2" width="100">');
                $input.after($preview);
            }

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Attach change listeners dynamically
        $('#editQuestionModal').on('change', 'input[type="file"]', function() {
            createOrUpdatePreview(this);
        });

        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('csvFile');
            const form = fileInput.closest('form');

            // Create an element to show errors below the input
            const errorDiv = document.createElement('div');
            errorDiv.classList.add('text-danger', 'mb-2', 'mt-2', 'text-center');
            errorDiv.style.display = 'none';
            fileInput.parentNode.appendChild(errorDiv); // place below input

            // Expected headers
            const expectedHeaders = ['question', 'type', 'option_A', 'option_B', 'option_C', 'option_D', 'correct_answers'];

            fileInput.addEventListener('change', function (e) {
                errorDiv.style.display = 'none';
                errorDiv.innerText = '';

                const file = e.target.files[0];
                if (!file) return;

                // 1️⃣ Check file type
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext !== 'csv') {
                    errorDiv.innerText = 'Please select a valid CSV file.';
                    errorDiv.style.display = 'block';
                    fileInput.value = ''; // Reset file input
                    return;
                }

                // 2️⃣ Check CSV headers
                const reader = new FileReader();
                reader.onload = function (e) {
                    const text = e.target.result;
                    const lines = text.split(/\r?\n/).filter(line => line.trim() !== '');
                    if (lines.length === 0) {
                        errorDiv.innerText = 'CSV file is empty.';
                        errorDiv.style.display = 'block';
                        fileInput.value = '';
                        return;
                    }

                    const headers = lines[0].split(',').map(h => h.trim());
                    const missingHeaders = expectedHeaders.filter(h => !headers.includes(h));

                    if (missingHeaders.length > 0) {
                        errorDiv.innerText = 'CSV is missing required headers: ' + missingHeaders.join(', ');
                        errorDiv.style.display = 'block';
                        fileInput.value = '';
                    }
                };
                reader.readAsText(file);
            });

            // Prevent submission if error is visible
            form.addEventListener('submit', function (e) {
                if (errorDiv.style.display === 'block') {
                    e.preventDefault();
                }
            });
        });
        
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('csvFile');
            const importModal = document.getElementById('importModal');

            // Find the error div appended below the input
            const errorDiv = fileInput.parentNode.querySelector('.text-danger');

            // Reset error and input when modal closes
            importModal.addEventListener('hidden.bs.modal', function () {
                if (errorDiv) {
                    errorDiv.style.display = 'none';
                    errorDiv.innerText = '';
                }
                fileInput.value = ''; // clear file input
            });
        });


    </script>
@endsection