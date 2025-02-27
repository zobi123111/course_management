  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
      <div class="d-flex align-items-center justify-content-between">
          <a href="{{ url('dashboard') }}" class="logo d-flex align-items-center">
              <!-- <img src="{{ url('assets/img/logo.png') }}" alt=""> -->
              <span class="d-none d-lg-block">{{env('PROJECT_NAME')}}</span>

          </a>
          <i class="bi bi-list toggle-sidebar-btn"></i>
      </div><!-- End Logo -->
      <nav class="header-nav ms-auto">
          <ul class="d-flex align-items-center">

                <li class="nav-item">
                    <select class="form-select" aria-label="Default select example" id="switch_role">
                        <option disabled>Change Role To</option>
                        @foreach(getMultipleRoles() as $val)
                            <option value="{{ $val->id }}" 
                                {{ session('current_role', auth()->user()->role) == $val->id ? 'selected' : '' }}>
                                {{ $val->role_name }}
                            </option>
                        @endforeach
                    </select>
                </li>
              <li class="nav-item dropdown pe-3">

                  <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                      {{-- <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle"> --}}
                        @if(Auth::user()->image)
                            <img src="{{ asset('storage/' .  Auth()->user()->image) }}" alt="Profile" class="rounded-circle">
                        @else
                            <img src="{{ asset('/assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle">
                        @endif
                        <span class="d-none d-md-block dropdown-toggle ps-2"> 
                            @if(Auth::check())
                                {{ Auth::user()->fname }} {{ Auth::user()->lname }}
                            @endif
                        </span>
                  </a><!-- End Profile Iamge Icon -->

                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                      <li class="dropdown-header">
                            <h6>
                                @if(Auth::check())
                                {{ Auth::user()->fname }} {{ Auth::user()->lname }}
                                @endif
                            </h6>
                          <span> {{ Auth::user()->roles->role_name }}</span><br>
                          @if(auth()->user()->is_owner==0)
                          <span> {{ auth()->user()->organization->org_unit_name  }}</span>
                          @endif
                      </li>
                      <li>
                          <hr class="dropdown-divider">
                      </li>

                      <!-- <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li> -->
                      <li>
                          <hr class="dropdown-divider">
                      </li>

                      <!-- <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li> -->
                      <li>
                          <hr class="dropdown-divider">
                      </li>
                      <li>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('user.profile') }}">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                      </li>
                      <li>
                          <a class="dropdown-item d-flex align-items-center" href="{{ url('logout') }}">
                              <i class="bi bi-box-arrow-right"></i>
                              <span>Sign Out</span>
                          </a>
                      </li>

                  </ul><!-- End Profile Dropdown Items -->
              </li><!-- End Profile Nav -->

          </ul>
      </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->







