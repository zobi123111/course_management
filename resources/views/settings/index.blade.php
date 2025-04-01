@extends('layout.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Settings</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="site_image">Site Logo:</label>
                                    <input type="file" name="site_image" id="site_image" class="form-control">
                                    @if(!empty($settings->site_image))
                                        <img src="{{ asset('storage/' . $settings->site_image) }}" width="100" class="mt-2">
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="login_image">Login Page Logo:</label>
                                    <input type="file" name="login_image" id="login_image" class="form-control">
                                    @if(!empty($settings->login_image))
                                        <img src="{{ asset('storage/' . $settings->login_image) }}" width="100" class="mt-2">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="text">Text:</label>
                                    <input name="text" id="text" class="form-control" value="{{ $settings->text ?? '' }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="font_type">Font Type:</label>
                                    <select name="font_type" id="font_type" class="form-control">
                                        <option value="Arial" {{ ($settings->font_type ?? '') == 'Arial' ? 'selected' : '' }}>Arial</option>
                                        <option value="Times New Roman" {{ ($settings->font_type ?? '') == 'Times New Roman' ? 'selected' : '' }}>Times New Roman</option>
                                        <option value="Verdana" {{ ($settings->font_type ?? '') == 'Verdana' ? 'selected' : '' }}>Verdana</option>
                                        <option value="Tahoma" {{ ($settings->font_type ?? '') == 'Tahoma' ? 'selected' : '' }}>Tahoma</option>
                                        <option value="Courier New" {{ ($settings->font_type ?? '') == 'Courier New' ? 'selected' : '' }}>Courier New</option>
                                        <option value="Georgia" {{ ($settings->font_type ?? '') == 'Georgia' ? 'selected' : '' }}>Georgia</option>
                                        <option value="Trebuchet MS" {{ ($settings->font_type ?? '') == 'Trebuchet MS' ? 'selected' : '' }}>Trebuchet MS</option>
                                        <option value="Comic Sans MS" {{ ($settings->font_type ?? '') == 'Comic Sans MS' ? 'selected' : '' }}>Comic Sans MS</option>
                                        <option value="Impact" {{ ($settings->font_type ?? '') == 'Impact' ? 'selected' : '' }}>Impact</option>
                                        <option value="Garamond" {{ ($settings->font_type ?? '') == 'Garamond' ? 'selected' : '' }}>Garamond</option>
                                        <option value="Palatino Linotype" {{ ($settings->font_type ?? '') == 'Palatino Linotype' ? 'selected' : '' }}>Palatino Linotype</option>
                                        <option value="Lucida Console" {{ ($settings->font_type ?? '') == 'Lucida Console' ? 'selected' : '' }}>Lucida Console</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="font_size">Font Size:</label>
                                    <input type="number" name="font_size" id="font_size" class="form-control" value="{{ $settings->font_size ?? 14 }}">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection