    @section('title', 'View')
    @section('sub-title', 'Training Event')
    @extends('layout.app')
    @section('content')

    <style>
        .course-dropdown .dropdown-list {
            bottom: 100%;
            /* instead of top */
            top: auto;
            margin-bottom: 5px;
            margin-top: 0;
        }

        .course-dropdown {
            position: relative;
            border: 1px solid #ced4da;
            border-radius: .375rem;
            padding: .375rem .75rem;
            cursor: pointer;
        }

        .course-dropdown .dropdown-label {
            display: block;
            color: #495057;
        }

        .course-dropdown .dropdown-list {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: .375rem;
            margin-top: 5px;
            width: 100%;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1050;
        }

        .header.grade_head {
            box-shadow: unset !important;
        }

        .course-dropdown.active .dropdown-list {
            display: block;
        }

        .course-dropdown .dropdown-option {
            display: block;
            padding: 5px 10px;
            cursor: pointer;
        }

        .course-dropdown .dropdown-option:hover {
            background: #f8f9fa;
        }

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
            /* white-space: nowrap; */
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

        .grade-comment {
            font-size: 12px;
        }

        .grade_here_cont td {
            min-width: 90px;
        }

        .grade_here_cont h5 {
            font-size: 15px;
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
                font-size: 13px;
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
            font-size: 14px;
            padding: 5px 8px;
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
        .radio-label input:checked+.custom-radio.incomplete {
            background-color: #FFFF00;
            /* Yellow */
            color: black;
            font-weight: bold;
            width: 100%;
            padding: 7px
        }

        .radio-label input:checked+.custom-radio.ftr {
            background-color: #ffc107;
            /* Amber */
            color: black;
            font-weight: bold;
            width: 100%;
            padding: 7px
        }

        .radio-label input:checked+.custom-radio.competent {
            background-color: #008000;
            /* green */
            color: black;
            font-weight: bold;
            width: 100%;
            padding: 7px
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
        td.overall_td {
            width: 33%;
        }
    </style>

    <head>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    </head>

    @php
    if (!function_exists('formatSeconds')) {
    function formatSeconds($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    return sprintf("%02d:%02d", $hours, $minutes);
    }
    }
    @endphp

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
                    <button class="nav-link" id="student-tab-{{ $student->id ?? ''}}" data-bs-toggle="tab" data-bs-target="#student-{{ $student->id ?? '' }}" type="button" role="tab" aria-controls="student-{{ $student->id ?? '' }}" aria-selected="false">
                        Overall Assessment
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
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <strong><i class="fas fa-book"></i> Course Name:</strong>
                                <span class="badge bg-info text-white">{{ $trainingEvent->course->course_name ?? 'N/A' }}</span>
                            </div>
                        </div>
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

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="row g-4 align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="text-muted mb-1"><i class="fas fa-user me-1"></i>Student</h6>
                                        <p class="mb-0 fw-semibold">

                                            {{ $trainingEvent->student->fname ?? '' }} {{ $trainingEvent->student->lname ?? '' }}
                                        </p>
                                    </div>
                                    <?php
                                    $atoNum = strtolower($trainingEvent->course->ato_num);

                                    $isUK = str_contains($atoNum, 'uk');
                                    $isEASA = str_contains($atoNum, 'easa');

                                    $ukLicence = '';
                                    $easaLicence = '';
                                    if (isset($trainingEvent->studentDocument->licence)) {
                                        $ukLicence = trim($trainingEvent->studentDocument->licence);
                                    }

                                    if (isset($trainingEvent->studentDocument->licence_2)) {
                                        $easaLicence = trim($trainingEvent->studentDocument->licence_2);
                                    }



                                    // Determine label based only on atoNum
                                    if ($isUK && !$isEASA) {
                                        $label = "Licence Number (UK)";
                                        $student_licence = !empty($ukLicence) ? $ukLicence : 'N/A';
                                    } elseif ($isEASA && !$isUK) {
                                        $label = "Licence Number (EASA)";
                                        $student_licence = !empty($easaLicence) ? $easaLicence : 'N/A';
                                    } elseif ($isUK && $isEASA) {
                                        if (empty($ukLicence) && empty($easaLicence)) {
                                            // Generic case, but no data — fallback to general label
                                            $label = "Licence";
                                            $student_licence = "N/A";
                                        } else {
                                            $label = "Licence Number";
                                            $student_licence = implode(', ', array_filter([$ukLicence, $easaLicence]));
                                        }
                                    } else {
                                        // Neither UK nor EASA in ATO number
                                        if (empty($ukLicence) && empty($easaLicence)) {
                                            $label = "Licence";
                                            $student_licence = "N/A";
                                        } else {
                                            $label = "Licence Number";
                                            $student_licence = implode(', ', array_filter([$ukLicence, $easaLicence]));
                                        }
                                    }
                                    ?>

                                    <div class="col-md-4">
                                        <h6 class="text-muted mb-1">
                                            <i class="fas fa-id-card me-1"></i><?= $label ?>
                                        </h6>
                                        <p class="mb-0 fw-semibold"><?= $student_licence ?></p>
                                    </div>
                                    <!-- // Rank -->
                                    @if(!empty($trainingEvent->rank))
                                    <div class="col-md-4">
                                        <h6 class="text-muted mb-1">
                                            <i class="fas fa-id-card me-1"></i>Rank
                                        </h6>
                                        <?php
                                        if ($trainingEvent->rank == 1) {
                                            $rank = "Captain";
                                        } elseif ($trainingEvent->rank == 2) {
                                            $rank = "First Officer";
                                        } elseif ($trainingEvent->rank == 3) {
                                            $rank = "Second Officer";
                                        }
                                        ?>
                                        <p class="mb-0 fw-semibold"><?= $rank ?></p>
                                    </div>
                                    @endif

                                    <!-- // End Rank -->


                                </div>
                            </div>
                        </div>




                        <!-- <pre> -->
                        @if($trainingEvent?->course?->course_type === 'one_event' && $trainingEvent->eventLessons->count())
                        @php
                        $eventLesson = $trainingEvent->eventLessons->first() ?? null;
                        $lessons = collect([$eventLesson]);
                        @endphp

                        @else
                        @php
                        $lessons = $trainingEvent->eventLessons;
                        @endphp
                        @endif

                        @if($lessons && $lessons->count())
                        @foreach($lessons as $lesson)
                        <div class="row mb-3 p-3 border rounded bg-light">
                            <div class="col-md-12">
                                <strong><i class="fas fa-book"></i> Lesson Name:</strong>
                                <span class="text-primary">{{ $lesson->lesson->lesson_title ?? 'Untitled' }}</span>
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-chalkboard-teacher"></i> Instructor:</strong>
                                {{ optional($lesson->instructor)->fname }} {{ optional($lesson->instructor)->lname }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="bi bi-card-text"></i> License:</strong>
                                {{ $lesson->instructor_license_number ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-toolbox"></i> Resource:</strong><br>
                                {{ optional($lesson->resource)->name ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-calendar-day"></i> Date:</strong><br>

                                @if(!empty($lesson->lesson_date))
                                {{ date('d-m-Y', strtotime($lesson->lesson_date)) }}
                                @endif
                            </div>

                            <!-- <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> Start:</strong><br>
                                {{ date('h:i A', strtotime($lesson->start_time)) }}
                            </div> -->
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> Start:</strong><br>
                                @if(!empty($lesson->start_time) && $lesson->start_time !== '00:00:00')
                                {{ date('h:i A', strtotime($lesson->start_time)) }}
                                @elseif($lesson->start_time === '00:00:00')
                                {{ $lesson->start_time }}
                                @else
                                -
                                @endif
                            </div>
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> End:</strong><br>
                                @if(!empty($lesson->end_time) && $lesson->end_time !== '00:00:00')
                                {{ date('h:i A', strtotime($lesson->end_time)) }}
                                @elseif($lesson->end_time === '00:00:00')
                                {{ $lesson->end_time }}
                                @else
                                -
                                @endif
                            </div>

                            <!-- <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> End:</strong><br>
                                {{ date('h:i A', strtotime($lesson->end_time)) }}
                            </div> -->

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-plane-departure"></i> Departure:</strong><br>
                                {{ $lesson->departure_airfield ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-plane-arrival"></i> Destination:</strong><br>
                                {{ $lesson->destination_airfield ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                @php $lessonType = $lesson?->lesson?->lesson_type ?? null; @endphp
                                <strong><i class="fas fa-clock"></i> Lesson Type:</strong><br>
                                {{ ucfirst($lessonType) ?? 'N/A' }}
                            </div>

                            @if($lessonType === 'groundschool')
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Duration:</strong><br>
                                {{ $trainingEvent->course->groundschool_hours ?? 'N/A' }}
                            </div>
                            @elseif($lessonType === 'simulator')
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Duration:</strong><br>
                                {{ $trainingEvent->course->simulator_hours ?? 'N/A' }}
                            </div>
                            @endif

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Credited Hours:</strong><br>
                                {{ $lesson->hours_credited ?? '00:00' }}
                            </div>


                            <!-- // Operation -->
                          <?php // dump($lesson->operation1); ?>
                            @if(!empty($lesson->operation1))    
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Operation:</strong><br>
                                @if($lesson->operation1 == 1)
                                PF in LHS
                                @elseif($lesson->operation1 == 2)
                                PM in LHS
                                @elseif($lesson->operation1 == 3)
                                PF in RHS
                                @elseif($lesson->operation1 == 4)
                                PM in RHS
                                @endif
                            </div>
                            @endif

                            <!-- // Rank -->
                            @if(!empty($lesson->rank))
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Rank:</strong><br>
                                @if($lesson->rank == 1)
                                Captain
                                @elseif($lesson->rank == 2)
                                First Officer
                                @elseif($lesson->rank == 3)
                                Second Officer
                                @endif
                            </div>
                            @endif


                            @php
                            $customTime = $lesson->lesson->customTime ?? null;
                            @endphp

                            @if($customTime)
                            <div class="col-md-6 mt-2">
                                <strong><i class="fas fa-clock"></i> Custom Time:</strong><br>
                                <span>Name: {{ $customTime->name }}</span><br>
                                <span>Allotted: {{ $customTime->given_hours }}</span><br>
                                <span>Credited: {{ $lesson->custom_hours_credited ?? '00:00' }}</span>
                            </div>
                            @endif

                            <!-- {{-- Lesson Summary --}} -->
                            @if(!empty($lesson->lesson_summary))
                            <div class="col-md-12 mt-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white py-0">
                                        <i class="bi bi-journal-text me-2"></i> Lesson Summary
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($lesson->lesson_summary))
                                        <p class="mb-0 text-muted">
                                            {{ $lesson->lesson_summary }}
                                        </p>
                                        @else
                                        <p class="mb-0 text-muted fst-italic">No Lesson summary provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if(Auth::user()->role == "1")
                            <!-- //  Instructor Comment -->
                            @if(!empty($lesson->instructor_comment))
                            <div class="col-md-12 mt-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white py-0">
                                        <i class="bi bi-journal-text me-2"></i> Instructor Comment
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($lesson->instructor_comment ))
                                        <p class="mb-0 text-muted">
                                            {{ $lesson->instructor_comment }}
                                        </p>
                                        @else
                                        <p class="mb-0 text-muted fst-italic">No Instructor Comment provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endif
                        </div>
                        @endforeach

                        @if($deferredLessons->isNotEmpty())
                        <strong><i class="fas fa-exclamation-circle"></i> Deferred Lessons:</strong>
                        @foreach($deferredLessons as $def)
                        @php
                        $start = strtotime($def->start_time);
                        $end = strtotime($def->end_time);
                        $duration = max(0, $end - $start);

                        $documents = $def?->instructor?->documents;

                        if ($documents && !empty($documents->licence_2)) {
                        $instructor_lic_no = $documents->licence_2;
                        } elseif ($documents && !empty($documents->licence)) {
                        $instructor_lic_no = $documents->licence;
                        } else {
                        $instructor_lic_no = 'N/A';
                        }
                        @endphp
                        <div class="row mb-3 p-3 border rounded bg-light shadow-sm">
                            <div class="col-md-6 mb-2">
                                <strong><i class="fas fa-book"></i> Lesson Name:</strong>
                                <span class="text-primary">{{ $def->lesson_title ?? 'Untitled' }}</span>
                            </div>

                            @if(Auth::user()->role == "1")
                            <div class="col-md-6 mb-2" style="text-align:end">
                                <button id="edit_deferred_lesson" class="btn btn-primary" data-event_id="{{ $def->event_id }}" data-deferred-lesson-id="{{$def->id }}" data-lesson-Type="deferred">Edit</button>

                                <button id="delete_deferred_lesson" class="btn btn-danger" data-event_id="{{ $def->event_id }}" data-deferred-lesson-id="{{$def->id }}" data-lesson-Type="deferred">Delete</button>
                            </div>
                            @endif

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-chalkboard-teacher"></i> Instructor:</strong>
                                {{ optional($def->instructor)->fname }} {{ optional($def->instructor)->lname }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="bi bi-card-text"></i> License:</strong>
                                {{ $instructor_lic_no }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-toolbox"></i> Resource:</strong><br>
                                {{ $def->resource->name ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-calendar-day"></i> Date:</strong><br>
                                {{ date('d-m-Y', strtotime($def->lesson_date)) }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> Start:</strong><br>
                                {{ date('h:i A', strtotime($def->start_time)) }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> End:</strong><br>
                                {{ date('h:i A', strtotime($def->end_time)) }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-plane-departure"></i> Departure:</strong><br>
                                {{ strtoupper($def->departure_airfield) ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-plane-arrival"></i> Destination:</strong><br>
                                {{ strtoupper($def->destination_airfield) ?? 'N/A' }}
                            </div>
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> Lesson Type:</strong><br>

                                {{ ucfirst($def->deftasks?->subddddLesson?->courseLesson?->lesson_type ?? 'N/A') }}

                            </div>
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Credited Hours:</strong><br>
                                {{ $def->defLesson->hours_credited ?? '00:00' }}
                            </div>
                            @if($def->operation != 0)
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Operation:</strong><br>
                                @if($def->operation == 1)
                                PF in LHS
                                @elseif($def->operation == 2)
                                PM in LHS
                                @elseif($def->operation == 3)
                                PF in RHS
                                @elseif($def->operation == 4)
                                PM in RHS
                                @else
                                N/A
                                @endif
                            </div>
                            @endif

                            <!-- {{-- Lesson Summary --}} -->
                            @if(!empty($def->lesson_summary))
                            <div class="col-md-12 mt-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white py-0">
                                        <i class="bi bi-journal-text me-2"></i> Lesson Summary
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($def->lesson_summary))
                                        <p class="mb-0 text-muted">
                                            {{ $def->lesson_summary }}
                                        </p>
                                        @else
                                        <p class="mb-0 text-muted fst-italic">No Lesson summary provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- //  Instructor Comment -->
                            @if(!empty($def->instructor_comment))
                            <div class="col-md-12 mt-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white py-0">
                                        <i class="bi bi-journal-text me-2"></i> Instructor Comment
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($def->instructor_comment ))
                                        <p class="mb-0 text-muted">
                                            {{ $def->instructor_comment }}
                                        </p>
                                        @else
                                        <p class="mb-0 text-muted fst-italic">No Instructor Comment provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                        @endif

                        @endif

                        <!--  -- Custom Lesson   -->


                        @if($customLessons->isNotEmpty())
                        <strong><i class="fas fa-exclamation-circle"></i> Custom Lessons:</strong>
                        @foreach($customLessons as $def)
                        @php
                        $start = strtotime($def->start_time);
                        $end = strtotime($def->end_time);
                        $duration = max(0, $end - $start);

                        $documents = $def?->instructor?->documents;
                        if ($documents && $documents->licence) {
                        $instructor_lic_no = $documents->licence;
                        } elseif ($documents && $documents->licence_2) {
                        $instructor_lic_no = $documents->licence_2;
                        } else {
                        $instructor_lic_no = 'N/A';
                        }
                        @endphp

                        <div class="row mb-3 p-3 border rounded bg-light shadow-sm">
                            <div class="col-md-6 mb-2 ">
                                <strong><i class="fas fa-book"></i> Lesson Name:</strong>
                                <span class="text-primary">{{ $def->lesson_title ?? 'Untitled' }}</span>
                            </div>
                            <div class="col-md-6 mb-2" style="text-align:end">
                                <button id="edit_custom_lesson" class="btn btn-primary" data-event_id="{{ $def->event_id }}" data-custom-lesson-id="{{$def->id }}" data-lesson-Type="custom">Edit</button>

                                <button id="delete_custom_lesson" class="btn btn-danger" data-event_id="{{ $def->event_id }}" data-custom-lesson-id="{{$def->id }}" data-lesson-Type="custom">Delete</button>
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-chalkboard-teacher"></i> Instructor:</strong>
                                {{ optional($def->instructor)->fname }} {{ optional($def->instructor)->lname }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="bi bi-card-text"></i> License:</strong>
                                {{ $instructor_lic_no }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-toolbox"></i> Resource:</strong><br>
                                {{ $def->resource->name ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-calendar-day"></i> Date:</strong><br>
                                {{ date('d-m-Y', strtotime($def->lesson_date)) }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> Start:</strong><br>
                                {{ date('h:i A', strtotime($def->start_time)) }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> End:</strong><br>
                                {{ date('h:i A', strtotime($def->end_time)) }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-plane-departure"></i> Departure:</strong><br>
                                {{ strtoupper($def->departure_airfield) ?? 'N/A' }}
                            </div>

                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-plane-arrival"></i> Destination:</strong><br>
                                {{ strtoupper($def->destination_airfield) ?? 'N/A' }}
                            </div>
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-clock"></i> Lesson Type:</strong><br>
                                {{ ucfirst($def->deftasks?->subddddLesson?->courseLesson?->lesson_type ?? 'N/A') }}

                            </div>
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Credited Hours:</strong><br>
                                {{ $def->defLesson->hours_credited ?? '00:00' }}
                            </div>
                            @if($def->operation != 0)
                            <div class="col-md-2 mt-2">
                                <strong><i class="fas fa-hourglass-half"></i> Operation:</strong><br>
                                @if($def->operation == 1)
                                PF in LHS
                                @elseif($def->operation == 2)
                                PM in LHS
                                @elseif($def->operation == 3)
                                PF in RHS
                                @elseif($def->operation == 4)
                                PM in RHS
                                @else
                                N/A
                                @endif
                            </div>
                            @endif

                            <!-- {{-- Lesson Summary --}} -->
                            @if(!empty($def->lesson_summary))
                            <div class="col-md-12 mt-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white py-0">
                                        <i class="bi bi-journal-text me-2"></i> Lesson Summary
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($def->lesson_summary))
                                        <p class="mb-0 text-muted">
                                            {{ $def->lesson_summary }}
                                        </p>
                                        @else
                                        <p class="mb-0 text-muted fst-italic">No Lesson summary provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- //  Instructor Comment -->

                            @if(!empty($def->instructor_comment))
                            <div class="col-md-12 mt-3">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-primary text-white py-0">
                                        <i class="bi bi-journal-text me-2"></i> Instructor Comment
                                    </div>
                                    <div class="card-body">
                                        @if(!empty($def->instructor_comment ))
                                        <p class="mb-0 text-muted">
                                            {{ $def->instructor_comment }}
                                        </p>
                                        @else
                                        <p class="mb-0 text-muted fst-italic">No Instructor Comment provided.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                        @endif



                        <!--  -- End Custom Lesson   -->
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
                                $credited = strtotime("1970-01-01 {$eventLesson?->hours_credited}") ?: 0;
                                $customCredited = strtotime("1970-01-01 {$eventLesson?->custom_hours_credited}") ?: 0;
                                $customTime = $eventLesson?->lesson?->customTime ?? null;
                                $totals = [
                                'groundschool' => ['duration' => 0, 'credited' => 0],
                                'simulator' => ['duration' => 0, 'credited' => 0],
                                'flight' => ['credited' => 0],
                                'custom' => [],
                                'deferred' => 0,
                                ];

                                if ($lessonType === 'groundschool') {
                                $groundschoolLessons = $trainingEvent->eventLessons->filter(function ($lesson) {
                                return $lesson->lesson?->lesson_type === 'groundschool';
                                });
                                $totals['groundschool']['duration'] = ($trainingEvent->course->groundschool_hours ?? 0) * $groundschoolLessons->count();

                                $totals['groundschool']['credited'] = $credited;
                                } elseif ($lessonType === 'simulator') {
                                $totals['simulator']['duration'] = $trainingEvent->course->simulator_hours ?? 0;
                                $totals['simulator']['credited'] = $credited;
                                } elseif ($lessonType === 'flight') {
                                $totals['flight']['credited'] = $credited;
                                }


                                if ($customTime) {
                                $totals['custom'][$customTime->name]['allotted'] = $customTime->given_hours;
                                $totals['custom'][$customTime->name]['credited'] = $customCredited;
                                }

                                if(isset($deferredLessons) && $deferredLessons->isNotEmpty()) {
                                foreach($deferredLessons as $defLesson) {
                                $start = strtotime($defLesson->start_time);
                                $end = strtotime($defLesson->end_time);
                                $duration = max(0, $end - $start);
                                $totals['deferred'] += $duration;
                                }
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

                                <!-- <p>
                                    <strong>Flight:</strong>
                                    Credited: {{ formatSeconds($totals['flight']['credited']) }}
                                </p> -->

                                @if(!empty($totals['custom']))
                                @foreach($totals['custom'] as $name => $custom)
                                <p>
                                    <strong>Custom ({{ $name }}):</strong>
                                    Allotted: {{ $custom['allotted'] }} |
                                    Credited: {{ formatSeconds($custom['credited']) }}
                                </p>
                                @endforeach
                                @endif
                                <?php
                                $totalFlightTime = $totals['flight']['credited'] + $totals['deferred'];

                                ?>
                                <p>
                                    <strong>Total Flight Time:</strong>
                                    {{ formatSeconds($totalFlightTime) }}
                                </p>

                                <!-- @if($totals['deferred'] > 0)
                                    <p>
                                        <strong>Deferred Lessons:</strong>
                                        Credited: {{ formatSeconds($totals['deferred']) }}
                                    </p>
                                @endif -->

                                @else
                                <?php
                                $totals = [
                                    'groundschool' => ['duration' => 0, 'credited' => 0],
                                    'simulator' => ['duration' => 0, 'credited' => 0],
                                    'flight' => ['credited' => 0],
                                    'custom' => [],
                                    'deferred' => 0,
                                    'customDuration' => 0,
                                ];
                                $groundschoolLessons = $trainingEvent->eventLessons->filter(function ($lesson) {
                                    return $lesson->lesson?->lesson_type === 'groundschool';
                                });
                                $totals['groundschool']['duration'] = ($trainingEvent->course->groundschool_hours ?? 0);

                                ?>


                                @foreach($trainingEvent->eventLessons as $lesson)
                                <?php
                                if (!function_exists('toSeconds')) {
                                    function toSeconds(?string $time): int
                                    {
                                        if (!$time) return 0;
                                        [$h, $m] = array_pad(explode(':', $time), 2, 0);
                                        return ($h * 3600) + ($m * 60);
                                    }
                                }
                                $type = $lesson->lesson?->lesson_type ?? '';
                                $credited = toSeconds($lesson->hours_credited);

                                if ($type === 'groundschool') {
                                    $totals['groundschool']['credited'] += $credited;
                                } elseif ($type === 'simulator') {
                                    $totals['simulator']['duration'] = $trainingEvent->course->simulator_hours ?? 0;
                                    $totals['simulator']['credited'] += $credited;
                                } elseif ($type === 'flight') {

                                    $totals['flight']['credited'] += $credited;
                                }

                                if ($lesson->lesson?->customTime) {
                                    $custom = $lesson->lesson->customTime;
                                    $totals['custom'][$custom->name]['allotted'] = $custom->hours;
                                    $totals['custom'][$custom->name]['credited'] = ($totals['custom'][$custom->name]['credited'] ?? 0) + strtotime("1970-01-01 {$lesson->custom_hours_credited}");
                                }
                                ?>
                                @endforeach

                                @if(isset($deferredLessons) && $deferredLessons->isNotEmpty())
                                @foreach($deferredLessons as $defLesson)

                                <?php $lessonType = $defLesson?->deftasks?->subddddLesson?->courseLesson?->lesson_type;
                                ?>
                                <?php
                                $start = strtotime($defLesson->start_time);
                                $end = strtotime($defLesson->end_time);
                                $duration = max(0, $end - $start);

                                if ($lessonType === 'flight') {
                                    // $totals['flight']['credited'] += $credited;
                                    $totals['deferred'] += $duration;
                                } elseif ($lessonType === 'groundschool') {
                                    $totals['flight']['credited'] += $credited;
                                } elseif ($lessonType === 'simulator') {
                                    $totals['flight']['credited'] += $credited;
                                }

                                ?>
                                @endforeach
                                @endif

                                @if(isset($customLessons) && $customLessons->isNotEmpty())
                                @foreach($customLessons as $custom)

                                <?php

                                $lessonType = $custom?->deftasks?->subddddLesson?->courseLesson?->lesson_type;

                                if ($lessonType === 'flight') {
                                    $start = strtotime($custom->start_time);
                                    $end = strtotime($custom->end_time);

                                    $cus_duration = max(0, $end - $start);

                                    if (!isset($totals['customDuration'])) {
                                        $totals['customDuration'] = 0;
                                    }
                                    $totals['customDuration'] += $cus_duration;
                                }
                                ?>

                                @endforeach
                                @endif



                                @if($totals['groundschool']['duration'] || $totals['groundschool']['credited'])
                                <p>
                                    <strong>Ground School:</strong>
                                    Duration: {{ number_format($totals['groundschool']['duration'], 2) ?? 'N/A' }} |
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

                                <!-- <p>
                                    <strong>Flight:</strong>
                                    Credited: {{ formatSeconds($totals['flight']['credited']) }}
                                </p> -->
                                <?php

                                $totalFlightTime = $totals['flight']['credited'] + $totals['deferred'] + $totals['customDuration'];

                                ?>
                                <p>
                                    <?php

                                    ?>
                                    <strong>Total Flight Time:</strong>
                                    {{ formatSeconds($totalFlightTime) }}
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


                                @if($totals['deferred'] > 0)
                                <!-- <p>
                                        <strong>Deferred Lessons:</strong>
                                        Credited: {{ formatSeconds($totals['deferred']) }}
                                    </p> -->
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
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong>{{ $title }}</strong>
                                                <span class="badge {{ $badgeClass }} ms-2">{{ $badgeText }}</span>
                                            </div>
                                            <div class="ms-auto">
                                                <button class="btn btn-sm btn-primary normal_lesson" data-title="{{ $title }}" data-def-id="{{ $item->task_id }}"> + Normal Lesson</button>
                                            </div>
                                        </div>
                                        @if($comment)
                                        <div class="text-muted ps-1 mt-1">
                                            <em>Reason: {{ $comment }}</em>
                                        </div>
                                        @endif
                                    </li>
                                    <div>
                                    </div>
                                    @endforeach
                                </ul>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0 text-primary">Add More Deferred Lessons</h5>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeferredLessonModal" id="addDeferredLesson">
                                        + Deferred Lesson
                                    </button>
                                </div>
                            </div>

                            @endif
                            <?php
                            foreach ($eventLessons as $eventLesson) {
                                $lesson = $eventLesson->lesson;

                                if ($lesson && $lesson->subLessons->isNotEmpty()) {
                                    foreach ($lesson->subLessons as $sublesson) {
                                    }
                                }
                            }

                            ?>

                            @if(Auth::user()->is_admin == "1" || Auth::user()->role ==18)
                            <div class="d-flex justify-content-between align-items-center mb-2 custom_lesson" style="margin-left: 21px;">
                                <h5 class="mb-0 text-primary">Add Custom Lessons</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeferredLessonModal" id="open_add_custom_lesson">
                                    + Custom Lesson
                                </button>
                            </div>
                            @endif
                        </div>


                        {{-- Deferred Lesson Modal Start --}}
                        <div class="modal fade" id="addDeferredLessonModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog">
                                <form action="" method="POST" id="deferredLessonForm">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                                    <input type="hidden" name="std_id" value="{{ $trainingEvent->student_id }}">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Deferred Lesson</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-2">
                                                <label class="form-label">Lesson Title <span class="text-danger">*</span></label>
                                                <input type="text" name="lesson_title" class="form-control">
                                                <input type="hidden" name="lesson_type" id="lesson_type" class="form-control">
                                                <div id="lesson_title_error" class="text-danger error_e"></div>
                                            </div>
                                            <!-- <div class="mb-2">
                                                <label class="form-label">Rank</label>
                                           <select class="form-select" name="rank" id="add_deferred_rank"> 
                                                @if($trainingEvent->course->enable_mp_lifus == 1)
                                                    <option value="1">Captain</option>
                                                @else
                                                    <option value="1">Captain</option>
                                                    <option value="2">First Officer</option>
                                                    <option value="3">Second Officer</option>
                                                @endif
                                            </select>
                                            </div> -->
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
                                                <label class="form-label">Departure Airfield</label>
                                                <input type="text" name="departure_airfield" class="form-control" maxlength="4" value="">
                                                <div id="departure_airfield" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Destination Airfield</label>
                                                <input type="text" name="destination_airfield" class="form-control" maxlength="4" value="">
                                                <div id="destination_airfield" class="text-danger error_e"></div>
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

                                            @if($trainingEvent->course->enable_mp_lifus == 2 || $trainingEvent->course->enable_mp_lifus == 3)
                                            <div class="mb-2">
                                                <label class="form-label"> Operation </label>
                                                <select class="form-select" name="operation">
                                                    <option value="0">Select Operation</option>
                                                    <option value="1">PF in LHS</option>
                                                    <option value="2">PM in LHS</option>
                                                    <option value="3">PF in RHS</option>
                                                    <option value="4">PM in RHS</option>
                                                </select>
                                            </div>
                                            @endif
                                            <div class="mb-2 select_task" style="display:none">
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
                                            <div class="mb-2 select_course_task">
                                                <div class="form-group">
                                                    <label for="courses" class="form-label">
                                                        Courses <span class="text-danger">*</span>
                                                    </label>

                                                    <div class="course-dropdown" id="course-task-dropdown" data-control="checkbox-dropdown">
                                                        <label class="dropdown-label">Select Lesson </label>
                                                        <div class="dropdown-list">
                                                            <a href="#" id="check-all-courses" data-toggle="check-all" class="dropdown-option">Check All</a>

                                                            @foreach($eventLessons as $eventLesson)
                                                            @php $lesson = $eventLesson->lesson; @endphp
                                                            @if($lesson && $lesson->subLessons->isNotEmpty())
                                                            <div class="dropdown-option fw-bold">{{ $lesson->lesson_title }}</div>
                                                            @foreach($lesson->subLessons as $sublesson)

                                                            <label class="dropdown-option ps-3" for="sublesson-{{ $sublesson->id }}">
                                                                <input type="checkbox"
                                                                    name="select_courseTask[]"
                                                                    value="{{ $sublesson->id }}"
                                                                    id="sublesson-{{ $sublesson->id }}">
                                                                {{ $sublesson->title }}
                                                            </label>
                                                            @endforeach
                                                            @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>


                                                <div id="select_courseTask_error" class="text-danger error_e"></div>
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

                        {{-- Normal Lesson Modal Start --}}
                        <div class="modal fade" id="normalLessonModel" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog">
                                <form id="normalLessonForm">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">

                                    <input type="hidden" name="std_id" value="{{ $trainingEvent->student_id }}">
                                    <input type="hidden" name="def_id" id="def_id">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Add to Normal Lesson</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">

                                            <div class="mb-2">
                                                <div class="card-body p-0">
                                                    <h6 class="fw-bold mb-0 text-primary">
                                                        Deferred Lesson: <span id="add_title" class="text-dark"></span>
                                                    </h6>
                                                </div><br>
                                                <label class="form-label">Transferred to Lesson <span class="text-danger">*</span></label>
                                                <select class="form-select" name="lesson" id="normalLessonId">
                                                    @foreach($eventLessons as $eventLesson)
                                                    @php $lesson = $eventLesson->lesson; @endphp
                                                    @if($lesson && $lesson->subLessons->isNotEmpty())
                                                    <option value="{{ $lesson->id }}"> {{ $lesson->lesson_title }} </option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-primary" type="submit" id="submitNormalItems">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Normal Lesson Modal End --}}


                        <!-- -- Edit Deffered Lesson-- -->
                        <div class="modal fade" id="edit_DeferredLessonModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog">
                                <form action="" method="POST" id="update_deferredLessonForm">
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
                                                <input type="text" name="lesson_title" id="edit_lesson_title" class="form-control">
                                                <input type="hidden" name="lesson_type" id="edit_lesson_type" class="form-control">
                                                <input type="hidden" name="deferredLessons_id" id="deferredLessons_id" class="form-control">
                                                <div id="lesson_title_uperror" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                                <input type="date" name="lesson_date" id="edit_lesson_date" class="form-control">
                                                <div id="lesson_date_uperror" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                                <input type="time" name="start_time" id="edit_start_time" class="form-control">
                                                <div id="start_time_uperror" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                                <input type="time" name="end_time" class="form-control" id="edit_end_time">
                                                <div id="end_time_uperror" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Departure Airfield</label>
                                                <input type="text" name="departure_airfield" class="form-control" maxlength="4" id="edit_departure_airfield">
                                                <div id="departure_airfield_uperror" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Destination Airfield</label>
                                                <input type="text" name="destination_airfield" class="form-control" maxlength="4" id="edit_destination_airfield">
                                                <div id="destination_airfield_uperror" class="text-danger error_e"></div>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label">Instructor <span class="text-danger">*</span></label>
                                                <select class="form-select" name="instructor_id" id="edit_instructor">
                                                    <option value="">Select Instructor</option>
                                                    @foreach($instructors as $row)
                                                    <option value="{{ $row->id }}" {{ (auth()->user()->id == $row->id) ? 'selected': '' }}>{{ $row->fname }} {{ $row->lname }}</option>
                                                    @endforeach
                                                </select>
                                                <div id="instructor_id_uperror" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Resource <span class="text-danger">*</span></label>
                                                <select class="form-select" name="resource_id" id="edit_resource_id">
                                                    <option value="">Select Resource</option>
                                                    @foreach($resources as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div id="resource_id_uperror" class="text-danger error_e"></div>
                                            </div>

                                            @if($trainingEvent->course->enable_mp_lifus == 2 || $trainingEvent->course->enable_mp_lifus == 3)
                                            <div class="mb-2">
                                                <label class="form-label">Operation </label>
                                                <select class="form-select" name="edit_operation" id="edit_operation">
                                                    <option value="0">Select Operation</option>
                                                    <option value="1">PF in LHS</option>
                                                    <option value="2">PM in LHS</option>
                                                    <option value="3">PF in RHS</option>
                                                    <option value="4">PM in RHS</option>
                                                </select>
                                            </div>
                                            @endif
                                            <div class="mb-2 select_task" id="select_task">
                                                <label class="form-label">Select Tasks <span class="text-danger">*</span></label>
                                                {{-- TaskGrading items --}}
                                                @if(isset($defTasks) && $defTasks->isNotEmpty())
                                                @foreach($defTasks as $item)
                                                <div class="form-check previous" style="margin-left: 22px;">
                                                    <input type="checkbox" class="form-check-input" name="item_ids[]" value="{{ $item->task_id }}">
                                                    <label class="form-check-label">
                                                        {{ $item->task_title ?? 'N/A' }}
                                                    </label>
                                                </div>
                                                @endforeach
                                                @endif
                                                <div class="form-check taskContainer">

                                                </div>

                                                <!-- <div class="form-check ">
                                                
                                                </div> -->

                                                <div id="item_ids_uperror" class="text-danger error_e"></div>
                                            </div>
                                            <div class="mb-2 select_course_task" id="custom_select_course_task">
                                                <div class="form-group">
                                                    <label for="courses" class="form-label">
                                                        Courses <span class="text-danger">*</span>
                                                    </label>

                                                    <div class="course-dropdown" id="course-task-dropdown" data-control="checkbox-dropdown">
                                                        <label class="dropdown-label">Select Courses</label>
                                                        <div class="dropdown-list">
                                                            <a href="#" id="check-all-courses" data-toggle="check-all" class="dropdown-option">Check All</a>

                                                            @foreach($eventLessons as $eventLesson)
                                                            @php $lesson = $eventLesson->lesson; @endphp
                                                            @if($lesson && $lesson->subLessons->isNotEmpty())
                                                            <div class="dropdown-option fw-bold">{{ $lesson->lesson_title }}</div>
                                                            @foreach($lesson->subLessons as $sublesson)
                                                            <label class="dropdown-option ps-3">
                                                                <input type="checkbox"
                                                                    name="select_courseTask[]"
                                                                    value="{{ $sublesson->id }}">
                                                                {{ $sublesson->title }}
                                                            </label>
                                                            @endforeach
                                                            @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="select_courseTask_uperror" class="text-danger error_e"></div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-primary" type="submit" id="submitDeferredItems">Update</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- -- Edit Deffered Lesson -- -->

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
                                            <input type="file" name="training_event_documents[{{ $doc->id }}]" class="form-control">
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
                        @foreach($eventLessons as $eventLesson)
                        <?php $isDisabled = false; ?>
                        <form action="" method="POST" id="gradingFrom">
                            @csrf
                            <input type="hidden" name="event_id" id="event_id" value="{{ $trainingEvent->id }}">
                            <div class="accordion accordion-flush" id="faq-group-2">
                                <?php
                                $hours_credited = $eventLesson->hours_credited;
                                $hours_credited = "08:02:00"; // example

                                list($hours, $minutes, $seconds) = explode(':', $eventLesson->hours_credited);

                                $hours = (int) $hours;
                                $minutes = (int) $minutes;

                                $hours_credited = $minutes > 0
                                    ? "{$hours}hrs {$minutes}min"
                                    : "{$hours}hrs";

                                $lesson = $eventLesson->lesson;
                                $isLocked = $eventLesson->is_locked == 1;
                                ?>


                                <?php
                                if ($lesson->lesson_type == "groundschool") {
                                    $duration = $trainingEvent->course->groundschool_hours ?? 0;
                                } elseif ($lesson->lesson_type == "simulator") {
                                    $duration = $trainingEvent->course->simulator_hours ?? 0;
                                } else {
                                    $duration = 0;
                                }

                                $formattedDuration = number_format($duration, 2);
                                $hourLabel = ($formattedDuration == 1.00) ? 'hour' : 'hours';
                                ?>
                                <div class="accordion-item">
                                    <input type="hidden" name="tg_lesson_id[]" value="{{ $eventLesson->id }}">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $isLocked ? 'collapsed' : 'collapsed' }}"
                                            type="button"
                                            {{ $isLocked ? '' : 'data-bs-toggle=collapse' }}
                                            {{ $isLocked ? '' : 'data-bs-target=#lesson-' . $eventLesson->id }}
                                            aria-expanded="false"
                                            aria-controls="lesson-{{ $eventLesson->id }}"
                                            style="{{ $isLocked ? 'cursor: not-allowed; background-color: #f8f9fa;' : '' }}">

                                            {{ $lesson->lesson_title ?? 'Untitled Lesson' }}
                                            (Duration: {{ $hours_credited }} / {{ number_format($duration, 2) }} hrs)

                                            @if($isLocked && !empty($groupedLogs[$eventLesson->lesson_id]))
                                            @php
                                            $lastLog = $groupedLogs[$eventLesson->lesson_id]->last();
                                            $lockedBy = trim(($lastLog->users->fname ?? '') . ' ' . ($lastLog->users->lname ?? ''));
                                            @endphp
                                            <span>(Locked By - {{ $lockedBy ?? '' }}, Time - {{ ($lastLog->created_at->format('d M Y, h:i A')) ?? '' }})</span>
                                            @endif

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
                                                <i class="bi bi-lock-fill" data-training-event-leeson="{{ $eventLesson->id }}"></i>
                                            </span>
                                            @endif
                                            @endif
                                        </button>

                                    </h2>
                                    <div class="d-flex flex-wrap gap-3 mb-3 small-text text-muted">
                                        <div><strong>Instructor:</strong> {{ $eventLesson->instructor->fname ?? '' }} {{ $eventLesson->instructor->lname ?? '' }}</div>
                                        <div><strong>Licence No:</strong> {{ !empty($eventLesson->instructor_license_number) ? $eventLesson->instructor_license_number : 'N/A' }}</div>
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
                                            <?php $is_my_lesson = $eventLesson->is_my_lesson; ?>
                                            <div class="custom-box">
                                                <input type="hidden" name="tg_subLesson_id[]" value="{{ $sublesson->id }}">
                                                <div class="header grade_head" data-bs-toggle="collapse" data-bs-target="#comment-box-{{ $sublesson->id }}" aria-expanded="false">

                                                    <div class="task-desc">
                                                        <span class="rmk">RMK</span>
                                                        <span class="question-mark">?</span>
                                                        <span class="title">{{ $sublesson->title }}</span>
                                                    </div>
                                                    <i class="grade-comment">click to enter comment</i>
                                                </div>

                                                <div class="table-container">
                                                    @php
                                                    $taskGrade = $taskGrades[$lesson->id . '_' . $sublesson->id] ?? null;
                                                    $selectedGrade = $taskGrade->task_grade ?? null;

                                                    $selectedComment = $taskGrade->task_comment ?? null;
                                                    $isDeferred = in_array($sublesson->id, $deferredTaskIds);
                                                    $is_my_lesson = $eventLesson->is_my_lesson;
                                                    $isDisabled = !$is_my_lesson;
                                                    @endphp


                                                    <div class="main-tabledesign">
                                                        <div class="back_deffered">
                                                            @if($sublesson->normal_lesson == 1)
                                                            <a class="btn btn-sm btn-danger backToDeferredLesson"
                                                                data-event-id="{{ $sublesson->event_id }}"
                                                                data-user-id="{{ $sublesson->user_id }}"
                                                                data-task-id="{{ $sublesson->task_id }}"
                                                                data-lesson-id="{{ $lesson->id  }}"
                                                                data-sublesson-id="{{ $sublesson->id }}">
                                                                <i class="bi bi-arrow-left-circle me-1"></i> Back To Deferred Lesson
                                                            </a>
                                                            @endif
                                                        </div>
                                                        <div class="grade_here_cont">
                                                            <input type="hidden" name="tg_user_id" value="{{ $student->id ?? '' }}">
                                                            <!-- <h5>{{ $student->fname ?? '' }} {{ $student->lname ?? '' }}</h5> -->
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
                                                                                <input type="radio" name="task_grade[{{ $lesson->id }}][{{ $sublesson->id }}]" value="Further training required" {{ $selectedGrade == 'Further training required' ? 'checked' : '' }} {{ $isDeferred ? 'disabled' : '' }}>
                                                                                <span class="custom-radio ftr">FTR</span>
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
                                                                            $colorClass=$i==1 ? 'incomplete' : ($i==2 ? 'ftr' : 'competent' );
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
                                                            <span class="custom-radio competent task_grade_{{ $lesson->id }}_{{ $sublesson->id }}_{{ $student->id ?? '' }}"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Toggleable Comment Box -->
                                            <div class="collapse mt-2" id="comment-box-{{ $sublesson->id }}">
                                                <textarea name="task_comments[{{ $lesson->id ?? '' }}][{{ $sublesson->id ?? '' }}]" rows="3" class="form-control" placeholder="Add your remarks or feedback here..." @if($isDeferred) readonly title="Deferred: You cannot edit this comment." @endif>{{ old("task_comments.$lesson->id.$sublesson->id.$student->id", $selectedComment) }}</textarea>
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
                                                <div class="custom-box">
                                                    <div class="header" data-bs-toggle="collapse" data-bs-target="#competency-box-{{ $code }}" aria-expanded="false">
                                                        <span class="rmk">RMK</span>
                                                        <span class="question-mark">?</span>
                                                        <span class="title"><span class="highlight">{{ $title }} ({{ strtoupper($code) }})</span></span>
                                                        <input type="hidden" name="cg_lesson_id" value="{{ $lesson->id }}">
                                                    </div>
                                                    <div class="table-container">
                                                        <div class="main-tabledesign">
                                                            <input type="hidden" name="cg_user_id" value="{{ $student->id ?? '' }}">
                                                            <table>
                                                                <tbody>
                                                                    <tr>
                                                                        @for ($i = 1; $i <= 5; $i++)
                                                                            @php
                                                                            $colorClass=$i==1 ? 'incomplete' : ($i==2 ? 'ftr' : 'competent' );
                                                                            @endphp
                                                                            <td>
                                                                            <label class="radio-label">
                                                                                <input type="radio" class="scale-radio"
                                                                                    name="comp_grade[{{ $lesson->id }}][{{ $code }}]"
                                                                                    value="{{ $i }}" data-event-id="{{ $trainingEvent->id }}" data-lesson-id="{{ $lesson->id }}" data-user-id="{{ $student->id ?? '' }}" data-code="{{ $code }}" data-color-class="{{ $colorClass }}"
                                                                                    {{ $selectedCompGrade == $i ? 'checked' : '' }}>
                                                                                <span class="custom-radio {{ $colorClass }}">{{ $i }}</span>
                                                                            </label>
                                                                            </td>
                                                                            @endfor
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <span class="custom-radio competent comp_grade_{{ $lesson->id }}_{{ $student->id ?? '' }}"></span>
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
                                        <div>
                                            <label>Lesson Summary</label>
                                            <textarea name="lesson_summary[{{ $lesson->id }}]" rows="3" class="form-control" placeholder="Write Lesson Summary">{{ old("lesson_summary.$lesson->id", $eventLesson->lesson_summary ?? '') }}</textarea>
                                        </div>

                                        @if(Auth::user()->role == "1")
                                        <div>
                                            <label>Instructor Comment</label>
                                            <textarea name="instructor_summary[{{ $lesson->id }}]" rows="3" class="form-control" placeholder="Instructor Comment">{{ old("lesson_summary.$lesson->id", $eventLesson->instructor_comment ?? '') }}</textarea>
                                        </div>
                                        @endif

                                        <!-- Examiner CBTA -->
                                        <div class="accordion-item">
                                            @if($lesson->examiner_cbta == 1)
                                            <h2 class="accordion-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <button type="button" class="accordion-button" data-bs-toggle="collapse"
                                                        data-bs-target="#examiner-{{ $eventLesson->id }}" aria-expanded="false">
                                                        Examiner Competency Grading
                                                    </button>
                                                </div>
                                            </h2>
                                            @endif
                                        </div>

                                        <div id="examiner-{{ $eventLesson->id }}" class="accordion-collapse collapse">
                                            <!-- Student name aligned to the right, above the competency grading -->
                                            <div class="text-end pe-4 pt-2 fw-semibold">
                                                {{ $student->fname }} {{ $student->lname }}
                                            </div>

                                            <div class="accordion-body">
                                                @foreach($examiner_cbta as $val)
                                                @php
                                                // Find grading for this competency and lesson
                                                $savedGrade = collect($examiner_grading)->first(function($g) use ($val, $lesson) {
                                                return $g['cbta_gradings_id'] == $val['id'] && $g['lesson_id'] == $lesson->id;
                                                });
                                                @endphp

                                                <div class="custom-box">
                                                    <div class="header" data-bs-toggle="collapse" data-bs-target="#competency-box-{{ $val['id'] }}" aria-expanded="false">
                                                        <span class="rmk">RMK</span>
                                                        <span class="question-mark">?</span>
                                                        <span class="title"><span class="highlight">{{ $val['competency'] }} ({{ $val['short_name'] }})</span></span>
                                                        <input type="hidden" name="cg_lesson_id" value="{{ $lesson->id }}">
                                                    </div>

                                                    <div class="table-container">
                                                        <div class="main-tabledesign">
                                                            <input type="hidden" name="cg_user_id" value="{{ $student->id ?? '' }}">
                                                            <table>
                                                                <tbody>
                                                                    <tr>
                                                                        @for ($i = 1; $i <= 5; $i++)
                                                                            @php
                                                                            $colorClass=$i==1 ? 'incomplete' : ($i==2 ? 'ftr' : 'competent' );
                                                                            @endphp
                                                                            <td>
                                                                            <label class="radio-label">
                                                                                <input type="radio" class="scale-radio"
                                                                                    name="examiner_grade[{{ $lesson->id }}][{{ $val['id'] }}]"
                                                                                    value="{{ $i }}"
                                                                                    data-event-id="{{ $trainingEvent->id }}"
                                                                                    data-lesson-id="{{ $lesson->id }}"
                                                                                    data-user-id="{{ $student->id ?? '' }}"
                                                                                    data-code="{{ $val['id'] }}"
                                                                                    data-color-class="{{ $colorClass }}"
                                                                                    {{-- check if saved grade matches --}}
                                                                                    {{ isset($savedGrade['competency_value']) && $savedGrade['competency_value'] == $i ? 'checked' : '' }}>
                                                                                <span class="custom-radio {{ $colorClass }}">{{ $i }}</span>
                                                                            </label>
                                                                            </td>
                                                                            @endfor
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <span class="custom-radio competent comp_grade_{{ $lesson->id }}_{{ $student->id ?? '' }}"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Toggleable Comment Box -->
                                                <div class="collapse mt-2" id="competency-box-{{ $val['id'] }}">
                                                    <textarea name="examiner_comments[{{ $lesson->id }}][{{ $val['id'] }}]"
                                                        rows="3"
                                                        class="form-control"
                                                        placeholder="Add remarks or comments on competency">{{ $savedGrade['comment'] ?? '' }}</textarea>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Instructor CBTA -->
                                        <div class="accordion-item">
                                            @if($lesson->instructor_cbta == 1)
                                            <h2 class="accordion-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <button type="button" class="accordion-button" data-bs-toggle="collapse"
                                                        data-bs-target="#instructor-{{ $eventLesson->id }}" aria-expanded="false">
                                                        Instructor Competency Grading
                                                    </button>
                                                </div>
                                            </h2>
                                            @endif
                                        </div>

                                        <div id="instructor-{{ $eventLesson->id }}" class="accordion-collapse collapse">
                                            <!-- Student name aligned to the right, above the competency grading -->
                                            <div class="text-end pe-4 pt-2 fw-semibold">
                                                {{ $student->fname }} {{ $student->lname }}
                                            </div>

                                            <div class="accordion-body">
                                                @foreach($instructor_cbta as $val)
                                                @php
                                                // Find grading for this competency and lesson
                                                $savedGrade = collect($instructor_grading)->first(function($g) use ($val, $lesson) {
                                                return $g['cbta_gradings_id'] == $val['id'] && $g['lesson_id'] == $lesson->id;
                                                });
                                                @endphp

                                                <div class="custom-box">
                                                    <div class="header" data-bs-toggle="collapse" data-bs-target="#competency-box-{{ $val['id'] }}" aria-expanded="false">
                                                        <span class="rmk">RMK</span>
                                                        <span class="question-mark">?</span>
                                                        <span class="title"><span class="highlight">{{ $val['competency'] }} ({{ $val['short_name'] }})</span></span>
                                                        <input type="hidden" name="cg_lesson_id" value="{{ $lesson->id }}">
                                                    </div>

                                                    <div class="table-container">
                                                        <div class="main-tabledesign">
                                                            <input type="hidden" name="cg_user_id" value="{{ $student->id ?? '' }}">
                                                            <table>
                                                                <tbody>
                                                                    <tr>
                                                                        @for ($i = 1; $i <= 5; $i++)
                                                                            @php
                                                                            $colorClass=$i==1 ? 'incomplete' : ($i==2 ? 'ftr' : 'competent' );
                                                                            @endphp
                                                                            <td>
                                                                            <label class="radio-label">
                                                                                <input type="radio" class="scale-radio"
                                                                                    name="instructor_grade[{{ $lesson->id }}][{{ $val['id'] }}]"
                                                                                    value="{{ $i }}"
                                                                                    data-event-id="{{ $trainingEvent->id }}"
                                                                                    data-lesson-id="{{ $lesson->id }}"
                                                                                    data-user-id="{{ $student->id ?? '' }}"
                                                                                    data-code="{{ $val['id'] }}"
                                                                                    data-color-class="{{ $colorClass }}"
                                                                                    {{-- check if saved grade matches --}}
                                                                                    {{ isset($savedGrade['competency_value']) && $savedGrade['competency_value'] == $i ? 'checked' : '' }}>
                                                                                <span class="custom-radio {{ $colorClass }}">{{ $i }}</span>
                                                                            </label>
                                                                            </td>
                                                                            @endfor
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <span class="custom-radio competent comp_grade_{{ $lesson->id }}_{{ $student->id ?? '' }}"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Toggleable Comment Box -->
                                                <div class="collapse mt-2" id="competency-box-{{ $val['id'] }}">
                                                    <textarea name="instructor_comments[{{ $lesson->id }}][{{ $val['id'] }}]"
                                                        rows="3"
                                                        class="form-control"
                                                        placeholder="Add remarks or comments on competency">{{ $savedGrade['comment'] ?? '' }}</textarea>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <!-- ////------------------------------------Overall assessment for multi lesson------------------------------------------------- -->
                                        @if($trainingEvent?->course?->course_type == "multi_lesson")
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                   <button type="button" class="accordion-button" style="cursor: default;">
                                                        Overall Assessment
                                                    </button>

                                                </div>
                                            </h2>
                                        </div>
                                        @endif
                                        <div id="overall-{{ $eventLesson->id }}" class="accordion-collapse show">
                                            <input type="hidden" name="lesson_id" value="{{ $eventLesson->id }}">
                                            <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                                            <input type="hidden" name="user_id" value="{{ $student->id }}">

                                            <div class="assessment-wrapper">
                                                <!-- Result -->
                                                <div class="row mb-3">

                                                   <?php
                                                        $lessonData = $eventLessons->firstWhere('lesson_id', $eventLesson->lesson_id);
                                                    ?>
                                                    <label class="col-sm-2 col-form-label">Result</label>
                                                    <div class="col-sm-6  buttons">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="overall_td">
                                                                        <label class="radio-label">
                                                                            <input type="radio"
                                                                                name="overall_result[{{ $eventLesson->id }}]"
                                                                                value="Incomplete"
                                                                                {{ $lessonData->overall_result == 'Incomplete' ? 'checked' : '' }}>
                                                                            <span class="custom-radio incomplete">Incomplete</span>
                                                                        </label>
                                                                    </td>

                                                                    <td class="overall_td">
                                                                        <label class="radio-label">
                                                                            <input type="radio"
                                                                                name="overall_result[{{ $eventLesson->id }}]"
                                                                                value="Further training required"
                                                                                {{ $lessonData->overall_result == 'Further training required' ? 'checked' : '' }}>
                                                                            <span class="custom-radio ftr">FTR</span>
                                                                        </label>
                                                                    </td>
                                                                         <td class="overall_td">
                                                                        <label class="radio-label">
                                                                            <input type="radio"
                                                                                name="overall_result[{{ $eventLesson->id }}]"
                                                                                value="Competent"
                                                                                {{ $lessonData->overall_result == 'Competent' ? 'checked' : '' }} style="width:100%">
                                                                            <span class="custom-radio competent">Competent</span>
                                                                        </label>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Remark -->
                                                <div class="row mb-3 remark-section">
                                                    <label class="col-sm-2 col-form-label">Remark</label>
                                                    <div class="col-sm-10">
                                                        <textarea class="form-control remark"
                                                            name="overall_remark[{{ $eventLesson->id }}]"
                                                            style="height: 100px"
                                                            placeholder="Enter your remarks here...">{{ $lessonData->overall_remark ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(!empty($groupedLogs[$eventLesson->lesson_id]))
                                        @php
                                        $lessonLogs = $groupedLogs[$eventLesson->lesson_id];
                                        @endphp

                                        <div class="accordion-body mt-3">
                                            <h5 class="text-primary mb-2">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Training Logs
                                            </h5>
                                            <table class="table table-striped table-bordered align-middle mb-0">
                                                <thead class="table-light text-center">
                                                    <tr>
                                                        <th scope="col">User</th>
                                                        <th scope="col">Lesson</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($lessonLogs as $log)
                                                    <tr>
                                                        <td>{{ $log->users->fname ?? '' }} {{ $log->users->lname ?? '' }}</td>
                                                        <td>{{ $log->lesson->lesson_title ?? '' }}</td>
                                                        <td>{{ $log->is_locked ? 'Locked' : 'Unlocked' }}</td>
                                                        <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                        <div class="d-flex align-items-center justify-content-end">
                                            @if($isDisabled)
                                            <div>
                                                <span style="color:red"> You are not eligible to perform any action on this lesson </span>
                                            </div>
                                            @endif
                                            <div class="btn-container ms-3 mt-3">
                                                <button type="submit" class="btn btn-save" id="submitGrading" {{ $isDisabled ? 'disabled' : '' }}>Save Lesson</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endforeach
                        @if($defLessonTasks->isNotEmpty())
                        <h4 class="mb-3 text-primary"><i class="bi bi-exclamation-triangle-fill me-2"></i>Deferred Lessons</h4>
                        @foreach($defLessonTasks->groupBy('def_lesson_id') as $defLessonId => $tasks)
                        <form action="" method="POST" id="defGradingFrom">
                            <input type="hidden" name="lesson_type" value="deferred" />
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

                            <?php $is_locked = $defLesson->is_locked; ?>
                            @csrf
                            <div class="accordion-item">
                                <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                                <input type="hidden" name="tg_def_user_id" value="{{ $trainingEvent?->student_id }}">
                                <input type="hidden" name="tg_def_lesson_id[]" value="{{ $defLesson?->id }}">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $is_locked == 1 ? 'collapsed disabled' : '' }}" @if($is_locked !=1) data-bs-toggle="collapse" data-bs-target="#def-lesson-{{ $defLesson?->id }}" aria-expanded="true" @else disabled aria-expanded="false" style="cursor: not-allowed; background-color:#f8f9fa;" @endif type="button"> {{ $defLesson->lesson_title }} {{-- Show lock inside button, after text, only for instructors --}}
                                        @if($is_locked == 1 && auth()->user()?->is_admin != 1) <span class="ms-2 text-muted" data-bs-toggle="tooltip" title="This lesson is locked"> <i class="bi bi-lock-fill"></i> </span>
                                        @endif
                                        @php
                                        // ✅ Get the most recent log for this lesson
                                        $lessonLogs = $grouped_deferredLogs->get($defLessonId, collect());
                                        $latestLog = $lessonLogs->sortByDesc('created_at')->first();
                                        @endphp

                                        @if($is_locked == 1 && $latestLog)
                                        <small class="text-secondary ms-1">
                                            (Locked By - {{ $latestLog->users->fname ?? '' }} {{ $latestLog->users->lname ?? '' }},
                                            Time - {{ \Carbon\Carbon::parse($latestLog->created_at)->format('d M Y, h:i A') }})

                                        </small>
                                        @endif
                                    </button>

                                    @if($is_locked == 1 && auth()->user()?->is_admin == 1)
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary ms-2 unlock-deflesson-btn"
                                        data-defLesson-id="{{ $defLesson?->id }}"
                                        data-event-id="{{ $defLesson->event_id }}"
                                        data-lesson-type="deferred"
                                        data-bs-toggle="tooltip"
                                        title="Unlock this event to enable grading edits.">
                                        <i class="bi bi-lock-fill"></i>
                                    </button>
                                    @endif
                                </h2>
                                <div class="d-flex flex-wrap gap-3 mb-3 small-text text-muted">
                                    <div><strong>Instructor:</strong> {{ $defLesson->instructor->fname ?? '' }} {{ $defLesson->instructor->lname ?? '' }}</div>
                                    <div><strong>Licence No:</strong> {{ $instructor_lic_no }}</div>
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
                                            <div class="header grade_head" data-bs-toggle="collapse" data-bs-target="#comment-box-{{ $task->id }}" aria-expanded="false">
                                                <div class="task-desc">
                                                    <span class="rmk">RMK</span>
                                                    <span class="question-mark">?</span>
                                                    <span class="title">{{ $task->task->title ?? 'Untitled Task' }}</span>
                                                </div>
                                                <i class="grade-comment">click to enter comment</i>
                                            </div>
                                            <div class="table-container">
                                                <div class="main-tabledesign">
                                                    <!-- <h5>{{ $task->user->fname }} {{ $task->user->lname }}</h5> -->
                                                    @php
                                                    $selectedGrade = $task->task_grade;
                                                    $selectedComment = $task->task_comment;
                                                    $isDeferredGraded = $gradedDefTasksMap->has($task->def_lesson_id . '_' . $task->task_id);
                                                    @endphp
                                                    <table>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                        <input type="radio" name="task_grade_def[{{ $task->id }}][{{ $task->def_lesson_id }}]" value="Incomplete" {{ $selectedGrade == 'Incomplete' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
                                                                        <span class="custom-radio incomplete">Incomplete</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                        <input type="radio" name="task_grade_def[{{ $task->id }}][{{ $task->def_lesson_id }}]" value="Further training required" {{ $selectedGrade == 'Further training required' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
                                                                        <span class="custom-radio ftr">FTR</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                        <input type="radio" name="task_grade_def[{{ $task->id }}][{{ $task->def_lesson_id }}]" value="Competent" {{ $selectedGrade == 'Competent' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
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
                                            <textarea
                                                name="task_comment_def[{{ $task->id }}][{{ $task->def_lesson_id }}]"
                                                rows="3"
                                                class="form-control"
                                                placeholder="Add your remarks or feedback here..."
                                                @if($isDeferredGraded) readonly title="Deferred: You cannot edit this comment." @endif>
                                            {{ old("task_comment_def.$task->id.$task->def_lesson_id", $selectedComment) }}
                                            </textarea>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="accordion-item">
                                        @if($task->task->courseLesson->course->enable_cbta==1)
                                        <h2 class="accordion-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <button type="button" class="accordion-button" data-bs-toggle="collapse"
                                                    data-bs-target="#comptency-{{ $task->id }}" aria-expanded="false">
                                                    Overall Competency Grading
                                                </button>
                                            </div>
                                        </h2>
                                        @endif
                                    </div>
                                    <div id="comptency-{{ $task->id }}" class="accordion-collapse collapse">
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
                                            $lessonCompetencies = $def_grading[$task->defLesson->id] ?? collect();
                                            @endphp
                                            @foreach($competencies as $code => $title)
                                            @php
                                            $code = strtolower($code); // make sure it's lowercase
                                            $grading = $lessonCompetencies->first();

                                            $selectedCompGrade = $grading?->{$code . '_grade'} ?? null;
                                            $selectedCompComment = $grading?->{$code . '_comment'} ?? null;
                                            @endphp

                                            <div class="custom-box">
                                                <div class="header" data-bs-toggle="collapse" data-bs-target="#competency-box-{{ $code }}" aria-expanded="false">
                                                    <span class="rmk">RMK</span>
                                                    <span class="question-mark">?</span>
                                                    <span class="title"><span class="highlight">{{ $title }} ({{ strtoupper($code) }})</span></span>
                                                    <input type="hidden" name="cg_lesson_id" value="{{ $task->defLesson->id }}">
                                                </div>
                                                <div class="table-container">
                                                    <div class="main-tabledesign">
                                                        <input type="hidden" name="cg_user_id" value="{{ $student->id ?? '' }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                        @php
                                                                        $colorClass=$i==1 ? 'incomplete' : ($i==2 ? 'ftr' : 'competent' );
                                                                        @endphp
                                                                        <td>
                                                                        <label class="radio-label">
                                                                            <input type="radio" class="scale-radio"
                                                                                name="comp_grade[{{ $task->defLesson->id }}][{{ $code }}]"
                                                                                value="{{ $i }}" data-event-id="{{ $trainingEvent->id }}" data-lesson-id="{{ $task->defLesson->id }}" data-user-id="{{ $student->id ?? '' }}" data-code="{{ $code }}" data-color-class="{{ $colorClass }}"
                                                                                {{ $selectedCompGrade == $i ? 'checked' : '' }}>
                                                                            <span class="custom-radio {{ $colorClass }}">{{ $i }}</span>
                                                                        </label>
                                                                        </td>
                                                                        @endfor
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <span class="custom-radio competent comp_grade_{{ $task->defLesson->id }}_{{ $student->id ?? '' }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Toggleable Comment Box -->
                                            <div class="collapse mt-2" id="competency-box-{{ $code }}">
                                                <textarea name="comp_comments[{{ $task->defLesson->id }}][{{ $code }}]" rows="3" class="form-control" placeholder="Add remarks or comments on competency">{{ $selectedCompComment }}</textarea>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div>
                                        <label>Lesson Summary</label>
                                        <textarea name="def_lesson_summary[{{ $task->def_lesson_id }}]" rows="3" class="form-control" placeholder="Write Lesson Summary">{{ old("def_lesson_summary.$task->def_lesson_id ", $defLesson->lesson_summary ?? '') }}</textarea>
                                    </div>

                                    <div>
                                        <label>Instructor Comment</label>
                                        <textarea name="def_instructor_summary[{{ $task->def_lesson_id }}]" rows="3" class="form-control" placeholder="Instructor Comment">{{ old("def_instructor_summary.$task->def_lesson_id ", $defLesson->instructor_comment ?? '') }}</textarea>
                                    </div>
                                    <!-- // Logs -->
                                    @if(!empty($grouped_deferredLogs) && $grouped_deferredLogs->count() > 0)
                                    <?php
                                    $lessonLogs = $grouped_deferredLogs->get($defLessonId, collect()); // <-- safe fallback
                                    ?>
                                    @if($lessonLogs->isNotEmpty())
                                    <div class="accordion-body mt-3">
                                        <h5 class="text-primary mb-2">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Training Logs
                                        </h5>
                                        <table class="table table-striped table-bordered align-middle mb-0">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th scope="col">User</th>
                                                    <!-- <th scope="col">Lesson</th> -->
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lessonLogs as $log)
                                                <tr>
                                                    <td>{{ $log->users->fname ?? '' }} {{ $log->users->lname ?? '' }}</td>
                                                    <!-- <td>{{ $log->lesson->lesson_title ?? '' }}</td> -->
                                                    <td>{{ $log->is_locked ? 'Locked' : 'Unlocked' }}</td>
                                                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    @endif
                                    <!-- // End Logs -->
                                    <div class="btn-container mt-4">
                                        <button type="submit" class="btn btn-save" id="submitDefGrading">Save Lesson</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endforeach
                        @endif
                        <!-- // Custom lesson  -->
                        @if($customLessonTasks->isNotEmpty())
                        <h4 class="mb-3 text-primary"><i class="bi bi-exclamation-triangle-fill me-2"></i>Custom Lessons</h4>
                        @foreach($customLessonTasks->groupBy('def_lesson_id') as $defLessonId => $tasks)
                        <form action="" method="POST" id="customGradingFrom">
                            <input type="hidden" name="lesson_type" value="custom" />
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
                            <?php $is_locked = $defLesson->is_locked; ?>
                            @csrf
                            <div class="accordion-item">
                                <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                                <input type="hidden" name="tg_def_user_id" value="{{ $trainingEvent?->student_id }}">
                                <input type="hidden" name="tg_def_lesson_id[]" value="{{ $defLesson?->id }}">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $is_locked == 1 ? 'collapsed disabled' : 'collapsed' }}"
                                        @if($is_locked !=1)
                                        data-bs-toggle="collapse"
                                        data-bs-target="#def-lesson-{{ $defLesson?->id }}"
                                        aria-expanded="false"
                                        @else
                                        disabled
                                        aria-expanded="false"
                                        style="cursor: not-allowed; background-color:#f8f9fa;"
                                        @endif
                                        type="button">

                                        {{ $defLesson->lesson_title }}

                                        {{-- Show lock inside button, after text, only for instructors --}}
                                        @if($is_locked == 1 && auth()->user()?->is_admin != 1)
                                        <span class="ms-2 text-muted" data-bs-toggle="tooltip" title="This lesson is locked">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>

                                        @endif
                                        <?php
                                        if ($is_locked == 1) {
                                            $lessonLogs = $grouped_customLogs->get($defLessonId, collect());
                                            $latestLog = $lessonLogs->sortByDesc('created_at')->first();
                                        }
                                        ?>

                                        @if($is_locked == 1 && $latestLog)
                                        <small class="text-secondary ms-1">
                                            (Locked By - {{ $latestLog->users->fname ?? '' }} {{ $latestLog->users->lname ?? '' }},
                                            Time - {{ \Carbon\Carbon::parse($latestLog->created_at)->format('d M Y, h:i A') }})

                                        </small>
                                        @endif
                                    </button>


                                    @if($is_locked == 1 && auth()->user()?->is_admin == 1)
                                    {{-- Unlock button for admin --}}
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary ms-2 unlock-deflesson-btn"
                                        data-defLesson-id="{{ $defLesson?->id }}"
                                        data-event-id="{{ $defLesson->event_id }}"
                                        data-lesson-type="custom"
                                        data-bs-toggle="tooltip"
                                        title="Unlock this event to enable grading edits.">
                                        <i class="bi bi-lock-fill"></i>
                                    </button>
                                    @endif
                                </h2>

                                <div class="d-flex flex-wrap gap-3 mb-3 small-text text-muted">
                                    <div><strong>Instructor:</strong> {{ $defLesson->instructor->fname ?? '' }} {{ $defLesson->instructor->lname ?? '' }}</div>
                                    <div><strong>Licence No:</strong> {{ $instructor_lic_no }}</div>
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
                                            <div class="header grade_head" data-bs-toggle="collapse" data-bs-target="#comment-box-{{ $task->id }}" aria-expanded="false">
                                                <div class="task-desc">
                                                    <span class="rmk">RMK</span>
                                                    <span class="question-mark">?</span>
                                                    <span class="title">{{ $task->task->title ?? 'Untitled Task' }}</span>
                                                </div>
                                                <i class="grade-comment">click to enter comment</i>
                                            </div>
                                            <div class="table-container">
                                                <div class="main-tabledesign">
                                                    <!-- <h5>{{ $task->user->fname }} {{ $task->user->lname }}</h5> -->
                                                    @php
                                                    $selectedGrade = $task->task_grade;
                                                    $selectedComment = $task->task_comment;
                                                    $isDeferredGraded = $gradedDefTasksMap->has($task->def_lesson_id . '_' . $task->task_id);
                                                    @endphp
                                                    <table>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                        <input type="radio" name="task_grade_def[{{ $task->id }}][{{ $task->def_lesson_id }}]" value="Incomplete" {{ $selectedGrade == 'Incomplete' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
                                                                        <span class="custom-radio incomplete">Incomplete</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                        <input type="radio" name="task_grade_def[{{ $task->id }}][{{ $task->def_lesson_id }}]" value="Further training required" {{ $selectedGrade == 'Further training required' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
                                                                        <span class="custom-radio ftr">FTR</span>
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <label class="radio-label" title="{{ $isDeferredGraded ? 'Already added to deferred task. Editing not allowed' : '' }}">
                                                                        <input type="radio" name="task_grade_def[{{ $task->id }}][{{ $task->def_lesson_id }}]" value="Competent" {{ $selectedGrade == 'Competent' ? 'checked' : '' }} {{ $isDeferredGraded ? 'disabled' : '' }}>
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
                                            <!-- <textarea name="task_comment_def[{{ $task->id }}]" rows="3" class="form-control" placeholder="Add your remarks or feedback here..." @if($isDeferredGraded) readonly title="Deferred: You cannot edit this comment." @endif>{{ old("task_comment_def.$task->id", $selectedComment) }}
                                            </textarea> -->

                                            <textarea name="task_comment_def[{{ $task->id }}][{{ $task->def_lesson_id }}]"
                                                rows="3"
                                                class="form-control"
                                                placeholder="Add your remarks or feedback here..."
                                                @if($isDeferredGraded) readonly title="Deferred: You cannot edit this comment." @endif>
                                            {{ old("task_comment_def.$task->id.$task->def_lesson_id", $selectedComment) }}
                                            </textarea>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="accordion-item">
                                        @if($task->task->courseLesson->course->enable_cbta==1)
                                        <h2 class="accordion-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <button type="button" class="accordion-button" data-bs-toggle="collapse"
                                                    data-bs-target="#comptency-{{ $task->id }}" aria-expanded="false">
                                                    Overall Competency Grading
                                                </button>
                                            </div>
                                        </h2>
                                        @endif
                                    </div>

                                    <div id="comptency-{{ $task->id }}" class="accordion-collapse collapse">
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
                                            $lessonCompetencies = $def_grading[$task->defLesson->id] ?? collect();
                                            @endphp
                                            @foreach($competencies as $code => $title)
                                            @php
                                            $code = strtolower($code); // make sure it's lowercase
                                            $grading = $lessonCompetencies->first();

                                            $selectedCompGrade = $grading?->{$code . '_grade'} ?? null;
                                            $selectedCompComment = $grading?->{$code . '_comment'} ?? null;
                                            @endphp

                                            <div class="custom-box">
                                                <div class="header" data-bs-toggle="collapse" data-bs-target="#competency-box-{{ $code }}" aria-expanded="false">
                                                    <span class="rmk">RMK</span>
                                                    <span class="question-mark">?</span>
                                                    <span class="title"><span class="highlight">{{ $title }} ({{ strtoupper($code) }})</span></span>
                                                    <input type="hidden" name="cg_lesson_id" value="{{ $task->defLesson->id }}">
                                                </div>
                                                <div class="table-container">
                                                    <div class="main-tabledesign">
                                                        <input type="hidden" name="cg_user_id" value="{{ $student->id ?? '' }}">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    @for ($i = 1; $i <= 5; $i++)
                                                                        @php
                                                                        $colorClass=$i==1 ? 'incomplete' : ($i==2 ? 'ftr' : 'competent' );
                                                                        @endphp
                                                                        <td>
                                                                        <label class="radio-label">
                                                                            <input type="radio" class="scale-radio"
                                                                                name="comp_grade[{{ $task->defLesson->id }}][{{ $code }}]"
                                                                                value="{{ $i }}" data-event-id="{{ $trainingEvent->id }}" data-lesson-id="{{ $task->defLesson->id }}" data-user-id="{{ $student->id ?? '' }}" data-code="{{ $code }}" data-color-class="{{ $colorClass }}"
                                                                                {{ $selectedCompGrade == $i ? 'checked' : '' }}>
                                                                            <span class="custom-radio {{ $colorClass }}">{{ $i }}</span>
                                                                        </label>
                                                                        </td>
                                                                        @endfor
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <span class="custom-radio competent comp_grade_{{ $task->defLesson->id }}_{{ $student->id ?? '' }}"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Toggleable Comment Box -->
                                            <div class="collapse mt-2" id="competency-box-{{ $code }}">
                                                <textarea name="comp_comments[{{ $task->defLesson->id }}][{{ $code }}]" rows="3" class="form-control" placeholder="Add remarks or comments on competency">{{ $selectedCompComment }}</textarea>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div>
                                        <label>Lesson Summary</label>
                                        <textarea name="def_lesson_summary[{{ $task->def_lesson_id }}]" rows="3" class="form-control" placeholder="Write Lesson Summary">{{ old("def_lesson_summary.$task->def_lesson_id ", $defLesson->lesson_summary ?? '') }}</textarea>
                                    </div>

                                    <div>
                                        <label>Instructor Comment</label>
                                        <textarea name="def_instructor_summary[{{ $task->def_lesson_id }}]" rows="3" class="form-control" placeholder="Instructor Comment">{{ old("def_instructor_summary.$task->def_lesson_id ", $defLesson->instructor_comment ?? '') }}</textarea>
                                    </div>
                                    <!-- // Logs -->
                                    @if(!empty($grouped_customLogs) && $grouped_customLogs->count() > 0)
                                    <?php
                                    $lessonLogs = $grouped_customLogs->get($defLessonId, collect()); // <-- safe fallback
                                    ?>
                                    @if($lessonLogs->isNotEmpty())
                                    <div class="accordion-body mt-3">
                                        <h5 class="text-primary mb-2">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> Training Logs
                                        </h5>
                                        <table class="table table-striped table-bordered align-middle mb-0">
                                            <thead class="table-light text-center">
                                                <tr>
                                                    <th scope="col">User</th>
                                                    <!-- <th scope="col">Lesson</th> -->
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lessonLogs as $log)
                                                <tr>
                                                    <td>{{ $log->users->fname ?? '' }} {{ $log->users->lname ?? '' }}</td>
                                                    <!-- <td>{{ $log->lesson->lesson_title ?? '' }}</td> -->
                                                    <td>{{ $log->is_locked ? 'Locked' : 'Unlocked' }}</td>
                                                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    @endif
                                    <!-- // End Logs -->
                                    <div class="btn-container">
                                        <button type="submit" class="btn btn-save" id="submitDefGrading">Save Lesson</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endforeach
                        @endif
                        <!-- // End custom lesson -->
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="student-{{ $student->id ?? '' }}" role="tabpanel" aria-labelledby="student-tab-{{ $student->id ?? '' }}">
                <form method="POST" class="overallAssessmentForm" data-user-id="{{ $student->id ?? '' }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $trainingEvent->id }}">
                    <input type="hidden" name="user_id" value="{{ $student->id ?? '' }}">
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
                                                        <input type="radio" name="user_result_{{ $student->id ?? '' }}" value="Competent" {{ isset($overallAssessments) && isset($overallAssessments->result) && $overallAssessments->result == 'Competent' ? 'checked' : '' }}>
                                                        <span class="custom-radio competent">Competent</span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="radio-label">
                                                        <input type="radio" name="user_result_{{ $student->id ?? '' }}" value="Further training required" {{ isset($overallAssessments) && isset($overallAssessments->result) && $overallAssessments->result == 'Further training required' ? 'checked' : '' }}>
                                                        <span class="custom-radio ftr">FTR</span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <label class="radio-label">
                                                        <input type="radio" name="user_result_{{ $student->id ?? ''}}" value="Incomplete" {{ isset($overallAssessments) && isset($overallAssessments->result) && $overallAssessments->result == 'Incomplete' ? 'checked' : '' }}>
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
                                <textarea class="form-control remark" name="remark_{{ $student->id ?? '' }}" style="height: 100px" placeholder="Enter your remarks here...">{{ $overallAssessments->remarks ?? '' }}</textarea>
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
            // Ensure all .tab-pane elements live inside #myTabContent to avoid layout gaps
            var $tabContent = $('#myTabContent');
            if ($tabContent.length) {
                $('.tab-pane').each(function() {
                    var pane = this;
                    if (!$tabContent.has(pane).length && pane.parentElement !== $tabContent[0]) {
                        $tabContent.append(pane);
                    }
                });

                // Normalize active state: only one pane should be active+show
                var $activePane = $tabContent.find('.tab-pane.active.show').first();
                if ($activePane.length === 0) {
                    var target = $('.nav-tabs .nav-link.active').attr('data-bs-target');
                    if (target) {
                        $tabContent.find('.tab-pane').removeClass('active show');
                        $tabContent.find(target).addClass('active show');
                    }
                } else {
                    $tabContent.find('.tab-pane').not($activePane).removeClass('active show');
                }
            }

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.course-dropdown').length) {
                    $('.course-dropdown').removeClass('active');
                }
            });

            $('#addDeferredLessonModal').on('shown.bs.modal', function() {
                $('#select_courseTask').select2({
                    dropdownParent: $('#addDeferredLessonModal'),
                    placeholder: "Select Courses",
                    width: '100%',
                    dropdownCssClass: 'select2-drop-up'
                }).on("select2:open", function() {
                    // force upward placement
                    $(".select2-container--open").addClass("select2-container--above");
                });
            });
            $(document).on('click', '.course-dropdown .dropdown-label', function() {
                $(this).closest('.course-dropdown').toggleClass('active');
            });
            // When modal is opened, initialize multiselect
            // Check/Uncheck all
            $(document).on('click', '#check-all-courses[data-toggle="check-all"]', function(e) {
                e.preventDefault();
                let $dropdown = $(this).closest('#course-task-dropdown');
                let $checkboxes = $dropdown.find('input[type="checkbox"]');

                if ($(this).hasClass('all-checked')) {
                    $checkboxes.prop('checked', false);
                    $(this).removeClass('all-checked').text('Check All');
                } else {
                    $checkboxes.prop('checked', true);
                    $(this).addClass('all-checked').text('Uncheck All');
                }

                updateSelectedCourses($dropdown);
            });

            // Single checkbox change
            $(document).on('change', '#course-task-dropdown input[type="checkbox"]', function() {
                let $dropdown = $(this).closest('#course-task-dropdown');
                updateSelectedCourses($dropdown);
            });

            // Update label text
            function updateSelectedCourses($dropdown) {
                let selected = [];
                $dropdown.find('input[type="checkbox"]:checked').each(function() {
                    selected.push($(this).parent().text().trim());
                });

                if (selected.length > 0) {
                    $dropdown.find('.dropdown-label').text(selected.join(', '));
                } else {
                    $dropdown.find('.dropdown-label').text('Select Courses');
                }
            }
            // $('.scale-radio').on('change', function() { 
            //     const name = $(this).attr('name');
            //     const wasChecked = $(this).data('waschecked');
            //     const event_id = $(this).data('event-id');
            //     const lesson_id = $(this).data('lesson-id');
            //     const user_id = $(this).data('user-id');
            //     const code = $(this).data('code');

            //     updateRadioStyles(name);

            //     if (wasChecked) {
            //         // Uncheck and remove color class
            //         //  $(this).prop('checked', false).data('waschecked', false);

            //         $.ajax({
            //             url: '/training/update-competency-grade', // Replace with your actual route
            //             method: 'POST',
            //             data: {
            //                 _token: '{{ csrf_token() }}',
            //                 event_id: event_id,
            //                 lesson_id: lesson_id,
            //                 user_id: user_id,
            //                 code: code,
            //             },
            //             success: function(response) {
            //                 console.log('Grade updated:', response);
            //             },
            //             error: function(xhr) {
            //                 alert('Failed to update grade.');
            //                 console.error(xhr.responseText);
            //             }
            //         });

            //     } else {
            //         // Deselect others, then mark this as checked
            //         $('input[name="' + name + '"]').each(function() {
            //             $(this).data('waschecked', false);
            //         });
            //         $(this).data('waschecked', true);
            //     }

            //     // updateRadioStyles(name);
            // });
            $('.scale-radio').on('click', function(e) {
                const $this = $(this);
                const name = $this.attr('name');
                const event_id = $this.data('event-id');
                const lesson_id = $this.data('lesson-id');
                const user_id = $this.data('user-id');
                const code = $this.data('code');
                const colorClass = $this.data('color-class');

                // If this was already checked, we toggle off
                if ($this.data('waschecked')) {
                    $this.prop('checked', false);
                    $this.data('waschecked', false);
                    $this.siblings('.custom-radio').removeClass('active-selected incomplete ftr competent');

                    // send ajax to clear
                    $.ajax({
                        url: '/training/update-competency-grade',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            event_id,
                            lesson_id,
                            user_id,
                            code,
                            grade: null
                        },
                        success: function(response) {
                            console.log('Grade cleared:', response);
                        },
                        error: function(xhr) {
                            console.error('Failed to update grade', xhr.responseText);
                        }
                    });
                } else {
                    // mark all others in group as unchecked
                    $('input[name="' + name + '"]').each(function() {
                        $(this).data('waschecked', false);
                        $(this).siblings('.custom-radio').removeClass('active-selected incomplete ftr competent');
                    });

                    // mark this one checked
                    $this.prop('checked', true);
                    $this.data('waschecked', true);
                    $this.siblings('.custom-radio').addClass('active-selected').addClass(colorClass);

                    // send ajax to update
                    $.ajax({
                        url: '/training/update-competency-grade',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            event_id,
                            lesson_id,
                            user_id,
                            code,
                            grade: $this.val()
                        },
                        success: function(response) {
                            console.log('Grade updated:', response);
                        },
                        error: function(xhr) {
                            console.error('Failed to update grade', xhr.responseText);
                        }
                    });
                }
            });


            function updateRadioStyles(groupName) {
                $('input[name="' + groupName + '"]').each(function() {
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
            $('.scale-radio:checked').each(function() {
                const name = $(this).attr('name');
                updateRadioStyles(name);
            });
            $('#addDeferredLesson').on('click', function() {
                $('.error_e').html('');
                $("#deferredLessonForm")[0].reset();
                $("#addDeferredLessonModal .modal-title").text("Add Deferred Lesson");
                $("#submitDeferredItems").text("Add Lesson");
                $('#lesson_type').val("deferred");
                $('.select_task').show();
                $('.select_course_task').hide();


            })

            $('#open_add_custom_lesson').on('click', function() {
                $('.error_e').html('');
                $("#deferredLessonForm")[0].reset();
                $("#addDeferredLessonModal .modal-title").text("Add Custom Lesson");
                $("#submitDeferredItems").text("Add Custom Lesson");
                $('#lesson_type').val("custom");
                $('.select_task').hide();
                $('.select_course_task').show();
                $('#select_courseTask').prop('checked', false).closest('label').hide();



            });

            $(document).on("submit", "#gradingFrom", function(e) {
                e.preventDefault(); // Prevent default form submission
                // $(".loader").fadeIn();

                // Collect task grade and competency grade data
                let taskGradeData = $("input[name^='task_grade']").filter(function() {
                    return ($(this).is(':radio') && $(this).is(':checked')) || ($(this).attr('type') === 'number' && $(this).val().trim() !== '');
                }).length;

                let compGradeData = $("input[name^='comp_grade']").filter(function() {
                    return ($(this).is(':radio') && $(this).is(':checked')) || ($(this).attr('type') === 'number' && $(this).val().trim() !== '');
                }).length;

                if (taskGradeData === 0 && compGradeData === 0) {
                    alert('You must fill in at least one task or competency grade for one lesson.');
                    return;
                }

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('training.store_grading') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // $(".loader").fadeOut("slow");
                            alert("Grading saved successfully!");
                            location.reload(); // Reload to reflect changes
                            // $(this)[0].reset();
                        } else {
                            alert("Something went wrong. Please try again.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        // alert("Error: " + xhr.responseText);
                        // $(".loader").fadeOut("slow");
                        var errorMessage = JSON.parse(xhr.responseText);
                        var validationErrors = errorMessage.errors;
                        $.each(validationErrors, function(key, value) {
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
                    alert('Please grade at least one task.');
                    return;
                }

                $.ajax({
                    url: "{{ route('training.store_def_grading') }}", // Update with your route
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // $(".loader").fadeOut("slow");
                            alert("Task Grading saved successfully!");
                            location.reload(); // Reload to reflect changes
                            // $(this)[0].reset();
                        } else {
                            alert("Something went wrong. Please try again.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        // alert("Error: " + xhr.responseText);
                        // $(".loader").fadeOut("slow");
                        var errorMessage = JSON.parse(xhr.responseText);
                        var validationErrors = errorMessage.errors;
                        $.each(validationErrors, function(key, value) {
                            // Correct selector using attribute selector
                            let errorElement = $('.' + key);
                            if (errorElement.length > 0) {
                                errorElement.html('<span class="error-text" style="color:red;">' + value + '</span>');
                            }
                        });
                    }
                });
            });

            $(document).on('submit', '.overallAssessmentForm', function(e) {
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
                    success: function(response) {
                        if (response.success) {
                            $(".loader").fadeOut("slow");
                            alert("Overall assessment saved successfully.");
                            form[0].reset();
                            location.reload();
                        } else {
                            alert("Error saving assessment.");
                        }
                    },
                    error: function(xhr) {
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
                        if (response.success) {
                            $(".loader").fadeOut("slow");
                            $('#addDeferredLessonModal').modal('hide');
                            location.reload();
                        } else {
                            $(".loader").fadeOut("slow");
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $(".loader").fadeOut("slow");
                        var errorMessage = JSON.parse(xhr.responseText);
                        var validationErrors = errorMessage.errors;
                        $('.error_e').html('');
                        $.each(validationErrors, function(key, value) {
                            var formattedKey = key.replace(/\./g, '_') + '_error';
                            var errorMsg = '<p>' + value[0] + '</p>';
                            $('#' + formattedKey).html(errorMsg);
                        });
                    }
                });

            })

            $('.unlock-lesson-btn').on('click', function() {
                const eventId = $(this).data('event-id');
                const lessonId = $(this).data('lesson-id');

                if (confirm('Are you sure you want to unlock this lesson for editing ?')) {
                    $.ajax({
                        url: '/training/unlock-lesson',
                        method: 'POST',
                        data: {
                            event_id: eventId,
                            lesson_id: lessonId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Lesson unlocked successfully.');
                                location.reload(); // Optional: You can replace this with dynamic DOM update
                            } else {
                                alert(response.message || 'Failed to unlock lesson.');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('An error occurred while unlocking the lesson.');
                        }
                    });
                }
            });

            // Unlock deffered lesson 
            $('.unlock-deflesson-btn').on('click', function() {
                const deflesson_id = $(this).data('deflesson-id');
                const event_id = $(this).data('event-id');
                const lesson_type = $(this).data('lesson-type');


                if (confirm('Are you sure you want to unlock this lesson for editing ?')) {
                    $.ajax({
                        url: '/training/unlock-deflesson',
                        method: 'POST',
                        data: {
                            deflesson_id: deflesson_id,
                            event_id: event_id,
                            lesson_type: lesson_type,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Lesson unlocked successfully.');
                                location.reload(); // Optional: You can replace this with dynamic DOM update
                            } else {
                                alert(response.message || 'Failed to unlock lesson.');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('An error occurred while unlocking the lesson.');
                        }
                    });
                }
            });

            setTimeout(function() {
                $('#successMessage').fadeOut('slow');
            }, 2000);

            $(document).on('click', '.unlock-event-icon', function() {
                let eventId = $(this).data('training-event-leeson');

                if (confirm('Are you sure you want to unlock this training event ?')) {
                    $.ajax({
                        url: '/grading/unlock/' + eventId,
                        type: 'POST',
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            console.error('Unlock failed:', xhr.responseText);
                        }
                    });
                }
            });
            $(document).on('change', '#select_course, #edit_select_course', function() {
                var courseId = $(this).val();
                var isEditForm = $(this).attr('id') === 'edit_select_course';

                var lessonContainer = isEditForm ? $('#editLessonDetailsContainer') : $('#lessonDetailsContainer');
                var mode = isEditForm ? 'update' : 'create';

                // For edit mode, map saved lessons by lesson_id for quick lookup
                var lessonPrefillMap = {};



                //  let selectedStudentId = $('#select_user').val() || $('#edit_select_user').val();

                $.ajax({
                    url: '{{ url("/training/get_course_lessons") }}',
                    type: 'GET',
                    data: {
                        course_id: courseId,
                        selectedStudentId: selectedStudentId
                    },
                    success: function(response) {
                        lessonContainer.empty(); // Clear existing lesson boxes

                        if (response.success && response.lessons.length > 0) {
                            let lessons = response.lessons;
                            resourcesdata = response.resources;
                            instructorsdata = response.instructors;

                            response.lessons.forEach(function(lesson, idx) {
                                let prefillData = isEditForm && lessonPrefillMap[lesson.id] ? lessonPrefillMap[lesson.id] : {};
                                renderLessonBox(lesson, lessonContainer, prefillData, idx, mode);
                            });
                        } else {
                            alert('No lessons found for the selected course.');
                        }
                        if (response.licence && response.licence.number) {
                            $('#std_licence_number').empty();
                            $('#std_licence_number').val(response.licence.number);

                        } else {

                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Error fetching lessons. Please try again.');
                    }
                });
            });

            $(document).on("click", '#delete_custom_lesson', function() {
                var event_id = $(this).attr("data-event_id");
                var custom_lesson_id = $(this).attr("data-custom-lesson-id");

                if (confirm("Are you sure you want to delete this lesson?")) {
                    var vdata = {
                        event_id: event_id,
                        custom_lesson_id: custom_lesson_id,
                        "_token": "{{ csrf_token() }}",
                    };

                    $.ajax({
                        url: '{{ url("training/delete_customLesson") }}',
                        type: 'POST',
                        data: vdata,
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Error deleting lesson. Please try again.');
                        }
                    });
                }
            });

            // Delete deferred lesson
            $(document).on("click", '#delete_deferred_lesson', function() {
                var event_id = $(this).attr("data-event_id");
                var deferred_lesson_id = $(this).attr("data-deferred-lesson-id");

                if (confirm("If you delete this deferred lesson, all related tasks and CBTA grading will also be deleted. Do you want to continue ?")) {
                    var vdata = {
                        event_id: event_id,
                        deferred_lesson_id: deferred_lesson_id,
                        "_token": "{{ csrf_token() }}",
                    };

                    $.ajax({
                        url: '{{ url("training/delete_deferredLesson") }}',
                        type: 'POST',
                        data: vdata,
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Error deleting lesson. Please try again.');
                        }
                    });
                }
            });


            $(document).on("click", "#edit_custom_lesson, #edit_deferred_lesson", function() {
                $('.error_e').empty();
                var event_id = $(this).attr("data-event_id");
                var lesson_type = $(this).attr("data-lesson-Type");
                if (lesson_type == "custom") {

                    $('#select_task').hide();
                    $('#edit_lesson_type').val("custom");
                    var custom_lesson_id = $(this).attr("data-custom-lesson-id");
                    $('#custom_select_course_task').show();

                    vdata = {
                        event_id: event_id,
                        custom_lesson_id: custom_lesson_id,
                        lesson_type: lesson_type,
                        "_token": "{{ csrf_token() }}",
                    };


                } else if (lesson_type == "deferred") {
                    $('#select_task').show();
                    $('#edit_lesson_type').val("deferred");
                    var deferred_lesson_id = $(this).attr("data-deferred-lesson-id");
                    $('#custom_select_course_task').hide();
                    vdata = {
                        event_id: event_id,
                        deferred_lesson_id: deferred_lesson_id,
                        lesson_type: lesson_type,
                        "_token": "{{ csrf_token() }}",
                    };
                }

                $.ajax({
                    url: '{{ url("training/edit_customLesson") }}',
                    type: 'POST',
                    data: vdata,
                    success: function(response) {
                        if (response.deferredLessons[0].operation) {
                            $('#edit_operation').val(response.deferredLessons[0].operation);
                        }
                        console.log(response.deferredLessons[0].operation);
                        $('#deferredLessons_id').val(response.deferredLessons[0].id);

                        if (lesson_type === "custom") {
                            $("#edit_DeferredLessonModal .modal-title").text("Edit Custom Lesson");
                        } else {
                            $("#edit_DeferredLessonModal .modal-title").text("Edit Deferred Lesson");
                        }


                        $('#edit_lesson_title').val(response.deferredLessons[0].lesson_title);

                        let lesson_date = response.deferredLessons[0].lesson_date;
                        if (lesson_date) {
                            $('#edit_lesson_date').val(lesson_date.split("T")[0]);
                        }

                        $('#edit_start_time').val(response.deferredLessons[0].start_time || "");
                        $('#edit_end_time').val(response.deferredLessons[0].end_time || "");
                        $('#edit_departure_airfield').val(response.deferredLessons[0].departure_airfield || "");
                        $('#edit_destination_airfield').val(response.deferredLessons[0].destination_airfield || "");
                        $("#edit_instructor").val(response.deferredLessons[0].instructor_id || "");
                        $("#edit_resource_id").val(response.deferredLessons[0].resource_id || "");

                        // reset checkboxes first
                        $("input[name='select_courseTask[]']").prop("checked", false);

                        if (lesson_type === "custom") {

                            if (response.defLessonTasks && response.defLessonTasks.length > 0) {
                                $.each(response.defLessonTasks, function(index, value) {
                                    $("input[name='select_courseTask[]'][value='" + value.task_id + "']").prop("checked", true);
                                });

                                let selectedCourses = response.defLessonTasks.map(item => item.task.title);
                                $(".course-dropdown .dropdown-label").text(selectedCourses.join(", "));
                            } else {
                                $(".course-dropdown .dropdown-label").text("Select Courses");
                            }

                        } else {
                            $('.taskContainer').empty();
                            // console.log(response.deferredLessons);
                            $.each(response.defLessonTasks, function(index, value) {

                                let append_task = `
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="item_ids[]" checked value="${value.task_id}">
                                        <label class="form-check-label">${value.task.title}</label>
                                    </div>
                                `;
                                $('.taskContainer').append(append_task);
                            });
                        }
                        $('#edit_DeferredLessonModal').modal('show');
                    },

                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Error fetching lessons. Please try again.');
                    }
                });
            });

            $(document).on("submit", "#customGradingFrom", function(e) {
                e.preventDefault();

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
                    success: function(response) {
                        if (response.success) {
                            // $(".loader").fadeOut("slow");
                            alert("Custom Task Grading saved successfully!");
                            location.reload(); // Reload to reflect changes
                            // $(this)[0].reset();
                        } else {
                            alert("Something went wrong. Please try again.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        // alert("Error: " + xhr.responseText);
                        // $(".loader").fadeOut("slow");
                        var errorMessage = JSON.parse(xhr.responseText);
                        var validationErrors = errorMessage.errors;
                        $.each(validationErrors, function(key, value) {
                            // Correct selector using attribute selector
                            let errorElement = $('.' + key);
                            if (errorElement.length > 0) {
                                errorElement.html('<span class="error-text" style="color:red;">' + value + '</span>');
                            }
                        });
                    }
                });
            });

            $("#update_deferredLessonForm").submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                $.ajax({
                    url: "{{ url('training/update_deferred_form') }}",
                    type: "POST",
                    data: formData,
                    processData: false, // required for FormData
                    contentType: false, // required for FormData
                    beforeSend: function() {
                        // show loader if you want
                        $("#formLoader").show();
                    },
                    success: function(response) {
                        $("#formLoader").hide();
                        if (response.success) {
                            $("#edit_DeferredLessonModal").modal("hide");
                            if (response.message == "custom") {
                                alert("Custom Lesson updated successfully!");
                            } else {
                                alert("Deffered Lesson updated successfully!");
                            }
                            location.reload();

                        } else {
                            alert("Something went wrong!");
                        }
                    },
                    error: function(xhr, status, error) {
                        $(".loader").fadeOut("slow");
                        var errorMessage = JSON.parse(xhr.responseText);
                        var validationErrors = errorMessage.errors;
                        $('.error_e').html('');
                        $.each(validationErrors, function(key, value) {
                            var formattedKey = key.replace(/\./g, '_') + '_uperror';
                            var errorMsg = '<p>' + value[0] + '</p>';
                            $('#' + formattedKey).html(errorMsg);
                        });
                    }
                });
            });

            $("#submitNormalItems").on("click", function(e) {
                e.preventDefault();
                $(".loader").fadeIn();
                $.ajax({
                    url: '{{ url("/training/submit_normal_items") }}',
                    type: 'POST',
                    data: $("#normalLessonForm").serialize(),
                    success: function(response) {
                        $(".loader").fadeOut("slow");
                        if (response.status === 'error') {
                            alert(response.message);
                        } else {
                            alert(response.message);
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        $(".loader").fadeOut("slow");
                        var errorMessage = JSON.parse(xhr.responseText);
                        var validationErrors = errorMessage.errors;
                        $('.error_e').html('');
                        $.each(validationErrors, function(key, value) {
                            var formattedKey = key.replace(/\./g, '_') + '_error';
                            var errorMsg = '<p>' + value[0] + '</p>';
                            $('#' + formattedKey).html(errorMsg);
                        });
                    }
                });
            });



            $('.normal_lesson').on('click', function() {
                let def_id = $(this).data("def-id");
                let title = $(this).data("title");
                $('#def_id').val(def_id);
                $('#add_title').html(title);
                $('#normalLessonModel').modal('show');
            });

            $(document).on('click', '.backToDeferredLesson', function() {
                let event_id = $(this).data("event-id");
                let user_id = $(this).data("user-id");
                let task_id = $(this).data("task-id");
                let lesson_id = $(this).data("lesson-id");
                let sublesson_id = $(this).data("sublesson-id");
                vdata = {
                    event_id: event_id,
                    user_id: user_id,
                    task_id: task_id,
                    lesson_id: lesson_id,
                    sublesson_id: sublesson_id,
                    "_token": "{{ csrf_token() }}",
                };
                if (confirm('Are you sure you want to move this task back to the deferred lessons ?')) {
                    $.ajax({
                        url: '{{ url("/training/backToDeferredLesson") }}',
                        type: 'POST',
                        data: vdata,
                        success: function(response) {
                            console.log(response);
                            if (response.status) {
                                alert("Task added  back to deferred lesson successfully");
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            $(".loader").fadeOut("slow");
                            var errorMessage = JSON.parse(xhr.responseText);
                            var validationErrors = errorMessage.errors;

                        }
                    });
                }
            });
        });
    </script>
    @endsection