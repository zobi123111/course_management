@section('title', 'Documents')
@section('sub-title', 'Documents')
@extends('layout.app')
@section('content')
<style>
.doc-tile {
    background-color: #d9d9d9;
    border-radius: 8px;
    padding: 12px 16px;
    min-width: 200px;
    max-width: 100%;
    transition: background-color 0.2s ease;
    display: inline-block;
    color: #333;
}

.doc-tile:hover {
    background-color: #e8ecf1;
    text-decoration: none;
}

.doc-tile-inner {
    display: flex;
    align-items: center;
    gap: 10px;
}

.doc-title {
    font-weight: 500;
    font-size: 0.95rem;
    color: #1a1a1a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
span.ack-icon .fa-solid.text-success:before {
    color: #198754 !important;
}
span.ack-icon .fa-solid.text-danger:before {
    color: #dc3545 !important;
}
</style>
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
        @php
            // Group documents by their folder_id
            $groupedFolders = $documents->groupBy('folder_id');
        @endphp

        @if($groupedFolders->isNotEmpty())
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                @foreach($groupedFolders as $folderId => $docs)
                    @php
                        $folder = $docs->first()->folder;
                        $isFirst = $loop->first;
                    @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $isFirst ? 'active' : '' }}"
                                id="tab-{{ $folderId }}"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-content-{{ $folderId }}"
                                type="button"
                                role="tab"
                                aria-controls="tab-content-{{ $folderId }}"
                                aria-selected="{{ $isFirst ? 'true' : 'false' }}">
                            {{ $folder->folder_name ?? 'No Folder' }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- Tab Content -->
            <div class="tab-content pt-3" id="myTabContent">
                @foreach($groupedFolders as $folderId => $docs)
                    @php
                        $folder = $docs->first()->folder;
                        $isFirst = $loop->first;
                    @endphp

                    <div class="tab-pane fade {{ $isFirst ? 'show active' : '' }}"
                        id="tab-content-{{ $folderId }}"
                        role="tabpanel"
                        aria-labelledby="tab-{{ $folderId }}">

                        <h4 class="mb-4">{{ $folder->folder_name ?? 'No Folder' }}</h4>
                        <hr>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($docs as $doc)
                                @php
                                    $groupUserIds = is_array($doc->group->user_ids ?? []) 
                                        ? $doc->group->user_ids 
                                        : explode(',', $doc->group->user_ids ?? '');

                                    $ackUsers = json_decode($doc->acknowledge_by ?? '[]', true);
                                    $isFullyAck = !empty($groupUserIds) && !array_diff($groupUserIds, $ackUsers);

                                    $ackDisplay = auth()->user()->is_admin || auth()->user()->is_owner
                                        ? ($isFullyAck ? '✔' : '❌')
                                        : (in_array(auth()->user()->id, $ackUsers) ? '✔' : '❌');

                                    $ackColor = $ackDisplay === '✔' ? 'text-success' : 'text-danger';
                                    $ackTooltip = $ackDisplay === '✔' ? 'Acknowledged' : 'Acknowledgment Pending';
                                @endphp
                                <a href="{{ route('document.show', encode_id($doc->id)) }}"
                                target="_blank"
                                class="doc-tile text-decoration-none" 
                                title="{{ $doc->doc_title }}">
                                    <div class="doc-tile-inner d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <i class="fa fa-file-alt fa-lg text-muted"></i>
                                            <span class="doc-title">
                                                <strong>Title:</strong> {{ $doc->doc_title }} |
                                                <strong>Version No:</strong> {{ $doc->version_no }} |
                                                <strong>Issue Date:</strong> {{ ($doc->issue_date ? date('d/m/Y', strtotime($doc->issue_date)): '') }} |
                                                <strong>Expiry Date:</strong> {{ ($doc->expiry_date? date('d/m/Y', strtotime($doc->expiry_date)): '') }}
                                            </span>
                                        </div>
                                        <span class="ack-icon ms-3" title="{{ $ackTooltip }}">
                                            @if($ackDisplay === '✔')
                                                <i class="fa-solid fa-check-circle text-success"></i>
                                            @else
                                                <i class="fa-solid fa-times-circle text-danger"></i>
                                            @endif
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info mt-3">No documents found with matching folder-group access.</div>
        @endif
    </div>
</div>

<!-- End Courses Delete Model -->
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {


    $('#documentTable').on('click', '.get_group_users', function() {
        var doc_id = $(this).data('doc-id');

        $.ajax({               
            url: "{{ url('document/user_list') }}",
            type: 'GET', 
            data: { doc_id: doc_id },
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
                var selectedGroups = response.document.folder_id;
              
                $('#edit_group').val(response.document.group_id).trigger('change');
                $('#edit_status').val(response.document.status);
                $('#edit_select_org_unit').val(response.document.ou_id); 
                $('#edit_completed_date').val(response.document.completed_date); 
                $('#edit_document_type').val(response.document.document_type); 
                  
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


});

</script>

@endsection