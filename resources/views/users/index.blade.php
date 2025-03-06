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
    color: gray; /* default color for unfilled stars */
    cursor: pointer;
}

.star.rated {
    color: gold; /* color for filled stars */
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
    <div class="create_btn">
        <a href="#" class="btn btn-primary create-button" id="createUser" data-toggle="modal"
            data-target="#userModal">Create User</a>
    </div>
    @endif
    <div id="update_success_msg"></div>
    <div class="card pt-4">
        <div class="card-body">
    <table class="table table-hover" id="user_table">
        <thead>
            <tr>
                <th scope="col">Image</th>
                <th scope="col">First Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Email</th>
                @if(auth()->user()->is_owner == 1)
                <th scope="col">OU</th>
                <th scope="col">Position</th>
                @endif
                @if(!empty(auth()->user()->ou_id) && auth()->user()->is_owner == 0)
                <th scope="col">Position</th>
                @endif
                <th scope="col">Status</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        {{-- <tbody>
            @foreach($users as $val)
            <tr>
                @if($val->image)
                    <td scope="row"><img src="{{ asset('storage/' . $val->image) }}" alt="Course Image"  class="rounded-circle" height="50px" width="50px"></td>
                @else
                    <td><p>No Image</p></td>
                @endif
                <td scope="row" class="fname">{{ $val->fname }}</td>
                <td scope="row" class="lname">{{ $val->lname }}</td>
                <td>{{ $val->email }}</td>
                @if(auth()->user()->is_owner == 1)
                <td>{{ $val->organization ? $val->organization->org_unit_name : '--' }}</td>
                <td>{{ $val->roles ? $val->roles->role_name : '--' }}</td>
                @endif
                @if(!empty(auth()->user()->ou_id) && auth()->user()->is_owner == 0)
                <td>{{ $val->roles ? $val->roles->role_name : '--' }}</td>
                @endif
                <td>{{ ($val->status==1)? 'Active': 'Inactive' }}</td>
                <td>
                    @if(checkAllowedModule('users','user.get')->isNotEmpty())
                    <i class="fa fa-edit edit-user-icon" style="font-size:18px; cursor: pointer;"
                        data-user-id="{{ encode_id($val->id) }}"></i>
                    @endif    
                    @if(checkAllowedModule('users','user.destroy')->isNotEmpty())
                    <i class="fa-solid fa-trash delete-icon" style="font-size:18px; cursor: pointer;"
                    data-user-id="{{ encode_id($val->id) }}"></i>
                    @endif 
                </td>
            </tr>
            @endforeach
        </tbody> --}}
        <tbody></tbody>
    </table>
    </div></div>
</div>

<!-- Create User -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document"> <!-- Extra Large Modal -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Create User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="Create_user" enctype="multipart/form-data" class="needs-validation">
                    @csrf
                    <div class="row g-3 mb-3"> <!-- Bootstrap Grid -->
                        <div class="col-md-6">
                            <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
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
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div id="image_error" class="text-danger error_e"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control">
                            <div id="password_error" class="text-danger error_e"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirmpassword" class="form-label">Confirm Password<span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" id="confirmpassword">
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
                            <label for="extra_roles" class="form-label">Select Multiple Roles</label>
                            <select class="form-select" name="extra_roles[]" id="extra_roles" multiple="multiple">
                                <option value="">Select roles</option>
                                @foreach($roles as $val)
                                    <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                                @endforeach
                            </select>
                            <div id="extra_roles_error" class="text-danger error_e"></div>
                        </div>

                        <!-- Licence -->
                        <div class="col-md-6">
                            <label for="licence_checkbox" class="form-label">Licence</label>
                            <input type="checkbox" name="licence_checkbox" id="licence_checkbox" class="ms-2"> <!-- Added margin start -->
                            
                            <label for="licence_verification_checkbox" class="form-label ms-4">Licence Verification required?</label>
                            <input type="checkbox" name="licence_verification_checkbox" id="licence_verification_checkbox" class="ms-2">

                            <input type="text" name="licence" id="licence" class="form-control mt-2" style="display: none;" placeholder="Enter Licence Number">
                            <input type="file" name="licence_file" id="licence_file" class="form-control mt-2" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <!-- Passport -->
                        <div class="col-md-6">
                            <label for="passport_checkbox" class="form-label">Passport</label>
                            <input type="checkbox" name="passport_checkbox" id="passport_checkbox" class="ms-2">

                            <label for="passport_verification_checkbox" class="form-label ms-4">Passport Verification required?</label>
                            <input type="checkbox" name="passport_verification_checkbox" id="passport_verification_checkbox" class="ms-2">

                            <input type="text" name="passport" id="passport" class="form-control mt-2" style="display: none;" placeholder="Enter Passport Number">
                            <input type="file" name="passport_file" id="passport_file" class="form-control mt-2" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <!-- Rating -->
                        <div class="col-md-6">
                            <label for="rating_checkbox" class="form-label">Rating/s</label>
                            <input type="checkbox" name="rating_checkbox" id="rating_checkbox" class="ms-2">
                            <div id="ratings" style="display: none;">
                                <div id="ratingStars" class="rating-stars">
                                    <span class="star" data-value="1">&#9733;</span>
                                    <span class="star" data-value="2">&#9733;</span>
                                    <span class="star" data-value="3">&#9733;</span>
                                    <span class="star" data-value="4">&#9733;</span>
                                    <span class="star" data-value="5">&#9733;</span>
                                </div>
                                <input type="hidden" name="rating" id="rating_value" value="">
                            </div>
                        </div>

                        <!-- Currency -->
                        <div class="col-md-6">
                            <label for="currency" class="form-label">Currency</label>
                            <input type="checkbox" name="currency_checkbox" id="currency_checkbox" class="ms-2">
                            <input type="text" name="currency" id="currency" class="form-control mt-2" style="display: none;" placeholder="Enter Currency">
                        </div>

                        <!-- Custom Field -->
                        <div class="col-md-6">
                            <label for="custom_field_checkbox" class="form-label">Custom Field</label>
                            <input type="checkbox" name="custom_field_checkbox" id="custom_field_checkbox" class="ms-2">
                            <input type="text" name="custom_field_name" id="custom_field_name" style="display: none;" class="form-control mt-2" placeholder="Enter Custom Field Name">
                            <input type="text" name="custom_field_value" id="custom_field_value" style="display: none;" class="form-control mt-2" placeholder="Enter Custom Field Value">
                        </div>

                        @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                        <div class="col-md-6">
                            <label for="ou_id" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                            <select class="form-select" name="ou_id">
                                <option value="">Select Org Unit</option>
                                @foreach($organizationUnits as $val)
                                    <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
                            <select class="form-select" name="status">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary sbt_btn">Save</button>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--End of create user-->

<!-- Edit user -->
<div class="modal fade" id="editUserDataModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="editUserDataModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserDataModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="editUserForm" class="row g-3 needs-validation">
                    @csrf
                    <div class="row g-3 mb-3"> <!-- Bootstrap Grid -->
                    <div class="col-md-6">
                        <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
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
                        <input type="password" name="password_confirmation" class="form-control" id="confirmpassword">
                        <div id="password_confirmation_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="lastname" class="form-label">Image<span class="text-danger"></span></label>
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
                    <div class="col-md-6">
                        <label for="extra_roles" class="form-label">Select Multiple Roles<span
                                class="text-danger"></span></label>
                        <select class="form-select " name="extra_roles[]" id="edit_extra_roles" multiple="multiple">
                        <option value="">Select roles</option>
                            @foreach($roles as $val)
                                <option value="{{ $val->id }}">{{ $val->role_name }}</option>
                            @endforeach
                        </select>
                        <div id="extra_roles_error_up" class="text-danger error_e"></div>
                    </div>    
                      <!-- Update Password Checkbox -->
                    <div class="col-md-6">
                        <label for="edit_update_password_checkbox" class="form-label">Update Password</label>
                        <input type="checkbox" name="edit_update_password_checkbox" id="edit_update_password_checkbox">
                        <input type="hidden" name="edit_update_password" id="edit_update_password" value="0">
                    </div>


                    <!-- Licence -->
                    <div class="col-md-6">
                        <label for="edit_licence_checkbox" class="form-label">Licence</label>
                        <input type="checkbox" name="edit_licence_checkbox" id="edit_licence_checkbox">
                        <input type="text" name="edit_licence" id="edit_licence" class="form-control" style="display: none;" placeholder="Enter Licence Number">
                        <div id="edit_licence_error_up" class="text-danger error_e"></div>
                        <input type="file" name="edit_licence_file" id="edit_licence_file" class="form-control mt-3" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="edit_licence_file_error_up" class="text-danger error_e"></div>
                    </div>

                    <!-- Passport -->
                    <div class="col-md-6">
                        <label for="edit_passport_checkbox" class="form-label">Passport</label>
                        <input type="checkbox" name="edit_passport_checkbox" id="edit_passport_checkbox">
                        <input type="text" name="edit_passport" id="edit_passport" class="form-control" style="display: none;" placeholder="Enter Passport Number">
                        <div id="edit_passport_error_up" class="text-danger error_e"></div>
                        <input type="file" name="edit_passport_file" id="edit_passport_file" class="form-control mt-3" style="display: none;" accept=".pdf,.jpg,.jpeg,.png">
                        <div id="edit_passport_file_error_up" class="text-danger error_e"></div>

                    </div>

                    <!-- Rating/s (Stars) -->
                    <div class="col-md-6">
                        <label for="edit_rating_checkbox" class="form-label">Rating/s</label>
                        <input type="checkbox" name="edit_rating_checkbox" id="edit_rating_checkbox">
                        <div id="edit_ratings" style="display: none;">
                            <div id="edit_ratingStars" class="rating-stars">
                                <span class="star" data-value="1">&#9733;</span>
                                <span class="star" data-value="2">&#9733;</span>
                                <span class="star" data-value="3">&#9733;</span>
                                <span class="star" data-value="4">&#9733;</span>
                                <span class="star" data-value="5">&#9733;</span>
                            </div>
                            <input type="hidden" name="edit_rating" id="edit_rating_value" value="">
                            <div id="edit_rating_error_up" class="text-danger error_e"></div>
                        </div>
                    </div>

                    <!-- Currency (Optional) -->
                    <div class="col-md-6">
                        <label for="edit_currency" class="form-label">Currency</label>
                        <input type="checkbox" name="edit_currency_checkbox" id="edit_currency_checkbox">
                        <input type="text" name="edit_currency" id="edit_currency" class="form-control" style="display: none;" placeholder="Enter Currency">
                        <div id="edit_currency_error_up" class="text-danger error_e"></div>
                    </div>

                    <!-- Custom Field -->
                    <div class="col-md-6">
                        <label for="edit_custom_field_checkbox" class="form-label">Custom Field</label>
                        <input type="checkbox" name="edit_custom_field_checkbox" id="edit_custom_field_checkbox">
                        <input type="text" name="edit_custom_field_name" id="edit_custom_field_name" style="display: none;" class="form-control" placeholder="Enter Custom Field Name">
                        <div id="edit_custom_field_name_error_up" class="text-danger error_e"></div>
                        <input type="text" name="edit_custom_field_value" id="edit_custom_field_value" style="display: none;" class="form-control mt-3" placeholder="Enter Custom Field Value">
                        <div id="edit_custom_field_value_error_up" class="text-danger error_e"></div>
                    </div>
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="col-md-6">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" id="edit_ou_id" aria-label="Default select example">
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
                        <select class="form-select" name="status" id="edit_status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
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
    
    $(document).ready(function() {
        // $('#user_table').DataTable();

        $('#user_table').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
        "url": "{{ route('users.data') }}",
        "type": "GET",
        "data": function(d) {
            d.extra_param = "value"; // Directly modifying the data object instead of returning a new one
        }
    },
    "columns": [
        { "data": "image" },
        { "data": "fname", className: 'fname' },
        { "data": "lname", className: 'lname' },
        { "data": "email" },
        @if(auth()->user()->is_owner == 1)
        { "data": "organization" },
        { "data": "position" },
        @endif
        @if(!empty(auth()->user()->ou_id) && auth()->user()->is_owner == 0)
        { "data": "position" },
        @endif
        { "data": "status" },
        { "data": "action", orderable: false, searchable: false } // Merged action column
    ],
    "order": [[1, 'asc']],
    "columnDefs": [
        {
            "targets": 0,
            "render": function(data) {
                return data ? `<img src="/storage/${data}" alt="User Image" class="rounded-circle" height="50px" width="50px">` : 'No Image';
            }
        },
        {
            "targets": -1, // Last column (Actions)
            "render": function(data) {
                return data; // No need to check permissions in JS, just display the action buttons from the controller.
            }
        }
    ]
});

        $('#licence_checkbox').change(function() {
            if (this.checked) {
                $('#licence').show().prop('required', true);
                $('#licence_file').show().prop('required', true);
            } else {
                $('#licence').hide().prop('required', false);
                $('#licence_file').hide().prop('required', false);
                $('#licence').val('');
                $('#licence_file').val('');
                $('#licence_error').hide().prop('required', false);
                $('#licence_file_error').hide().prop('required', false);
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
                $('#ratings').show().prop('required', true);
            } else {
                $('#ratings').hide().prop('required', false);
                $('#ratings').val('');
                $('#ratings_error').hide().prop('required', false);
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

        $('#ratingStars .star').on('click', function() {
            var rating = $(this).data('value');
            $('#rating_value').val(rating);

            $('#ratingStars .star').removeClass('active');

            for (var i = 1; i <= rating; i++) {
                $('#ratingStars .star[data-value="' + i + '"]').addClass('active');
            }
        });

        $('#createUser').on('click', function() {
            $('.error_e').html('');
            $('.alert-danger').css('display', 'none');
            $('#userModal').modal('show');       
            
            initializeSelect2(); // Ensure Select2 is re-initialized
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
                            // $('#loader').hide();
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


        // edit 

        $('#edit_licence_checkbox').change(function() {
            if (this.checked) {
                $('#edit_licence').show().prop('required', true);
                $('#edit_licence_file').show().prop('required', true);
            } else {
                $('#edit_licence').hide().prop('required', false);
                $('#edit_licence_file').hide().prop('required', false);
                $('#edit_licence').val('');
                $('#edit_licence_file').val('');
                $('#edit_licence_error_up').hide().prop('required', false);
                $('#edit_licence_file_error_up').hide().prop('required', false);
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
                $('#edit_ratings').show().prop('required', true);
            } else {
                $('#edit_ratings').hide().prop('required', false);
                $('#edit_rating_value').val('');
                $('#edit_ratings_error_up').hide().prop('required', false);
            }
        });

        $('#edit_ratingStars .star').click(function() {
            var ratingValue = $(this).data('value');
            $('#edit_rating_value').val(ratingValue);

            $('#edit_ratingStars .star').each(function() {
                if ($(this).data('value') <= ratingValue) {
                    $(this).addClass('rated');
                } else {
                    $(this).removeClass('rated');
                }
            });
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
            $('.error_ee').html('');
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

                    // Set extra roles
                    var extraRoles = response.user.extra_roles ? JSON.parse(response.user.extra_roles) : []; // Convert to array if needed
                    $('#edit_extra_roles option').prop('selected', false); // Reset selection
                    extraRoles.forEach(function(roleId) {
                        $('#edit_extra_roles option[value="' + roleId + '"]').prop('selected', true);
                    });

                    // Primary role
                    var userRoleId = response.user.role;
                    $('#role_id option').removeAttr('selected');
                    $('#edit_role option[value="' + userRoleId + '"]').attr('selected',
                        'selected');

                        if (response.user.licence_required) {
                            $('#edit_licence_checkbox').prop('checked', true);
                            $('#edit_licence').val(response.user.licence).show().prop('required', true);
                            $('#edit_licence_file').show().prop('required', true);
                            
                        } else {
                            $('#edit_licence_checkbox').prop('checked', false);
                            $('#edit_licence').hide().prop('required', false);
                            $('#edit_licence_file').hide().prop('required', false);
                        }

                        if (response.user.password_flag == 1) {
                            $('#edit_update_password_checkbox').prop('checked', true);
                        } else {
                            $('#edit_update_password_checkbox').prop('checked', false);                           
                        }
                        
                        // Set passport checkbox and fields

                        if (response.user.passport_required) {
                            $('#edit_passport_checkbox').prop('checked', true);
                            $('#edit_passport').val(response.user.passport).show().prop('required', true);
                            $('#edit_passport_file').show().prop('required', true);
                        } else {
                            $('#edit_passport_checkbox').prop('checked', false);
                            $('#edit_passport').hide().prop('required', false);
                            $('#passport_file').hide().prop('required', false);
                           
                        }

                        if (response.user.rating) {
                            $('#edit_rating_checkbox').prop('checked', true);
                            $('#edit_ratings').show();
                            $('#edit_rating_value').val(response.user.rating);

                            $('#edit_ratingStars .star').each(function() {
                                var starValue = $(this).data('value');
                                if (starValue <= response.user.rating) {
                                    $(this).addClass('rated');
                                } else {
                                    $(this).removeClass('rated');
                                }
                            });
                        } else {
                            $('#edit_rating_checkbox').prop('checked', false);
                            $('#edit_ratings').hide();
                            $('#edit_ratings').val('')
                            $('#edit_rating_value').val('');
                        }

                        // Set currency checkbox and field
                        if (response.user.currency_required) {
                            $('#edit_currency_checkbox').prop('checked', true);
                            $('#edit_currency').val(response.user.currency).show().prop('required', true);
                        } else {
                            $('#edit_currency_checkbox').prop('checked', false);
                            $('#edit_currency').hide().prop('required', false);
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
                    initializeSelect2();
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

        $('#edit_ratingStars .star').click(function() {
            var ratingValue = $(this).data('value');
            $('#edit_rating_value').val(ratingValue);

            $('#edit_ratingStars .star').each(function() {
                if ($(this).data('value') <= ratingValue) {
                    $(this).addClass('rated');
                } else {
                    $(this).removeClass('rated');
                }
            });
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

                    setTimeout(function() {
                        $('#update_success_msg').fadeOut('slow');

                    }, 5000);

                    setTimeout(function() {
                        location.reload();
                    }, 100);
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

        // $('.delete-icon').click(function(e) {
        //     e.preventDefault();
        $(document).on('click', '.delete-icon', function() {

            $('#deleteUserModal').modal('show');
            var userId = $(this).data('user-id');
            var fname = $(this).closest('tr').find('.fname').text();
            var lname = $(this).closest('tr').find('.lname').text();
            var name = fname + ' ' + lname;
            $('#append_name').html(name);
            $('#userid').val(userId);

        });

        // Ensure Select2 works when modal is shown
        $('#userModal, #editUserDataModal').on('shown.bs.modal', function() {
            initializeSelect2();
        });

        setTimeout(function() {
            $('#successMessage').fadeOut('slow');
        }, 2000);

    });
</script>

@endsection