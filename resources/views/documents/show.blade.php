
@section('title', 'Document Details')
@section('sub-title', 'Document Details')
@extends('layout.app')
@section('content')

<div class="container">    
    <div class="doc_details d-flex justify-content-around m-3">
        <span><strong>Document Title:</strong> {{ $document->doc_title }}</span>
        <span><strong>Version No:</strong> {{ $document->version_no }}</span>
        <span><strong>Issue Date:</strong> {{ $document->issue_date }}</span>
        <span><strong>Expiry Date:</strong> {{ $document->expiry_date }}</span>
    </div>
    
@php
    $file = asset('storage/' . $document->document_file); // Get file path
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION)); // Extract extension

    // Supported file types
    $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
    $pdfTypes = ['pdf'];
@endphp

<div class="file-preview">
    <!-- Show Images -->
    @if(in_array($extension, $imageTypes))
        <img src="{{ $file }}" alt="Uploaded Image" style="max-width: 100%; height: auto;">

    <!-- Show PDFs -->
    @elseif(in_array($extension, $pdfTypes))
        <iframe src="{{ $file }}" width="100%" height="600px"></iframe>

    <!-- Show Download Link for Other Files -->
    @else
        <p>No preview available. <a href="{{ $file }}" download>Download file</a>.</p>
    @endif
</div>

    <!-- Acknowledgment Form -->
    <form method="POST" id="docAcknowledgeForm">
        @csrf
        <input type="hidden" name="document_id" value="{{ $document->id }}">
        
        <div class="mt-3 text-center">
            <input type="checkbox" id="acknowledged" name="acknowledged" value="1" {{ ($document->acknowledged==1)? 'checked': '' }}>
            <label for="acknowledged">I have read and acknowledged this document</label>
            <div class="create_btn">
            <a href="{{ route('document.index') }}" class="btn btn-primary create-button btn_primary_color" id="backBtn"><i class="bi bi-arrow-left-circle-fill"> </i>back</a>
    </div>
        </div>
        <!-- <div class="text-center">
            <button type="submit" class="btn btn-primary mt-2 ">Acknowledge</button>
        </div> -->
    </form>
</div>

@endsection

@section('js_scripts')

<script>
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
</script>

@endsection