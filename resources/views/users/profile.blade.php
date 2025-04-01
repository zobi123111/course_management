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
</style>


<style>
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
                    <form id="userProfileForm">
                        @csrf
                        <div class="row">
                            <!-- First Name -->
                            <div class="form-group col-sm-6">
                                <label for="firstName"><strong>First Name</strong></label>
                                <input type="hidden" id="id" name="id" class="form-control" value="{{ $user->id }}">
                                <input type="text" id="firstName" name="firstName" class="form-control"
                                    value="{{ $user->fname }}" readonly>
                            </div>

                            <!-- Last Name -->
                            <div class="form-group col-sm-6">
                                <label for="lastName"><strong>Last Name</strong></label>
                                <input type="text" id="lastName" name="lastName" class="form-control"
                                    value="{{ $user->lname }}" readonly>
                            </div>


                        </div>

                        <div class="row">

                            <!-- Email -->
                            <div class="form-group col-sm-6">
                                <label for="email"><strong>Email</strong></label>
                                <input type="email" id="email" name="email" class="form-control"
                                    value="{{ $user->email }}" readonly>
                            </div>

                            <!-- Currency -->
                            @if ($user->currency_required == 1)
                            <div class="form-group col-sm-6">
                                <label for="currency" class="form-label"><strong>Currency <span
                                            class="text-danger">*</span> </strong></label>
                                <input type="text" name="currency" id="currency"
                                    value="{{ $user->currency ? $user->currency : ''}}" class="form-control"
                                    placeholder="Enter Currency">
                                <div id="currency_error_up" class="text-danger error_e"></div>
                            </div>
                            @endif

                        </div>

                        <div class="row">
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

                            <!-- Licence -->
                            @if ($user->licence_required == 1)
                            <div class="col-sm-6">
                                <label for="licence_checkbox" class="form-label">
                                    <strong>Licence <span class="text-danger">*</span></strong>
                                    @if ($user->licence_verified)
                                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                    @endif
                                </label>
                                <input type="text" name="licence" id="licence"
                                    value="{{ $user->licence ? $user->licence : ''}}" placeholder="Enter Licence Number"
                                    class="form-control" {{ $user->licence ? 'disabled' : '' }}>
                                <div id="licence_error_up" class="text-danger error_e"></div>
                                <label for="licence_expiry_date" class="form-label mt-3"><strong>Expiry Date <span
                                            class="text-danger">*</span></strong></label>
                                <input type="date" name="licence_expiry_date" id="licence_expiry_date"
                                    value="{{ $user->licence_expiry_date ?? '' }}" class="form-control mt-3"
                                    {{ $user->licence_expiry_date ? 'disabled' : '' }}>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="non_expiring_licence"
                                        name="non_expiring_licence" value="1"
                                        {{ $user->licence_non_expiring ? 'checked disabled' : '' }}>
                                    <label class="form-check-label" for="non_expiring_licence"><strong>Non-Expiring
                                            Licence</strong></label>
                                </div>
                                <div id="licence_expiry_date_error_up" class="text-danger error_e"></div>
                                <input type="file" name="licence_file" id="licence_file" class="form-control mt-3"
                                    accept=".pdf,.jpg,.jpeg,.png" {{ $user->licence_file ? 'disabled' : '' }}>
                                <div id="licence_file_error_up" class="text-danger error_e"></div>
                                <input type="hidden" name="old_licence_file" value="{{ $user->licence_file }}">

                                @if ($user->licence_file)
                                <div class="mt-3">
                                    <a href="{{ asset('storage/' . $user->licence_file) }}" target="_blank"
                                        class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                        style="border-radius: 6px; padding: 6px 10px; font-size: 14px; font-weight: 500; width: fit-content;">
                                        <i class="bi bi-file-earmark-text me-1" style="font-size: 16px;"></i> View
                                        Licence
                                    </a>
                                </div>
                                @endif
                            </div>
                            @endif


                            <!-- Passport -->
                            @if ($user->passport_required == 1)
                            <div class="form-group col-sm-6">
                                <label for="passport_checkbox" class="form-label">
                                    <strong>Passport <span class="text-danger">*</span> </strong>
                                    @if($user->passport_verified)
                                    <span class="text-success"><i class="bi bi-check-circle-fill"></i> Verified</span>
                                    @endif
                                </label>
                                <input type="text" name="passport" id="passport" class="form-control"
                                    value="{{ $user->passport ? $user->passport : ''}}"
                                    placeholder="Enter Passport Number" {{ $user->passport ? 'disabled' : '' }}>
                                <div id="passport_error_up" class="text-danger error_e"></div>

                                <label for="licence_" class="form-label mt-3">
                                    <strong>Expiry Date <span class="text-danger">*</span> </strong>
                                </label>
                                <input type="date" name="passport_expiry_date" id="passport_expiry_date"
                                    value="{{ $user->passport_expiry_date ? $user->passport_expiry_date : ''}}"
                                    class="form-control mt-3" {{ $user->passport_expiry_date ? 'disabled' : '' }}>
                                <div id="passport_expiry_date_error_up" class="text-danger error_e"></div>

                                <input type="file" name="passport_file" id="passport_file" class="form-control mt-3"
                                    accept=".pdf,.jpg,.jpeg,.png" {{ $user->passport_file ? 'disabled' : '' }}>
                                <div id="passport_file_error_up" class="text-danger error_e"></div>

                                <input type="hidden" name="old_passport_file" value="{{ $user->passport_file }}">


                                @if ($user->passport_file)
                                <div class="mt-3">
                                    <a href="{{ asset('storage/' . $user->passport_file) }}" target="_blank"
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
                        @if ($user->medical == 1)

                        <label for="extra_roles" class="form-label">Medical Issued By<span
                                class="text-danger"></span></label>
                        <select class="form-select " name="issued_by" id="issued_by">
                            <option value="">Select Issued By</option>
                            <option value="UKCAA" <?php echo ($user->medical_issuedby == "UKCAA")?'selected':'' ?>>UK CAA</option>
                            <option value="EASA" <?php echo ($user->medical_issuedby == "EASA")?'selected':'' ?>>EASA</option>
                            <option value="FAA" <?php echo ($user->medical_issuedby == "FAA")?'selected':'' ?>>FAA</option>
                        </select>
                        <div id="issued_by_error_up" class="text-danger error_e"></div>

                        <label for="extra_roles" class="form-label">Medical Class<span
                                class="text-danger"></span></label>
                        <select class="form-select " name="medical_class" id="medical_class">
                            <option value="">Select the Class</option>
                            <option value="class1" <?php echo ($user->medical_class == "class1")?'selected':'' ?>>Class 1</option>
                            <option value="class2" <?php echo ($user->medical_class == "class2")?'selected':'' ?>>Class 2</option>
                        </select>
                        <div id="medical_class_error_up" class="text-danger error_e"></div>
                        <label for="extra_roles" class="form-label">Medical Issue Date<span
                                class="text-danger"></span></label>
                        <input type="date" name="medical_issue_date" id="medical_issue_date"
                            class="form-control" placeholder="Medical Issue Date" value="<?php echo isset($user->medical_issuedate) ? $user->medical_issuedate : ''; ?>">
                            <div id="medical_issue_date_error_up" class="text-danger error_e"></div>

                        <label for="extra_roles" class="form-label">Medical Expiry Date<span
                                class="text-danger"></span></label>
                        <input type="date" name="medical_expiry_date" id="medical_expiry_date"
                            class="form-control" placeholder="Medical Expiry Date" value="<?php echo isset($user->medical_expirydate) ? $user->medical_expirydate : ''; ?>">
                            <div id="medical_expiry_date_error_up" class="text-danger error_e"></div>


                        <label for="extra_roles" class="form-label">Medical Detail <span
                                class="text-danger"></span></label>
                        <input type="text" name="medical_detail" id="medical_detail" class="form-control"
                            placeholder="Enter the Detail" value="<?php echo isset($user->medical_restriction) ? $user->medical_restriction : ''; ?>">
                         <div id="medical_detail_error_up" class="text-danger error_e"></div>


                </div>
                @endif

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
// $('#ratingStars .star').on('click', function() {
//         var rating = $(this).data('value');
//         $('#rating_value').val(rating);

//         $('#ratingStars .star').removeClass('active');

//         for (var i = 1; i <= rating; i++) {
//             $('#ratingStars .star[data-value="' + i + '"]').addClass('active');
//         }
//     });

$(document).ready(function() {
    function toggleFields() {
        const isNonExpiringChecked = $('#non_expiring_licence').prop('checked');
        const isExpiryDateFilled = $('#licence_expiry_date').val().trim() !== '';
        if (isExpiryDateFilled) {
            $('#non_expiring_licence').prop('checked', false).parent().hide();
        } else {
            $('#non_expiring_licence').parent().show();
        }

        if (isNonExpiringChecked) {
            $('#licence_expiry_date').val('').hide().prop('required', false);
        } else {
            $('#licence_expiry_date').show().prop('required', true);
        }
    }

    // Initialize the fields on page load
    toggleFields();

    // Event listeners
    $('#non_expiring_licence').change(toggleFields);
    $('#licence_expiry_date').on('input', toggleFields);

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
                $(".loader").fadeOut('slow');

                $('#editUserDataModal').modal('hide');
                $('#update_success_msg').html(`
                    <div class="alert alert-success fade show" role="alert">
                        <i class="bi bi-check-circle me-1"></i>
                        ${response.message}
                    </div>
                    `).stop(true, true).fadeIn();

                setTimeout(function() {
                    $('#update_success_msg').fadeOut('slow');

                }, 5000);

                setTimeout(function() {
                    location.reload();
                }, 2000);
                // location.reload();
            },
            error: function(xhr, status, error) {

                $(".loader").fadeOut("slow");

                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var html = '<p>' + value + '</p>';
                    $('#' + key + '_error_up').html(html);
                });
            }
        });
    });
});
</script>
@endsection