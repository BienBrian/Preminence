<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon"
        href="{{ $site_settings == null ? 'favicon.ico' : asset('website/' . $site_settings->favicon) }}">



    <title>{{ $site_settings == null ? 'Church App' : $site_settings->name }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,400i,700&display=fallback">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('fontawesome-free-6.4.0-web/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!--Select2 css-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- bootstrap 5-->
    <link rel='stylesheet' href="{{ asset('css/app.css') }}">
    <!-- dropzone css-->
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/jqvmap/jqvmap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dashboard/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/summernote/summernote-bs4.min.css') }}">
    <!--datatables-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <!--datetime picker-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!--croppie-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />
    <!--toastr-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <!--custom css-->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}" />


</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake"
                src="{{ $site_settings == null ? asset('website/icon.png') : asset('website/' . $site_settings->icon) }}"
                alt="{{ $site_settings == null ? 'Church App' : $site_settings->name }}" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light bg-white">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link toggleMenu d-flex align-items-center text-center" data-widget="pushmenu"
                        href="#" role="button"><i class="fa-solid fa-bars-staggered"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->

            <ul class="navbar-nav ml-auto">
                <!--
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center text-center" href="#" role="button"><i class="fa-solid fa-cog"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center text-center" href="#" role="button"><i class="fa-solid fa-bell"></i>
                        <span class="badge badge-primary navbar-badge">15</span></a>
                </li>-->
                <li class="nav-item dropdown">
                    <a class="nav-link profile" data-toggle="dropdown" href="#">
                        <div class='user-panel d-flex'>
                            <div class='image'>
                                <img src='{{ Auth::user()->image != '' ? asset('profile_images/' . Auth::user()->image) : asset('profile_images/default.jpg') }}'
                                    class="img-circle"> {{ \Auth::user()->firstname }} {{ \Auth::user()->lastname }}
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right border-0 shadow">
                        <!--<span class="dropdown-item dropdown-header text-left">{{ \Auth::user()->firstname }} {{ \Auth::user()->lastname }}</span>
                        <div class="dropdown-divider"></div>-->
                        <a href="{{ url('dashboard/profile') }}" class="dropdown-item">
                            <i class="fas fa-user-circle mr-2"></i> My Profile
                            <!--<span class="float-right text-muted text-sm">3 mins</span>-->
                        </a>
                        <!--<div class="dropdown-divider"></div>-->
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                  document.getElementById('logout-form').submit();">
                            <i class="fas fa-power-off mr-2"></i> Logout
                            <!--<span class="float-right text-muted text-sm">3 mins</span>-->
                        </a>
                    </div>
                </li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary">
            <!-- Brand Logo -->
            <a href="{{ url('dashboard/home') }}" class="brand-link shadow border-right">
                <img src="{{ $site_settings == null ? 'favicon.ico' : asset('website/' . $site_settings->favicon) }}"
                    alt="{{ $site_settings == null ? 'Church App' : $site_settings->name }}"
                    class="brand-image img-circle" style="opacity: .8">
                <span
                    class="brand-text font-weight-light">{{ $site_settings == null ? 'Church App' : $site_settings->name }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
                         with font-awesome or any other icon font library -->

                        <li class="nav-item">
                            <a href="{{ url('dashboard/home') }}"
                                class="nav-link {{ Request::is('dashboard/home') ? 'active' : '' }}">
                                <i class="nav-icon main-icon shadow fas fa-th"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        @if (auth()->user()->can('View Website Settings'))
                            <li class="nav-item {{ Request::is('dashboard/website*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/website*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-globe"></i>
                                    <p>
                                        Website Settings
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/website/settings') }}"
                                            class="nav-link {{ Request::is('dashboard/website/settings') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>General Settings</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/website/homepage') }}"
                                            class="nav-link {{ Request::is('dashboard/website/homepage') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Home Page Settings</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/website/gallery') }}"
                                            class="nav-link {{ Request::is('dashboard/website/gallery*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Gallery</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/website/pastorsmessage') }}"
                                            class="nav-link {{ Request::is('dashboard/website/pastorsmessage*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Pastor's Message</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/website/orderofservice') }}"
                                            class="nav-link {{ Request::is('dashboard/website/orderofservice*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Order of Service</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/website/weeklyverse') }}"
                                            class="nav-link {{ Request::is('dashboard/website/weeklyverse*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Weekly Verse</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View Finances'))
                            <li class="nav-item {{ Request::is('dashboard/finances*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/finances*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-coins"></i>
                                    <p>
                                        Finances
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/overview') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/overview') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Overview</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/funds') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/funds') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Funds/Tithe/Offering</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/tithing/individual') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/tithing*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Individual Tithing</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/budgets') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/budgets*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Budgets</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/donations') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/donations*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Donations</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/assets') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/assets*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Assets</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/expenses') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/expenses*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Expenses</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/activities') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/activities*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Activities</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/summaries') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/summaries*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Summaries</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/finances/missing_mpesa_phones') }}"
                                            class="nav-link {{ Request::is('dashboard/finances/missing_mpesa_phones*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Missing Mpesa Phones</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View People'))
                            <li class="nav-item {{ Request::is('dashboard/people*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/people*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-users"></i>
                                    <p>
                                        People
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/people/new') }}"
                                            class="nav-link {{ Request::is('dashboard/people/new*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>New</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/people/pastors') }}"
                                            class="nav-link {{ Request::is('dashboard/people/pastors*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Pastors</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/people/communities') }}"
                                            class="nav-link {{ Request::is('dashboard/people/communities*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Communities</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/people/departments') }}"
                                            class="nav-link {{ Request::is('dashboard/people/departments*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Departments</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View Events & Notices'))
                            <li class="nav-item {{ Request::is('dashboard/events_and_notices*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/events_and_notices*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-bell"></i>
                                    <p>
                                        Events & Notices
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/events_and_notices/events') }}"
                                            class="nav-link {{ Request::is('dashboard/events_and_notices/events*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Events</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/events_and_notices/notices') }}"
                                            class="nav-link {{ Request::is('dashboard/events_and_notices/notices*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Notices</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/events_and_notices/seminars') }}"
                                            class="nav-link {{ Request::is('dashboard/events_and_notices/seminars*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Seminars</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/events_and_notices/attendance') }}"
                                            class="nav-link {{ Request::is('dashboard/events_and_notices/attendance*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Attendance</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View Children Checkin'))
                            <li class="nav-item {{ Request::is('dashboard/children*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/children*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-children"></i>
                                    <p>
                                        Children
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/children') }}"
                                            class="nav-link {{ Request::is('dashboard/children') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Children</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/children/attendance') }}"
                                            class="nav-link {{ Request::is('dashboard/children/attendance*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Children Checkin</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View Spiritual'))
                            <li class="nav-item {{ Request::is('dashboard/spiritual*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/spiritual*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-bible"></i>
                                    <p>
                                        Spiritual
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/spiritual/sermons') }}"
                                            class="nav-link {{ Request::is('dashboard/spiritual/sermons*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Sermons</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/spiritual/prayers') }}"
                                            class="nav-link {{ Request::is('dashboard/spiritual/prayers*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Prayers</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/spiritual/testimonials') }}"
                                            class="nav-link {{ Request::is('dashboard/spiritual/testimonials*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Testimonials</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View Shop'))
                            <li class="nav-item {{ Request::is('dashboard/shop*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/shop*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-shop"></i>
                                    <p>
                                        Shop
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/shop/products') }}"
                                            class="nav-link {{ Request::is('dashboard/shop/products*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Products</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/shop/purchases') }}"
                                            class="nav-link {{ Request::is('dashboard/shop/purchases*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Purchases</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View Communication'))
                            <li class="nav-item {{ Request::is('dashboard/communication*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/communication*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-envelope"></i>
                                    <p>
                                        Communication
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/communication/emails') }}"
                                            class="nav-link {{ Request::is('dashboard/communication/emails*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Emails</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/communication/sms') }}"
                                            class="nav-link {{ Request::is('dashboard/communication/sms*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>SMS</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/communication/schedule/sms') }}"
                                            class="nav-link {{ Request::is('dashboard/communication/schedule/sms*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Schedule SMS</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @can('View Articles')
                        <li class="nav-item">
                            <a href="{{ url('dashboard/articles') }}"
                                class="nav-link {{ Request::is('dashboard/articles*') ? 'active' : '' }}">
                                <i class="nav-icon main-icon shadow fas fa-blog"></i>
                                <p>
                                    Articles
                                </p>
                            </a>
                        </li>
                        @endcan
                        @if (auth()->user()->can('View Payment Settings'))
                            <li class="nav-item {{ Request::is('dashboard/settings*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/settings*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-cog"></i>
                                    <p>
                                        Settings
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/settings/funds/sources') }}"
                                            class="nav-link {{ Request::is('dashboard/settings/funds*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Fund Sources</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/settings/sms') }}"
                                            class="nav-link {{ Request::is('dashboard/settings/sms*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>SMS Settings</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('dashboard/settings/email') }}"
                                            class="nav-link {{ Request::is('dashboard/settings/email*') ? 'active' : '' }}">
                                            <i class="fas fa-minus nav-icon"></i>
                                            <p>Email Settings</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->can('View Users') || auth()->user()->can('View Roles'))
                            <li class="nav-item {{ Request::is('dashboard/users*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ Request::is('dashboard/users*') ? 'active' : '' }}">
                                    <i class="nav-icon main-icon shadow fas fa-users"></i>
                                    <p>
                                        Users
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @can('View Users')
                                        <li class="nav-item">
                                            <a href="{{ url('dashboard/users/all') }}"
                                                class="nav-link {{ Request::is('dashboard/users/all*') || Request::is('dashboard/users/view*') ? 'active' : '' }}">
                                                <i class="fas fa-minus nav-icon"></i>
                                                <p>Users</p>
                                            </a>
                                        </li>
                                    @endcan
                                    @can('View Roles')
                                        <li class="nav-item">
                                            <a href="{{ url('dashboard/users/roles') }}"
                                                class="nav-link {{ Request::is('dashboard/users/roles*') ? 'active' : '' }}">
                                                <i class="fas fa-minus nav-icon"></i>
                                                <p>Roles</p>
                                            </a>
                                        </li>
                                    @endcan
                                </ul>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a href="{{ url('dashboard/profile') }}"
                                class="nav-link {{ Request::is('dashboard/profile') ? 'active' : '' }}">
                                <i class="nav-icon main-icon shadow fas fa-user"></i>
                                <p>
                                    Profile
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                          document.getElementById('logout-form').submit();">
                                <i class="nav-icon main-icon shadow fas fa-power-off"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                        <!--
                        <li class="nav-item mt-3">
                            <a class="nav-link btn btn-primary shadow order-now" href="{{ url('order/place') }}">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>Order Now</p>
                            </a>
                        </li>-->
                    </ul>
                </nav>
            </div>
            <!-- /.sidebar-menu -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong> {{ config('app.name', 'Laravel') }}&copy; {{ date('Y') }}</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <div class="user-not text-center">
        <p class='text-white text-center'>
            <i class="fas fa-spinner fa-pulse"></i> Updating... please wait!
        </p>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('dashboard/plugins/jquery/jquery.min.js') }}"></script>
    <!-- select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!--Datatables-->
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="{{ asset('dashboard/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('dashboard/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!--fixed columns datatable-->
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <!-- ChartJS -->
    <script src="{{ asset('dashboard/plugins/chart.js/Chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-rounded-bars"></script>
    <!-- jQuery Knob Chart -->
    <script src="{{ asset('dashboard/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ asset('dashboard/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('dashboard/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ asset('dashboard/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('dashboard/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ asset('dashboard/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dashboard/dist/js/adminlte.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <!--<script src="{{ asset('dashboard/dist/js/demo.js') }}"></script>-->
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{ asset('dashboard/dist/js/pages/dashboard.js') }}"></script>
    <!--datetimepicker-->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- croppie js-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
    <!--dropzone-->
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <!--toastr js-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.33/moment-timezone-with-data.min.js"></script>
    @stack('js')
    <script>
        toastr.options.closeButton = true;
        toastr.options.closeMethod = 'fadeOut';
        toastr.options.closeDuration = 300;
        toastr.options.closeEasing = 'swing';
    </script>
    @if (\Session::has('success'))
        <script>
            toastr.success("{!! \Session::get('success') !!}");
        </script>
    @endif
    @if (\Session::has('error'))
        <script>
            toastr.error("{!! \Session::get('error') !!}");
        </script>
    @endif
    @if (count($errors) > 0)
        @foreach ($errors->all() as $error)
            <script>
                toastr.error("{{ $error }}");
            </script>
        @endforeach
    @endif
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 150,
                toolbar: [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    //['font', ['strikethrough', 'superscript', 'subscript']],
                    //['fontsize', ['fontsize']],
                    //['color', ['color']],
                    //['para', ['ul', 'ol', 'paragraph']],
                    //['height', ['height']]
                ]
            });
            //var lastLink = $(".notifications-dropdown a.notification-dropdown-item:last").remove();

            //const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            //console.log(timeZone);
            //alert(timeZone);
            //localStorage.setItem('toggleMenu', 1);
            //localStorage.removeItem('myCat');
            //localStorage.clear();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let menu = localStorage.getItem('toggleMenu');
            if (menu != null) {
                if (menu == 1) {
                    $('body').addClass('sidebar-collapse');
                } else {
                    $('body').removeClass('sidebar-collapse');
                }
            }
            $('.toggleMenu').click(function() {
                if (menu == 1) {
                    menu = 0;
                } else if (menu == 0) {
                    menu = 1;
                } else {
                    menu = 1;
                }
                localStorage.setItem('toggleMenu', menu);
            });
            setInterval(() => {
                checkLogin();
            }, 5000);

            function checkLogin() {
                $.ajax({
                    url: '{{ url('check-login') }}',
                    method: 'GET',
                    success: function(response) {
                        if (!response.loggedIn) {
                            // User is not logged in
                            location.href = "{{ url('/login') }}";
                        }
                    },
                    /*
                                    error: function() {
                                        // Error handling
                                        console.log('Error checking login status');
                                    }*/
                });
            }

        });
    </script>
</body>

</html>
