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
                    <form id="userProfileForm" enctype="multipart/form-data">
                        @csrf
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
                                    <div class="col-md-6">
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
                                        <div class="row mt-4">
                                            {{-- Licence 1 (UK) Section --}}
                                            @if(isset($grouped['licence_1']) && count($grouped['licence_1']))
                                            <!-- <div class="col-lg-6 col-md-12"> -->
                                            <h4>Ratings linked to UK Licence</h4>
                                            @foreach($grouped['licence_1'] as $parentId => $entry)
                                            @php $i = $loop->index; @endphp
                                            @php
                                            $parent = $entry['parent'];
                                            $children = $entry['children'] ?? [];
                                            $rating = $parent->rating;
                                            @endphp

                                            <?php
                                            // echo $entry['children'][0]['user_id'] . "<br>";
                                            // echo $entry['children'][0]['rating_id'] . "<br>";
                                            // echo $entry['children'][0]['parent_id'] . "<br>";
                                            // echo $entry['children'][0]['linked_to'] . "<br>";
                                            // echo $entry['children'][0]['issue_date'] . "<br>";
                                            // echo $entry['children'][0]['expiry_date'] . "<br>";
                                            ?>
                                            <div class="card shadow-sm border rounded mb-4">
                                                <div class="card-body">
                                                    <h5 class="card-title">{{ $rating->name ?? ' ' }}</h5>
                                                    <div class="row">



                                                        <div class="col-md-6">
                                                            <label class="form-label mt-2"><strong>Issue Date</strong></label>
                                                            <!-- <input type="date" name="issue_date[{{ $rating->id }}][{{ $entry['children'][0]['parent_id']}}]" class="form-control"
                                                                value="{{ old("issue_date.$rating->id", $parent->issue_date) }}"> -->
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
                                                    <input type="file" name="rating_file[{{ $rating->id }}]" class="form-control">

                                                    @if($parent->file_path)
                                                    <a href="{{ asset('storage/' . $parent->file_path) }}" target="_blank"
                                                        class="btn btn-outline-primary btn-sm mt-2">
                                                        <i class="bi bi-file-earmark-text me-1"></i> View File
                                                    </a>
                                                    @endif

                                                    {{-- Children Ratings --}}
                                                  
                                                    @if(count($children))
                                                    <h6 class="mt-4">Privileges</h6>
                                                    <div class="row">
                                                        @foreach($children as $childRating)
                                                        @php $child = $childRating->rating; @endphp
                                                        <div class="col-md-6 mb-3">
                                                            <div class="card h-100 border shadow-sm">
                                                                <div class="card-body">
                                                                    <h6 class="card-title">{{ $child->name ?? '' }}</h6>
                                                                    <p class="card-text small">
                                                                        Issue Date: {{ $childRating->issue_date ?? 'N/A' }}<br>
                                                                        Expiry Date: {{ $childRating->expiry_date ?? 'N/A' }}
                                                                    </p>
                                                                    @if($childRating->file_path)
                                                                    <a href="{{ asset('storage/' . $childRating->file_path) }}" target="_blank"
                                                                        class="btn btn-sm btn-outline-primary">
                                                                        <i class="bi bi-file-earmark-text me-1"></i> View File
                                                                    </a>
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
                                        <a href="{{ asset('storage/' . $document->licence_file_2) }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm mt-3 d-flex align-items-center" style="width: fit-content;">
                                            <i class="bi bi-file-earmark-text me-1"></i> View Licence
                                        </a>
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
                                        //echo $entry['children'][0]['user_id'] . "<br>";
                                        // echo $entry['children'][0]['rating_id'] . "<br>";
                                        // echo $entry['children'][0]['parent_id'] . "<br>";
                                        // echo $entry['children'][0]['linked_to'] . "<br>";
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
                                                <input type="file" name="rating_file[{{ $rating->id }}]" class="form-control">

                                                @if($parent->file_path)
                                                <a href="{{ asset('storage/' . $parent->file_path) }}" target="_blank"
                                                    class="btn btn-outline-primary btn-sm mt-2">
                                                    <i class="bi bi-file-earmark-text me-1"></i> View File
                                                </a>
                                                @endif
                                              
                                                @if(count($children))
                                                <h6 class="mt-4">Privileges</h6>
                                                <div class="row">
                                                    @foreach($children as $childRating) 
                                                    @php $child = $childRating->rating; @endphp 
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card h-100 border shadow-sm">
                                                            <div class="card-body">
                                                                <h6 class="card-title">{{ $child->name ?? ' ' }}</h6>
                                                                <p class="card-text small">
                                                                    Issue Date: {{ $childRating->issue_date ?? 'N/A' }}<br>
                                                                    Expiry Date: {{ $childRating->expiry_date ?? 'N/A' }}
                                                                </p>
                                                                @if($childRating->file_path) 
                                                                <a href="{{ asset('storage/' . $childRating->file_path) }}" target="_blank"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-file-earmark-text me-1"></i> View File
                                                                </a>
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
                                    @if ($user->medical == 1)
                                    <div class="col-md-12">
                                        <div class="row">

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
                                        </div>
                                    </div>

                                    @endif

                                    @php
                                    // Map rating_id => UserRating model
                                    $userRatingsMap = $user->usrRatings->keyBy('rating_id');

                                    // Filter only general ratings from the passed-in $ratings
                                    $hasGeneralRatings = collect($ratings)->filter(function($rating) use ($userRatingsMap) {
                                    $ur = $userRatingsMap[$rating->id] ?? null;
                                    return $ur && $ur->linked_to === 'general';
                                    });

                                    // Group child ratings under their parent_id
                                    $groupedChildRatings = [];
                                    foreach ($user->usrRatings as $ur) {
                                    $rating = $ur->rating;
                                    if ($rating && $rating->parent_id) {
                                    $groupedChildRatings[$rating->parent_id][] = $ur;
                                    }
                                    }
                                    @endphp
                                    @if($user->rating_required == 1 && $hasGeneralRatings->isNotEmpty())
                                    <h4 class="mt-4">Rating Data</h4>
                                    <div class="row mt-3">
                                        @foreach($hasGeneralRatings as $rating)
                                        @php
                                        $userRating = $userRatingsMap[$rating->id] ?? null;
                                        $childRatings = $groupedChildRatings[$rating->id] ?? [];
                                        @endphp

                                        {{-- Skip if not a parent rating --}}
                                        {{-- @if($rating->parent_id !== null)
                                         @continue
                                         @endif --}}

                                        <div class="col-6 border p-3 mb-3 rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">{{ $rating->name }}</h5>
                                                @if($userRating?->admin_verified)
                                                <span class="text-success ms-3">
                                                    <i class="bi bi-check-circle-fill"></i> Verified
                                                </span>
                                                @endif
                                            </div>

                                            {{-- Issue Date --}}
                                            <label class="form-label mt-2" for="issue_date_{{ $rating->id }}">
                                                <strong>{{ $rating->name }} Issue Date</strong>
                                            </label>
                                            <input type="date"
                                                name="issue_date[{{ $rating->id }}]"
                                                id="issue_date_{{ $rating->id }}"
                                                class="form-control"
                                                value="{{ old("issue_date.$rating->id", $userRating?->issue_date) }}">
                                            <div class="text-danger error_e" id="issue_date_{{ $rating->id }}_error_up"></div>

                                            {{-- Expiry Date --}}
                                            <label class="form-label mt-2" for="expiry_date_{{ $rating->id }}">
                                                <strong>{{ $rating->name }} Expiry Date</strong>
                                                @php $status = $userRating?->expiry_status; @endphp
                                                @if($status === 'Red')
                                                <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Expired</span>
                                                @elseif($status === 'Yellow')
                                                <span class="text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Expiring Soon</span>
                                                @elseif($status === 'Green')
                                                <span class="text-success"><i class="bi bi-check-circle-fill"></i> Valid</span>
                                                @else
                                                <span class="text-secondary"><i class="bi bi-question-circle-fill"></i> N/A</span>
                                                @endif
                                            </label>
                                            <input type="date"
                                                name="expiry_date[{{ $rating->id }}]"
                                                id="expiry_date_{{ $rating->id }}"
                                                class="form-control"
                                                value="{{ old("expiry_date.$rating->id", $userRating?->expiry_date) }}">
                                            <div class="text-danger error_e" id="expiry_date_{{ $rating->id }}_error_up"></div>

                                            {{-- File Upload --}}
                                            <label class="form-label mt-2" for="rating_file_{{ $rating->id }}">
                                                <strong>{{ $rating->name }} File Upload</strong>
                                            </label>
                                            <input type="file"
                                                name="rating_file[{{ $rating->id }}]"
                                                id="rating_file_{{ $rating->id }}"
                                                class="form-control"
                                                accept=".pdf,.jpg,.jpeg,.png">
                                            <div class="text-danger error_e" id="rating_file_{{ $rating->id }}_error_up"></div>

                                            @if(!empty($userRating?->file_path))
                                            <a href="{{ asset('storage/' . $userRating->file_path) }}" target="_blank"
                                                class="btn btn-outline-primary btn-sm d-flex align-items-center mt-3"
                                                style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                                <i class="bi bi-file-earmark-text me-1"></i> View File
                                            </a>
                                            @endif

                                            {{-- Child Ratings --}}
                                            @if(count($childRatings) > 0)
                                            <hr>
                                            <h6>Privileges</h6>
                                            <div class="row mt-3">
                                                @foreach($childRatings as $childUserRating)
                                                @php $child = $childUserRating->rating; @endphp
                                                <div class="col-md-6 mb-3">
                                                    <div class="card border border-secondary h-100">
                                                        <div class="card-body">
                                                            <h6 class="card-title">{{ $child->name }}</h6>
                                                            <p class="card-text small">
                                                                Issue Date: {{ $userRating?->issue_date ?? 'N/A' }}<br>
                                                                Expiry Date: {{ $userRating?->expiry_date ?? 'N/A' }}
                                                            </p>

                                                            @if($childUserRating->admin_verified)
                                                            <span class="text-success mt-2 d-inline-block">
                                                                <i class="bi bi-check-circle-fill"></i> Verified
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif


                                </div>


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
    </div>
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
                    $(".loader").fadeOut('slow');

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
                    // location.reload();
                },
                error: function(xhr, status, error) {

                    $(".loader").fadeOut("slow");

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
</script>
@endsection