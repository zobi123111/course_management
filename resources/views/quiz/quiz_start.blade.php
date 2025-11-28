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
        max-height: 80vh; /* or whatever fits your layout */
        overflow-y: auto;
    }

    .quiz-nav {
        border-top: 1px solid #ddd; /* optional separator */
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

    /* .sidebar-item {
        transition: background-color 0.3s ease;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
    } */

    .sidebar-item {
        background-color: #ffffff !important; /* default white */
        transition: background-color 0.3s ease;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        color: #000 !important;
        display: inline-block;
    }

    /* Light green for answered */
    .sidebar-item.answered {
        background-color: #c8e6c9 !important;
    }

    /* Light yellow for not answered */
    .sidebar-item.unanswered {
        background-color: #fff3cd !important;
    }

    /* Blue for current question */
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
               @foreach($quiz->quizQuestions as $index => $quizQuestion)
    @php
        $q = $quizQuestion->question; // Shortcut for TopicQuestion
    @endphp

    <div class="question-box {{ $index === 0 ? 'active' : 'd-none' }}" data-index="{{ $index }}">

        <!-- Question Text -->
        <h5>Q{{ $index + 1 }}. {{ $q->question_text }}</h5>

        <input type="hidden" name="questions[{{ $quizQuestion->id }}][id]" value="{{ $quizQuestion->id }}">

        {{-- Single Choice --}}
        @if($q->question_type === 'single_choice')
            @foreach(['A', 'B', 'C', 'D'] as $option)
                @php $optionValue = $q->{'option_' . $option}; @endphp

                @if(!empty($optionValue))
                    @php $inputId = 'q-' . $quizQuestion->id . '-opt-' . $option; @endphp
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                            id="{{ $inputId }}"
                            name="questions[{{ $quizQuestion->id }}][answer]"
                            value="{{ $option }}">
                        <label class="form-check-label" for="{{ $inputId }}">{{ $optionValue }}</label>
                    </div>
                @endif
            @endforeach
        @endif


        {{-- Multiple Choice --}}
        @if($q->question_type === 'multiple_choice')
            @foreach(['A', 'B', 'C', 'D'] as $option)
                @php $optionValue = $q->{'option_' . $option}; @endphp

                @if(!empty($optionValue))
                    @php $inputId = 'q-' . $quizQuestion->id . '-opt-' . $option; @endphp
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                            id="{{ $inputId }}"
                            name="questions[{{ $quizQuestion->id }}][answer][]"
                            value="{{ $option }}">
                        <label class="form-check-label" for="{{ $inputId }}">{{ $optionValue }}</label>
                    </div>
                @endif
            @endforeach
        @endif


        {{-- Text Type --}}
        @if($q->question_type === 'text')
            <textarea class="form-control mt-2"
                name="questions[{{ $quizQuestion->id }}][answer]"
                rows="3"
                placeholder="Type your answer here..."></textarea>
        @endif


        {{-- Sequence Type --}}
        @if($q->question_type === 'sequence')
            <p>Drag and drop to arrange the options in correct order:</p>

            <ul id="sequence-{{ $quizQuestion->id }}" class="list-group">
                @foreach(['A', 'B', 'C', 'D'] as $option)
                    @php $optionValue = $q->{'option_' . $option}; @endphp

                    @if(!empty($optionValue))
                        <li class="list-group-item" data-option="{{ $option }}">
                            {{ $optionValue }}
                        </li>
                    @endif
                @endforeach
            </ul>

            <input type="hidden"
                name="questions[{{ $quizQuestion->id }}][answer]"
                id="sequence-input-{{ $quizQuestion->id }}">
        @endif


        <!-- Navigation -->
        <div class="mt-4 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary prev-btn"
                {{ $index === 0 ? 'disabled' : '' }}>
                Previous
            </button>

            @if($index < count($quiz->quizQuestions) - 1)
                <button type="button" class="btn btn-primary next-btn">
                    Next Question
                </button>
            @else
                <button type="submit" class="btn btn-success">
                    Submit Quiz
                </button>
            @endif
        </div>

    </div>
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
        // global flag to indicate we're intentionally submitting/navigating
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

            // Get question ID
            const questionIdInput = currentQuestionBox.querySelector('input[name$="[id]"]');
            const questionId = questionIdInput ? questionIdInput.value : null;

            if (!questionId) {
                console.error('Question ID not found!');
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
            else if (currentQuestionBox.querySelector('ul[id^="sequence-"]')) {
                const sequenceInput = currentQuestionBox.querySelector('input[id^="sequence-input-"]');
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

                    // saveAnswer(currentQuestionBox);
                    saveAnswer(currentQuestionBox, 'next');

                    currentIndex++;
                    if (currentIndex < questions.length) {
                        showQuestion(currentIndex);
                    }

                    updateSidebar();
                }
            });
        });

        // document.querySelectorAll('.next-btn').forEach(btn => {
        //     btn.addEventListener('click', () => {
        //         const currentQuestionBox = questions[currentIndex];
        //         saveAnswer(currentQuestionBox, 'next');

        //         currentIndex++;
        //         if (currentIndex < questions.length) {
        //             showQuestion(currentIndex);
        //         } else {
        //             // If last question, hide next button
        //             btn.style.display = 'none';
        //         }
        //     });
        // });

        // Submit button
        // form.addEventListener('submit', function (e) {
        //     e.preventDefault();

        //     questions.forEach(currentQuestionBox => {
        //         saveAnswer(currentQuestionBox, 'submit');
        //     });

        //     Swal.fire({
        //         icon: 'success',
        //         title: 'Quiz Submitted!',
        //         text: 'All answers have been saved.',
        //         confirmButtonText: 'OK'
        //     }).then(() => {
        //         // Prevent beforeunload from showing when we intentionally redirect
        //         window.quizSubmitting = true;
        //         try { localStorage.removeItem(`quiz_{{ $quiz->id }}_start_time`); } catch(e){}
        //         window.location.href = '{{ route("quiz.index") }}';
        //     });
        // });

        // document.querySelectorAll('.next-btn').forEach(btn => {
        //     btn.addEventListener('click', () => {
        //         if (currentIndex < questions.length - 1) {
        //             currentIndex++;
        //             showQuestion(currentIndex);
        //             updateSidebar();
        //         }
        //     });
        // });

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

        // document.querySelectorAll('ul[id^="sequence-"]').forEach(list => {
        //     const hiddenInput = document.getElementById('sequence-input-' + list.id.split('-')[1]);
        //     Sortable.create(list, {
        //         animation: 150,
        //         onSort: function () {
        //             const order = Array.from(list.children).map(li => li.getAttribute('data-option'));
        //             hiddenInput.value = order.join(',');
        //         }
        //     });

        //     hiddenInput.value = Array.from(list.children).map(li => li.getAttribute('data-option')).join(',');
        // });

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
        // const questions = document.querySelectorAll('.question-box');

        // let currentIndex = 0;

        // function updateSidebar() {
        //     sidebarItems.forEach((item, i) => {
        //         item.classList.toggle('current-question', i === currentIndex);

        //         const questionInputs = questions[i].querySelectorAll('input[type="radio"], input[type="checkbox"], textarea');
        //         let answered = false;

        //         questionInputs.forEach(input => {
        //             if ((input.type === 'radio' || input.type === 'checkbox') && input.checked) answered = true;
        //             if (input.tagName === 'TEXTAREA' && input.value.trim() !== '') answered = true;
        //         });

        //         const tick = item.querySelector('.question-tick');
        //         tick.style.display = answered ? 'inline' : 'none';
        //     });
        // }

        function updateSidebar() {
            sidebarItems.forEach((item, i) => {
                item.classList.remove('answered', 'unanswered', 'current-question');

                const questionInputs = questions[i].querySelectorAll('input[type="radio"], input[type="checkbox"], textarea');
                let answered = false;

                questionInputs.forEach(input => {
                    if ((input.type === 'radio' || input.type === 'checkbox') && input.checked) answered = true;
                    if (input.tagName === 'TEXTAREA' && input.value.trim() !== '') answered = true;
                });

                const sequenceInput = questions[i].querySelector('input[type="hidden"][id^="sequence-input-"]');
                if (sequenceInput && sequenceInput.value.trim() !== '') answered = true;

                if (i === currentIndex) {
                    item.classList.add('current-question');
                } else if (answered) {
                    item.classList.add('answered');
                } else if (i < currentIndex) {
                    item.classList.add('unanswered');
                }
                // else → default white (not yet reached)
            });
        }
        
        updateSidebar();

        // Click on sidebar question to jump
        sidebarItems.forEach((item, index) => {
            item.addEventListener('click', () => {
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
            // ensure global flag exists in this scope too
            window.quizSubmitting = window.quizSubmitting || false;

            const quizDuration = {{ $quiz->duration }} * 60; // convert minutes to seconds
            const quizId = "{{ $quiz->id }}";
            const form = document.getElementById('quizForm');
            const timerDisplay = document.getElementById('quiz-timer');

            // Optional basic styling — remove if you already styled in Blade
            timerDisplay.style.background = "#007bff";
            timerDisplay.style.color = "#fff";
            timerDisplay.style.padding = "8px 15px";
            timerDisplay.style.borderRadius = "6px";
            timerDisplay.style.display = "inline-block";
            timerDisplay.style.fontWeight = "600";

            // Load or create quiz start time
            let startTime = localStorage.getItem(`quiz_${quizId}_start_time`);
            if (!startTime) {
                startTime = Date.now();
                localStorage.setItem(`quiz_${quizId}_start_time`, startTime);
            } else {
                startTime = parseInt(startTime);
            }

            // Calculate remaining time
            function getRemainingTime() {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const remaining = quizDuration - elapsed;
                return remaining > 0 ? remaining : 0;
            }

            // Format time as mm:ss
            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            }

            // Update timer every second
            const timerInterval = setInterval(() => {
                const remaining = getRemainingTime();

                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    // mark as intentionally submitting so beforeunload doesn't prompt
                    window.quizSubmitting = true;
                    try { localStorage.removeItem(`quiz_${quizId}_start_time`); } catch(e){}
                    alert("Time is up! Your quiz will now be submitted.");
                    // Dispatch the submit event so our JS handler runs (prevents real POST to start route)
                    form.dispatchEvent(new Event('submit', { cancelable: true }));
                } else {
                    timerDisplay.textContent = `⏱ Time Left: ${formatTime(remaining)}`;
                }
            }, 1000);

            // Warn before closing tab
            window.addEventListener('beforeunload', function (e) {
                // don't show prompt when we're intentionally submitting/navigating
                if (window.quizSubmitting) return;

                const remaining = getRemainingTime();

                if (remaining > 0) {
                    const message = "Your quiz will be submitted if you leave. Are you sure you want to exit?";
                    e.preventDefault();
                    e.returnValue = message;

                    // If they click OK, auto-submit
                    setTimeout(() => {
                        if (confirm(message)) {
                            try { localStorage.removeItem(`quiz_${quizId}_start_time`); } catch(e){}
                            // mark submitting so we don't re-prompt
                            window.quizSubmitting = true;
                            // Dispatch submit event to trigger AJAX submit handler instead of native submit
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
            const questionIdInput = lastQuestionBox.querySelector('input[name$="[id]"]');
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

            else if (lastQuestionBox.querySelector('ul[id^="sequence-"]')) {
                const sequenceInput = lastQuestionBox.querySelector('input[id^="sequence-input-"]');
                answer = sequenceInput ? sequenceInput.value : null;
            }

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
            .then(res => res.json())
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Quiz Submitted!',
                    text: 'Your last answer has been saved.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.quizSubmitting = true;
                    try { localStorage.removeItem(`quiz_{{ $quiz->id }}_start_time`); } catch(e){}
                    window.location.href = '{{ route("quiz.index") }}';
                });
            })
            .catch(err => {
                console.error('Error saving answer:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: 'There was an error submitting your quiz. Please try again.'
                });
            });
        });

        // Intercept form submission
        // form.addEventListener('submit', function (e) {
        //     e.preventDefault();

        //     const questions = document.querySelectorAll('.question-box');
        //     const answersData = [];

        //     questions.forEach(currentQuestionBox => {
        //         const questionIdInput = currentQuestionBox.querySelector('input[name$="[id]"]');
        //         const questionId = questionIdInput ? questionIdInput.value : null;
        //         if (!questionId) return;

        //         let answer = null;

        //         // Single choice (radio)
        //         const selectedRadio = currentQuestionBox.querySelector('input[type="radio"]:checked');
        //         if (selectedRadio) answer = selectedRadio.value;

        //         // Multiple choice (checkbox)
        //         else if (currentQuestionBox.querySelectorAll('input[type="checkbox"]:checked').length > 0) {
        //             const selectedCheckboxes = currentQuestionBox.querySelectorAll('input[type="checkbox"]:checked');
        //             answer = Array.from(selectedCheckboxes).map(cb => cb.value).join(',');
        //         }

        //         // Text input
        //         else if (currentQuestionBox.querySelector('textarea')) {
        //             const textarea = currentQuestionBox.querySelector('textarea');
        //             answer = textarea.value.trim() || null;
        //         }

        //         // Sequence / drag & drop
        //         else if (currentQuestionBox.querySelector('ul[id^="sequence-"]')) {
        //             const sequenceInput = currentQuestionBox.querySelector('input[id^="sequence-input-"]');
        //             answer = sequenceInput ? sequenceInput.value : null;
        //         }

        //         answersData.push({
        //             question_id: questionId,
        //             answer: answer
        //         });
        //     });

        //     // Send all answers via AJAX
        //     fetch('{{ route("quiz.saveAnswer") }}', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
        //         },
        //         body: JSON.stringify({
        //             attempt_id: '{{ $attempt_id ?? "1" }}',
        //             answers: answersData
        //         })
        //     })
        //     .then(res => res.json())
        //     .then(data => {
        //         // Show SweetAlert success
        //         Swal.fire({
        //             icon: 'success',
        //             title: 'Quiz Submitted!',
        //             text: 'Your answers have been saved successfully.',
        //             confirmButtonText: 'OK'
        //         }).then(() => {
        //             // Prevent beforeunload prompt and clear timer start time, then redirect
        //             window.quizSubmitting = true;
        //             try { localStorage.removeItem(`quiz_{{ $quiz->id }}_start_time`); } catch(e){}
        //             // Redirect to quiz.index
        //             window.location.href = '{{ route("quiz.index") }}';
        //         });
        //     })
        //     .catch(err => {
        //         console.error('Error saving answers:', err);
        //         Swal.fire({
        //             icon: 'error',
        //             title: 'Submission Failed',
        //             text: 'There was an error submitting your quiz. Please try again.'
        //         });
        //     });
        // });
    });
</script>


@endsection
