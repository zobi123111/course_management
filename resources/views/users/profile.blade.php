@section('title', 'Users')
@section('sub-title', 'Users')
@extends('layout.app')
@section('content')

<style>
    .rating-stars {
        display: flex;
        cursor: pointer;
        font-size: 30px;
        color: gray;
    }

    .rating-stars .star {
        padding: 5px;
    }

    .rating-stars .star.active {
        color: gold;
    }

    .star {
        font-size: 30px;
        color: gray;
        /* default color for unfilled stars */
        cursor: pointer;
    }

    .star.rated {
        color: gold;
        /* color for filled stars */
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 50%;
        margin: 0 auto;
        background-color: #fff;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-top: 50px;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        color: #555;
        margin-bottom: 5px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #007bff;
    }

    .btn {
        display: block;
        width: 100%;
        padding: 10px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #218838;
    }

    .add_btn {
        width: 30% !important;
    }

    .card-header {
        font-size: 1.1rem;
        font-weight: bold;
    }

    .card-title {
        margin-bottom: 0.5rem;
    }

    .list-group-item {
        font-size: 1rem;
    }

    .sbt_btn {
        background-color: #0d6efd;
    }

    .close_btn {
        background-color: #0d6efd;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 110px;
        height: 30px;
    }

    .switch-input {
        display: none;
    }

    .switch-button {
        position: absolute;
        cursor: pointer;
        background-color: #dc3545; /* OFF - red */
        border-radius: 30px;
        inset: 0;
        transition: background-color 0.3s ease;
        overflow: hidden;
    }

    .switch-button-left,
    .switch-button-right {
        position: absolute;
        width: 70%;
        text-align: center;
        line-height: 30px;
        font-size: 11px;
        font-weight: bold;
        color: #fff;
        transition: all 0.3s ease;
    }

    /* OFF text */
    .switch-button-left {
        left: 30px;
    }

    /* ON text (hidden initially) */
    .switch-button-right {
        right: 30px;
        transform: translateX(100%);
        opacity: 0;
    }

    /* Knob */
    .switch-button::before {
        content: "";
        position: absolute;
        height: 26px;
        width: 26px;
        left: 2px;
        top: 2px;
        background-color: #fff;
        border-radius: 50%;
        transition: transform 0.3s ease;
    }

    /* ON state */
    .switch-input:checked + .switch-button {
        background-color: #28a745; /* green */
    }

    .switch-input:checked + .switch-button::before {
        transform: translateX(78px);
    }

    .switch-input:checked + .switch-button .switch-button-left {
        transform: translateX(-100%);
        opacity: 0;
    }

    .switch-input:checked + .switch-button .switch-button-right {
        transform: translateX(0);
        opacity: 1;
    }

    .email-switch {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: 15px;
    }

    .switch-text {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
    }
</style>

<div class="main_cont_outer">
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif
    <div id="update_success_msg"></div>


    <div class="card pt-4">
        <div class="card-body">
            <div class="container-fluid">
                <div class="row">
                    <h2 class="mb-5">User Profile</h2>
                    <div class="change_password d-flex justify-content-end">
                        <a href="{{ url('forgot-password') }}" class="btn btn-primary me-2 change-password-btn" style="width:unset;background-color: #0d6efd;">Forget Password</a>
                    </div>

                </div>
                <form id="userProfileForm" enctype="multipart/form-data">
                    @csrf
                    <div class="loader" style="display:none;"></div>
                    <div class="row">
                        <!-- First Name -->
                        <div class="form-group col-sm-6">
                            <label for="firstName"><strong>First Name</strong></label>
                            <input type="hidden" id="id" name="id" class="form-control" value="{{ $user->id }}">
                            <input type="text" id="firstName" name="firstName" class="form-control"
                                value="{{ $user->fname }}">
                        </div>

                        <!-- Last Name -->
                        <div class="form-group col-sm-6">
                            <label for="lastName"><strong>Last Name</strong></label>
                            <input type="text" id="lastName" name="lastName" class="form-control"
                                value="{{ $user->lname }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        @if($OuSetting && $OuSetting->show_dob == 1)
                            <div class="col-md-6 create_date_of_birth_div">
                                <label  class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" value="{{ $user->date_of_birth }}"id="create_date_of_birth">
                            </div>
                        @endif
                        
                        @if($OuSetting && $OuSetting->show_phone == 1)
                            <div class="col-md-6 create_phone_number_div">
                                <label  class="form-label">Phone Number</label>
                                <input type="number" class="form-control" name="phone_number" value="{{ $user->phone_number }}" id="create_phone_number">
                            </div>
                        @endif
                    </div>

                    <div class="row">

                        <!-- Email -->
                        <div class="form-group col-sm-6">
                            <label for="email"><strong>Email</strong></label>
                            <input type="email" id="email" name="email" class="form-control"
                                value="{{ $user->email }}">
                        </div>

                        <!-- Profile Image Upload -->
                        <div class="form-group col-sm-6">
                            <label for="profile_image"><strong>Profile Image</strong></label>
                            <input type="file" id="profile_image" name="profile_image" class="form-control">
                            @if($user->image)
                            <a href="{{ asset('storage/' . $user->image) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2" style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                View Image
                            </a>
                            @endif
                        </div>

                    </div>

                    <!-- Currency -->
                    @if ($user->currency_required == 1)
                    <div class="row mb-3">
                        <div class="form-group col-sm-6">
                            <label for="currency" class="form-label"><strong>Currency <span
                                        class="text-danger">*</span> </strong></label>
                            <input type="text" name="currency" id="currency"
                                value="{{ $user->currency ? $user->currency : ''}}" class="form-control"
                                placeholder="Enter Currency">
                            <div id="currency_error_up" class="text-danger error_e"></div>
                        </div>
                    </div>
                    @endif
                    <div class="row mb-3">
                        <!-- Rating -->
                        {{-- @if ($user->rating_required == 0)
                                <div class="form-group col-sm-6">
                                    <label for="rating_checkbox" class="form-label">Rating/s</label>
                                    <div id="ratings">
                                        <div id="ratingStars" class="rating-stars">
                                            <span class="star" data-value="1">&#9733;</span>
                                            <span class="star" data-value="2">&#9733;</span>
                                            <span class="star" data-value="3">&#9733;</span>
                                            <span class="star" data-value="4">&#9733;</span>
                                            <span class="star" data-value="5">&#9733;</span>
                                        </div>
                                        <input type="hidden" name="rating" id="rating_value" value="">
                                        <div id="rating_error" class="text-danger error_e"></div>
                                    </div>
                                </div>
                            @endif --}}

                        @php
                        $document = $user->documents;
                        @endphp

                        <!-- Licence -->
                        @if ($user->licence_required == 1)
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-lg-6 col-md-12" id="first_licence_section">
                                    {{-- Licence Fields --}}
                                    <label for="licence_checkbox" class="form-label">
                                        <strong>UK Licence <span class="text-danger">*</span>
                                            @if($document?->licence_invalidate == 1)
                                            <span class="text-danger">(Re-upload a new document and date.)</span>
                                            @endif
                                        </strong>
                                        @if ($document && $document?->licence_verified)
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                        @endif
                                    </label>
                                    <input type="text" name="licence" id="licence" value="{{ $document?->licence ?? '' }}" placeholder="Enter UK Licence Number" class="form-control">

                                    <div id="licence_error_up" class="text-danger error_e"> </div>
                                    <label for="licence_expiry_date" class="form-label mt-3">
                                        <strong>
                                            Expiry Date <span class="text-danger">*</span>
                                        </strong>
                                        @if($document?->licence_status == 'Red')
                                        <span class="text-danger">
                                            <i class="bi bi-x-circle-fill"></i> Expired
                                        </span>
                                        @elseif($document?->licence_status == 'Yellow')
                                        <span class="text-warning">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon
                                        </span>
                                        @elseif($document?->licence_status == 'Green')
                                        <span class="text-success">
                                            <i class="bi bi-check-circle-fill"></i> Valid
                                        </span>
                                        @else
                                        <span class="text-secondary">
                                            <i class="bi bi-question-circle-fill"></i> N/A
                                        </span>
                                        @endif
                                    </label>
                                    <input type="date" name="licence_expiry_date" id="licence_expiry_date" value="{{ $document?->licence_expiry_date ?? '' }}" class="form-control mb-2">

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="non_expiring_licence" name="non_expiring_licence" value="1" {{ $document?->licence_non_expiring ? 'checked': '' }}>
                                        <label class="form-check-label" for="non_expiring_licence">
                                            <strong>Non-Expiring Licence</strong>
                                        </label>
                                    </div>

                                    <div id="licence_expiry_date_error_up" class="text-danger error_e"></div>
                                    <input type="file" name="licence_file" id="licence_file" class="form-control mt-3 mb-3" accept=".pdf,.jpg,.jpeg,.png">
                                    <div id="licence_file_error_up" class="text-danger error_e"></div>
                                    <input type="hidden" name="old_licence_file" value="{{ $document?->licence_file ?? '' }}">

                                    @if ($document?->licence_file)
                                    <div class="mt-3">
                                        <a href="{{ asset('storage/' . $document?->licence_file) }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                            style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                            <i class="bi bi-file-earmark-text me-1" style="font-size: 16px;"></i> View
                                            Licence
                                        </a>
                                    </div>
                                    @endif
                                    <div class="row">
                                        {{-- Licence 1 (UK) Section --}}
                                        @if(isset($grouped['licence_1']) && count($grouped['licence_1']))
                                        <!-- <div class="col-lg-6 col-md-12"> -->
                                        <h4 class="mt-2">Ratings linked to UK Licence</h4>
                                        @foreach($grouped['licence_1'] as $parentId => $entry)
                                        @php $i = $loop->index; @endphp
                                        @php
                                        $parent = $entry['parent'];
                                        $children = $entry['children'] ?? [];
                                        $rating = $parent->rating;
                                        $hasValidChildren = collect($children)->contains(function ($child) {
                                        return !is_null($child->rating_id);
                                        });

                                        if (!$hasValidChildren) {
                                        $children = [];
                                        }
                                        @endphp

                                        <div class="card shadow-sm border rounded mb-4">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $rating->name ?? ' ' }}</h5>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label mt-2"><strong>Issue Date</strong></label>

                                                        <input type="hidden" name="issue_date[{{ $i }}][id]" value="{{ $rating->id }}">

                                                        <!-- parentid -->
                                                        <input type="hidden" name="issue_date[{{ $i }}][parentid]" value="{{ $entry['children'][0]['parent_id'] }}">

                                                        <!-- actual date -->
                                                        <input type="date" name="issue_date[{{ $i }}][issue_date]" class="form-control"
                                                            value="{{ $entry['children'][0]['issue_date'] ?? '' }}">
                                                        <!-- // user id  -->
                                                        <input type="hidden" name="issue_date[{{ $i }}][user_id]" class="form-control"
                                                            value="{{ $entry['children'][0]['user_id'] }}">
                                                        <!-- // Linked to -->
                                                        <input type="hidden" name="issue_date[{{ $i }}][linked_to]" class="form-control"
                                                            value="{{ $entry['children'][0]['linked_to'] }}">
                                                    </div>





                                                    <div class="col-md-6">
                                                        <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                                                        <!-- <input type="date" name="expiry_date[{{ $rating->id }}]" class="form-control"
                                                                value="{{ old("expiry_date.$rating->id", $parent->expiry_date) }}"> -->

                                                        <input type="hidden" name="expiry_date[{{ $i }}][id]" value="{{ $rating->id }}">

                                                        <!-- parentid -->
                                                        <input type="hidden" name="expiry_date[{{ $i }}][parentid]" value="{{ $entry['children'][0]['parent_id'] }}">

                                                        <!-- actual date -->
                                                        <input type="date" name="expiry_date[{{ $i }}][expiry_date]" class="form-control"
                                                            value="{{ $entry['children'][0]['expiry_date'] ?? '' }}">
                                                        <!-- // user id  -->
                                                        <input type="hidden" name="expiry_date[{{ $i }}][user_id]" class="form-control"
                                                            value="{{ $entry['children'][0]['user_id'] }}">
                                                        <!-- // Linked to -->
                                                        <input type="hidden" name="expiry_date[{{ $i }}][linked_to]" class="form-control"
                                                            value="{{ $entry['children'][0]['linked_to'] }}">
                                                    </div>
                                                </div>
                                                <label class="form-label mt-3"><strong>Upload File</strong></label>

                                                <input type="hidden" name="rating_file[{{ $i }}][parentid]" value="{{ $entry['children'][0]['parent_id'] }}" class="form-control">
                                                <input type="hidden" name="rating_file[{{ $i }}][user_id]" value="{{ $entry['children'][0]['user_id'] ?? '' }}" class="form-control">

                                                <input type="hidden" name="rating_file[{{ $i }}][linked_to]" value="{{ $entry['children'][0]['linked_to'] ?? '' }}" class="form-control">

                                                <input type="file" name="rating_file[{{ $i }}][upload]" class="form-control">
                                                <div class="licence1_file">
                                                    @if(!empty($entry['children'][0]['file_path']))
                                                    <a href="{{ asset('storage/' . $entry['children'][0]['file_path']) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary" style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                                        <i class="bi bi-file-earmark-text me-1"></i> View File
                                                    </a>
                                                    @endif
                                                </div>

                                                @if($parent->file_path)
                                                <a href="{{ asset('storage/' . $parent->file_path) }}" target="_blank"
                                                    class="btn btn-outline-primary btn-sm mt-2">
                                                    <i class="bi bi-file-earmark-text me-1"></i> View File
                                                </a>

                                                @endif

                                                {{-- Children Ratings --}}

                                                @if(count($children))
                                                @php
                                                // Get first non-empty issue and expiry date from any child
                                                $firstIssueDate = null;
                                                $firstExpiryDate = null;

                                                foreach ($children as $childRating) {
                                                if (!$firstIssueDate && !empty($childRating->issue_date)) {
                                                $firstIssueDate = $childRating->issue_date;
                                                }
                                                if (!$firstExpiryDate && !empty($childRating->expiry_date)) {
                                                $firstExpiryDate = $childRating->expiry_date;
                                                }
                                                if ($firstIssueDate && $firstExpiryDate) break;
                                                }

                                                // Fallback if still null
                                                $firstIssueDate = $firstIssueDate ?? 'N/A';
                                                $firstExpiryDate = $firstExpiryDate ?? 'N/A';
                                                @endphp
                                                <h6 class="mt-4">Privileges</h6>
                                                <div class="row">
                                                    @foreach($children as $childRating)
                                                    @php $child = $childRating->rating; @endphp
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card h-100 border shadow-sm">
                                                            <div class="card-body">
                                                                <h6 class="card-title">{{ $child->name ?? '' }}</h6>
                                                                <p class="card-text small">
                                                                    Issue Date: {{ $firstIssueDate ?? 'N/A' }}<br>
                                                                    Expiry Date: {{ $firstExpiryDate ?? 'N/A' }}
                                                                </p>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                        <!-- </div> -->
                                        @endif


                                    </div>
                                    @endif
                                </div>
                                {{-- Licence 2 (EASA) Section --}}
                                @if ($user->licence_2_required == 1)
                                <div class="col-lg-6 col-md-12" id="second_licence_section">
                                    {{-- Licence Fields --}}
                                    <label class="form-label"><strong>EASA Licence <span class="text-danger">*</span>
                                            @if($document?->licence_2_invalidate == 1)
                                            <span class="text-danger">(Re-upload required)</span>
                                            @endif
                                        </strong>
                                        @if ($document?->licence_verified_2 == 1)
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                        @endif
                                    </label>

                                    <input type="text" name="licence_2" id="licence_2" value="{{ $document?->licence_2 ?? '' }}" class="form-control" placeholder="Enter EASA Licence Number">
                                    <div class="text-danger error_e" id="licence_error_up"></div>

                                    <label class="form-label mt-3"><strong>Expiry Date <span class="text-danger">*</span></strong></label>
                                    <input type="date" name="licence_expiry_date_2" id="licence_expiry_date_2" value="{{ $document?->licence_expiry_date_2 ?? '' }}" class="form-control mb-2">

                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="non_expiring_licence_2" name="non_expiring_licence_2" value="1" {{ $document?->licence_non_expiring_2 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="non_expiring_licence_2"><strong>Non-Expiring Licence</strong></label>
                                    </div>

                                    <input type="file" name="licence_file_2" id="licence_file_2" class="form-control mt-3" accept=".pdf,.jpg,.jpeg,.png">
                                    <input type="hidden" name="old_licence_file_2" value="{{ $document?->licence_file_2 }}">
                                    <div class="text-danger error_e" id="licence_file_2_error_up"></div>

                                    @if ($document?->licence_file_2)
                                    <div class="mt-3">
                                        <a href="{{ asset('storage/' . $document->licence_file_2) }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                            style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                            <i class="bi bi-file-earmark-text me-1" style="font-size: 16px;"></i> View
                                            Licence
                                        </a>
                                    </div>
                                    @endif
                                
                                    {{-- Ratings --}}
                                    @if(isset($grouped['licence_2']) && count($grouped['licence_2']))
                                    <h4 class="mt-2">Ratings linked to EASA Licence</h4>
                                    @foreach($grouped['licence_2'] as $parentId => $entry)
                                    @php $j = $loop->index; @endphp
                                    @php
                                    $parent = $entry['parent'];
                                    $children = $entry['children'] ?? [];

                                    $rating = $parent->rating;
                                    $children = $entry['children'] ?? [];

                                    $hasValidChildren = collect($children)->contains(function ($child) {
                                    return !is_null($child->rating_id);
                                    });

                                    if (!$hasValidChildren) {
                                    $children = [];
                                    }
                                    @endphp



                                    <?php
                                    
                                    ?>
                                    <div class="card shadow-sm border rounded mb-4">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $rating->name ?? '' }}</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label mt-2"><strong>Issue Date</strong></label>
                                                    <!-- <input type="date" name="issue_date[{{ $rating->id }}]" class="form-control"
                                                            value="{{ old("issue_date.$rating->id", $parent->issue_date) }}"> -->

                                                    <input type="hidden" name="issue_date_licence2[{{ $j }}][id]" value="{{ $rating->id }}">

                                                    <!-- parentid -->
                                                    <input type="hidden" name="issue_date_licence2[{{ $j }}][parentid]" value="{{ $entry['children'][0]['parent_id'] }}">

                                                    <!-- actual date -->
                                                    <input type="date" name="issue_date_licence2[{{ $j }}][issue_date]" class="form-control"
                                                        value="{{ $entry['children'][0]['issue_date'] ?? '' }}">
                                                    <!-- // user id  -->
                                                    <input type="hidden" name="issue_date_licence2[{{ $j }}][user_id]" class="form-control"
                                                        value="{{ $entry['children'][0]['user_id'] }}">
                                                    <!-- // Linked to -->
                                                    <input type="hidden" name="issue_date_licence2[{{ $j }}][linked_to]" class="form-control"
                                                        value="{{ $entry['children'][0]['linked_to'] }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                                                    <!-- <input type="date" name="expiry_date[{{ $rating->id }}]" class="form-control"
                                                            value="{{ old("expiry_date.$rating->id", $parent->expiry_date) }}"> -->
                                                    <input type="hidden" name="expiry_date_licence2[{{ $j }}][id]" value="{{ $rating->id }}">

                                                    <!-- parentid -->
                                                    <input type="hidden" name="expiry_date_licence2[{{ $j }}][parentid]" value="{{ $entry['children'][0]['parent_id'] }}">

                                                    <!-- actual date -->
                                                    <input type="date" name="expiry_date_licence2[{{ $j }}][expiry_date]" class="form-control"
                                                        value="{{ $entry['children'][0]['expiry_date'] ?? '' }}">
                                                    <!-- // user id  -->
                                                    <input type="hidden" name="expiry_date_licence2[{{ $j }}][user_id]" class="form-control"
                                                        value="{{ $entry['children'][0]['user_id'] }}">
                                                    <!-- // Linked to -->
                                                    <input type="hidden" name="expiry_date_licence2[{{ $j }}][linked_to]" class="form-control"
                                                        value="{{ $entry['children'][0]['linked_to'] }}">
                                                </div>
                                            </div>

                                            <label class="form-label mt-3"><strong>Upload File</strong></label>
                                            <!-- <input type="file" name="rating_file[{{ $rating->id }}]" class="form-control"> -->
                                            <input type="hidden" name="rating_file_licence2[{{ $j }}][parentid]" value="{{ $entry['children'][0]['parent_id'] }}" class="form-control">
                                            <input type="hidden" name="rating_file_licence2[{{ $j }}][user_id]" value="{{ $entry['children'][0]['user_id'] ?? '' }}" class="form-control">

                                            <input type="hidden" name="rating_file_licence2[{{ $j }}][linked_to]" value="{{ $entry['children'][0]['linked_to'] ?? '' }}" class="form-control">

                                            <input type="file" name="rating_file_licence2[{{ $j }}][upload2]" class="form-control">

                                            <div class="licence2_file">
                                                @if(!empty($entry['children'][0]['file_path']))
                                                <a href="{{ asset('storage/' . $entry['children'][0]['file_path']) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary" style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                                    <i class="bi bi-file-earmark-text me-1"></i> View File
                                                </a>
                                                @endif

                                            </div>



                                            @if($parent->file_path)
                                            <a href="{{ asset('storage/' . $parent->file_path) }}" target="_blank"
                                                class="btn btn-outline-primary btn-sm mt-2">
                                                <i class="bi bi-file-earmark-text me-1"></i> View File
                                            </a>
                                            @endif

                                            @if(count($children))
                                            @php
                                            // Get first non-empty issue and expiry date from any child
                                            $firstIssueDate = null;
                                            $firstExpiryDate = null;

                                            foreach ($children as $childRating) {
                                            if (!$firstIssueDate && !empty($childRating->issue_date)) {
                                            $firstIssueDate = $childRating->issue_date;
                                            }
                                            if (!$firstExpiryDate && !empty($childRating->expiry_date)) {
                                            $firstExpiryDate = $childRating->expiry_date;
                                            }
                                            if ($firstIssueDate && $firstExpiryDate) break;
                                            }

                                            // Fallback if still null
                                            $firstIssueDate = $firstIssueDate ?? 'N/A';
                                            $firstExpiryDate = $firstExpiryDate ?? 'N/A';
                                            @endphp
                                            <h6 class="mt-4">Privileges</h6>
                                            <div class="row">
                                                @foreach($children as $childRating)
                                                @php $child = $childRating->rating; @endphp
                                                <div class="col-md-6 mb-3">
                                                    <div class="card h-100 border shadow-sm">
                                                        <div class="card-body">
                                                            <h6 class="card-title">{{ $child->name ?? ' ' }}</h6>
                                                            <p class="card-text small">
                                                                Issue Date: {{ $firstIssueDate ?? 'N/A' }}<br>
                                                                Expiry Date: {{ $firstExpiryDate ?? 'N/A' }}
                                                            </p>
                                                            @if($childRating->file_path)
                                                            <!-- <a href="{{ asset('storage/' . $childRating->file_path) }}" target="_blank"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-file-earmark-text me-1"></i> View File
                                                                </a> -->
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                                @endif

                                <!-- Passport -->
                                @if ($user->passport_required == 1)
                                <div class="form-group col-sm-6 mt-3">
                                    <label for="passport_checkbox" class="form-label">
                                        <strong>Passport <span class="text-danger">*</span>
                                            @if($document?->passport_invalidate == 1)
                                            <span class="text-danger">(Re-upload a new document and date.)</span>
                                            @endif
                                        </strong>
                                        @if($document?->passport_verified)
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                        @endif
                                    </label>
                                    <input type="text" name="passport" id="passport" class="form-control"
                                        value="{{ $document?->passport ? $document?->passport : ''}}"
                                        placeholder="Enter Passport Number">
                                    <div id="passport_error_up" class="text-danger error_e"></div>


                                    <label for="licence_" class="form-label mt-3">
                                        <strong>Expiry Date <span class="text-danger">*</span> </strong>
                                        @if($document?->passport_status == 'Red')
                                        <span class="text-danger">
                                            <i class="bi bi-x-circle-fill"></i> Expired
                                        </span>
                                        @elseif($document?->passport_status == 'Yellow')
                                        <span class="text-warning">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon
                                        </span>
                                        @elseif($document?->passport_status == 'Green')
                                        <span class="text-success">
                                            <i class="bi bi-check-circle-fill"></i> Valid
                                        </span>
                                        @else
                                        <span class="text-secondary">
                                            <i class="bi bi-question-circle-fill"></i> N/A
                                        </span>
                                        @endif
                                    </label>


                                    <input type="date" name="passport_expiry_date" id="passport_expiry_date"
                                        value="{{ $document?->passport_expiry_date ? $document?->passport_expiry_date : ''}}"
                                        class="form-control mt-3">
                                    <div id="passport_expiry_date_error_up" class="text-danger error_e"></div>

                                    <input type="file" name="passport_file" id="passport_file" class="form-control mt-3"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <div id="passport_file_error_up" class="text-danger error_e"></div>
                                    <input type="hidden" name="old_passport_file" value="{{ $document?->passport_file }}">
                                    @if ($document?->passport_file && $document?->passport_invalidate == 0)
                                    <div class="mt-3">
                                        <a href="{{ asset('storage/' . $document?->passport_file) }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                            style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                            <i class="bi bi-file-earmark-text me-1" style="font-size: 16px;"></i> View
                                            Passport
                                        </a>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                      <!-- // -->@if ($user->medical == 1)
                                        <div class="col-md-6">
                                            <label for="extra_roles" class="form-label"><strong>UK Medical Issued By </strong>
                                                <span class="text-danger"></span>
                                                @if($document?->medical_invalidate == 1)
                                                <span class="text-danger">(Re-upload a new document and date.)</span>
                                                @endif

                                            </label>

                                            @if ($document?->medical_verified)
                                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                            @endif
                                            <select class="form-select" name="issued_by" id="issued_by">
                                                <option value="">Select Issued By</option>
                                                <option value="UKCAA" {{ $document?->medical_issuedby == "UKCAA" ? 'selected' : '' }}>UK CAA</option>
                                                <option value="EASA" {{ $document?->medical_issuedby == "EASA" ? 'selected' : '' }}>EASA</option>
                                                <option value="FAA" {{ $document?->medical_issuedby == "FAA" ? 'selected' : '' }}>FAA</option>
                                            </select>
                                            <div id="issued_by_error_up" class="text-danger error_e"></div>
                                            <label for="extra_roles" class="form-label mt-3"><strong> Medical Class </strong><span
                                                    class="text-danger"></span></label>
                                            <select class="form-select " name="medical_class" id="medical_class">

                                                <option value="">Select the Class</option>
                                                <option value="class1" <?php echo ($document?->medical_class == "class1") ? 'selected' : '' ?>>Class 1</option>
                                                <option value="class2" <?php echo ($document?->medical_class == "class2") ? 'selected' : '' ?>>Class 2</option>
                                            </select>
                                            <div id="medical_class_error_up" class="text-danger error_e"></div>
                                            <label for="extra_roles" class="form-label mt-3"><strong>Medical Issue Date </strong> <span
                                                    class="text-danger"></span></label>
                                            <input type="date" name="medical_issue_date" id="medical_issue_date"
                                                class="form-control" placeholder="Medical Issue Date" value="<?php echo isset($document?->medical_issuedate) ? $document?->medical_issuedate : ''; ?>">
                                            <div id="medical_issue_date_error_up" class="text-danger error_e"></div>

                                            <label for="extra_roles" class="form-label mt-3"><strong> Medical Expiry Date </strong><span
                                                    class="text-danger"></span>
                                                @if($document?->medical_status == 'Red')
                                                <span class="text-danger">
                                                    <i class="bi bi-x-circle-fill"></i> Expired
                                                </span>
                                                @elseif($document?->medical_status == 'Yellow')
                                                <span class="text-warning">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon
                                                </span>
                                                @elseif($document?->medical_status == 'Green')
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle-fill"></i> Valid
                                                </span>
                                                @else
                                                <span class="text-secondary">
                                                    <i class="bi bi-question-circle-fill"></i> N/A
                                                </span>
                                                @endif
                                            </label>
                                            <input type="date" name="medical_expiry_date" id="medical_expiry_date"
                                                class="form-control" placeholder="Medical Expiry Date" value="<?php echo isset($document?->medical_expirydate) ? $document?->medical_expirydate : ''; ?>">
                                            <div id="medical_expiry_date_error_up" class="text-danger error_e"></div>

                                            <label for="extra_roles" class="form-label mt-3"><strong> Medical Detail </strong> <span
                                                    class="text-danger"></span></label>
                                            <textarea name="medical_detail" id="medical_detail" class="form-control"
                                                placeholder="Enter the Detail"><?php echo isset($document?->medical_restriction) ?  $document?->medical_restriction : ''; ?></textarea>

                                            <input type="file" name="medical_file" id="medical_file" class="form-control mt-3"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <input type="hidden" name="old_medical_file" value="{{ $document?->medical_file }}">
                                            @if ($document?->medical_file)
                                            <div class="mt-3">
                                                <a href="{{ asset('storage/' . $document?->medical_file) }}" target="_blank"
                                                    class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                                    style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                                    <i class="bi bi-file-earmark-text me-1" style="font-size: 16px;"></i> View
                                                    Medical
                                                </a>
                                            </div>
                                            @endif
                                            <div id="medical_file_error_up" class="text-danger error_e"></div>

                                            <!-- @if(empty($document && $document?->medical_issuedby_2))
                                            <button type="button" id="add_second_medical_btn" class="btn btn-secondary add_btn mt-3 mb-3">
                                                Add Second Medical
                                            </button>
                                        @endif -->
                                        </div>
                                        <!-- // -->  @endif
                                   <!-- // -->@if ($user->medical_2_required == 1)
                                        <div class="col-md-6" id="edit_second_medical_section" style="display: {{ !empty($user?->medical_2_required) ? 'block' : 'none' }};">
                                            <!-- <div> -->
                                            <label for="extra_roles" class="form-label"><strong>EASA Medical Issued By </strong>
                                                <span class="text-danger"></span></label>
                                            @if($document?->medical_2_invalidate == 1)
                                            <span class="text-danger">(Re-upload a new document and date.)</span>
                                            @endif
                                            @if ($document?->medical_verified_2)
                                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                            @endif
                                            <select class="form-select" name="issued_by_2" id="issued_by_2">
                                                <option value="">Select Issued By</option>
                                                <option value="UKCAA" {{ $document?->medical_issuedby_2 == "UKCAA" ? 'selected' : '' }}>UK CAA</option>
                                                <option value="EASA" {{ $document?->medical_issuedby_2 == "EASA" ? 'selected' : '' }}>EASA</option>
                                                <option value="FAA" {{ $document?->medical_issuedby_2 == "FAA" ? 'selected' : '' }}>FAA</option>
                                            </select>
                                            <div id="issued_by_error_up" class="text-danger error_e"></div>
                                            <label for="extra_roles" class="form-label mt-3"><strong> Medical Class </strong><span
                                                    class="text-danger"></span></label>
                                            <select class="form-select " name="medical_class_2" id="medical_class_2">

                                                <option value="">Select the Class</option>
                                                <option value="class1" <?php echo ($document?->medical_class_2 == "class1") ? 'selected' : '' ?>>Class 1</option>
                                                <option value="class2" <?php echo ($document?->medical_class_2 == "class2") ? 'selected' : '' ?>>Class 2</option>
                                            </select>
                                            <div id="medical_class_error_up" class="text-danger error_e"></div>
                                            <label for="extra_roles" class="form-label mt-3"><strong>Medical Issue Date </strong> <span
                                                    class="text-danger"></span></label>
                                            <input type="date" name="medical_issue_date_2" id="medical_issue_date_2"
                                                class="form-control" placeholder="Medical Issue Date" value="<?php echo isset($document?->medical_issuedate_2) ? $document?->medical_issuedate_2 : ''; ?>">
                                            <div id="medical_issue_date_2_error_up" class="text-danger error_e"></div>

                                            <label for="extra_roles" class="form-label mt-3"><strong> Medical Expiry Date </strong><span
                                                    class="text-danger"></span>
                                                @if($document?->medical_2_status == 'Red')
                                                <span class="text-danger">
                                                    <i class="bi bi-x-circle-fill"></i> Expired
                                                </span>
                                                @elseif($document?->medical_2_status == 'Yellow')
                                                <span class="text-warning">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon
                                                </span>
                                                @elseif($document?->medical_2_status == 'Green')
                                                <span class="text-success">
                                                    <i class="bi bi-check-circle-fill"></i> Valid
                                                </span>
                                                @else
                                                <span class="text-secondary">
                                                    <i class="bi bi-question-circle-fill"></i> N/A
                                                </span>
                                                @endif
                                            </label>
                                            <input type="date" name="medical_expiry_date_2" id="medical_expiry_date_2"
                                                class="form-control" placeholder="Medical Expiry Date" value="<?php echo isset($document?->medical_expirydate_2) ? $document?->medical_expirydate_2 : ''; ?>">
                                            <div id="medical_expiry_date_2_error_up" class="text-danger error_e"></div>


                                            <label for="extra_roles" class="form-label mt-3"><strong> Medical Detail </strong> <span
                                                    class="text-danger"></span></label>
                                            <textarea name="medical_detail_2" id="medical_detail_2" class="form-control"
                                                placeholder="Enter the Detail"><?php echo isset($document?->medical_restriction_2) ?  $document?->medical_restriction_2 : ''; ?></textarea>

                                            <input type="file" name="medical_file_2" id="medical_file_2" class="form-control mt-3"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <input type="hidden" name="old_medical_file_2" value="{{ $document?->medical_file_2 }}">
                                            @if ($document?->medical_file_2)
                                            <div class="mt-3">
                                                <a href="{{ asset('storage/' . $document?->medical_file_2) }}" target="_blank"
                                                    class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                                    style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                                    <i class="bi bi-file-earmark-text me-1" style="font-size: 16px;"></i> View
                                                    Medical
                                                </a>
                                            </div>
                                            @endif
                                            <div id="medical_file_2_error_up" class="text-danger error_e"></div>
                                            <!-- </div> -->
                                        </div>
                                    <!-- // -->  @endif
                                    </div>
                                </div>
                              
                            </div>

                            <div class="row">

                                @if ($user->custom_field_required == 1)
                                    <div class="col-6">
                                        <label for="custom_field_checkbox" class="form-label"><strong>Custom Fields </strong></label>
                                        <label for="customfield_filelabel" id="customfield_filelabel" class="form-label">Date</label>
                                        <input type="checkbox" name="custom_date_checkbox" id="custom_date_checkbox" class="m-2" {{ ($user->custom_field_date)? 'checked': '' }}>
                                        <label for="customfield_textlabel" id="customfield_textlabel" class="form-label">Text</label>
                                        <input type="checkbox" name="custom_text_checkbox" id="custom_text_checkbox" class="ms-2" {{ ($user->custom_field_text)? 'checked': '' }}>
                                        <div class="">
                                            <input type="date" name="custom_field_date" id="custom_date" class="form-control mt-3"
                                                style="display: none;" value="{{ ($user->custom_field_date)? $user->custom_field_date: '' }}">
                                            <div id="custom_field_date_error_up" class="text-danger error_e"></div>
                                            <input type="text" name="custom_field_text" id="custom_text" class="form-control mt-3"
                                                placeholder="Enter the Text" style="display: none;" value="{{ ($user->custom_field_text)? $user->custom_field_text: '' }}">
                                            <div id="custom_field_text_error_up" class="text-danger error_e"></div>
                                        </div>
                                    </div>
                                @endif

                                <?php 
                                    $user = auth()->user(); 
                                    $isAdmin = $user->is_admin == 1;
                                    $email = $user->organization->send_email ?? 0;
                                ?>

                                @if($isAdmin)
                                    <div class="col-md-6">
                                        <div class="email-switch">
                                            <label for="send_email" class="switch-text">
                                                Send Email Notification
                                            </label>

                                            <input type="hidden" name="send_email" value="0">

                                            <label class="switch mt-2">
                                                <input type="checkbox" id="send_email" name="send_email" value="1" class="switch-input" {{ $email == 1 ? 'checked' : '' }} >
                                                <div class="switch-button">
                                                    <span class="switch-button-left">Send Email</span>
                                                    <span class="switch-button-right">Not Send Email</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>

                    <div class="text-center" style="display: flex; justify-content: center;">
                        <button type="submit" id="updateForm" style="width: auto !important; "
                            class="btn btn-primary mt-3">Save Changes</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changePasswordForm" method="POST" class="row g-3">
                        @csrf
                        <div class="alert alert-danger" style="display:none"></div>
                        <div class="form-group">
                            <label for="password">New Password<span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control">
                            <div id="password_errorr" class="text-danger error_ee"></div>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password<span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control">
                            <div id="password_confirmation_errorr" class="text-danger error_ee"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary  sbt_btn">Change</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End of change password -->
</div>


@endsection

@section('js_scripts')

<script>
    $(document).ready(function() {
        function toggleFields() {
            const isNonExpiringChecked = $('#non_expiring_licence').prop('checked');
            const isNonExpiringChecked_2 = $('#non_expiring_licence_2').prop('checked');
            const expiryDateField = $('#licence_expiry_date');
            const expiryDateField_2 = $('#licence_expiry_date_2');

            // Check if the expiry date field exists and is not empty
            const isExpiryDateFilled = expiryDateField.length && expiryDateField.val().trim() !== '';
            const isExpiryDateFilled_2 = expiryDateField_2.length && expiryDateField_2.val().trim() !== '';

            if (isExpiryDateFilled) {
                $('#non_expiring_licence').prop('checked', false).parent().hide();
            } else {
                $('#non_expiring_licence').parent().show();
            }

            if (isExpiryDateFilled_2) {
                $('#non_expiring_licence_2').prop('checked', false).parent().hide();
            } else {
                $('#non_expiring_licence_2').parent().show();
            }

            if (isNonExpiringChecked) {
                expiryDateField.val('').hide().prop('required', false);
            } else {
                expiryDateField.show().prop('required', true);
            }

            if (isNonExpiringChecked_2) {
                expiryDateField_2.val('').hide().prop('required', false);
            } else {
                expiryDateField_2.show().prop('required', true);
            }
        }


        // Initialize the fields on page load
        toggleFields();

        // Event listeners
        $('#non_expiring_licence').change(toggleFields);
        $('#non_expiring_licence_2').change(toggleFields);
        $('#licence_expiry_date').on('input', toggleFields);
        $('#licence_expiry_date_2').on('input', toggleFields);

        $(document).on('click', '#updateForm', function(e) {
            e.preventDefault();
            var formData = new FormData($('#userProfileForm')[0]);

            $(".loader").fadeIn('fast');

            $.ajax({
                type: 'post',
                url: "/users/profile/update",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response.message);
                    // $(".loader").fadeOut('slow');

                    $('#editUserDataModal').modal('hide');
                    $('#update_success_msg').html(`
                    <div class="alert alert-success fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i>
                        ${response.message}
                    </div>
                    `);

                    setTimeout(function() {
                        $('#update_success_msg').fadeOut('slow');

                    }, 5000);

                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                },
                error: function(xhr, status, error) {

                    //  $(".loader").fadeOut("slow");

                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function(key, value) {
                        var formattedKey = key.replace(/\./g, '_') + '_error_up';
                        var errorMsg = '<p>' + value[0] + '</p>';
                        $('#' + formattedKey).html(errorMsg);
                    });
                }
            });
        });

        toggleCustomFields();

        // When checkboxes are toggled
        $('#custom_date_checkbox, #custom_text_checkbox').on('change', function() {
            // Ensure only one checkbox can be checked at a time
            if ($(this).is('#custom_date_checkbox')) {
                $('#custom_text_checkbox').prop('checked', false);
            } else {
                $('#custom_date_checkbox').prop('checked', false);
            }
            toggleCustomFields();
        });

    });

    function toggleCustomFields() {
        if ($('#custom_date_checkbox').is(':checked')) {
            $('#custom_date').show();
            $('#custom_text').hide();
        } else if ($('#custom_text_checkbox').is(':checked')) {
            $('#custom_text').show();
            $('#custom_date').hide();
        } else {
            $('#custom_text, #custom_date').hide();
        }
    }

    $(document).on('click', '.change-password-btn', function(e) {
        e.preventDefault();
        $('.alert-danger').css('display', 'none');
        $('#changePasswordModal').modal('show');
    });

    $('#changePasswordForm').submit(function(e) {
        e.preventDefault();
        $('.error_ee').html('');
        var formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: '{{ url("/users/change-password") }}',
            data: formData,
            success: function(response) {
                $('.alert-danger').html('');
                $('#changePasswordModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage;
                $.each(validationErrors, function(key, value) {

                    var html1 = '<p>' + value + '</p>';
                    $('#' + key + '_errorr').html(html1);

                });


            }
        });
    });
</script>
@endsection