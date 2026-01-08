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

    .info-grid {
        display: grid;
        grid-template-columns: 120px minmax(150px, 300px) 120px minmax(150px, 300px);
        row-gap: 12px;
        column-gap: 20px;
        padding: 10px;
    }

    .label {
        font-weight: 600;
        color: #555;
    }

    .value {
        color: #000;
    }
    
    .text-muted {
        padding: 10px;
        color: #000000 !important;
    }
</style>

<div class="card mb-3 shadow-sm">
    <div class="card-body">

        <div class="info-grid mt-2 mb-2">
            <div class="label"> <strong> Quiz: </strong> </div>
            <div class="value">{{ $quiz->title ?? 'N/A' }}</div>

            <div class="label"> <strong> Course: </strong> </div>
            <div class="value">{{ $quiz->course->course_name ?? 'N/A' }}</div>

            <div class="label"> <strong> Duration: </strong> </div>
            <div class="value">{{ $quiz->duration }} mins</div>

            <div class="label"> <strong> Passing: </strong> </div>
            <div class="value">{{ $quiz->passing_score }}%</div>
        </div>

        <div class="fw-bold text-muted mb-2">
            <strong>Latest Attempt Result : </strong>
        </div>

        <div class="info-grid">
            <div class="label"> <strong> Marks: </strong> </div>
            <div class="value">
                {{ $quizAttempt->score !== null ? $quizAttempt->score.'%' : 'N/A' }}
            </div>

            <div class="label"> <strong> Result: </strong> </div>
            <div class="value">
                @if ($quizAttempt->result)
                    <span class="badge bg-{{ $quizAttempt->result === 'pass' ? 'success' : 'danger' }}">
                        {{ ucfirst($quizAttempt->result) }}
                    </span>
                @else
                    N/A
                @endif
            </div>

            <div class="label"> <strong> Time Taken: </strong> </div>
            <div class="value">
                @if($quizAttempt->started_at && $quizAttempt->submitted_at)
                    @php
                        $seconds = \Carbon\Carbon::parse($quizAttempt->started_at)
                            ->diffInSeconds(\Carbon\Carbon::parse($quizAttempt->submitted_at));
                        $minutes = floor($seconds / 60);
                        $remainingSeconds = $seconds % 60;
                    @endphp

                    {{ $minutes > 0 ? "$minutes mins $remainingSeconds secs" : "$remainingSeconds secs" }}
                @else
                    N/A
                @endif
            </div>
        </div>

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
