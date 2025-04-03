@extends('layout.app')

@section('title', 'Users')
@section('sub-title', 'Users Document Reqiured Table')

@section('content')
    <div class="mb-3 d-flex justify-content-between align-items-center gap-3">
        <a href="{{ route('user.index') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left-circle"></i> Back
        </a>

        <div class="d-flex gap-3">
            <span class="d-flex align-items-center">
                <i class="bi bi-check text-success fs-4 me-1"></i> 
                <span class="fw-bold text-success">Required Fields</span>
            </span>

            <span class="d-flex align-items-center">
                <i class="bi bi-x text-danger fs-4 me-1"></i> 
                <span class="fw-bold text-danger">Not Required Fields</span>
            </span>
        </div>
    </div>

    <div class="card pt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="documentTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Licence</th>
                            <th>Passport</th>
                            <th>Medical</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js_scripts')
<script>

    $(document).ready(function() {
        $('#documentTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            // ajax: "{{ route('users.document.data') }}",
            ajax: {
                url: "{{ route('users.document.data') }}",
                type: "GET",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    console.log(xhr.responseText);
                }
            },
            columns: [
                { data: 'fullname', name: 'fullname', orderable: false, searchable: false },
                { data: 'licence_required', name: 'licence_required', orderable: false, searchable: false },
                { data: 'passport_required', name: 'passport_required', orderable: false, searchable: false },
                { data: 'medical', name: 'medical', orderable: false, searchable: false },
                { data: 'rating_required', name: 'rating_required', orderable: false, searchable: false },
            ]
        });
    });


</script>

@endsection
