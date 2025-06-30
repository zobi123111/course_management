@section('title', 'Ratings')
@section('sub-title', 'Ratings')
@extends('layout.app')
@section('content')



<div class="main_cont_outer">
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif
    @if(session()->has('error'))
    <div id="errorMessage" class="alert alert-warning fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('error') }}
    </div>
    @endif

    <div id="update_success_msg"></div>

    @if(checkAllowedModule('users','user.store')->isNotEmpty())
        <div class="create_rating">
            <a href="#" class="btn btn-primary create-button" id="addrating" data-toggle="modal"
                data-target="#ratingModal">Add Rating
            </a>
        </div>
    @endif

    
    <div class="card pt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="rating_table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Rating</th>
                            <th scope="col">Kind Of Rating</th>
                            <th scope="col">Rating Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($ratings as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="rating">{{ $row->name }}</td>
                            <td class="rating">{{ $row->kind_of_rating }}</td>
                             <td>
                                @if ($row->is_fixed_wing)
                                    <span class="badge bg-primary">Fixed Wing</span>
                                @endif
                                @if ($row->is_rotary)
                                    <span class="badge bg-info">Rotary</span>
                                @endif
                                @if ($row->is_instructor)
                                    <span class="badge bg-success">Instructor</span>
                                @endif
                                @if ($row->is_examiner)
                                    <span class="badge bg-warning text-dark">Examiner</span>
                                @endif
                            </td>
                            <td>
                                {!! $row->status == 1 
                                    ? '<span class="badge bg-success">Active</span>' 
                                    : '<span class="badge bg-danger">Inactive</span>' !!}
                            </td>
                            <td>
                                <i class="fa fa-edit edit-user-icon text-primary me-2" style="font-size:18px; cursor: pointer;" data-rating-id="{{ encode_id($row->id) }}"></i>
                                <i class="fa-solid fa-trash delete-icon text-danger" style="font-size:18px; cursor: pointer;" data-rating-id="{{ encode_id($row->id) }}"></i>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="ratingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ratingModalLabel">Add Rating</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="add_rating" class="row g-3 needs-validation">
                    @csrf
                    <div class="row g-3 mb-3">
                        <!-- Bootstrap Grid -->
                       <div class="form-group mt-2">
                            <label for="parent_rating" class="form-label">Parent Rating</label>
                        <select name="parent_id" id="parent_rating" class="form-select">
                            <option value="">No Parent (This is a root rating)</option>
                            @foreach($ratingDropdownOptions as $r)
                                <option value="{{ $r->id }}">{!! $r->name !!}</option>
                            @endforeach
                        </select>
                        </div>
                        <div class="form-group">
                            <label for="firstname" class="form-label">Rating Name<span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control">
                            <div id="name_error" class="text-danger error_e"></div>
                        </div>

                          <!-- Kind of Rating Dropdown -->
                        <div class="form-group">
                            <label for="kind_of_rating" class="form-label">Kind of Rating <span class="text-danger">*</span></label>
                            <select name="kind_of_rating" id="kind_of_rating" class="form-select" required>
                                <option value="">Select Kind of rating</option>
                                <option value="type_rating" {{ old('kind_of_rating') == 'type_rating' ? 'selected' : '' }}>Type Rating</option>
                                <option value="class_rating" {{ old('kind_of_rating') == 'class_rating' ? 'selected' : '' }}>Class Rating</option>
                                <option value="instrument_rating" {{ old('kind_of_rating') == 'instrument_rating' ? 'selected' : '' }}>Instrument Rating</option>
                                <option value="instructor_rating" {{ old('kind_of_rating') == 'instructor_rating' ? 'selected' : '' }}>Instructor Rating</option>
                                <option value="examiner_rating" {{ old('kind_of_rating') == 'examiner_rating' ? 'selected' : '' }}>Examiner Rating</option>
                                <option value="others" {{ old('kind_of_rating') == 'others' ? 'selected' : '' }}>Others</option>
                            </select>
                            <div id="kind_of_rating_error" class="text-danger error_e"></div>
                        </div>

                        <!-- Category Tag Checkboxes -->
                        <div class="mb-3">
                            <label class="form-label d-block">Category Tag</label>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_fixed_wing" id="fixed_wing" value="1">
                                <label class="form-check-label" for="fixed_wing">Fixed Wing</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_rotary" id="rotary" value="1">
                                <label class="form-check-label" for="rotary">Rotary</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_instructor" id="instructor" value="1">
                                <label class="form-check-label" for="instructor">Instructor</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_examiner" id="examiner" value="1">
                                <label class="form-check-label" for="examiner">Examiner</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" aria-label="Default select example">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div id="status_error" class="text-danger error_e"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="saveRating" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editRatingModal" tabindex="-1" role="dialog" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="editRatingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRatingModalLabel">Edit Rating</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="editRatingForm" class="row g-3 needs-validation">
                    @csrf
                    <div class="row g-3 mb-3">
                        <!-- Parent Rating Dropdown -->
                       <div class="form-group mt-3">
    <label for="parent_rating" class="form-label">Parent Rating</label>
    <select name="parent_id" id="edit_parent_rating" class="form-select">
        <option value="">No Parent (This is a root rating)</option>
        @foreach($ratingDropdownOptions as $r)
            @if(isset($rating) && $rating->id != $r->id) {{-- Prevent selecting self as parent --}}
                <option value="{{ $r->id }}" {{ $rating->parent_id == $r->id ? 'selected' : '' }}>
                    {!! $r->name !!}
                </option>
            @endif
        @endforeach
    </select>
</div>
                        <div class="form-group">
                            <label for="firstname" class="form-label">Rating Name<span
                                class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control">
                            <input type="hidden" name="rating_id" id="rating_id" class="form-control">
                            <div id="name_error_up" class="text-danger error_e"></div>
                        </div>   
                         <!-- Kind of Rating Dropdown -->
                    <div class="form-group">
                        <label for="edit_kind_of_rating" class="form-label">Kind of Rating <span class="text-danger">*</span></label>
                        <select name="kind_of_rating" id="edit_kind_of_rating" class="form-select" required>
                            <option value="">Select Kind</option>
                            <option value="type_rating">Type Rating</option>
                            <option value="class_rating">Class Rating</option>
                            <option value="instrument_rating">Instrument Rating</option>
                            <option value="instructor_rating">Instructor Rating</option>
                            <option value="examiner_rating">Examiner Rating</option>
                            <option value="others">Others</option>
                        </select>
                        <div id="kind_of_rating_error_up" class="text-danger error_e"></div>
                    </div> 
                      <!-- Category Tag Checkboxes -->
                        <div class="form-group mb-3">
                            <label class="form-label">Category Tag <span class="text-danger">*</span></label>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_fixed_wing" id="edit_fixed_wing" value="1">
                                <label class="form-check-label" for="edit_fixed_wing">Fixed Wing</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_rotary" id="edit_rotary" value="1">
                                <label class="form-check-label" for="edit_rotary">Rotary</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_instructor" id="edit_instructor" value="1">
                                <label class="form-check-label" for="edit_instructor">Instructor</label>
                            </div>

                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="is_examiner" id="edit_examiner" value="1">
                                <label class="form-check-label" for="edit_examiner">Examiner</label>
                            </div>
                        </div>           
                        <div class="form-group">
                            <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="edit_status"
                                aria-label="Default select example">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div id="status_error_up" class="text-danger error_e"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="updateRating" class="btn btn-primary sbt_btn">Update</a>
                    </div>

                    <div class="loader" style="display: none;"></div>

                </form>
            </div>
        </div>
    </div>
</div>
<!--Delete  Modal -->
<form action="{{ url('/rating/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteRatingModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Rating</h5>
                    <input type="hidden" name="rating_id" id="ratingId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rating_delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>


<!-- End of Delete Model -->
@endsection

@section('js_scripts')

<script>
    $(document).ready(function (){

        $("#rating_table").dataTable(); 

        $('#addrating').on('click', function() {
            $('.error_e').html('');
            $('.alert-danger').css('display', 'none');
            $('#ratingModal').modal('show');                   
        });

        $('#saveRating').click(function(e) {
            e.preventDefault();
            // $('#loader').show();
            $(".loader").fadeIn();
            $('.error_e').html('');            
            var formData = new FormData($('#add_rating')[0]);
            $.ajax({
                url: '{{ url("/rating/save") }}',
                type: 'POST',
                data: formData,
                processData: false, 
                contentType: false,
                success: function(response) {
                    $(".loader").fadeOut("slow");
                    $('#ratingModal').modal('hide');
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

        $(document).on('click', '.edit-user-icon', function() {
            $('.error_e').html('');
            $("#editRatingForm")[0].reset();
            var ratingId = $(this).data('rating-id');

            $.ajax({
                type: 'GET',
                url: "{{ url('/rating/edit') }}",
                data: {
                    rating_id: ratingId,
                    _token: '{{ csrf_token() }}' // Include CSRF token
                },
                success: function(response) {
                    if (response.success) {
                        $('#rating_id').val(response.rating.id);
                        $('#edit_name').val(response.rating.name);
                        $('#edit_status').val(response.rating.status);
                        $('#edit_fixed_wing').prop('checked', response.rating.is_fixed_wing == 1);
                        $('#edit_rotary').prop('checked', response.rating.is_rotary == 1);
                        $('#edit_instructor').prop('checked', response.rating.is_instructor == 1);
                        $('#edit_examiner').prop('checked', response.rating.is_examiner == 1);
                        $('#edit_kind_of_rating').val(response.rating.kind_of_rating);
                        $('#edit_parent_rating').empty().append(`<option value="">No Parent (This is a root rating)</option>`);
                        response.dropdown.forEach(function(r) {
                            if (r.id != response.rating.id) {
                                $('#edit_parent_rating').append(`
                                    <option value="${r.id}" ${r.id == response.rating.parent_id ? 'selected' : ''}>
                                        ${r.name}
                                    </option>
                                `);
                            }
                        });
                        $('#editRatingModal').modal('show');
                    } else {
                        alert(response.msg || 'Unable to fetch rating.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('An error occurred while fetching the rating.');
                }
            });
        });

        $(document).on('click', '#updateRating', function(e) {
            e.preventDefault();

            var formData = new FormData($('#editRatingForm')[0]);        
            $(".loader").fadeIn('fast');

            $.ajax({
                type: 'POST',
                url: '/rating/update',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $(".loader").fadeOut('slow');
                    if (response.success) {
                        $('#editRatingModal').modal('hide');
                        $('#update_success_msg').html(`
                            <div class="alert alert-success fade show" role="alert">
                                <i class="bi bi-check-circle me-1"></i>
                                ${response.msg}
                            </div>
                        `).stop(true, true).fadeIn();
                        setTimeout(function() {
                            $('#update_success_msg').fadeOut('slow');
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    $(".loader").fadeOut("slow");
                    $('.error_e').html(''); // Clear previous errors

                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $('#' + key + '_error_up').html('<p>' + value + '</p>');
                        });
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                }
            });
        });


        $(document).on('click', '.delete-icon', function() {
            var ratingId = $(this).data('rating-id');
            var rating = $(this).closest('tr').find('.rating').text();
            $('#ratingId').val(ratingId);
            $('#append_name').text(rating);
            $('#deleteRatingModal').modal('show');
        });

        ['#successMessage', '#errorMessage'].forEach(function(selector) {
            setTimeout(function() {
                $(selector).fadeOut('slow');
            }, 2000);
        });
$('#ratingModal').on('hidden.bs.modal', function () {
    $('#add_rating')[0].reset();
    $('.error_e').html('');
});
$('#editRatingModal').on('hidden.bs.modal', function () {
    $('#editRatingForm')[0].reset();
    $('.error_e').html('');
});


    })
</script>
@endsection