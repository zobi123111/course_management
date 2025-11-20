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
   
    <div class="section">
        <strong>Date:</strong> {{ date('M d, Y', strtotime($eventLesson?->lesson_date)) }}<br>
        <strong>Student Name:</strong> {{ $event?->student?->fname }} {{ $event?->student?->lname }}<br>
        <strong>Instructor Name:</strong> {{ $eventLesson?->instructor?->fname }} {{ $eventLesson?->instructor?->lname }}<br>
        <strong>Start Time:</strong> {{ date('h:i A', strtotime($eventLesson?->start_time)) }}<br>
        <strong>End Time:</strong> {{ date('h:i A', strtotime($eventLesson?->end_time)) }}<br>
        <strong>Total Lesson Time:</strong> {{ \Carbon\Carbon::parse($eventLesson?->end_time)->diffInMinutes(\Carbon\Carbon::parse($eventLesson?->start_time)) }} minutes<br>
        <strong>Departure Airfield:</strong> {{ $eventLesson?->departure_airfield ?? 'N/A' }}<br>
        <strong>Destination Airfield:</strong> {{ $eventLesson?->destination_airfield ?? 'N/A' }}<br>

        <strong>Resource :</strong> {{ $eventLesson?->resource_name ?? 'N/A' }}<br>
        @php
        $resource = $eventLesson?->resource ?? $event?->resource;
        @endphp
        @if ($resource)
        <strong>Aircraft:</strong> {{ $resource->type ?? $resource->class ?? 'N/A' }}<br>
        <strong>Reg:</strong> {{ $resource->registration ?? 'N/A' }}<br>
        @else
        <strong>Aircraft:</strong> N/A<br>
        <strong>Reg:</strong> N/A<br>
        @endif
        @if(!empty($eventLesson->operation1))

           <strong>Operation :</strong>  
               @if($eventLesson->operation1 == 1)
                    PF in LHS
                @elseif($eventLesson->operation1 == 2)
                    PM in LHS
                @elseif($eventLesson->operation1 == 3)
                    PF in RHS
                @elseif($eventLesson->operation1 == 4) 
                    PM in RHS
                @endif
            <br>
        @endif
        <!-- // Rank -->
        @if(!empty($event->rank))
        <strong>Rank :</strong>    
                @if($event->rank == 1)
                    Captain
                @elseif($event->rank == 2)
                    First Officer
                @elseif($event->rank == 3)
                    Second Officer
                @endif<br>
        @endif
        <!-- ATO Num -->
        
      @if(!empty($event->course->ato_num)) 
        @php
            $atoNum = $event->course->ato_num;
            // Remove prefixes "easa-" or "uk-" (case-insensitive)
            $atoNum = preg_replace('/^(easa-|uk-)/i', '', $atoNum);
        @endphp
        <strong>ATO Num:</strong> {{ strtoupper($atoNum) }}
    @endif

  
    </div>

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



    <!-- // Examiner competency grading  -->
   @if($event->course->examiner_cbta == 1) 
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
                @if($examiner_grading->isNotEmpty())
                @foreach($examiner_grading as $grading)
                @php
                $grade = $grading->examinerGrading[0]->competency_value ?? null;
                $badgeClass = 'bg-secondary'; // default

                if ($grade == 1) {
                $badgeClass = 'grade-incomplete';
                } elseif ($grade == 2) {
                $badgeClass = 'grade-ftr';
                } elseif (in_array($grade, [3, 4, 5])) {
                $badgeClass = 'grade-competent';
                }
                @endphp
                <tr>
                    <td><strong>{{ strtoupper($grading->short_name) }}</strong></td>
                    <td>
                        <span class="badge {{ $badgeClass }}">
                            {{ $grading->examinerGrading[0]->competency_value ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-start">{{ $grading->examinerGrading[0]->comment ?? '-' }}</td>
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
   
     @if($event->course->instructor_cbta == 1) 
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
                @if($instructor_grading->isNotEmpty())
                @foreach($instructor_grading as $grading)
                @php
                $grade = $grading->examinerGrading[0]->competency_value ?? null;
                $badgeClass = 'bg-secondary'; // default

                if ($grade == 1) {
                $badgeClass = 'grade-incomplete';
                } elseif ($grade == 2) {
                $badgeClass = 'grade-ftr';
                } elseif (in_array($grade, [3, 4, 5])) {
                $badgeClass = 'grade-competent';
                }
                @endphp
                <tr>
                    <td><strong>{{ strtoupper($grading->short_name) }}</strong></td>
                    <td>
                        <span class="badge {{ $badgeClass }}">
                            {{ $grading->examinerGrading[0]->competency_value ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="text-start">{{ $grading->examinerGrading[0]->comment ?? '-' }}</td>
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
    @if($event->overallAssessments->isNotEmpty())
    <!-- <div class="section">
        <h2>Lesson Summary</h2>
        @foreach($event->overallAssessments as $assessment)
        <p><strong>Result:</strong> {{ $assessment->result }}</p>
        <p><strong>Remarks:</strong> {{ $assessment->remarks ?? 'No remarks' }}</p>
        @endforeach
    </div> -->
    @endif

    {{-- Lesson Summary Section --}}
    <div class="section">
        <h2>Lesson Comment</h2>
        <p><strong>Result:</strong> {{ $event->eventLessons[0]->lesson_summary ?? '' }}</p>
    </div>

    {{-- Instructor Comment Section --}}
     @if($event->course->instructor_cbta == 1) 
    <div class="section">
        <h2>Instructor Comment</h2>
        <p><strong>Result:</strong> {{ $event->eventLessons[0]->instructor_comment ?? '' }}</p>
    </div>
    @endif




</body>

</html>