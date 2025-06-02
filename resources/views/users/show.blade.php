@extends('layout.app')
@section('title', 'User Details')
@section('sub-title', 'User Details')

@section('content')
<div class="main_cont_outer">
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif

    <div class="card shadow-lg rounded-4 border-0 overflow-hidden">
    <div class="card-body p-4">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="row align-items-center mb-4">
                <!-- Profile Picture -->
                <div class="col-md-3 text-center mb-3 mb-md-0">
                    <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('/assets/img/default_profile.png') }}"
                        class="rounded-circle img-thumbnail shadow" width="150" alt="Profile Picture">
                </div>

                <!-- User Info -->
                <div class="col-md-9">
                    <h3 class="fw-bold mb-2">{{ $user->firstname }} {{ $user->lastname }}</h3>
                    <p class="mb-1"><i class="bi bi-envelope-fill text-primary me-2"></i>{{ $user->email }}</p>
                    <p class="mb-1"><i class="bi bi-person-badge-fill text-success me-2"></i>Role: <strong>{{ $user->roles->role_name }}</strong></p>

                    @if(!empty($extraRoles))
                    <p class="mb-1"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Extra Roles: <strong>{{ implode(', ', $extraRoles) }}</strong></p>
                    @endif

                    <p class="mb-1"><i class="bi bi-currency-dollar text-info me-2"></i>Currency: {{ $user->currency ?? 'N/A' }}</p>

                    <span class="badge {{ $user->status == 1 ? 'bg-success' : 'bg-danger' }} mt-2">
                        {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <hr class="my-4">

            @php
                $document = $user->documents;
            @endphp

            <!-- Additional Details: Passport & License -->
            <div class="row g-4">
                <!-- <div class="col-md-6">
                    <h5 class="text-muted mb-3"><i class="bi bi-passport text-primary me-2"></i>Passport Details</h5>
                    @if($document?->passport)
                        <p>Number: {{ $document?->passport }}</p>
                        <a href="{{ Storage::url($document?->passport_file) }}" class="btn btn-outline-primary btn-sm mb-2" target="_blank">View File</a>
                        @if($user->passport_admin_verification_required==1)
                            <div class="form-check form-switch">
                                <input class="form-check-input verify-toggle" type="checkbox" id="passport_verify" data-user-id="{{ encode_id($user->id) }}" data-type="passport" {{ $document?->passport_verified ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="passport_verify">{{ $document?->passport_verified ? 'Verified' : 'Mark as Verified' }}</label>
                            </div>
                        @endif
                        @if($document?->passport_verified)
                            <button class="btn btn-danger btn-sm invalidate-btn" data-user-id="{{ $user->id }}" data-type="passport">Invalidate</button>
                        @endif
                    @else
                        <p class="text-muted">No passport details available.</p>
                    @endif
                </div> -->

                <div class="col-md-12 mb-4">
                    <h5 class="text-muted mb-3">
                        <i class="bi bi-passport text-primary me-2"></i>Passport Details
                    </h5>
                    @if($document?->passport)
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <p class="mb-0"><strong>Number:</strong> {{ $document->passport }}</p>

                            @if($user->passport_admin_verification_required==1 && $document?->passport_file)
                            <a href="{{ Storage::url($document->passport_file) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                View File
                            </a>

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input verify-toggle" type="checkbox" id="passport_verify"
                                        data-user-id="{{ encode_id($user->id) }}" data-type="passport"
                                        {{ $document->passport_verified ? 'checked disabled' : '' }}>
                                    <label class="form-check-label" for="passport_verify">
                                        {{ $document->passport_verified ? 'Verified' : 'Mark as Verified' }}
                                    </label>
                                </div>
                            @endif

                            @if($document->passport_verified)
                                <button class="btn btn-danger btn-sm invalidate-btn"
                                    data-user-id="{{ $user->id }}" data-type="passport">
                                    Invalidate
                                </button>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">No passport details available.</p>
                    @endif
                </div>


                <!-- License Details -->
                <div class="col-md-12 mb-4">
                    <h5 class="text-muted mb-3"><i class="bi bi-award-fill text-danger me-2"></i>License Details</h5>
                    @if($document && $document->licence)
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <p class="mb-0"><strong>Number:</strong> {{ $document->licence }}</p>
                            @if($user->licence_admin_verification_required == 1  && $document?->licence_file)
                            <a href="{{ Storage::url($document->licence_file) }}" class="btn btn-outline-danger btn-sm" target="_blank">View File</a>

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input verify-toggle" type="checkbox" id="licence_verify"
                                        data-user-id="{{ encode_id($user->id) }}" data-type="licence"
                                        {{ $document->licence_verified ? 'checked disabled' : '' }}>
                                    <label class="form-check-label" for="licence_verify">{{ $document->licence_verified ? 'Verified' : 'Mark as Verified' }}</label>
                                </div>
                            @endif

                            @if($document->licence_verified)
                                <button class="btn btn-danger btn-sm invalidate-btn" data-user-id="{{ $user->id }}" data-type="licence">Invalidate</button>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">No license details available.</p>
                    @endif
                </div>

                <!-- Second License Details -->
                @if($document && $document->licence_2)
                <div class="col-md-12 mb-4">
                    <h5 class="text-muted mb-3"><i class="bi bi-award-fill text-danger me-2"></i>Second License Details</h5>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <p class="mb-0"><strong>Number:</strong> {{ $document->licence_2 }}</p>
                            @if($user->licence_2_admin_verification_required == 1 && $document->licence_file_2)
                                <a href="{{ Storage::url($document->licence_file_2) }}" class="btn btn-outline-danger btn-sm" target="_blank">View File</a>

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input verify-toggle" type="checkbox" id="licence2_verify"
                                        data-user-id="{{ encode_id($user->id) }}" data-type="licence_2"
                                        {{ $document->licence_verified_2 ? 'checked disabled' : '' }}>
                                    <label class="form-check-label" for="licence2_verify">{{ $document->licence_verified_2 ? 'Verified' : 'Mark as Verified' }}</label>
                                </div>
                            @endif

                            @if($document->licence_verified_2)
                                <button class="btn btn-danger btn-sm invalidate-btn" data-user-id="{{ $user->id }}" data-type="licence_2">Invalidate</button>
                            @endif
                        </div>
                </div>
                @endif

                <!-- Medical Details -->
                <div class="col-md-12 mb-4">
                    <h5 class="text-muted mb-3"><i class="bi bi-heart-pulse-fill text-danger me-2"></i>Medical Details</h5>
                    @if($document && $document->medical && !empty($document->medical_issuedby) && !empty($document->medical_class) && !empty($document->medical_issuedate))
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <p class="mb-0"><strong>Issued By:</strong> {{ $document->medical_issuedby }}</p>
                            <p class="mb-0"><strong>Class:</strong> {{ $document->medical_class }}</p>
                            <p class="mb-0"><strong>Issue Date:</strong> {{ $document->medical_issuedate }}</p>
                            <p class="mb-0"><strong>Expiry Date:</strong> {{ $document->medical_expirydate }}</p>

                            @if($user->medical_adminRequired == 1 && $document->medical_file)
                            <a href="{{ Storage::url($document->medical_file) }}" class="btn btn-outline-danger btn-sm" target="_blank">View File</a>

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input verify-toggle" type="checkbox" id="medical_verify"
                                        data-user-id="{{ encode_id($user->id) }}" data-type="medical"
                                        {{ $document->medical_verified ? 'checked disabled' : '' }}>
                                    <label class="form-check-label" for="medical_verify">{{ $document->medical_verified ? 'Verified' : 'Mark as Verified' }}</label>
                                </div>
                            @endif

                            @if($document->medical_verified)
                                <button class="btn btn-danger btn-sm invalidate-btn" data-user-id="{{ $user->id }}" data-type="medical">Invalidate</button>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">No medical details available.</p>
                    @endif
                </div>

                <!-- Second Medical Details -->
                @if($document && $document->medical_2 && !empty($document->medical_issuedby_2) && !empty($document->medical_class_2) && !empty($document->medical_issuedate_2))
                <div class="col-md-12 mb-4">
                    <h5 class="text-muted mb-3"><i class="bi bi-heart-pulse-fill text-danger me-2"></i>Second Medical Details</h5>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <p class="mb-0"><strong>Issued By:</strong> {{ $document->medical_issuedby_2 }}</p>
                            <p class="mb-0"><strong>Class:</strong> {{ $document->medical_class_2 }}</p>
                            <p class="mb-0"><strong>Issue Date:</strong> {{ $document->medical_issuedate_2 }}</p>
                            <p class="mb-0"><strong>Expiry Date:</strong> {{ $document->medical_expirydate_2 }}</p>

                            @if($user->medical_2_adminRequired == 1 && $document->medical_file_2)
                            <a href="{{ Storage::url($document->medical_file_2) }}" class="btn btn-outline-danger btn-sm" target="_blank">View File</a>

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input verify-toggle" type="checkbox" id="medical2_verify"
                                        data-user-id="{{ encode_id($user->id) }}" data-type="medical_2"
                                        {{ $document->medical_verified_2 ? 'checked disabled' : '' }}>
                                    <label class="form-check-label" for="medical2_verify">{{ $document->medical_verified_2 ? 'Verified' : 'Mark as Verified' }}</label>
                                </div>
                            @endif

                            @if($document->medical_verified_2)
                                <button class="btn btn-danger btn-sm invalidate-btn" data-user-id="{{ $user->id }}" data-type="medical_2">Invalidate</button>
                            @endif
                        </div>                  
                </div>
                @endif


            <hr class="my-4">

            <!-- Ratings & Organization -->
            <div class="row g-4">
                <div class="col-md-12 mt-4">
                    <h4 class="text-dark mb-4">
                        <i class="bi bi-award-fill text-warning me-2"></i> User Rating Files
                    </h4>

                    <div class="d-flex flex-wrap">
                        @if($user->usrRatings->count())
                            @foreach($user->usrRatings as $rating)
                                <div class="card shadow-sm border-0 me-3 mb-3" style="width: 18rem;">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3 text-primary">
                                            {{ $rating->rating->name ?? 'Unknown Rating' }}
                                        </h5>
                                        <ul class="list-unstyled mb-3">
                                            <li><strong>Issue Date:</strong> {{ $rating->issue_date ?? 'N/A' }}</li>
                                            <li><strong>Expiry Date:</strong> {{ $rating->expiry_date ?? 'N/A' }}</li>
                                        </ul>

                                        @if($rating->file_path)
                                            <a href="{{ Storage::url($rating->file_path) }}" target="_blank"
                                            class="btn btn-outline-primary btn-sm me-2">
                                                <i class="bi bi-file-earmark-arrow-down"></i> View File
                                            </a>

                                                <div class="form-check form-switch mt-3">
                                                    <input class="form-check-input verify-toggle" type="checkbox"
                                                        id="rating_verify_{{ $rating->id }}"
                                                        data-user-id="{{ encode_id($user->id) }}"
                                                        data-type="user_rating"
                                                        data-rating-id="{{ encode_id($rating->id) }}"
                                                        {{ $rating->admin_verified ? 'checked disabled' : '' }}>
                                                    <label class="form-check-label" for="rating_verify_{{ $rating->id }}">
                                                        {{ $rating->admin_verified ? 'Verified' : 'Mark as Verified' }}
                                                    </label>
                                                </div>
                                        @else
                                            <p class="text-muted">No rating file available.</p>
                                        @endif

                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> No user rating records available.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <h5 class="text-muted mb-2">
                        <i class="bi bi-building text-secondary me-2"></i> Organization Unit
                    </h5>
                    <div class="p-3 border rounded bg-light">
                        {{ $user->organization->org_unit_name ?? 'N/A' }}
                    </div>
                </div>
            </div>



            <!-- Custom Fields -->
            @if($user->custom_field_name && $user->custom_field_value)
            <hr class="my-4">
            <h5 class="text-muted"><i class="bi bi-list-check me-2"></i>Custom Field</h5>
            <p><strong>{{ $user->custom_field_name }}:</strong> {{ $user->custom_field_value }}</p>
            @endif
        </div>
    </div>
</div>

</div>
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
    $(".verify-toggle").on('change', function() {
        var userId = $(this).data("user-id");
        var ratingId = $(this).data("rating-id");
        var docType = $(this).data("type"); // Example: passport or licence
        var isChecked = $(this).prop("checked") ? 1 : 0;
        $.ajax({
            url: '{{ url("/users/verify") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                userId: userId,
                ratingId: ratingId,
                documentType: docType,
                verified: isChecked
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    // Show success message
                    alert(response.success);
                    location.reload();
                } else {
                    // Show general error if success is not returned
                    alert("Something went wrong!");
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422) {
                    // Validation error occurred
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = '';
                    $.each(errors, function(key, messages) {
                        errorMessages += messages.join('\n') + '\n';
                    });
                    alert(errorMessages); // Display all validation errors
                } else {
                    // Handle other errors
                    alert("An unexpected error occurred.");
                }
            }
        }); 
    });

})


$(document).ready(function () {
    $('.invalidate-btn').on('click', function () {
        if (!confirm('Are you sure you want to invalidate this document?')) return;

        const userId = $(this).data('user-id');
        const type = $(this).data('type');

        $.ajax({
            url: `{{ route('user.invalidateDocument') }}`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_id: userId,
                document_type: type
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Failed to invalidate.');
                }
            },
            error: function () {
                alert('An error occurred while processing the request.');
            }
        });
    });
});

</script>

@endsection