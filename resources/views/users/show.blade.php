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
                    <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('/assets/img/No_Image_Available.jpg') }}"
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

            <!-- Additional Details: Passport & License -->
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="text-muted mb-3"><i class="bi bi-passport text-primary me-2"></i>Passport Details</h5>
                    @if($user->passport)
                        <p>Number: {{ $user->passport }}</p>
                        <a href="{{ Storage::url($user->passport_file) }}" class="btn btn-outline-primary btn-sm mb-2" target="_blank">View File</a>
                        @if($user->passport_admin_verification_required==1)
                            <div class="form-check form-switch">
                                <input class="form-check-input verify-toggle" type="checkbox" id="passport_verify" data-user-id="{{ encode_id($user->id) }}" data-type="passport" {{ $user->passport_verified ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="passport_verify">Verified</label>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No passport details available.</p>
                    @endif
                </div>

                <div class="col-md-6">
                    <h5 class="text-muted mb-3"><i class="bi bi-award-fill text-danger me-2"></i>License Details</h5>
                    @if($user->licence)
                        <p>Number: {{ $user->licence }}</p>
                        <a href="{{ Storage::url($user->licence_file) }}" class="btn btn-outline-danger btn-sm mb-2" target="_blank">View File</a>
                        @if($user->licence_admin_verification_required==1)
                            <div class="form-check form-switch">
                                <input class="form-check-input verify-toggle" type="checkbox" id="licence_verify" data-user-id="{{ encode_id($user->id) }}" data-type="licence" {{ $user->licence_verified ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="licence_verify">Verified</label>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No license details available.</p>
                    @endif
                </div>
            </div>

            <!-- Medical Details -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <h5 class="text-muted mb-3"><i class="bi bi-heart-pulse-fill text-danger me-2"></i>Medical Details</h5>
                    @if($user->medical && !empty($user->medical_issuedby) && !empty($user->medical_class) && !empty($user->medical_issuedate))
                        <p>Issued By: {{ $user->medical_issuedby }}</p>
                        <p>Class: {{ $user->medical_class }}</p>
                        <p>Issue Date: {{ $user->medical_issuedate }}</p>
                        <p>Expiry Date: {{ $user->medical_expirydate }}</p>
                        <a href="{{ Storage::url($user->medical_file) }}" class="btn btn-outline-danger btn-sm mb-2" target="_blank">View File</a>
                        @if($user->medical_adminRequired==1)
                            <div class="form-check form-switch">
                                <input class="form-check-input verify-toggle" type="checkbox" id="licence_verify" data-user-id="{{ encode_id($user->id) }}" data-type="medical" {{ $user->medical_verified ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="licence_verify">Verified</label>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No medical details available.</p>
                    @endif
                </div>
            </div>

            <hr class="my-4">

            <!-- Ratings & Organization -->
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="text-muted mb-2"><i class="bi bi-star-fill text-warning me-2"></i>User Ratings</h5>
                    @if($user->rating)
                        <div class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= $user->rating ? 'text-warning' : 'text-muted' }}">&#9733;</span>
                            @endfor
                        </div>
                    @else
                        <p class="text-muted">No ratings available.</p>
                    @endif
                </div>

                <div class="col-md-6">
                    <h5 class="text-muted mb-2"><i class="bi bi-building text-secondary me-2"></i>Organization Unit</h5>
                    <p>{{ $user->organization->org_unit_name ?? 'N/A' }}</p>
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
        var docType = $(this).data("type"); // Example: passport or licence
        var isChecked = $(this).prop("checked") ? 1 : 0;
        console.log(userId);
        console.log(docType);
        console.log(isChecked);

        $.ajax({
            url: '{{ url("/users/verify") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                userId: userId,
                documentType: docType,
                verified: isChecked
            },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    // Show success message
                    alert(response.success);
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
</script>

@endsection