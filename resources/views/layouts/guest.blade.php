<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- plugins:css -->
    <link href="{{ Storage::url('vendors/css/vendor.bundle.base.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('vendors/mdi/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('vendors/ti-icons/css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('css/app.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('css/style.css') }}" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ Storage::url('images/favicon/favicon-96x96.png') }}"
        sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ Storage::url('images/favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ Storage::url('images/favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ Storage::url('images/favicon/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Aayoo" />
    <link rel="manifest" href="{{ Storage::url('images/favicon/site.webmanifest') }}" />
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="row w-100">
                <div class="content-wrapper full-page-wrapper d-flex align-items-center auth"
                    style="background:#111728">
                    @yield('content')
                </div>
                <!-- content-wrapper ends -->
            </div>
            <!-- row ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ Storage::url('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ Storage::url('vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
    <script src="{{ Storage::url('vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/off-canvas.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/misc.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/settings.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/todolist.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/dashboard.js') }}"></script>
</body>

</html>
