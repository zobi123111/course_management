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

<div class="main_cont_outer">
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert"> 
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif
    @if(checkAllowedModule('users','user.store')->isNotEmpty())
    <div class="create_btn d-flex justify-content-between align-items-center"> 
        <div>
            <a href="#" class="btn btn-primary me-2 create-button" id="createUser" data-toggle="modal"
                data-target="#userModal">Create Users</a>
            @if(auth()->user()->is_owner == 1)
            <a href="{{ route('users.rating') }}" class="btn btn-primary" id="addRating">View Rating</a>
            @endif
            @if(auth()->user()->ou_id != null)
            <a href="{{ route('users.ou_rating') }}" class="btn btn-primary" id="addRating">View OU Rating</a> 
            @endif
            @if(auth()->user()->is_owner == 1)
            <a href="{{ route('archieveUser.index') }}" class="btn btn-primary" id="addRating">Archive User</a>
            @endif
        </div>

        <a href="{{ route('users.document.data') }}" class="btn btn-primary">Document Required Table</a>
    </div>
    @endif
    <div id="update_success_msg"></div>
    <div class="card pt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="user_table">
                    <thead>
                        <tr>
                            <th scope="col">Profile Picture</th>
                            <th scope="col">First Name</th>
                            <th scope="col">Last Name</th>
                            <th scope="col">Email</th>
                            @if(auth()->user()->is_owner == 1)
                            <th scope="col">Position</th>
                            <th scope="col">OU</th>
                            @endif
                            @if(!empty(auth()->user()->ou_id) && auth()->user()->is_owner == 0)
                            <th scope="col">Position</th>
                            @endif
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create User -->
<div class="modal fade" id="userModal" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Create User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="Create_user" enctype="multipart/form-data"
                    class="row g-3 needs-validation">
                    @csrf
                    <div class="row g-3 mb-3">
                        <!-- Bootstrap Grid -->
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">First Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="firstname" class="form-control">
                            <div id="firstname_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                            <input type="text" name="lastname" class="form-control">
                            <div id="lastname_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control">
                            <div id="email_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label">Profile Picture<span
                                    class="text-danger"></span></label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div id="image_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control">
                            <div id="password_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmpassword" class="form-label">Confirm Password<span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                id="confirmpassword">
                            <div id="password_confirmation_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
                            <select name="role_name" class="form-select" id="role">
                                <option value="">Select role</option>
                                @foreach($roles as $val)
                                <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                                @endforeach
                            </select>
                            <div id="role_name_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">

                        </div>
                        {{-- <div class="col-md-6">
                            <label for="extra_roles" class="form-label">Select Multiple Roles<span
                                    class="text-danger"></span></label>
                            <select class="form-select extra_roles" name="extra_roles[]" id="extra_roles" multiple="multiple">
                                <option value="">Select roles</option>
                                @foreach($roles as $val)
                                <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                        @endforeach
                        </select>
                        <div id="extra_roles_error" class="text-danger error_e"></div>
                    </div> --}}

                     <div class="col-md-6">
                         <div class="mt-3">
                        <label for="licence_checkbox" class="form-label">UK Licence</label>
                        <input type="checkbox" name="licence_checkbox" id="licence_checkbox" class="ms-2">

                        <label for="licence_verification_required" class="form-label ms-4">Admin Verification required?</label>
                        <input type="checkbox" name="licence_verification_required" id="licence_verification_required" class="ms-2" value="1">
                        </div>
                        <input type="text" name="licence" id="licence" class="form-control mt-2" style="display: none;" placeholder="Enter UK Licence Number">
                        <div id="licence_error" class="text-danger error_e"></div>

                        <input type="file" name="licence_file" id="licence_file" class="form-control mt-3" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="licence_file_error" class="text-danger error_e"></div>

                        <div id="licence_rating_section" class="mt-3">
                            <label class="form-label">Select Ratings for UK Licence</label>
                            <input type="checkbox" id="uk_licence" />
                            <div id="rating_select_boxes_container" class="mt-2" style="display: none;">
                                <!-- Select boxes will be appended here -->
                            </div>
                            <button type="button" id="add_rating_box" class="btn btn-primary mt-2" style="display: none;">Add Rating</button>
                        </div>
                    </div>
                    <!-- Licence -->
                    <div class="col-md-6">
                        <!-- Enable Licence 2 -->
                        <div class="mt-3" id="license_2">
                            <label for="licence2_checkbox" class="form-label"> EASA Licence</label>
                            <input type="checkbox" name="licence_2_checkbox" id="licence_2_checkbox" value="1" class="ms-2">

                            <label for="licence_2_verification_required" class="form-label ms-4">Admin Verification required?</label>
                            <input type="checkbox" name="licence_2_verification_required" id="licence_2_verification_required" class="ms-2" value="1">
                        </div>

                        <!-- Second Licence Fields -->
                        <div id="second_licence_section" style="display: none;" class="mt-3">
                            <input type="text" name="licence_2" id="licence_2" class="form-control" placeholder="Enter EASA Licence Number">
                            <div id="licence_2_error" class="text-danger error_e"></div>

                            <input type="file" name="licence_file_2" id="licence_file_2" class="form-control mt-3" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="licence_file_2_error" class="text-danger error_e"></div>

                            <!-- Ratings for Licence 2 -->
                            <div id="licence_2_rating_section" class="mt-3">
                                <div id="licence_2_rating_section" class="mt-3">
                                    <label class="form-label">Select Ratings for EASA Licence</label>
                                    <input type="checkbox" id="easa_licence" />
                                    <div id="easa_select_boxes_container" class="mt-2" style="display: none;">
                                        <!-- Select boxes will be appended here -->
                                    </div>
                                    <button type="button" id="easa_add_rating_box" class="btn btn-primary mt-2" style="display: none;">Add Rating</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mt-3">
                        <label for="medical_checkbox" class="form-label">UK Medical</label>
                        <input type="checkbox" name="medical_checkbox" id="medical_checkbox" class="ms-2" value="1">
                        <label for="medical_verification_required" class="form-label ms-4">Admin Verification
                            required?</label>
                        <input type="checkbox" name="medical_verification_required"
                            id="medical_verification_required" class="ms-2" value="1">
                        </div>
                        <div class="medical_issued_div" style="display:none">
                            <label for="extra_roles" class="form-label">Medical Issued By<span
                                    class="text-danger"></span></label>
                            <select class="form-select " name="issued_by" id="issued_by">
                                <option value="">Select Issued By</option>
                                <option value="UKCAA">UK CAA</option>
                                <option value="EASA">EASA</option>
                                <option value="FAA">FAA</option>
                            </select>

                        </div>
                        <div class="medical_class_div" style="display:none">
                            <label for="extra_roles" class="form-label">Medical Class<span
                                    class="text-danger"></span></label>
                            <select class="form-select " name="medical_class" id="medical_class">
                                <option value="">Select the Class</option>
                                <option value="class1">Class 1</option>
                                <option value="class2">Class 2</option>
                            </select>
                            <div id="medical_issue_date_div">
                                <label for="extra_roles" class="form-label">Medical Issue Date<span
                                        class="text-danger"></span></label>
                                <input type="date" name="medical_issue_date" id="medical_issue_date"
                                    class="form-control" placeholder="Medical Issue Date">
                            </div>
                            <div id="medical_expiry_date_div">
                                <label for="extra_roles" class="form-label">Medical Expiry Date<span
                                        class="text-danger"></span></label>
                                <input type="date" name="medical_expiry_date" id="medical_expiry_date"
                                    class="form-control" placeholder="Medical Expiry Date">
                            </div>
                            <div id="medical_detail_div">
                                <label for="extra_roles" class="form-label">Medical Detail <span
                                        class="text-danger"></span></label>
                                <!-- <input type="text" name="medical_detail" id="medical_detail" class="form-control"
                                        placeholder="Enter the Detail"> -->
                                <textarea name="medical_detail" id="medical_detail" class="form-control"
                                    placeholder="Enter the Detail"></textarea>
                            </div>
                            <div id="medical_file_div">
                                <label for="extra_roles" class="form-label">Medical Upload <span
                                        class="text-danger"></span></label>
                                <input type="file" name="medical_file" id="medical_file"
                                    class="form-control" placeholder="Enter the Detail">
                            </div>

                            <button type="button" id="add_second_medical_btn" class="btn btn-secondary mt-3" style="display: none;">
                                Second Medical
                            </button>

                        </div>
                    </div>
                    <!--  // Medical  -->
                    <div class="col-md-6">
                        <div class="mt-3" id="medical_2">
                            <label for="medical_2_checkbox" class="form-label">EASA Medical</label>
                            <input type="checkbox" name="medical_2_checkbox" id="medical_2_checkbox" class="ms-2" value="1">
                            <label for="medical_2_verification_required" class="form-label ms-4">Admin Verification
                                required?</label>
                            <input type="checkbox" name="medical_2_verification_required"
                                id="medical_2_verification_required" class="ms-2" value="1">
                        </div>
                        <div id="second_medical_section" class="mt-3" style="display: none;">
                            <div class="medical_issued_div_2">
                                <label for="issued_by_2" class="form-label">Medical Issued By</label>
                                <select class="form-select" name="issued_by_2" id="issued_by_2">
                                    <option value="">Select Issued By</option>
                                    <option value="UKCAA">UK CAA</option>
                                    <option value="EASA">EASA</option>
                                    <option value="FAA">FAA</option>
                                </select>
                            </div>

                            <div class="medical_class_div_2 mt-3">
                                <label for="medical_class_2" class="form-label">Medical Class</label>
                                <select class="form-select" name="medical_class_2" id="medical_class_2">
                                    <option value="">Select the Class</option>
                                    <option value="class1">Class 1</option>
                                    <option value="class2">Class 2</option>
                                </select>
                            </div>

                            <div class="mt-3">
                                <label for="medical_issue_date_2" class="form-label">Medical Issue Date</label>
                                <input type="date" name="medical_issue_date_2" id="medical_issue_date_2" class="form-control">
                            </div>

                            <div class="mt-3">
                                <label for="medical_expiry_date_2" class="form-label">Medical Expiry Date</label>
                                <input type="date" name="medical_expiry_date_2" id="medical_expiry_date_2" class="form-control">
                            </div>

                            <div class="mt-3">
                                <label for="medical_detail_2" class="form-label">Medical Detail</label>
                                <textarea name="medical_detail_2" id="medical_detail_2" class="form-control"
                                    placeholder="Enter the Detail"></textarea>
                            </div>

                            <div class="mt-3">
                                <label for="medical_file_2" class="form-label">Medical Upload</label>
                                <input type="file" name="medical_file_2" id="medical_file_2" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>

                    <!-- Passport -->
                    <div class="col-md-6">
                        <div class="mt-3">
                        <label for="passport_checkbox" class="form-label">Passport</label>
                        <input type="checkbox" name="passport_checkbox" id="passport_checkbox" class="ms-2">
                        <label for="passport_verification_required" class="form-label ms-4">Admin Verification
                            required?</label>
                        <input type="checkbox" name="passport_verification_required"
                            id="passport_verification_required" class="ms-2" value="1">
                            </div>
                        <input type="text" name="passport" id="passport" class="form-control" style="display: none;"
                            placeholder="Enter Passport Number">
                        <div id="passport_error" class="text-danger error_e"></div>
                        <input type="file" name="passport_file" id="passport_file" class="form-control mt-3"
                            style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="passport_file_error" class="text-danger error_e"></div>
                    </div>

        <!-- Custom Field -->
        <div class="col-md-6">
            <div class="mt-3">
            <label for="custom_field_checkbox" class="form-label">Custom Field</label>
            <input type="checkbox" name="custom_field_checkbox" id="custom_field_checkbox" class="ms-2" value="1">
            <label for="customField_verification_required" class="form-label ms-4">Admin Verification
                required?</label>
            <input type="checkbox" name="customField_verification_required"
                id="customField_verification_required" class="ms-2" value="1">
                </div>
        </div>
        <div>
            <label for="customfield_filelabel" id="customfield_filelabel" class="form-label"
                style="display: none;">Date</label>
            <input type="checkbox" name="custom_date_checkbox" id="custom_date_checkbox" class="m-2"
                style="display: none;">
            <label for="customfield_textlabel" id="customfield_textlabel" class="form-label"
                style="display: none;">Text</label>
            <input type="checkbox" name="custom_text_checkbox" id="custom_text_checkbox" class="ms-2"
                style="display: none;">
            <div class="col-md-6">
                <input type="date" name="custom_field_date" id="custom_date" class="form-control mt-3"
                    style="display: none;">
                <input type="text" name="custom_field_text" id="custom_text" class="form-control mt-3"
                    placeholder="Enter the Text" style="display: none;">
            </div>
        </div>
        <div id="customfield_error" class="text-danger error_e"></div>

        @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
        <div class="col-md-6">
            <label for="email" class="form-label">Select Org Unit<span
                    class="text-danger">*</span></label>
            <select class="form-select" name="ou_id" aria-label="Default select example">
                <option value="">Select Org Unit</option>
                @foreach($organizationUnits as $val)
                <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                @endforeach
            </select>
            <div id="ou_id_error" class="text-danger error_e"></div>
        </div>
        @endif
        <div class="col-md-6">
            <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
            <select class="form-select" name="status" aria-label="Default select example">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
            </select>
            <div id="status_error" class="text-danger error_e"></div>
        </div>
        <div class="col-md-6">
            <label for="archive_status" class="form-label">Archive Status</label>
            <select class="form-select" name="archive_status" aria-label="Default select example">
                <option value="0" selected>UnArchive</option>
                <option value="1">Archive</option>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
        <a href="#" type="button" id="saveuser" class="btn btn-primary sbt_btn">Save </a>
    </div>
    <div class="loader" style="display: none;"></div>
    </form>
</div>
</div>
</div>
</div>
<!--End of create user-->

<!-- Edit user -->
<div class="modal fade" id="editUserDataModal" tabindex="-1" role="dialog" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="editUserDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserDataModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="editUserForm" class="row g-3 needs-validation">
                    @csrf
                    <div class="row g-3 mb-3">
                        <!-- Bootstrap Grid -->
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">First Name<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="edit_firstname" class="form-control">
                            <input type="hidden" name="edit_form_id" id="edit_firstname_error_up" class="form-control">

                            <div id="fname_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                            <input type="text" name="edit_lastname" class="form-control">
                            <div id="edit_lastname_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                            <input type="email" name="edit_email" class="form-control">
                            <div id="edit_email_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control">
                            <div id="password_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmpassword" class="form-label">Confirm Password<span
                                    class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control"
                                id="confirmpassword">
                            <div id="password_confirmation_error_up" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="lastname" class="form-label">Profile Picture<span
                                    class="text-danger"></span></label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div id="image_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
                            <select name="edit_role_name" class="form-select" id="edit_role">
                                <option value="">Select role</option>
                                @foreach($roles as $val)
                                <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                                @endforeach
                            </select>
                            <div id="edit_role_name_error_up" class="text-danger error_e"></div>
                        </div>
                        {{-- <div class="col-md-6">
                            <label for="extra_roles" class="form-label">Select Multiple Roles<span
                                    class="text-danger"></span></label>
                            <select class="form-select extra_roles" name="extra_roles[]" id="edit_extra_roles" multiple="multiple">
                                <option value="">Select roles</option>
                                @foreach($roles as $val)
                                <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                        @endforeach
                        </select>
                        <div id="extra_roles_error_up" class="text-danger error_e"></div>
                    </div> --}}
                    <!-- Update Password Checkbox -->
                    <div class="col-md-6">
                        <div class="">
                        <label for="edit_update_password_checkbox" class="form-label">Password Change on next
                            logon</label>
                        <input type="checkbox" name="edit_update_password_checkbox"
                            id="edit_update_password_checkbox">
                        <input type="hidden" name="edit_update_password" id="edit_update_password" value="0">
                    </div>
                    </div>


                    <!-- Licence -->
                    <div class="col-md-6">
                        <div class="mt-3">
                        <label for="edit_licence_checkbox" class="form-label">UK Licence</label>
                        <input type="checkbox" name="edit_licence_checkbox" value="1" id="edit_licence_checkbox" class="ms-2">

                        <label for="edit_licence_verification_required" class="form-label ms-4">Admin Verification required?</label>
                        <input type="checkbox" name="edit_licence_verification_required" id="edit_licence_verification_required" class="ms-2" value="1">
                        </div>
                        <input type="text" name="edit_licence" id="edit_licence" class="form-control" style="display: none;" placeholder="Enter UK Licence Number">
                        <div id="edit_licence_error_up" class="text-danger error_e"></div>
                        <input type="file" name="edit_licence_file" id="edit_licence_file" class="form-control mt-3" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="edit_licence_file_error_up" class="text-danger error_e"></div>

                        <div id="edit_licence_rating_section" class="mt-3" style="display: none;">
                            <label class="form-label">Select Ratings for UK Licence</label>
                            <input type="checkbox" id="edit_uk_licence" />
                            <div id="edit_rating_select_boxes_container" class="mt-2" style="display: none;">
                                <!-- Select boxes will be appended here -->
                            </div>
                            <button type="button" id="edit_add_rating_box" class="btn btn-primary mt-2" style="display: none;">Add Rating</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                            <div class="mt-3" id="edit_license2">
                            <label for="edit_licence_checkbox" class="form-label"> EASA Licence</label>
                            <input type="checkbox" name="edit_licence_2_checkbox" value="1" id="edit_licence_2_checkbox" class="ms-2">

                            <label for="edit_licence_verification_required" class="form-label ms-4">Admin Verification required?</label>
                            <input type="checkbox" name="edit_licence_2_verification_required" id="edit_licence_2_verification_required" class="ms-2" value="1">
                        </div>
                        <div id="edit_second_licence_section" style="display: none;" class="mt-3">
                            <input type="text" name="edit_licence_2" id="edit_licence_2" class="form-control edit_licence_2" placeholder="Enter EASA Licence Number">
                            <div id="edit_licence_2_error" class="text-danger error_e"></div>

                            <input type="file" name="edit_licence_file_2" id="edit_licence_file_2" class="form-control mt-3" accept=".pdf,.jpg,.jpeg,.png">
                            <div id="edit_licence_file_2_error" class="text-danger error_e"></div>
                        </div>

                           <div id="edit_licence_2_rating_section" class="mt-3">
                                <label class="form-label">Select Ratings for EASA Licence</label>
                                <input type="checkbox" id="licence_2_ratings" />
                                <div id="licence_2_ratings_container" class="mt-2" style="display: none;">
                                    <!-- Select boxes will be appended here -->
                                </div>
                                <button type="button" id="edit_licence_2_ratings" class="btn btn-primary mt-2" style="display: none;">Add Rating</button>
                            </div>
                    </div>
                    <!--   // Medical  -->
                    <div class="col-md-6">
                        <div class="mt-3">
                        <label for="licence_checkbox" class="form-label">UK Medical</label>
                        <input type="checkbox" name="editmedical_checkbox" id="editmedical_checkbox" class="ms-2"
                            value="1">
                        <label for="licence_verification_required" class="form-label ms-4">Admin Verification
                            required?</label>
                        <input type="checkbox" name="editmedical_verification_required"
                            id="editmedical_verification_required" class="ms-2" value="1">
                            </div>
                        <div class="editmedical_issued_div" style="display:none">
                            <label for="extra_roles" class="form-label">Medical Issued By<span
                                    class="text-danger"></span></label>
                            <select class="form-select " name="editissued_by" id="editissued_by">
                                <option value="">Select Issued By</option>
                                <option value="UKCAA">UK CAA</option>
                                <option value="EASA">EASA</option>
                                <option value="FAA">FAA</option>
                            </select>
                        </div>
                        <div class="editmedical_class_div" style="display:none">
                            <label for="extra_roles" class="form-label">Medical Class<span
                                    class="text-danger"></span></label>
                            <select class="form-select " name="editmedical_class" id="editmedical_class">
                                <option value="">Select the Class</option>
                                <option value="class1">Class 1</option>
                                <option value="class2">Class 2</option>
                            </select>
                            <div id="editmedical_issue_date_div">
                                <label for="extra_roles" class="form-label">Medical Issue Date<span
                                        class="text-danger"></span></label>
                                <input type="date" name="editmedical_issue_date" id="editmedical_issue_date"
                                    class="form-control" placeholder="Medical Issue Date">
                            </div>
                            <div id="editmedical_expiry_date_div">
                                <label for="extra_roles" class="form-label">Medical Expiry Date<span
                                        class="text-danger"></span></label>
                                <input type="date" name="editmedical_expiry_date" id="editmedical_expiry_date"
                                    class="form-control" placeholder="Medical Expiry Date">
                            </div>
                            <div id="editmedical_detail_div">
                                <label for="extra_roles" class="form-label">Medical Detail <span
                                        class="text-danger"></span></label>
                                <!-- <input type="text" name="editmedical_detail" id="editmedical_detail"
                                        class="form-control" placeholder="Enter the Detail"> -->
                                <textarea name="editmedical_detail" id="editmedical_detail" class="form-control" placeholder="Enter the Detail"></textarea>
                            </div>
                            <div id="editmedical_detail_div">
                                <label for="extra_roles" class="form-label">Medical Upload <span
                                        class="text-danger"></span></label>
                                <input type="file" name="editmedical_file" id="editmedical_file"
                                    class="form-control" placeholder="Enter the Detail">
                            </div>
                            <!-- <button type="button" id="edit_second_medical_btn" class="btn btn-secondary mt-3">
                                    Second Medical
                                </button> -->
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mt-3" id="edit_medical_2">
                            <label for="licence_checkbox" class="form-label">EASA Medical</label>
                            <input type="checkbox" name="edit_medical_2_checkbox" id="edit_medical_2_checkbox" class="ms-2" value="1">
                            <label for="licence_verification_required" class="form-label ms-4">Admin Verification
                                required?</label>
                            <input type="checkbox" name="edit_medical_2_verification_required"
                                id="edit_medical_2_verification_required" class="ms-2" value="1">
                        </div>


                        <!-- Second Medical Fields -->
                        <div id="edit_second_medical_section" style="display: none;" class="mt-3">

                            <!-- <div class="no-left-margin">
                                <label for="licence_verification_required" class="form-label ms-4">Admin Verification required?</label>
                                <input type="checkbox" name="editmedical_verification_required_2" id="editmedical_verification_required_2" class="ms-2" value="1">
                            </div> -->

                            <label class="form-label">EASA Medical Issued By</label>
                            <select class="form-select" name="editissued_by_2" id="editissued_by_2">
                                <option value="">Select Issued By</option>
                                <option value="UKCAA">UK CAA</option>
                                <option value="EASA">EASA</option>
                                <option value="FAA">FAA</option>
                            </select>

                            <label class="form-label mt-2">EASA Medical Class</label>
                            <select class="form-select" name="editmedical_class_2" id="editmedical_class_2">
                                <option value="">Select the Class</option>
                                <option value="class1">Class 1</option>
                                <option value="class2">Class 2</option>
                            </select>

                            <label class="form-label mt-2">EASA Medical Issue Date</label>
                            <input type="date" name="editmedical_issue_date_2" id="editmedical_issue_date_2" class="form-control">

                            <label class="form-label mt-2">EASA Medical Expiry Date</label>
                            <input type="date" name="editmedical_expiry_date_2" id="editmedical_expiry_date_2" class="form-control">

                            <label class="form-label mt-2">EASA Medical Detail</label>
                            <textarea name="editmedical_detail_2" id="editmedical_detail_2" class="form-control" placeholder="Enter the Detail"></textarea>

                            <label class="form-label mt-2">EASA Medical Upload</label>
                            <input type="file" name="editmedical_file_2" id="editmedical_file_2" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <!-- Passport -->
                    <div class="col-md-6">
                        <label for="edit_passport_checkbox" class="form-label">Passport</label>
                        <input type="checkbox" name="edit_passport_checkbox" id="edit_passport_checkbox"
                            class="ms-2">
                        <label for="edit_passport_verification_required" class="form-label ms-4">Admin Verification
                            required?</label>
                        <input type="checkbox" name="edit_passport_verification_required"
                            id="edit_passport_verification_required" class="ms-2" value="1">
                        <input type="text" name="edit_passport" id="edit_passport" class="form-control"
                            style="display: none;" placeholder="Enter Passport Number">
                        <div id="edit_passport_error_up" class="text-danger error_e"></div>
                        <input type="file" name="edit_passport_file" id="edit_passport_file"
                            class="form-control mt-3" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="edit_passport_file_error_up" class="text-danger error_e"></div>
                    </div>



                    <!-- Currency (Optional) -->
                    <!-- <div class="col-md-6">
                            <label for="edit_currency" class="form-label">Currency</label>
                            <input type="checkbox" name="edit_currency_checkbox" id="edit_currency_checkbox"
                                class="ms-2">
                            <input type="text" name="edit_currency" id="edit_currency" class="form-control"
                                style="display: none;" placeholder="Enter Currency">
                            <div id="edit_currency_error_up" class="text-danger error_e"></div>
                    </div> -->

                    <!-- Custom Field -->
                    <div class="col-md-6">
                        <label for="custom_field_checkbox" class="form-label">Custom Field</label>
                        <input type="checkbox" name="custom_field_checkbox" id="editcustom_field_checkbox"
                            class="ms-2" value="1">
                        <label for="customField_verification_required" class="form-label ms-4">Admin Verification
                            required?</label>
                        <input type="checkbox" name="edit_custom_field_verification_required"
                            id="edit_custom_field_verification_required" class="ms-2" value="1">
                    </div>

                    <div class="col-md-6">
                        <label for="customfield_filelabel" id="editcustomfield_datelabel" class="form-label"
                            style="display: none;">Date</label>
                        <input type="checkbox" name="editcustom_date_checkbox" id="editcustom_date_checkbox"
                            style="display: none;" class="ms-2">
                        <label for="customfield_textlabel" id="editcustomfield_textlabel" class="form-label"
                            style="display: none;">Text</label>
                        <input type="checkbox" name="editcustom_text_checkbox" id="editcustom_text_checkbox"
                            class="ms-2" style="display: none;">
                        <div class="col-md-6">
                            <input type="date" name="editcustom_field_date" id="editcustom_date"
                                class="form-control mt-3" style="display: none;">
                            <input type="text" name="editcustom_field_text" id="editcustom_text"
                                class="form-control mt-3" style="display: none;" placeholder="Enter the Text">
                        </div>
                    </div>
                    </div>

                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="col-md-6">
                        <label for="email" class="form-label">Select Org Unit<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" id="edit_ou_id"
                            aria-label="Default select example">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error_up" class="text-danger error_e"></div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="edit_status"
                            aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6" style="display:none" id="archiveStatus_col">
                        <label for="archive_status" class="form-label">Archive Status</label>
                        <select class="form-select" name="archive_status" id="archive_status" aria-label="Default select example">
                            <option value="0" selected>UnArchive</option>
                            <option value="1">Archive</option>
                        </select>
                    </div>
            </div>
            <div class="modal-footer">
                <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                <a href="#" type="button" id="updateForm" class="btn btn-primary sbt_btn">Update</a>
            </div>

            <div class="loader" style="display: none;"></div>

            </form>
        </div>
    </div>
</div>
</div>
<!--End of Edit user-->

<!--Delete  Modal -->
<form action="{{ url('/users/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="id" id="userid" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary user_delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End of Delete Model -->
@endsection

@section('js_scripts')

<script>
    let selectBoxIndex = 0;
    $(document).ready(function() { 
        // Edit licence 2 
        $('#licence_2_ratings').on('change', function() { 
            if ($(this).is(':checked')) {
                $('#licence_2_ratings_container').show();
                $('#edit_licence_2_ratings').show();
                $('#licence_2_ratings_container').empty(); // reset

                $('#edit_licence_2_ratings').trigger('click'); // add one by default
            } else {
                $('#licence_2_ratings_container').empty().hide();
                $('#edit_licence_2_ratings').hide();
            }
        });



        $('#edit_licence_2_ratings').on('click', function() {
            let index = licence2_selectBoxIndex++;
            let selectBoxHtml = `
            <div class="rating-select-group border p-3 mb-3 rounded" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button type="button" class="btn btn-danger btn-sm remove-rating-box" data-index="${index}">Remove</button>
                </div>

                <label class="form-label">Rating</label>
                <select class="form-select parent-rating mb-2" name="licence_2_ratings[${index}][parent]" data-index="${index}" >
                    <option value="">Select Parent</option>
                    @foreach($rating as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>

                <label class="form-label">Privileges</label>
                <select class="form-select child-rating" name="licence_2_ratings[${index}][child][]" multiple data-index="${index}">
                    <!-- Populated via AJAX -->
                </select>

                <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Issue Date</strong></label> 
                    <input type="date" name="issue_date_licence2[${index}][child][]"  class="form-control"  value="">
                 </div>
                <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                    <input type="date" name="expiry_date_licence2[${index}][child][]" class="form-control" value="">
                 </div>
                 <div class="col-md-6">
                    <label class="form-label mt-3"><strong>Upload File</strong></label>
                  
                    <input type="file" name="rating_file_licence2[${index}][child][]" class="form-control">
                 </div>
            </div>
        `;
            $('#licence_2_ratings_container').append(selectBoxHtml);
        });



        //---------------------------------------------------------------
        $('#uk_licence').on('change', function() {
            if ($(this).is(':checked')) {
                $('#rating_select_boxes_container').show();
                $('#add_rating_box').show();
                $('#rating_select_boxes_container').empty(); // reset
                selectBoxIndex = 0;
                $('#add_rating_box').trigger('click'); // add one by default
            } else {
                $('#rating_select_boxes_container').empty().hide();
                $('#add_rating_box').hide();
            }
        });

        $('#add_rating_box').on('click', function() {
            let index = selectBoxIndex++;
            let selectBoxHtml = `
            <div class="rating-select-group border p-3 mb-3 rounded" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button type="button" class="btn btn-danger btn-sm remove-rating-box" data-index="${index}">Remove</button>
                </div>

                <label class="form-label">Rating</label>
                <select class="form-select parent-rating mb-2" name="ratings[${index}][parent]" data-index="${index}" >
                    <option value="">Select Parent</option>
                    @foreach($rating as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>

                <label class="form-label">Privileges</label>
                <select class="form-select child-rating" name="ratings[${index}][child][]" multiple data-index="${index}">
                    <!-- Populated via AJAX -->
                </select>
                <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Issue Date</strong></label>
                    <input type="date" name="issue_date[${index}][child][]" class="form-control"  value="">
                 </div>
                <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                    <input type="date" name="expiry_date[${index}][child][]" class="form-control" value="">
                 </div>
                 <div class="col-md-6">
                    <label class="form-label mt-3"><strong>Upload File</strong></label>
                    <input type="file" name="licence_file_one[${index}][child][]"  class="form-control">
                 </div>

            </div>
        `;
            $('#rating_select_boxes_container').append(selectBoxHtml);
            $(`.child-rating[data-index="${index}"]`).select2({
                placeholder: 'Select the Privilages',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible')
            });
            $(`.child-rating[data-index="${index}"]`).prop('disabled', true);
        });

        //-----------------------------------------------------------------------
        let selectBoxIndexeasa = 0;
        $('#easa_licence').on('change', function() {
            if ($(this).is(':checked')) {
                $('#easa_select_boxes_container').show();
                $('#easa_add_rating_box').show();
                $('#easa_select_boxes_container').empty(); // reset
                selectBoxIndexeasa = 0;
                $('#easa_add_rating_box').trigger('click'); // add one by default
            } else {
                $('#easa_select_boxes_container').empty().hide();
                $('#easa_add_rating_box').hide();
            }
        });
        $('#easa_add_rating_box').on('click', function() {
            let index = selectBoxIndexeasa++;
            let selectBoxHtml = `
            <div class="rating-select-group border p-3 mb-3 rounded" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button type="button" class="btn btn-danger btn-sm remove-rating-box" data-index="${index}">Remove</button>
                </div>

                <label class="form-label">Rating</label>
                <select class="form-select parent-rating-easa mb-2" name="licence_2_ratings[${index}][parent]" data-index="${index}" >
                    <option value="">Select Parent</option>
                    @foreach($rating as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>

                <label class="form-label">Privileges</label>
                <select class="form-select child-rating-easa" name="licence_2_ratings[${index}][child][]" multiple data-index="${index}">
                    <!-- Populated via AJAX -->
                </select>
                    <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Issue Date</strong></label>
                    <input type="date" name="issue_date_2_licence[${index}][child][]" class="form-control"  value="">
                 </div>
                <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                    <input type="date" name="expiry_date_2_licence[${index}][child][]" class="form-control" value="">
                 </div>
                 <div class="col-md-6">
                    <label class="form-label mt-3"><strong>Upload File</strong></label>
                    <input type="file" name="licence_file_two[${index}][child][]"  class="form-control">
                 </div>
            </div>
        `;
            $('#easa_select_boxes_container').append(selectBoxHtml);
            $(`.child-rating-easa[data-index="${index}"]`).select2({
                placeholder: 'Select the Privilages',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible')
            });
            $(`.child-rating-easa[data-index="${index}"]`).prop('disabled', true);
        });

        //------------------------------------------------------------------------
        // Edit form 

        $('#edit_uk_licence').on('change', function() {
            if ($(this).is(':checked')) {
                $('#edit_rating_select_boxes_container').show();
                $('#edit_add_rating_box').show();
                $('#edit_rating_select_boxes_container').empty(); // reset

                $('#edit_add_rating_box').trigger('click'); // add one by default
            } else {
                $('#edit_rating_select_boxes_container').empty().hide();
                $('#edit_add_rating_box').hide();
            }
        });


        $('#edit_add_rating_box').on('click', function() {
            let index = edit_selectBoxIndex++;
            let selectBoxHtml = `
            <div class="rating-select-group border p-3 mb-3 rounded" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <button type="button" class="btn btn-danger btn-sm remove-rating-box" data-index="${index}">Remove</button>
                </div>

                <label class="form-label">Rating</label>
                <select class="form-select parent-rating mb-2" name="licence_1_ratings[${index}][parent]" data-index="${index}" >
                    <option value="">Select Parent</option>
                    @foreach($rating as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>

                <label class="form-label">Privileges</label>
                <select class="form-select child-rating" name="licence_1_ratings[${index}][child][]" multiple data-index="${index}">
                    <!-- Populated via AJAX -->
                </select>
                <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Issue Date</strong></label>
                    <input type="date" name="issue_date[${index}][child][]" class="form-control"  value="">
                 </div>
                <div class="col-md-6">
                    <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                    <input type="date" name="expiry_date[${index}][child][]" class="form-control" value="">
                 </div>
                 <div class="col-md-6">
                    <label class="form-label mt-3"><strong>Upload File</strong></label>
                    <input type="file" name="rating_file[${index}][child][]"  class="form-control">
                 </div>
                
            </div>
        `;
            $('#edit_rating_select_boxes_container').append(selectBoxHtml);

            $(`.child-rating[data-index="${index}"]`).select2({
                placeholder: 'Select the Privileges',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible')
            });

        });

        //--------------------------------------------------------------------------

        // Remove rating select box group
        $(document).on('click', '.remove-rating-box', function() {
            $(this).closest('.rating-select-group').remove();
        });

        // Handle parent select change (load child ratings)
        $(document).on('change', '.parent-rating', function() {
            let $group = $(this).closest('.rating-select-group');
            let $childSelect = $group.find('.child-rating');
            let parentId = $(this).val();

            if (!parentId) return;

            // Check previously loaded parentId
            const previousLoadedParentId = $childSelect.data('loadedParentId');

            // Only clear child if previously loaded with a different parent
            if (previousLoadedParentId && previousLoadedParentId != parentId) {
                if ($childSelect.hasClass("select2-hidden-accessible")) {
                    $childSelect.select2('destroy');
                }
                $childSelect.empty();
            }

            $.ajax({
                url: "{{ url('get-child-ratings') }}",
                type: 'post',
                data: {
                    parentId: parentId,
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.length > 0) {
                        if (!$childSelect.data('loaded') || previousLoadedParentId != parentId) {
                            $.each(response, function(i, child) {
                                $childSelect.append(`<option value="${child.id}">${child.name}</option>`);
                            });
                        }
                    }

                    $childSelect.prop('disabled', false);

                    $childSelect.select2({
                        placeholder: 'Select the Privileges',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible')
                    });

                    // Mark as loaded with this parent
                    $childSelect.data('loaded', true);
                    $childSelect.data('loadedParentId', parentId);

                    // ✅ If this was triggered from select2:opening, reopen dropdown
                    if ($childSelect.data('forceOpenAfterLoad')) {
                        $childSelect.select2('open');
                        $childSelect.removeData('forceOpenAfterLoad');
                    }
                }
            });
        });


        // ✅ 2. When child is clicked (opened), trigger parent change only if not loaded
        $(document).on('select2:opening', '.child-rating', function(e) {
            let $childSelect = $(this);

            if (!$childSelect.data('loaded')) {
                e.preventDefault(); // prevent dropdown opening

                let $group = $childSelect.closest('.rating-select-group');
                let $parentSelect = $group.find('.parent-rating');

                // Set flag to force dropdown open after AJAX load
                $childSelect.data('forceOpenAfterLoad', true);

                // Trigger parent change to load children
                $parentSelect.trigger('change');
            }
        });


        // Handle parent select change (load child ratings)
        $(document).on('change', '.parent-rating-easa', function() {
            let parentId = $(this).val();
            let index = $(this).data('index');
            let $childSelect = $(`.child-rating-easa[data-index="${index}"]`);

            $childSelect.empty();

            if (parentId) {
                $.ajax({
                    url: "{{ url('get-child-ratings') }}",
                    type: 'post',
                    data: {
                        parentId: parentId,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.length > 0) {
                            $.each(response, function(i, child) {
                                $childSelect.append(`<option value="${child.id}">${child.name}</option>`);
                            });
                        }
                        $childSelect.prop('disabled', false);

                        // Reinitialize select2
                        $childSelect.select2({
                            placeholder: 'Select the Privileges',
                            allowClear: true,
                            width: '100%',
                            dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible')
                        });
                    }
                });
            }
        });
    });


    $(document).ready(function() {

        function initializeSelect2() {

            $('.extra_roles').select2({
                allowClear: true,
                placeholder: 'Select the roles',
                multiple: true,
                dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible') // More specific
            });

        }

        initializeSelect2();

        $('#user_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('users.data') }}",
            columns: [{
                    data: 'image',
                    name: 'image',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        let baseUrl = "{{ url('storage') }}";
                        let defaultImage = "{{ asset('assets/img/default_profile.png') }}";

                        if (data) {
                            return `<img src="${baseUrl}/${data}" width="50" height="50" class="img-thumbnail"/>`;
                        } else {
                            return `<img src="${defaultImage}" width="50" height="50" class="img-thumbnail"/>`;
                        }
                    }
                },
                {
                    data: 'fname',
                    name: 'fname'
                },
                {
                    data: 'lname',
                    name: 'lname'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'position',
                    name: 'position'
                },
                @if(auth() -> user() -> is_owner == 1) {
                    data: 'organization',
                    name: 'organization'
                },
                @endif {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#licence_checkbox').change(function() {
            if (this.checked) {
                $('#licence').show().prop('required', true);
                $('#licence_file').show().prop('required', true);
                // $('#license_2').show();

                // 👉 Show the ratings for Licence 1
                $('#licence_rating_section').show();
            } else {
                $('#licence').hide().prop('required', false).val('');
                $('#licence_file').hide().prop('required', false).val('');
                $('#licence_error, #licence_file_error').hide();
                $('#second_licence_section').hide();
                // $('#license_2').hide();

                // 👉 Hide the ratings for Licence 1
                $('#licence_rating_section').hide();
                $('#licence_rating_value').val(null).trigger('change'); // clear selection
            }
        });

        $('#licence_2_checkbox').change(function() {
            if (this.checked) {
                $('#licence_2').show();
                $('#licence_file_2').show();
                $('#second_licence_section').show();
                $('#licence_2').prop('required', true);
                $('#licence_file_2').prop('required', true);

                // 👉 Show the ratings for Licence 2
                $('#licence_2_rating_section').show();
            } else {
                $('#licence_2').prop('required', false).val('');
                $('#licence_file_2').prop('required', false).val('');

                // 👉 Hide the ratings for Licence 2
                $('#licence_2').hide();
                $('#licence_file_2').hide();
                $('#licence_2_rating_section').hide();
                $('#licence_2_rating_value').val(null).trigger('change'); // clear selection
            }
        });


        // Custom field 
        $('#custom_field_checkbox').change(function() {
            if (this.checked) {

                $('#customfield_filelabel').show();
                $('#custom_date_checkbox').show();
                $('#customfield_textlabel').show();
                $('#custom_text_checkbox').show();
            } else {
                $('#customfield_filelabel').hide();
                $('#custom_date_checkbox').hide();
                $('#customfield_textlabel').hide();
                $('#custom_text_checkbox').hide();
                $('#custom_text').hide();
                $('#custom_date').hide();
                $('#custom_date_checkbox').prop('checked', false);
                $('#custom_text_checkbox').prop('checked', false);
                $('#custom_text').val('');
            }
        });
        $('#editcustom_field_checkbox').change(function() {
            if (this.checked) {
                $('#editcustomfield_datelabel').show();
                $('#editcustom_date_checkbox').show();
                $('#editcustomfield_textlabel').show();
                $('#editcustom_text_checkbox').show();
            } else {
                $('#editcustomfield_datelabel').hide();
                $('#editcustom_date_checkbox').hide();
                $('#editcustomfield_textlabel').hide();
                $('#editcustom_text_checkbox').hide();
                $('#editcustom_text').hide();
                $('#editcustom_date').hide();
                $('#editcustom_date_checkbox').prop('checked', false);
                $('#editcustom_text_checkbox').prop('checked', false);
                // $('#editcustom_text').val('');
            }
        });

        // Custom field file checkbox
        $('#custom_date_checkbox').change(function() {
            if (this.checked) {
                $('#custom_text_checkbox').prop('checked', false);
                $('#custom_text').hide();
                $('#custom_date').show();
            } else {
                $('#custom_date').hide();
            }
        });
        $('#editcustom_date_checkbox').change(function() {
            if (this.checked) {
                $('#editcustom_text_checkbox').prop('checked', false);
                $('#editcustom_text').hide();
                $('#editcustom_date').show();
                // $('#editcustom_file_checkbox').val('');
            } else {
                $('#editcustom_date').hide();
            }
        });

        // Custom text checkbox
        $('#custom_text_checkbox').change(function() {
            if (this.checked) {
                $('#custom_date_checkbox').prop('checked', false);
                $('#custom_date').hide();
                $('#custom_text').show();
            } else {
                $('#custom_text').hide();
            }
        });

        $('#editcustom_text_checkbox').change(function() {
            if (this.checked) {
                $('#editcustom_date_checkbox').prop('checked', false);
                $('#editcustom_date').hide();
                $('#editcustom_text').show();
                // $('#editcustom_file').val(''); 
            } else {
                $('#editcustom_text').hide();
            }
        });


        $('#passport_checkbox').change(function() {
            if (this.checked) {
                $('#passport').show().prop('required', true);
                $('#passport_file').show().prop('required', true);
            } else {
                $('#passport').hide().prop('required', false);
                $('#passport_file').hide().prop('required', false);
                $('#passport').val('');
                $('#passport_file').val('');
                $('#passport_error').hide().prop('required', false);
                $('#passport_file_error').hide().prop('required', false);
            }
        });

        $('#rating_checkbox').change(function() {
            if (this.checked) {
                $('#ratings').show();
                $('#ratings select').prop('required', true);
            } else {
                $('#ratings').hide();
                $('#ratings select').prop('required', false).val('');
                $('#rating_error').text(''); // Optional: clear validation error if any
            }
        });


        $('#currency_checkbox').change(function() {
            if (this.checked) {
                $('#currency').show().prop('required', true);
            } else {
                $('#currency').hide().prop('required', false);
                $('#currency').val('');
                $('#currency_error').hide().prop('required', false);
            }
        });

        $('#custom_field_checkbox').change(function() {
            if (this.checked) {
                $('#custom_field_name').show().prop('required', true);
                $('#custom_field_value').show().prop('required', true);
            } else {
                $('#custom_field_name').hide().prop('required', false);
                $('#custom_field_value').hide().prop('required', false);
                $('#custom_field_name').val('');
                $('#custom_field_value').val('');
                $('#custom_field_name_error').hide().prop('required', false);
                $('#custom_field_value_error').hide().prop('required', false);

            }
        });

        $('#medical_checkbox').change(function() {
            if (this.checked) {
                $('.medical_issued_div').show();
                $('.medical_class_div').show();
                // $('#medical_2').show();
            } else {
                $('.medical_issued_div').hide();
                $('.medical_class_div').hide();
                // $('#medical_2').hide();
                $('#second_medical_section').hide();

                // Reset second medical fields
                $('#issued_by_2, #medical_class_2, #medical_issue_date_2, #medical_expiry_date_2, #medical_detail_2, #medical_file_2').val('');
            }
        });

        $('#medical_2_checkbox').change(function() {
            if (this.checked) {
                $('#second_medical_section').show();
            } else {
                $('#second_medical_section').hide();

                // Reset second medical fields
                $('#issued_by_2, #medical_class_2, #medical_issue_date_2, #medical_expiry_date_2, #medical_detail_2, #medical_file_2').val('');
            }
        });

        // Edit Medical Toggle
        $('#editmedical_checkbox').change(function() {
            if (this.checked) {
                $('.editmedical_issued_div').show();
                $('.editmedical_class_div').show();
                // $('#edit_medical_2').show();

                // Make fields required
                $('#editissued_by').prop('required', true);
                $('#editmedical_class').prop('required', true);
                $('#editmedical_issue_date').prop('required', true);
                $('#editmedical_expiry_date').prop('required', true);
                $('#editmedical_detail').prop('required', true);
                $('#editmedical_file').prop('required', true);
            } else {
                $('.editmedical_issued_div').hide();
                $('.editmedical_class_div').hide();
                // $('#edit_medical_2').hide();
                $('#edit_second_medical_section').hide();

                $('#editissued_by, #editmedical_class, #editmedical_issue_date, #editmedical_expiry_date, #editmedical_detail, #editmedical_file').val('').prop('required', false);

                // Second medical
                $('#editissued_by_2, #editmedical_class_2, #editmedical_issue_date_2, #editmedical_expiry_date_2, #editmedical_detail_2, #editmedical_file_2').val('').prop('required', false);
            }
        });

        $('#edit_medical_2_checkbox').change(function() {
            if (this.checked) {
                $('#edit_second_medical_section').show();
            } else {
                $('#edit_second_medical_section').hide();

                // Reset second medical fields
                $('#issued_by_2, #medical_class_2, #medical_issue_date_2, #medical_expiry_date_2, #medical_detail_2, #medical_file_2').val('');
            }
        });

        $('#createUser').on('click', function() {
            $('.error_e').html('');
            $('.alert-danger').css('display', 'none');
            $("#Create_user")[0].reset();

            // Manually hide and reset all conditional sections
            $('#licence').hide().prop('required', false).val('');
            $('#licence_file').hide().prop('required', false).val('');
            $('#licence_error, #licence_file_error').hide();
            // $('#license_2').hide();
            $('#licence_rating_section').hide();
            $('#licence_2_checkbox').prop('checked', false);
            $('#second_licence_section').hide();
            $('#licence_2').prop('required', false).val('');
            $('#licence_file_2').prop('required', false).val('');
            $('#licence_2_error, #licence_file_2_error').hide();
            $('#licence_checkbox').prop('checked', false);
            $('#licence_verification_required').prop('checked', false);
            $('#licence_2_verification_required').prop('checked', false);

            // Hide and reset medical fields
            $('#medical_checkbox').prop('checked', false);
            $('#medical_verification_required').prop('checked', false);
            $('.medical_issued_div').hide();
            $('.medical_class_div').hide();
            $('#medical_2').show();
            $('#issued_by').val('');
            $('#medical_class').val('');
            $('#medical_issue_date').val('');
            $('#medical_expiry_date').val('');
            $('#medical_detail').val('');
            $('#medical_file').val('');
            // Hide and reset second medical section
            // $('#medical_2_checkbox').prop('checked', false);
            $('#medical_2_verification_required').prop('checked', false);
            $('#second_medical_section').hide();
            $('#issued_by_2').val('');
            $('#medical_class_2').val('');
            $('#medical_issue_date_2').val('');
            $('#medical_expiry_date_2').val('');
            $('#medical_detail_2').val('');
            $('#medical_file_2').val('');

            $('#userModal').modal('show');
        });

        $('#saveuser').click(function(e) {
            e.preventDefault();
            // $('#loader').show();
            $(".loader").fadeIn();
            $('.error_e').html('');

            var formData = new FormData($('#Create_user')[0]);
            $.ajax({
                url: '{{ url("/users/save") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $(".loader").fadeOut("slow");
                    $('#userModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    // $('#loader').hide();
                    $(".loader").fadeOut("slow");
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function(key, value) {
                        var html1 = '<p>' + value + '</p>';
                        $('#' + key + '_error').html(html1);
                    });
                }
            });
        });
        //edit 

        $('#edit_licence_checkbox').change(function() {
            if (this.checked) {
                $('#edit_licence').show().prop('required', true);
                $('#edit_licence_file').show().prop('required', true);
                // $('#edit_license2').show();
                $('#edit_licence_rating_section').show();
                if (!$('#edit_licence_rating_value').hasClass("select2-hidden-accessible")) {
                    $('#edit_licence_rating_value').select2({
                        width: '100%'
                    });
                }
            } else {
                $('#edit_licence').hide().prop('required', false).val('');
                $('#edit_licence_file').hide().prop('required', false).val('');
                $('#edit_licence_rating_section').hide();
                $('#edit_licence_rating').val(null); // clear selection
                //  $('#edit_licence_2').val('');
                $('#edit_licence_file_2').val('');
                // $('#edit_license2').hide();
            }
        });

        $('#edit_licence_2_checkbox').change(function() {
            if (this.checked) {
                $('#edit_second_licence_section').show();
                $('#edit_licence_2').prop('required', true);
                $('#edit_licence_file_2').prop('required', true);
                $('#edit_licence_2_rating_section').show();
            } else {
                $('#edit_licence_2').prop('required', false).val('');
                $('#edit_licence_file_2').prop('required', false).val('');
                $('#edit_licence_2_rating_section').hide();
                $('#edit_licence_2_rating').val(null); // clear selection
                $('#edit_second_licence_section').hide();
            }
        });


        $('#edit_passport_checkbox').change(function() {
            if (this.checked) {
                $('#edit_passport').show().prop('required', true);
                $('#edit_passport_file').show().prop('required', true);
            } else {
                $('#edit_passport').hide().prop('required', false);
                $('#edit_passport_file').hide().prop('required', false);
                $('#edit_passport').val('');
                $('#edit_passport_file').val('');
                $('#edit_passport_error_up').hide().prop('required', false);
                $('#edit_passport_file_error_up').hide().prop('required', false);
            }
        });

        $('#edit_rating_checkbox').change(function() {
            if (this.checked) {
                $('#edit_ratings').show();
                $('#edit_ratings select').prop('required', true);
            } else {
                $('#edit_ratings').hide();
                $('#edit_ratings select').prop('required', false).val('');
                $('#edit_rating_error_up').text('');
            }
        });
        $('#edit_currency_checkbox').change(function() {
            if (this.checked) {
                $('#edit_currency').show().prop('required', true);
            } else {
                $('#edit_currency').hide().prop('required', false);
                $('#edit_currency').val('');
                $('#edit_currency_error_up').hide().prop('required', false);
            }
        });

        $('#edit_custom_field_checkbox').change(function() {
            if (this.checked) {
                $('#edit_custom_field_name').show().prop('required', true);
                $('#edit_custom_field_value').show().prop('required', true);
            } else {
                $('#edit_custom_field_name').hide().prop('required', false);
                $('#edit_custom_field_value').hide().prop('required', false);
                $('#edit_custom_field_name').val('');
                $('#edit_custom_field_value').val('');
                $('#edit_custom_field_name_error_up').hide().prop('required', false);
                $('#edit_custom_field_value_error_up').hide().prop('required', false);

            }
        });

        //

        // $('.edit-user-icon').click(function(e) {
        //     e.preventDefault();
        

        $(document).on('click', '.edit-user-icon', function() {
            $('.error_e').html('');
            $("#editUserForm")[0].reset();
            var userId = $(this).data('user-id');
            vdata = {
                id: userId,
                "_token": "{{ csrf_token() }}",
            };
            $.ajax({
                type: 'post',
                url: "{{ url('users/edit') }}", 
                data: vdata,
                success: function(response) {
                    $('input[name="edit_firstname"]').val(response.user.fname);
                    $('input[name="edit_lastname"]').val(response.user.lname);
                    $('input[name="edit_email"]').val(response.user.email);
                    $('input[name="edit_form_id"]').val(response.user.id);
                    $('#edit_ou_id').val(response.user.ou_id);
                    $('#edit_status').val(response.user.status);
                    $('#archive_status').val(response.user.is_activated);

                    edit_selectBoxIndex = response.userRatings_licence_1.length || 0;
                    if (response.licence1 == 1) {
                        $('#edit_licence_rating_section').show();
                        $('#edit_uk_licence').prop('checked', true);
                        $('#edit_rating_select_boxes_container').show().empty();
                        $('#edit_add_rating_box').show();


                        // let editSelectBoxIndex = 0;
                        edit_selectBoxIndex = response.userRatings_licence_1.length || 0;
                        response.userRatings_licence_1.forEach(function(group, i) {
                            
                            let index = i;
                            let parentId = group.parent_id;
                            let childIds = group.children;
                            let issueDate = group.issue_date || '';
                            let expiryDate = group.expire_date || '';
                            let filePath = group.file_path || '';
                            let fileUrl = filePath ? `{{ asset('storage/') }}/${filePath}` : '';


                            let selectBoxHtml = `
                        <div class="rating-select-group border p-3 mb-3 rounded" data-index="${index}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <button type="button" class="btn btn-danger btn-sm remove-rating-box" data-index="${index}">Remove</button>
                            </div>

                            <label class="form-label">Rating</label>
                            <select class="form-select parent-rating mb-2" name="licence_1_ratings[${index}][parent]" data-index="${index}"  
                            >
                                <option value="">Select Parent</option>
                                @foreach($rating as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>

                            <label class="form-label">Privileges</label>
                            <select class="form-select child-rating"  name="licence_1_ratings[${index}][child][]" multiple data-index="${index}">
                                <!-- Will be populated by AJAX -->
                            </select>
                               <div class="col-md-6">
                                    <label class="form-label mt-2"><strong>Issue Date</strong></label>
                                
                                     <input type="date" name="issue_date[${index}][child][]" class="form-control"  value="${issueDate}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                                 
                                     <input type="date"  name="expiry_date[${index}][child][]" class="form-control" value="${expiryDate}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mt-3"><strong>Upload File</strong></label>
                                   
                                  

                               
                                   <input type="file" name="rating_file[${index}][child][]" class="form-control">



                                </div>
                                <div class="col-md-6 mt-2">
                                    ${filePath ? `<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary">View Uploaded File</a>` : ''}
                                </div>
                             
                        </div>`;

                            $('#edit_rating_select_boxes_container').append(selectBoxHtml);

                            // Set selected parent
                            $(`select[name="licence_1_ratings[${index}][parent]"]`).val(parentId);
                            $(`.child-rating[data-index="${index}"]`).select2({
                                placeholder: 'Select the Privileges',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible')
                            });

                            // Load and set children
                            $.ajax({
                                type: 'POST',
                                url: "{{ url('get-children-by-parent') }}",
                                data: {
                                    parent_id: parentId,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    const $childSelect = $(`select[name="licence_1_ratings[${index}][child][]"]`);
                                    $childSelect.empty();
                                    res.children.forEach(child => {
                                        const selected = childIds.includes(child.id) ? 'selected' : '';
                                        $childSelect.append(`<option value="${child.id}" ${selected}>${child.name}</option>`);
                                    });

                                }
                            });
                        });
                    }
                    licence2_selectBoxIndex = response.userRatings_licence_2.length || 0;
                    if (response.licence2 == 1) {
                        $('#edit_licence_2_rating_section').show();
                        $('#licence_2_ratings').prop('checked', true);
                        $('#licence_2_ratings_container').show().empty();
                        $('#edit_licence_2_ratings').show();



                        licence2_selectBoxIndex = response.userRatings_licence_2.length || 0;
                        response.userRatings_licence_2.forEach(function(group, i) {
                            let index = i;
                            let parentId = group.parent_id;
                            let childIds = group.children;
                            let issueDate = group.issue_date || '';
                            let expiryDate = group.expire_date || '';
                            let filePath = group.file_path || '';
                            let fileUrl = filePath ? `{{ asset('storage/') }}/${filePath}` : '';

                            let selectBoxHtml = `
                        <div class="rating-select-group border p-3 mb-3 rounded" data-index="${index}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <button type="button" class="btn btn-danger btn-sm remove-rating-box" data-index="${index}">Remove</button>
                            </div>

                            <label class="form-label">Rating</label>
                            <select class="form-select parent-rating mb-2" name="licence_2_ratings[${index}][parent]" data-index="${index}">
                                <option value="">Select Parent</option> 
                                @foreach($rating as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>

                            <label class="form-label">Privileges</label>
                            <select class="form-select child-rating" name="licence_2_ratings[${index}][child][]" multiple data-index="${index}">
                                <!-- Will be populated by AJAX -->
                            </select>
                                <div class="col-md-6">
                                    <label class="form-label mt-2"><strong>Issue Date</strong></label>
                                    <input type="date" name="issue_date_licence2[${index}][child][]" class="form-control" value="${issueDate}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mt-2"><strong>Expiry Date</strong></label>
                                    <input type="date" name="expiry_date_licence2[${index}][child][]" class="form-control" class="form-control" value="${expiryDate}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mt-3"><strong>Upload File</strong></label>
                                    <input type="file" name="rating_file_licence2[${index}][child][]"  class="form-control">
                                </div>
                                <div class="col-md-6 mt-2">
                                    ${filePath ? `<a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary">View Uploaded File</a>` : ''}
                                </div>
                        </div>
                    `;

                            $('#licence_2_ratings_container').append(selectBoxHtml);

                            // Set selected parent
                            $(`select[name="licence_2_ratings[${index}][parent]"]`).val(parentId);
                            $(`select[name="licence_2_ratings[${index}][parent]"]`).val(parentId);
                            $(`.child-rating[data-index="${index}"]`).select2({
                                placeholder: 'Select the Privileges',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: $('#userModal .modal-content:visible, #editUserDataModal .modal-content:visible')
                            });

                            // Load and set children
                            $.ajax({
                                type: 'POST',
                                url: "{{ url('get-children-by-parent') }}",
                                data: {
                                    parent_id: parentId,
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function(res) {
                                    const $childSelect = $(`select[name="licence_2_ratings[${index}][child][]"]`);
                                    $childSelect.empty();
                                    res.children.forEach(child => {
                                        const selected = childIds.includes(child.id) ? 'selected' : '';
                                        $childSelect.append(`<option value="${child.id}" ${selected}>${child.name}</option>`);
                                    });
                                }
                            });
                        });
                    }

                    // Set extra roles
                    var extraRoles = response.user.extra_roles ? JSON.parse(response.user.extra_roles) : []; // Convert to array if needed
                    $('#edit_extra_roles option').prop('selected', false); // Reset selection
                    extraRoles.forEach(function(roleId) {
                        $('#edit_extra_roles option[value="' + roleId + '"]').prop('selected', true);
                    });


                    var userRoleId = response.user.role;
                    $('#role_id option').removeAttr('selected');
                    $('#edit_role option[value="' + userRoleId + '"]').attr('selected','selected');
                    if(userRoleId == 18){
                       $('#archiveStatus_col').show();
                    }else{
                       $('#archiveStatus_col').hide(); 
                    }
                    const document = response.user.documents ?? {};
                    const ratings = response.user_ratings || {};

                    // General Ratings
                    if (ratings.general) {
                        $('#edit_rating_checkbox').prop('checked', true);
                        $('#edit_ratings').show();
                        $('#edit_rating_value').val(ratings.general).trigger('change');
                    } else {
                        $('#edit_rating_checkbox').prop('checked', false);
                        $('#edit_ratings').hide();
                        $('#edit_rating_value').val([]).trigger('change');
                    }

                    // Licence 1 Ratings
                    if (ratings.licence_1) {
                        $('#edit_licence_rating_checkbox').prop('checked', true);
                        $('#edit_licence_rating_section').show();
                        $('#edit_licence_rating_value').val(ratings.licence_1).trigger('change');
                    } else {
                        $('#edit_licence_rating_checkbox').prop('checked', false);
                        $('#edit_licence_rating_section').hide();
                        $('#edit_licence_rating_value').val([]).trigger('change');
                    }

                    // Licence 2 Ratings
                    if (ratings.licence_2) {
                        $('#edit_licence_2_rating_value').val(ratings.licence_2).trigger('change');
                    } else {
                        $('#edit_licence_2_rating_value').val([]).trigger('change');
                    }

                    if (response.user.licence_required) {
                        $('#edit_licence_checkbox').prop('checked', true).trigger('change');

                        if (document) {
                            $('#edit_licence').val(document.licence);
                        } else {
                            $('#edit_licence').val('');
                        }
                    } else {
                        $('#edit_licence_checkbox').prop('checked', false).trigger('change');
                    }

                  
                    if (response.user.licence_2_required) {
                        $('#edit_licence_2_checkbox').prop('checked', true).trigger('change');
                        $('#edit_second_licence_section').show();

                        if (document.licence_2) {
                            
                            $('.edit_licence_2').val(response.user.documents.licence_2).prop('required', true);
                            // $('#edit_licence_file_2').show().prop('required', true);
                        } else {
                            $('#edit_licence_2').val('').prop('required', true);
                            // $('#edit_licence_file_2').show().prop('required', true);
                        }
                    } else {
                        $('#edit_licence_2_checkbox').prop('checked', false).trigger('change'); // ✅ this hides everything
                    }

                    if (response.user.password_flag == 1) {
                        $('#edit_update_password_checkbox').prop('checked', true);
                    } else {
                        $('#edit_update_password_checkbox').prop('checked', false);
                    }

                    // Set passport checkbox and fields

                    if (response.user.passport_required) {
                        $('#edit_passport_checkbox').prop('checked', true);
                        $('#edit_passport').val(document.passport).show().prop('required', true);
                        $('#edit_passport_file').show().prop('required', true);
                    } else {
                        $('#edit_passport_checkbox').prop('checked', false);
                        $('#edit_passport').hide().prop('required', false);
                        $('#passport_file').hide().prop('required', false);

                    }


                    // Set currency checkbox and field
                    if (response.user.currency_required) {
                        $('#edit_currency_checkbox').prop('checked', true);
                        $('#edit_currency').val(response.user.currency).show().prop('required', true);
                    } else {
                        $('#edit_currency_checkbox').prop('checked', false);
                        $('#edit_currency').hide().prop('required', false);
                    }
                    if (response.user.custom_field_date || response.user.custom_field_text) {
                        $('#editcustomfield_datelabel').show();
                        $('#editcustom_date_checkbox').show();
                        $('#editcustomfield_textlabel').show();
                        $('#editcustom_text_checkbox').show();
                        $('#editcustom_field_checkbox').prop('checked', true);
                        if (response.user.custom_field_date) {
                            $('#editcustom_date_checkbox').prop('checked', true);
                            $('#editcustom_text').hide();
                            $('#editcustom_date').show();
                            $('#editcustom_date').val(response.user.custom_field_date);

                        } else {
                            $('#editcustom_text_checkbox').prop('checked', true);
                            $('#editcustom_text').show();
                            $('#editcustom_date').hide();
                            $('#editcustom_text').val(response.user.custom_field_text);
                        }

                    }


                    if (response.user.medical == 1) {
                        $('#editmedical_checkbox').prop('checked', true).trigger('change');

                        $('#editmedical_issue_date').val(document.medical_issuedate ?? '');
                        $('#editmedical_expiry_date').val(document.medical_expirydate ?? '');
                        $('#editmedical_detail').val(document.medical_restriction ?? '');

                        $('#editissued_by').val(document.medical_issuedby?.trim() ?? '');
                        $('#editmedical_class').val(document.medical_class?.trim() ?? '');
                    } else {
                        $('#editmedical_checkbox').prop('checked', false).trigger('change');
                    }


                    if (response.user.licence_required) {
                        $('#edit_licence_checkbox').prop('checked', true).trigger('change');

                        if (document) {
                            $('#edit_licence').val(document.licence);
                        } else {
                            $('#edit_licence').val('');
                        }
                    } else {
                        $('#edit_licence_checkbox').prop('checked', false).trigger('change');
                    }

                    if (response.user.medical_2_required == 1) {
                        $('#edit_medical_2_checkbox').prop('checked', true).trigger('change');
                        $('#editmedical_issue_date_2').val(document.medical_issuedate_2 ?? '');
                        $('#editmedical_expiry_date_2').val(document.medical_expirydate_2 ?? '');
                        $('#editmedical_detail_2').val(document.medical_restriction_2 ?? '');

                        let issuedBy2 = document.medical_issuedby_2?.trim() ?? '';
                        let medicalClass2 = document.medical_class_2?.trim() ?? '';
                        $('#editissued_by_2').val(issuedBy2);
                        $('#editmedical_class_2').val(medicalClass2);
                    } else {
                        $('#edit_medical_2_checkbox').prop('checked', false).trigger('change');
                        $('#editmedical_issue_date_2').val('');
                        $('#editmedical_expiry_date_2').val('');
                        $('#editmedical_detail_2').val('');
                        $('#editissued_by_2').val('');
                        $('#editmedical_class_2').val('');
                    }
                    if (response.user.medical_adminRequired == 1) {
                        $('#editmedical_verification_required').prop('checked', true);
                    }
                    if (response.user.medical_2_adminRequired == 1) {
                        $('#edit_medical_2_verification_required').prop('checked', true);

                    }
                    if (response.user.medical_2_required) {
                        $('#edit_second_medical_section').show();
                    } else {
                        $('#edit_second_medical_section').hide();
                    }

                    if (response.user.licence_2_required) {
                        $('#edit_second_licence_section').show();
                    } else {
                        $('#edit_second_licence_section').hide();
                    }
                    if (response.user.licence_admin_verification_required == 1) {
                        $('#edit_licence_verification_required').prop('checked', true);
                    }
                    if (response.user.licence_2_admin_verification_required == 1) {
                        $('#edit_licence_2_verification_required').prop('checked', true);
                    }
                    if (response.user.passport_admin_verification_required == 1) {
                        $('#edit_passport_verification_required').prop('checked', true);

                    }
                    if (response.user.custom_field_required == 1) {
                        $('#editcustom_field_checkbox').prop('checked', true);

                    }
                    if (response.user.custom_field_admin_verification_required == 1) {
                        $('#edit_custom_field_verification_required').prop('checked', true);

                    }

                    // Set custom field checkbox and fields
                    if (response.user.custom_field_name && response.user.custom_field_value) {
                        $('#edit_custom_field_checkbox').prop('checked', true);
                        $('#edit_custom_field_name').val(response.user.custom_field_name).show().prop('required', true);
                        $('#edit_custom_field_value').val(response.user.custom_field_value).show().prop('required', true);
                    } else {
                        $('#edit_custom_field_checkbox').prop('checked', false);
                        $('#edit_custom_field_name').hide().prop('required', false);
                        $('#edit_custom_field_value').hide().prop('required', false);

                    }

                    $('#editUserDataModal').modal('show');

                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        document.getElementById('edit_update_password_checkbox').addEventListener('change', function() {
            var passwordField = document.getElementById('edit_update_password');
            if (this.checked) {
                passwordField.value = '1';
            } else {
                passwordField.value = '0';
            }
        });


        // Update user  form 
        // Use event delegation for update form button
        $(document).on('click', '#updateForm', function(e) {
            e.preventDefault();
            var formData = new FormData($('#editUserForm')[0]);

            $(".loader").fadeIn('fast');
            $.ajax({
                type: 'post',
                url: "/users/update",
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
                    $('#user_table').DataTable().ajax.reload(null, false);
                    setTimeout(function() {
                        $('#update_success_msg').fadeOut('slow');

                    }, 5000);
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

        // $('.delete-icon').click(function(e) {
        //     e.preventDefault();
        $(document).on('click', '.delete-icon', function() {
            $('#deleteUserModal').modal('show');
            var userId = $(this).data('user-id');
            var row = $(this).closest('tr');
            var fname = row.find('td:eq(1)').text(); // Assuming 'fname' is in the 2nd column
            var lname = row.find('td:eq(2)').text();
            var name = fname + ' ' + lname;
            $('#append_name').html(name);
            $('#userid').val(userId);
        });

        // Ensure Select2 works when modal is shown
        $('#userModal, #editUserDataModal').on('shown.bs.modal', function() {
            // initializeSelect2();
        });

        setTimeout(function() {
            $('#successMessage').fadeOut('slow');
        }, 2000);

    });
    $(document).ready(function() {
        // Initialize select2
        $('#edit_licence_rating_value').select2({
            width: '100%',
            placeholder: "Select Rating",
            allowClear: true
        });

        // Toggle display based on checkbox
        $('#edit_licence_rating_checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#edit_licence_rating_section').slideDown();
            } else {
                $('#edit_licence_rating_section').slideUp();
                $('#edit_licence_rating_value').val(null).trigger('change'); // Clear selection
            }
        });
    });
    $(document).ready(function() {
        $('#edit_rating_value').select2({
            width: '100%',
            placeholder: "Select Rating",
            allowClear: true
        });
    });
    // if (response.user_ratings?.licence_1?.length > 0) {
    //     $('#edit_licence_checkbox').prop('checked', true).trigger('change');
    //     setTimeout(function() {
    //         $('#edit_licence_rating_value').val(response.user_ratings.licence_1).trigger('change');
    //     }, 300);
    // }



    // if (response.user_ratings?.licence_2?.length > 0) {
    //     $('#edit_licence_2_checkbox').prop('checked', true).trigger('change');
    //     $('#edit_licence_2_rating_value').val(response.user_ratings.licence_2).trigger('change');
    // }
    $('#licence_checkbox').change(function() {
        if (this.checked) {
            $('#licence').show();
            $('#licence_file').show();
            $('#licence_rating_section').show();
        } else {
            $('#licence').hide();
            $('#licence_file').hide();
            $('#licence_rating_section').hide();
        }
    });

    $('#licence_2_checkbox').change(function() {
        if (this.checked) {
            $('#second_licence_section').show();
            $('#licence_2_rating_section').show();
        } else {
            $('#second_licence_section').hide();
            $('#licence_2_rating_section').hide();
        }
    });

    //------------------------------------
</script>


@endsection