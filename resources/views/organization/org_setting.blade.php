@section('title', 'Organization General setting')
@section('sub-title', 'Organization General setting')
@extends('layout.app')

@section('content')

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 54px;
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
        transform: translateX(25px);
    }

    .switch-input:checked + .switch-button .switch-button-left {
        transform: translateX(-100%);
        opacity: 0;
    }

    .switch-input:checked + .switch-button .switch-button-right {
        transform: translateX(0);
        opacity: 1;
    }

    .teach-switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }

    .teach-slider {
        position: absolute;
        cursor: pointer;
        background-color: #ccc;
        border-radius: 30px;
        inset: 0;
        transition: 0.3s;
    }

    .teach-slider::before {
        content: "";
        position: absolute;
        height: 18px;
        width: 18px;
        left: 3px;
        top: 3px;
        background-color: #fff;
        border-radius: 50%;
        transition: 0.3s;
    }

    .teach-switch-input{
        display: none;
    }
    
    .teach-switch-input:checked + .teach-slider {
        background-color: #28a745;
    }

    .teach-switch-input:checked + .teach-slider::before {
        transform: translateX(22px);
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
            <button class="btn btn-primary" id="manage_teachtrack">Manage Teach Track</button> 
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
                                <span class="switch-button-left"></span>
                                <span class="switch-button-right"></span>
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
                                <span class="switch-button-left"></span>
                                <span class="switch-button-right"></span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-4 col-form-label">
                        Enable Licence Validation
                            <i class="bi bi-info-circle-fill text-primary ms-1"
                                data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                title="When enabled, an email notification will be sent while booking a resource during calendar booking.">
                            </i>
                    </label>
                    <div class="col-sm-8">
                        <label class="switch"> 
                            <input type="checkbox"
                                   id="enable_licence_validation"
                                   name="enable_licence_validation"
                                   value="1"
                                   class="switch-input"
                                   {{ optional($OuSetting)->enable_licence_validation == 1 ? 'checked' : '' }}>
                            <div class="switch-button">
                                <span class="switch-button-left"></span>
                                <span class="switch-button-right"></span>
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

            <!-- TeachTrack Modal -->
            <div class="modal fade" id="teachtrackModal" tabindex="-1">
                <div class="modal-dialog modal-md modal-dialog-centered">
                    <div class="modal-content rounded-3 shadow">

                        <!-- Header -->
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Manage TeachTrack</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body px-4 pt-2">

                            <!-- General Settings -->
                            <div class="mb-4">
                                <h6 class="fw-semibold text-muted mt-3 mb-3">General Settings</h6>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="mb-0">Enable TeachTrack</label>

                                    <input type="hidden" name="teachtrack_enabled" value="0">

                                    <label class="teach-switch mb-0">
                                        <input type="checkbox"
                                            id="teachtrack_enabled"
                                            class="teach-switch-input"
                                            {{ optional($OuSetting)->teachtrack_enabled == 1 ? 'checked' : '' }}>
                                        <span class="teach-slider"></span>
                                    </label>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">Enable Email Alerts</label>

                                    <input type="hidden" name="teachtrack_email_enabled" value="0">

                                    <label class="teach-switch mb-0">
                                        <input type="checkbox"
                                            id="teachtrack_email_enabled"
                                            class="teach-switch-input"
                                            {{ optional($OuSetting)->teachtrack_email_enabled == 1 ? 'checked' : '' }}>
                                        <span class="teach-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <hr>

                            <!-- Configuration -->
                            <div>
                                <h6 class="fw-semibold text-muted mb-3">Configuration</h6>

                                <div class="mb-3">
                                    <label class="form-label">Validity (Months)</label>
                                    <input type="number"
                                        id="teachtrack_validity_months"
                                        class="form-control"
                                        placeholder="e.g. 12"
                                        value="{{ optional($OuSetting)->teachtrack_validity_months ?? 12 }}">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label">Alert Before (Days)</label>
                                    <input type="number"
                                        id="teachtrack_alert_days"
                                        class="form-control"
                                        placeholder="e.g. 30"
                                        value="{{ optional($OuSetting)->teachtrack_alert_days ?? 30 }}">
                                </div>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary px-4" id="saveTeachTrack">Save</button>
                        </div>

                    </div>
                </div>
            </div>

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

    $('#manage_teachtrack').on('click', function () {
        $('#teachtrackModal').modal('show');
    });

    $('#saveTeachTrack').on('click', function () {

        let data = {
            organization_unit_id: "{{ $ou_id }}",
            teachtrack_enabled: $('#teachtrack_enabled').is(':checked') ? 1 : 0,
            teachtrack_validity_months: $('#teachtrack_validity_months').val(),
            teachtrack_alert_days: $('#teachtrack_alert_days').val(),
            teachtrack_email_enabled: $('#teachtrack_email_enabled').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: "{{ url('/store/teachtrack-settings') }}",
            type: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                if (res.success) {
                    $('#teachtrackModal').modal('hide');
                    location.reload();
                }
            },
            error: function (xhr) {
                console.log(xhr.responseText);
                alert('Failed to save TeachTrack settings');
            }
        });

    });


    $('#teachtrack_enabled').on('change', function () {
        let isEnabled = $(this).is(':checked');

        // Disable/enable dependent fields
        $('#teachtrack_validity_months').prop('disabled', !isEnabled);
        $('#teachtrack_alert_days').prop('disabled', !isEnabled);
        $('#teachtrack_email_enabled').prop('disabled', !isEnabled);

        // IMPORTANT: force OFF when TeachTrack is disabled
        if (!isEnabled) {
            $('#teachtrack_email_enabled').prop('checked', false);
        }
    }).trigger('change');
});
</script>
@endsection
