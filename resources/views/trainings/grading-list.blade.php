@extends('layout.app')

@section('title', 'Grading List')
@section('sub-title', 'Student Grading')

@section('content')
<section class="section">
    <div class="container">
    @if($events->isEmpty())
    <div class="alert alert-info">
        No Grading available for this event.
    </div>
    @else
    <div class="row">
    @foreach($events as $event)
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">{{ $event->course?->course_name }}</h5>
                <small>
                    Instructor: {{ $event->instructor?->fname }} {{ $event->instructor?->lname }} | 
                    Group: {{ $event->group?->name }}
                </small>
            </div>
            <div class="card-body">
                <!-- Sub-Lesson Based Grading -->
                <h6 class="text-uppercase text-muted border-bottom pb-1 mb-3">
                    Task Based Grading
                </h6>
                @if($event->taskGradings->isEmpty())
                    <p class="text-muted">No task grading available.</p>
                @else
                    @php
                        // Group task gradings by lesson
                        $groupedTasks = $event->taskGradings->groupBy('lesson_id');
                    @endphp
                    <ul class="list-group mb-4">
                        @foreach($groupedTasks as $lessonId => $tasks)
                            <li class="list-group-item">
                                <div>
                                    <strong>Lesson:</strong> {{ optional($tasks->first()->lesson)->lesson_title ?? 'N/A' }}
                                </div>
                                <ul class="list-group mt-2">
                                    @foreach($tasks as $task)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>
                                                <strong>Sub-Lesson:</strong> {{ $task->subLesson?->title ?? 'N/A' }}
                                            </span>
                                            <span class="badge bg-success">{{ $task->task_grade }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <!-- Lesson Based Competency Grading -->
                <h6 class="text-uppercase text-muted border-bottom pb-1 mb-3">
                    Competency Grading
                </h6>
                @if($event->competencyGradings->isEmpty())
                    <p class="text-muted">No competency grading available.</p>
                @else
                    <ul class="list-group">
                        @foreach($event->competencyGradings as $competency)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <strong>Lesson:</strong> {{ $competency->lesson?->lesson_title ?? 'N/A' }}
                                </span>
                                <span class="badge bg-warning">{{ $competency->competency_grade }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <!-- Overall Assessments -->
                <h6 class="text-uppercase text-muted border-bottom pb-1 mt-4 mb-3">
                    Overall Assessments
                </h6>
                @if($event->overallAssessments->isEmpty())
                    <p class="text-muted">No overall assessments available.</p>
                @else
                    <ul class="list-group mt-2">
                        @foreach($event->overallAssessments as $assessment)
                            <li class="list-group-item">
                                <div>
                                    <strong>Overall Result:</strong> {{ $assessment->result }}
                                </div>
                                <small class="text-muted">
                                    Remarks: {{ $assessment->remarks ?? 'No remarks' }}
                                </small>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="card-footer text-muted">
                {{ date('h:i A', strtotime($event->start_time)) }} - {{ date('h:i A', strtotime($event->end_time)) }}
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif




    </div>
</section>
@endsection
