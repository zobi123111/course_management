@section('title', 'Users')
@section('sub-title', 'Users')
@extends('layout.app')
@section('content')
<div class="create_btn">
    <a href="{{ url('create/course') }}" class="btn btn-primary" id="create_course" >Create Course</a>
</div>
@if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
@endif
<div class="container mt-5">
  <div class="row">
    <div class="col-sm-4">
      <h3>Column 1</h3>
      <div class="card">
    <div class="card-header">Header</div>
    <div class="card-body">Content</div> 
    <div class="card-footer">Footer</div>
  </div>
    </div>
    <div class="col-sm-4">
      <h3>Column 2</h3>
      <div class="card">
    <div class="card-header">Header</div>
    <div class="card-body">Content</div> 
    <div class="card-footer">Footer</div>
  </div>
    </div>
    <div class="col-sm-4">
      <h3>Column 3</h3>        
      <div class="card">
    <div class="card-header">Header</div>
    <div class="card-body">Content</div> 
    <div class="card-footer">Footer</div>
  </div>
    </div>
  </div>
</div>
@endsection

@section('js_scripts')

<script>
$(document).ready(function() {
    $('#user_table').DataTable();

});
</script>

@endsection
