@extends('layout.app')
@section('title', 'Licence Validation Type')
@section('sub-title', 'Licence Validation Type') 

@section('content')

@if(session()->has('message'))
    <div class="alert alert-success fade show">
        {{ session()->get('message') }}
    </div>
@endif

<div class="main_cont_outer">
     
    <div class="create_btn">
        <a href="{{ url('org_setting/'.request('ou_id')) }}" class="btn btn-primary" > <i class="bi bi-arrow-left"></i> Back</a>

        <button class="btn btn-primary" id="create-validation">
            Create Validation Type
        </button>
    </div>
   
    <br>

    <div class="card pt-4">
        <div class="card-body">
            <table class="table table-hover" id="validationTable">
                <thead>
                    <tr>
                        <th style="display:none;">ID</th>
                        <th>Code</th>
                        <th>Country Name</th>
                        <th>Aircraft Prefix</th>
                        <th>Organization Unit</th>
                        <th>Enabled</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($validation_type as $val)
                    <tr>
                        <td style="display:none;">{{ $val->id }}</td>
                        <td>{{ $val->code }}</td>
                        <td>{{ $val->country_name }}</td>
                        <td>{{ $val->aircraft_prefix }}</td>
                        <td>{{ optional($val->OrganizationUnit)->org_unit_name }}</td>
                        <td>
                            @if($val->enabled)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </td>
                        <td>
                            <i class="fa fa-edit edit-icon"
                               style="cursor:pointer"
                               data-id="{{ $val->id }}"></i>
                        </td>
                        <td>
                            <i class="fa fa-trash delete-icon"
                               style="cursor:pointer"
                               data-id="{{ $val->id }}"></i>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- CREATE MODAL --}}
<div class="modal fade" id="createModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Create Licence Validation Type</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="createForm">
                    @csrf

                    @if(auth()->user()->is_owner == 1)
                    <div class="mb-3">
                        <label>Organization Unit</label>
                        <select name="ou_id" class="form-select">
                            <option value="">Select OU</option>
                            @foreach ($organizationUnits as $ou)
                                <option value="{{ $ou->id }}">
                                    {{ $ou->org_unit_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label>Code *</label>
                        <input type="text" name="code" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Country Name *</label>
                        <input type="text" name="country_name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Aircraft Prefix *</label>
                        <input type="text" name="aircraft_prefix" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Enabled</label>
                        <select name="enabled" class="form-select">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <button type="button" id="saveBtn" class="btn btn-primary">
                        Save
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Edit Licence Validation Type</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="editForm">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">

                    @if(auth()->user()->is_owner == 1)
                        <div class="mb-3">
                            <label>Organization Unit</label>
                            <select name="ou_id" class="form-select">
                                <option value="">Select OU</option>
                                @foreach ($organizationUnits as $ou)
                                    <option value="{{ $ou->id }}">
                                        {{ $ou->org_unit_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label>Code *</label>
                        <input type="text" name="code" id="edit_code" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Country Name *</label>
                        <input type="text" name="country_name" id="edit_country_name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Aircraft Prefix *</label>
                        <input type="text" name="aircraft_prefix" id="edit_aircraft_prefix" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Enabled</label>
                        <select name="enabled" id="edit_enabled" class="form-select">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <button type="button" id="updateBtn" class="btn btn-primary">
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection


@section('js_scripts')

<script>

$(document).ready(function () {

    setTimeout(function () {
        let alerts = document.querySelectorAll('.alert');

        alerts.forEach(alert => {
            alert.classList.remove('show');
            alert.classList.add('fade');
            setTimeout(() => alert.remove(), 1000);
        });
    }, 1000);


    $('#validationTable').DataTable({
        order: [[0, 'desc']],
        columnDefs: [
            { targets: 0, visible: false }
        ]
    });

    $("#create-validation").click(function () {
        $("#createForm")[0].reset();
        $("#createModal").modal('show');
    });

    $("#saveBtn").click(function () {

        $.ajax({
            url: "{{ url('/validation-codes-add') }}",
            type: "POST",
            data: $("#createForm").serialize(),
            success: function () {
                location.reload();
            }
        });
    });

    $(document).on("click", ".edit-icon", function () {
        var id = $(this).data("id");

        $.post("{{ url('/validation-codes-edit') }}",
        {
            id: id,
            _token: "{{ csrf_token() }}"
        },
        function (response) {
            var data = response.validation_type;

            $("#edit_id").val(data.id);
            $("#edit_code").val(data.code);
            $("#edit_country_name").val(data.country_name);
            $("#edit_aircraft_prefix").val(data.aircraft_prefix);
            $("#edit_enabled").val(data.enabled ? 1 : 0);

            // Select the correct OU
            $("#editForm select[name='ou_id']").val(data.ou_id);

            $("#editModal").modal("show");
        });
    });



    $("#updateBtn").click(function () {

        $.ajax({
            url: "{{ url('/validation-codes-update') }}",
            type: "POST",
            data: $("#editForm").serialize(),
            success: function () {
                location.reload();
            }
        });
    });

    $(document).on("click", ".delete-icon", function () {

        if(!confirm("Are you sure?")) return;

        var id = $(this).data("id");

        $.post("{{ url('/validation-codes-delete') }}",
        {
            id: id,
            _token: "{{ csrf_token() }}"
        },
        function () {
            location.reload();
        });
    });

});

</script>

@endsection
