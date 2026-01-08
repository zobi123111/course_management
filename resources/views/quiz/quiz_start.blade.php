@extends('quiz_layout.app')

@section('title', 'View Quiz')
@section('sub-title', 'Quiz Details')

@section('content')

<style>
    .list-group-item {
        cursor: grab;
        user-select: none;
    }
    .quiz-container {
        max-height: 80vh;
        overflow-y: auto;
    }

    .quiz-nav {
        border-top: 1px solid #ddd;
    }

    #quiz-timer {
        background-color: #007bff;
        color: #fff;
        padding: 8px 15px;
        border-radius: 6px;
        display: inline-block;
        font-weight: 600;
        font-size: 16px;
    }
    .timer_div {
        flex: 0 0 auto;
        width: 16.666667%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    .form-check-label img {
        cursor: pointer;
        border: 2px solid transparent;
    }

    .form-check-input:checked + .form-check-label img,
    .form-check-input:checked + .form-check-label {
        border-color: #0d6efd;
    }

    .sidebar-item {
        background-color: #ffffff !important;
        transition: background-color 0.3s ease;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        color: #000 !important;
        display: inline-block;
    }

    .sidebar-item.answered {
        background-color: #c8e6c9 !important;
    }

    .sidebar-item.unanswered {
        background-color: #fff3cd !important;
    }

    .sidebar-item.current-question {
        background-color: #007bff !important;
        color: #fff !important;
    }
    .quiz_type_box {
        color: #000000;
        background: #d4e9ff;
        padding: 10px;
        font-size: 20px;
        text-align: center;
    }

    .form-check-label {
        cursor: pointer;
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
                        <td>
                            <span class="badge bg-{{ $quiz->status == 'published' ? 'success' : 'secondary' }}">
                                {{ ucfirst($quiz->status) }}
                            </span>
                        </td>
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
                    @if($quiz->quiz_type != 'normal')
                    <tr>
                        <th>Quiz Type</th>
                        <td>{{ ucfirst($quiz->quiz_type) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        <div class="col-md-2 timer_div">
            <div id="quiz-timer"></div>
        </div>
        @if($quiz->quiz_type != 'normal')
            <div class="col-md-2">
                <div><h3 class="quiz_type_box">{{ ucfirst($quiz->quiz_type) }} Quiz </h3></div>
            </div>
        @endif
    </div>
</div>

<div class="card pt-4 mt-5">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Quiz Questions</h2>
        </div>

        <form id="quizForm" method="POST" action="{{ route('quiz.saveAnswer') }}">
            @csrf
            <div class="quiz-container position-relative">

                @php
                    $questionIndex = 0;
                    $totalQuestions = $questions->count();
                @endphp

               @foreach($quiz->quizQuestions as $quizQuestion)
                    @foreach($quizQuestion->question_id as $qid)

                        @php
                            $q = $questions[$qid] ?? null;
                        @endphp

                        @if(!$q)
                            @continue
                        @endif

                        <div class="question-box {{ $questionIndex === 0 ? 'active' : 'd-none' }}"
                            data-index="{{ $questionIndex }}">

                            <h5>Q{{ $questionIndex + 1 }}. {{ $q->question_text }}</h5>

                            @if($q->question_image)
                                <div class="text-center mb-3">
                                    <img src="{{ Storage::url($q->question_image) }}"
                                        class="img-fluid rounded"
                                        style="max-height:150px">
                                </div>
                            @endif

                            <input type="hidden" value="{{ $q->id }}" class="question-id">

                            {{-- SINGLE CHOICE --}}
                            @if($q->question_type === 'single_choice')
                                @foreach(['A','B','C','D'] as $opt)
                                    @php
                                        $val = $q->{'option_'.$opt};
                                        $inputId = 'q'.$q->id.'_radio_'.$opt;
                                    @endphp
                                    @if($val)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input"
                                                type="radio"
                                                id="{{ $inputId }}"
                                                name="answer_{{ $q->id }}"
                                                value="{{ $opt }}">
                                            <label class="form-check-label" for="{{ $inputId }}">
                                                {{ $val }}
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            {{-- MULTIPLE CHOICE --}}
                            @if($q->question_type === 'multiple_choice')
                                @foreach(['A','B','C','D'] as $opt)
                                    @php
                                        $val = $q->{'option_'.$opt};
                                        $inputId = 'q'.$q->id.'_check_'.$opt;
                                    @endphp
                                    @if($val)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                id="{{ $inputId }}"
                                                value="{{ $opt }}">
                                            <label class="form-check-label" for="{{ $inputId }}">
                                                {{ $val }}
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            {{-- TEXT --}}
                            @if($q->question_type === 'text')
                                <textarea class="form-control"
                                        rows="3"
                                        placeholder="Type your answer"></textarea>
                            @endif

                            {{-- SEQUENCE --}}
                            @if($q->question_type === 'sequence')
                                <ul class="list-group sequence-list" data-question="{{ $q->id }}">
                                    @foreach(['A','B','C','D'] as $opt)
                                        @php $val = $q->{'option_'.$opt}; @endphp
                                        @if($val)
                                            <li class="list-group-item"
                                                data-option="{{ $opt }}">
                                                {{ $val }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                                <input type="hidden" class="sequence-answer">
                            @endif

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button"
                                        class="btn btn-secondary prev-btn"
                                        {{ $questionIndex === 0 ? 'disabled' : '' }}>
                                    Previous
                                </button>

                                @if($questionIndex < $totalQuestions - 1)
                                    <button type="button" class="btn btn-primary next-btn">
                                        Next
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-success">
                                        Submit Quiz
                                    </button>
                                @endif
                            </div>
                        </div>

                        @php $questionIndex++; @endphp

                    @endforeach
                @endforeach

            </div>
        </form>
    </div>
</div>

@endsection

@section('js_scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        window.quizSubmitting = window.quizSubmitting || false;
        let currentIndex = 0;
        const form = document.getElementById('quizForm');
        const questions = document.querySelectorAll('.question-box');

        function showQuestion(index) {
            questions.forEach((q, i) => {
                q.classList.toggle('d-none', i !== index);
                q.classList.toggle('active', i === index);
            });
        }

        function saveAnswer(currentQuestionBox) {
            console.log('Saving answer for current question box:', currentQuestionBox);

            const questionIdInput = currentQuestionBox.querySelector('.question-id'); // <- FIXED
            const questionId = questionIdInput ? questionIdInput.value : null;

            if (!questionId) {
                console.error('Question ID not found!', currentQuestionBox);
                return;
            }

            let answer = null;

            const selectedRadio = currentQuestionBox.querySelector('input[type="radio"]:checked');
            if (selectedRadio) {
                answer = selectedRadio.value;
            }
            else if (currentQuestionBox.querySelectorAll('input[type="checkbox"]:checked').length > 0) {
                const selectedCheckboxes = currentQuestionBox.querySelectorAll('input[type="checkbox"]:checked');
                answer = Array.from(selectedCheckboxes).map(cb => cb.value).join(',');
            }
            else if (currentQuestionBox.querySelector('textarea')) {
                const textarea = currentQuestionBox.querySelector('textarea');
                answer = textarea.value.trim();
            }
            else if (currentQuestionBox.querySelector('ul.sequence-list')) {
                const sequenceInput = currentQuestionBox.querySelector('.sequence-answer');
                answer = sequenceInput ? sequenceInput.value : null;
            }

            console.log('Saving Answer:', { questionId, answer });

            if (answer !== null && answer !== '') {
                fetch('{{ route("quiz.saveAnswer") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quiz_id: {{ $quiz->id }},
                        question_id: questionId,
                        answer: answer
                    })
                })
                .then(res => res.json())
                .then(data => console.log('Saved:', data))
                .catch(err => console.error('Error saving answer:', err));
            }
        }


        document.querySelectorAll('.next-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentIndex < questions.length) {
                    const currentQuestionBox = questions[currentIndex];

                    saveAnswer(currentQuestionBox, 'next');

                    currentIndex++;
                    if (currentIndex < questions.length) {
                        showQuestion(currentIndex);
                    }

                    updateSidebar();
                }
            });
        });

        document.querySelectorAll('.prev-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentIndex > 0) {
                    currentIndex--;
                    showQuestion(currentIndex);
                    updateSidebar();
                }
            });
        });

        showQuestion(currentIndex);

        document.querySelectorAll('ul[id^="sequence-"]').forEach(list => {
            const hiddenInput = document.getElementById('sequence-input-' + list.id.split('-')[1]);
            hiddenInput.value = '';

            Sortable.create(list, {
                animation: 150,
                onSort: function () {
                    const order = Array.from(list.children).map(li => li.getAttribute('data-option'));
                    hiddenInput.value = order.join(',');

                    updateSidebar();
                }
            });
        });


        const sidebarItems = document.querySelectorAll('.sidebar-question');

        function updateSidebar() {
            sidebarItems.forEach((item, i) => {

                // 🚨 SAFETY CHECK
                if (!questions[i]) {
                    console.warn('No question box for sidebar index:', i);
                    return;
                }

                item.classList.remove('answered', 'unanswered', 'current-question');

                const questionBox = questions[i];
                const questionInputs = questionBox.querySelectorAll(
                    'input[type="radio"], input[type="checkbox"], textarea'
                );

                let answered = false;

                questionInputs.forEach(input => {
                    if ((input.type === 'radio' || input.type === 'checkbox') && input.checked) answered = true;
                    if (input.tagName === 'TEXTAREA' && input.value.trim() !== '') answered = true;
                });

                const sequenceInput = questionBox.querySelector('.sequence-answer');
                if (sequenceInput && sequenceInput.value.trim() !== '') answered = true;

                if (i === currentIndex) {
                    item.classList.add('current-question');
                } else if (answered) {
                    item.classList.add('answered');
                } else if (i < currentIndex) {
                    item.classList.add('unanswered');
                }
            });
        }
        
        updateSidebar();

        sidebarItems.forEach((item, index) => {
            item.addEventListener('click', () => {

                const currentQuestionBox = questions[currentIndex];
                saveAnswer(currentQuestionBox);

                currentIndex = index;
                questions.forEach((q, i) => q.classList.toggle('d-none', i !== index));
                questions[index].classList.add('active');
                updateSidebar();
            });
        });

        questions.forEach(q => {
            q.addEventListener('input', updateSidebar);
            q.addEventListener('change', updateSidebar);
        });
    });
</script>

@if($quiz->quiz_type == 'normal')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.quizSubmitting = window.quizSubmitting || false;

            const quizDuration = {{ $quiz->duration }} * 60;
            const quizId = "{{ $quiz->id }}";
            const form = document.getElementById('quizForm');
            const timerDisplay = document.getElementById('quiz-timer');

            timerDisplay.style.background = "#007bff";
            timerDisplay.style.color = "#fff";
            timerDisplay.style.padding = "8px 15px";
            timerDisplay.style.borderRadius = "6px";
            timerDisplay.style.display = "inline-block";
            timerDisplay.style.fontWeight = "600";

            let startTime = localStorage.getItem(`quiz_${quizId}_start_time`);
            if (!startTime) {
                startTime = Date.now();
                localStorage.setItem(`quiz_${quizId}_start_time`, startTime);
            } else {
                startTime = parseInt(startTime);
            }

            function getRemainingTime() {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const remaining = quizDuration - elapsed;
                return remaining > 0 ? remaining : 0;
            }

            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }

            const timerInterval = setInterval(() => {
                const remaining = getRemainingTime();

                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    window.quizSubmitting = true;
                    try { localStorage.removeItem(`quiz_${quizId}_start_time`); } catch(e){}
                    alert("Time is up! Your quiz will now be submitted.");
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                } else {
                    timerDisplay.textContent = `⏱ Time Left: ${formatTime(remaining)}`;
                }
            }, 1000);

            window.addEventListener('beforeunload', function (e) {
                if (window.quizSubmitting) return;

                const remaining = getRemainingTime();

                if (remaining > 0) {
                    const message = "Your quiz will be submitted if you leave. Are you sure you want to exit?";
                    e.preventDefault();
                    e.returnValue = message;

                    setTimeout(() => {
                        if (confirm(message)) {
                            try { localStorage.removeItem(`quiz_${quizId}_start_time`); } catch(e){}
                            window.quizSubmitting = true;
                            form.dispatchEvent(new Event('submit', { cancelable: true }));
                        }
                    }, 0);

                    return message;
                }
            });
        });
    </script>
@else
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('quiz-timer').style.display = "none";
        });
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('quizForm');


        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const questions = document.querySelectorAll('.question-box');
            const lastQuestionBox = questions[questions.length - 1];

            let answer = null;
            const questionIdInput = lastQuestionBox.querySelector('.question-id');
            const questionId = questionIdInput ? questionIdInput.value : null;

            if (!questionId) return;

            const selectedRadio = lastQuestionBox.querySelector('input[type="radio"]:checked');
            if (selectedRadio) answer = selectedRadio.value;

            else if (lastQuestionBox.querySelectorAll('input[type="checkbox"]:checked').length > 0) {
                const selectedCheckboxes = lastQuestionBox.querySelectorAll('input[type="checkbox"]:checked');
                answer = Array.from(selectedCheckboxes).map(cb => cb.value).join(',');
            }

            else if (lastQuestionBox.querySelector('textarea')) {
                const textarea = lastQuestionBox.querySelector('textarea');
                answer = textarea.value.trim() || null;
            }

            else if (lastQuestionBox.querySelector('ul.sequence-list')) {
                const sequenceInput = lastQuestionBox.querySelector('.sequence-answer');
                answer = sequenceInput ? sequenceInput.value : null;
            }

            /* -------------------------------
            NO ANSWER → SUBMIT QUIZ ONLY
            --------------------------------*/

            if (!answer) {
                return fetch('{{ route("quiz.saveFinalData") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ quiz_id: '{{ $quiz->id }}' })
                })
                .then(res => res.json())
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Quiz Submitted!',
                    }).then(() => {
                        window.quizSubmitting = true;
                        try {
                            localStorage.removeItem(`quiz_{{ $quiz->id }}_start_time`);
                        } catch (e) {}

                        window.location.href = '{{ route("quiz.index") }}';
                    });

                });

            }
            /* -------------------------------
            ANSWER EXISTS → SAVE THEN SUBMIT
            --------------------------------*/
            else {
                fetch('{{ route("quiz.saveAnswer") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quiz_id: '{{ $quiz->id }}',
                        question_id: questionId,
                        answer: answer,
                        answertype: 'submitquiz'
                    })
                })
                .then(() => fetch('{{ route("quiz.saveFinalData") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ quiz_id: '{{ $quiz->id }}' })
                }))
                .then(() => finishQuiz())
                .catch(() => Swal.fire('Error', 'Submission failed', 'error'));
            }

            function finishQuiz() {
                Swal.fire({
                    icon: 'success',
                    title: 'Quiz Submitted!'
                }).then(() => {
                    window.quizSubmitting = true;
                    try { localStorage.removeItem(`quiz_{{ $quiz->id }}_start_time`); } catch(e){}
                    window.location.href = '{{ route("quiz.index") }}';
                });
            }
        });
    });
</script>


@endsection
