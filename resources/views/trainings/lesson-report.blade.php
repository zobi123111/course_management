<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Lesson Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1,
        h2,
        h3 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        .section {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <table width="100%" style="border: none; border-collapse: collapse;">
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">
                <h1 style="display: inline-block; margin: 0;">Lesson Report -</h1> 
                <h2 style="display: inline-block; margin: 0 0 3px 9px;">{{ $lesson->lesson_title }}</h2>
            </td>
            <td style="border: none; text-align: right; vertical-align: top;">
                @if($event?->orgUnit?->org_logo)
                <img src="{{ public_path('storage/organization_logo/' . $event->orgUnit->org_logo) }}" alt="Org Logo" style="height: 60px;">
                @endif
            </td>
        </tr>
    </table>
    <hr>

    <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: none;">
        <tr>
            <td width="40%" valign="top" style="padding-right: 15px; border: none;">
                <div class="section" style="border: none;">
                    <strong>Date:</strong> {{ date('M d, Y', strtotime($eventLesson?->lesson_date)) }}<br>
                    <strong>Student Name:</strong> {{ $event?->student?->fname }} {{ $event?->student?->lname }}<br>
                    <strong>Instructor Name:</strong> {{ $eventLesson?->instructor?->fname }} {{ $eventLesson?->instructor?->lname }}<br>
                    @php
                        $resource = $eventLesson?->resource ?? $event?->resource;
                    @endphp
                    @if ($resource)
                        <strong>Aircraft:</strong> {{ $resource->type ?? $resource->class ?? 'N/A' }}<br>
                        <strong>Reg:</strong> {{ $resource->registration ?? 'N/A' }}<br>
                    @endif
                    @if(!empty($event->rank))
                        <strong>Rank :</strong>
                        @if($event->rank == 1) Captain
                        @elseif($event->rank == 2) First Officer
                        @elseif($event->rank == 3) Second Officer
                        @endif
                        <br>
                    @endif
                    @if(!empty($event->course->ato_num))
                        @php
                            $atoNum = preg_replace('/^(easa-|uk-)/i', '', $event->course->ato_num);
                        @endphp
                        <strong>ATO Number:</strong> {{ strtoupper($atoNum) }} <br>
                    @endif
                    @if(!empty($eventLesson->operation1))
                        <strong>Operation :</strong>
                        @if($eventLesson->operation1 == 1) PF in LHS
                        @elseif($eventLesson->operation1 == 2) PM in LHS
                        @elseif($eventLesson->operation1 == 3) PF in RHS
                        @elseif($eventLesson->operation1 == 4) PM in RHS
                        @endif
                        <br>
                    @endif
                    <strong>Total Blocks Time:</strong> {{ $blockCreditedFormatted }}<br>
                    <strong>Total Flight Time:</strong> {{ $totalFlightTimeFormatted }}<br>
                    <strong>Off-Blocks Time:</strong>{{ \Carbon\Carbon::parse($eventLesson->start_time)->format('H:i') }} <br>
                    <strong>On-Blocks Time:</strong> {{ \Carbon\Carbon::parse($eventLesson->end_time)->format('H:i') }} <br>
                    @php
                        $block = \Carbon\Carbon::parse($eventLesson?->end_time)->diffInMinutes(\Carbon\Carbon::parse($eventLesson?->start_time));
                    @endphp
                    <strong>Departure :</strong> {{ $eventLesson?->departure_airfield ?? 'N/A' }}<br>
                    <strong>Arrival :</strong> {{ $eventLesson?->destination_airfield ?? 'N/A' }}<br>
                </div>
            </td>

            <!-- RIGHT: Sectors Grid -->
            <td width="60%" valign="top" style="border: none;">
                <table width="100%" cellspacing="0" cellpadding="10" style="border-collapse: collapse; border: none;">
                    <tr>
                        @foreach($eventLesson->sectors as $sector)

                            @php
                                $eventType = $enable_mp_lifus;
                                $op = $sector->operation;

                                $operationName = 'N/A';

                                if ($eventType == 1) {
                                    $operationName = match($op) {
                                        1 => 'PF LHS',
                                        2 => 'PF RHS',
                                        default => 'N/A'
                                    };
                                }
                                elseif ($eventType == 2 || $eventType == 3) {
                                    $operationName = match($op) {
                                        1 => 'PF LHS',
                                        2 => 'PM LHS',
                                        3 => 'PF RHS',
                                        4 => 'PM RHS',
                                        default => 'N/A'
                                    };
                                }
                            @endphp

                            <td width="50%" valign="top" style="border: none; padding-bottom: 10px;">
                                <h3 style="margin: 0 0 5px 0;">Sector {{ $loop->iteration }} - ({{ $sector->lesson_date ? date('d/m/Y', strtotime($sector->lesson_date)) : 'N/A' }})</h3>
                                <strong>Reg:</strong> {{ $sector->resourceData->name ?? 'N/A' }}<br>
                                <strong>Opration:</strong> {{ $operationName }}<br>
                                <strong>Departure:</strong> {{ $sector->departure_airfield ?? 'N/A' }}<br>
                                <strong>Off Block Time:</strong>
                                {{ $sector->start_time ? \Carbon\Carbon::parse($sector->start_time)->format('H:i') : 'N/A' }}<br>
                                <strong>On Block Time:</strong>
                                {{ $sector->end_time ? \Carbon\Carbon::parse($sector->end_time)->format('H:i') : 'N/A' }}<br>
                                <strong>Arrival:</strong> {{ $sector->destination_airfield ?? 'N/A' }}<br>
                                @if($sector->takeoff_time && $sector->landing_time)
                                    <strong>Takeoff Time:</strong>
                                    {{ $sector->takeoff_time ? \Carbon\Carbon::parse($sector->takeoff_time)->format('H:i') : 'N/A' }}<br>

                                    <strong>Landing Time:</strong>
                                    {{ $sector->landing_time ? \Carbon\Carbon::parse($sector->landing_time)->format('H:i') : 'N/A' }}<br>
                                @endif
                            </td>

                            @if($loop->iteration % 2 == 0)
                                </tr><tr>
                            @endif
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <div class="section">
        <h2>Tasks Completed</h2>
        @if($event->taskGradings->isNotEmpty() && $event->taskGradings->pluck('subLesson')->filter()->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Result</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                @foreach($event->taskGradings as $task)
             
                <tr>
                    <td>{{ $task->subLesson->title ?? 'N/A' }}</td>
                    <td>
                        @if($task->lesson?->grade_type === 'percentage')
                        {{ $task->task_grade }}%
                        @else
                        {{ $task->task_grade ?? 'N/A' }}
                        @endif
                    </td>
                    <td>{{ $task->task_comment ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p><strong>No Data Available</strong></p>
        @endif
    </div>
        @if($event->eventLessons[0]->lesson->enable_cbta == 1)
                <div class="section">
                    <h2>Competencies</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Competency</th>
                                <th>Grade</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($event->competencyGradings) && count($event->competencyGradings) > 0)
                            @foreach($event->competencyGradings as $competency)
                            @foreach(['kno','pro','com','fpa','fpm','ltw','psd','saw','wlm'] as $comp)
                            <tr>
                                <td>{{ strtoupper($comp) }}</td>
                                <td>{{ !empty($competency->{$comp.'_grade'}) ? $competency->{$comp.'_grade'} : 'N/A' }}</td>
                                <td>{{ !empty($competency->{$comp.'_comment'}) ? $competency->{$comp.'_comment'} : '-' }}</td>
                            </tr>
                            @endforeach
                            @endforeach
                            @else
                            <tr>
                                <td colspan="3" class="text-center text-muted">No Competency Grading Found</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
        @endif

  



    <!-- // Examiner competency grading  -->
    @if($lesson->examiner_cbta == 1) 
        <div class="section">
            <h2>Examiner Competency</h2>
            <table>
                <thead>
                    <tr>
                        <th>Competency</th>
                        <th>Grade</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($examiner_grading))
                        @foreach($examiner_grading as $item)
                            @php
                                $grade = $item['graded_value'];
                                $badgeClass = 'bg-secondary';

                                if ($grade == 1) $badgeClass = 'grade-incomplete';
                                elseif ($grade == 2) $badgeClass = 'grade-ftr';
                                elseif (in_array($grade, [3,4,5])) $badgeClass = 'grade-competent';
                            @endphp
                            <tr>
                                <td><strong>{{ strtoupper($item['short_name']) }}</strong></td>
                                <td><span class="badge {{ $badgeClass }}">{{ $grade ?? '' }}</span></td>
                                <td class="text-start">{{ $item['comment'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center text-muted">No Examiner Grading Found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif
    <!-- // End Examiner competency grading  -->

    <!-- // Instructor competency grading  -->
    @if($lesson->instructor_cbta == 1) 
        <div class="section">
            <h2>Instructor Competency</h2>
            <table>
                <thead>
                    <tr>
                        <th>Competency</th>
                        <th>Grade</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($instructor_grading))
                        @foreach($instructor_grading as $item)
                            @php
                                $grade = $item['graded_value'];
                                $badgeClass = 'bg-secondary';

                                if ($grade == 1) $badgeClass = 'grade-incomplete';
                                elseif ($grade == 2) $badgeClass = 'grade-ftr';
                                elseif (in_array($grade, [3,4,5])) $badgeClass = 'grade-competent';
                            @endphp
                            <tr>
                                <td><strong>{{ strtoupper($item['short_name']) }}</strong></td>
                                <td><span class="badge {{ $badgeClass }}">{{ $grade ?? '' }}</span></td>
                                <td class="text-start">{{ $item['comment'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="text-center text-muted">No Instructor Grading Found</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif
    <!-- // End Instructor competency grading  -->

    <!-- // Pilot competency grading  -->
    @if($lesson->pilot_cbta == 1) 
        <div class="section">
            <h2>Pilot Competency</h2>
            <table>
                <thead>
                    <tr>
                        <th>Competency</th>
                        <th>Grade</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($pilot_grading))
                        @foreach($pilot_grading as $item)
                            @php
                                $grade = $item['graded_value'];
                                $badgeClass = 'bg-secondary';

                                if ($grade == 1) $badgeClass = 'grade-incomplete';
                                elseif ($grade == 2) $badgeClass = 'grade-ftr';
                                elseif (in_array($grade, [3,4,5])) $badgeClass = 'grade-competent';
                            @endphp

                            <tr>
                                <tr>
                                    <td><strong>{{ strtoupper($item['short_name']) }}</strong></td>
                                    <td><span class="badge {{ $badgeClass }}">{{ $grade ?? '' }}</span></td>
                                    <td class="text-start">{{ $item['comment'] ?? '' }}</td>
                                </tr>
                            </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="3" class="text-center text-muted">No Pilot Grading Found</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif
    <!-- // End Pilot competency grading  -->


    @if($event->overallAssessments->isNotEmpty())

    @endif

    {{-- Lesson Summary Section --}}
    <div class="section">
        <h2>Lesson Comment</h2>
        <p><strong>Result:</strong> {{ $event->eventLessons[0]->lesson_summary ?? '' }}</p>
    </div>

    {{-- Instructor Comment Section --}}
     @if($event->entry_source == "instructor") 
    <div class="section">
        <h2>Instructor Comment</h2>
        <p><strong>Result:</strong> {{ $event->eventLessons[0]->instructor_comment ?? '' }}</p>
    </div>
    @endif




</body>

</html>