@section('title', 'Organization General setting')
@section('sub-title', 'Organization General setting')
@extends('layout.app')

@section('content')

<style>
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
        background-color: #dc3545;
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

    .switch-button-left {
        left: 30px;
    }

    .switch-button-right {
        right: 30px;
        transform: translateX(100%);
        opacity: 0;
    }

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

    .switch-input:checked + .switch-button {
        background-color: #28a745;
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
</style>

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show">
    {{ session()->get('message') }}
</div>
@endif

<div class="card">
    <div class="create_btn d-flex justify-content-between align-items-center mt-4" style="margin-left:10px"> 
        <div>
            <a href="{{ url('tags') }}?ou_id={{ encode_id($ou_id) }}" class="btn btn-primary me-2 create-button">Manage Tags</a>
            <a href="{{ url('custom-cbta') }}?ou_id={{ encode_id($ou_id) }}" class="btn btn-primary" id="addRating">Manage Competency Grading</a> 
            <a href="{{ url('validation-codes') }}?ou_id={{ encode_id($ou_id) }}" class="btn btn-primary" id="addRating">Manage Validation Code</a> 
        </div>
    </div>
    <div class="container mt-4">
        <div class="card-body">
            <form method="POST" id="ouSettingsForm">
                @csrf
                {{-- Organization Unit --}}
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Organization Unit</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" value="{{ $ou_name }}" readonly>
                        <input type="hidden" name="organization_unit_id" value="{{ $ou_id }}">
                    </div>
                </div>

                {{-- Auto Archive --}}
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">
                        Auto Archive on Training Event Completion
                    </label>
                    <div class="col-sm-8">
                        <select name="auto_archive" id="auto_archive" class="form-control">
                            <option value="">Select the Option</option>
                            <option value="0" {{ (int) optional($OuSetting)->auto_archive == 0 ? 'selected' : '' }}>No</option>
                            <option value="1" {{ (int) optional($OuSetting)->auto_archive == 1 ? 'selected' : '' }}>Yes</option>
                        </select>
                        <div id="auto_archive_error" class="text-danger error_e"></div>
                    </div>
                </div>

                {{-- Archive Months --}}
                <div class="row mb-3 d-none" id="archive_months_div">
                    <label class="col-sm-4 col-form-label">
                        Archive After Months
                            <i class="bi bi-info-circle-fill text-primary ms-1"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                title="User will be automatically archived after the selected number of months once the training event is completed.">
                            </i>
                    </label>
                    <div class="col-sm-8">
                        <select name="archive_after_months" id="archive_after_months" class="form-control">
                            <option value="">Select</option>
                            @for ($i = 1; $i <= 24; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Time Zone  -->
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">
                        Time Zone
                    </label>
                    <div class="col-sm-8">
                        <select name="timezone" class="form-control" class="form-control">
                            <option value="">Select Time Zone</option>
                          @foreach($timezones as $utc => $zones)
                                <optgroup label="{{ $utc }}">
                                    @foreach($zones as $zone)
                                        @php
                                            $tzValue = "({$utc}) {$zone}";
                                        @endphp

                                        <option value="{{ $tzValue }}"
                                            {{ 
                                                (isset($ou) && $ou->timezone === $tzValue) || 
                                                (isset($OuSetting) && $OuSetting->timezone === $tzValue)
                                                ? 'selected' : '' 
                                            }}>
                                            {{ $tzValue }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <div id="timezone_error" class="text-danger error_e"></div>
                    </div>
                </div>

                <hr>

                <h5>User Create/ Update – Custom Fields     
                    <i class="bi bi-info-circle-fill text-primary ms-1"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        title="These fields are only visible while creating and updating users when you enable these checkboxes.">
                    </i>
                </h5> 

                {{-- DOB --}}
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Date of Birth</label>
                    <div class="col-sm-8 d-flex align-items-center">
                        <input class="form-check-input"
                               type="checkbox"
                               id="show_dob"
                               name="show_dob"
                               {{ optional($OuSetting)->show_dob == 1 ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Phone --}}
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Contact Phone Number</label>
                    <div class="col-sm-8 d-flex align-items-center">
                        <input class="form-check-input"
                               type="checkbox"
                               id="show_phone"
                               name="show_phone"
                               {{ optional($OuSetting)->show_phone == 1 ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Email Switch --}}
                <div class="row mb-4">
                    <label class="col-sm-4 col-form-label">
                        Send Email Notification
                            <i class="bi bi-info-circle-fill text-primary ms-1"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                title="When enabled, an email notification will be sent while booking a resource during calendar booking.">
                            </i>
                    </label>
                    <div class="col-sm-8">
                        <input type="hidden" name="send_email" value="0">

                        <label class="switch"> 
                            <input type="checkbox"
                                   id="edit_send_email"
                                   name="send_email"
                                   value="1"
                                   class="switch-input"
                                   {{ optional($OuSetting)->send_email == 1 ? 'checked' : '' }}>
                            <div class="switch-button">
                                <span class="switch-button-left">Send Email</span>
                                <span class="switch-button-right">Not Send Email</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-4 col-form-label">
                        Enable Tacho Fields
                            <i class="bi bi-info-circle-fill text-primary ms-1"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                title="When enabled, an email notification will be sent while booking a resource during calendar booking.">
                            </i>
                    </label>
                    <div class="col-sm-8">
                        <input type="hidden" name="enable_tacho_fields" value="0">

                        <label class="switch"> 
                            <input type="checkbox"
                                   id="edit_enable_tacho_fields"
                                   name="enable_tacho_fields"
                                   value="1"
                                   class="switch-input"
                                   {{ optional($OuSetting)->enable_tacho_fields == 1 ? 'checked' : '' }}>
                            <div class="switch-button">
                                <span class="switch-button-left">Enable Tacho Fields</span>
                                <span class="switch-button-right">Disable Tacho Fields</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="row">
                    <div class="col-sm-8 offset-sm-4">
                        <button type="submit" class="btn btn-primary">
                            Save OU Settings
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection

{{-- JS --}}
@section('js_scripts')
<script>
$(document).ready(function () {

    var autoArchive = "{{ $OuSetting->auto_archive ?? '' }}";
    var archiveMonths = "{{ $OuSetting->archive_after_months ?? '' }}";

    if (autoArchive == '1') {
        $('#archive_months_div').removeClass('d-none');
        $('#archive_after_months').val(archiveMonths);
    } else {
        $('#archive_months_div').addClass('d-none');
        $('#archive_after_months').val('');
    }

    $('#auto_archive').on('change', function () {
        if ($(this).val() == '1') {
            $('#archive_months_div').removeClass('d-none');
            if (!$('#archive_after_months').val()) {
                $('#archive_after_months').val('1');
            }
        } else {
            $('#archive_months_div').addClass('d-none');
            $('#archive_after_months').val('');
        }
    });

    $('#ouSettingsForm').on('submit', function (e) {
        e.preventDefault();
        $('.error_e').html('');

        let formData = $(this).serializeArray();

        formData.push({
            name: 'show_dob',
            value: $('#show_dob').is(':checked') ? 1 : 0
        });

        formData.push({
            name: 'show_phone',
            value: $('#show_phone').is(':checked') ? 1 : 0
        });

        $.ajax({
            url: '/store/org_setting',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success === true) {
                    location.reload();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key + '_error').html('<p>' + value[0] + '</p>');
                    });
                } else {
                    alert('Something went wrong.');
                }
            }
        });
    });

    setTimeout(function () {
        $('#successMessage').fadeOut('fast');
    }, 2000);
});
</script>
@endsection
