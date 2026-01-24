<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- plugins:css -->

    <link href="{{ Storage::url('vendors/css/vendor.bundle.base.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('vendors/mdi/css/materialdesignicons.min.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('vendors/ti-icons/css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('css/style.css') }}" rel="stylesheet">
    <link href="{{ Storage::url('css/app.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <!-- End layout styles -->
    <link rel="icon" type="image/png" href="{{ Storage::url('images/favicon/favicon-96x96.png') }}"
        sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ Storage::url('images/favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ Storage::url('images/favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ Storage::url('images/favicon/apple-touch-icon.png') }}" />
    <meta name="apple-mobile-web-app-title" content="Aayoo" />
    <link rel="manifest" href="{{ Storage::url('images/favicon/site.webmanifest') }}" />
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>

<body>
    <div class="container-scroller">
        <!-- partial:../../partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
                <a class="sidebar-brand brand-logo" href="{{ route('home') }}">
                    <img class="w-50 h-50" src="{{ Storage::url('images/aayoo.png') }}" alt="logo" />
                </a>
                <a class="sidebar-brand brand-logo-mini" href="{{ route('home') }}">
                    <img class="w-50 h-50" src="{{ Storage::url('images/aayoo-mini.svg') }}" alt="logo" />
                </a>
            </div>
            <ul class="nav">
                <li class="nav-item menu-items {{ request()->routeIs('home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('home') }}">
                        <span class="menu-icon">
                            <i class="mdi mdi-speedometer"></i>
                        </span>
                        <span class="menu-title">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <span class="menu-icon">
                            <i class="mdi mdi-account-group"></i>
                        </span>
                        <span class="menu-title">{{ __('Users') }}</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="{{ route('quotes.index') }}">
                        <span class="menu-icon">
                            <i class="mdi mdi-format-quote-open"></i>
                        </span>
                        <span class="menu-title">{{ __('Daily Quotes') }}</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="{{ route('hrvs.index') }}">
                        <span class="menu-icon">
                            <i class="mdi mdi-heart text-danger"></i>
                        </span>
                        <span class="menu-title">{{ __('HRV') }}</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" href="{{ route('faqs.index') }}">
                        <span class="menu-icon">
                            <i class="mdi mdi-help-circle text-warning"></i>
                        </span>
                        <span class="menu-title">{{ __('FAQs') }}</span>
                    <a class="nav-link" href="{{ route('faq-categories.index') }}">
                        <span class="menu-icon">
                            <i class="mdi mdi-folder-text text-warning"></i>
                        </span>
                        <span class="menu-title">FAQ Categories</span>
                    </a>
                </li>
                <li class="nav-item menu-items">
                    <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false"
                        aria-controls="ui-basic">
                        <span class="menu-icon">
                            <i class="mdi mdi-laptop text-warning"></i>
                        </span>
                        <span class="menu-title">Settings</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-basic">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link" href="{{ route('cms-pages.index') }}">Pages</a>
                            </li>
                        </ul>
                    </div>
                </li>
                {{-- <li class="nav-item menu-items">
                    <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false"
                        aria-controls="ui-basic">
                        <span class="menu-icon">
                            <i class="mdi mdi-laptop"></i>
                        </span>
                        <span class="menu-title">Basic UI Elements</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-basic">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"> <a class="nav-link"
                                    href="../../pages/ui-features/buttons.html">Buttons</a></li>
                            <li class="nav-item"> <a class="nav-link"
                                    href="../../pages/ui-features/dropdowns.html">Dropdowns</a></li>
                            <li class="nav-item"> <a class="nav-link"
                                    href="../../pages/ui-features/typography.html">Typography</a></li>
                        </ul>
                    </div>
                </li> --}}
            </ul>
        </nav>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper d-flex flex-column min-vh-100">
            <!-- partial:../../partials/_navbar.html -->
            <nav class="navbar p-0 fixed-top d-flex flex-row">
                <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
                    <a class="navbar-brand brand-logo-mini text-white" href="{{ route('home') }}">
                        <img src="{{ Storage::url('/images/aayoo-mini.svg') }}" class="w-100 h-100"
                            alt="logo" />
                        {{-- {{ config('app.name', 'Aayoo') }} --}}
                    </a>
                </div>
                <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
                    <button class="navbar-toggler navbar-toggler align-self-center" type="button"
                        data-toggle="minimize">
                        <span class="mdi mdi-menu"></span>
                    </button>
                    <ul class="navbar-nav navbar-nav-right">
                        <li class="nav-item dropdown">
                            <a class="nav-link" id="profileDropdown" href="#" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <div class="navbar-profile">
                                    <img class="img-xs rounded-circle"
                                        src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&bold=true&background=111728&color=00E5B3&rounded=true&format=png"
                                        alt="{{ Auth::user()->name }}">
                                    <p class="mb-0 d-none d-sm-block navbar-profile-name">{{ Auth::user()->name }}</p>
                                    <i class="mdi mdi-menu-down d-none d-sm-block"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list"
                                aria-labelledby="profileDropdown">
                                <h6 class="p-3 mb-0">Profile</h6>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('users.editprofile') }}" class="dropdown-item preview-item">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-dark rounded-circle">
                                            <i class="mdi mdi-cog text-success"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1">Settings</p>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item preview-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-dark rounded-circle">
                                            <i class="mdi mdi-logout text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1">Log out</p>
                                    </div>
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                        data-toggle="offcanvas">
                        <span class="mdi mdi-format-line-spacing"></span>
                    </button>
                </div>
            </nav>
            <!-- partial -->
            <div class="main-panel h-100">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="card-body">
                            @if (session()->has('success'))
                                <div class="alert alert-success bg-success text-white border-0" role="alert">
                                    <i class="mdi mdi-check-circle"></i> {{ session('success') }}
                                </div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger bg-danger text-white border-0" role="alert">
                                    <ul class="m-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    @yield('content')
                </div>
                <!-- content-wrapper ends -->
                <!-- partial:../../partials/_footer.html -->
                {{-- <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright ©
                            {{ date('Y') }}. All rights reserved.</span>
                        <span class="text-muted float-none float-sm-end d-block mt-1 mt-sm-0 text-center"> <span
                                class="text-muted float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Hand-crafted
                                & made with <i class="mdi mdi-heart text-danger"></i></span> <i
                                class="mdi mdi-heart text-danger"></i></span>
                    </div>
                </footer> --}}
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-HoA4...=="
        crossorigin="anonymous"></script>
    <script src="{{ Storage::url('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ Storage::url('vendors/jvectormap/jquery-jvectormap.min.js') }}"></script>
    <script src="{{ Storage::url('vendors/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/off-canvas.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/misc.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/settings.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/todolist.js') }}"></script>
    <script src="{{ Storage::url('vendors/js/dashboard.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    {{-- @yield('script') --}}
    @stack('scripts')
</body>

</html>
