@extends('layout.app')
@section('title', 'Quiz Section')
@section('sub-title', 'Quiz Section')
@section('content')


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

    @foreach($quiz->quizQuestions as $index => $question)
        @php
            $isCorrect = false;
            $userAnswer = $answers[$question->id] ?? null;
            if($userAnswer){
                $user = strtolower($userAnswer->selected_option);
                $correct = strtolower($question->question->correct_option);

                $userArr = array_map('trim', explode(',', $user));
                $correctArr = array_map('trim', explode(',', $correct));

                sort($userArr);
                sort($correctArr);

                $isCorrect = ($userArr == $correctArr);
            }
        @endphp

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    {{ $loop->iteration }}. {{ $question->question->question_text }}
                </h5>

                @if(in_array($question->question->question_type, ['single_choice', 'multiple_choice', 'sequence']))
                    <ul class="list-group mb-3">
                        @foreach(['A', 'B', 'C', 'D'] as $opt)
                            @php $optKey = 'option_' . $opt; @endphp
                            @if($question->question->$optKey)
                                <li class="list-group-item
                                    @if($question->correct_option == $opt) list-group-item-success @endif
                                    @if($userAnswer && $userAnswer->answer_text == $opt && !$isCorrect) list-group-item-danger @endif
                                ">
                                    <strong>{{ $opt }}:</strong> {{ $question->question->$optKey }}
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif

                <p class="text-muted"><strong>Type:</strong> {{ $question->question->question_type }}</p>

                <p><strong>Your Answer:</strong>
                    @if(in_array($question->question_type, ['multiple_choice', 'sequence']))
                        @if($userAnswer)
                            @php
                                $decoded = $userAnswer->selected_option;
                            @endphp
                            {{ $decoded }}
                        @else
                            <span class="text-muted">No Answer</span>
                        @endif
                    @else
                        {{ $userAnswer->selected_option ?? 'No Answer' }}
                    @endif
                </p>

                <p><strong>Correct Answer:</strong>
                    @if(in_array($question->question->question_type, ['multiple_choice', 'sequence']))
                        @php
                            $decodedCorrect = $question->question->correct_option;
                        @endphp
                        {{ $decodedCorrect }}
                    @else
                        {{ $question->question->correct_option }}
                    @endif
                </p>


                <p>
                    <strong>Result:</strong>
                    @if(!$userAnswer)
                        <span class="badge bg-secondary">Not Answered</span>
                    @elseif($isCorrect)
                        <span class="badge bg-success">Correct </span>
                    @else
                        <span class="badge bg-danger">Wrong </span>
                    @endif
                </p>
            </div>
        </div>
    @endforeach

<!-- </div> -->
@endsection
