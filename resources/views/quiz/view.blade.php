@extends('layout.app')

@section('title', 'View Quiz')
@section('sub-title', 'Quiz Details')

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
        background-color: red;
    }
</style>

    @if(session()->has('message'))
        <div id="successMessage" class="alert alert-success fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            {{ session()->get('message') }}
        </div>
        {{ session()->forget('message') }}
    @endif

    <div class="card mb-3 shadow-sm">
        <div class="row g-0">
            <div class="col-md-4 mt-3">
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Quiz Title</th>
                            <td>{{ $quiz->title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Course</th>
                            <td>{{ $quiz->course->course_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>OU</th>
                            <td>{{ $quiz->quizOu->org_unit_name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-4 mt-3">
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Duration</th>
                            <td>{{ $quiz->duration }} mins</td>
                        </tr>
                        <tr>
                            <th>Passing Score</th>
                            <td>{{ $quiz->passing_score }}%</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td><span class="badge bg-{{ $quiz->status == 'published' ? 'success' : 'secondary' }}">{{ ucfirst($quiz->status) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <h3 class="me-3 mt-3">Assigned Topics</h3>
                @if($quiz->question_selection == 'manual')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTopicModal">
                        Add Topic
                    </button>
                @endif
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Topic</th>
                        <th>Assigned Questions</th>
                        <th>Total Questions</th>
                        @if($quiz->question_selection == 'manual')
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($quiz->topics as $index => $topic)                 
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="topicTitle">{{ $topic->topic->title }}</td>
                        <td>{{ $topic->question_quantity }}</td>
                        <td>{{ $topic->topic->questions->count() }}</td>
                        @if($quiz->question_selection == 'manual')
                            <td>
                                <!-- <i class="fa fa-eye action-btn" data-bs-toggle="modal" data-bs-target="#viewQuestionsModal_{{ $topic->id }}" style="font-size:25px; cursor:pointer;"></i> -->
                                <i class="fa-solid fa-pen-to-square edit-topic-icon action-btn" style="font-size:25px; cursor:pointer;" data-topic-id="{{ encode_id($topic->topic->id) }}" data-topic-name="{{ $topic->topic->title }}" data-quiz-id="{{ encode_id($quiz->id) }}" data-total="{{ $topic->topic->questions->count() }}" data-quantity="{{ $topic->question_quantity }}"></i>
                                <i class="fa-solid fa-trash delete-topic-icon action-btn" style="font-size:25px; cursor: pointer;" data-topic-id="{{ encode_id($topic->topic->id) }}" data-quiz-id="{{ encode_id($quiz->id) }}" data-topic-name="{{ $topic->topic->title }}"></i>
                            </td>
                        @endif

                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No topics assigned yet.</td>
                    </tr>
                    @endforelse
                </tbody>

                @if($quiz->topics->count())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="">Total</td>
                            <td>
                                {{ $quiz->topics->sum('question_quantity') }}
                            </td>
                            <td>
                                {{ $quiz->topics->sum(fn($t) => $t->topic->questions->count()) }}
                            </td>

                            @if($quiz->question_selection == 'manual')
                                <td></td>
                            @endif
                        </tr>
                    </tfoot>
                @endif

            </table>
        </div>
    </div>

   @foreach($quiz->topics as $topic)

    @php
        $topicQuestions = $quizQuestions->where('topic_id', $topic->topic_id);
    @endphp

    <div class="modal fade" id="viewQuestionsModal_{{ $topic->id }}" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Questions — {{ $topic->topic->title }}
                    </h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <table class="table table-hover mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Question Text</th>
                                <th>Question Type</th>
                                <th>Option A</th>
                                <th>Option B</th>
                                <th>Option C</th>
                                <th>Option D</th>
                                <th>Correct Option</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($topicQuestions as $q)
                                <tr>
                                    <td>{{ $q->question->question_text }}</td>

                                    <td>
                                        @switch($q->question->question_type)
                                            @case('single_choice') Single Choice @break
                                            @case('multiple_choice') Multiple Choice @break
                                            @case('sequence') Sequence @break
                                            @case('text') Text @break
                                        @endswitch
                                    </td>

                                    <!-- <td>{{ $q->question->option_A ?? '-' }}</td>
                                    <td>{{ $q->question->option_B ?? '-' }}</td>
                                    <td>{{ $q->question->option_C ?? '-' }}</td>
                                    <td>{{ $q->question->option_D ?? '-' }}</td>
                                    <td>{{ $q->question->correct_option ?? '-' }}</td> -->

                                    @if($q->question->option_type == 'text')
                                        <td>{{ $q->question->option_A ? $q->question->option_A : '-' }}</td>
                                        <td>{{ $q->question->option_B ? $q->question->option_B : '-' }}</td>
                                        <td>{{ $q->question->option_C ? $q->question->option_C : '-' }}</td>
                                        <td>{{ $q->question->option_D ? $q->question->option_D : '-' }}</td>
                                    @else
                                        <td>
                                            @if($q->question->option_A)
                                                <img src="{{ Storage::url($q->question->option_A) }}" alt="Option A" width="100">
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            @if($q->question->option_B)
                                            <img src="{{ Storage::url($q->question->option_B) }}" alt="Option B" width="100">
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            @if($q->question->option_C)
                                                <img src="{{ Storage::url($q->question->option_C) }}" alt="Option C" width="100">
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            @if($q->question->option_D)
                                                <img src="{{ Storage::url($q->question->option_D) }}" alt="Option D" width="100">
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{ $q->question->correct_option ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No questions assigned to this topic.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

    @endforeach



    <!-- Add Topic Modal -->
    <div class="modal fade" id="addTopicModal" tabindex="-1" aria-labelledby="addTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('quiz.addTopic', $quiz->id) }}" method="POST">
                @csrf

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTopicModalLabel">Add Topic to Quiz</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Topic</label>
                            <select name="topic_id" id="add_topic_select" class="form-select" required>
                                <option value="" disabled selected>-- Select Topic --</option>

                                @foreach($topics as $topic)
                                    @unless($quiz->topics->pluck('topic_id')->contains($topic->id))
                                        <option value="{{ $topic->id }}" data-total="{{ $topic->questions_count }}">
                                            {{ $topic->title }}
                                        </option>
                                    @endunless
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Number of Questions</label>
                            <input type="number" name="question_quantity" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Number of Questions</label>
                            <span id="total_question_quantity" class="fw-bold">0</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Topic</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Topic Quantity Modal -->

    <div class="modal fade" id="editTopicModal" tabindex="-1" aria-labelledby="addTopicModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('quiz.editTopic', $quiz->id) }}" id="editTopicForm" method="POST">
                @csrf
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Question Quantity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="topic_id" id="edit_topic_id">
                        <input type="hidden" name="quiz_id" id="edit_quiz_id">

                        <div class="mb-3">
                            <label class="form-label">Topic</label>
                            <input type="text" id="edit_topic_name" class="form-control" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Question Quantity</label>
                            <input type="number" name="question_quantity" id="edit_question_quantity" class="form-control" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Available Questions</label>
                            <span id="edit_total_question" class="fw-bold">0</span>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Quiz Modal -->
    <form action="{{ route('quiz.deleteTopic') }}" id="deletetopicForm" method="POST">
        @csrf
        <div class="modal fade" id="deleteTopic" tabindex="-1" aria-labelledby="deleteTopicLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteTopicLabel">Delete Topic</h5>
                        <input type="hidden" name="topic_id" id="topicId">
                        <input type="hidden" name="quiz_id" id="quizId">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this topic "<strong><span id="append_title"></span></strong>"?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="confirmDeleteTopic" class="btn btn-danger delete_topic">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </form>


@endsection

@section('js_scripts')
    
<script>
    setTimeout(function() {
        $('#successMessage').fadeOut('slow', function(){
            $(this).remove();
        });
    }, 2000);

    $(document).ready(function() {
    
        $(document).on('click', '.delete-topic-icon', function() {
            $('#deleteTopic').modal('show');
            var topicId = $(this).data('topic-id');
            var quizId = $(this).data('quiz-id');
            var topicName = $(this).closest('tr').find('.topicTitle').text();
            $('#append_title').html(topicName);
            $('#topicId').val(topicId);
            $('#quizId').val(quizId);
        });

        $(document).on('change', '#add_topic_select', function() {
            let total = $('option:selected', this).data('total') || 0;
            $('#total_question_quantity').text(total);

            $('input[name="question_quantity"]').attr("max", total);
        });


        $(document).on('click', '.edit-topic-icon', function () {
            let id = $(this).data('topic-id');
            let quizId = $(this).data('quiz-id');
            let name = $(this).data('topic-name');
            let quantity = $(this).data('quantity');
            let total = $(this).data('total');

            $('#edit_topic_id').val(id);
            $('#edit_topic_name').val(name);
            $('#edit_question_quantity').val(quantity);
            $('#edit_total_question').text(total);
            $('#edit_question_quantity').attr("max", total);
            $('#edit_quiz_id').val(quizId);
            $('#editTopicModal').modal('show');
        });

        $('input[type="number"]').on('input', function() {
            let max = parseInt($(this).attr("max"));
            let value = parseInt($(this).val());

            if (value > max) {
                $(this).val(max);
            }
        });
    });


</script>

@endsection
