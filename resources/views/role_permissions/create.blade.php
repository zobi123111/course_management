@section('title', 'Roles')
@section('sub-title', 'Roles')
@extends('layout.app')
@section('content')
<div class="main_cont_outer">
    <div class="create_btn">
        <a href="{{ route('roles.index') }}" class="btn btn-primary create-button btn_primary_color" id="backBtn"><i
                class="bi bi-arrow-left-circle-fill"></i> back</a>
    </div>
    <div id="successMessagea" class="alert alert-success" style="display: none;" role="alert">
        <i class="bi bi-check-circle me-1"></i>
    </div>
    @if(session()->has('message'))
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session()->get('message') }}
    </div>
    @endif
    <div class="card card-container">
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <div class="form-group">
                        <label for="role_name" class="form-label">Role Name<span class="text-danger">*</span></label>
                        <input type="text" name="role_name" class="form-control">
                        <div id="role_name_error" class="text-danger error_e"></div>
                    </div>
                    @error('role_name')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Pages and their Modules:</label>
                    <div id="pages-modules-container">
                        @foreach ($pages as $page)
                        <div class="row mb-3">
                            <legend class="col-form-label col-sm-2 pt-0">{{ $page->name }}</legend>
                            <div class="col-sm-10">
                                <div class="module_cont">
                                    @foreach ($page->modules as $module)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="module_ids[{{ $page->id }}][]" value="{{ $module->id }}"
                                            id="module-{{ $module->id }}"
                                            {{ in_array($module->id, old('module_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="module-{{ $module->id }}">
                                            {{ $module->name }}
                                        </label><br>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('module_ids')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary create-button btn_primary_color">Submit</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js_scripts')

<script>
$(document).ready(function() {

});
</script>

@endsection