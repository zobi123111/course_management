@section('title', 'Dashboard')
@section('sub-title', 'Welcome to Dashboard')
@extends('layout.app')
@section('content')

<section class="section dashboard">
  <div class="row">

    <!-- Left side columns -->
    <div class="col-lg-8">
      <div class="row">

        <!-- Users Card -->
        <div class="col-xxl-4 col-md-6">
            @if(auth()->user()->role == 1 )
                <a href="{{ route('user.index') }}" class="text-decoration-none">
            @endif
                <div class="card info-card sales-card">
                    <div class="card-body">
                        <h5 class="card-title">Users</h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-user-5-fill dashboard_icon"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $user_count }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            @if(auth()->user()->role == 1 )
                </a>
            @endif
        </div>
        <!-- End Users Card -->

        <!-- Group Card -->
        <div class="col-xxl-4 col-md-6">
            @if(auth()->user()->role == 1 )
                <a href="{{ route('group.index') }}" class="text-decoration-none">
            @endif
                <div class="card info-card revenue-card">
                    <div class="card-body">
                        <h5 class="card-title">Groups</h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-group-fill dashboard_icon"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $group_count }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            @if(auth()->user()->role == 1 )
                </a>
            @endif
        </div>
        <!-- End Group Card -->

        <!-- Folder Card -->
        <div class="col-xxl-4 col-xl-12">
            @if(auth()->user()->role == 1 )
                <a href="{{ route('folder.index') }}" class="text-decoration-none">
            @endif
                <div class="card info-card customers-card">
                    <div class="card-body">
                        <h5 class="card-title">Folders</h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-briefcase-2-fill dashboard_icon"></i>
                            </div>
                            <div class="ps-3">
                                <h6>{{ $folder_count }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            @if(auth()->user()->role == 1 )
                </a>
            @endif
        </div>
        <!-- End Folder Card -->

      </div>
    </div>
    <!-- End Left side columns -->
  </div>
</section>

@endsection

@section('js_scripts')


@endsection