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
                            <th>Status</th>
                            <td><span class="badge bg-{{ $quiz->status == 'published' ? 'success' : 'secondary' }}">{{ ucfirst($quiz->status) }}</span></td>
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
                    </table>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="card-body d-flex align-items-center">
                <h1 class="me-3">Assigned Topics</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTopicModal">
                    Add Topic
                </button>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Topic</th>
                        <th>Question Quantity</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($quiz->topics as $index => $topic)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="topicTitle">{{ $topic->topic->title }}</td>
                        <td>{{ $topic->question_quantity }}</td>
                        <td>
                            <i class="fa-solid fa-trash delete-topic-icon action-btn" style="font-size:25px; cursor: pointer;" data-topic-id="{{ encode_id($topic->id) }}" data-topic-name="{{ $topic->topic->title }}"></i>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No topics assigned yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
                            <select name="topic_id" class="form-select" required>
                                <option value="" disabled selected>-- Select Topic --</option>
                                @foreach($topics as $topic)
                                    <option value="{{ $topic->id }}">{{ $topic->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Number of Questions</label>
                            <input type="number" name="question_quantity" class="form-control" min="1" required>
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

    <!-- Delete Quiz Modal -->
    <form action="{{ url('quiz/deleteTopic') }}" id="deletetopicForm" method="POST">
        @csrf
        <div class="modal fade" id="deleteTopic" tabindex="-1" aria-labelledby="deleteTopicLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteTopicLabel">Delete Topic</h5>
                        <input type="hidden" name="topic_id" id="topicId">
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
        $('#successMessage').fadeOut('slow');
    }, 2000);

    // $(document).ready(function() {
    
        $(document).on('click', '.delete-topic-icon', function() {
            $('#deleteTopic').modal('show');
            var topicId = $(this).data('topic-id');
            var topicName = $(this).closest('tr').find('.topicTitle').text();
            $('#append_title').html(topicName);
            $('#topicId').val(topicId);
        });

    // });

</script>

@endsection
