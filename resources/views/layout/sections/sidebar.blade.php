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

  </aside><!-- End Sidebar-->