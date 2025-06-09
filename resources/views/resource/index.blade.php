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
<div class="main_cont_outer">
    <div class="create_btn">
        @if(checkAllowedModule('resource','save.index')->isNotEmpty())
        <button class="btn btn-primary create-button" id="create_resource" data-toggle="modal"
            data-target="#orgUnitModal">Create Resource</button>
        @endif
    </div>
    <div id="update_success_msg"></div>
    <div class="card pt-4">
        <div class="card-body">
            <table class="table table-hover" id="resourceTable">
                <thead>
                    <tr>
                        @if(auth()->user()->is_owner==1)
                        <th scope="col">OU</th>
                        @endif
                        <th scope="col">Name</th>
                        <th scope="col">Registration</th> 
                        <th scope="col">Class</th>
                        <th scope="col">Type</th>
                        <th scope="col">Note</th>
                        @if(checkAllowedModule('resource','edit.index')->isNotEmpty() || checkAllowedModule('resource','delete.index')->isNotEmpty() || checkAllowedModule('resource','resource.show')->isNotEmpty())
                        <th scope="col">Action</th>         
                        @endif         
                    </tr>
                </thead>
        
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>

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
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                       
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example" id="select_org_unit">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>            
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="registration" class="form-label">Name<span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control">
                        <div id="name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="registration" class="form-label">Registration</label>
                        <input type="text" name="registration" class="form-control">
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
                        <label for="Type" class="form-label">Classroom</label>
                        <input type="text" name="classroom" id="classroom" class="form-control">
                        <div id="classroom_error_up" class="text-danger error_e"></div>
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
                        <input type="number" name="Hours_from_RTS" id="Hours_from_RTS" class="form-control">
                        <div id="Hours_from_RTS_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Date from RTS" class="form-label">Date from RTS</label>
                        <input type="date" name="Date_from_RTS" id="Date_from_RTS" class="form-control">
                        <div id="Date_from_RTS_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" name="resource_logo" class="form-control" accept="image/*" >
                            <div id="resource_logo_error" class="text-danger error_e"></div>           
                    </div>
                    <div class="form-group">
                        <label for="Date for maintenance" class="form-label">Date for maintenance</label>
                        <input type="date" name="Date_for_maintenance" id="Date_for_maintenance" class="form-control">
                        <div id="Date_for_maintenance_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="Hours_Remaining" class="form-label">Hours Remaining</label>
                        <input type="number" name="Hours_Remaining" id="Hours_Remaining" class="form-control" min="0" step="1">
                        <div id="Hours_Remaining_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="enable_doc_upload" name="enable_doc_upload">
                        <label class="form-check-label" for="enable_doc_upload">
                             Enable Document Upload
                        </label>
                    </div>
                    </div>
                    <div id="resource_documents_container" style="display: none;">
                        <div id="resource_documents_items">
                            <div class="resource-documents-item border p-2 mt-2">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="resource_documents[0][name]" class="form-control">
                                    <div id="resource_documents_0_name_error" class="text-danger error_e"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">File</label>
                                    <input type="file" name="resource_documents[0][file]" class="form-control">
                                    <div id="resource_documents_0_file_error" class="text-danger error_e"></div>
                                </div>
                                <button type="button" class="btn btn-danger remove-documents-container">X</button>
                            </div>
                        </div>
                        <button type="button" id="addDocumentsContainer" class="btn btn-primary mt-2">Add More</button>
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
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="edit_ou_id" aria-label="Default select example" id="edit_select_org_unit">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>            
                    </div>
                    @endif
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
                        <label for="Type" class="form-label">Classroom</label>
                        <input type="text" name="edit_classroom" id="edit_classroom" class="form-control">
                        <div id="edit_classroom_error_up" class="text-danger error_e"></div>
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
                    <div class="form-group">
                        <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="edit_enable_doc_upload" name="enable_doc_upload">
                            <label class="form-check-label" for="edit_enable_doc_upload">
                                Enable Resource Upload
                            </label>
                        </div>
                    </div>
                    <div id="edit_resource_documents_container" style="display: none;">
                        <div id="edit_resource_documents_items">
                            <div class="edit-resource-documents-item border p-2 mt-2">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="resource_documents[0][name]" class="form-control">
                                    <div id="resource_documents_0_name_error_up" class="text-danger error_e"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">File</label>
                                    <input type="file" name="resource_documents[0][file]" class="form-control">
                                    <div id="resource_documents_0_file_error_up" class="text-danger error_e"></div>
                                </div>
                                <button type="button" class="btn btn-danger edit-remove-documents-container">X</button>
                            </div>
                        </div>
                        <button type="button" id="editAddDocumentsContainer" class="btn btn-primary mt-2">Add More</button>
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
            @if(auth()->user()->is_owner)
            { data: 'OU', name: 'OU' }, // dynamically add OU column for owners
            @endif            
            { data: 'name', name: 'name' ,class:'resource_name'},
            { data: 'registration', name: 'registration', class: 'orgUnitName' },
            { data: 'class', name: 'class' },
            { data: 'type', name: 'type' },
            { data: 'note', name: 'note' },
            @if(checkAllowedModule('resource','edit.index')->isNotEmpty() || checkAllowedModule('resource','delete.index')->isNotEmpty() || checkAllowedModule('resource','resource.show')->isNotEmpty())
                { data: 'action', name: 'action', class: 'text-center', orderable: false, searchable: false },
            @endif
        ]
    });

    $("#create_resource").on('click', function() { 
        $(".error_e").html('');
        $("#createResourceForm")[0].reset();
        $("#createResourceModel").modal('show'); 
    })

        // Toggle Instructor Documents section
    $("#enable_doc_upload").change(function () {
            if ($(this).is(":checked")) {
                $("#resource_documents_container").show();
            } else {
                $("#resource_documents_container").hide();
                // $("#prerequisite_items").empty();

                
            // Clear all inputs inside the container
            $("#resource_documents_container input").val('');
            $("#resource_documents_container input[type='file']").val('');
            $("#resource_documents_container .error_e").html('');

            // Optional: Reset to the default single resource item
            $('#resource_documents_items').html(`
                <div class="resource-documents-item border p-2 mt-2">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="resource_documents[0][name]" class="form-control">
                        <div id="resource_documents_0_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">File</label>
                        <input type="file" name="resource_documents[0][file]" class="form-control">
                        <div id="resource_documents_0_file_error" class="text-danger error_e"></div>
                    </div>
                </div>
            `);

            }
    });
    // Toggle Instructor Documents section On Editing
    $("#edit_enable_doc_upload").change(function () {
            if ($(this).is(":checked")) {
                $("#edit_resource_documents_container").show();
            } else {
                $("#edit_resource_documents_container").hide();
                // $("#prerequisite_items").empty();
            }
    });

        // Add New Documents Container
    $("#addDocumentsContainer").click(function() {
        let index = $(".resource-documents-item").length;

        let documentContainerHTML = `
                            <div class="resource-documents-item border p-2 mt-2">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="resource_documents[${index}][name]"  class="form-control">
                                    <div id="resource_documents_${index}_name_error" class="text-danger error_e"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">File</label>
                                    <input type="file" name="resource_documents[${index}][file]" class="form-control">
                                    <div id="resource_documents_${index}_file_error" class="text-danger error_e"></div>
                                </div>
                                <button type="button" class="btn btn-danger remove-documents-container">X</button>
                            </div>
        `;
        $("#resource_documents_items").append(documentContainerHTML);
    });

    // Remove Resource Documents section
    $(document).on("click", ".remove-documents-container", function() {
        $(this).closest(".resource-documents-item").remove();
    });

    // Remove Edit Resource Documents section
    $(document).on("click", ".edit-remove-documents-container", function() {
        $(this).closest(".edit-resource-documents-item").remove();
    });

    function generateDocumentsContainerHtml(resource_documents, index) {
        let documentName = resource_documents.name || '';
        let filePath = resource_documents.file_path ? `/storage/${resource_documents.file_path}` : '';
        let existingFilePath = resource_documents.file_path || '';
        let docRowId = resource_documents.id || '';

        let uploadedFileLinkHtml = '';
        if (filePath) {
            uploadedFileLinkHtml = `<div class="mt-2">
                                    <a href="${filePath}" target="_blank">View Uploaded Document</a>
                                </div>`;
        }

        return `<div class="edit-resource-documents-item border p-2 mt-2">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="resource_documents[${index}][name]" value="${documentName}" id="documents_name_${index}" class="form-control">
                        <div id="resource_documents_${index}_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">File</label>
                        <input type="file" name="resource_documents[${index}][file]" class="form-control">
                        <div id="resource_documents_${index}_file_error_up" class="text-danger error_e"></div>
                        ${uploadedFileLinkHtml}
                        <input type="hidden" name="resource_documents[${index}][existing_file_path]" value="${existingFilePath}">
                        <input type="hidden" name="resource_documents[${index}][row_id]" value="${docRowId}">
                    </div>
                    <button type="button" class="btn btn-danger edit-remove-documents-container mt-2">X</button>
                </div>`;
    }


    // Add New Documents Container while editing
    $("#editAddDocumentsContainer").click(function() {
        // Find the highest existing index first
        let maxIndex = 0;
        $(".edit-resource-documents-item").each(function() {
            $(this).find('input[name^="resource_documents"]').each(function() {
                let match = $(this).attr('name').match(/\[(\d+)\]/);
                if (match && parseInt(match[1]) > maxIndex) {
                    maxIndex = parseInt(match[1]);
                }
            });
        });

        // Increment to get the new index
        let newIndex = maxIndex + 1;

        let documentContainerHTML = `
            <div class="edit-resource-documents-item border p-2 mt-2">
                <div class="form-group">
                    <label class="form-label" for="documents_name_${newIndex}">Name</label>
                    <input type="text" name="resource_documents[${newIndex}][name]" id="documents_name_${newIndex}" class="form-control">
                    <div id="resource_documents_${newIndex}_name_error_up" class="text-danger error_e"></div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="documents_file_${newIndex}">File</label>
                    <input type="file" name="resource_documents[${newIndex}][file]" id="documents_file_${newIndex}" class="form-control">
                    <div id="resource_documents_${newIndex}_file_error_up" class="text-danger error_e"></div>
                </div>
                <button type="button" class="btn btn-danger edit-remove-documents-container">X</button>
            </div>
        `;
        $("#edit_resource_documents_items").append(documentContainerHTML);
    });

    $(document).ready(function () {

        $('#classroom').on('input', function() {
            let isClassroomFilled = $(this).val().trim() !== '';

            $('input[name="Hours_from_RTS"]').prop('disabled', isClassroomFilled);
            $('input[name="Date_from_RTS"]').prop('disabled', isClassroomFilled);
            $('input[name="Date_for_maintenance"]').prop('disabled', isClassroomFilled);
            $('input[name="Hours_Remaining"]').prop('disabled', isClassroomFilled);
        });

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

            // Also handle classroom field
            let shouldDisableClassroom = selectedField === "class" || selectedField === "type";
            $("input[name='" + formType + "classroom']").val("").prop("disabled", shouldDisableClassroom).css("background-color", shouldDisableClassroom ? "#e9ecef" : "");
        }

        function initFieldRestrictions(formType) {
            $("input[name='" + formType + "class'], input[name='" + formType + "type'], input[name='" + formType + "other']").on("input", function () {
                let selectedName = $(this).attr("name").replace(formType, "");
                if ($(this).val().trim() !== "") {
                    toggleAndClearFields(selectedName, formType);
                } else {
                    // Re-enable all related fields if input is cleared
                    ["class", "type", "other", "classroom"].forEach(field => {
                        $("input[name='" + formType + field + "']")
                            .prop("disabled", false)
                            .css("background-color", "");
                    });
                }
            });
        }

        // Initialize for both normal and edit forms
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
                    var formattedKey = key.replace(/\./g, '_') + '_error';
                    var errorMsg = '<p>' + value[0] + '</p>';
                    $('#' + formattedKey).html(errorMsg);
                })
            }
        });
    })

    $("#edit_classroom").on("input", function () {
        handleClassroomFieldDependency("edit_");
    });

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
                    $('input[name="edit_classroom"]').val(response.resourcedata.classroom || ''); 
                    $('input[name="edit_class"]').val(response.resourcedata.class || '');
                    $('input[name="edit_other"]').val(response.resourcedata.other || '');
                     $('input[name="edit_note"]').val(response.resourcedata.note || '');
                     $('input[name="edit_Hours_from_RTS"]').val(response.resourcedata.hours_from_rts || '');
                    $('input[name="edit_Date_from_RTS"]').val(response.resourcedata.date_from_rts || '');
                    $('input[name="edit_Date_for_maintenance"]').val(response.resourcedata.date_for_maintenance || '');
                     $('input[name="edit_Hours_Remaining"]').val(response.resourcedata.hours_remaining || '');
                     $('input[name="resourse_id"]').val(response.resourcedata.id || '');
                     $('#edit_select_org_unit').val(response.resourcedata.ou_id).trigger('change');

                  
                     if (response.resourcedata.resource_logo) {
                            // Reset the image and filename before updating
                            $('#resourse_logo_preview').attr('src', '').hide();
                            $('#resorse_logo_filename').text('');
                            $('#existing_resourse_logo').val('');

                            // Now, set the new values
                            let fileName = response.resourcedata.resource_logo;
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
                    handleClassroomFieldDependency("edit_");

                     //Handle Document Container
                    if (response.resourcedata.enable_doc_upload) {
                        $('#edit_enable_doc_upload').prop('checked', true);
                        $('#edit_resource_documents_container').show();
                    } else {
                        $('#edit_enable_doc_upload').prop('checked', false);
                        $('#edit_resource_documents_container').hide();
                    }

                    $('#edit_resource_documents_items').empty();  // Clear existing containers
                    let resource_documents = response.resourcedata.documents;
                    // console.log(instructor_documents);
                    if (resource_documents.length > 0) {
                        resource_documents.forEach((resource_documents, index) => {
                            let resourceDocumentHtml = generateDocumentsContainerHtml(
                                resource_documents, index
                            );
                            $('#edit_resource_documents_items').append(resourceDocumentHtml);
                        });
                    } else {
                        let resourceDocumentHtml = generateDocumentsContainerHtml({
                            document_name: '',
                            file_path: ''
                        }, 0);
                        $('#edit_resource_documents_items').append(resourceDocumentHtml);
                    }
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

    function handleClassroomFieldDependency(formType) {
        const classroomValue = $("input[name='" + formType + "classroom']").val().trim();

        const rtsFields = [
            "Hours_from_RTS",
            "Date_from_RTS",
            "Date_for_maintenance",
            "Hours_Remaining"
        ];

        rtsFields.forEach(field => {
            const fullFieldName = "input[name='" + formType + field + "']";
            if (classroomValue !== "") {
                $(fullFieldName).val("").prop("disabled", true).css("background-color", "#e9ecef");
            } else {
                $(fullFieldName).prop("disabled", false).css("background-color", "");
            }
        });
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
                    var formattedKey = key.replace(/\./g, '_') + '_error_up';
                    var errorMsg = '<p>' + value[0] + '</p>';
                    $('#' + formattedKey).html(errorMsg);
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