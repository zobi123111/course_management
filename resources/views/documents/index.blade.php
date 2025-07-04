@section('title', 'Documents')
@section('sub-title', 'Documents')
@extends('layout.app')
@section('content')

@if(session()->has('message'))  
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
            {{ session()->get('message') }} 
    </div>
@endif

@if(checkAllowedModule('documents','document.store')->isNotEmpty())
<div class="create_btn">
    <button class="btn btn-primary create-button" id="createDocument" data-toggle="modal"
    data-target="#createDocumentModal">Create Document</button>
</div>
@endif
<br>
<div class="card pt-4">
    <div class="card-body">
        <table class="table" id="documentTable">
            <thead>
                <tr>
                    <th>Document Title</th>
                    <th>Version Number</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Assigned Group</th>
                    <th>Document</th>
                    <th>Status</th>
                    <th>Acknowledged</th>
                    @if(checkAllowedModule('documents','document.edit')->isNotEmpty())
                        <th>Edit</th>
                    @endif
                    @if(checkAllowedModule('documents','document.delete')->isNotEmpty())
                        <th>Delete</th>
                    @endif
                </tr>
            </thead>
        </table>
    </div>  
</div>

<!-- Group Users List Modal -->
<div class="modal fade" id="groupUsersModal" tabindex="-1" role="dialog" aria-labelledby="groupUsersModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupUsersModalLabel">Group Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <table class="table" id="groupUsersTable">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Aknowledged</th>
                    </tr>
                </thead>
                <tbody id="tblBody">                    
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<!--End of Group Users List Modal-->

<!-- Create Document--> 
<div class="modal fade" id="createDocumentModal" tabindex="-1" role="dialog" aria-labelledby="documentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Create Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="documentsForm" method="POST" class="row g-3 needs-validation" enctype="multipart/form-data">
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
                        <label for="firstname" class="form-label">Document Title<span class="text-danger">*</span></label>
                        <input type="text" name="doc_title" class="form-control">
                        <div id="course_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="form-label">Version Number<span class="text-danger">*</span></label>
                        <input type="text" name="version_no" class="form-control">
                        <div id="version_no_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Issue Date<span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control">
                        <div id="issue_date_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Expiry Date<span class="text-danger">*</span></label>
                        <input type="date" name="expiry_date" class="form-control">
                        <div id="expiry_date_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Upload Document<span class="text-danger">*</span></label>
                        <input type="file" name="document_file" class="form-control">
                        <div id="document_file_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Folder<span class="text-danger">*</span></label>
                        <select class="form-select all-folders" name="folder" aria-label="Default select example">
                            <option value="">No Parent (Root Folder)</option>
                                @foreach($folders as $folder)
                                    @include('folders.partials.folder_option', ['folder' => $folder, 'level' => 0])
                                @endforeach
                        </select>
                        </select>
                        <div id="folder_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="form-group">
                        <label for="users" class="form-label">Assign to Group<span class="text-danger">*</span></label>
                        <select class="form-select group-select" name="group[]" id="group" multiple="multiple">
                        <!-- <option value="">Select Group</option> -->
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_error" class="text-danger error_e"></div> 
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error" class="text-danger error_e"></div>            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitDocument" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Document-->

<!-- Edit Document -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" role="dialog" aria-labelledby="editDocumentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDocumentModalLabel">Edit Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDocumentForm" method="POST"  class="row g-3 needs-validation" enctype="multipart/form-data">
                    @csrf
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example"  id="edit_select_org_unit">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>            
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="firstname" class="form-label">Document Title<span class="text-danger">*</span></label>
                        <input type="text" name="doc_title" id="edit_doc_title" class="form-control">
                        <div id="course_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="form-label">Version Number<span class="text-danger">*</span></label>
                        <input type="text" name="version_no" id="edit_version_no" class="form-control">
                        <input type="hidden" name="document_id" id="document_id" class="form-control">
                        <div id="version_no_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Issue Date<span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" id="edit_issue_date" class="form-control">
                        <div id="issue_date_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Expiry Date<span class="text-danger">*</span></label>
                        <input type="date" name="expiry_date" id="edit_expiry_date"  class="form-control">
                        <div id="expiry_date_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Upload Document<span class="text-danger">*</span></label>
                        <input type="file" name="document_file" id="edit_document_file" class="form-control">
                        <div id="document_file_error_up" class="text-danger error_e"></div>            
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Select Folder<span class="text-danger">*</span></label>
                        <select class="form-select edit-all-folders" name="folder" id="edit_folder" aria-label="Default select example">
                            <option value="">No Parent (Root Folder)</option>
                                @foreach($folders as $folder)
                                    @include('folders.partials.folder_option', [
                                        'folder' => $folder, 
                                        'level' => 0
                                    ])
                                @endforeach
                        </select>
                        <div id="folder_error_up" class="text-danger error_e"></div>          
                    </div>
                    <div class="form-group">
                        <label for="users" class="form-label">Assign to Group<span class="text-danger">*</span></label>
                        <select class="form-select group-select" name="group[]" id="edit_group" multiple="multiple">
                        <!-- <option value="">Select Group</option> -->
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <div id="group_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Status<span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="edit_status" aria-label="Default select example">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div id="status_error_up" class="text-danger error_e"></div>
                    </div>  
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateDocument" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End Edit Document-->

<!--Courses Document  Modal -->
<form action="{{ url('document/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteDocument" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Document</h5>
                    <input type="hidden" name="document_id" id="documentId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Document "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary delete_document">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Courses Delete Model -->
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {

    // Initialize Select2 globally on all user selection dropdowns
    function initializeSelect2() {
        $('.group-select').select2({
            allowClear: true,
            placeholder: 'Select the Groups',
            multiple: true,
            dropdownParent: $('#createDocumentModal .modal-content:visible, #editDocumentModal .modal-content:visible') // More specific
        });
    }

    initializeSelect2(); // Call on page load
    $('#documentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('documents.data') }}",
        columns: [
            { data: 'doc_title', name: 'doc_title', class: 'docTitle' },
            { data: 'version_no', name: 'version_no' },
            { data: 'issue_date', name: 'issue_date' },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'assigned_group', name: 'assigned_group' },
            { data: 'document', name: 'document', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
            { data: 'acknowledged', name: 'acknowledged' },
            @if(checkAllowedModule('documents','document.edit')->isNotEmpty())
                { data: 'edit', name: 'edit', orderable: false, searchable: false },
            @endif
            @if(checkAllowedModule('documents','document.delete')->isNotEmpty())
                { data: 'delete', name: 'delete', orderable: false, searchable: false },
            @endif
        ]
    });

    $('#documentTable').on('click', '.get_group_users', function() {
        var doc_id = $(this).data('doc-id');
        var group_id = $(this).data('group-id');

        $.ajax({               
            url: "{{ url('document/user_list') }}",
            type: 'GET', 
            data: {
                doc_id: doc_id,
                group_id: group_id  
            },
            success: function(response) {
                console.log(response);

                // Clear previous data
                $('#groupUsersTable tbody').html('');

                if (!response.groupUsers || response.groupUsers.length === 0) {
                    $('#groupUsersTable tbody').html('<tr><td colspan="4" class="text-center">No users found for this Group.</td></tr>');
                } else {
                    // Append new data
                    response.groupUsers.forEach(user => {
                        var imageUrl = user.image 
                            ? "{{ asset('storage') }}/" + user.image 
                            : "{{ asset('assets/img/no_image.png') }}"; // Default image if none provided
                        
                        var acknowledgeStatus = user.acknowledged 
                            ? '<span style="color: green;">✔</span>' 
                            : '<span style="color: red;">❌</span>';

                        var row = `
                            <tr>
                                <td><img src="${imageUrl}" alt="Profile Image" width="40" height="40" class="rounded-circle"></td>
                                <td>${user.fname} ${user.lname}</td>
                                <td>${user.email}</td>
                                <td>${acknowledgeStatus}</td>
                            </tr>`;
                        $('#groupUsersTable tbody').append(row);
                    });
                }

                // Show modal
                $('#groupUsersModal').modal('show');
            },
            error: function(xhr, status, error) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    $('#groupUsersTable tbody').html('<tr><td colspan="4" class="text-center">' + (response.error || 'An unknown error occurred.') + '</td></tr>');
                } catch (e) {
                    $('#groupUsersTable tbody').html('<tr><td colspan="4" class="text-center">Failed to fetch users. Please try again.</td></tr>');
                }
                $('#groupUsersModal').modal('show');
            }
        });
    });

    $(document).on("click","#createDocument", function(){
        $(".error_e").html('');
        $("#documentsForm")[0].reset();
        $(".group-select").val(null).trigger("change"); // Reset Select2
        $("#createDocumentModal").modal('show');
        
    })

    $("#submitDocument").on("click", function(e){
        e.preventDefault();
        $(".loader").fadeIn();
        $(".error_e").html('');
        var formData = new FormData($('#documentsForm')[0]); 
        
        $.ajax({
            url: '{{ url("/document/create") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $(".loader").fadeOut("slow");
                $('#createDocumentModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error){
                $(".loader").fadeOut("slow");
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key,value){
                    var msg = '<p>'+value+'<p>';
                    $('#'+key+'_error').html(msg); 
                }) 
            }
        });

    })

    $(document).on("click",".edit-document-icon",function(e) {
        e.preventDefault();
        $('.error_e').html('');
        var $groupSelect = $(".group-select"); 
       
        var $folderSelect = $(".edit-all-folders");
        var documentId = $(this).data('document-id');
        $.ajax({
            url: "{{ url('/document/edit') }}", 
            type: 'GET',
            data: {  id: documentId },
            success: function(response) {
                
                if (response.group && Array.isArray(response.group)) { 
                    var options = "<option value=''>Select Group </option>"; 
                    
                    response.group.forEach(function(value){
                        options += "<option value='" + value.id + "'>" + value.name  + "</option>";
                    });
                    $groupSelect.html(options); 
                    $groupSelect.trigger("change");
                } 
                if (response.folders) { 
                var options = "<option value=''>No Parent (Root Folder)</option>";

                // Recursive function to generate folder options with indentation
                function generateFolderOptions(folder, level) {
                    var indent = "&nbsp;&nbsp;&nbsp;&nbsp;".repeat(level); // Indentation
                    var option = `<option value="${folder.id}">${indent}${folder.folder_name}</option>`;

                    if (folder.children_recursive && folder.children_recursive.length > 0) {
                        folder.children_recursive.forEach(child => {
                            option += generateFolderOptions(child, level + 1);
                        });
                    }
                    return option;
                }

                // Process only the top-level folders
                response.folders.forEach(function(folder) {
                    options += generateFolderOptions(folder, 0);
                });

                $folderSelect.html(options);
                 $folderSelect.trigger("change");
            }
                $('#edit_doc_title').val(response.document.doc_title);
                $('#edit_version_no').val(response.document.version_no);
                $('#document_id').val(response.document.id);
                $('#edit_issue_date').val(response.document.issue_date);
                $('#edit_expiry_date').val(response.document.expiry_date);
                $('#edit_folder').val(response.document.folder_id);
                $('#edit_status').val(response.document.status);
                $('#edit_select_org_unit').val(response.document.ou_id); 

                $("#edit_group").val(null).trigger("change");

                // Populate Select2 with multiple groups
                let selectedGroupIds = response.document.groups.map(group => group.id);
                $('#edit_group').val(selectedGroupIds).trigger('change');
                
                $('#editDocumentModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $(document).on('click','#updateDocument', function(e){
        $(".loader").fadeIn('fast');
        e.preventDefault();
        var formData = new FormData($('#editDocumentForm')[0]);
        $.ajax({
            url: "{{ url('/document/update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){
                $(".loader").fadeIn('fast');
                $('#editDocumentModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error){
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key,value){
                    var msg = '<p>'+value+'<p>';
                    $('#'+key+'_error_up').html(msg); 
                }) 
            }
        })
    })

    $(document).on("click",".delete-document-icon", function(e) {
        e.preventDefault();
        $('#deleteDocument').modal('show');
        var documentId = $(this).data('document-id');
        var documentName = $(this).closest('tr').find('.docTitle').text();
        $('#append_name').html(documentName);
        $('#documentId').val(documentId);
      
    });

    setTimeout(function() {
            $('#successMessage').fadeOut('slow');
        }, 3000);

    $(document).on("change", "#select_org_unit", function(){ 
        var ou_id = $(this).val(); 
        var $groupSelect = $(".group-select"); 
        var $folderSelect = $(".all-folders");

        $.ajax({
            url: "/document/get_ou_folder/",
            type: "GET",
            data: { 'ou_id': ou_id },
            dataType: "json",
            success: function(response){
                console.log(response);
                if (response.org_group && Array.isArray(response.org_group)) { 
                        var options = "<option value=''>Select Group </option>"; 
                        
                        response.org_group.forEach(function(value){
                            options += "<option value='" + value.id + "'>" + value.name  + "</option>";
                        });
                        $groupSelect.html(options); 
                        $groupSelect.trigger("change");
                    } 

                if (response.org_folder && Array.isArray(response.org_folder)) { 
                    var options = "<option value=''>No Parent (Root Folder)</option>";

                    // Recursive function to generate folder options with indentation
                    function generateFolderOptions(folder, level) {
                        var indent = "&nbsp;&nbsp;&nbsp;&nbsp;".repeat(level); // Indentation
                        var option = `<option value="${folder.id}">${indent}${folder.folder_name}</option>`;

                        if (folder.children_recursive && folder.children_recursive.length > 0) {
                            folder.children_recursive.forEach(child => {
                                option += generateFolderOptions(child, level + 1);
                            });
                        }
                        return option;
                    }

                    // Process only the top-level folders
                    response.org_folder.forEach(function(folder) {
                        options += generateFolderOptions(folder, 0);
                    });

                    $folderSelect.html(options);
                    $folderSelect.trigger("change");
                }
            },
            error: function(xhr, status, error){
            console.error(xhr);
            } 
        });
    });


    $(document).on("change", "#edit_select_org_unit", function(){ 
        var ou_id = $(this).val(); 
        var $groupSelect = $(".group-select"); 
        var $folderSelect = $(".edit-all-folders");

        $.ajax({
            url: "/document/get_ou_folder/",
            type: "GET",
            data: { 'ou_id': ou_id },
            dataType: "json",
            success: function(response){
                if (response.org_group && Array.isArray(response.org_group)) { 
                        var options = "<option value=''>Select Group </option>"; 
                        
                        response.org_group.forEach(function(value){
                            options += "<option value='" + value.id + "'>" + value.name  + "</option>";
                        });
                        $groupSelect.html(options); 
                        $groupSelect.trigger("change");
                    } 
                if (response.org_folder && Array.isArray(response.org_folder)) { 
                    var options = "<option value=''>No Parent (Root Folder)</option>";

                //  Recursive function to generate folder options with indentation
                    function generateFolderOptions(folder, level) {
                        var indent = "&nbsp;&nbsp;&nbsp;&nbsp;".repeat(level); // Indentation
                        var option = `<option value="${folder.id}">${indent}${folder.folder_name}</option>`;

                        if (folder.children_recursive && folder.children_recursive.length > 0) {
                            folder.children_recursive.forEach(child => {
                                option += generateFolderOptions(child, level + 1);
                            });
                        }
                        return option;
                    }

                // Process only the top-level folders
                    response.org_folder.forEach(function(folder) {
                        options += generateFolderOptions(folder, 0);
                    });

                    $folderSelect.html(options);

                    $folderSelect.trigger("change");
                }
            },
            error: function(xhr, status, error){
                console.error(xhr.responseText);
            } 
        });
    });

    // $('#createDocumentModal').on('shown.bs.modal', function () {
    //     initializeSelect2();
    // });

    $(document).on("shown.bs.modal", "#createDocumentModal, #editDocumentModal", function(){
        initializeSelect2();
    })


});

</script>

@endsection