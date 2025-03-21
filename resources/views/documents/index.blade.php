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
                    <th>Document</th>
                    <th>Status</th>
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
                        <select class="form-select" name="folder" aria-label="Default select example">
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
                        <select class="form-select group-select" name="group" id="group">
                        <option value="">Select Group</option>
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
                        <select class="form-select" name="folder" id="edit_folder" aria-label="Default select example">
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
                        <select class="form-select group-select" name="group" id="edit_group">
                        <option value="">Select Group</option>
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                        <div id="user_ids_error" class="text-danger error_e"></div>
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
    $('#documentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('documents.data') }}",
        columns: [
            { data: 'doc_title', name: 'doc_title' },
            { data: 'version_no', name: 'version_no' },
            { data: 'issue_date', name: 'issue_date' },
            { data: 'expiry_date', name: 'expiry_date' },
            { data: 'document', name: 'document', orderable: false, searchable: false },
            { data: 'status', name: 'status' },
            @if(checkAllowedModule('documents','document.edit')->isNotEmpty())
                { data: 'edit', name: 'edit', orderable: false, searchable: false },
            @endif
            @if(checkAllowedModule('documents','document.delete')->isNotEmpty())
                { data: 'delete', name: 'delete', orderable: false, searchable: false },
            @endif
        ]
    });

    $(document).on("click","#createDocument", function(){
        $(".error_e").html('');
        $("#documentsForm")[0].reset();
        $("#createDocumentModal").modal('show');
    })

    $("#submitDocument").on("click", function(e){
        e.preventDefault();
        $(".loader").fadeIn();
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
        var documentId = $(this).data('document-id');
        $.ajax({
            url: "{{ url('/document/edit') }}", 
            type: 'GET',
            data: {  id: documentId },
            success: function(response) {
                $('#edit_doc_title').val(response.document.doc_title);
                $('#edit_version_no').val(response.document.version_no);
                $('#document_id').val(response.document.id);
                $('#edit_issue_date').val(response.document.issue_date);
                $('#edit_expiry_date').val(response.document.expiry_date);
                $('#edit_folder').val(response.document.folder_id);
                $('#edit_group').val(response.document.group_id);
                $('#edit_status').val(response.document.status);

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

});
</script>

@endsection