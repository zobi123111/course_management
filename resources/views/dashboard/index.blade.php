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

@php

$messages = [];
$user = Auth::user();

// Check for Admin
if ($user->is_admin == "1") {
    foreach ($users as $u) {
        $userDoc = $u->documents; 

        // Admin Verification Alerts
        if ($u->licence_admin_verification_required == '1' && $userDoc?->licence_verified == "0" && !empty($userDoc?->licence_file)) {
            $messages[] = "📝 <strong>Licence 1</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
        }

        if ($u->licence_admin_verification_required == '1' && $userDoc?->licence_verified_2 == "0" && !empty($userDoc?->licence_file_2)) {
            $messages[] = "📝 <strong>Licence 2</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
        }

        if ($u->passport_admin_verification_required == '1' && $userDoc?->passport_verified == "0" && !empty($userDoc?->passport_file)) {
            $messages[] = "📝 <strong>Passport</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
        }

        if ($u->medical_adminRequired == '1' && $userDoc?->medical_verified == "0" && !empty($userDoc?->medical_file)) {
            $messages[] = "📝 <strong>Medical 1</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
        }

        if ($u->medical_adminRequired == '1' && $userDoc?->medical_verified_2 == "0" && !empty($userDoc?->medical_file_2)) {
            $messages[] = "📝 <strong>Medical 2</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
        }

        // Expiry Alerts
        $expiryStatuses = [
            'Licence 1' => $userDoc?->licence_status,
            'Licence 2' => $userDoc?->licence_2_status,
            'Passport' => $userDoc?->passport_status,
            'Medical 1' => $userDoc?->medical_status,
            'Medical 2' => $userDoc?->medical_2_status,
        ];

        foreach ($expiryStatuses as $doc => $status) {
            if ($status === 'Red') {
                $messages[] = "❌ <strong>{$doc}</strong> for <strong>{$u->fname} {$u->lname}</strong> has <strong>expired</strong>.";
            } elseif ($status === 'Yellow') {
                $messages[] = "⚠️ <strong>{$doc}</strong> for <strong>{$u->fname} {$u->lname}</strong> will expire in <strong>less than 90 days</strong>.";
            }
        }

        // User Ratings (untouched)
        foreach ($u->usrRatings as $userRating) {
            $ratingName = $userRating->rating?->name ?? 'Unknown Rating';

            if ($userRating->admin_verified == '0' && !empty($userRating->file_path)) {
                $messages[] = "📝 <strong>{$ratingName}</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
            }

            $status = $userRating->expiry_status;
            if ($status === 'Red') {
                $messages[] = "❌ <strong>{$ratingName}</strong> for <strong>{$u->fname} {$u->lname}</strong> has <strong>expired</strong>.";
            } elseif ($status === 'Yellow') {
                $messages[] = "⚠️ <strong>{$ratingName}</strong> for <strong>{$u->fname} {$u->lname}</strong> will expire in <strong>less than 90 days</strong>.";
            }
        }
        
    }
}

// For Regular Users
if ($user->is_admin != "1" && !empty($user->ou_id)) {
    $userDoc = $user->documents;

    if ($user->licence_admin_verification_required == '1' && $userDoc?->licence_verified == "0" && !empty($userDoc?->licence_file)) {
        $messages[] = "📝 Your <strong>Licence 1</strong> is pending admin verification.";
    }

    if ($user->licence_admin_verification_required == '1' && $userDoc?->licence_verified_2 == "0" && !empty($userDoc?->licence_file_2)) {
        $messages[] = "📝 Your <strong>Licence 2</strong> is pending admin verification.";
    }

    if ($user->passport_admin_verification_required == '1' && $userDoc?->passport_verified == "0" && !empty($userDoc?->passport_file)) {
        $messages[] = "📝 Your <strong>Passport</strong> is pending admin verification.";
    }

    if ($user->medical_adminRequired == '1' && $userDoc?->medical_verified == "0" && !empty($userDoc?->medical_file)) {
        $messages[] = "📝 Your <strong>Medical 1</strong> is pending admin verification.";
    }

    if ($user->medical_adminRequired == '1' && $userDoc?->medical_verified_2 == "0" && !empty($userDoc?->medical_file_2)) {
        $messages[] = "📝 Your <strong>Medical 2</strong> is pending admin verification.";
    }

    $expiryStatuses = [
        'Licence 1' => $userDoc?->licence_status,
        'Licence 2' => $userDoc?->licence_2_status,
        'Passport' => $userDoc?->passport_status,
        'Medical 1' => $userDoc?->medical_status,
        'Medical 2' => $userDoc?->medical_2_status,
    ];

    foreach ($expiryStatuses as $doc => $status) {
        if ($status === 'Red') {
            $messages[] = "❌ Your <strong>{$doc}</strong> has <strong>expired</strong>.";
        } elseif ($status === 'Yellow') {
            $messages[] = "⚠️ Your <strong>{$doc}</strong> will expire in <strong>less than 90 days</strong>.";
        }
    }

    // User Ratings (untouched)
    foreach ($user->usrRatings as $userRating) {
        $ratingName = $userRating->rating?->name ?? 'Unknown Rating';

        if ($userRating->admin_verified == '0' && !empty($userRating->file_path)) {
            $messages[] = "📝 Your <strong>{$ratingName}</strong> is pending admin verification.";
        }

        $status = $userRating->expiry_status;
        if ($status === 'Red') {
            $messages[] = "❌ Your <strong>{$ratingName}</strong> has <strong>expired</strong>.";
        } elseif ($status === 'Yellow') {
            $messages[] = "⚠️ Your <strong>{$ratingName}</strong> will expire in <strong>less than 90 days</strong>.";
        }
    }

}
@endphp


@if (!empty($messages))
    <div id="alertBox" class="alert alert-warning alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h5><strong>⚠️ Attention Required</strong></h5>
        <ul class="mb-0">
            @foreach ($messages as $message)
                <li>{!! $message !!}</li>
            @endforeach
        </ul>
    </div>
@endif





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
            <th>UK Licence Status</th>
            <th>EASA Licence Status</th>
            <th>UK Medical Status</th> 
            <th>EASA Medical Status</th> 
            <th>Passport Status</th> 
            <th>Action</th> 
        </tr>
    </thead>
<tbody>
@php
    function getTooltip($status, $type) {
        return match ($status) {
            'Red' => "This {$type} has expired.",
            'Yellow' => "This {$type} will expire soon.",
            'Green' => "This {$type} is valid.",
            'Non-Expiring' => "This {$type} does not expire.",
            default => "Status unknown.",
        };
    }
@endphp

@foreach($users as $user)
    <tr>
        <td>{{ $user->fname }} {{ $user->lname }}</td>

        @php $doc = $user->documents;  @endphp

        {{-- Licence 1 --}}
        <td>
            @if($doc && $doc->licence_file_uploaded)
                @php
                    if ($doc->licence_non_expiring) {
                        $status = 'Non-Expiring';
                        $color = 'success';
                        $date = 'Non-Expiring';
                    } else {
                        $status = $doc->licence_status;
                        $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                        $date = $doc->licence_expiry_date ? date('d/m/Y', strtotime($doc->licence_expiry_date)) : 'N/A';
                    }
                    $tooltip = getTooltip($status, 'licence 1');
                @endphp
                <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
            @else
                <span class="text-muted">Not Uploaded</span>
            @endif
        </td>

        {{-- Licence 2 --}}
        <td>
            @if($doc && $doc->licence_file_uploaded_2)
                @php
                    if ($doc->licence_non_expiring_2) {
                        $status = 'Non-Expiring';
                        $color = 'success';
                        $date = 'Non-Expiring';
                    } else {
                        $status = $doc->licence_2_status;
                        $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                        $date = $doc->licence_expiry_date_2 ? date('d/m/Y', strtotime($doc->licence_expiry_date_2)) : 'N/A';
                    }
                    $tooltip = getTooltip($status, 'licence 2');
                @endphp
                <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
            @else
                <span class="text-muted">Not Uploaded</span>
            @endif
        </td>

        {{-- Medical 1 --}}
        <td>
            @if($doc && $doc->medical_file_uploaded)
                @php
                    $status = $doc->medical_status;
                    $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                    $date = $doc->medical_expirydate ? date('d/m/Y', strtotime($doc->medical_expirydate)) : 'N/A';
                    $tooltip = getTooltip($status, 'medical 1');
                @endphp
                <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
            @else
                <span class="text-muted">Not Uploaded</span>
            @endif
        </td>

        {{-- Medical 2 --}}
        <td>
            @if($doc && $doc->medical_file_uploaded_2)
                @php
                    $status = $doc->medical_2_status;
                    $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                    $date = $doc->medical_expirydate_2 ? date('d/m/Y', strtotime($doc->medical_expirydate_2)) : 'N/A';
                    $tooltip = getTooltip($status, 'medical 2');
                @endphp
                <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
            @else
                <span class="text-muted">Not Uploaded</span>
            @endif
        </td>

        {{-- Passport --}}
        <td>
            @if($doc && $doc->passport_file_uploaded)
                @php
                    $status = $doc->passport_status;
                    $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                    $date = $doc->passport_expiry_date ? date('d/m/Y', strtotime($doc->passport_expiry_date)) : 'N/A';
                    $tooltip = getTooltip($status, 'passport');
                @endphp
                <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
            @else
                <span class="text-muted">Not Uploaded</span>
            @endif
        </td>

        {{-- View Link --}}
        <td>
            <a href="{{ route('user.show', ['user_id' => encode_id($user->id)]) }}" class="view-icon" title="View User" style="font-size:18px; cursor: pointer;">
                <i class="fa fa-eye text-danger me-2"></i>
            </a>
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
        pageLength: 10,
        language: {
        emptyTable: "No records found"
      }
    });

});

setTimeout(function() {
        $('#successMessage').fadeOut('fast');
}, 3000);

document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@endsection