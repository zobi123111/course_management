@extends('layout.app')

@section('title', 'Reports Section')
@section('sub-title', 'Reports Section')

<style>
.card-hover:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease-in-out;
}
.card-hover {
    transition: all 0.3s ease-in-out;
}
</style>

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
        {{-- OU Filter for Owner --}}
        <div class="row mb-3">
            @if(auth()->user()->is_owner == 1) 
                <div class="col-md-4">
                    <label for="ou_filter" class="form-label">Select Organization Unit:</label>
                    <select id="ou_filter" class="form-select" onchange="filterCoursesByOU(this.value)">
                        <option value="">All OUs</option>
                        @foreach($ous as $ou)
                            <option value="{{ $ou->id }}">{{ $ou->org_unit_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- Courses List --}}
        <div class="row" id="course_list">
            @forelse ($courses as $course)       
                    
                <div class="col-md-4 mb-4 course-card" data-ou="{{ $course->ou_id }}">
                    <a href="{{ route('reports.course', encode_id($course->id )) }}" class="text-decoration-none text-dark">
                        <div class="card shadow-sm border h-100 card-hover">
                            <div class="card-body">
                                <h5 class="card-title text-primary">{{ $course->course_name }}</h5>

                                <div class="d-flex align-items-center mt-3">
                                    <!-- Doughnut chart -->
                                    <canvas id="chart-{{ $course->id }}" width="100" height="100"></canvas>

                                    <!-- Text info -->
                                    <div class="ms-4">
                                        <div><span class="badge bg-primary">Enrolled: {{ $course->students_enrolled }}</span></div>
                                        <div class="mt-2"><span class="badge bg-warning text-dark">Active: {{ $course->students_active }}</span></div>
                                        <div class="mt-2"><span class="badge bg-success">Completed: {{ $course->students_completed }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="bi bi-exclamation-circle me-1"></i> No courses available.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- JS-based "No courses found" message for filter --}}
        <div class="row">
            <div class="col-12" id="no_courses_msg" style="display: none;">
                <div class="alert alert-warning text-center" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i> No courses found for the selected Organization Unit.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function filterCoursesByOU(ouId) { 
        // Save selected OU to sessionStorage
        sessionStorage.setItem('selected_ou', ouId);

        let visibleCount = 0;

        document.querySelectorAll('.course-card').forEach(card => {
            const courseOuId = card.getAttribute('data-ou');
            const show = !ouId || courseOuId === ouId;

            card.style.display = show ? 'block' : 'none';

            if (show) visibleCount++;
        });

        // Show or hide the "No courses found" message
        document.getElementById('no_courses_msg').style.display = visibleCount === 0 ? 'block' : 'none';
    }

    // On page load: restore selected OU filter if available
    document.addEventListener('DOMContentLoaded', function () {
        @foreach($courses as $course)
           const enrolled_{{ $course->id }} = {{ $course->students_enrolled ?? 0 }};
           const active_{{ $course->id }} = {{ $course->students_active ?? 0 }};
             const completed_{{ $course->id }} = {{ $course->students_completed ?? 0 }};

            const hasData_{{ $course->id }} = enrolled_{{ $course->id }} > 0 || active_{{ $course->id }} > 0 || completed_{{ $course->id }} > 0;

            const ctx{{ $course->id }} = document.getElementById('chart-{{ $course->id }}').getContext('2d');

            new Chart(ctx{{ $course->id }}, {
                type: 'doughnut',
                data: {
                    labels: hasData_{{ $course->id }} ? ['Enrolled', 'Active', 'Completed'] : ['No Data'],
                    datasets: [{
                        data: hasData_{{ $course->id }} 
                            ? [enrolled_{{ $course->id }}, active_{{ $course->id }}, completed_{{ $course->id }}]
                            : [1],
                        backgroundColor: hasData_{{ $course->id }} 
                            ? ['#0d6efd', '#ffc107', '#198754']  // blue, yellow, green
                            : ['#dee2e6'],                       // gray fallback
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return hasData_{{ $course->id }}
                                        ? `${context.label}: ${context.parsed}`
                                        : 'No course data available';
                                }
                            }
                        }
                    }
                }
            });
        @endforeach
    });
</script>
@endsection
