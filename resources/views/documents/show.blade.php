
@section('title', 'Document Details')
@section('sub-title', 'Document Details')
@extends('layout.app')
@section('content')

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-file-alt me-2"></i>Document Details</h5>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-6">
                <strong>📄 Title:</strong> {{ $document->doc_title }}
            </div>

            <div class="col-md-6">
                <strong>📁 Folder:</strong> {{ $document?->folder?->folder_name ?? 'N/A' }}
            </div>

            <div class="col-md-6">
                <strong>👥 Group:</strong> {{ $document?->group?->name ?? 'N/A' }}
            </div>

            <div class="col-md-6">
                <strong>🆔 Version No:</strong> {{ $document->version_no }}
            </div>

            <div class="col-md-6">
                <strong>📅 Issue Date:</strong> {{ $document->issue_date ? date('d/m/Y', strtotime($document->issue_date)): 'N/A' }}
            </div>

            <div class="col-md-6">
                <strong>⏳ Expiry Date:</strong> {{ $document->expiry_date ? date('d/m/Y', strtotime($document->expiry_date)): 'N/A' }}
            </div>

            <div class="col-md-6">
                <strong>📂 Type:</strong> {{ $document->document_type ?? 'N/A' }}
            </div>

            <div class="col-md-6">
                <strong>🔐 Acknowledged:</strong>
                <span class="badge {{ in_array(auth()->id(), json_decode($document->acknowledge_by ?? '[]')) ? 'bg-success' : 'bg-danger' }}">
                    {{ in_array(auth()->id(), json_decode($document->acknowledge_by ?? '[]')) ? 'Yes' : 'No' }}
                </span>
            </div>
        </div>

        <hr class="my-4">

        {{-- File Preview --}}
        <div class="text-center">
            @php
                $file = asset('storage/' . $document->document_file);
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                $pdfTypes = ['pdf'];
            @endphp

            @if(in_array($extension, $imageTypes))
                <img src="{{ $file }}" alt="Document Image" class="img-fluid rounded shadow">
            @elseif(in_array($extension, $pdfTypes))
                <iframe src="{{ $file }}" width="100%" height="600px" class="border rounded shadow"></iframe>
            @else
                <p class="text-muted">No preview available.</p>
                <a href="{{ $file }}" class="btn btn-outline-secondary" download>
                    <i class="fa fa-download"></i> Download File
                </a>
            @endif
        </div>

        {{-- Acknowledgement Checkbox --}}
        @if(empty(auth()->user()->is_admin) && empty(auth()->user()->is_owner))
            <form method="POST" id="docAcknowledgeForm" class="mt-4">
                @csrf
                <input type="hidden" name="document_id" value="{{ $document->id }}">
                @php
                    $acknowledgedByUsers = json_decode($document->acknowledge_by ?? '[]', true);
                @endphp
                <div class="mt-3 text-center">
                    <input type="checkbox" class="form-check-input" id="acknowledged" name="acknowledged" value="1"
                        {{ in_array(auth()->user()->id, $acknowledgedByUsers) ? 'checked' : '' }}>
                    <label class="form-check-label" for="acknowledged">
                        I have read and acknowledged this document
                    </label>
                </div>
            </form>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('document.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle-fill"></i> Back to Documents
            </a>
        </div>
    </div>
</div>


@endsection

@section('js_scripts')

<!-- <script>
    $(document).ready(function() {

        $('#acknowledged').on('change', function() {
            if ($(this).is(':checked')) {
                $.ajax({
                    url: "{{ route('document.acknowledge') }}", // Update with your route
                    type: "POST",
                    data: $('#docAcknowledgeForm').serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(response.success);
                            // $('#acknowledged').prop('disabled', true); // Disable checkbox after acknowledgment
                        } else {
                            alert(response.error);
                        }
                    },
                    error: function(xhr) {
                        alert("An error occurred while updating acknowledgment.");
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
</script> -->


<script>
    $(document).ready(function() {
        $('#acknowledged').on('change', function() {
            if ($(this).is(':checked')) {
                $.ajax({
                    url: "{{ route('document.acknowledge') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        document_id: $("input[name='document_id']").val(),
                        acknowledged: 1
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert(response.success);
                        } else {
                            alert(response.error);
                        }
                    },
                    error: function(xhr) {
                        alert("An error occurred while updating acknowledgment.");
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
</script>

@endsection