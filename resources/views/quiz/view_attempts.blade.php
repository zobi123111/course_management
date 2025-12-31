@extends('layout.app')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h3 class="me-3 mt-3">Quiz Attempts - {{ $quiz->title }}</h3>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>User Name</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Result</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($Attempt as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->student->fname }} {{ $item->student->lname }}</td>
                        <td>{{ $item->score }}%</td>
                        <td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td>
                        <td>{{ ucfirst($item->result ?? 'N/A') }}</td>

                        <td>
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm view-attempt-icon" data-quiz-id="{{ encode_id($item->quiz_id) }}" data-user-id="{{ encode_id($item->student_id) }}"> View </a>

                           <a href="javascript:void(0);" onclick="confirmReset(this)" data-quiz-id="{{ encode_id($item->quiz_id) }}" data-user-id="{{ encode_id($item->student_id) }}" data-attempt-id="{{ encode_id($item->id) }}" class="btn btn-danger btn-sm"> Reset </a>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No attempts found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection


@section('js_scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).on('click', '.view-attempt-icon', function () {
            let quizId = $(this).data('quiz-id');
            let userId = $(this).data('user-id');

            $.ajax({
                url: "{{ route('quiz.viewSingleAttempt') }}",
                type: "POST",
                data: {
                    quiz_id: quizId,
                    user_id: userId,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    window.location.href = response.redirect_url;
                }
            });
        });

        function confirmReset(button) {
            let quiz_id = button.getAttribute('data-quiz-id');
            let user_id = button.getAttribute('data-user-id');
            let attempt_id = button.getAttribute('data-attempt-id');

            Swal.fire({
                title: 'Are you sure?',
                html: "This will reset the student's quiz and allow them to retake it.<br><br>" +
                    "<strong>Please note:</strong> Resetting a quiz only clears the student's answers — it does NOT change or modify the quiz questions.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, reset it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('quiz.resetAttempt') }}",
                        type: "POST",
                        data: {
                            quiz_id: quiz_id,
                            user_id: user_id,
                            attempt_id: attempt_id,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire(
                                'Reset Successful!',
                                'The quiz attempt has been reset.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong.',
                                'error'
                            );
                        }
                    });

                }
            });
        }


    </script>


@endsection