  <!-- ======= Sidebar ======= -->

  <aside id="sidebar" class="sidebar">

      <ul class="sidebar-nav" id="sidebar-nav">
          @foreach(getAllowedPages() as $page)
            <!-- {{$page->modules}} -->
                <li class="nav-item" style="list-style: none;">
                    <a class="nav-link {{ Request::is($page->route_name) ? 'active' : '' }}" href="{{ url($page->route_name) }}">
                    <i class="{{ $page->icon }}"></i> 
                        <span>{{ ucfirst($page->name) }}</span>
                    </a>
                </li>
          @endforeach
      </ul>
    <div class="course_logo">
    <?php 
        $org_detail = ou_logo(); 
        if ($org_detail && $org_detail->org_logo) { 
        ?>
            <a href="{{ url('dashboard') }}" class="logo d-flex align-items-center">
            <!-- <img src="{{env('PROJECT_LOGO')}}" alt="" class="avms_logo"> -->
            @php
                $setting = settingData();
            @endphp

            @if(isset($setting->site_image))
                <img src="{{ asset('storage/' . $setting->site_image) }}" alt="" class="site_logo">
            @else
                <img src="{{env('SITE_LOGO')}}" alt="" class="avms_logo">
            @endif
            <!-- <img src="https://altcruise.co.uk/assets/img/logo.png" alt="">
                <span class="d-none d-lg-block">Management</span>   -->
            </a>
        <?php  } ?>
        </div>
  </aside><!-- End Sidebar-->
