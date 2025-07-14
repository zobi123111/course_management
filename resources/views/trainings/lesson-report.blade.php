<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lesson Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; }
        .section { margin-bottom: 20px; }
    </style>
</head>
<body>
        <table width="100%" style="border: none; border-collapse: collapse;">
            <tr>
                <td style="border: none; text-align: left; vertical-align: top;">
                    <h1 style="margin: 0;">Lesson Report</h1>
                </td>
                <td style="border: none; text-align: right; vertical-align: top;">
                    @if($event?->orgUnit?->org_logo)
                        <img src="{{ public_path('storage/organization_logo/' . $event->orgUnit->org_logo) }}"
                            alt="Org Logo"
                            style="height: 60px;">
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
        </div>

        <div class="section">
            <h2>Tasks Completed</h2>
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
                            <td>{{ $task->comments ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($event->competencyGradings->isNotEmpty())
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
                        @foreach($event->competencyGradings as $competency)
                            @foreach(['kno','pro','com','fpa','fpm','ltw','psd','saw','wlm'] as $comp)
                                <tr>
                                    <td>{{ strtoupper($comp) }}</td>
                                    <td>{{ $competency[$comp.'_grade'] ?? 'N/A' }}</td>
                                    <td>{{ $competency[$comp.'_comment'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif


        <div class="section">
            <h2>Lesson Summary</h2>
            @foreach($event->overallAssessments as $assessment)
                <p><strong>Result:</strong> {{ $assessment->result }}</p>
                <p><strong>Remarks:</strong> {{ $assessment->remarks ?? 'No remarks' }}</p>
            @endforeach
        </div>
</body>
</html>
