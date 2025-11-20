<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <!-- <ul class="sidebar-nav" id="sidebar-nav">
        @php $questionCount = count($quiz->quizQuestions); @endphp

        <h4 class="question-count text-center mb-3">{{ $questionCount }} Questions</h4>
        @foreach($quiz->quizQuestions as $index => $question_)
            <li class="sidebar-question {{ $index === 0 ? 'current-question' : '' }}" data-index="{{ $index }}">
                <div class="left-section">
                    <span class="question-number">{{ $index + 1 }}</span>
                </div>
                <span class="question-tick" style="display:none;">✔</span>
            </li>
        @endforeach
    </ul> -->
    <ul class="sidebar-nav grid-layout" id="sidebar-nav">
        @php $questionCount = count($quiz->quizQuestions); @endphp

        <h4 class="question-count text-center mb-3">{{ $questionCount }} Questions</h4>

        @foreach($quiz->quizQuestions as $index => $question_)
            <li class="sidebar-question {{ $index === 0 ? 'current-question' : '' }}" data-index="{{ $index }}">
                <div class="left-section">
                    <span class="question-number">{{ $index + 1 }}</span>
                </div>
                <span class="question-tick" style="display:none;">✔</span>
            </li>
        @endforeach

    </ul>

    <div class="course_logo">
        {{-- Optional: Organization logo --}}
    </div>
</aside>

<!-- <style>
    .sidebar-question {
        display: flex;
        align-items: flex-start;
        /* justify-content: space-between; */
        gap: 8px;
        padding: 10px 12px;
        border-bottom: 1px solid #ddd;
        cursor: pointer;
        background-color: #fff;
        color: #333;
        transition: background-color 0.2s, color 0.2s;
    }

    .sidebar-question.current-question {
        background-color: #007bff;
        color: #fff;
    }

    .sidebar-question .left-section {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        flex: 1;
    }

    .sidebar-question .question-number {
        width: 20px;
        text-align: right;
        flex-shrink: 0;
    }

    .sidebar-question .question-text {
        flex: 1;
        word-break: break-word;
        white-space: normal;
        line-height: 1.4;
    }

    .sidebar-question .question-tick {
        color: green;
        font-weight: bold;
        flex-shrink: 0;
    }

    .sidebar-nav li {
        padding: 15px 0 !important;
        list-style: none;
    }
</style> -->

<style>
    .grid-layout {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        list-style: none;
        padding: 0;
    }

    .sidebar-question {
        background-color: #ffffff !important; /* default white */
        transition: background-color 0.3s ease;
        padding: 6px 10px;
        border-radius: 4px;
        border: 1px solid black;
        text-align: center;
        cursor: pointer;
        color: #000 !important;
    }

    .sidebar-question.answered {
        background-color: #c8e6c9 !important; /* light green */
    }

    .sidebar-question.unanswered {
        background-color: #fff3cd !important; /* light yellow */
    }

    .sidebar-question.current-question {
        background-color: #007bff !important; /* blue */
        color: #fff !important;
    }

    .question-count {
        grid-column: 1 / -1;
        text-align: center;
        margin-bottom: 10px;
    }
</style>

