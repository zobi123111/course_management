  <!-- ======= Sidebar ======= -->
  <aside id="sidebar" class="sidebar">

      <ul class="sidebar-nav" id="sidebar-nav">
          <li class="nav-item">
              <a class="nav-link " href="{{ url('dashboard') }}">
                  <i class="bi bi-grid"></i>
                  <span>Dashboard</span>
              </a>
          </li>
          <!-- End Dashboard Nav -->

          <!-- <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart"></i><span>Charts</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="charts-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="charts-chartjs.html">
              <i class="bi bi-circle"></i><span>Chart.js</span>
            </a>
          </li>
          <li>
            <a href="charts-apexcharts.html">
              <i class="bi bi-circle"></i><span>ApexCharts</span>
            </a>
          </li>
          <li>
            <a href="charts-echarts.html">
              <i class="bi bi-circle"></i><span>ECharts</span>
            </a>
          </li>
        </ul>
      </li> -->

          @if(auth()->check() && auth()->user()->role == 1 && empty(auth()->user()->ou_id))
          <!-- End User Nav -->
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('users') }}">
                  <i class="bi bi-person"></i>
                  <span>Users</span>
              </a>
          </li>
          <!-- End User  Nav -->

          <!-- Start Courses Nav -->
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('organization') }}">
                  <i class="bi bi-person"></i>
                  <span>Organization Unit</span>
              </a>
          </li>
          <!-- End Courses Nav -->
          @endif

          @if(auth()->check() && auth()->user()->role == 1 && !empty(auth()->user()->ou_id))
          <!-- End User Nav -->
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('users') }}">
                  <i class="bi bi-person"></i>
                  <span>Users</span>
              </a>
          </li>
          <!-- End User  Nav -->
          <!-- Start Courses Nav -->
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('courses') }}">
                  <i class="bi bi-person"></i>
                  <span>Course List</span>
              </a>
          </li>
          <!-- End Courses Nav -->
          @endif

          @if(auth()->check() && auth()->user()->role == 2)
          <!-- End User Nav -->
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('users') }}">
                  <i class="bi bi-person"></i>
                  <span>Users</span>
              </a>
          </li>
          <!-- End User  Nav -->
          <!-- Start Courses Nav -->
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('courses') }}">
                  <i class="bi bi-person"></i>
                  <span>Course List</span>
              </a>
          </li>
          <!-- End Courses Nav -->
          @endif

          @if(auth()->check() && auth()->user()->role == 3)
          <!-- Start Courses Nav -->
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('courses') }}">
                  <i class="bi bi-person"></i>
                  <span>Course List</span>
              </a>
          </li>
          <!-- End Courses Nav -->
          @endif
          <li class="nav-item">
              <a class="nav-link collapsed" href="{{ url('groups') }}">
                  <i class="bi bi-person"></i>
                  <span>Groups</span>
              </a>
          </li>
      </ul>

  </aside><!-- End Sidebar-->