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
    <h1>Lesson Report</h1>
    <div class="section">
        <strong>Date:</strong> {{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y') }}<br>
        <strong>Student Name:</strong> {{ auth()->user()->name }}<br>
        <strong>Instructor Name:</strong> {{ $event->instructor?->fname }} {{ $event->instructor?->lname }}<br>
        <strong>Start Time:</strong> {{ $event->start_time }}<br>
        <strong>End Time:</strong> {{ $event->end_time }}<br>
        <strong>Total Lesson Time:</strong> {{ \Carbon\Carbon::parse($event->end_time)->diffInMinutes(\Carbon\Carbon::parse($event->start_time)) }} minutes<br>
        <strong>Departure Airfield:</strong> {{ $event->departure_airfield ?? 'N/A' }}<br>
        <strong>Destination Airfield:</strong> {{ $event->destination_airfield ?? 'N/A' }}
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
                        <td>{{ $task->task_grade }}</td>
                        <td>{{ $task->comments ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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

    <div class="section">
        <h2>Lesson Summary</h2>
        @foreach($event->overallAssessments as $assessment)
            <p><strong>Result:</strong> {{ $assessment->result }}</p>
            <p><strong>Remarks:</strong> {{ $assessment->remarks ?? 'No remarks' }}</p>
        @endforeach
    </div>
</body>
</html>
