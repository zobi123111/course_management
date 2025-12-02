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
                            <th>Duration</th>
                            <td>{{ $quiz->duration }} mins</td>
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
                            <th>Passing Score</th>
                            <td>{{ $quiz->passing_score }}%</td>
                        </tr>
                        @if($quiz->show_result == 'yes')
                            <tr>
                                <th> Marks Obtained </th>
                                @if(!empty($quizAttempt->score))
                                    <td>{{ $quizAttempt->score }}%</td>
                                @endif
                            </tr>
                            <tr>
                                <th>Result</th>
                                <td><span class="badge bg-{{ $quizAttempt->result == 'pass' ? 'success' : 'danger' }}">{{ strtoupper($quizAttempt->result) }}</span></td>
                            </tr>
                        @endif
                        <tr>
                            <th>Time Taken</th>
                            @if(!empty($quizAttempt->submitted_at))
                            <td>
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
                            </td>
                            @endif
                        </tr>

                    </table>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>

    @foreach($quizDetails as $index => $question)
        @php
            $userAnswer = $question['user_answer'] ?? null;
            $correctAnswer = $question['correct_option'] ?? null;

            $userArr = $userAnswer ? array_map('trim', explode(',', strtolower($userAnswer))) : [];
            $correctArr = $correctAnswer ? array_map('trim', explode(',', strtolower($correctAnswer))) : [];

            sort($userArr);
            sort($correctArr);

            $isCorrect = ($userArr == $correctArr);
        @endphp

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">
                    {{ $index + 1 }}. {{ $question['question_text'] }}
                </h5>

                @if(in_array($question['question_type'], ['single_choice', 'multiple_choice', 'sequence']))
                    <ul class="list-group mb-3">
                        @foreach(['A','B','C','D'] as $opt)
                            @if(isset($question['option_' . $opt]))
                            <li class="list-group-item
                                @if(in_array($opt, explode(',', $correctAnswer))) list-group-item-success @endif
                                @if(in_array($opt, explode(',', $userAnswer)) && !$isCorrect) list-group-item-danger @endif
                            ">
                                <strong>{{ $opt }}:</strong> {{ $question['option_' . $opt] }}
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


<!-- </div> -->
@endsection
