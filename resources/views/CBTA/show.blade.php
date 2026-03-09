@section('title', 'Competency Grading')
@section('sub-title', 'Competency Grading')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

<div class="main_cont_outer">

    <div class="create_btn ">
        <a href="{{ url('org_setting/'.request('ou_id')) }}" class="btn btn-primary"> <i class="bi bi-arrow-left"></i> Back</a>

        <button class="btn btn-primary create-button" id="create-cbta" data-toggle="modal"
            data-target="#orgUnitModal">Create CBTA</button>

    </div>

    <br>
    <div id="update_success_msg"></div>
    <div class="card pt-4">
        <div class="card-body">
            <nav>
                <ul id="myTab" role="tablist"
                    class="nav nav-pills with-arrow flex-column flex-sm-row text-center bg-light border-0 rounded-nav mt-3 mb-4">

                    <li class="nav-item flex-sm-fill">
                        <a class="nav-link font-weight-bold active"
                            id="nav-pilot-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#nav-pilot"
                            role="tab"
                            aria-controls="nav-pilot"
                            aria-selected="true">
                            Pilot
                        </a>
                    </li>

                    <li class="nav-item flex-sm-fill">
                        <a class="nav-link font-weight-bold"
                            id="nav-instructor-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#nav-instructor"
                            role="tab"
                            aria-controls="nav-instructor"
                            aria-selected="false">
                            Instructor
                        </a>
                    </li>

                    <li class="nav-item flex-sm-fill">
                        <a class="nav-link font-weight-bold"
                            id="nav-examiner-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#nav-examiner"
                            role="tab"
                            aria-controls="nav-examiner"
                            aria-selected="false">
                            Examiner
                        </a>
                    </li>

                </ul>
            </nav>

            <div class="tab-content border bg-light" id="nav-tabContent">

                <!-- Pilot Tab -->
                <div class="tab-pane fade show active"
                    id="nav-pilot"
                    role="tabpanel"
                    aria-labelledby="nav-pilot-tab">

                    <table class="table table-hover" id="pilotTable">
                        <thead>
                            <tr>
                                <th>Competency</th>
                                <th>Short Name</th>
                                <th>Organization Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pilot as $val)
                            <tr>
                                <td>{{ $val->competency }}</td>
                                <td>{{ $val->short_name }}</td>
                                <td>{{ optional($val->organization_unit)->org_unit_name ?? 'N/A' }}</td>
                                <td>
                                    <i class="fa fa-edit edit-cbta-icon"
                                        style="font-size:25px;cursor:pointer;"
                                        data-id="{{ $val->id }}"></i>

                                    <i class="fa-solid fa-trash delete-cbta-icon"
                                        style="font-size:25px;cursor:pointer;"
                                        data-id="{{ $val->id }}"></i>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>


                <!-- Instructor Tab -->
                <div class="tab-pane fade"
                    id="nav-instructor"
                    role="tabpanel"
                    aria-labelledby="nav-instructor-tab">

                    <table class="table table-hover" id="instructorTable">
                        <thead>
                            <tr>
                                <th>Competency</th>
                                <th>Short Name</th>
                                <th>Organization Unit</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($instructor as $val)
                            <tr>
                                <td>{{ $val->competency }}</td>
                                <td>{{ $val->short_name }}</td>
                                <td>{{ optional($val->organization_unit)->org_unit_name ?? 'N/A' }}</td>

                                <td>
                                    <i class="fa fa-edit edit-cbta-icon"
                                        style="font-size:25px;cursor:pointer;"
                                        data-id="{{ $val->id }}"></i>
                                </td>

                                <td>
                                    <i class="fa-solid fa-trash delete-cbta-icon"
                                        style="font-size:25px;cursor:pointer;"
                                        data-id="{{ $val->id }}"></i>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>


                <!-- Examiner Tab -->
                <div class="tab-pane fade"
                    id="nav-examiner"
                    role="tabpanel"
                    aria-labelledby="nav-examiner-tab">

                    <table class="table table-hover" id="examinerTable">
                        <thead>
                            <tr>
                                <th>Competency</th>
                                <th>Short Name</th>
                                <th>Organization Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($examiner as $val)
                            <tr>
                                <td>{{ $val->competency }}</td>
                                <td>{{ $val->short_name }}</td>
                                <td>{{ optional($val->organization_unit)->org_unit_name ?? 'N/A' }}</td>

                                <td>
                                    <i class="fa fa-edit edit-cbta-icon"
                                        style="font-size:25px;cursor:pointer;"
                                        data-id="{{ $val->id }}"></i>

                                    <i class="fa-solid fa-trash delete-cbta-icon"
                                        style="font-size:25px;cursor:pointer;"
                                        data-id="{{ $val->id }}"></i>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>
</div>
<!-- Create cbta-->
<div class="modal fade" id="createcbtaModal" tabindex="-1" role="dialog" aria-labelledby="createcbtaModal"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" enctype="multipart/form-data">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createcbtaModal">Create Competency Grading</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="cbta_form" method="POST" class="row g-3 needs-validation">
                    @csrf
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="mb-3">
                        <label class="form-label">
                            Organization Unit <span class="text-danger">*</span>
                        </label>
                        <select name="organization_unit" class="form-select">
                            <option value="">Select the Organization Unit</option>
                            @foreach ($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="" class="form-label">Competency<span
                                class="text-danger">*</span></label>
                        <input type="text" name="competency" class="form-control">
                        <div id="competency_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">Short Name</label>
                        <input type="text" name="short_name" class="form-control">
                        <div id="short_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">Competency Type<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="competency_type">
                            <option value="instructor">Instructor</option>
                            <option value="examiner">Examiner</option>
                            <option value="pilot">Pilot</option>
                        </select>
                        <div id="course_type_error" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="submitCbta" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of cbta -->

<!-- Edit cbta-->
<div class="modal fade" id="editcbtaModal" tabindex="-1" role="dialog" aria-labelledby="editcbtaModal"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" enctype="multipart/form-data">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editcbtaModal">Update Competency Grading</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="editcbta_form" method="POST" class="row g-3 needs-validation">
                    @csrf
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="mb-3">
                        <label class="form-label">
                            Organization Unit <span class="text-danger">*</span>
                        </label>
                        <select name="organization_unit" id="edit_organization_unit" class="form-select">
                            <option value="">Select the Organization Unit</option>
                            @foreach ($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="" class="form-label">Competency<span class="text-danger">*</span></label>
                        <input type="text" name="edit_competency" class="form-control">
                        <input type="hidden" name="cbta_id" id="cbta_id" class="form-control">
                        <div id="edit_competency_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">Short Name</label>
                        <input type="text" name="edit_short_name" class="form-control">
                        <div id="edit_short_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">Competency Type<span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="edit_competency_type" id="edit_competency_type">
                            <option value="instructor">Instructor</option>
                            <option value="examiner">Examiner</option>
                            <option value="pilot">Pilot</option>
                        </select>

                    </div>
                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="update" class="btn btn-primary sbt_btn">Update </a>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of edit cbta -->



@endsection

@section('js_scripts')
<script>
    $(document).ready(function() {

        // Instructor table
        var instructorTable = $('#instructorTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            ordering: true,
            searching: true,
            responsive: true
        });

        // Examiner table
        var examinerTable = $('#examinerTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            ordering: true,
            searching: true,
            responsive: true
        });

        // Pilot table
        var examinerTable = $('#pilotTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 20, 50, 100],
            ordering: true,
            searching: true,
            responsive: true
        });



    });

    $("#create-cbta").on('click', function() {
        $(".error_e").html('');
        $("#cbta_form")[0].reset();
        $("#createcbtaModal").modal('show');
    })

    $("#submitCbta").on("click", function(e) {
        e.preventDefault();
        $(".loader").fadeIn();
        var formData = new FormData($('#cbta_form')[0]);
        $.ajax({
            url: '{{ url("/custom-cbta-add") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#createcbtaModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                $(".loader").fadeOut("slow");
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    console.log(key);
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error').html(msg);
                })
            }
        });

    })

    $(document).on('click', '.edit-cbta-icon', function() {
        $('.error_e').html('');
        $("#editcbta_form")[0].reset();
        var CbtaID = $(this).data('id');
        $('#cbta_id').val(CbtaID);

        $.ajax({
            url: "{{ url('custom-cbta-edit') }}",
            type: 'post',
            data: {
                CbtaID: CbtaID,
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                if (response.cbta) {
                    $('input[name="edit_competency"]').val(response.cbta[0].competency || '');
                    $('input[name="edit_short_name"]').val(response.cbta[0].short_name || '');
                    $('#edit_competency_type option[value=' + response.cbta[0].competency_type + ']').prop('selected', true);
                    $('#edit_organization_unit').val(response.cbta[0].ou_id);



                }
                $('#editcbtaModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
    // update  
    $("#update").on("click", function(e) {
        e.preventDefault();
        $(".loader").fadeIn();
        var formData = new FormData($('#editcbta_form')[0]);
        $.ajax({
            url: '{{ url("/custom-cbta-update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#editcbtaModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                $(".loader").fadeOut("slow");
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    console.log(key);
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error').html(msg);
                })
            }
        });

    })

    // Delete cbta
    $(".delete-cbta-icon").on("click", function(e) {
        e.preventDefault();

        var CbtaID = $(this).data('id');

        if (!confirm("Are you sure you want to delete this competency?")) {
            return false; // stop if user cancels
        }

        var formData = {
            CbtaID: CbtaID,
            _token: '{{ csrf_token() }}' // add CSRF token for Laravel
        };

        $.ajax({
            url: '{{ url("/custom-cbta-delete") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert("Deleted successfully!");
                location.reload();
            },
            error: function(xhr, status, error) {
                alert("Something went wrong!");
            }
        });
    });
</script>

@endsection