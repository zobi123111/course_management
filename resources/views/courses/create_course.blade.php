@section('title', 'Create Course')
@section('sub-title', 'Create Course')
@extends('layout.app')
@section('content')

<form action="{{ url('store/course') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row mb-3 mt-4">
            <label for="title" class="col-sm-3 col-form-label required">Name<span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="course_name" >
                @if ($errors->has('course_name'))
                <span class="text-danger">{{ $errors->first('course_name') }}</span>
                @endif 
            </div>
        </div>
        <div class="row mb-3 mt-4">
            <label for="title" class="col-sm-3 col-form-label required">Description<span class="text-danger">*</span></label>
            <div class="col-sm-9">
            <textarea id="myeditorinstance" name="course_description"></textarea>
              @if ($errors->has('course_description'))
                <span class="text-danger">{{ $errors->first('course_description') }}</span>
                @endif 
            </div>
        </div>
    </div>


    <div class="modal-footer back-btn">
        <button type="submit" class="btn btn-primary btn-default">Save</button>
    </div>
</form>
@endsection

@section('js_scripts')

@endsection