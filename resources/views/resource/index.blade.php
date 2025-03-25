@section('title', 'Resource')
@section('sub-title', 'Resource')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="alertMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif
@if(session()->has('error'))
<div id="alertMessage" class="alert alert-danger fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif
<div class="main_cont_outer" >
    <div class="create_btn " >
        <button class="btn btn-primary create-button" id="create_resource" data-toggle="modal"
            data-target="#orgUnitModal">Create Resource</button>
    </div>
    <br>
    <div id="update_success_msg"></div>
    <div class="card pt-4">
        <div class="card-body">
    <table class="table table-hover" id="resourceTable">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Registration</th>
                <th scope="col">Class</th>
                <th scope="col">Type</th>
                <th scope="col">Note</th>
                <th scope="col">Edit</th>
                <th scope="col">Delete</th>
            </tr>
        </thead>
   
        <tbody>

        </tbody>
    </table>
</div>
</div>
</div>

<!-- OU Users List Modal -->
<div class="modal fade" id="orgUnitUsersModal" tabindex="-1" role="dialog" aria-labelledby="orgUnitUsersModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orgUnitUsersModalLabel">OU Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <table class="table" id="orgUnitUsersTable">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                    </tr>
                </thead>
                <tbody id="tblBody">                    
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<!--End of OU Users List Modal-->

<!-- Create Resource  Unit-->
<div class="modal fade" id="createResourceModel" tabindex="-1" role="dialog" aria-labelledby="orgUnitModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" enctype="multipart/form-data">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orgUnitModalLabel">Create Resource</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="createResourceForm" method="POST" class="row g-3 needs-validation" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="registration" class="form-label">Name<span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control">
                        <div id="name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="registration" class="form-label">Registration</label>
                        <input type="number" name="registration" class="form-control">
                        <div id="registration_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Class" class="form-label">Class</label>
                        <input type="text" name="class" class="form-control">
                        <div id="class_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Type" class="form-label">Type</label>
                        <input type="text" name="type" class="form-control">
                        <div id="type_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Class" class="form-label">Others</label>
                        <input type="text" name="other" class="form-control">
                        <div id="other _error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Note" class="form-label">Note</label>
                        <input type="text" name="note" class="form-control">
                        <div id="note_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Hours from RTS" class="form-label">Hours from RTS</label>
                        <input type="number" name="Hours_from_RTS" class="form-control">
                        <div id="Hours_from_RTS_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Date from RTS" class="form-label">Date from RTS</label>
                        <input type="date" name="Date_from_RTS" class="form-control">
                        <div id="Date_from_RTS_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="resource_logo" class="form-control" accept="image/*">
                            <div id="resource_logo_error" class="text-danger error_e"></div>           
                    </div>
                    <div class="form-group">
                        <label for="Date for maintenance" class="form-label">Date for maintenance</label>
                        <input type="date" name="Date_for_maintenance" class="form-control">
                        <div id="Date_for_maintenance_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Hours_Remaining" class="form-label">Hours Remaining</label>
                        <input type="number" name="Hours_Remaining" id="Hours_Remaining" class="form-control" min="0" step="1">
                        <div id="Hours_Remaining_error" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer"> 
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="save_resource" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of resource  Unit-->

<!-- Edit resource  Unit-->
<div class="modal fade" id="editOrgUnitModal" tabindex="-1" role="dialog" aria-labelledby="editOrgUnitModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" enctype="multipart/form-data">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrgUnitModalLabel">Edit Resource Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <form action="" id="editResource" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="registration" class="form-label">Name<span
                                class="text-danger">*</span></label>
                        <input type="text" name="edit_name" class="form-control">
                        <div id="edit_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="registration" class="form-label">Registration</label>
                        <input type="text" name="edit_registration" class="form-control">
                        <input type="hidden" name="resourse_id" class="form-control">
                        
                        <div id="edit_registration_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Class" class="form-label">Class</label>
                        <input type="text" name="edit_class" class="form-control">
                        <div id="edit_class_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Type" class="form-label">Type</label>
                        <input type="text" name="edit_type" class="form-control">
                        <div id="edit_type_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Class" class="form-label">Other</label>
                        <input type="text" name="edit_other" class="form-control">
                        <div id="other_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Note" class="form-label">Note</label>
                        <input type="text" name="edit_note" class="form-control">
                        <div id="edit_note_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Hours from RTS" class="form-label">Hours from RTS</label>
                        <input type="number" name="edit_Hours_from_RTS" class="form-control">
                        <div id="edit_Hours_from_RTS_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Date from RTS" class="form-label">Date from RTS</label>
                        <input type="date" name="edit_Date_from_RTS" class="form-control">
                        <div id="edit_Date_from_RTS_error_up" class="text-danger error_e"></div>
                    </div>
          
                    <div class="form-group">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" name="edit_organization_logo" id="edit_organization_logo" class="form-control" accept="image/*">
                        <small id="resorse_logo_filename" class="text-muted"></small> <!-- Show file name here -->
                        <div id="edit_organization_logo_error_up" class="text-danger error_e"></div>  
                        <img id="resourse_logo_preview" src="" alt="Organization Logo" style="max-width: 200px; display: none; margin-top: 10px;">    
                    </div>
                    <input type="hidden" name="existing_resourse_logo" id="existing_resourse_logo"> <!-- Hidden input to store existing filename -->
                    <div class="form-group">
                        <label for="Date for maintenance" class="form-label">Date for maintenance</label>
                        <input type="date" name="edit_Date_for_maintenance" class="form-control">
                        <div id="edit_Date_for_maintenance_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Hours_Remaining" class="form-label">Hours Remaining</label>
                        <input type="number" name="edit_Hours_Remaining" id="Hours_Remaining" class="form-control" min="0" step="1">
                        <div id="edit_Hours_Remaining_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="modal-footer"> 
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="update_resourse" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Organizational  Unit-->

<!--Organizational Unit Delete  Modal -->
<form action="{{ url('/resource/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteresourceModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="resource_id" id="resource_id" value="">
  
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Resource "<strong><span id="append_name">
                        </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary org_unit_delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End of Organizational Unit Delete Model -->

@endsection

@section('js_scripts')
<script>
    $('#resourceTable').DataTable({
        processing: true, 
        serverSide: true,
        ajax: {
            url: "{{ route('resource.index') }}",  
            type: "GET",
        },
        columns: [
            { data: 'name', name: 'name' ,class:'resource_name'},
            { data: 'registration', name: 'registration', class: 'orgUnitName' },
            { data: 'class', name: 'class' },
            { data: 'type', name: 'type' },
            { data: 'note', name: 'note' },
            { data: 'edit', name: 'edit', orderable: false, searchable: false },
            { data: 'delete', name: 'delete', orderable: false, searchable: false },
        ]
    });

    $("#create_resource").on('click', function() { 
        $(".error_e").html('');
        $("#createResourceForm")[0].reset();
        $("#createResourceModel").modal('show'); 
    })

    $(document).ready(function () {
    function toggleAndClearFields(selectedField, formType) {
        let fields = ["class", "type", "other"];
        fields.forEach(field => {
            let fieldName = formType + field;
            if (field !== selectedField) {
                $("input[name='" + fieldName + "']").val("").prop("disabled", true).css("background-color", "#e9ecef"); // Clear & disable
            } else {
                $("input[name='" + fieldName + "']").prop("disabled", false).css("background-color", ""); // Enable selected
            }
        });
    }

    function initFieldRestrictions(formType) {
        $("input[name='" + formType + "class'], input[name='" + formType + "type'], input[name='" + formType + "other']").on("input", function () {
            let selectedName = $(this).attr("name").replace(formType, "");
            if ($(this).val().trim() !== "") {
                toggleAndClearFields(selectedName, formType);
            } else {
                $("input[name='" + formType + "class'], input[name='" + formType + "type'], input[name='" + formType + "other']")
                    .prop("disabled", false)
                    .css("background-color", "");
            }
        });
    }

    // Initialize for edit modal only
    initFieldRestrictions("");
    initFieldRestrictions("edit_");
});





    $("#save_resource").on("click", function(e) {
        e.preventDefault();
        $(".loader").fadeIn();
        var formData = new FormData($('#createResourceForm')[0]);
       
        $.ajax({
            url: '{{ url("/resource/save") }}', 
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) { 
                if(response.success)
                    {
                        $(".loader").fadeOut("slow");
                        $('#createResourceModel').modal('hide');
                        location.reload();
                    }
               
            },
            error: function(xhr, status, error) {
                $(".loader").fadeOut("slow");
                var errorMessage = JSON.parse(xhr.responseText); 
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error').html(msg);
                })
            }
        });
    })
    $(document).on('click', '.edit-resource-icon', function() { 
        $('.error_e').html('');
        $("#editResource")[0].reset();
        var resource_id = $(this).data('resource-id');
        var userId = $(this).data('user-id');

        $.ajax({
            url: "{{ url('/resource/edit') }}",
            type: 'GET',
            data: {
                resourceId: resource_id,
                userId: userId
            },
            success: function(response) {
                if (response.resourcedata) {
                    $('input[name="edit_name"]').val(response.resourcedata.name || '');
                    $('input[name="edit_registration"]').val(response.resourcedata.registration || '');
                    $('input[name="edit_type"]').val(response.resourcedata.type || '');
                    $('input[name="edit_class"]').val(response.resourcedata.class || '');
                    $('input[name="edit_other"]').val(response.resourcedata.other || '');
                     $('input[name="edit_note"]').val(response.resourcedata.note || '');
                     $('input[name="edit_Hours_from_RTS"]').val(response.resourcedata.hours_from_rts || '');
                    $('input[name="edit_Date_from_RTS"]').val(response.resourcedata.date_from_rts || '');
                    $('input[name="edit_Date_for_maintenance"]').val(response.resourcedata.date_for_maintenance || '');
                     $('input[name="edit_Hours_Remaining"]').val(response.resourcedata.hours_remaining || '');
                     $('input[name="resourse_id"]').val(response.resourcedata.id || '');

                  
                     if (response.resourcedata.resource_logo) {
                            // Reset the image and filename before updating
                            $('#resourse_logo_preview').attr('src', '').hide();
                            $('#resorse_logo_filename').text('');
                            $('#existing_resourse_logo').val('');

                            // Now, set the new values
                            let fileName = response.resourcedata.resource_logo;
                            console.log(fileName);
                            let imagePath = '/storage/resource_logo/' + fileName; // Adjust the path as per your storage setup 
                            $('#resourse_logo_preview').attr('src', imagePath).show();
                            $('#resorse_logo_filename').text('Current File: ' + fileName);
                            $('#existing_resourse_logo').val(fileName);
                        } else {
                            // If no resource logo, ensure it's cleared
                            $('#resourse_logo_preview').attr('src', '').hide();
                            $('#resorse_logo_filename').text('No file selected');
                            $('#existing_resourse_logo').val('');
                        }


                    // Show selected file name when a new file is chosen
                    $('#org_logo').on('change', function() {
                        let file = this.files[0];
                        if (file) {
                            $('#org_logo_filename').text('Selected File: ' + file.name);
                        }
                    });
                    autoDisableFields("edit_");
                }
           
                $('#editOrgUnitModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
    // Function to disable and grey out other fields if one is filled
function autoDisableFields(formType) {
    let fields = ["class", "type", "other"];
    let selectedField = null;

    // Check which field has data
    fields.forEach(field => {
        let fieldName = formType + field;
        if ($("input[name='" + fieldName + "']").val().trim() !== "") {
            selectedField = field;
        }
    });

    // If one field has data, disable others
    if (selectedField) {
        fields.forEach(field => {
            let fieldName = formType + field;
            if (field !== selectedField) {
                $("input[name='" + fieldName + "']").val("").prop("disabled", true).css("background-color", "#e9ecef");
            } else {
                $("input[name='" + fieldName + "']").prop("disabled", false).css("background-color", "");
            }
        });
    }
}
        $('#update_resourse').on('click', function(e) { 
        e.preventDefault();
        $(".loader").fadeIn();
        var formData = new FormData($('#editResource')[0]);
      
        $.ajax({
            url: "{{ url('resourse/update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#editOrgUnitModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                $(".loader").fadeOut('slow');
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error_up').html(msg);
                })
            }
        })
    })





    $(document).on('click', '.delete-icon', function(e) {
        e.preventDefault();
        $('#deleteresourceModal').modal('show');
        var resourceId = $(this).data('resource-id');
     
        var resource_name = $(this).closest('tr').find('.resource_name').text();
        $('#append_name').html(resource_name);
        $('#resource_id').val(resourceId);
       
    });

    setTimeout(function() { 
        $('#alertMessage').fadeOut('slow');
    }, 2000);
</script>

@endsection