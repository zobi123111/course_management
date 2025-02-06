
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
<table class="table" id="documentTable">
  <thead>
    <tr>
      <th scope="col">Version Number</th>
      <th scope="col">Issue date</th>
      <th scope="col">Expiry Date</th>
      <th scope="col">Document</th>
      @if(checkAllowedModule('courses','course.edit')->isNotEmpty())
      <th scope="col">Edit</th>
      @endif
      @if(checkAllowedModule('courses','course.delete')->isNotEmpty())
      <th scope="col">Delete</th>
      @endif
    </tr>
  </thead>
  <tbody>
    @foreach($documents as $val)
            <tr>
                <td class="courseName">{{ $val->version_no}}</td>
                <td>{{ $val->issue_date}}</td>
                <td>{{ $val->expiry_date}}</td>
                <td>
                    @if($val->document_file)
                    <a href="{{ asset('storage/'.$val->document_file) }}" target="_blank">View Document</a>
                    @else
                    No File uploaded
                    @endif
                </td>
                @if(checkAllowedModule('documents','document.edit')->isNotEmpty())
                    <td><i class="fa fa-edit edit-document-icon" style="font-size:25px; cursor: pointer;" data-document-id="{{ encode_id($val->id) }}" ></i></td>
                @endif
                @if(checkAllowedModule('documents','document.delete')->isNotEmpty())
                    <td><i class="fa-solid fa-trash delete-document-icon" style="font-size:25px; cursor: pointer;" data-document-id="{{ encode_id($val->id) }}" ></i></td>
                @endif
            </tr> 
    @endforeach
  </tbody>
</table>

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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitDocument" class="btn btn-primary sbt_btn">Save </button>
                    </div>
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
    $('#courseTable').DataTable();

    $("#createDocument").on('click', function(){
        $(".error_e").html('');
        $("#documentsForm")[0].reset();
        $("#createDocumentModal").modal('show');
    })

    $("#submitDocument").on("click", function(e){
        e.preventDefault();

        var formData = new FormData($('#documentsForm')[0]);
        
        $.ajax({
            url: '{{ url("/document/create") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#createDocumentModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error){
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key,value){
                    var msg = '<p>'+value+'<p>';
                    $('#'+key+'_error').html(msg); 
                }) 
            }
        });

    })

    $('.edit-document-icon').click(function(e) {
        e.preventDefault();

        $('.error_e').html('');
        var documentId = $(this).data('document-id');
        $.ajax({
            url: "{{ url('/document/edit') }}", 
            type: 'GET',
            data: {  id: documentId },
            success: function(response) {
                $('#edit_version_no').val(response.document.version_no);
                $('#document_id').val(response.document.id);
                $('#edit_issue_date').val(response.document.issue_date);
                $('#edit_expiry_date').val(response.document.expiry_date);

                $('#editDocumentModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#updateDocument').on('click', function(e){
        e.preventDefault();

        var formData = new FormData($('#editDocumentForm')[0]);
        $.ajax({
            url: "{{ url('/document/update') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response){
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

    $('.delete-document-icon').click(function(e) {
    e.preventDefault();
        $('#deleteDocument').modal('show');
        var documentId = $(this).data('document-id');
        var documentName = $(this).closest('tr').find('.courseName').text();
        $('#append_name').html(documentName);
        $('#documentId').val(documentId);
      
    });

});
</script>

@endsection