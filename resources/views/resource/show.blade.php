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
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Resource Details</h5>
        </div>
        <div class="card-body p-4">
            <div class="container-fluid">

                {{-- Resource Overview --}}
                <div class="row mb-4">
                    <div class="col-md-4 text-center">
                        @if($resourceData->resource_logo)
                            <img src="{{ asset('storage/resource_logo/' . $resourceData->resource_logo) }}" alt="Resource Logo" class="img-fluid rounded border p-2" style="max-height: 150px;">
                        @else
                            <img src="{{ asset('/assets/img/No_Image_Available.jpg') }}" alt="No Logo" class="img-fluid rounded border p-2" style="max-height: 150px;">
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $resourceData->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Registration</th>
                                    <td>{{ $resourceData->registration ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $resourceData->type ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Class</th>
                                    <td>{{ $resourceData->class ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Classroom</th>
                                    <td>{{ $resourceData->classroom ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Other</th>
                                    <td>{{ $resourceData->other ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Note</th>
                                    <td>{{ $resourceData->note ?? 'N/A' }}</td> 
                                </tr>
                                <tr>
                                    <th>Hours From RTS</th>
                                    <td>{{ optional($resourceData->hours_from_rts)->format('H:i:s') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Date From RTS</th>
                                    <td>{{ optional($resourceData->date_from_rts)->format('d M Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Date for Maintenance</th>
                                    <td>{{ optional($resourceData->date_for_maintenance)->format('d M Y') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Hours Remaining</th>
                                    <td>{{ optional($resourceData->hours_remaining)->format('H:i:s') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Enable Document Upload</th>
                                    <td>{{ $resourceData->enable_doc_upload ? 'Yes' : 'No' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="mt-4">
                    <h5 class="text-primary">Attached Documents</h5>
                    @if($resourceData->documents->count() && $resourceData->enable_doc_upload)
                        <ul class="list-group">
                            @foreach($resourceData->documents as $doc)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $doc->name ?? 'Untitled Document' }}
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-info mt-2 mb-0">
                            No documents are attached to this resource.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="{{ route('resource.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

@endsection

@section('js_scripts')
<script>

</script>

@endsection