@section('title', 'Dashboard')

@php
$currentUser = Auth()->user();
if (empty($currentUser->is_admin) && empty($currentUser->is_owner)) {
$subTitle = "Welcome to " . $currentUser->organization->org_unit_name ?? null. " Dashboard";
} elseif ($currentUser->is_admin == 1) {
$subTitle = "Welcome to " . (isset($currentUser->organization) ? $currentUser->organization->org_unit_name :
"Dashboard");
} else {
$subTitle = "Welcome to Admin Dashboard";
}
@endphp

@section('sub-title', $subTitle)

@extends('layout.app')
@section('content')


<section class="section dashboard">
    @if(!empty(auth()->user()->is_owner))
    <div class="row">

                <!-- Users Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('users','user.index')->isNotEmpty())
                    <a href="{{ route('user.index') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Users</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-user-5-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $user_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Users Card -->

                <!-- Courses Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('courses','course.index')->isNotEmpty())
                    <a href="{{ route('course.index') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Courses</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-user-5-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $course_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Courses Card -->

                <!-- Group Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('groups','group.index')->isNotEmpty())
                    <a href="{{ route('group.index') }}" class="text-decoration-none">
                        @endif
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Groups</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-group-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $group_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Group Card -->

                <!-- Folder Card -->
                <div class="col-xxl-3 col-xl-12">
                @if(checkAllowedModule('folders','folder.index')->isNotEmpty())
                    <a href="{{ route('folder.index') }}" class="text-decoration-none"> 
                        @endif
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Folders</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-briefcase-2-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $folder_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(auth()->user()->role == 1)
                    </a>
                    @endif
                </div>
                <!-- End Folder Card -->

        <!-- End Left side columns -->
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Documents Pie Chart</h5>

                    <!-- Pie Chart -->
                    <canvas id="pieChart" style="max-height: 400px;"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        let ctx = document.querySelector('#pieChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: ['Total Documents', 'Read Documents', 'Unread Documents'],
                                datasets: [{
                                    label: 'Document Statistics',
                      data: [
                        {{ $totalDocuments }},
                        {{ $readDocuments }},
                        {{ $unreadDocuments }}
                                    ],
                                    backgroundColor: [
                                        'rgb(54, 162, 235)', // Blue
                                        'rgb(75, 192, 192)', // Green
                                        'rgb(255, 99, 132)' // Red
                                    ],
                                    hoverOffset: 4
                                }]
                            }
                        });
                    });
                    </script>
                    <!-- End Pie Chart -->

                </div>
            </div>
        </div>
    </div>
    @elseif (auth()->user()->is_admin==1)
    <div class="row">         
                <!-- Users Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('users','user.index')->isNotEmpty())
                    <a href="{{ route('user.index') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Users</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-user-5-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $user_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Users Card -->

                <!-- Courses Card -->
                <div class="col-xxl-3 col-md-6">
                @if(checkAllowedModule('courses','course.index')->isNotEmpty())
                    <a href="{{ route('course.index') }}" class="text-decoration-none">
                        @endif
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Courses</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-user-5-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $course_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Courses Card -->

                <!-- Group Card -->
                <div class="col-xxl-3 col-md-6">
                @if(checkAllowedModule('groups','group.index')->isNotEmpty())
                    <a href="{{ route('group.index') }}" class="text-decoration-none">
                        @endif
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Groups</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-group-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $group_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Group Card -->

                <!-- Folder Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('folders','folder.index')->isNotEmpty())
                        <a href="{{ route('folder.index') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Folders</h5>

                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-briefcase-2-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $folder_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </a>
                </div>
                <!-- End Folder Card -->

                <!-- Resource  Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('resource','resource.approval')->isNotEmpty())
                    <a href="{{ url('resource.approval') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Resource Request</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-briefcase-2-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $requestCount }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Resource  Card -->


    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Documents Pie Chart</h5>

                    <!-- Pie Chart -->
                    <canvas id="pieChart" style="max-height: 400px;"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        let ctx = document.querySelector('#pieChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: ['Total Documents', 'Read Documents', 'Unread Documents'],
                                datasets: [{
                                    label: 'Document Statistics',
                    data: [
                        {{ $totalDocuments }},
                        {{ $readDocuments }},
                        {{ $unreadDocuments }}
                                    ],
                                    backgroundColor: [
                                        'rgb(54, 162, 235)', // Blue
                                        'rgb(75, 192, 192)', // Green
                                        'rgb(255, 99, 132)' // Red
                                    ],
                                    hoverOffset: 4
                                }]
                            }
                        });
                    });
                    </script>
                    <!-- End Pie Chart -->
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">

                <!-- Courses Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('courses','course.index')->isNotEmpty())
                    <a href="{{ route('course.index') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Courses</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-user-5-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $course_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Users Card -->

                <!-- Group Card -->
                <div class="col-xxl-3 col-md-6">
                    @if(checkAllowedModule('groups','group.index')->isNotEmpty())
                    <a href="{{ route('group.index') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Groups</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-group-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $group_count }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Group Card -->

                <!-- Folder Card -->
                <div class="col-xxl-3 col-xl-12">
                        @if(checkAllowedModule('folders','folder.index')->isNotEmpty())
                        <a href="{{ route('folder.index') }}" class="text-decoration-none">
                        @endif
                            <div class="card info-card customers-card">
                                <div class="card-body">
                                    <h5 class="card-title">Folders</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="ri-briefcase-2-fill dashboard_icon"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>{{ $folder_count }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                </div>
                <!-- End Folder Card -->

                <!-- Resource  Card -->
                <div class="col-xxl-3 col-xl-12">
                    @if(checkAllowedModule('resource','resource.approval')->isNotEmpty())
                    <a href="{{ url('resource.approval') }}" class="text-decoration-none">
                    @endif
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Resource Request</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-briefcase-2-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $requestCount }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Resource  Card -->


    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Documents Pie Chart</h5>

                    <!-- Pie Chart -->
                    <canvas id="pieChart" style="max-height: 400px;"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        let ctx = document.querySelector('#pieChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: ['Total Documents', 'Read Documents', 'Unread Documents'],
                                datasets: [{
                                    label: 'Document Statistics',
                      data: [
                        {{ $totalDocuments }},
                        {{ $readDocuments }},
                        {{ $unreadDocuments }}
                                    ],
                                    backgroundColor: [
                                        'rgb(54, 162, 235)', // Blue
                                        'rgb(75, 192, 192)', // Green
                                        'rgb(255, 99, 132)' // Red
                                    ],
                                    hoverOffset: 4
                                }]
                            }
                        });
                    });
                    </script>
                    <!-- End Pie Chart -->

                </div>
            </div>
        </div>
    </div>
    @endif
</section>

@endsection

@section('js_scripts')


@endsection