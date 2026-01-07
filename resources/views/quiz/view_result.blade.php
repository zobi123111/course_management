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

/* Tabs default (inactive) */
.nav-tabs .nav-link {
    color: #000;
    background-color: #fff;
    border: 1px solid #dee2e6;
    margin-right: 4px;
}

/* Active tab */
.nav-tabs .nav-link.active {
    color: #fff;
    background-color: #0d6efd; /* Bootstrap primary blue */
    border-color: #0d6efd #0d6efd #fff;
}

/* Hover effect */
.nav-tabs .nav-link:hover {
    background-color: #e9f2ff;
    color: #000;
}

/* Keep tab bottom border aligned */
.nav-tabs {
    border-bottom: 1px solid #dee2e6;
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
        <ul class="nav nav-tabs mb-4" id="attemptTabs" role="tablist">
            @foreach($userQuizzes as $index => $attempt)
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link {{ $index === 0 ? 'active' : '' }}"
                        id="attempt-tab-{{ $index }}"
                        data-bs-toggle="tab"
                        data-bs-target="#attempt-{{ $index }}"
                        type="button"
                        role="tab"
                    >
                        {{ $index === 0 ? 'Latest' : 'Attempt ' . $index }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="attemptTabsContent">
            @foreach($userQuizzes as $index => $attempt)
                @php
                    $quizDetails = json_decode($attempt->quiz_details, true);
                @endphp

                <div
                    class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                    id="attempt-{{ $index }}"
                    role="tabpanel"
                >

                    @foreach($quizDetails as $qIndex => $question)
                        @php
                            $userAnswer    = $question['user_answer'] ?? null;
                            $correctAnswer = $question['correct_option'] ?? null;
                            $isCorrect     = false;

                            if ($question['question_type'] === 'sequence') {
                                $isCorrect = array_map('trim', explode(',', $userAnswer ?? ''))
                                        === array_map('trim', explode(',', $correctAnswer ?? ''));
                            } else {
                                $ua = $userAnswer ? explode(',', strtolower($userAnswer)) : [];
                                $ca = $correctAnswer ? explode(',', strtolower($correctAnswer)) : [];
                                sort($ua); sort($ca);
                                $isCorrect = $ua == $ca;
                            }

                            $correctOptions = $correctAnswer ? explode(',', strtoupper($correctAnswer)) : [];
                            $userOptions    = $userAnswer ? explode(',', strtoupper($userAnswer)) : [];
                        @endphp

                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">
                                    {{ $qIndex + 1 }}. {{ $question['question_text'] }}
                                </h5>

                                @if(!empty($question['question_image']))
                                    <div class="text-center mb-3">
                                        <img src="{{ Storage::url($question['question_image']) }}"
                                            class="img-fluid rounded"
                                            style="max-height:120px">
                                    </div>
                                @endif

                                @if(in_array($question['question_type'], ['single_choice','multiple_choice','sequence']))
                                    <ul class="list-group mb-3">
                                        @foreach(['A','B','C','D'] as $opt)
                                            @php $val = $question['option_'.$opt] ?? null; @endphp
                                            @if($val)
                                                <li class="list-group-item
                                                    @if(in_array($opt,$correctOptions)) list-group-item-success
                                                    @elseif(in_array($opt,$userOptions)) list-group-item-danger
                                                    @endif
                                                ">
                                                    <strong>{{ $opt }}:</strong>
                                                    {{ $val }}
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif

                                <p><strong>Correct:</strong> {{ $correctAnswer ?? 'N/A' }}</p>
                                <p><strong>Your Answer:</strong> {{ $userAnswer ?? 'No Answer' }}</p>

                                <p>
                                    <strong>Result:</strong>
                                    @if(!$userAnswer)
                                        <span class="badge bg-secondary">Not Answered</span>
                                    @elseif($isCorrect)
                                        <span class="badge bg-success">Correct</span>
                                    @else
                                        <span class="badge bg-danger">Wrong</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach

                </div>
            @endforeach
        </div>
    @endif

<!-- </div> -->
@endsection
