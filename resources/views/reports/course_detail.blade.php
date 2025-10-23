@extends('layout.app')
@section('title', 'Course Report')
@section('sub-title', 'Students Enrolled In Course')
<style>
    .row-alert {
        border-left: 6px solid #dc3545 !important;
        background-color: #f8d7da !important;
    }

    .row-alert .fa {
        color: #721c24 !important; 
    }

    #flash-message {
        display: none;
        font-size: 16px;
        font-weight: 500;
    }

    .status-group .status-box {
        padding: 8px 16px;
        border: 1px solid #dee2e6;
        border-right: none;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 50px;
        font-weight: bold;
    }
</style>

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="backbtn" style= "display: flex; justify-content: space-between;">
                <h3 class="text-primary mt-4">{{ $course->course_name }}</h3>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary mt-4 " style=" border-radius: 26px;"><i class="bi bi-arrow-left-circle-fill"></i> Back to Reports</a>
            </div>

            <div id="flash-message" class="alert mt-3"></div>

            <!-- Toggle Show Archived and Show Failing -->
            <div class="d-flex gap-4 mb-2 mt-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="toggleArchived" {{ $showArchived ? 'checked' : '' }}>
                    <strong><label class="form-check-label" for="toggleArchived">
                        Show Archived Students
                    </label></strong>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="toggleFailing" {{ request('show_failing') == '1' ? 'checked' : '' }}>
                    <strong><label class="form-check-label" for="toggleFailing">
                        Show Failing Students
                    </label><strong>
                </div>
            </div>

            <!-- Pie Chart + Legend Section Styled Like Example -->
            <div class="text-center my-3">
                <!-- Chart Canvas -->
                <div id="pieChartContainer" class="mx-auto" style="width: 250px; height: 250px;">
                    <canvas id="studentChart" width="250" height="250"></canvas>
                </div>

                <!-- Legend Badges Below Pie Chart -->
                <div class="row mt-3 justify-content-center gap-2">
                    <div class="col-auto">
                        <span class="badge text-white" style="background-color: #007bff;" title="Enrolled">Enrolled: {{ $chartData['enrolled'] }}</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge text-white" style="background-color: #28a745;" title="Completed">Completed: {{ $chartData['completed'] }}</span>
                    </div>
                    <div class="col-auto">
                        <span class="badge text-white" style="background-color: #ffc107;" title="Active">Active: {{ $chartData['active'] }}</span>
                    </div>

                    @if ($showArchived)
                    <div class="col-auto">
                        <span class="badge text-white" style="background-color: #6c757d;" title="Archived">
                            Archived: {{ $chartData['archived'] }}
                        </span>
                    </div>
                    @endif


                    @if ($showFailing)
                    <div class="col-auto">
                        <span class="badge text-white" style="background-color: #dc3545;" title="Failing">Failing: {{ $chartData['failing'] }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($students->count())
            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead>
                        <tr>
                            <th scope="col">Full Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Start Date</th>
                            <th scope="col">End Date</th>
                            <th scope="col">Archive</th>
                            <th scope="col">Progress</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                       <tr class="clickable-row {{ $student->show_alert ? 'row-alert' : '' }}" 
                            data-href="{{ route('training.grading-list', ['event_id' => encode_id($student->event_id)]) }}" 
                            style="cursor: pointer;">
                            <td class="expiry">
                                @if($student->show_alert)
                                    <i class="fas fa-exclamation-triangle me-1" style="color: #dc3545 !important;" title="Nearing 6-month deadline"></i>
                                @endif
                                {{ $student->fname }} {{ $student->lname }}
                            </td>  
                            <td>{{ $student->email ?? 'N/A' }}</td>
                            <td>{{ $student->event_date ? \Carbon\Carbon::parse($student->event_date)->format('d-m-Y') : '--' }}</td>
                            <td>{{ $student->course_end_date ? \Carbon\Carbon::parse($student->course_end_date)->format('d-m-Y') : '--' }}</td>
                            <td class="no-click">
                                <input type="checkbox" class="archive-checkbox"
                                    data-id="{{ $student->id }}"
                                    data-event="{{ $student->event_id }}"
                                    {{ $student->is_archived ? 'checked' : '' }}>
                            </td>
                            <td class="no-click"> 
                                @php
                                    $progress = $student->progress;
                                    $total = max(1, $progress['total']); 
                                    $incompletePercent = round(($progress['incomplete'] / $total) * 100);
                                    $furtherPercent = round(($progress['further'] / $total) * 100);
                                    $competentPercent = 100 - ($incompletePercent + $furtherPercent);
                                @endphp

                                <div class="d-flex justify-content-center status-group progress" style="height: 30px;">
                                    @if ($incompletePercent > 0)
                                        <div class="progress-bar status-box" role="progressbar"
                                            style="width: {{ $incompletePercent }}%; background-color: #FFFF00; color: #000; font-weight: 700; font-size: 11px;"
                                            title="Incomplete">
                                            {{ $incompletePercent . '%' }}
                                        </div>
                                    @endif
                                   
                                  
                                    @if ($furtherPercent > 0) 
                                        <div class="progress-bar status-box" role="progressbar"
                                            style="width: {{ $furtherPercent }}%; background-color: #ffc107; color: #000; font-weight: 700; font-size: 11px;"
                                            title="Further Training Required">
                                            {{ $furtherPercent . '%' }}
                                        </div>
                                    @endif

                                    @if ($competentPercent > 0)
                                        <div class="progress-bar status-box" role="progressbar"
                                            style="width: {{ $competentPercent }}%; background-color: #008000; color: #000; font-weight: 700; font-size: 11px;"
                                            title="Competent">
                                            {{ $competentPercent . '%' }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('training.grading-list', ['event_id' =>  encode_id($student->event_id)]) }}" class="view-icon" title="View Record" style="font-size:18px; cursor: pointer;">
                                    <i class="fa fa-eye text-danger"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-muted">No students found for this course.</p>
            @endif
        </div>
    </div>
</div>
@endsection
@section('js_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function () {
    $('#studentsTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ students",
            paginate: {
                previous: "Prev",
                next: "Next"
            }
        }
    });

    $('#studentsTable tbody').on('click', 'tr.clickable-row', function (e) {
        // Prevent redirection if clicked element is a checkbox, label, or inside a td with class 'no-click'
        if (
            $(e.target).is('input[type="checkbox"]') ||
            $(e.target).is('label') ||
            $(e.target).closest('td').hasClass('no-click')
        ) {
            return;
        }
        if (!$(e.target).closest('a').length) {
            window.location = $(this).data('href');
        }
    });

    // Toggle archived filter
    $('#toggleArchived').on('change', function () {
        const showArchived = $(this).is(':checked') ? 1 : 0;
        const url = new URL(window.location.href);
        url.searchParams.set('show_archived', showArchived);
        window.location.href = url.toString();
    });

    $('#toggleFailing').on('change', function () {
        const showFailing = $(this).is(':checked') ? '1' : '';
        const url = new URL(window.location.href);

        if (showFailing) {
            url.searchParams.set('show_failing', showFailing);
        } else {
            url.searchParams.delete('show_failing');
        }

        window.location.href = url.toString();
    });

        $('.archive-checkbox').on('change', function () {
        const checkbox = $(this);
        const studentId = checkbox.data('id');
        const eventId = checkbox.data('event');
        const isArchived = checkbox.is(':checked') ? 1 : 0;

        if (isArchived === 0) {
            if (!confirm("Are you sure you want to unarchive this student?")) {
                checkbox.prop('checked', true);
                return;
            }
        }

        $.ajax({
            url: "{{ route('students.archive.ajax') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: studentId,
                event_id: eventId,
                is_archived: isArchived
            },
            success: function (res) {
                if (isArchived && !$('#toggleArchived').is(':checked')) {
                    checkbox.closest('tr').fadeOut();
                }

                const msg = res.message;
                showFlashMessage(msg, 'success');

                if (res.chartData) {
                    const updatedData = [
                        res.chartData.enrolled,
                        res.chartData.completed,
                        res.chartData.active
                    ];

                    let labels = ['Enrolled', 'Completed', 'Active'];
                    let colors = ['#007bff', '#28a745', '#ffc107'];

                    if ($('#toggleArchived').is(':checked')) {
                        updatedData.push(res.chartData.archived);
                        labels.push('Archived');
                        colors.push('#6c757d');

                        // Update badge
                        $('span[title="Archived"]').text('Archived: ' + res.chartData.archived);
                    }

                    if ($('#toggleFailing').is(':checked')) {
                        updatedData.push(res.chartData.failing || 0);
                        labels.push('Failing');
                        colors.push('#dc3545');

                        // Update badge
                        $('span[title="Failing"]').text('Failing: ' + res.chartData.failing);
                    }

                    // Always update all other badges
                    $('span[title="Enrolled"]').text('Enrolled: ' + res.chartData.enrolled);
                    $('span[title="Completed"]').text('Completed: ' + res.chartData.completed);
                    $('span[title="Active"]').text('Active: ' + res.chartData.active);

                    const isAllZero = updatedData.every(val => val === 0);

                    if (isAllZero) {
                        studentChart.data = {
                            labels: ['No Data (0)'],
                            datasets: [{
                                data: [0.0001],
                                backgroundColor: ['#d6d6d6']
                            }]
                        };
                    } else {
                        studentChart.data = {
                            labels: labels,
                            datasets: [{
                                data: updatedData,
                                backgroundColor: colors
                            }]
                        };
                    }

                    studentChart.update();
                }
            },
            error: function () {
                showFlashMessage("Failed to update archive status.", 'danger');
            }
        });
    });

    function showFlashMessage(message, type = 'success') {
        const flash = $('#flash-message');
        flash
            .removeClass()
            .addClass('alert alert-' + type)
            .text(message)
            .fadeIn();

        setTimeout(() => {
            flash.fadeOut();
        }, 3000);
    }

    const ctx = document.getElementById('studentChart').getContext('2d');

    const rawData = {
        enrolled: {{ $chartData['enrolled'] }},
        completed: {{ $chartData['completed'] }},
        active: {{ $chartData['active'] }},
        @if($showArchived) archived: {{ $chartData['archived'] }}, @endif
        @if($showFailing) failing: {{ $chartData['failing'] }} @endif
    };

    let chartValues = Object.values(rawData).filter(val => typeof val === 'number' && !isNaN(val));
    const isAllZero = chartValues.length === 0 || chartValues.every(val => val === 0);

    let pieData;

    if (isAllZero) {
        pieData = {
            labels: ['No Data'],
            datasets: [{
                data: [0.0001], // simulate 0 so chart renders something
                backgroundColor: ['#d6d6d6'],
            }]
        };
    }else {
        pieData = {
            labels: [
                'Enrolled',
                'Completed',
                'Active',
                @if($showArchived) 'Archived', @endif
                @if($showFailing) 'Failing' @endif
            ],
            datasets: [{
                data: [
                    rawData.enrolled,
                    rawData.completed,
                    rawData.active,
                    @if($showArchived) rawData.archived, @endif
                    @if($showFailing) rawData.failing @endif
                ],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    @if($showArchived) '#6c757d', @endif
                    @if($showFailing) '#dc3545' @endif
                ],
                hoverOffset: 5
            }]
        };
    }

    studentChart = new Chart(ctx, {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
});
</script>
@endsection
