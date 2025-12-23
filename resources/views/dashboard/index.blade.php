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

<style>
    div#document_table_filter,
    div#document_table_length {
        margin-bottom: 15px;
    }

    .booking-btn {
        border-radius: 25px;
    }

</style>
<?php

$messages = [];
$user = Auth::user();

// Check for Admin
if ($user->is_admin == "1") {



    foreach ($users as $u) {
        if ($u->is_activated == 0 && $u->status == 1) {
            $userDoc = $u->documents;


            // Admin Verification Alerts
            if ($u->licence_admin_verification_required == '1' && $userDoc?->licence_verified == "0" && !empty($userDoc?->licence_file)) {
                $messages[] = "📝 <strong>UK Licence</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
            }

            if ($u->licence_admin_verification_required == '1' && $userDoc?->licence_verified_2 == "0" && !empty($userDoc?->licence_file_2)) {
                $messages[] = "📝 <strong>EASA Licence</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
            }

            if ($u->passport_admin_verification_required == '1' && $userDoc?->passport_verified == "0" && !empty($userDoc?->passport_file)) {
                $messages[] = "📝 <strong>Passport</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
            }

            if ($u->medical_adminRequired == '1' && $userDoc?->medical_verified == "0" && !empty($userDoc?->medical_file)) {
                $messages[] = "📝 <strong>UK Medical</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
            }

            if ($u->medical_adminRequired == '1' && $userDoc?->medical_verified_2 == "0" && !empty($userDoc?->medical_file_2)) {
                $messages[] = "📝 <strong>EASA Medical</strong> verification required for <strong>{$u->fname} {$u->lname}</strong>.";
            }

            // Expiry Alerts
            $expiryStatuses = [
                'UK Licence' => $userDoc?->licence_status,
                'EASA Licence' => $userDoc?->licence_2_status,
                'Passport' => $userDoc?->passport_status,
                'UK Medical' => $userDoc?->medical_status,
                'EASA Medical' => $userDoc?->medical_2_status,
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
                if ($userRating->linked_to == "licence_1") {
                    $linked_to = "UK";
                }
                if ($userRating->linked_to == "licence_2") {
                    $linked_to = "EASA";
                }

                $ratingName = $linked_to . ' Rating ' . ($userRating->parentRating?->name ?? '');

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
}

// For Regular Users
if ($user->is_admin != "1" && !empty($user->ou_id)) {
    $userDoc = $user->documents;


    if ($user->licence_admin_verification_required == '1' && $userDoc?->licence_verified == "0" && !empty($userDoc?->licence_file)) {
        $messages[] = "📝 Your <strong>UK Licence</strong> is pending admin verification.";
    }

    if ($user->licence_admin_verification_required == '1' && $userDoc?->licence_verified_2 == "0" && !empty($userDoc?->licence_file_2)) {
        $messages[] = "📝 Your <strong>EASA Licence</strong> is pending admin verification.";
    }

    if ($user->passport_admin_verification_required == '1' && $userDoc?->passport_verified == "0" && !empty($userDoc?->passport_file)) {
        $messages[] = "📝 Your <strong>Passport</strong> is pending admin verification.";
    }

    if ($user->medical_adminRequired == '1' && $userDoc?->medical_verified == "0" && !empty($userDoc?->medical_file)) {
        $messages[] = "📝 Your <strong>UK Medical</strong> is pending admin verification.";
    }

    if ($user->medical_adminRequired == '1' && $userDoc?->medical_verified_2 == "0" && !empty($userDoc?->medical_file_2)) {
        $messages[] = "📝 Your <strong>EASA Medical</strong> is pending admin verification.";
    }

    $expiryStatuses = [
        'UK Licence' => $userDoc?->licence_status,
        'EASA Licence' => $userDoc?->licence_2_status,
        'Passport' => $userDoc?->passport_status,
        'UK Medical' => $userDoc?->medical_status,
        'EASA Medical' => $userDoc?->medical_2_status,
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

        $ratingName = $userRating->parentRating->name ?? '';

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
?>


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
    <table class="table table-bordered table-striped dashboard_doc_tab" id="document_table">
        <thead>
            <tr>
                <th>Name</th>
                <th>UK Licence Status</th>
                <th>Associated Ratings (UK)</th>
                <th>EASA Licence Status</th>
                <th>Associated Ratings (EASA)</th>
                <th>UK Medical Status</th>
                <th>EASA Medical Status</th>
                <th>Passport Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @php

            if (!function_exists('getTooltip')) {
            function getTooltip($status, $type) {
            return match ($status) {
            'Red' => "This {$type} has expired.",
            'Yellow' => "This {$type} will expire soon.",
            'Green' => "This {$type} is valid.",
            'Non-Expiring' => "This {$type} does not expire.",
            default => "Status unknown.",
            };
            }
            }


            @endphp

            @foreach($users as $user)
            @if ($user->is_activated == 0 && $user->status == 1) 
            <tr>
                <td>{{ $user->fname }} {{ $user->lname }}</td>

                @php
                $doc = $user->documents;
                $ratingsByLicence = $user->usrRatings->groupBy('linked_to');

                @endphp

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
                    $tooltip = getTooltip($status, 'UK License');
                    @endphp
                    <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                    @else
                    <span class="text-muted">Not Uploaded</span>
                    @endif

                    {{-- Licence 1 Ratings --}}
                    @if(isset($user->ratings_by_license['license_1']) && $user->ratings_by_license['license_1']->count())
                    <div class="mt-2">
                        @foreach($user->ratings_by_license['license_1'] as $ur)
                        @php
                        $r = $ur->rating;
                        $expiry = $ur->expiry_date ? \Carbon\Carbon::parse($ur->expiry_date)->format('d/m/Y') : 'N/A';
                        $status = $ur->expiry_status; // Uses accessor from model
                        $color = match($status) {
                        'Red' => 'danger',
                        'Orange' => 'warning',
                        'Amber' => 'info',
                        'Blue' => 'primary',
                        default => 'secondary'
                        };
                        $tooltip = "$r->name expires on $expiry";
                        @endphp

                        <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">
                            {{ $r->name ?? '' }}
                        </span>

                        {{-- Nested (child) ratings --}}
                        @if($r->children && $r->children->count())
                        @foreach($r->children as $child)
                        <span class="badge bg-light text-dark border ms-1" data-bs-toggle="tooltip" title="Child of {{ $r->name }} (inherits expiry)">
                            → {{ $child->name ?? 'N/A' }}
                        </span>
                        @endforeach
                        @endif
                        @endforeach
                    </div>
                    @endif
                </td>

                <!-- <td>
                    <?php
                    $groupedEASA = [];
                    if (isset($ratingsByLicence['licence_1'])) {
                        //   print_r($ratingsByLicence['licence_1']);
                        foreach ($ratingsByLicence['licence_1'] as $ratings) {
                            $child_id = $ratings->rating_id;
                            $parent_id = $ratings->parent_id;
                            $expiry_date = $ratings->expiry_date;

                            if ($parent_id === null && $ratings->rating) {

                                $groupedEASA[$child_id] = [
                                    'parent' => $ratings->rating->name,
                                    'children' => [],
                                    'parent_expiry' => $expiry_date,
                                ];
                            } elseif ($ratings->rating) {
                                $parentRating = $ratings->parentRating;
                                $childRating = $ratings->rating;

                                if (!isset($groupedEASA[$parent_id])) {

                                    $groupedEASA[$parent_id] = [
                                        'parent' => $parentRating?->name ?? '',
                                        'children' => [],
                                        'parent_expiry' => $expiry_date,
                                    ];
                                }

                                $groupedEASA[$parent_id]['children'][] = $childRating->name;
                            } else {
                                $parentRating = $ratings->parentRating;
                                $groupedEASA[$parent_id] = [
                                    'parent' => $parentRating?->name ?? '',
                                    'children' => [],
                                    'parent_expiry' => $expiry_date,
                                ];
                            }
                        }
                    }
                    ?>

                    @foreach ($groupedEASA as $entry)
                    <strong>{{ $entry['parent'] }}</strong><br>
                    @if (!empty($entry['children']))
                    <ul style="margin-left: 15px;">
                        @foreach ($entry['children'] as $child)
                        <li>{{ $child }}</li>
                        @endforeach
                    </ul>
                    @endif
                    <br>
                    @endforeach
                </td> -->
                <td class="lic_rating_td">
                    <?php
                    $groupedEASA = [];
                    if (isset($ratingsByLicence['licence_1'])) {
                        foreach ($ratingsByLicence['licence_1'] as $ratings) {
                            $child_id = $ratings->rating_id;
                            $parent_id = $ratings->parent_id;
                            $expiry_date = $ratings->expiry_date;

                            if ($parent_id === null && $ratings->rating) {
                                $groupedEASA[$child_id] = [
                                    'parent' => $ratings->rating,
                                    'children' => [],
                                    'parent_expiry' => $expiry_date,
                                ];
                            } elseif ($ratings->rating) {
                                $parentRating = $ratings->parentRating;
                                $childRating = $ratings->rating;

                                if (!isset($groupedEASA[$parent_id])) {
                                    $groupedEASA[$parent_id] = [
                                        'parent' => $parentRating,
                                        'children' => [],
                                        'parent_expiry' => $expiry_date,
                                    ];
                                }
                                $groupedEASA[$parent_id]['children'][] = $childRating;
                            } else {
                                $parentRating = $ratings->parentRating;
                                $groupedEASA[$parent_id] = [
                                    'parent' => $parentRating,
                                    'children' => [],
                                    'parent_expiry' => $expiry_date,
                                ];
                            }
                        }
                    }

                    // -------------------------
                    // Sorting logic (same as controller)
                    // -------------------------
                    $getPriority = function ($rating) {
                        if (!$rating) return 999;

                        $r = $rating;

                        if (($r->is_fixed_wing || $r->is_rotary) && !$r->is_instructor && !$r->is_examiner) {
                            return 1;
                        }
                        if ($r->is_instructor) {
                            return 2;
                        }
                        if ($r->is_examiner) {
                            return 3;
                        }
                        return 999;
                    };

                    // Sort parent ratings
                    uasort($groupedEASA, function ($a, $b) use ($getPriority) {
                        $prioA = $getPriority($a['parent'] ?? null);
                        $prioB = $getPriority($b['parent'] ?? null);

                        if ($prioA !== $prioB) {
                            return $prioA <=> $prioB;
                        }

                        $nameA = strtolower($a['parent']->name ?? '');
                        $nameB = strtolower($b['parent']->name ?? '');
                        return $nameA <=> $nameB;
                    });

                    // Sort children under each parent
                    foreach ($groupedEASA as &$entry) {
                        if (!empty($entry['children'])) {
                            usort($entry['children'], function ($a, $b) use ($getPriority) {
                                $prioA = $getPriority($a ?? null);
                                $prioB = $getPriority($b ?? null);

                                if ($prioA !== $prioB) {
                                    return $prioA <=> $prioB;
                                }

                                $nameA = strtolower($a->name ?? '');
                                $nameB = strtolower($b->name ?? '');
                                return $nameA <=> $nameB;
                            });
                        }
                    }
                    unset($entry);
                    ?>

                    @foreach ($groupedEASA as $entry)
                        @if (!empty($entry['children']))
                            <?php
                                $expirty_date = $entry['parent_expiry'];
                                $color = getExpiryStatus($expirty_date);

                                if (is_null($expirty_date)) {
                                    $color = "#198754";
                                    $tooltip = "This rating does not expire";
                                } elseif ($color == "Red") {
                                    $color = "#dc3545";
                                    $tooltip = "This rating has expired";
                                } elseif ($color == "Yellow") {
                                    $color = "#ffc107";
                                    $tooltip = "This rating will expire soon";
                                } else {
                                    $color = "#198754";
                                    $tooltip = "This rating is valid until " . date('d/m/Y', strtotime($expirty_date));
                                }
                            ?>

                            <div class="collapsible">
                                <span class="badge" style="background-color:{{ $color }}" data-bs-toggle="tooltip" data-bs-original-title="{{ $tooltip }}" aria-describedby="tooltip281406">{{ $entry['parent']->name }}</span>
                            </div>

                            <div class="content">
                                <ul>
                                    @foreach ($entry['children'] as $child)
                                    <li>{{ $child->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <?php
                                $expirty_date = $entry['parent_expiry'];
                                $color = getExpiryStatus($expirty_date);

                                if ($color == "Red") {
                                    $color = "#dc3545";
                                    $tooltip = "This rating has expired";
                                } elseif ($color == "Yellow") {
                                    $color = "#ffc107";
                                    $tooltip = "This rating will expire soon";
                                } else {
                                    $color =  "#198754";
                                    $tooltip = "This rating does not expire";
                                }
                            ?>
                            <div class="parent_rate">
                                <span class="badge" style="background-color:{{ $color }}" data-bs-toggle="tooltip" data-bs-original-title="{{ $tooltip }}" aria-describedby="tooltip281406">{{ $entry['parent']->name }}</span>
                            </div>
                        @endif
                    @endforeach
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
                    $tooltip = getTooltip($status, 'EASA Licence');
                    @endphp
                    <span class="badge bg-{{ $color }}" data-bs-toggle="tooltip" title="{{ $tooltip }}">{{ $date }}</span>
                    @else
                    <span class="text-muted">Not Uploaded</span>
                    @endif
                </td>

                {{-- Associated Ratings (Licence 2) --}}
            
                <td class="lic_rating_td">
                    @php
                    $groupedEASA = [];

                    if (isset($ratingsByLicence['licence_2'])) {
                    foreach ($ratingsByLicence['licence_2'] as $ratings) {
                    $child_id = $ratings->rating_id;
                    $parent_id = $ratings->parent_id;
                    $expiry_date = $ratings->expiry_date;

                    if ($parent_id === null && $ratings->rating) {
                    $groupedEASA[$child_id] = [
                    'parent' => $ratings->rating,
                    'children' => [],
                    'parent_expiry' => $expiry_date,
                    ];
                    } elseif ($ratings->rating) {
                    $parentRating = $ratings->parentRating;
                    $childRating = $ratings->rating;

                    if (!isset($groupedEASA[$parent_id])) {
                    $groupedEASA[$parent_id] = [
                    'parent' => $parentRating,
                    'children' => [],
                    'parent_expiry' => $expiry_date,
                    ];
                    }

                    $groupedEASA[$parent_id]['children'][] = $childRating;
                    } else {
                    $parentRating = $ratings->parentRating;
                    $groupedEASA[$parent_id] = [
                    'parent' => $parentRating,
                    'children' => [],
                    'parent_expiry' => $expiry_date,
                    ];
                    }
                    }
                    }

                    // -------------------------
                    // Sorting logic (same as controller)
                    // -------------------------
                    $getPriority = function ($rating) {
                    if (!$rating) return 999;

                    $r = $rating;

                    if (($r->is_fixed_wing || $r->is_rotary) && !$r->is_instructor && !$r->is_examiner) {
                    return 1;
                    }
                    if ($r->is_instructor) {
                    return 2;
                    }
                    if ($r->is_examiner) {
                    return 3;
                    }
                    return 999;
                    };

                    // Sort parent ratings
                    uasort($groupedEASA, function ($a, $b) use ($getPriority) {
                    $prioA = $getPriority($a['parent'] ?? null);
                    $prioB = $getPriority($b['parent'] ?? null);

                    if ($prioA !== $prioB) {
                    return $prioA <=> $prioB;
                        }

                        $nameA = strtolower($a['parent']->name ?? '');
                        $nameB = strtolower($b['parent']->name ?? '');
                        return $nameA <=> $nameB;
                            });

                            // Sort children under each parent
                            foreach ($groupedEASA as &$entry) {
                            if (!empty($entry['children'])) {
                            usort($entry['children'], function ($a, $b) use ($getPriority) {
                            $prioA = $getPriority($a ?? null);
                            $prioB = $getPriority($b ?? null);

                            if ($prioA !== $prioB) {
                            return $prioA <=> $prioB;
                                }

                                $nameA = strtolower($a->name ?? '');
                                $nameB = strtolower($b->name ?? '');
                                return $nameA <=> $nameB;
                                    });
                                    }
                                    }
                                    unset($entry);
                                    @endphp

                                    @foreach ($groupedEASA as $entry)
                                    @if (!empty($entry['children']))
                                        <?php
                                        $expirty_date = $entry['parent_expiry'];
                                        $color = getExpiryStatus($expirty_date);

                                        if ($color == "Red") {
                                            $color = "#dc3545";
                                            $tooltip = "This rating has expired";
                                        } elseif ($color == "Yellow") {
                                            $color = "#ffc107";
                                            $tooltip = "This rating will expire soon";
                                        } elseif ($color == "Green") {
                                            $color =  "#198754";
                                            $tooltip = "This rating does not expire";
                                        }
                                        ?>
                                        <div class="collapsible">
                                            <span class="badge" style="background-color:{{ $color }}" data-bs-toggle="tooltip" data-bs-original-title="{{ $tooltip }}" aria-describedby="tooltip281406">{{ $entry['parent']->name }}</span>
                                        </div>
                                    <div class="content">
                                        <ul>
                                            @foreach ($entry['children'] as $child)
                                            <li>{{ $child->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @else
                                    <?php
                                            $expirty_date = $entry['parent_expiry'];
                                            $color = getExpiryStatus($expirty_date);

                                            if ($color == "Red") {
                                                $color = "#dc3545";
                                                $tooltip = "This rating has expired";
                                            } elseif ($color == "Yellow") {
                                                $color = "#ffc107";
                                                $tooltip = "This rating will expire soon";
                                            } else {
                                                $color =  "#198754";
                                                $tooltip = "This rating does not expire";
                                            }
                                    ?>
                                            <div class="parent_rate">
                                                <span class="badge" style="background-color:{{ $color }}" data-bs-toggle="tooltip" data-bs-original-title="{{ $tooltip }}" aria-describedby="tooltip281406">{{ $entry['parent']->name }}</span>
                                            </div>
                                    @endif
                                    @endforeach
                </td>







                {{-- Medical 1 --}}
                <td>
                    @if($doc && $doc->medical_file_uploaded)
                    @php
                    $status = $doc->medical_status;
                    $color = $status === 'Red' ? 'danger' : ($status === 'Yellow' ? 'warning' : 'success');
                    $date = $doc->medical_expirydate ? date('d/m/Y', strtotime($doc->medical_expirydate)) : 'N/A';
                    $tooltip = getTooltip($status, 'UK Medical Status');
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
                    $tooltip = getTooltip($status, 'EASA Medical Status');
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
            @endif
            @endforeach
        </tbody>
    </table>
@endif
    <section class="section dashboard mt-3">
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
                                            data: [{{ $readDocuments }}, {{ $unreadDocuments }}],
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
                                                        let total = {{ $totalDocuments }};
                                                        let percentage = ((value / total) * 100).toFixed(2);
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
                                            data: [{{ $readDocuments }}, {{ $unreadDocuments }}],
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
                                                        let total = {{ $totalDocuments }};
                                                        let percentage = ((value / total) * 100).toFixed(2);
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

                 <!-- Quiz  Card -->
                <div class="col-xxl-3 col-md-6">
                    <a href="{{ route('quiz.index') }}" class="text-decoration-none">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Quiz</h5>

                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="ri-briefcase-2-fill dashboard_icon"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $quizscount }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- End Quiz  Card -->
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
                                                data: [{{ $readDocuments }}, {{ $unreadDocuments }}],
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
                                                            let total = {{ $totalDocuments }};
                                                            let percentage = ((value / total) * 100).toFixed(2);
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
            
            <!-- Last Training Event -->
            <!-- <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Last Training Event</h5>
                            <table class="table table-hover" id="trainingEventTable">
                                <thead>
                                    <tr>
                                        <th scope="col">Event</th>
                                        <th scope="col">Student</th>
                                        <th scope="col">Instructor</th>
                                        <th scope="col">Resource</th>
                                        <th scope="col">Event Date</th>
                                        <th scope="col">Start Time</th>
                                        <th scope="col">End Time</th>
                                        @if(checkAllowedModule('training','training.show')->isNotEmpty() || checkAllowedModule('training','training.delete')->isNotEmpty() || checkAllowedModule('training','training.delete')->isNotEmpty() || checkAllowedModule('training','training.grading-list')->isNotEmpty())
                                        <th scope="col">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trainingEvents as $event)

                                    @php
                                    $lesson = $event->firstLesson;
                                    @endphp

                                    <tr>
                                        <td class="eventName">{{ $event->course?->course_name }} </td>
                                        <td>{{ $event->student?->fname }} {{ $event->student?->lname }}</td>
                                        <td>{{ $lesson?->instructor?->fname }} {{ $lesson?->instructor?->lname }}</td>
                                        <td>{{ $lesson?->resource?->name }}</td>
                                        <td>{{ $lesson?->lesson_date ? date('d-m-y', strtotime($lesson->lesson_date)) : '' }}</td>
                                        <td>{{ $lesson?->start_time ? date('h:i A', strtotime($lesson->start_time)) : '' }}</td>
                                        <td>{{ $lesson?->end_time ? date('h:i A', strtotime($lesson->end_time)) : '' }}</td>
                                        <td>
                                            @if(get_user_role(auth()->user()->role) == 'administrator')
                                            @if(empty($event->is_locked))


                                            @if(checkAllowedModule('training','training.delete')->isNotEmpty())
                                            <i class="fa-solid fa-trash delete-event-icon me-2" style="font-size:25px; cursor: pointer;"
                                                data-event-id="{{ encode_id($event->id) }}"></i>
                                            @endif
                                            @if(checkAllowedModule('training','training.show')->isNotEmpty())
                                            <a href="{{ route('training.show', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Training Event" style="font-size:18px; cursor: pointer;">
                                                <i class="fa fa-eye text-danger me-2"></i>
                                            </a>
                                            @endif

                                            @if($event->can_end_course)
                                            {{-- Active “End Course” button/icon --}}
                                            <button class="btn btn-sm btn-flag-checkered end-course-btn" data-event-id="{{ encode_id($event->id) }}"
                                                title="End Course/Event">
                                                <i class="fa fa-flag-checkered text-primary"></i>
                                            </button>
                                            @endif
                                            @else
                                            {{-- This event is already locked/ended --}}
                                            <span class="badge bg-secondary unlocked" data-bs-toggle="tooltip" data-id="{{ $event->id}}"
                                                title="This course has been ended and is locked from editing">
                                                <i class="bi bi-lock-fill me-1"></i>Ended
                                            </span>
                                            @if(checkAllowedModule('training','training.delete')->isNotEmpty())
                                            <i class="fa-solid fa-trash delete-event-icon me-2" style="font-size:25px; cursor: pointer;"
                                                data-event-id="{{ encode_id($event->id) }}"></i>
                                            @endif
                                            @endif
                                            @elseif(get_user_role(auth()->user()->role) == 'instructor')
                                            @if(empty($event->is_locked))


                                            @if(checkAllowedModule('training','training.show')->isNotEmpty())
                                            <a href="{{ route('training.show', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Training Event" style="font-size:18px; cursor: pointer;">
                                                <i class="fa fa-eye text-danger me-2"></i>
                                            </a>
                                            @endif
                                            @else
                                            {{-- This event is already locked/ended --}}
                                            <span class="badge bg-secondary" data-bs-toggle="tooltip"
                                                title="This course has been ended and is locked from editing">
                                                <i class="bi bi-lock-fill me-1"></i>Ended
                                            </span>
                                            @endif
                                            @else
                                            @if(checkAllowedModule('training','training.grading-list')->isNotEmpty())
                                            <a href="{{ route('training.grading-list', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Grading" style="font-size:18px; cursor: pointer;">
                                                <i class="fa fa-list text-danger me-2"></i>
                                            </a>
                                            @endif
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Pending Items -->
            <!-- <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"> Pending Items</h5>
                            <table class="table table-hover" id="pendingquizTable">
                                <thead>
                                    <tr>
                                        <th scope="col">Title</th>
                                        <th scope="col">Course</th>
                                        <th scope="col">Lesson</th>
                                        <th scope="col">Duration</th>
                                        <th scope="col">Passing Score</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quizs as $quiz)
                                        <tr>
                                            <td class="quizTitle">{{ $quiz->title }}</td>
                                            <td>{{ $quiz->course->course_name ?? 'N/A' }}</td>
                                            <td>{{ $quiz->lesson->lesson_title ?? 'N/A' }}</td>
                                            <td>{{ $quiz->duration }} mins</td>
                                            <td>{{ $quiz->passing_score }}%</td>
                                            <td>{{ ucfirst($quiz->quiz_type) }}</td>
                                            @if(get_user_role(auth()->user()->role) == 'administrator')  
                                                <td>
                                                    <label class="switch">
                                                        <input type="checkbox" 
                                                            class="switch-input toggle-status" 
                                                            data-id="{{ $quiz->id }}"
                                                            {{ $quiz->status == 'published' ? 'checked' : '' }}>
                                                        <div class="switch-button">
                                                            <span class="switch-button-left">Draft</span>
                                                            <span class="switch-button-right">Published</span>
                                                        </div>
                                                    </label>
                                                </td>
                                            @else
                                                <td>{{ ucfirst($quiz->status) }}</td>
                                            @endif
                                            <td>
                                                @if(checkAllowedModule('quiz','quiz.view')->isNotEmpty())
                                                    <i class="fa fa-eye action-btn" style="font-size:25px; cursor: pointer;" onclick="window.location.href='{{ route('quiz.view', ['id' => encode_id($quiz->id)]) }}'"></i>
                                                @endif
                                                
                                                @if(checkAllowedModule('quiz','quiz.edit')->isNotEmpty())
                                                    <i class="fa fa-edit edit-quiz-icon action-btn" style="font-size:25px; cursor: pointer;" data-quiz-id="{{ encode_id($quiz->id) }}"></i>
                                                @endif

                                                @if(checkAllowedModule('quiz','quiz.destroy')->isNotEmpty())
                                                    <i class="fa-solid fa-trash delete-quiz-icon action-btn" style="font-size:25px; cursor: pointer;" data-quiz-id="{{ encode_id($quiz->id) }}" data-quiz-name="{{ $quiz->title }}"></i>
                                                @endif
                                                
                                                @if(checkAllowedModule('quiz','quiz.start')->isNotEmpty())
                                                    @if(auth()->user()->role == 3)
                                                        @if($quiz->topics->isNotEmpty())
                                                            @if($quiz->quizAttempts->contains('student_id', auth()->user()->id))
                                                                <button class="start-quiz-btn action-btn view-result-icon btn btn-primary" style="cursor: pointer; color: white;" 
                                                                    data-quiz-id="{{ encode_id($quiz->id) }}" data-quiz-name="{{ $quiz->title }}"> View
                                                                </button>
                                                            @else
                                                                <button class="start-quiz-btn action-btn start-quiz-icon" style="cursor: pointer; background: #198754; color: white; border-radius: .25rem; border: none;" 
                                                                    data-quiz-id="{{ encode_id($quiz->id) }}" data-quiz-name="{{ $quiz->title }}" data-duration="{{ $quiz->duration }}"> Start Quiz
                                                                </button>
                                                            @endif
                                                        @else
                                                            <span class="text-danger">You can't started yet</span>
                                                        @endif
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- merge table  -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"> Pending Items</h5>
                            <table class="table table-hover" id="pendingitemTable">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Course</th>
                                        <th>Lesson</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outstandingItems as $item)
                                        <tr>
                                            <td>{{ $item['title'] }}</td>
                                            <td>{{ $item['course'] }}</td>
                                            <td>{{ $item['lesson'] }}</td>
                                            <td>{{ ucfirst(str_replace('_',' ', $item['type'])) }}</td>
                                            <td>{{ $item['status'] }}</td>
                                            <td>
                                            @if($item['type'] == 'TrainingEvents')
                                                @if(get_user_role(auth()->user()->role) == 'instructor')
                                                    @if(empty($event->is_locked))
                                                        @if(checkAllowedModule('training','training.show')->isNotEmpty())
                                                            <a href="{{ route('training.show', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Training Event" style="font-size:18px; cursor: pointer;">
                                                                <i class="fa fa-eye text-danger me-2"></i>
                                                            </a>
                                                        @endif
                                                    @else
                                                        {{-- This event is already locked/ended --}}
                                                        <span class="badge bg-secondary" data-bs-toggle="tooltip"
                                                            title="This course has been ended and is locked from editing">
                                                            <i class="bi bi-lock-fill me-1"></i>Ended
                                                        </span>
                                                    @endif
        
                                                @else
        
                                                    @if(checkAllowedModule('training','training.grading-list')->isNotEmpty())
                                                        <a href="{{ route('training.grading-list', ['event_id' => encode_id($event->id)]) }}" class="view-icon" title="View Grading" style="font-size:18px; cursor: pointer;">
                                                            <i class="fa fa-list text-danger me-2"></i>
                                                        </a>
                                                    @endif
        
                                                @endif
                                            @else
                                                <a href="{{ $item['action_url'] }}" class="btn btn-primary btn-sm">
                                                    {{ $item['action_text'] }}
                                                </a>
                                            @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if(auth()->user()->role == 18)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"> Pending Bookings</h5>
                                <table class="table table-hover" id="pendingbookingTable">
                                    <thead>
                                        <tr>
                                            <th scope="col">OU Unit</th>
                                            <th scope="col">Student</th>
                                            <th scope="col">Resource</th>
                                            <th scope="col">Start</th>
                                            <th scope="col">End</th>
                                            <th scope="col">Booking Type</th>
                                            <th scope="col">Resource Type</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookings as $booking)
                                            <tr>
                                                <td class="quizTitle">{{ $booking->organizationUnit->org_unit_name }}</td>
                                                <td>{{ $booking->users->fname. " " . $booking->users->lname ?? 'N/A' }}</td>
                                                <td>{{ $booking->resources->name ?? 'N/A' }}</td>
                                                <td>{{ $booking->start }}</td>
                                                <td>{{ $booking->end }}</td>
                                                <td>
                                                    @if($booking->booking_type == 1)
                                                        Solo
                                                    @elseif($booking->booking_type == 2)
                                                        Lesson
                                                    @elseif($booking->booking_type == 3)
                                                        Standby
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($booking->resource_type == 1)
                                                        Plane
                                                    @elseif($booking->resource_type == 2)
                                                        Simulator
                                                    @elseif($booking->resource_type == 3)
                                                        Classroom
                                                    @endif
                                                </td>
                                                <td>{{ $booking->status }}</td>
                                                <td>
                                                    @if(auth()->user()->role == 18)
                                                        <button class="btn btn-success booking-btn approve-btn"
                                                            data-id="{{ $booking->id }}">
                                                            Approve
                                                        </button>

                                                        <button class="btn btn-danger booking-btn reject-btn"
                                                            data-id="{{ $booking->id }}">
                                                            Reject
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <div class="loader" style="display: none;"></div>

    </section>
@endsection

@section('js_scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#document_table').DataTable({
                searching: true,
                pageLength: 10,
                language: {
                    emptyTable: "No records found"
                }
            });

            // $('#trainingEventTable').DataTable({
            //     searching: true,
            //     pageLength: 10,
            //     language: {
            //         emptyTable: "No previous training events completed"
            //     }
            // });

            // $('#pendingquizTable').DataTable({
            //     searching: true,
            //     pageLength: 10,
            //     language: {
            //         emptyTable: "No pending quizzes"
            //     }
            // });

            $('#pendingitemTable').DataTable({
                searching: true,
                pageLength: 10,
                language: {
                    emptyTable: "No outstanding items"
                }
            });

            $('#pendingbookingTable').DataTable({
                searching: true,
                pageLength: 10,
                language: {
                    emptyTable: "No pending bookings"
                }
            });

            $('#document_table').on("click", ".collapsible", function() {
                $(this).toggleClass("active");
                $(this).next(".content").slideToggle();
            });

            $(document).on('click', '.approve-btn', function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Approve Booking?',
                    text: "Are you sure you want to approve this booking?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(".loader").fadeIn();

                        $.post("{{ url('/booking/approve') }}", {
                            _token: "{{ csrf_token() }}",
                            id: id
                        })
                        .done(function () {
                            // Swal.fire('Approved!', 'Booking has been approved.', 'success');
                            location.reload();
                        })
                        .fail(function () {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        })
                        .always(function () {
                            $(".loader").fadeOut('slow');
                        });
                    }
                });
            });

            $(document).on('click', '.reject-btn', function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Reject Booking?',
                    text: "This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(".loader").fadeIn();

                        $.post("{{ url('/booking/reject') }}", {
                            _token: "{{ csrf_token() }}",
                            id: id
                        })
                        .done(function () {
                            // Swal.fire('Rejected!', 'Booking has been rejected.', 'success');
                            location.reload();
                        })
                        .fail(function () {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        })
                        .always(function () {
                            $(".loader").fadeOut('slow');
                        });
                    }
                });
            });


        });

        setTimeout(function() {
            $('#successMessage').fadeOut('fast');
        }, 3000);

        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection