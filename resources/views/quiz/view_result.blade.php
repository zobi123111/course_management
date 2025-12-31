@extends('layout.app')
@section('title', 'Quiz Section')
@section('sub-title', 'Quiz Section')
@section('content')

<style>
    .list-group-item-success img {
    border: 3px solid #198754;
}

.list-group-item-danger img {
    border: 3px solid #dc3545;
}

</style>
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
                            <th>Duration</th>
                            <td>{{ $quiz->duration }} mins</td>
                        </tr>
                        @if(auth()->check() && auth()->user()->is_owner == 1 || auth()->user()->is_admin == 1)
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-{{ $quiz->status == 'published' ? 'success' : 'secondary' }}">{{ ucfirst($quiz->status) }}</span></td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
            <div class="col-md-4 mt-3">
                <div class="card-body">
                    <table class="table table-borderless">
                        
                        <tr>
                            <th>Passing Score</th>
                            <td>{{ $quiz->passing_score }}%</td>
                        </tr>
                            <tr>
                                <th> Marks Obtained </th>
                                <td>{{ $quizAttempt->score !== null ? $quizAttempt->score.'%' : 'N/A' }}</td>
                                <!-- @if(!empty($quizAttempt->score)) -->
                                <!-- @endif -->
                            </tr>
                            <tr>
                                <th>Result</th>
                                <td>
                                    @if ($quizAttempt->result)
                                        <span class="badge bg-{{ $quizAttempt->result === 'pass' ? 'success' : 'danger' }}">
                                            {{ ucfirst($quizAttempt->result) }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        <tr>
                            <th>Time Taken</th>
                            <!-- @if(!empty($quizAttempt->submitted_at)) -->
                            <!-- @endif -->
                            <td>
                                @if($quizAttempt->started_at && $quizAttempt->submitted_at)
                                    @php
                                        $seconds = \Carbon\Carbon::parse($quizAttempt->started_at)
                                                    ->diffInSeconds(\Carbon\Carbon::parse($quizAttempt->submitted_at));

                                        $minutes = floor($seconds / 60);
                                        $remainingSeconds = $seconds % 60;
                                    @endphp

                                    @if($minutes > 0)
                                        {{ $minutes }} mins {{ $remainingSeconds }} secs
                                    @else
                                        {{ $remainingSeconds }} secs
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>

                        </tr>

                    </table>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>

    @if($quiz->show_result === 'yes' || (auth()->check() && auth()->user()->is_owner == 1 || auth()->user()->is_admin == 1))
        @foreach($quizDetails as $index => $question)
            @php

                $isCorrect = false;

                $userAnswer = $question['user_answer'] ?? null;
                $correctAnswer = $question['correct_option'] ?? null;

                if($question['question_type'] === 'sequence'){
                    $userArr    = $userAnswer ? explode(',', $userAnswer) : [];
                    $correctArr = $correctAnswer ? explode(',', $correctAnswer) : [];

                    $userArr    = array_map('trim', $userArr);
                    $correctArr = array_map('trim', $correctArr);

                    $isCorrect = ($userArr === $correctArr);

                }else{
                    $userArr = $userAnswer
                        ? array_map('trim', explode(',', strtolower($userAnswer)))
                        : [];

                    $correctArr = $correctAnswer
                        ? array_map('trim', explode(',', strtolower($correctAnswer)))
                        : [];

                    sort($userArr);
                    sort($correctArr);

                    $isCorrect = ($userArr == $correctArr);
                }
            @endphp

            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $index + 1 }}. {{ $question['question_text'] }}
                    </h5>

                    @if(!empty($question['question_image']))
                        <div style="text-align: center;">
                            <img src="{{ Storage::url($question['question_image']) }}" alt="question image" class="img-fluid rounded mb-3" style="max-height:120px">
                        </div>
                    @endif


                    @if(in_array($question['question_type'], ['single_choice', 'multiple_choice', 'sequence']))
                        <ul class="list-group mb-3">
                            @foreach(['A','B','C','D'] as $opt)
                                @php
                                    $optionValue = $question['option_' . $opt] ?? null;
                                @endphp

                                @if(!empty($optionValue))
                                    <li class="list-group-item
                                        @if(in_array($opt, explode(',', $correctAnswer))) list-group-item-success @endif
                                        @if(in_array($opt, explode(',', $userAnswer ?? '')) && !$isCorrect) list-group-item-danger @endif
                                    ">
                                        <strong>{{ $opt }}:</strong>

                                        @if(($question['option_type'] ?? 'text') === 'image')
                                            <div class="mt-2">
                                                <img src="{{ Storage::url($optionValue) }}"
                                                    alt="Option {{ $opt }}"
                                                    class="img-fluid rounded"
                                                    style="max-height:100px">
                                            </div>
                                        @else
                                            {{ $optionValue }}
                                        @endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif


                    <p><strong>Type:</strong> {{ $question['question_type'] }}</p>
                    <p><strong>Correct Answer:</strong> {{ $correctAnswer ?? 'N/A' }}</p>
                    <p><strong>Your Answer:</strong> {{ $userAnswer ?? 'No Answer' }}</p>

                    <p>
                        <strong>Result:</strong>
                        @if($question['question_type'] == 'text')
                            <span class="badge bg-warning text-dark">Instructor has not yet reviewed this answer</span>
                        @else
                            @if(!$userAnswer)
                                <span class="badge bg-secondary">Not Answered</span>
                            @elseif($isCorrect)
                                <span class="badge bg-success">Correct</span>
                            @else
                                <span class="badge bg-danger">Wrong</span>
                            @endif
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    @endif


<!-- </div> -->
@endsection
