@include('layout.includes.head')



<body>

    @include('layout.sections.header')
    @include('layout.sections.sidebar')



    <main id="main" class="main">
        <div id="app">
                    <main class="col table-design">
                        <div class="pagetitle">
                            <h1>@yield('sub-title')</h1>
                        </div><!-- End Page Title -->
                        <!--begin::Main-->
                        @yield('content')
                        <!--end::Main-->
                    </main>
        </div>
    </main>

    @include('layout.sections.footer')

    @include('layout.includes.js')
    <script type="text/javascript">
    $(document).ready(function() {});
    </script>
    @yield('js_scripts')


</body>

</html>