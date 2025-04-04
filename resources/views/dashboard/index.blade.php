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

<?php 
if(Auth()->user()->is_admin == "1"){


$messages = [];
foreach ($users as $user) {

    if ($user->licence_file_uploaded == "1" && $user->licence_admin_verification_required == '1' && $user->licence_verified=="0") { 
        $messages[] = "Licence verification required for " . $user->fname . " " . $user->lname;

    }
    if ($user->passport_file_uploaded == "1" && $user->passport_admin_verification_required == '1' && $user->passport_verified=="0") {
        $messages[] = "Passport verification required for " . $user->fname . " " . $user->lname;

    }
    if ($user->medical_file_uploaded == "1"  && $user->medical_adminRequired == '1' && $user->medical_verified=="0") {
        $messages[] = "Medical verification required for " . $user->fname . " " . $user->lname;

    }
}

if (!empty($messages)) { ?>
    <div id="successMessage" class="alert alert-success fade show" role="alert">
       
        <?php echo implode('<br>', $messages); ?>
    </div>
<?php }} ?>



@if(auth()->user()->is_admin == 1)
@if(session()->has('message'))  
<div id="successMessage" class="alert alert-success fade show" role="alert">
  <i class="bi bi-check-circle me-1"></i>
  {{ session()->get('message') }}
</div>
@endif
@endif

@if(auth()->user()->is_admin == 1)
<table class="table table-bordered table-striped" id="document_table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Licence Status</th>
            <th>Medical Status</th> 
            <th>Passport Status</th> 
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->fname }} {{ $user->lname }}</td>
            <td>
            @if($user->licence_file_uploaded)
                <strong style="color: 
                    {{ $user->licence_status == 'Red' ? 'red' : 
                       ($user->licence_status == 'Amber' ? 'orange' : 'green') }}">
                
                    @if($user->licence_status == 'Red')
                         <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Expired </span>
                    @elseif($user->licence_status == 'Orange')
                        <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon</span>
                    @elseif($user->licence_status == 'Amber')
                        <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Expiring in 3 Months</span>
                    @else
                        <span class="text-success"> Valid </span>
                    @endif
                </strong>
             @endif
            </td>
            <td>
            @if($user->medical_issuedby)
                <strong style="color: 
                    {{ $user->medical_status == 'Red' ? 'red' : 
                       ($user->medical_status == 'Amber' ? 'orange' : 'green') }}">
                  <?php //  dump($user->Medical_Status ); ?>
                    @if($user->Medical_Status == 'Red')
                         <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Expired </span>
                    @elseif($user->Medical_Status == 'Orange')
                        <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon</span>
                    @elseif($user->Medical_Status == 'Amber')
                        <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Expiring in 3 Months</span>
                    @else
                        <span class="text-success"> Valid </span>
                    @endif
                </strong>
            @endif
            </td>
            <td>
                @if($user->passport_file)
                    <strong style="color: 
                        {{ $user->passport_status == 'Red' ? 'red' : 
                        ($user->passport_status == 'Amber' ? 'orange' : 'green') }}">
                            @if($user->passport_status == 'Red')
                                        <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Expired </span>
                                    @elseif($user->passport_status == 'Orange')
                                        <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon</span>
                                    @elseif($user->passport_status == 'Amber')
                                        <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Expiring in 3 Months</span>
                                    @else
                                        <span class="text-success"> Valid </span>
                                    @endif
                    </strong>
                @endif

            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif


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
                    <p>Total Documents: <strong>{{ $totalDocuments }}</strong></p>

                    <!-- Pie Chart -->
                    <canvas id="pieChart" style="max-height: 400px;"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            let ctx = document.querySelector('#pieChart').getContext('2d');
                            
                            new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: ['Read Documents', 'Unread Documents'],
                                    datasets: [{
                                        label: 'Document Statistics',
                                        data: [
                                            {{ $readDocuments }},
                                            {{ $unreadDocuments }}
                                        ],
                                        backgroundColor: [
                                            'rgb(75, 192, 192)', // Green (Read)
                                            'rgb(255, 99, 132)'  // Red (Unread)
                                        ],
                                        hoverOffset: 4
                                    }]
                                },
                                options: {
                                    plugins: {
                                        tooltip: {
                                            callbacks: {
                                                label: function(tooltipItem) {
                                                    let value = tooltipItem.raw;
                                                    let percentage = ((value / {{ $totalDocuments }}) * 100).toFixed(2);
                                                    return `${tooltipItem.label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        },
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
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
                    <p>Total Documents: <strong>{{ $totalDocuments }}</strong></p>

                    <!-- Pie Chart -->
                    <canvas id="pieChart" style="max-height: 400px;"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            let ctx = document.querySelector('#pieChart').getContext('2d');
                            
                            new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: ['Read Documents', 'Unread Documents'],
                                    datasets: [{
                                        label: 'Document Statistics',
                                        data: [
                                            {{ $readDocuments }},
                                            {{ $unreadDocuments }}
                                        ],
                                        backgroundColor: [
                                            'rgb(75, 192, 192)', // Green (Read)
                                            'rgb(255, 99, 132)'  // Red (Unread)
                                        ],
                                        hoverOffset: 4
                                    }]
                                },
                                options: {
                                    plugins: {
                                        tooltip: {
                                            callbacks: {
                                                label: function(tooltipItem) {
                                                    let value = tooltipItem.raw;
                                                    let percentage = ((value / {{ $totalDocuments }}) * 100).toFixed(2);
                                                    return `${tooltipItem.label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        },
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
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
                <!-- <div class="col-xxl-3 col-xl-12">
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
                </div> -->
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
                    <p>Total Documents: <strong>{{ $totalDocuments }}</strong></p>

                    <!-- Pie Chart -->
                    <canvas id="pieChart" style="max-height: 400px;"></canvas>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            let ctx = document.querySelector('#pieChart').getContext('2d');
                            
                            new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: ['Read Documents', 'Unread Documents'],
                                    datasets: [{
                                        label: 'Document Statistics',
                                        data: [
                                            {{ $readDocuments }},
                                            {{ $unreadDocuments }}
                                        ],
                                        backgroundColor: [
                                            'rgb(75, 192, 192)', // Green (Read)
                                            'rgb(255, 99, 132)'  // Red (Unread)
                                        ],
                                        hoverOffset: 4
                                    }]
                                },
                                options: {
                                    plugins: {
                                        tooltip: {
                                            callbacks: {
                                                label: function(tooltipItem) {
                                                    let value = tooltipItem.raw;
                                                    let percentage = ((value / {{ $totalDocuments }}) * 100).toFixed(2);
                                                    return `${tooltipItem.label}: ${value} (${percentage}%)`;
                                                }
                                            }
                                        },
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
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
<script>
 $(document).ready(function() {
    $('#document_table').DataTable({ 
        searching: true,
        pageLength: 5,
        language: {
        emptyTable: "No records found"
      }
    });
});

    setTimeout(function() {
            $('#successMessage').fadeOut('fast');
        }, 3000);
</script>

@endsection