@extends('layout.app')

@section('title', 'Feedback From')
@section('sub-title', 'Feedback From')

@section('content')

@if(session()->has('success'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('success') }}
</div>
@endif
@if(session()->has('error'))
<div id="successMessage" class="alert alert-warning fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('error') }}
</div>
@endif

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-primary text-white rounded-top-4">
        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Training Feedback Form</h5>
    </div>

    <div class="card-body p-4">
        <form action="{{ route('training.feedback.submit') }}" method="POST">
            @csrf

            <input type="hidden" name="event_id" value="{{ $event->id }}">

            @if($event->course && $event->course->training_feedback_questions && count($event->course->training_feedback_questions))
                @foreach($event->course->training_feedback_questions as $index => $question)
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            {{ $index + 1 }}. {{ $question->question }}
                        </label>

                        @if($question->answer_type === 'yes_no')
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
                                <select name="answers[{{ $question->id }}]" class="form-select">
                                    <option value="">-- Select Answer --</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        @elseif($question->answer_type === 'rating')
                            <small class="text-muted d-block mt-1 mb-2">Please Grade 1–5 how strongly you agree (5 being <strong>Strongly Agree</strong>)</small>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-star-fill text-warning"></i></span>
                                <select name="answers[{{ $question->id }}]" class="form-select">
                                    <option value="">-- Select Rating --</option>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} - {{ ['Strongly Disagree', 'Disagree', 'Neutral', 'Agree', 'Strongly Agree'][$i - 1] }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif

                        {{-- Display validation error --}}
                        @error("answers.{$question->id}")
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                @endforeach

                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4 shadow">
                        <i class="bi bi-check-circle me-1"></i> Submit Feedback
                    </button>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i> No feedback questions available for this course.
                </div>
            @endif
        </form>

    </div>
</div>



@endsection

@section('js_scripts')

<script>

$(document).ready(function() {

    $(document).on('click', '.acknowledge-btn', function(){
        var eventId = $(this).data('event-id');
        var ack_comment = $('#ack_comments').val();
// alert(ack_comment);
// return;
        $.ajax({
                url: '/grading/acknowledge',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "eventId": eventId,
                    "ack_comment": ack_comment
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.error('Unlock failed:', xhr.responseText);
                }
            });
    })

    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
    }, 2000);
})


</script>

@endsection