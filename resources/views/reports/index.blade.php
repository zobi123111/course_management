@section('title', 'Reports Section')
@section('sub-title', 'Reports Section')
@extends('layout.app')
@section('content')


@if(session()->has('message'))  
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif
<br>

<div class="card pt-4">
    <div class="card-body">
<div class="row">
    @foreach ($courses as $course)
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $course->course_name }}</h5>
                    <p class="mb-2">
                        <span class="badge bg-info">In Training: {{ $course->students_enrolled }}</span>
                        <span class="badge bg-success">Completed: {{ $course->students_completed }}</span>
                    </p>

                    @if ($course->students && $course->students->count())
                        <ul class="list-group list-group-flush">
                            @foreach ($course->students as $student)
                                <li class="list-group-item">
                                    <strong>{{ $student->fname }} {{ $student->lname }}</strong><br>
                                    <small>Date Started: <em>--</em></small><br>
                                    <small>Completion Target: <em>--</em></small>
                                    <!-- Add logic to fill the above if needed -->
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted">No enrolled students found.</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

    </div>
</div>


@endsection

@section('js_scripts')

<script>
</script>

@endsection

