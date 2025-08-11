@extends('layout.app')

@section('title', 'Grading List')
@section('sub-title', 'Student Grading')

@section('content')

<style>
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

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

<section class="section py-4">
    <div class="container">
        @if(!$event)
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle-fill me-2"></i> This Event has not been graded yet.
            </div>
        @else
            <div class="card shadow-lg mb-5 border-0">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-journal-text me-2"></i>{{ $event->course?->course_name }}
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Task Grading -->
                    <div class="mb-4">
                        <h5 class="text-primary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#taskGrading" role="button" aria-expanded="false" aria-controls="taskGrading">
                            <span><i class="bi bi-card-checklist me-2"></i>Task Based Grading</span>
                            <i class="bi bi-chevron-down"></i>
                        </h5>
                        <div class="collapse" id="taskGrading">
                            @if($event->taskGradings->isEmpty())
                                <p class="text-muted">No task grading available.</p>
                            @else
                                @php
                                    $groupedTasks = $event->taskGradings->groupBy('lesson_id');
                                    $lessonMeta = $event->eventLessons->keyBy('lesson_id');
                                @endphp
                                @foreach($groupedTasks as $lessonId => $tasks)
                                 @php
                                    $meta = $lessonMeta[$lessonId] ?? null;
                                @endphp
                                    <div class="mb-3">
                                        <div class="fw-bold text-secondary mb-2">
                                            <i class="bi bi-book me-1"></i>Lesson: {{ $tasks->first()->lesson?->lesson_title ?? 'N/A' }}
                                            @if($meta)
                                                <br><small class="text-muted">
                                                    <i class="bi bi-person-video3 me-1"></i>Instructor: {{ $meta->instructor?->fname }} {{ $meta->instructor?->lname }} |
                                                    <i class="bi bi-calendar-date me-1"></i>Date: {{ date('M d, Y', strtotime($meta->lesson_date)) }} |
                                                    <i class="bi bi-clock me-1"></i>Time: {{ date('h:i A', strtotime($meta->start_time)) }} - {{ date('h:i A', strtotime($meta->end_time)) }}
                                                </small>
                                            @endif
                                        </div>
                                        <ul class="list-group shadow-sm">
                                        @foreach($tasks as $task)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><i class="bi bi-chevron-double-right me-1"></i>{{ $task->subLesson?->title ?? 'N/A' }}</span>
                                                @php
                                                    $grade = $task->task_grade ?? null;

                                                    // Default class
                                                    $gradeClass = 'bg-secondary';

                                                    if (in_array($grade, ['Incomplete', 'Further training required'])) {
                                                        $gradeClass = match($grade) {
                                                            'Incomplete' => 'grade-incomplete',
                                                            'Further training required' => 'grade-ftr',
                                                        };
                                                    } elseif (in_array($grade, [1, 2, 3, 4, 5])) {
                                                        $gradeClass = match((int) $grade) {
                                                            1 => 'grade-incomplete',
                                                            2 => 'grade-ftr',
                                                            3, 4, 5 => 'grade-competent',
                                                        };
                                                    } elseif (is_numeric($grade)) {
                                                        // fallback for other numeric values (if ever)
                                                        $gradeClass = 'grade-competent';
                                                    } else {
                                                        $gradeClass = 'grade-competent';
                                                    }
                                                @endphp


                                                <span class="badge {{ $gradeClass }}">
                                                    {{ $task->task_grade ?? 'N/A' }}
                                                </span>
                                            </li>
                                        @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @if($defLessonGrading->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="text-danger d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#defLessonGrading" role="button" aria-expanded="false" aria-controls="defLessonGrading">
                                <span><i class="bi bi-exclamation-circle me-2"></i>Deferred Lesson Grading</span>
                                <i class="bi bi-chevron-down"></i>
                            </h5>
                            <div class="collapse" id="defLessonGrading">
                                @foreach($defLessonGrading as $defLessonId => $tasks)
                                    @php
                                        $defLesson = $tasks->first()?->defLesson;
                                        $instructor = $defLesson?->instructor;
                                    @endphp
                                    <div class="mb-3">
                                        <div class="fw-bold text-secondary mb-2">
                                            <i class="bi bi-book me-1"></i>Deferred Lesson: {{ $defLesson?->lesson_title ?? 'N/A' }}
                                            @if($defLesson)
                                                <br><small class="text-muted">
                                                    @if($instructor)
                                                        <i class="bi bi-person-video3 me-1"></i>Instructor: {{ $instructor->fname }} {{ $instructor->lname }} |
                                                    @endif
                                                    @if($defLesson->lesson_date)
                                                        <i class="bi bi-calendar-date me-1"></i>Date: {{ \Carbon\Carbon::parse($defLesson->lesson_date)->format('M d, Y') }} |
                                                    @endif
                                                    @if($defLesson->start_time && $defLesson->end_time)
                                                        <i class="bi bi-clock me-1"></i>Time: {{ date('h:i A', strtotime($defLesson->start_time)) }} - {{ date('h:i A', strtotime($defLesson->end_time)) }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>

                                        <ul class="list-group shadow-sm">
                                            @foreach($tasks as $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <i class="bi bi-chevron-double-right me-1"></i>
                                                        {{ $item->task?->title ?? 'N/A' }}
                                                        @if($item->task_comment)
                                                            <br><small class="text-muted"><i class="bi bi-chat-left-text"></i> {{ $item->task_comment }}</small>
                                                        @endif
                                                    </div>
                                                    @php
                                                        $gradeClass = match($item->task_grade) {
                                                            'Incomplete' => 'grade-incomplete',
                                                            'Further training required' => 'grade-ftr',
                                                            default => 'grade-competent',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $gradeClass }}">
                                                        {{ $item->task_grade ?? 'N/A' }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Competency Grading -->
                    <div class="mb-4">
                        <h5 class="text-primary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#competencyGrading" role="button" aria-expanded="false" aria-controls="competencyGrading">
                            <span><i class="bi bi-bar-chart-steps me-2"></i>Competency Grading</span>
                            <i class="bi bi-chevron-down"></i>
                        </h5>
                        <div class="collapse" id="competencyGrading">
                            @if($event->competencyGradings->isEmpty())
                                <p class="text-muted">No competency grading available.</p>
                            @else
                            @php
                                $lessonMeta = $event->eventLessons->keyBy('lesson_id');
                            @endphp
                                @foreach($event->competencyGradings as $grading)
                                    @php
                                        $meta = $lessonMeta[$grading->lesson_id] ?? null;
                                    @endphp
                                    <div class="mb-4">
                                        <h6 class="fw-bold text-secondary mb-2">
                                            <i class="bi bi-book me-1"></i>Lesson: {{ $grading->lesson?->lesson_title ?? 'N/A' }}
                                            @if($meta)
                                                <br><small class="text-muted">
                                                    <i class="bi bi-person-video3 me-1"></i>Instructor: {{ $meta->instructor?->fname }} {{ $meta->instructor?->lname }} |
                                                    <i class="bi bi-calendar-date me-1"></i>Date: {{ date('M d, Y', strtotime($meta->lesson_date)) }} |
                                                    <i class="bi bi-clock me-1"></i>Time: {{ date('h:i A', strtotime($meta->start_time)) }} - {{ date('h:i A', strtotime($meta->end_time)) }}
                                                </small>
                                            @endif
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle text-center shadow-sm">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Competency</th>
                                                        <th>Grade</th>
                                                        <th>Comment</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @foreach(['kno','pro','com','fpa','fpm','ltw','psd','saw','wlm'] as $competency)
                                                    @php
                                                        $grade = $grading[$competency.'_grade'] ?? null;
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
                                                        <td><strong>{{ strtoupper($competency) }}</strong></td>
                                                        <td>
                                                            <span class="badge {{ $badgeClass }}">
                                                                {{ $grade ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-start">{{ $grading[$competency.'_comment'] ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <!-- Overall Assessments -->
                    <div class="mb-4">
                        <h5 class="text-primary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#overallAssessments" role="button" aria-expanded="false" aria-controls="overallAssessments">
                            <span><i class="bi bi-award me-2"></i>Overall Assessments</span>
                            <i class="bi bi-chevron-down"></i>
                        </h5>
                        <div class="collapse" id="overallAssessments">
                            @if($event->overallAssessments->isEmpty())
                                <p class="text-muted">No overall assessments available.</p>
                            @else
                                <ul class="list-group shadow-sm">
                                    @foreach($event->overallAssessments as $assessment)
                                        <li class="list-group-item">
                                            <strong><i class="bi bi-check-circle-fill me-1"></i>Result:</strong>
                                            @php
                                                $resultClass = 'bg-secondary'; // default

                                                if ($assessment->result === 'Incomplete') {
                                                    $resultClass = 'grade-incomplete';
                                                } elseif ($assessment->result === 'Further training required') {
                                                    $resultClass = 'grade-ftr';
                                                } elseif ($assessment->result === 'Competent') {
                                                    $resultClass = 'grade-competent';
                                                }
                                            @endphp
                                            <span class="badge {{ $resultClass }}">
                                                {{ $assessment->result ?? 'N/A' }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-chat-left-dots me-1"></i>Remarks: {{ $assessment->remarks ?? 'No remarks' }}
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <hr>
                    <!-- Training Event Documents -->
                    <div class="mb-4">
                        <h5 class="text-primary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#eventDocuments" role="button" aria-expanded="false" aria-controls="eventDocuments">
                            <span><i class="bi bi-paperclip me-2"></i>Training Event Documents</span>
                            <i class="bi bi-chevron-down"></i>
                        </h5>
                        <div class="collapse" id="eventDocuments">
                            @if($event->documents->isEmpty())
                                <p class="text-muted">No documents uploaded for this event.</p>
                            @else
                                <ul class="list-group shadow-sm">
                                    @foreach($event->documents as $document)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="bi bi-file-earmark-text me-2"></i>{{ $document->courseDocument->document_name ?? 'Unnamed Document' }}
                                            </span>
                                            <a href="{{ asset('storage/' . $document->file_path) }}" download class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-download me-1"></i>Download
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                    <hr>
                    @if($event->eventLessons->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="text-primary">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Download Lesson Reports
                            </h5>                        
                            <ul class="list-group shadow-sm">
                                @foreach($event->eventLessons as $eventLesson)                              
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="bi bi-book me-1"></i>{{ $eventLesson->lesson->lesson_title ?? 'N/A' }}
                                        </span>
                                        <a href="{{ route('lesson.report.download', ['event_id' => $event->id, 'lesson_id' => $eventLesson->lesson_id, 'userID' => $event->student_id]) }}"
                                        class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- <div class="card-footer d-flex justify-content-between align-items-center bg-light p-3">
                    <span>
                        <i class="bi bi-clock-history me-1"></i>
                        {{ date('h:i A', strtotime($event->start_time)) }} - {{ date('h:i A', strtotime($event->end_time)) }}
                    </span>
                </div> -->

                @auth
                    @if(auth()->user()->id === $event->student_id)
                        <div class="card-footer bg-white border-top" >
                            @if($event->student_acknowledged)       
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-hand-thumbs-up-fill me-1"></i> You have acknowledged this training event.
                                    <br>
                                    <small><strong>Your Comments:</strong> {{ $event->student_acknowledgement_comments ?? 'N/A' }}</small>
                                </div>
                            @else
                                <form id="acknowledgeForm" class="p-3">
                                    @csrf
                                    <div class="mb-2">
                                        <label for="ack_comments" class="form-label">
                                            <i class="bi bi-chat-left-text me-1"></i>Comments (optional):
                                        </label>
                                        <textarea class="form-control" id="ack_comments" rows="2" name="comments" placeholder="Leave a note..."></textarea>
                                    </div>
                                    <button type="button"
                                            class="btn btn-outline-primary acknowledge-btn"
                                            data-event-id="{{ encode_id($event->id) }}">
                                        <i class="bi bi-check2-square me-1"></i> Acknowledge
                                    </button>
                                </form>
                            @endif
                        </div>                        
                        {{-- Buttons shown only after acknowledgment --}}
                        @if($event->student_acknowledged)
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 p-3">
                                    <a href="{{ route('training.certificate', ['event' => encode_id($event->id)]) }}" class="btn btn-success shadow-sm">
                                        <i class="bi bi-patch-check-fill me-1"></i> Generate Course Completion Certificate
                                    </a>
                                    @if($event->course->enable_feedback && !$event->student_feedback_submitted)
                                        <a href="{{ route('training.feedback.form', ['event_id' => encode_id($event->id)]) }}"
                                           class="btn btn-outline-primary shadow-sm">
                                            <i class="bi bi-chat-square-text me-1"></i> Training Feedback
                                        </a>
                                    @elseif($event->course->enable_feedback && $event->student_feedback_submitted)
                                        <div class="text-success small">
                                            <i class="bi bi-check2-circle me-1"></i> Feedback submitted. Thank you!
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif
                @endauth
            </div>
        @endif
    </div>
</section>
@endsection

@section('js_scripts')

<script>

$(document).ready(function() {

    $(document).on('click', '.acknowledge-btn', function(){
        var eventId = $(this).data('event-id');
        var ack_comment = $('#ack_comments').val();
        $.ajax({
                url: '/grading/acknowledge',
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "eventId": eventId,
                    "ack_comment": ack_comment
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
    })

    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
    }, 2000);
})


</script>

@endsection