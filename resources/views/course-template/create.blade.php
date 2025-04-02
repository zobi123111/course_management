@section('title', 'Course Template')
@section('sub-title', 'Course Template')
@extends('layout.app')
@section('content')


@if(session()->has('message'))
<div id="successMessage" class="alert alert-success fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i>
    {{ session()->get('message') }}
</div>
@endif

<div class="card pt-4">
    <div class="card-body">
        <form action="{{ route('course-template.store') }}" method="POST">
            @csrf

            <!-- Org Unit Select Box for Admin -->
            @if(auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                <div class="mb-3">
                    <label for="ou_id" class="form-label">Select Org Unit<span class="text-danger">*</span></label>
                    <select class="form-select @error('ou_id') is-invalid @enderror" name="ou_id" id="edit_ou_id" aria-label="Default select example">
                        <option value="">Select Org Unit</option>
                        @foreach($organizationUnits as $val)
                            <option value="{{ $val->id }}" {{ old('ou_id') == $val->id ? 'selected' : '' }}>
                                {{ $val->org_unit_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ou_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <!-- Template Name -->
            <div class="mb-3">
                <label class="form-label">Template Name<span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label class="form-label">Description:</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Enable CBTA Grading -->
            <div class="mb-3">
                <input type="checkbox" name="enable_cbta" id="enable_cbta" value="1" {{ old('enable_cbta') ? 'checked' : '' }}>
                <label for="enable_cbta">Enable CBTA Grading</label>
                @error('enable_cbta')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Enable Manual Time Entry -->
            <div class="mb-3">
                <input type="checkbox" name="enable_manual_time_entry" id="enable_manual_time_entry" value="1" {{ old('enable_manual_time_entry') ? 'checked' : '' }}>
                <label for="enable_manual_time_entry">Enable Manual Time Entry</label>
                @error('enable_manual_time_entry')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <h3>Template Fields</h3>
            <div id="fields">
                @if(old('fields'))
                    @foreach(old('fields') as $index => $field)
                        <div class="field-group row align-items-center mb-3">
                            <div class="col-md-5">
                                @if($index == 0) <!-- Only show label for the first field -->
                                    <label class="form-label">Field Name<span class="text-danger">*</span></label>
                                @endif
                                <input type="text" name="fields[{{ $index }}][name]" class="form-control @error('fields.'.$index.'.name') is-invalid @enderror" 
                                    value="{{ old('fields.'.$index.'.name') }}">
                                @error('fields.'.$index.'.name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                @if($index == 0) <!-- Only show label for the first field -->
                                    <label class="form-label">Grading Type<span class="text-danger">*</span></label>
                                @endif
                                <select name="fields[{{ $index }}][grading_type]" class="form-select @error('fields.'.$index.'.grading_type') is-invalid @enderror">
                                    <option value="pass_fail" {{ old("fields.$index.grading_type") == 'pass_fail' ? 'selected' : '' }}>Pass/Fail</option>
                                    <option value="deferred" {{ old("fields.$index.grading_type") == 'deferred' ? 'selected' : '' }}>Deferred</option>
                                    <option value="numeric" {{ old("fields.$index.grading_type") == 'numeric' ? 'selected' : '' }}>Numeric</option>
                                </select>
                                @error('fields.'.$index.'.grading_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 d-flex align-items-center pt-4">
                                <button type="button" class="btn btn-danger btn-sm remove-field" onclick="removeField(this)">Remove</button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Default Empty Field (First Field) -->
                    <div class="field-group row align-items-center mb-3">
                        <div class="col-md-5">
                            <label class="form-label">Field Name<span class="text-danger">*</span></label>
                            <input type="text" name="fields[0][name]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Grading Type<span class="text-danger">*</span></label>
                            <select name="fields[0][grading_type]" class="form-select">
                                <option value="pass_fail">Pass/Fail</option>
                                <option value="deferred">Deferred</option>
                                <option value="numeric">Numeric</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-center pt-4">
                            <button type="button" class="btn btn-danger btn-sm remove-field" onclick="removeField(this)">Remove</button>
                        </div>
                    </div>
                @endif
            </div>

            <button type="button" class="btn btn-success btn-sm" onclick="addField()">+ Add Field</button>

            <!-- Submit Button -->
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Template</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js_scripts')

<script>
function addField() {
    let index = document.querySelectorAll('.field-group').length; // Get total fields count
    let fieldHTML = `
        <div class="field-group row align-items-center mb-3">
            <div class="col-md-5">
                <label class="form-label">Field Name:</label>
                <input type="text" name="fields[${index}][name]" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Grading Type:</label>
                <select name="fields[${index}][grading_type]" class="form-select">
                    <option value="pass_fail">Pass/Fail</option>
                    <option value="deferred">Deferred</option>
                    <option value="numeric">Numeric</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-center pt-4">
                <button type="button" class="btn btn-danger btn-sm remove-field" onclick="removeField(this)">Remove</button>
            </div>
        </div>`;
    document.getElementById('fields').insertAdjacentHTML('beforeend', fieldHTML);
}

function removeField(button) {
    button.closest('.field-group').remove();
}

</script>

@endsection