<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        @php
            $pages = getAllowedPages();

            // Parents
            $parents = $pages->whereNull('parent_page_id');
        @endphp

        @foreach($parents as $parent)

            @php
                $children = $pages->where('parent_page_id', $parent->id);
                $collapseId = 'menu-' . $parent->id;

                $isActive = Request::is($parent->route_name) || 
                            $children->contains(function($child){
                                return Request::is($child->route_name);
                            });
            @endphp

            <li class="nav-item" style="list-style: none;">

                @if($children->count() > 0)

                    <!-- Parent Menu -->
                    <a class="nav-link {{ $isActive ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse"
                    href="#{{ $collapseId }}">

                        <i class="{{ $parent->icon }}"></i>
                        <span>{{ ucfirst($parent->name) }}</span>
                        <i class="bi bi-chevron-down ms-auto"></i>
                    </a>

                    <!-- Child Menu -->
                    <ul id="{{ $collapseId }}"
                        class="nav-content collapse {{ $isActive ? 'show' : '' }}"
                        data-bs-parent="#sidebar-nav">

                        @foreach($children as $child)
                            <li>
                                <a href="{{ url($child->route_name) }}"
                                class="{{ Request::is($child->route_name) ? 'active' : '' }}">

                                    <i class="{{ $child->icon }}" style="font-size: 15px !important;"></i>
                                    <span>{{ ucfirst($child->name) }}</span>
                                </a>
                            </li>
                        @endforeach

                    </ul>
                @else
                    <!-- Single Menu -->
                    <a class="nav-link {{ Request::is($parent->route_name) ? 'active' : '' }}"
                    href="{{ url($parent->route_name) }}">

                        <i class="{{ $parent->icon }}"></i>
                        <span>{{ ucfirst($parent->name) }}</span>
                    </a>
                @endif
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
                <img src="{{ asset('storage/' . $setting->site_image) }}" alt="" class="site_logo" style="width:108px;height:48px">
            @else
                <img src="{{env('SITE_LOGO')}}" alt="" class="avms_logo">
            @endif
            <!-- <img src="https://altcruise.co.uk/assets/img/logo.png" alt="">
                <span class="d-none d-lg-block">Management</span>   -->
            </a>
        <?php  } ?>
    </div>
  </aside>