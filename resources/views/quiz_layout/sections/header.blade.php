  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">
      <div class="d-flex align-items-center justify-content-between">
      <?php 
        $org_detail = ou_logo(); 
      
        if ($org_detail && $org_detail->org_logo) {   
        ?>
            <a href="{{ url('dashboard') }}" class="logo d-flex align-items-center">
                <img id="org_logo_preview" class="avms_logo" src="{{ asset('storage/organization_logo/' . $org_detail->org_logo) }}" alt="Organization Logo">
                <span class="d-none d-lg-block">{{ $org_detail->org_unit_name ?? '' }} </span>  
            </a>
        <?php 
        } else{ ?>
          <a href="https://altcruise.co.uk/dashboard" class="logo d-flex align-items-center logo-bottom">

            @php
                $setting = settingData();
            @endphp

            @if(isset($setting->site_image))
                <img src="{{ asset('storage/' . $setting->site_image) }}" alt="" class="avms_logo">
            @else
            <img src="{{env('SITE_LOGO')}}" alt="" class="avms_logo" >

            @endif
          </a>
      <?php  } ?>


          <i class="bi bi-list toggle-sidebar-btn"></i>
      </div><!-- End Logo -->
      <nav class="header-nav ms-auto">
          <ul class="d-flex align-items-center">

                <li class="nav-item">
                    <select class="form-select" aria-label="Default select example" id="switch_role">
                        <!-- <option disabled>Change Role To</option> -->
                         @foreach(getMultipleRoles() as $val)
                             @if(session('current_role', auth()->user()->role) == $val->id)
                                <option value="{{ $val->id }}" selected>
                                    {{ $val->role_name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </li>
              <li class="nav-item dropdown pe-3">

                  <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                      {{-- <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle"> --}}
                        @if(Auth::user()->image)
                            <img src="{{ asset('storage/' .  Auth()->user()->image) }}" alt="Profile" class="rounded-circle">
                        @else
                            <img src="{{ asset('/assets/img/default_profile.png') }}" alt="Profile" class="rounded-circle">
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
                         
                          <span> {{ Auth::user()?->roles?->role_name }}</span>

                          <br>
                          @if(auth()->user()->is_owner==0)
                          <span>{{ optional(auth()->user()->organization)->org_unit_name }}</span>

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

                        @if(Auth::user()->is_owner == 1)
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('settings.index') }}">
                                <i class="bi bi-gear"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                        @endif
                    
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







