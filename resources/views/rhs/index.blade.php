@section('title', 'RHS Tag')
@section('sub-title', 'RHS Tag')
@extends('layout.app')
@section('content')

@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif


<div class="create_btn">
    <button class="btn btn-primary create-button" id="createtag" data-toggle="modal"
        data-target="#createTagModal">Create Tag</button>
</div>

<br>

<div class="card pt-4">
        <div class="card-body">
    <table class="table table-hover" id="tagTable">
    <thead>
        <tr>
            <th scope="col">Tag Name</th>
            <th scope="col">Edit</th>
            <th scope="col">Delete</th>
           
        </tr>
    </thead>
<tbody>
@foreach ($tags as $val)
    <tr>
        <td class="tagName">{{ $val->rhstag }}</td>
        <td>
            <i class="fa fa-edit edit-tag-icon" style="font-size:25px; cursor:pointer" tag-id="{{ encode_id($val->id) }}"></i>
        </td>
        <td>
            <i class="fa-solid fa-trash delete-tag-icon" style="font-size:25px; cursor:pointer" tag-id="{{ encode_id($val->id) }}"></i>
        </td>
    </tr>
@endforeach
</tbody>

</table>
</div>
</div>


<!-- Create Groups-->
<div class="modal fade" id="createTagModal" tabindex="-1" role="dialog" aria-labelledby="groupModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupModalLabel">Create RHS Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form  id="tag_form" method="POST" class="row g-3 needs-validation">
                    @csrf
                    <div class="form-group">
                        <label for="name" class="form-label">Tag Name<span class="text-danger">*</span></label>
                        <input type="text" name="tag_name" class="form-control">
                        <div id="tag_name_error" class="text-danger error_e"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="submitTag" class="btn btn-primary sbt_btn">Save</button>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Groups-->

<!-- Edit Group Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1" role="dialog" aria-labelledby="editTagModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTagModalLabel">Edit Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTagForm" class="row g-3 needs-validation">
                    @csrf
                    <input type="hidden" name="tag_id" id="edit_tag_id">
            
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Tag Name<span class="text-danger">*</span></label>
                        <input type="text" name="tag_name" id="edit_tagname" class="form-control">
                        <div id="tag_name_error_up" class="text-danger error_e"></div>
                    </div>
            
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" id="updateTag" class="btn btn-primary sbt_btn">Update</button>
                    </div>
                    <div class="loader" style="display: none;"></div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Group Edit-->

<!-- Delete Group Modal -->
<form action="{{ url('tag/delete') }}" id="deleteTagForm" method="POST">
    @csrf
    <div class="modal fade" id="deleteTag" tabindex="-1" aria-labelledby="deleteTagLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTagLabel">Delete Tag</h5>
                    <input type="hidden" name="tag_id" id="deletetag_id">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this Tag "<strong><span id="append_tag"></span></strong>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="confirmDeleteTag" class="btn btn-danger delete_group">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
    $('#tagTable').DataTable();
    $(document).on('shown.bs.modal', '#tagModal', function () {
    if (!$.fn.DataTable.isDataTable('#tagTable')) {
        $('#tagTable').DataTable();
    }
});
 

    $("#createtag").on('click', function() {
        $(".error_e").html('');
        $("#tag_form")[0].reset();
        $("#createTagModal").modal('show');

        initializeSelect2(); // Ensure Select2 is re-initialized
    })

    $("#submitTag").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: '{{ url("/tag/create") }}',
            type: 'POST',
            data: $("#tag_form").serialize(),
            success: function(response) {
                $('#createTagModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error').html(msg);
                })
            }
        });

    })

    $(document).on('click', '.edit-tag-icon', function () {
        $('.error_e').html('');
         var tagid = $(this).attr("tag-id");
        // $(".loader").fadeIn();
        $.ajax({
            url: "{{ url('/tag/edit') }}",
            type: 'post',
            data: { id: tagid, "_token": "{{ csrf_token() }}" },
            success: function (response) {
                console.log(response.tag.rhstag);
                $('#edit_tagname').val(response.tag.rhstag);
                $('#edit_tag_id').val(tagid);
                 $('#editTagModal').modal('show');
              
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                $(".loader").fadeOut("slow");
            }
        });
    });

    $('#updateTag').on('click', function(e) {
        e.preventDefault(); 

        $.ajax({
            url: "{{ url('/tag/update') }}",
            type: "POST",
            data: $("#editTagForm").serialize(),
            success: function(response) {
                $('#editTagModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var msg = '<p>' + value + '<p>';
                    $('#' + key + '_error_up').html(msg);
                })
            }
        })
    })

    // Delete Group
    $(document).on('click', '.delete-tag-icon', function() {  
        $('#deleteTag').modal('show');
        var tag_id = $(this).attr("tag-id");
        var tagName = $(this).closest('tr').find('.tagName').text();
        $('#append_tag').html(tagName);
        $('#deletetag_id').val(tag_id);
      
    });

  

    setTimeout(function() {
        $('#successMessage').fadeOut('slow');
    }, 2000);

});
</script>

@endsection