@include('layout.includes.head')

<meta name="csrf-token" content="{{ csrf_token() }}">


<body>

    @include('quiz_layout.sections.sidebar')

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
    $(document).ready(function() {
        $(document).on('change', '#switch_role', function() {
            var role_id = $(this).val();

            $.ajax({
                url: "{{ url('/users/switch_role') }}",
                type: 'POST',
                data: {
                    role_id: role_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    // alert(response);
                    alert(response.message);
                    location.reload(); // Refresh page to update permissions
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Failed to switch role. Please try again.");
                }
            });
        });
    });
    </script>
    @yield('js_scripts')


</body>

</html>