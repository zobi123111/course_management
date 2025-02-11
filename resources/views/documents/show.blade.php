
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
    
    <!-- PDF Viewer -->
    <iframe src="{{ asset('storage/'.$document->document_file) }}" width="100%" height="600px"></iframe>

    <!-- Acknowledgment Form -->
    <form id="docAcknowledgeForm">
        @csrf
        <input type="hidden" name="document_id" value="{{ $document->id }}">
        
        <div class="mt-3 text-center">
            <input type="checkbox" id="acknowledged" name="acknowledged" value="1">
            <label for="acknowledged">I have read and acknowledged this document</label>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary mt-2 ">Acknowledge</button>
        </div>
    </form>
</div>

@endsection

@section('js_scripts')

<script>
$(document).ready(function() {

});
</script>

@endsection