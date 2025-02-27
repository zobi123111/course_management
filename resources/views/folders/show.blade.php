
@section('title', 'Sub Folder')
@section('sub-title', 'Sub Folders')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="alertMessage" class="alert alert-success fade show" role="alert">
  <i class="bi bi-check-circle me-1"></i>
  {{ session()->get('message') }}
</div>
@endif

<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('folder.index') }}">Folders</a></li>
        @foreach($breadcrumbs as $breadcrumb)
            @if ($loop->last)
                <li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>

<div class="card pt-4">
    <div class="card-body">
    <h3 class="mb-3">Sub Folders List</h3>
        <table class="table table-bordered table-hover" id="subfoldersTable">
            <thead class="table-dark">
                <tr>
                <th>#</th>
                <th scope="col">Folder Name</th>
                <th scope="col">Description</th>
                <th scope="col">Status</th>
                <th scope="col">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div class="card pt-4">
    <div class="card-body">
    <h3 class="mb-3">Documents List</h3>
        @if($documents->isNotEmpty())
            <div class="table-responsive">
                <table id="subFoldersDocsTable" class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Document Name</th>
                            <th>Uploaded On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>                  
                </table>
            </div>
        @else
            <p class="alert alert-warning">No documents available in the current directory.</p>
        @endif
    </div>
</div>
<!-- Create Courses-->
<div class="modal fade" id="createFolderModal" tabindex="-1" role="dialog" aria-labelledby="folderModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="folderModalLabel">Create Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="folders" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <!-- Parent Folder Selection -->
                    <div class="form-group">
                        <label for="parent_id" class="form-label">Parent Folder<span class="text-danger">*</span></label>
                        <select class="form-select" name="parent_id" aria-label="Select Parent Folder">
                            <option value="">No Parent (Root Folder)</option>
                            @foreach($folders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->folder_name }}</option>
                            @endforeach
                        </select>
                        <div id="parent_id_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="form-label">Folder Name<span class="text-danger">*</span></label>
                        <input type="text" name="folder_name" class="form-control">
                        <div id="folder_name_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description"  rows="3"></textarea>
                        <div id="description_error" class="text-danger error_e"></div>
                    </div>
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" aria-label="Default select example">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>            
                    </div>
                    @endif
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
                        <button type="button" id="submitFolder" class="btn btn-primary sbt_btn">Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Courses-->

<!-- Edit Courses -->
<div class="modal fade" id="editFolderModal" tabindex="-1" role="dialog" aria-labelledby="editFolderModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFolderModalLabel">Edit Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFolder" class="row g-3 needs-validation">
                    @csrf
                    <!-- Parent Folder Selection -->
                    <div class="form-group">
                        <label for="parent_id" class="form-label">Parent Folder<span class="text-danger">*</span></label>
                        <select class="form-select" name="parent_id" id="edit_parent_folder" aria-label="Select Parent Folder">
                        <option value="">No Parent (Root Folder)</option>
                            @foreach($folders as $folder)
                                @include('folders.partials.folder_option', ['folder' => $folder, 'level' => 0])
                            @endforeach
                        </select>
                        <div id="parent_id_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="form-label">Folder Name<span class="text-danger">*</span></label>
                        <input type="text" name="folder_name" id="edit_folder_name" class="form-control">
                        <input type="hidden" name="folder_id" id="folder_id" class="form-control">
                        <div id="folder_name_error_up" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Description<span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        <div id="description_error_up" class="text-danger error_e"></div>
                    </div>
                    @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                    <div class="form-group">
                        <label for="email" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                        <select class="form-select" name="ou_id" id="edit_ou_id" aria-label="Default select example">
                            <option value="">Select Org Unit</option>
                            @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                            @endforeach
                        </select>
                        <div id="ou_id_error" class="text-danger error_e"></div>            
                    </div>
                    @endif
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
                        <button type="button" id="updateFolder" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End Edit Courses-->

<!--Courses Delete  Modal -->
<form action="{{ url('folder/delete') }}" method="POST">
    @csrf
    <div class="modal fade" id="deleteFolder" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="folder_id" id="folderId" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Folder "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary delete_folder">Delete</button>
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
    let folderId = "{{ request()->folder_id }}"; // Get current folder ID from request

    $('#subfoldersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('folders.subfolders.list') }}",
            data: { folder_id: folderId } // Send folder_id as parameter
        },
        columns: [
            { data: 'index', name: 'index', orderable: false, searchable: false },
            { data: 'folder_name', name: 'folder_name' },
            { data: 'description', name: 'description' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $('#subFoldersDocsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('subfolder.documents') }}",
            data: { folder_id: folderId } // Send folder_id as parameter
        },
        columns: [
            { data: 'index', name: 'index' },
            { data: 'document_name', name: 'original_filename' },
            { data: 'uploaded_on', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    $("#createFolder").on('click', function(){
        $(".error_e").html('');
        $("#folders")[0].reset();
        $("#createFolderModal").modal('show');
    })

    $("#submitFolder").on("click", function(e){
        e.preventDefault();
        $.ajax({
            url: '{{ url("/folder/create") }}',
            type: 'POST',
            data: $("#folders").serialize(),
            success: function(response) {
                $('#createFolderModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error){
                if(xhr.responseJSON && xhr.responseJSON.error){
                    alert(xhr.responseJSON.error);
                }
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key,value){
                    var msg = '<p>'+value+'<p>';
                    $('#'+key+'_error').html(msg); 
                }) 
            }
        });

    })

    $(document).on("click", ".edit-folder-icon", function(e) {
        e.preventDefault();

        $('.error_e').html('');
        var folderId = $(this).data('folder-id');
        $.ajax({
            url: "{{ url('/folder/edit') }}", 
            type: 'GET',
            data: { id: folderId },
            success: function(response) {
                console.log(response);
                $('#edit_parent_folder').val(response.folder.parent_id).trigger('change');
                $('#edit_folder_name').val(response.folder.folder_name);
                $('#folder_id').val(response.folder.id);
                $('#edit_description').val(response.folder.description);
                $('#edit_ou_id').val(response.folder.ou_id);
                $('#edit_status').val(response.folder.status);

                $('#editFolderModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#updateFolder').on('click', function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ url('folder/update') }}",
            type: "POST",
            data: $("#editFolder").serialize(),
            success: function(response){
                $('#editFolderModal').modal('hide');
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

    $(document).on("click", ".delete-folder-icon",function(e) {
        e.preventDefault();
        $('#deleteFolder').modal('show');
        var folderId = $(this).data('folder-id');
        var folderName = $(this).closest('tr').find('.folderName').text();
        $('#append_name').html(folderName);
        $('#folderId').val(folderId);
      
    });
    setTimeout(function() {
        $('#alertMessage').fadeOut('slow');
    }, 2000);

});
</script>

@endsection