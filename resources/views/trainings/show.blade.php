<!-- @section('title', 'Training Event') -->
@section('sub-title', 'Training Event')
@extends('layout.app')
@section('content')

<style>
    .accordion-button::after {
        flex-shrink: 0;
        width: 1.25rem;
        height: 1.25rem;
        margin-left: auto;
        content: "";
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23212529'%3e%3cpath d='M8 1.5a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5a.5.5 0 0 1 .5-.5z'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-size: 1.25rem;
        transition: transform .2s ease-in-out;
    }

    .accordion-button[aria-expanded="true"]::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23212529'%3e%3cpath d='M2 7.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z'/%3e%3c/svg%3e");
    }


    button.accordion-button.collapsed {
        font-size: 18px;
        color: #000;
        font-weight: 600;
    }

    .custom-box {
        background-color: #ffffff;
        padding: 5px 10px 5px;
        border-radius: 5px;
        display: flex;
        box-shadow: 0px 2px 20px rgba(1, 41, 112, 0.1);
        justify-content: space-between;
        border: 1px solid #ddd;
        align-items: center;
        white-space: nowrap;
        flex-direction: row;
        /* flex-wrap: wrap; */
        gap: 20px;
    }

    .custom-box .header-design {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: bold;
        /* flex-wrap: wrap; */
    }



    .custom-box .question-mark {
        color: #4959dc;
    }


    .custom-box .highlight {
        color: #4154f1;
    }

    .accordion-body {
        display: flex;
        flex-direction: column;
        row-gap: 12px;
    }

    .custom-box table {
        border-collapse: collapse;
        border: 1px solid #444;
    }

    .table-container {
        display: flex;
        gap: 10px;
    }

    .custom-box td {
        border: 1px solid #444;
        /* padding: 12px; */
        text-align: center;
    }

    .custom-box td.highlight {
        color: #4154f1;
        font-size: 16px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .custom-box {
            flex-direction: column;
            align-items: flex-start;
            white-space: normal;
            padding: 10px;
        }

        .custom-box .header {
            flex-direction: row;
            height: fit-content;
        }

        .custom-box .table-container {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }

        .custom-box table {
            width: 100%;
        }

        .custom-box td {
            padding: 8px;
        }

        .custom-radio {
            display: inline-block;
            font-size: 15px;
            padding: 7px 6px;
            overflow: hidden;
            white-space: nowrap;
        }
    }


    table {
        border-collapse: collapse;
        width: 100%;
    }


    td {
        padding: 0;
        margin: 0;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s ease-in-out;
        border: 1px solid #444;
        position: relative;
    }


    .radio-label input[type="radio"] {
        display: none;
        padding: 14px;
    }


    .radio-label {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        /* / padding: 12px;/ font-weight: 600; */
        text-align: center;
        transition: background-color 0.3s ease-in-out;
    }

    .radio-label {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .custom-radio {
        display: inline-block;
        font-size: 16px;
        padding: 10px 15px;
        overflow: hidden;
        white-space: nowrap;
    }

    .custom-radio input {
        display: none;
    }

    .radio-label input:checked+.custom-radio {
        background-color: #4154f1;
        color: white;
        font-weight: bold;
    }

    .custom-radio {
        display: inline-block;
        font-size: 16px;
        padding: 7px 13px;
        overflow: hidden;
        white-space: nowrap;
    }

    .btn-container {
        display: flex;
        gap: 15px;
        justify-content: end;
    }

    .btn {
        padding: 7px 18px;
        border: none;
        font-size: 13px;
        /* font-weight: bold; */
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        text-transform: uppercase;
    }

    .btn-save {
        background-color: #4154f1;
        color: white;
    }

    .btn-save:hover {
        background-color: #218838;
    }

    .btn-cancel {
        background-color: #212529;
        color: white;
    }

    .btn-cancel:hover {
        background-color: #c82333;
    }

    .btn-incomplete {
        background-color: #d82100;
        color: white;
    }

    .btn-incomplete:hover {
        background-color: #e06b0c;
    }

    .custom-radio {
        display: inline-block;
        font-size: 16px;
        padding: 7px 13px;
        overflow: hidden;
        white-space: nowrap;
    }

    .assessment-wrapper {
        /* / max-width: 700px; / */
        width: 100%;
        /* / background: #111; / */
        padding: 20px;
        border-radius: 8px;
    }

    .assessment-wrapper h2 {
        margin-bottom: 15px;
        font-size: 22px;
        /* / text-align: center; / */
        font-weight: 600;
        color: #4a5cf2;
    }

    .assessment-wrapper .section {
        margin-bottom: 20px;
        display: flex;
        gap: 30px;
    }

    .assessment-wrapper .result-section {
        display: flex;
        align-items: center;
        gap: 43px;
    }

    .assessment-wrapper .result-section label {
        white-space: nowrap;
    }

    .assessment-wrapper .buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .assessment-wrapper .option {
        background: #222;
        color: white;
        border: 1px solid white;
        padding: 10px 15px;
        cursor: pointer;
        border-radius: 5px;
    }

    .assessment-wrapper .selected {
        background: #4154f1;
        color: #ffffff;
    }

    .assessment-wrapper textarea {
        width: 100%;
        height: 100px;
        background: #ffffff;
        /* / border: none; / */
        color: white;
        padding: 10px;
        border-radius: 5px;
    }

    .assessment-wrapper .activate {
        background: #000;
        color: white;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }


    @media (max-width: 600px) {
        .assessment-wrapper .result-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .assessment-wrapper .buttons {
            flex-direction: column;
            width: 100%;
        }

        .assessment-wrapper .option {
            width: 100%;
            text-align: center;
        }
    }

    .assessment-wrapper .activate:hover {
        background: #4154f1;
    }
</style>

<div class="card">
    <div class="card-body">
        <form action="#">
            <h5 class="card-title">{{ $trainingEvent?->course?->course_name }}</h5>

            <!-- Default Tabs -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="overview" aria-controls="overview" aria-selected="false" tabindex="-1">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link " id="Lesson-tab" data-bs-toggle="tab" data-bs-target="#Lesson" type="button" role="tab" aria-controls="Lesson" aria-selected="true">Lesson Plan</button>
                </li>
                @foreach($groupUsers as $user)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="student-tab-{{ $user->id }}" data-bs-toggle="tab" data-bs-target="#student-{{ $user->id }}" type="button" role="tab" aria-controls="contact" aria-selected="false">
                        {{ $user->fname }} {{ $user->lname }}
                    </button>
                </li>
                @endforeach
            </ul>
            <div class="tab-content pt-2" id="myTabContent">
                <div class="tab-pane fade p-3  active show" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                    <div class="card shadow-sm p-3">
                        <h4 class="mb-3">Training Event Overview</h4>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Course Name:</strong> {{ $trainingEvent->course->course_name ?? 'N/A' }}
                            </div>
                            <div class="col-md-6">
                                <strong>Instructor:</strong>
                                {{ optional($trainingEvent->instructor)->fname }} {{ optional($trainingEvent->instructor)->lname }}
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Start Time:</strong> {{ date('h:i A', strtotime($trainingEvent->start_time)) }}
                            </div>
                            <div class="col-md-6">
                                <strong>End Time:</strong> {{ date('h:i A', strtotime($trainingEvent->end_time)) }}
                            </div>
                        </div>

                        <div class="mt-3">
                            <strong>Students:</strong>
                            <ul class="list-group mt-2">
                                @forelse($groupUsers as $user)
                                <li class="list-group-item">
                                    {{ $user->fname }} {{ $user->lname }}
                                </li>
                                @empty
                                <li class="list-group-item text-muted">No students assigned</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="Lesson" role="tabpanel" aria-labelledby="Lesson-tab">
                    <div class="card-body">
                        @if($courseLessons->isNotEmpty())
                        <div class="accordion accordion-flush" id="faq-group-2">
                            @foreach($courseLessons as $lesson)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" data-bs-toggle="collapse"
                                        data-bs-target="#lesson-{{ $lesson->id }}"
                                        aria-expanded="false">
                                        {{ $lesson->lesson_title }}
                                    </button>
                                </h2>
                                <div id="lesson-{{ $lesson->id }}" class="accordion-collapse collapse" data-bs-parent="#faq-group-2">
                                    <div class="accordion-body">
                                        @if($lesson->subLessons->isNotEmpty())
                                        @foreach($lesson->subLessons as $sublesson)
                                        <div class="custom-box">
                                            <div class="header">
                                                <span class="rmk">RMK</span>
                                                <span class="question-mark">?</span>
                                                <span class="title">{{ $sublesson->title }}</span>
                                            </div>
                                            <div class="table-container">
                                                <div class="main-tabledesign">
                                                    <table>
                                                        @foreach($groupUsers as $user)
                                                        <h5>{{ $user->fname }} {{ $user->lname }}</h5>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <label class="radio-label">
                                                                        <input type="radio" name="training-status-{{ $sublesson->id }}-user-{{ $user->id }}" value="N/A">
                                                                        <span class="custom-radio">N/A</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label class="radio-label">
                                                                        <input type="radio" name="training-status-{{ $sublesson->id }}-user-{{ $user->id }}" value="Further training required">
                                                                        <span class="custom-radio highlight">Further training required</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label class="radio-label">
                                                                        <input type="radio" name="training-status-{{ $sublesson->id }}-user-{{ $user->id }}" value="Competent">
                                                                        <span class="custom-radio competent">Competent</span>
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    @endforeach
                                                </div>
                                            </div>

                                        </div>
                                        @endforeach
                                        @else
                                        <p class="text-muted">No sub-lessons available.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" data-bs-toggle="collapse"
                                        data-bs-target="#comptency"
                                        aria-expanded="false">
                                        Overall Competency Garding
                                    </button>
                                </h2>
                            </div>
                            <div id="comptency" class="accordion-collapse collapse" data-bs-parent="#faq-group-2">
                                <div class="accordion-body">
                                    @foreach($courseLessons as $lesson)
                                    <div class="custom-box">
                                        <div class="header">
                                            <span class="rmk">RMK</span>
                                            <span class="question-mark">?</span>
                                            <span class="title"><span class="highlight">{{ $lesson->lesson_title }}</span></span>
                                        </div>

                                        <!-- Scrollable Container -->
                                        <div class="table-container">
                                            <div class="main-tabledesign">
                                                @foreach($groupUsers as $user)
                                                <h5>{{ $user->fname }} {{ $user->lname }}</h5>
                                                <table>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <label class="radio-label">
                                                                    <input type="radio" name="training-status1" value="1">
                                                                    <span class="custom-radio">1</span>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <label class="radio-label">
                                                                    <input type="radio" name="training-status1" value="2">
                                                                    <span class="custom-radio">2</span>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <label class="radio-label">
                                                                    <input type="radio" name="training-status1" value="3">
                                                                    <span class="custom-radio">3</span>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <label class="radio-label">
                                                                    <input type="radio" name="training-status1" value="4">
                                                                    <span class="custom-radio">4</span>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <label class="radio-label">
                                                                    <input type="radio" name="training-status1" value="5">
                                                                    <span class="custom-radio">5</span>
                                                                </label>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="accordion accordion-flush" id="faq-group-2">
                            <p class="text-muted">No lessons available for this course.</p>
                        </div>
                        @endif
                    </div>
                </div>
                @foreach($groupUsers as $user)
                <div class="tab-pane fade" id="student-{{ $user->id }}" role="tabpanel" aria-labelledby="student-tab">
                    <div class="assessment-wrapper">
                        <h2>Overall assessment</h2>
                        <div class="row mb-3">
                            <label for="result" class="col-sm-2 col-form-label">Result</label>
                            <div class="col-sm-10 buttons">
                                <button class="option">Competent - Ready for OPC/LPC</button>
                                <button class="option selected">Further training required</button>
                                <button class="option">Incomplete</button>
                                <button class="option">Stand-In</button>
                            </div>
                        </div>

                        <div class="row mb-3 remark-section">
                            <label for="remark" class="col-sm-2 col-form-label">Remark</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" style="height: 100px" placeholder="Enter your remarks here..."></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">For manager's attention</label>
                            <div class="col-sm-10 buttons">
                                <button class="activate">Activate</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div><!-- End Default Tabs -->

            <div class="btn-container">
                <button class="btn btn-save">Save</button>
                <button class="btn btn-cancel">Cancel</button>
                <button class="btn btn-incomplete">Incomplete</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js_scripts')

<script>
    $(document).ready(function() {

    });
</script>

@endsection