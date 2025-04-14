@extends('layout.app')

@section('title', 'Folder List')
@section('sub-title', 'Folder List')
@section('content')

@if(session()->has('message'))
<div id="alertMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

@if(checkAllowedModule('courses','course.store')->isNotEmpty())
<div class="create_btn">
    <button class="btn btn-primary create-button" id="createFolder" data-toggle="modal"
        data-target="#createFolderModal">Create Folders</button>
</div>
@endif

<div class="row">
    <!-- Folders Card -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Folders</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($folders as $folder)
                    <div class="col-md-3 col-sm-4 mb-4">
                        <div class="folder-wrapper" onclick="openFolder('{{ $folder->id }}')">
                            <div class="folder-visual">
                                <div class="folder-container">
                                    <div class="folder-tab"></div>
                                    <div class="folder-icon">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-2 fw-bold folder_name">{{ $folder->folder_name }}</div>
                            <div class="folder-actions">
                                <a href="{{ url('folder/show/'.encode_id($folder->id)) }}" title="View"
                                    onclick="event.stopPropagation();">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="javascript:void(0);" title="Edit"
                                    onclick="editFolder('{{ encode_id($folder->id) }}')">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <a href="javascript:void(0);" title="Delete"
                                    onclick="deleteFolder('{{ encode_id($folder->id) }}', '{{ $folder->folder_name }}');   event.stopPropagation();">
                                    <i class="fas fa-trash"></i>
                                </a>


                            </div>
                        </div>
                    </div>

                    @endforeach
                </div>
            </div>
        </div>

        <!-- Documents Section -->
        <div class="row">
            <!-- Folders Card -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Documents</h5>
                    </div>
                    <div class="card-body">
                        @if(count($documents))

                        <div class="row">
                            @foreach ($documents as $doc)
                            <div class="col-md-3 col-sm-4 mb-4">
                                <div class="folder-wrapper"
                                    onclick="window.open('{{ asset('storage/' . $doc->file_path) }}', '_blank')">
                                    <div class="folder-visual">
                                        <div class="folder-container" style="background-color: #60a5fa;">
                                            <div class="folder-tab" style="background-color: #fff;"></div>
                                            <div class="folder-icon">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2 fw-bold document_title">{{ $doc->original_filename }}
                                    </div>
                                    <div class="folder-actions">
                                        <a href="{{ Storage::url($doc->document_file) }}" title="View"
                                            onclick="event.stopPropagation();">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ Storage::url($doc->document_file) }}" title="View"
                                            onclick="event.stopPropagation();" download>
                                            <i class="fas fa-download"></i>
                                        </a>

                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Courses-->
        <div class="modal fade" id="createFolderModal" tabindex="-1" role="dialog" aria-labelledby="folderModalLabel"
            aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                            @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                            <div class="form-group">
                                <label for="email" class="form-label">Select Org Unit<span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="ou_id" aria-label="Default select example"
                                    id="select_org_unit">
                                    <option value="">Select Org Unit</option>
                                    @foreach($organizationUnits as $val)
                                    <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                    @endforeach
                                </select>
                                <div id="ou_id_error" class="text-danger error_e"></div>
                            </div>
                            @endif
                            <div class="form-group">
                                <label for="parent_id" class="form-label">Parent Folder<span
                                        class="text-danger">*</span></label>
                                <select class="form-select all-folders" name="parent_id">
                                    <option value="">No Parent (Root Folder)</option>
                                    @foreach($folders as $folder)
                                    @include('folders.partials.folder_option', ['folder' => $folder, 'level' => 0])
                                    @endforeach
                                </select>
                                <div id="parent_id_error" class="text-danger error_e"></div>
                            </div>
                            <div class="form-group">
                                <label for="firstname" class="form-label">Folder Name<span
                                        class="text-danger">*</span></label>
                                <input type="text" name="folder_name" class="form-control">
                                <div id="folder_name_error" class="text-danger error_e"></div>
                            </div>
                            <div class="form-group">
                                <label for="lastname" class="form-label">Description<span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                                <div id="description_error" class="text-danger error_e"></div>
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
                                <button type="button" id="submitFolder" class="btn btn-primary sbt_btn">Save </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--End of Courses-->

        <!-- Edit Courses -->
        <div class="modal fade" id="editFolderModal" tabindex="-1" role="dialog" aria-labelledby="editFolderModalLabel"
            aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFolderModalLabel">Edit Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editFolder" class="row g-3 needs-validation">
                            @csrf
                            @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                            <div class="form-group">
                                <label for="email" class="form-label">Select Org Unit<span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="ou_id" id="edit_ou_id"
                                    aria-label="Default select example">
                                    <option value="">Select Org Unit</option>
                                    @foreach($organizationUnits as $val)
                                    <option value="{{ $val->id }}">{{ $val->org_unit_name }}</option>
                                    @endforeach
                                </select>
                                <div id="ou_id_error" class="text-danger error_e"></div>
                            </div>
                            @endif
                            <!-- Parent Folder Selection -->
                            <div class="form-group">
                                <label for="parent_id" class="form-label">Parent Folder<span
                                        class="text-danger">*</span></label>
                                <select class="form-select all-folders" name="parent_id" id="edit_parent_folder">
                                    <option value="">No Parent (Root Folder)</option>
                                    @foreach($folders as $folder)
                                    @include('folders.partials.folder_option', [
                                    'folder' => $folder,
                                    'level' => 0
                                    ])
                                    @endforeach
                                </select>
                                <div id="parent_id_error_up" class="text-danger error_e"></div>
                            </div>
                            <div class="form-group">
                                <label for="firstname" class="form-label">Folder Name<span
                                        class="text-danger">*</span></label>
                                <input type="text" name="folder_name" id="edit_folder_name" class="form-control">
                                <input type="hidden" name="folder_id" id="folder_id" class="form-control">
                                <div id="folder_name_error_up" class="text-danger error_e"></div>
                            </div>
                            <div class="form-group">
                                <label for="lastname" class="form-label">Description<span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" id="edit_description"
                                    rows="3"></textarea>
                                <div id="description_error_up" class="text-danger error_e"></div>
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
            <div class="modal fade" id="deleteFolder" tabindex="-1" aria-labelledby="exampleModalLabel"
                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                            <input type="hidden" name="folder_id" id="folderId" value="">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this Folder "<strong><span id="append_name">
                                </span></strong>" ?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary close_btn"
                                data-bs-dismiss="modal">Close</button>
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
        function openFolder(folderId) {

        }

        $("#createFolder").on('click', function() {
            $(".error_e").html('');
            $("#folders")[0].reset();
            $("#createFolderModal").modal('show');
        })

        $("#submitFolder").on("click", function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ url("/folder/create") }}',
                type: 'POST',
                data: $("#folders").serialize(),
                success: function(response) {
                    $('#createFolderModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        alert(xhr.responseJSON.error);
                    }
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    $.each(validationErrors, function(key, value) {
                        var msg = '<p>' + value + '<p>';
                        $('#' + key + '_error').html(msg);
                    })
                }
            });

        })

        function editFolder(folderId) {
            $('.error_e').html('');
            var $folderSelect = $(".all-folders");
            $.ajax({
                url: "{{ url('/folder/edit') }}",
                type: 'GET',
                data: {
                    id: folderId
                },
                success: function(response) {
                    let currentFolderId = response.current_folder_id;
                    let selectedParentId = response.selected_parent_id;
                    // Set parent folder dropdown
                    $('#edit_parent_folder').val(selectedParentId).trigger('change');

                    // Set other form values
                    $('#edit_folder_name').val(response.folder.folder_name);
                    $('#folder_id').val(response.folder.id);
                    $('#edit_description').val(response.folder.description);
                    $('#edit_ou_id').val(response.folder.ou_id);
                    $('#edit_status').val(response.folder.status);
                    if (response.org_folders) {
                        var options = "<option value=''>No Parent (Root Folder)</option>";

                        // Recursive function to generate folder options with indentation
                        function generateFolderOptions(folder, level) {
                            var indent = "&nbsp;&nbsp;&nbsp;&nbsp;".repeat(level); // Indentation
                            var option =
                                `<option value="${folder.id}">${indent}${folder.folder_name}</option>`;

                            if (folder.children_recursive && folder.children_recursive.length > 0) {
                                folder.children_recursive.forEach(child => {
                                    option += generateFolderOptions(child, level + 1);
                                });
                            }
                            return option;
                        }

                        // Process only the top-level folders
                        response.org_folders.forEach(function(folder) {
                            options += generateFolderOptions(folder, 0);
                        });

                        $folderSelect.html(options);
                        $folderSelect.trigger("change");
                    }

                    $('#editFolderModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        $('#updateFolder').on('click', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ url('folder/update') }}",
                type: "POST",
                data: $("#editFolder").serialize(),
                success: function(response) {

                    if (response.error) {
                        $('#editFolderModal').modal('show');
                        $('#parent_id_error_up').html(response.error);
                    } else {
                        $('#editFolderModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = JSON.parse(xhr.responseText);
                    var validationErrors = errorMessage.errors;
                    console.log(validationErrors);
                    $.each(validationErrors, function(key, value) {
                        var msg = '<p>' + value + '<p>';
                        $('#' + key + '_error_up').html(msg);

                    })
                }
            })
        })

        function deleteFolder(folderId, folderName) {
            $('#deleteFolder').modal('show');
            $('#append_name').html(folderName);
            $('#folderId').val(folderId);
        }

        $(document).on("change", "#select_org_unit", function() {
            var ou_id = $(this).val();
            var $folderSelect = $(".all-folders");

            $.ajax({
                url: "/folder/get_ou_folder/",
                type: "GET",
                data: {
                    'ou_id': ou_id
                },
                dataType: "json",
                success: function(response) {
                    if (response.org_folder && Array.isArray(response.org_folder)) {
                        var options = "<option value=''>No Parent (Root Folder)</option>";

                        // Recursive function to generate folder options with indentation
                        function generateFolderOptions(folder, level) {
                            var indent = "&nbsp;&nbsp;&nbsp;&nbsp;".repeat(level); // Indentation
                            var option =
                                `<option value="${folder.id}">${indent}${folder.folder_name}</option>`;

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
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });

        $(document).on("change", "#edit_ou_id", function() {
            var ou_id = ($(this).val());

            var $folderSelect = $(".all-folders");

            $.ajax({
                url: "/folder/get_ou_folder/",
                type: "GET",
                data: {
                    'ou_id': ou_id
                },
                dataType: "json",
                success: function(response) {


                    if (response.org_folder && Array.isArray(response.org_folder)) {
                        var options = "<option value=''>No Parent (Root Folder)</option>";

                        // Recursive function to generate folder options with indentation
                        function generateFolderOptions(folder, level) {
                            var indent = "&nbsp;&nbsp;&nbsp;&nbsp;".repeat(level); // Indentation
                            var option =
                                `<option value="${folder.id}">${indent}${folder.folder_name}</option>`;

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
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
        setTimeout(function() {
            $('#alertMessage').fadeOut('slow');
        }, 2000);
        </script>
        @endsection