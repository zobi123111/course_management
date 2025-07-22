@section('title', 'View')
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

    .accordion-item {
        border: 0px solid #ebeef4 !important;
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
        border: 0px solid #ddd;
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

    /* .custom-box .highlight {
        color: #4154f1;
    } */

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
    .accordion-button:not(.collapsed) {
            color: #0c63e4;
            background-color: #fff;
            box-shadow: inset 0 0px 0 rgba(0, 0, 0, .125);
        }
        button.accordion-button {
            padding: 1rem 0rem;
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

    /* .radio-label input:checked+.custom-radio {
        background-color: #4154f1;
        color: white;
        font-weight: bold;
    } */

    /* value-specific styles for task grading */
    .radio-label input:checked + .custom-radio.incomplete {
        background-color:  #FFFF00; /* Yellow */
        color: black;
        font-weight: bold;
    }

    .radio-label input:checked + .custom-radio.ftr {
        background-color: #ffc107; /* Amber */
        color: black;
        font-weight: bold;
    }

    .radio-label input:checked + .custom-radio.competent {
        background-color: #008000; /* green */
        color: black;
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
        /* color: white; */
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

    .small-text {
        font-size: 0.85rem;
    }

    button.accordion-button {
    font-size: 18px;
    color: #000 !important;
    font-weight: 600;
    }

    .accordion-flush .accordion-button {
        color: #000 !important;
    }

    .grade-incomplete {
    background-color: #FFFF00;
    color: black;
    font-weight: bold;
    }

    .grade-ftr {
        background-color: #ffc107;
        color: black;
        font-weight: bold;
    }

    .grade-competent {
        background-color: #008000;
        color: black;
        font-weight: bold;
    }
</style>

<div class="card">
    @if(session()->has('message'))
        <div id="successMessage" class="alert alert-success fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            {{ session()->get('message') }}
        </div>
    @endif
    <div class="loader" style="display: none;"></div>
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ $trainingEvent?->course?->course_name }}</h5>
            <div class="ms-3 px-3 py-2 bg-warning text-dark rounded" style="font-size: 0.9rem; max-width: 60%;">
                <strong>NOTE:</strong> Please ensure all grading is completed carefully. Once saved, the training event will be locked.
            </div>
        </div>
        <!-- Default Tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="overview" aria-controls="overview" aria-selected="false" tabindex="-1">Overview</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link " id="Lesson-tab" data-bs-toggle="tab" data-bs-target="#Lesson" type="button" role="tab" aria-controls="Lesson" aria-selected="true">Lesson Plan</button>
            </li>
            @if($trainingEvent?->course?->course_type === 'one_event' && $student)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="student-tab-{{ $student->id }}" data-bs-toggle="tab" data-bs-target="#student-{{ $student->id }}" type="button" role="tab" aria-controls="student-{{ $student->id }}" aria-selected="false">
                        {{ $student->fname }} {{ $student->lname }}
                    </button>
                </li>
            @endif
        </ul>
        <div class="tab-content pt-2" id="myTabContent">
        <div class="tab-pane fade p-3 active show" id="overview" role="tabpanel" aria-labelledby="overview-tab">
            <div class="card shadow-sm p-4 border-0">
                <h4 class="mb-3 text-primary">
                    <i class="fas fa-calendar-alt"></i> Training Event Overview
                </h4>
                @if($trainingEvent->course?->duration_value && $trainingEvent->course?->duration_type)
                    <div class="mb-3">
                        <strong><i class="fas fa-hourglass-half"></i> Course Total Duration:</strong>
                        @php
                            $value = $trainingEvent->course->duration_value;
                            $type = $trainingEvent->course->duration_type;

                            if ($type === 'hours') {
                                $label = $value == 1 ? 'hour' : 'hours';
                            } elseif ($type === 'events') {
                                $label = $value == 1 ? 'event' : 'events';
                            } else {
                                $label = '';
                            }
                        @endphp
                        <span class="badge bg-success text-white">{{ $value }} {{ $label }}</span>
                    </div>
                @endif

                @if($trainingEvent?->course?->course_type === 'one_event' && $trainingEvent->eventLessons->count())
                    @php
                        $eventLesson = $trainingEvent->eventLessons->first();
                    @endphp

                    {{-- ONE EVENT: Pulling from eventLessons --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-book"></i> Course Name:</strong>
                            <span class="badge bg-info text-white">{{ $trainingEvent->course->course_name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-chalkboard-teacher"></i> Instructor:</strong>
                            {{ optional($eventLesson->instructor)->fname }} {{ optional($eventLesson->instructor)->lname }}
                            <small class="text-muted d-block">
                                <i class="bi bi-card-text"></i> License: {{ $eventLesson->instructor_license_number ?? 'N/A' }}
                            </small>
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-toolbox"></i> Resource:</strong>
                            <span class="badge bg-secondary text-white">{{ optional($eventLesson->resource)->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="fas fa-calendar-day"></i> Lesson Date:</strong>
                            {{ date('d-m-Y', strtotime($eventLesson->lesson_date)) }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-clock"></i> Start Time:</strong>
                            {{ date('h:i A', strtotime($eventLesson->start_time)) }}
                        </div>
                        <div class="col-md-4">
                            <strong><i class="fas fa-clock"></i> End Time:</strong>
                            {{ date('h:i A', strtotime($eventLesson->end_time)) }}
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-plane-departure"></i> Departure Airfield:</strong> 
                            {{ $eventLesson->departure_airfield ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-plane-arrival"></i> Destination Airfield:</strong> 
                            {{ $eventLesson->destination_airfield ?? 'N/A' }}
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user"></i> Student:</strong>
                            {{ $trainingEvent->student->fname ?? '' }} {{ $trainingEvent->student->lname ?? '' }}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-id-card"></i> License Number:</strong>
                            {{ $trainingEvent->std_license_number ?? 'N/A' }}
                        </div>
                    </div>

                    @php                    
                        $lessonType = $eventLesson?->lesson?->lesson_type ?? null;
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-6">
                            @if($lessonType === 'groundschool')
                                <strong><i class="fas fa-book-reader"></i> Ground School Time:</strong>
                                <div>
                                    <span>Duration: {{ $trainingEvent->course->groundschool_hours ?? 'N/A' }}</span> |
                                    <span>Credited: {{ $eventLesson->hours_credited ?? '00:00' }}</span>
                                </div>
                            @elseif($lessonType === 'simulator')
                                <strong><i class="fas fa-vr-cardboard"></i> Simulator Time:</strong>
                                <div>
                                    <span>Duration: {{ $trainingEvent->course->simulator_hours ?? 'N/A' }}</span> |
                                    <span>Credited: {{ $eventLesson->hours_credited ?? '00:00' }}</span>
                                </div>
                            @elseif($lessonType === 'flight')
                                <strong><i class="fas fa-plane"></i> Flight Time:</strong>
                                <div>
                                    <span>Credited: {{ $eventLesson->hours_credited ?? '00:00' }}</span>
                                </div>
                            @endif
                            {{-- Custom Time if available --}}
                            @php
                                $customTime = $eventLesson->lesson->customTime ?? null;
                            @endphp

                            @if($customTime)
                                <div class="mt-2">
                                    <strong><i class="fas fa-clock"></i> Custom Time:</strong>
                                    <ul class="ps-3">
                                        <li><strong>Name:</strong> {{ $customTime->name }}</li>
                                        <li><strong>Allotted Hours:</strong> {{ $customTime->given_hours }}</li>
                                        <li><strong>Credited Hours:</strong> {{ $eventLesson->custom_hours_credited ?? '00:00' }}</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- MULTI-LESSON COURSE TYPE --}}
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong><i class="fas fa-book"></i> Course Name:</strong>
                            <span class="badge bg-info text-white">{{ $trainingEvent->course->course_name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        @if($trainingEvent->eventLessons && $trainingEvent->eventLessons->count())
                            <div class="col-md-12">
                                <strong><i class="fas fa-chalkboard-teacher"></i> Instructors & License Numbers:</strong>
                                <ul class="list-group mt-2">
                                    @foreach($trainingEvent->eventLessons as $lesson)
                                        @if(auth()->user()->is_admin || auth()->user()->is_owner || auth()->user()->id === $lesson->instructor_id)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    {{ $lesson->instructor?->fname }} {{ $lesson->instructor?->lname }}
                                                    @if($lesson->lesson?->lesson_title)
                                                        <small class="text-muted d-block">Lesson: {{ $lesson->lesson->lesson_title }}</small>
                                                    @endif
                                                </span>
                                                <span class="badge bg-secondary">
                                                    License: {{ $lesson->instructor_license_number ?? 'N/A' }}
                                                </span>
                                            </li>
                                        @endif
                                        @php
                                            $lessonType = $lesson?->lesson?->lesson_type ?? null;
                                            $groundschoolHours = $trainingEvent->course->groundschool_hours ?? null;
                                            $simulatorHours = $trainingEvent->course->simulator_hours ?? null;
                                        @endphp
                                        <small class="text-muted d-block mt-1">
                                            @if($lessonType === 'groundschool')
                                                <i class="fas fa-book-reader"></i>
                                                Ground School - Duration: {{ $groundschoolHours ?? 'N/A' }}, 
                                                Credited: {{ $lesson->hours_credited ?? '00:00' }}
                                            @elseif($lessonType === 'simulator')
                                                <i class="fas fa-vr-cardboard"></i>
                                                Simulator - Duration: {{ $simulatorHours ?? 'N/A' }}, 
                                                Credited: {{ $lesson->hours_credited ?? '00:00' }}
                                            @elseif($lessonType === 'flight')
                                                <i class="fas fa-plane"></i>
                                                Flight Time - Credited: {{ $lesson->hours_credited ?? '00:00' }}
                                            @endif
                                        </small>
                                        @php
                                            $customTime = $lesson->lesson->customTime ?? null;
                                        @endphp

                                        @if($customTime)
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-clock"></i> 
                                                <strong>Custom Time:</strong> 
                                                {{ $customTime->name }} – 
                                                Allotted: {{ $customTime->hours }}, 
                                                Credited: {{ $lesson->custom_hours_credited ?? '00:00' }}
                                            </small>
                                        @endif

                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>                  
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user"></i> Student:</strong>
                            {{ $trainingEvent->student->fname ?? '' }} {{ $trainingEvent->student->lname ?? '' }}
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-id-card"></i> License Number:</strong>
                            {{ $trainingEvent->std_license_number ?? 'N/A' }}
                        </div>
                    </div>
                @endif

                {{-- Event Summary Section --}}
                <div class="card shadow-sm mt-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <strong><i class="fas fa-clock"></i> Event Summary</strong>
                    </div>
                    <div class="card-body">

                        {{-- Total Course Duration --}}
                        @if($trainingEvent->course?->duration_value && $trainingEvent->course?->duration_type)
                            @php
                                $value = $trainingEvent->course->duration_value;
                                $type = $trainingEvent->course->duration_type;

                                $label = match($type) {
                                    'hours' => $value == 1 ? 'hour' : 'hours',
                                    'events' => $value == 1 ? 'event' : 'events',
                                    default => '',
                                };
                            @endphp
                            <p>
                                <strong><i class="fas fa-hourglass-half"></i> Total Course Duration:</strong>
                                <span class="badge bg-success text-white">{{ $value }} {{ $label }}</span>
                            </p>
                        @endif

                        {{-- Time Tracking --}}
                        @if($trainingEvent?->course?->course_type === 'one_event')
                            @php
                                $lessonType = $eventLesson?->lesson?->lesson_type ?? null;
                                $credited = $eventLesson?->hours_credited ?? '00:00';
                                $customCredited = $eventLesson?->custom_hours_credited ?? '00:00';
                                $customTime = $eventLesson?->lesson?->customTime;
                            @endphp

                            @if($lessonType)
                                <p>
                                    <strong>Type:</strong> {{ ucfirst($lessonType) }}<br>
                                    <strong>Credited Hours:</strong> {{ $credited }}
                                </p>
                            @endif

                            @if($customTime)
                                <p>
                                    <strong>Custom Time:</strong> {{ $customTime->name }}<br>
                                    <strong>Allotted:</strong> {{ $customTime->given_hours }} |
                                    <strong>Credited:</strong> {{ $customCredited }}
                                </p>
                            @endif

                        @else
                            @php
                                $totals = [
                                    'groundschool' => ['duration' => 0, 'credited' => 0],
                                    'simulator' => ['duration' => 0, 'credited' => 0],
                                    'flight' => ['credited' => 0],
                                    'custom' => [],
                                ];
                            @endphp

                            @foreach($trainingEvent->eventLessons as $lesson)
                                @php
                                    $type = $lesson->lesson?->lesson_type ?? '';
                                    $credited = strtotime("1970-01-01 {$lesson->hours_credited}") ?: 0;
                                    if ($type === 'groundschool') {
                                        $totals['groundschool']['duration'] = $trainingEvent->course->groundschool_hours ?? 0;
                                        $totals['groundschool']['credited'] += $credited;
                                    } elseif ($type === 'simulator') {
                                        $totals['simulator']['duration'] = $trainingEvent->course->simulator_hours ?? 0;
                                        $totals['simulator']['credited'] += $credited;
                                    } elseif ($type === 'flight') {
                                        $totals['flight']['credited'] += $credited;
                                    }

                                    // Custom time tracking
                                    if ($lesson->lesson?->customTime) {
                                        $custom = $lesson->lesson->customTime;
                                        $totals['custom'][$custom->name]['allotted'] = $custom->hours;
                                        $totals['custom'][$custom->name]['credited'] = ($totals['custom'][$custom->name]['credited'] ?? 0) + strtotime("1970-01-01 {$lesson->custom_hours_credited}");
                                    }
                                @endphp
                            @endforeach

                            @php
                                function formatSeconds($seconds) {
                                    $hours = floor($seconds / 3600);
                                    $minutes = floor(($seconds % 3600) / 60);
                                    return sprintf("%02d:%02d", $hours, $minutes);
                                }
                            @endphp

                            @if($totals['groundschool']['duration'] || $totals['groundschool']['credited'])
                                <p>
                                    <strong>Ground School:</strong>
                                    Duration: {{ $totals['groundschool']['duration'] ?? 'N/A' }} |
                                    Credited: {{ formatSeconds($totals['groundschool']['credited']) }}
                                </p>
                            @endif

                            @if($totals['simulator']['duration'] || $totals['simulator']['credited'])
                                <p>
                                    <strong>Simulator:</strong>
                                    Duration: {{ $totals['simulator']['duration'] ?? 'N/A' }} |
                                    Credited: {{ formatSeconds($totals['simulator']['credited']) }}
                                </p>
                            @endif

                            <p>
                                <strong>Flight:</strong>
                                Credited: {{ formatSeconds($totals['flight']['credited']) }}
                            </p>

                            @if(!empty($totals['custom']))
                                @foreach($totals['custom'] as $name => $custom)
                                    <p>
                                        <strong>Custom ({{ $name }}):</strong>
                                        Allotted: {{ $custom['allotted'] }} |
                                        Credited: {{ formatSeconds($custom['credited']) }}
                                    </p>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>


                {{-- Deferred Items(fallback) --}}
                @if(isset($defTasks) && $defTasks->isNotEmpty())
                    <div class="card shadow-sm mb-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <strong><i class="fas fa-exclamation-triangle"></i> Deferred Items (Auto-Generated)</strong>
                        </div>
                        <div class="card-body">
                            <ul class="mb-3 ps-4">
                                @foreach($defTasks as $item)
                                    @php
                                        $grade = $item->task_grade ?? null;
                                        $comment = $item->task_comment ?? null;
                                        $title = $item->task_title ?? 'Unnamed Task';

                                        $badgeClass = 'badge'; // base class

                                        if ($grade === 'Further training required') {
                                            $badgeClass .= ' grade-ftr';
                                        } elseif ($grade === 'Incomplete') {
                                            $badgeClass .= ' grade-incomplete';
                                        } elseif (in_array($grade, ['Competent', 'Completed', 'Passed'])) {
                                            $badgeClass .= ' grade-competent';
                                        } else {
                                            $badgeClass .= ' bg-secondary'; // fallback style
                                        }

                                        $badgeText = $grade ?? 'Deferred';
                                    @endphp

                                    <li class="mb-3">
                                        <div>
                                            <strong>{{ $title }}</strong>
                                            <span class="badge {{ $badgeClass }} ms-2">{{ $badgeText }}</span>
                                        </div>
                                        @if($comment)
                                            <div class="text-muted ps-1 mt-1">
                                                <em>Reason: {{ $comment }}</em>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0 text-primary">Add More Deferred Lessons</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeferredLessonModal" id="addDeferredLesson">
                                    + Lesson
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Deferred Lesson Modal Start --}}   
                <div class="modal fade" id="addDeferredLessonModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog">
                        <form action="" method="POST" id="deferredLessonForm"> 
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                            <input type="hidden" name="std_id" value="{{ $trainingEvent->student_id }}">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Deferred Lesson</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                                        <input type="text" name="lesson_title" class="form-control">
                                        <div id="lesson_title_error" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="lesson_date" class="form-control">
                                        <div id="lesson_date_error" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                        <input type="time" name="start_time" class="form-control">
                                        <div id="start_time_error" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                                        <input type="time" name="end_time" class="form-control">
                                        <div id="end_time_error" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Destination Airfield</label>
                                        <input type="text" name="destination_airfield" class="form-control" maxlength="4" value="">
                                        <div id="destination_airfield" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Departure Airfield</label>
                                        <input type="text" name="departure_airfield" class="form-control" maxlength="4" value="">
                                        <div id="departure_airfield" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Instructor <span class="text-danger">*</span></label>
                                            <select class="form-select" name="instructor_id" id="instructor">
                                                    <option value="">Select Instructor</option>
                                                    @foreach($instructors as $row)  
                                                    <option value="{{ $row->id }}" {{ (auth()->user()->id == $row->id) ? 'selected': '' }}>{{ $row->fname }} {{ $row->lname }}</option>
                                                    @endforeach
                                            </select>
                                        <div id="instructor_id_error" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Resource <span class="text-danger">*</span></label>
                                        <select class="form-select" name="resource_id">
                                                <option value="">Select Resource</option>
                                                @foreach($resources as $row)
                                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                @endforeach
                                        </select>
                                        <div id="resource_id_error" class="text-danger error_e"></div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Select Tasks <span class="text-danger">*</span></label>
                                            {{-- TaskGrading items --}}
                                            @if(isset($defTasks) && $defTasks->isNotEmpty())
                                                @foreach($defTasks as $item)
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" name="item_ids[]" value="{{ $item->task_id }}">
                                                        <label class="form-check-label">
                                                            {{ $item->task_title ?? 'N/A' }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        <div id="item_ids_error" class="text-danger error_e"></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary" type="submit" id="submitDeferredItems">Add Lesson</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Deferred Lesson Modal End --}}
                
                @if($trainingFeedbacks && $trainingFeedbacks->isNotEmpty())
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <strong><i class="fas fa-comments"></i> Student Feedback</strong>
                        </div>
                        <div class="card-body">
                            @foreach($trainingFeedbacks as $index => $feedback)
                                @php
                                    $question = $feedback->question;
                                    $answerType = $question->answer_type ?? null;
                                    $answer = $feedback->answer;
                                    $ratingLabels = [
                                        1 => 'Strongly Disagree',
                                        2 => 'Disagree',
                                        3 => 'Neutral',
                                        4 => 'Agree',
                                        5 => 'Strongly Agree'
                                    ];
                                @endphp

                                @if($question)
                                    <div class="mb-3">
                                        <p class="mb-1">
                                            <strong>Q{{ $index + 1 }}:</strong> {{ $question->question ?? 'No Question' }}
                                        </p>
                                        <p class="text-muted">
                                            Answer:
                                            @if($answerType === 'rating' && is_numeric($answer))
                                                {{ $answer }} - {{ $ratingLabels[$answer] ?? 'Unknown' }}
                                            @else
                                                {{ $answer ?? 'No Answer' }}
                                            @endif
                                        </p>
                                        <hr>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($isGradingCompleted && $trainingEvent->course->documents->isNotEmpty())
                    <div class="mt-4">
                        <h5><i class="fas fa-file-upload"></i>Instructor Document Uploads</h5>
                        <form action="{{ route('training.upload-documents', $trainingEvent->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                @foreach($trainingEvent->course->documents as $doc)
                                    @php
                                        $uploadedDoc = optional($trainingEvent->documents)->where('course_document_id', $doc->id)->first();
                                    @endphp
                                    <div class="col-md-6 mb-3">
                                        <div class="border p-3 rounded">
                                            <label class="form-label fw-bold">
                                                {{ $doc->document_name }}
                                            </label>

                                            {{-- Show existing uploaded document --}}
                                            @if($uploadedDoc)
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-success">Already Uploaded</span>
                                                    <a href="{{ asset('storage/' . $uploadedDoc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download"></i> View
                                                    </a>
                                                </div>
                                            @endif

                                            {{-- File input --}}
                                            <input  type="file"   name="training_event_documents[{{ $doc->id }}]"  class="form-control" >
                                            @error('training_event_documents.' . $doc->id)
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Submit button --}}
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Submit Documents
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
            <div class="tab-pane fade" id="Lesson" role="tabpanel" aria-labelledby="Lesson-tab">
                <div class="card-body">
                    <form action="" method="POST" id="gradingFrom">
                        @csrf
                        <input type="hidden" name="event_id" id="event_id" value="{{ $trainingEvent->id }}">
                            <div class="accordion accordion-flush" id="faq-group-2">
                                @foreach($eventLessons as $eventLesson)
                                @php
                                    $lesson = $eventLesson->lesson;
                                    $isLocked = $eventLesson->is_locked == 1;
                                @endphp
                                    <div class="accordion-item">
                                        <input type="hidden" name="tg_lesson_id[]" value="{{ $eventLesson->id }}">
                                                 <h2 class="accordion-header">
                                                    <button class="accordion-button {{ $isLocked ? 'collapsed' : '' }}"
                                                        type="button"
                                                        {{ $isLocked ? '' : 'data-bs-toggle=collapse' }}
                                                        {{ $isLocked ? '' : 'data-bs-target=#lesson-' . $eventLesson->id }}
                                                        aria-expanded="{{ $isLocked ? 'false' : 'true' }}"
                                                        aria-controls="lesson-{{ $eventLesson->id }}"
                                                        style="{{ $isLocked ? 'cursor: not-allowed; background-color: #f8f9fa;' : '' }}">
                                                        
                                                        {{ $lesson->lesson_title ?? 'Untitled Lesson' }}

                                                        @if($isLocked)
                                                            @if(auth()->user()?->is_admin==1)
                                                                <button type="button"
                                                                        class="btn btn-sm btn-outline-secondary ms-2 unlock-lesson-btn"
                                                                        data-event-id="{{ $eventLesson->training_event_id }}"
                                                                        data-lesson-id="{{ $eventLesson->lesson_id }}"
                                                                        data-bs-toggle="tooltip"
                                                                        title="Unlock this event to enable grading edits.">
                                                                    <i class="bi bi-lock-fill"></i>
                                                                </button>
                                                            @else
                                                                <span class="ms-2 text-muted" data-bs-toggle="tooltip" title="This lesson is locked">
                                                                    <i class="bi bi-lock-fill"></i>
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </button>
                                                </h2>
                                        <div class="d-flex flex-wrap gap-3 mb-3 small-text text-muted">
                                            <div><strong>Instructor:</strong> {{ $eventLesson->instructor->fname ?? '' }} {{ $eventLesson->instructor->lname ?? '' }}</div>
                                            <div><strong>License No:</strong> {{ !empty($eventLesson->instructor_license_number) ? $eventLesson->instructor_license_number : 'N/A' }}</div>
                                            <div><strong>Resource:</strong> {{ $eventLesson->resource->name ?? 'N/A' }}</div>
                                            <div><strong>Lesson Date:</strong> {{ ($eventLesson->lesson_date) ? date('d/m/Y', strtotime($eventLesson->lesson_date)) : 'N/A' }}</div>
                                            <div><strong>Start Time:</strong> {{ ($eventLesson->start_time) ? date('h:i A', strtotime($eventLesson->start_time)) : 'N/A' }}</div>
                                            <div><strong>End Time:</strong> {{ ($eventLesson->end_time) ? date('h:i A', strtotime($eventLesson->end_time)) : 'N/A' }}</div>
                                            <div><strong>Departure Airfield:</strong> {{ !empty($eventLesson->departure_airfield) ? $eventLesson->departure_airfield : 'N/A' }}</div>
                                            <div><strong>Destination Airfield:</strong>{{ !empty($eventLesson->destination_airfield) ? $eventLesson->destination_airfield : 'N/A' }}</div>
                                        </div>

                                        <div id="lesson-{{ $eventLesson->id }}" class="accordion-collapse collapse" data-bs-parent="#faq-group-2">
                                            <div class="accordion-body">
                                                @if($lesson && $lesson->subLessons->isNotEmpty())
                                                    @foreach($lesson->subLessons as $sublesson)
                                                        <div class="custom-box" >
                                                            <input type="hidden" name="tg_subLesson_id[]" value="{{ $sublesson->id }}">
                                                            <div class="header" data-bs-toggle="collapse" data-bs-target="#comment-box-{{ $sublesson->id }}" aria-expanded="false">
                                                                <span class="rmk">RMK</span>
                                                                <span class="question-mark">?</span>
                                                                <span class="title">{{ $sublesson->title }}</span>
                                                            </div>
                                                            <div class="table-container">
                                                            @php
                                                                $taskGrade = $taskGrades[$lesson->id . '_' . $sublesson->id] ?? null;
                                                                $selectedGrade = $taskGrade->task_grade ?? null;
                                                                $selectedComment = $taskGrade->task_comment ?? null;
                                                                $isDeferred = in_array($sublesson->id, $deferredTaskIds);
                                                            @endphp
                                                                <div class="main-tabledesign">
                                                                    <input type="hidden" name="tg_user_id" value="{{ $student->id }}">
                                                                    <h5>{{ $student->fname }} {{ $student->lname }}</h5>
                                                                    <table>
                                                                        <tbody>
                                                                            @if($sublesson->grade_type == 'pass_fail')
                                                                                <tr>
                                                                                    <td>
                                                                                        <label class="radio-label" title="{{ $isDeferred ? 'Deferred: You cannot edit this grading.' : '' }}">
                                                                                            <input type="radio" class="deselectable-radio" name="task_grade[{{ $lesson->id }}][{{ $sublesson->id }}]" value="Incomplete" {{ $selectedGrade == 'Incomplete' ? 'checked' : '' }} {{ $isDeferred ? 'disabled' : '' }}>
                                                                                            <span class="custom-radio incomplete">Incomplete</span>
                                                                                        </label>                                                                        
                                                                                    </td>
                                                                                    <td>
                                                                                        <label class="radio-label" title="{{ $isDeferred ? 'Deferred: You cannot edit this grading.' : '' }}">
                                                                                            <input type="radio" name="task_grade[{{ $lesson->id }}][{{ $sublesson->id }}]" value="Further training required"  {{ $selectedGrade == 'Further training required' ? 'checked' : '' }} {{ $isDeferred ? 'disabled' : '' }}>
                                                                                            <span class="custom-radio ftr">Further training required</span>
                                                                                        </label>
                                                                                    </td>
                                                                                    <td>
                                                                                        <label class="radio-label" title="{{ $isDeferred ? 'Deferred: You cannot edit this grading.' : '' }}">
                                                                                            <input type="radio" name="task_grade[{{ $lesson->id }}][{{ $sublesson->id }}]" value="Competent" {{ $selectedGrade == 'Competent' ? 'checked' : '' }} {{ $isDeferred ? 'disabled' : '' }}>
                                                                                            <span class="custom-radio competent">Competent</span>
                                                                                        </label>
                                                                                    </td>
                                                                                </tr>
                                                                            @elseif($lesson->grade_type == 'percentage')
                                                                                <tr>
                                                                                    <td colspan="5">
                                                                                        <input type="number"
                                                                                            name="task_grade[{{ $lesson->id }}][{{ $sublesson->id }}]"
                                                                                            value="{{ old("task_grade.$lesson->id.$sublesson->id.$student->id", $selectedGrade) }}"
                                                                                            class="form-control"
                                                                                            placeholder="Enter percentage"
                                                                                            min="0" max="100"
                                                                                            step="0.1"
                                                                                            style="width: 250px;" {{-- Increased width --}}
                                                                                            {{ $isDeferred ? 'readonly title=Deferred: You cannot edit this grading.' : '' }}>
                                                                                    </td>
                                                                                </tr>
                                                                            @else
                                                                                <tr>
                                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                                        @php
                                                                                            $colorClass = $i == 1 ? 'incomplete' : ($i == 2 ? 'ftr' : 'competent');
                                                                                        @endphp
                                                                                        <td>
                                                                                            <label class="radio-label">
                                                                                                <input type="radio" name="task_grade[{{ $lesson->id }}][{{ $sublesson->id }}]" value="{{ $i }}" {{ old("task_grade.$lesson->id.$sublesson->id.$student->id", $selectedGrade) == $i ? 'checked' : '' }}>
                                                                                                <span class="custom-radio {{ $colorClass }}">{{ $i }}</span>
                                                                                            </label>
                                                                                        </td>
                                                                                    @endfor                                                          
                                                                                </tr>
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
                                                                    <span class="custom-radio competent task_grade_{{ $lesson->id }}_{{ $sublesson->id }}_{{ $student->id }}"></span>                                                                    
                                                                </div>                                                        
                                                            </div>
                                                        </div>
                                                        <!-- Toggleable Comment Box -->
                                                        <div class="collapse mt-2" id="comment-box-{{ $sublesson->id }}">
                                                            <textarea  name="task_comments[{{ $lesson->id }}][{{ $sublesson->id }}]" rows="3" class="form-control" placeholder="Add your remarks or feedback here..." @if($isDeferred) readonly title="Deferred: You cannot edit this comment." @endif>{{ old("task_comments.$lesson->id.$sublesson->id.$student->id", $selectedComment) }}</textarea>
                                                        </div>
                                                    @endforeach 
                                                @else($lesson->subLessons->isEmpty())
                                                    <p class="text-muted">No Task available.</p>
                                                @endif
                                                </div>
                                                    <div class="accordion-item">
                                                @if($lesson->enable_cbta==1)        
                                                <h2 class="accordion-header">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <button type="button" class="accordion-button" data-bs-toggle="collapse"
                                                            data-bs-target="#comptency-{{ $eventLesson->id }}" aria-expanded="false">
                                                            Overall Competency Grading
                                                        </button>
                                                    </div>                                                
                                                </h2>
                                                @endif  
                                    </div>
                                    <div id="comptency-{{ $eventLesson->id }}" class="accordion-collapse collapse">
                                        <!-- Student name aligned to the right, above the competency grading -->
                                        <div class="text-end pe-4 pt-2 fw-semibold">
                                            {{ $student->fname }} {{ $student->lname }}
                                        </div>
                                        <div class="accordion-body">
                                        @php
                                            $competencies = [
                                                'KNO' => 'Application of knowledge',
                                                'PRO' => 'Application of Procedures and compliance with regulations',
                                                'COM' => 'Communication',
                                                'FPA' => 'Aeroplane flight path management - automation',
                                                'FPM' => 'Aeroplane flight path management - Manual Control',
                                                'LTW' => 'Leadership & Teamwork',
                                                'PSD' => 'Problem-solving - decision-making',
                                                'SAW' => 'Situation awareness and management of information',
                                                'WLM' => 'Workload Management',
                                            ];
                                            $lessonCompetencies = $competencyGrades[$lesson->id] ?? collect();
                                        @endphp
                                        @foreach($competencies as $code => $title)
                                        @php
                                            $code = strtolower($code); // make sure it's lowercase  
                                            $grading = $lessonCompetencies->first();

                                            $selectedCompGrade = $grading?->{$code . '_grade'} ?? null;
                                            $selectedCompComment = $grading?->{$code . '_comment'} ?? null;
                                        @endphp
                                            <div class="custom-box" >
                                                <div class="header" data-bs-toggle="collapse" data-bs-target="#competency-box-{{ $code }}" aria-expanded="false">
                                                    <span class="rmk">RMK</span>
                                                    <span class="question-mark">?</span>
                                                    <span class="title"><span class="highlight">{{ $title }} ({{ strtoupper($code) }})</span></span>
                                                    <input type="hidden" name="cg_lesson_id" value="{{ $lesson->id }}">
                                                </div>
                                                <div class="table-container">
                                                    <div class="main-tabledesign">
                                                        <input type="hidden" name="cg_user_id" value="{{ $student->id }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                @for ($i = 1; $i <= 5; $i++)
                                                                    @php
                                                                        $colorClass = $i == 1 ? 'incomplete' : ($i == 2 ? 'ftr' : 'competent');
                                                                    @endphp
                                                                    <td>
                                                                        <label class="radio-label">
                                                                            <input type="radio" class="scale-radio"
                                                                                name="comp_grade[{{ $lesson->id }}][{{ $code }}]"
                                                                                value="{{ $i }}" data-event-id="{{ $trainingEvent->id }}" data-lesson-id="{{ $lesson->id }}" data-user-id="{{ $student->id }}" data-code="{{ $code }}" data-color-class="{{ $colorClass }}"
                                                                                {{ $selectedCompGrade == $i ? 'checked' : '' }}>
                                                                            <span class="custom-radio {{ $colorClass }}">{{ $i }}</span>
                                                                        </label>
                                                                    </td>
                                                                @endfor
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <span class="custom-radio competent comp_grade_{{ $lesson->id }}_{{ $student->id }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Toggleable Comment Box -->
                                            <div class="collapse mt-2" id="competency-box-{{ $code }}">
                                            <textarea name="comp_comments[{{ $lesson->id }}][{{ $code }}]" rows="3" class="form-control" placeholder="Add remarks or comments on competency">{{ $selectedCompComment }}</textarea>
                                            </div>
                                        @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach                                
                            <div class="btn-container">
                                <button type="submit" class="btn btn-save" id="submitGrading">Save</button>
                            </div>
                            </div>
                    </form>
                    @if($defLessonTasks->isNotEmpty())
                     <h4 class="mb-3 text-primary"><i class="bi bi-exclamation-triangle-fill me-2"></i>Deferred Lessons</h4>
                     <form action="" method="POST" id="defGradingFrom">
                        @foreach($defLessonTasks->groupBy('def_lesson_id') as $defLessonId => $tasks)
                            @php $defLesson = $tasks->first()->defLesson; 
                            $documents = $defLesson?->instructor?->documents; // Only one row expected

                                if ($documents && $documents->licence) {
                                    $instructor_lic_no = $documents->licence;
                                } elseif ($documents && $documents->licence_2) {
                                    $instructor_lic_no = $documents->licence_2;
                                } else {
                                    $instructor_lic_no = 'N/A';
                                }
                        
                            @endphp
                            @csrf
                            <div class="accordion-item">    
                                <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                                <input type="hidden" name="tg_def_user_id" value="{{ $trainingEvent?->student_id }}">
                                <input type="hidden" name="tg_def_lesson_id[]" value="{{ $defLesson?->id }}">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#def-lesson-{{ $defLesson?->id }}" aria-expanded="false">
                                        {{ $defLesson->lesson_title ?? 'Untitled Deferred Lesson' }}
                                    </button>
                                </h2>
                                <div class="d-flex flex-wrap gap-3 mb-3 small-text text-muted">
                                    <div><strong>Instructor:</strong> {{ $defLesson->instructor->fname ?? '' }} {{ $defLesson->instructor->lname ?? '' }}</div>
                                    <div><strong>License No:</strong> {{ $instructor_lic_no }}</div>
                                    <div><strong>Resource:</strong> {{ $defLesson->resource->name ?? 'N/A' }}</div>
                                    <div><strong>Lesson Date:</strong> {{ $defLesson?->lesson_date ? \Carbon\Carbon::parse($defLesson->lesson_date)->format('d/m/Y') : 'N/A' }}</div>
                                    <div><strong>Start Time:</strong> {{ $defLesson?->start_time ? \Carbon\Carbon::parse($defLesson?->start_time)->format('h:i A') : 'N/A' }}</div>
                                    <div><strong>End Time:</strong> {{ $defLesson?->end_time ? \Carbon\Carbon::parse($defLesson?->end_time)->format('h:i A') : 'N/A' }}</div>
                                    <div><strong>Departure Airfield:</strong> {{ !empty($defLesson?->departure_airfield) ? strtoupper($defLesson?->departure_airfield) : 'N/A' }}</div>
                                    <div><strong>Destination Airfield:</strong>{{ !empty($defLesson?->destination_airfield) ? strtoupper($defLesson?->destination_airfield) : 'N/A' }}</div>
                                </div>

                                <div id="def-lesson-{{ $defLesson?->id }}" class="accordion-collapse collapse" data-bs-parent="#faq-group-2">
                                    <div class="accordion-body">
                                        @foreach($tasks as $task)
                                            <div class="custom-box">                        
                                                <div class="header" data-bs-toggle="collapse" data-bs-target="#comment-box-{{ $task->id }}" aria-expanded="false">
                                                    <span class="rmk">RMK</span>
                                                    <span class="question-mark">?</span>
                                                    <span class="title">{{ $task->task->title ?? 'Untitled Task' }}</span>
                                                </div>
                                                <div class="table-container">
                                                    <div class="main-tabledesign"> 
                                                        <h5>{{ $task->user->fname }} {{ $task->user->lname }}</h5>
                                                        @php
                                                            $selectedGrade = $task->task_grade;
                                                            $selectedComment = $task->task_comment;
                                                             $isDeferredGraded = $gradedDefTasksMap->has($task->def_lesson_id . '_' . $task->task_id);
                                                        @endphp
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}" >
                                                                            <input type="radio" name="task_grade_def[{{ $task->id }}]" value="Incomplete" {{ $selectedGrade == 'Incomplete' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }} >
                                                                            <span class="custom-radio incomplete">Incomplete</span>
                                                                        </label>
                                                                    </td>
                                                                    <td>
                                                                        <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                            <input type="radio" name="task_grade_def[{{ $task->id }}]" value="Further training required" {{ $selectedGrade == 'Further training required' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
                                                                            <span class="custom-radio ftr">Further training required</span>
                                                                        </label>
                                                                    </td>
                                                                    <td>
                                                                        <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                            <input type="radio" name="task_grade_def[{{ $task->id }}]" value="Competent" {{ $selectedGrade == 'Competent' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
                                                                            <span class="custom-radio competent">Competent</span>
                                                                        </label>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Toggleable Comment Box -->
                                            <div class="collapse mt-2" id="comment-box-{{ $task->id }}">
                                                <textarea name="task_comment_def[{{ $task->id }}]" rows="3" class="form-control" placeholder="Add your remarks or feedback here..." @if($isDeferredGraded) readonly title="Deferred: You cannot edit this comment." @endif>{{ old("task_comment_def.$task->id", $selectedComment) }}</textarea>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div class="btn-container">
                                <button type="submit" class="btn btn-save" id="submitDefGrading">Save</button>
                            </div>
                        </form>                            
                    @endif
                </div>
            </div>
            <div class="tab-pane fade" id="student-{{ $student->id }}" role="tabpanel" aria-labelledby="student-tab-{{ $student->id }}">
                <form method="POST" class="overallAssessmentForm" data-user-id="{{ $student->id }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                    <input type="hidden" name="user_id" value="{{ $student->id }}">
                    <div class="assessment-wrapper">
                        <h2>Overall assessment</h2>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Result</label>
                            <div class="col-sm-10 buttons">
                                <div class="main-tabledesign">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label class="radio-label">
                                                        <input type="radio" name="user_result_{{ $student->id }}" value="Competent" {{ isset($overallAssessments) && isset($overallAssessments->result) && $overallAssessments->result == 'Competent' ? 'checked' : '' }} >
                                                        <span class="custom-radio competent">Competent</span>
                                                    </label>                                                                    
                                                </td>
                                                <td>
                                                    <label class="radio-label">
                                                        <input type="radio" name="user_result_{{ $student->id }}" value="Further training required" {{ isset($overallAssessments) && isset($overallAssessments->result) && $overallAssessments->result == 'Further training required' ? 'checked' : '' }}>
                                                        <span class="custom-radio ftr">Further training required</span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="radio-label">
                                                        <input type="radio" name="user_result_{{ $student->id }}" value="Incomplete" {{ isset($overallAssessments) && isset($overallAssessments->result) && $overallAssessments->result == 'Incomplete' ? 'checked' : '' }}>
                                                        <span class="custom-radio incomplete">Incomplete</span>
                                                    </label>
                                                </td>                                                                 
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3 remark-section">
                            <label class="col-sm-2 col-form-label">Remark</label>
                            <div class="col-sm-10">
                                <textarea class="form-control remark" name="remark_{{ $student->id }}" style="height: 100px" placeholder="Enter your remarks here...">{{ $overallAssessments->remarks ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn btn-save">Save</button>
                        </div>
                    </div>
                </form>
            </div>

    </div><!-- End Default Tabs -->
</div>
</div>
@endsection

@section('js_scripts')

<script>
    $(document).ready(function() {

    $('.scale-radio').on('click', function () {
        const name = $(this).attr('name');
        const wasChecked = $(this).data('waschecked');
        const event_id = $(this).data('event-id');
        const lesson_id = $(this).data('lesson-id');
        const user_id = $(this).data('user-id');
        const code = $(this).data('code');

        if (wasChecked) {
            // Uncheck and remove color class
            $(this).prop('checked', false).data('waschecked', false);

              $.ajax({
                url: '/training/update-competency-grade', // Replace with your actual route
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    event_id: event_id,
                    lesson_id: lesson_id,
                    user_id: user_id,
                    code: code,
                },
                success: function (response) {
                    console.log('Grade updated:', response);
                },
                error: function (xhr) {
                    alert('Failed to update grade.');
                    console.error(xhr.responseText);
                }
            });

        } else {
            // Deselect others, then mark this as checked
            $('input[name="' + name + '"]').each(function () {
                $(this).data('waschecked', false);
            });
            $(this).data('waschecked', true);
        }

        // updateRadioStyles(name);
    });

    function updateRadioStyles(groupName) {
        $('input[name="' + groupName + '"]').each(function () {
            const span = $(this).siblings('.custom-radio');
            const colorClass = $(this).data('color-class');

            // Remove all color classes first
            span.removeClass('incomplete ftr competent active-selected');

            if ($(this).is(':checked')) {
                span.addClass(colorClass).addClass('active-selected');
            }
        });
    }

    // Initial color update for already checked radios
    $('.scale-radio:checked').each(function () {
        const name = $(this).attr('name');
        updateRadioStyles(name);
    });
        $('#addDeferredLesson').on('click', function() {
            $('.error_e').html('');
            $("#deferredLessonForm")[0].reset();
        })

        $(document).on("submit", "#gradingFrom", function(e) {
            e.preventDefault(); // Prevent default form submission
            // $(".loader").fadeIn();

             // Collect task grade and competency grade data
            let taskGradeData = $("input[name^='task_grade']").filter(function () {
                return ($(this).is(':radio') && $(this).is(':checked')) || ($(this).attr('type') === 'number' && $(this).val().trim() !== '');
            }).length;

            let compGradeData = $("input[name^='comp_grade']").filter(function () {
                return ($(this).is(':radio') && $(this).is(':checked')) || ($(this).attr('type') === 'number' && $(this).val().trim() !== '');
            }).length;

            if (taskGradeData === 0 && compGradeData === 0) {
                alert('You must fill in at least one task or competency grade for one lesson.');
                return;
            }

            let formData = new FormData(this);
            console.log(formData);

            $.ajax({
                url: "{{ route('training.store_grading') }}", // Update with your route
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        // $(".loader").fadeOut("slow");
                        alert("Grading saved successfully!");
                        location.reload(); // Reload to reflect changes
                        // $(this)[0].reset();
                    } else {
                        alert("Something went wrong. Please try again.");
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                    // alert("Error: " + xhr.responseText);
                    // $(".loader").fadeOut("slow");
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function (key, value) {
                        // Correct selector using attribute selector
                        let errorElement = $('.' + key);
                        if (errorElement.length > 0) {
                            errorElement.html('<span class="error-text" style="color:red;">' + value + '</span>');
                        }
                    });
                }
            });
        });

        $(document).on("submit", "#defGradingFrom", function(e) {
            e.preventDefault(); // Prevent default form submission

            let form = this;
            let formData = new FormData(form);

            // Optional: Validate if at least one task grade is selected
            let taskGradeCount = $(form).find("input[name^='task_grade_def']:checked").length;
            if (taskGradeCount === 0) {
                alert('Please grade at least one deferred task.');
                return;
            }

            $.ajax({
                url: "{{ route('training.store_def_grading') }}", // Update with your route
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        // $(".loader").fadeOut("slow");
                        alert("Deferred Task Grading saved successfully!");
                        location.reload(); // Reload to reflect changes
                        // $(this)[0].reset();
                    } else {
                        alert("Something went wrong. Please try again.");
                    }
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                    // alert("Error: " + xhr.responseText);
                    // $(".loader").fadeOut("slow");
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function (key, value) {
                        // Correct selector using attribute selector
                        let errorElement = $('.' + key);
                        if (errorElement.length > 0) {
                            errorElement.html('<span class="error-text" style="color:red;">' + value + '</span>');
                        }
                    });
                }
            });
        });

        $(document).on('submit', '.overallAssessmentForm', function (e) {
            e.preventDefault();
            // $(".loader").fadeIn();
            let form = $(this);
            let userId = form.data('user-id');
            let event_id = form.find('input[name="event_id"]').val();
            let user_id = form.find('input[name="user_id"]').val();
            let result = form.find(`input[name="user_result_${userId}"]:checked`).val();
            let remarks = form.find(`textarea[name="remark_${userId}"]`).val();

            if (!result) {
                alert("Please select a result before submitting.");
                return;
            }

            $.ajax({
                url: "{{ route('training.overall_assessment') }}", // Update with actual route name
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    event_id: event_id,
                    user_id: user_id,
                    result: result,
                    remarks: remarks
                },
                success: function (response) {
                    if (response.success) {
                        $(".loader").fadeOut("slow");
                        alert("Overall assessment saved successfully.");
                        form[0].reset();
                        location.reload();
                    } else {
                        alert("Error saving assessment.");
                    }
                },
                error: function (xhr) {
                    alert("Something went wrong. Please try again.");
                    console.log(xhr.responseText);
                    // $(".loader").fadeOut("slow");
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function(key, value) {
                        var html1 = '<p>' + value + '</p>';
                        $('#' + key + '_error').html(html1);
                    });
                }
            });
        });

        $("#submitDeferredItems").on("click", function(e) { 
            e.preventDefault();
            $(".loader").fadeIn();
            $.ajax({
                url: '{{ url("/training/submit_deferred_items") }}',
                type: 'POST',
                data: $("#deferredLessonForm").serialize(),
                success: function(response) {
                    if(response.success){
                        $(".loader").fadeOut("slow");
                        $('#addDeferredLessonModal').modal('hide');
                        location.reload();
                    }else{
                        $(".loader").fadeOut("slow");
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $(".loader").fadeOut("slow");
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    // $.each(validationErrors, function(key, value) {
                    //     var msg = '<p>' + value + '<p>';
                    //     $('#' + key + '_error').html(msg);
                    // })
                    // Clear old errors
                    $('.error_e').html('');
                    $.each(validationErrors, function(key, value) {
                        var formattedKey = key.replace(/\./g, '_') + '_error';
                        var errorMsg = '<p>' + value[0] + '</p>';
                        $('#' + formattedKey).html(errorMsg);
                    });
                }
            });

        })

        $('.unlock-lesson-btn').on('click', function () {
            const eventId = $(this).data('event-id');
            const lessonId = $(this).data('lesson-id');

            if (confirm('Are you sure you want to unlock this lesson for editing?')) {
                $.ajax({
                    url: '/training/unlock-lesson',
                    method: 'POST',
                    data: {
                        event_id: eventId,
                        lesson_id: lessonId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {
                            alert('Lesson unlocked successfully.');
                            location.reload(); // Optional: You can replace this with dynamic DOM update
                        } else {
                            alert(response.message || 'Failed to unlock lesson.');
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        alert('An error occurred while unlocking the lesson.');
                    }
                });
            }
        });

        setTimeout(function() {
            $('#successMessage').fadeOut('slow');
        }, 2000);

    });
</script>

@endsection