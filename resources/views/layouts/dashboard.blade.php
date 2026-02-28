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
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('dashboard/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}" />
    <!--custom css-->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}" />


</head>

@php
$activeModule = '';
if (Request::is('dashboard/website*')) $activeModule = 'website';
elseif (Request::is('dashboard/finances*')) $activeModule = 'finances';
elseif (Request::is('dashboard/people*') || Request::is('dashboard/users/all*') || Request::is('dashboard/users/view*') || Request::is('dashboard/users/duplicates*') || Request::is('dashboard/children') || Request::is('dashboard/children/view*')) $activeModule = 'people';
elseif (Request::is('dashboard/events_and_notices*') || Request::is('dashboard/children/attendance*')) $activeModule = 'events';
elseif (Request::is('dashboard/spiritual*')) $activeModule = 'spiritual';
elseif (Request::is('dashboard/communication*')) $activeModule = 'communication';
elseif (Request::is('dashboard/reports*')) $activeModule = 'reports';
// Settings uses dropdown popover, not secondary panel
@endphp

<body class="hold-transition sidebar-mini layout-fixed {{ $activeModule ? 'secondary-open sidebar-collapse' : '' }}" data-active-module="{{ $activeModule }}">
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
                @if (auth()->user()->can('View Payment Settings') || auth()->user()->can('View Roles'))
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" title="Settings">
                        <i class="fas fa-cog"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right border-0 shadow" style="min-width:220px;">
                        <span class="dropdown-item-text dropdown-header font-weight-bold"><i class="fas fa-cog mr-1"></i> Settings</span>
                        <div class="dropdown-divider"></div>
                        <a href="{{ url('dashboard/settings/general') }}" class="dropdown-item {{ Request::is('dashboard/settings/general*') ? 'active' : '' }}"><i class="fas fa-sliders-h mr-2"></i> General Settings</a>
                        <a href="{{ url('dashboard/settings/funds/sources') }}" class="dropdown-item {{ Request::is('dashboard/settings/funds*') ? 'active' : '' }}"><i class="fas fa-coins mr-2"></i> Fund Sources</a>
                        @can('View Payment Settings')
                        <a href="{{ url('dashboard/payments/settings/banks') }}" class="dropdown-item {{ Request::is('dashboard/payments/settings*') ? 'active' : '' }}"><i class="fas fa-credit-card mr-2"></i> Payment Settings</a>
                        @endcan
                        @can('View Roles')
                        <a href="{{ url('dashboard/users/roles') }}" class="dropdown-item {{ Request::is('dashboard/users/roles*') ? 'active' : '' }}"><i class="fas fa-user-shield mr-2"></i> Roles</a>
                        @endcan
                        @can('Manage Tags')
                        <a href="{{ url('dashboard/settings/tags') }}" class="dropdown-item {{ Request::is('dashboard/settings/tags*') ? 'active' : '' }}"><i class="fas fa-tags mr-2"></i> Tags</a>
                        @endcan
                        <a href="{{ url('dashboard/settings/lookups') }}" class="dropdown-item {{ Request::is('dashboard/settings/lookups*') ? 'active' : '' }}"><i class="fas fa-list mr-2"></i> Lookups</a>
                        <a href="{{ url('dashboard/settings/integrations') }}" class="dropdown-item {{ Request::is('dashboard/settings/integrations*') ? 'active' : '' }}"><i class="fas fa-plug mr-2"></i> Integrations</a>
                    </div>
                </li>
                @endif

                {{-- SMS & Email Credits Chip --}}
                @can('View Credits')
                <li class="nav-item d-flex align-items-center" id="credits-chip-wrapper">
                    <a href="#" class="credits-chip" id="credits-chip" title="SMS & Email Credits">
                        <i class="fas fa-comment-sms"></i>
                        <span class="credits-count" id="sms-credits"><i class="fas fa-spinner fa-spin" style="font-size:0.7rem;"></i></span>
                        <span class="credits-divider">|</span>
                        <i class="fas fa-envelope"></i>
                        <span class="credits-count" id="email-credits"><i class="fas fa-spinner fa-spin" style="font-size:0.7rem;"></i></span>
                    </a>

                    {{-- Credits Purchase Popover --}}
                    <div class="credits-popover shadow" id="credits-popover">
                        <div class="credits-popover-arrow"></div>

                        {{-- Step 1: Purchase Form --}}
                        <div id="credits-step-1">
                            <div class="credits-popover-header">
                                <span class="font-weight-bold"><i class="fas fa-coins mr-1"></i> Buy Credits</span>
                                <a href="#" class="credits-popover-close" id="credits-popover-close">&times;</a>
                            </div>
                            <div class="credits-popover-body">
                                <div class="credits-balance-row">
                                    <div class="credits-balance-item">
                                        <i class="fas fa-comment-sms text-primary"></i>
                                        <div>
                                            <div class="credits-balance-label">SMS Credits</div>
                                            <div class="credits-balance-value font-weight-bold" id="credits-balance-sms">—</div>
                                        </div>
                                    </div>
                                    <div class="credits-balance-item">
                                        <i class="fas fa-envelope text-primary"></i>
                                        <div>
                                            <div class="credits-balance-label">Email Credits</div>
                                            <div class="credits-balance-value font-weight-bold" id="credits-balance-email">—</div>
                                        </div>
                                    </div>
                                </div>

                                @can('Buy Credits')
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold mb-1">Top Up</label>
                                    <div class="credits-type-toggle" id="credits-type-toggle">
                                        <button type="button" class="credits-type-btn active" data-type="sms">
                                            <i class="fas fa-comment-sms mr-1"></i> SMS
                                        </button>
                                        <button type="button" class="credits-type-btn" data-type="email">
                                            <i class="fas fa-envelope mr-1"></i> Email
                                        </button>
                                        <button type="button" class="credits-type-btn" data-type="both">
                                            <i class="fas fa-layer-group mr-1"></i> Both
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group mb-2" id="sms-amount-group">
                                    <label class="small font-weight-bold mb-1">SMS Amount (KES)</label>
                                    <input type="number" class="form-control form-control-sm credits-amount-input" id="credits-amount-sms" placeholder="e.g. 500" min="1">
                                </div>

                                <div class="form-group mb-2" id="email-amount-group" style="display:none;">
                                    <label class="small font-weight-bold mb-1">Email Amount (KES)</label>
                                    <input type="number" class="form-control form-control-sm credits-amount-input" id="credits-amount-email" placeholder="e.g. 500" min="1">
                                </div>

                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold mb-1">Payment Method</label>
                                    <select class="form-control form-control-sm" id="credits-payment-method">
                                        <option value="">-- Select --</option>
                                        <option value="mpesa">M-Pesa</option>
                                        <option value="card">Credit / Debit Card</option>
                                        <option value="bank">Bank Transfer</option>
                                    </select>
                                </div>

                                <div class="credits-preview" id="credits-preview" style="display:none;">
                                    <div class="credits-preview-row" id="preview-sms-row">
                                        <span><i class="fas fa-comment-sms mr-1"></i> SMS Credits</span>
                                        <span class="font-weight-bold text-success" id="preview-sms-credits">0</span>
                                    </div>
                                    <div class="credits-preview-row" id="preview-email-row" style="display:none;">
                                        <span><i class="fas fa-envelope mr-1"></i> Email Credits</span>
                                        <span class="font-weight-bold text-success" id="preview-email-credits">0</span>
                                    </div>
                                    <div class="credits-preview-row credits-preview-total">
                                        <span>Total</span>
                                        <span class="font-weight-bold">KES <span id="preview-total">0</span></span>
                                    </div>
                                    <div class="credits-preview-note">
                                        <i class="fas fa-info-circle mr-1"></i> 1 credit = KES 1
                                    </div>
                                </div>

                                <button class="btn btn-primary btn-sm btn-block mt-2" id="credits-next-btn" disabled>
                                    Next <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                                @else
                                <p class="text-muted small mt-2 mb-0"><i class="fas fa-lock mr-1"></i> Contact an administrator to purchase credits.</p>
                                @endcan
                            </div>
                        </div>

                        {{-- Step 2: Coming Soon --}}
                        @can('Buy Credits')
                        <div id="credits-step-2" style="display:none;">
                            <div class="credits-popover-header">
                                <span class="font-weight-bold"><i class="fas fa-coins mr-1"></i> Buy Credits</span>
                                <a href="#" class="credits-popover-close" id="credits-popover-close-2">&times;</a>
                            </div>
                            <div class="credits-popover-body text-center py-4">
                                <div class="credits-coming-soon-icon">
                                    <i class="fas fa-rocket"></i>
                                </div>
                                <h6 class="font-weight-bold mt-3 mb-2">Coming Soon!</h6>
                                <p class="text-muted small mb-3">You'll soon be able to purchase SMS credits on demand directly from this panel.</p>
                                <button class="btn btn-outline-primary btn-sm" id="credits-back-btn">
                                    <i class="fas fa-arrow-left mr-1"></i> Go Back
                                </button>
                            </div>
                        </div>
                        @endcan
                    </div>
                </li>
                @endcan

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('dashboard/shop') }}" title="Shop">
                        <i class="fas fa-shopping-bag"></i>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link profile" data-toggle="dropdown" href="#">
                        <div class='user-panel d-flex'>
                            <div class='image'>
                                <img src='{{ Auth::user()->image != '' ? asset('profile_images/' . Auth::user()->image) : asset('profile_images/default.jpg') }}'
                                    class="img-circle">
                            </div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right border-0 shadow">
                        <span class="dropdown-item-text py-2">
                            <span class="font-weight-bold">{{ \Auth::user()->firstname }} {{ \Auth::user()->lastname }}</span>
                            <br><small class="text-muted">{{ \Auth::user()->email }}</small>
                            <br>
                            @php $roles = \Auth::user()->getRoleNames(); @endphp
                            @foreach($roles as $role)
                                <span class="badge mt-1" style="background:#007bff;color:#fff;font-size:0.72rem;padding:3px 8px;border-radius:10px;">
                                    <i class="fas fa-user-shield mr-1" style="font-size:0.7rem;"></i>{{ $role }}
                                </span>
                            @endforeach
                        </span>
                        <div class="dropdown-divider"></div>
                        <a href="{{ url('dashboard/profile') }}" class="dropdown-item">
                            <i class="fas fa-user-circle mr-2"></i> My Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                  document.getElementById('logout-form').submit();">
                            <i class="fas fa-power-off mr-2"></i> Logout
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
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">

                        <li class="nav-item">
                            <a href="{{ url('dashboard/home') }}"
                                class="nav-link {{ Request::is('dashboard/home') ? 'active' : '' }}">
                                <i class="nav-icon main-icon shadow fas fa-th"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if (auth()->user()->can('View Website Settings'))
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/website*') ? 'active' : '' }}" data-module="website">
                                <i class="nav-icon main-icon shadow fas fa-globe"></i>
                                <p>Website <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endif

                        @can('View Finances')
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/finances*') ? 'active' : '' }}" data-module="finances">
                                <i class="nav-icon main-icon shadow fas fa-coins"></i>
                                <p>Finances <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endcan

                        @if (auth()->user()->can('View People') || auth()->user()->can('View Users'))
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/people*') || Request::is('dashboard/users/all*') || Request::is('dashboard/users/view*') || Request::is('dashboard/users/duplicates*') || Request::is('dashboard/children') || Request::is('dashboard/children/view*') ? 'active' : '' }}" data-module="people">
                                <i class="nav-icon main-icon shadow fas fa-users"></i>
                                <p>People <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endif

                        @can('View Events & Notices')
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/events_and_notices*') || Request::is('dashboard/children/attendance*') ? 'active' : '' }}" data-module="events">
                                <i class="nav-icon main-icon shadow fas fa-bell"></i>
                                <p>Events <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endcan

                        @can('View Spiritual')
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/spiritual*') ? 'active' : '' }}" data-module="spiritual">
                                <i class="nav-icon main-icon shadow fas fa-bible"></i>
                                <p>Spiritual <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endcan

                        @can('View Communication')
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/communication*') ? 'active' : '' }}" data-module="communication">
                                <i class="nav-icon main-icon shadow fas fa-envelope"></i>
                                <p>Communication <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endcan

                        @can('View Finances')
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/reports*') ? 'active' : '' }}" data-module="reports">
                                <i class="nav-icon main-icon shadow fas fa-chart-line"></i>
                                <p>Reports <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endcan

                        @can('View Prayer Requests')
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ Request::is('dashboard/prayer-requests*') ? 'active' : '' }}" data-module="prayer-requests">
                                <i class="nav-icon main-icon shadow fas fa-praying-hands"></i>
                                <p>Prayer Requests <span class="right nav-dots"><i class="fas fa-ellipsis-h"></i></span></p>
                            </a>
                        </li>
                        @endcan

                        @can('View Articles')
                        <li class="nav-item">
                            <a href="{{ url('dashboard/articles') }}"
                                class="nav-link {{ Request::is('dashboard/articles*') ? 'active' : '' }}">
                                <i class="nav-icon main-icon shadow fas fa-blog"></i>
                                <p>Articles</p>
                            </a>
                        </li>
                        @endcan

                        <li class="nav-item">
                            <a href="{{ url('dashboard/file-manager') }}"
                                class="nav-link {{ Request::is('dashboard/file-manager*') ? 'active' : '' }}">
                                <i class="nav-icon main-icon shadow fas fa-photo-video"></i>
                                <p>File Manager</p>
                            </a>
                        </li>

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

    <!-- Secondary Sidebar (Dynamic) -->
    <div class="ss-panel" id="secondary-sidebar">
        <div class="ss-header">
            <i class="mr-2" id="ss-icon"></i>
            <span class="font-weight-bold" id="ss-title"></span>
            <a href="#" class="ml-auto ss-close" id="btn-close-secondary"><i class="fas fa-arrow-left"></i></a>
        </div>
        <div id="ss-content">
            <!-- Populated dynamically from #module-menus -->
        </div>
    </div>
    <div class="ss-overlay" id="secondary-overlay"></div>

    <!-- Hidden Module Menus (used by JS to populate secondary sidebar + popovers) -->
    <div id="module-menus" style="display:none">

        @if (auth()->user()->can('View Website Settings'))
        <div data-module="website" data-title="Website Settings" data-icon="fas fa-globe">
            <ul class="ss-nav">
                <li><a href="{{ url('dashboard/website/settings') }}" class="{{ Request::is('dashboard/website/settings') ? 'active' : '' }}"><i class="fas fa-cog"></i> <span>General Settings</span></a></li>
                <li><a href="{{ url('dashboard/website/homepage') }}" class="{{ Request::is('dashboard/website/homepage') ? 'active' : '' }}"><i class="fas fa-home"></i> <span>Home Page</span></a></li>
                <li><a href="{{ url('dashboard/website/gallery') }}" class="{{ Request::is('dashboard/website/gallery*') ? 'active' : '' }}"><i class="fas fa-images"></i> <span>Gallery</span></a></li>
                <li><a href="{{ url('dashboard/website/pastorsmessage') }}" class="{{ Request::is('dashboard/website/pastorsmessage*') ? 'active' : '' }}"><i class="fas fa-cross"></i> <span>Pastor's Message</span></a></li>
                <li><a href="{{ url('dashboard/website/orderofservice') }}" class="{{ Request::is('dashboard/website/orderofservice*') ? 'active' : '' }}"><i class="fas fa-list-ol"></i> <span>Order of Service</span></a></li>
                <li><a href="{{ url('dashboard/website/weeklyverse') }}" class="{{ Request::is('dashboard/website/weeklyverse*') ? 'active' : '' }}"><i class="fas fa-book-open"></i> <span>Weekly Verse</span></a></li>
            </ul>
        </div>
        @endif

        @can('View Finances')
        <div data-module="finances" data-title="Finances" data-icon="fas fa-coins">
            <ul class="ss-nav">
                <li><a href="{{ url('dashboard/finances/overview') }}" class="{{ Request::is('dashboard/finances/overview') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> <span>Overview</span></a></li>
                <li><a href="{{ url('dashboard/finances/funds') }}" class="{{ Request::is('dashboard/finances/funds') ? 'active' : '' }}"><i class="fas fa-hand-holding-usd"></i> <span>Funds/Tithe/Offering</span></a></li>
                <li><a href="{{ url('dashboard/finances/tithing/individual') }}" class="{{ Request::is('dashboard/finances/tithing*') ? 'active' : '' }}"><i class="fas fa-user-tag"></i> <span>Individual Tithing</span></a></li>
                <li><a href="{{ url('dashboard/finances/budgets') }}" class="{{ Request::is('dashboard/finances/budgets*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> <span>Budgets</span></a></li>
                <li><a href="{{ url('dashboard/finances/donations') }}" class="{{ Request::is('dashboard/finances/donations*') ? 'active' : '' }}"><i class="fas fa-gift"></i> <span>Donations</span></a></li>
                <li><a href="{{ url('dashboard/finances/assets') }}" class="{{ Request::is('dashboard/finances/assets*') ? 'active' : '' }}"><i class="fas fa-building"></i> <span>Assets</span></a></li>
                <li><a href="{{ url('dashboard/finances/expenses') }}" class="{{ Request::is('dashboard/finances/expenses*') ? 'active' : '' }}"><i class="fas fa-receipt"></i> <span>Expenses</span></a></li>
                <li><a href="{{ url('dashboard/finances/activities') }}" class="{{ Request::is('dashboard/finances/activities*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i> <span>Activities</span></a></li>
                <li><a href="{{ url('dashboard/finances/summaries') }}" class="{{ Request::is('dashboard/finances/summaries*') ? 'active' : '' }}"><i class="fas fa-chart-bar"></i> <span>Summaries</span></a></li>
                <li><a href="{{ url('dashboard/finances/missing_mpesa_phones') }}" class="{{ Request::is('dashboard/finances/missing_mpesa_phones*') ? 'active' : '' }}"><i class="fas fa-phone-slash"></i> <span>Missing Mpesa Phones</span></a></li>
            </ul>
        </div>
        @endcan

        @if (auth()->user()->can('View People') || auth()->user()->can('View Users'))
        <div data-module="people" data-title="People" data-icon="fas fa-users">
            <ul class="ss-nav">
                @can('View Users')
                <li><a href="{{ url('dashboard/users/all') }}" class="{{ Request::is('dashboard/users/all*') || Request::is('dashboard/users/view*') ? 'active' : '' }}"><i class="fas fa-users"></i> <span>Users</span></a></li>
                @endcan
                <li><a href="{{ url('dashboard/people/pastors') }}" class="{{ Request::is('dashboard/people/pastors*') ? 'active' : '' }}"><i class="fas fa-user-tie"></i> <span>Pastors</span></a></li>
                <li><a href="{{ url('dashboard/people/communities') }}" class="{{ Request::is('dashboard/people/communities*') ? 'active' : '' }}"><i class="fas fa-sitemap"></i> <span>Communities</span></a></li>
                <li><a href="{{ url('dashboard/people/departments') }}" class="{{ Request::is('dashboard/people/departments*') ? 'active' : '' }}"><i class="fas fa-building-user"></i> <span>Departments</span></a></li>
                @can('View Children Checkin')
                <li><a href="{{ url('dashboard/children') }}" class="{{ Request::is('dashboard/children') || Request::is('dashboard/children/view*') ? 'active' : '' }}"><i class="fas fa-child"></i> <span>Children</span></a></li>
                @endcan
                @can('View Users')
                <li><a href="{{ url('dashboard/users/duplicates') }}" class="{{ Request::is('dashboard/users/duplicates*') ? 'active' : '' }}"><i class="fas fa-clone"></i> <span>Duplicates</span></a></li>
                @endcan
            </ul>
        </div>
        @endif

        @can('View Events & Notices')
        <div data-module="events" data-title="Events & Notices" data-icon="fas fa-bell">
            <ul class="ss-nav">
                <li><a href="{{ url('dashboard/events_and_notices/events') }}" class="{{ Request::is('dashboard/events_and_notices/events*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> <span>Events</span></a></li>
                <li><a href="{{ url('dashboard/events_and_notices/notices') }}" class="{{ Request::is('dashboard/events_and_notices/notices*') ? 'active' : '' }}"><i class="fas fa-bullhorn"></i> <span>Notices</span></a></li>
                <li><a href="{{ url('dashboard/events_and_notices/seminars') }}" class="{{ Request::is('dashboard/events_and_notices/seminars*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> <span>Seminars</span></a></li>
                <li><a href="{{ url('dashboard/events_and_notices/attendance') }}" class="{{ Request::is('dashboard/events_and_notices/attendance*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> <span>Attendance</span></a></li>
                @can('View Children Checkin')
                <li><a href="{{ url('dashboard/children/attendance') }}" class="{{ Request::is('dashboard/children/attendance*') ? 'active' : '' }}"><i class="fas fa-child"></i> <span>Children Check-in</span></a></li>
                @endcan
            </ul>
        </div>
        @endcan

        @can('View Spiritual')
        <div data-module="spiritual" data-title="Spiritual" data-icon="fas fa-bible">
            <ul class="ss-nav">
                <li><a href="{{ url('dashboard/spiritual/sermons') }}" class="{{ Request::is('dashboard/spiritual/sermons*') ? 'active' : '' }}"><i class="fas fa-microphone"></i> <span>Sermons</span></a></li>
                <li><a href="{{ url('dashboard/spiritual/discipleship') }}" class="{{ Request::is('dashboard/spiritual/discipleship*') ? 'active' : '' }}"><i class="fas fa-walking"></i> <span>Discipleship & Mentorship</span></a></li>
                <li><a href="{{ url('dashboard/prayer-requests') }}" class="{{ Request::is('dashboard/prayer-requests*') ? 'active' : '' }}"><i class="fas fa-pray"></i> <span>Prayer Requests</span></a></li>
                <li><a href="{{ url('dashboard/spiritual/testimonials') }}" class="{{ Request::is('dashboard/spiritual/testimonials*') ? 'active' : '' }}"><i class="fas fa-comments"></i> <span>Testimonials</span></a></li>
            </ul>
        </div>
        @endcan

        @can('View Communication')
        <div data-module="communication" data-title="Communication" data-icon="fas fa-envelope">
            <ul class="ss-nav">
                <li><a href="{{ url('dashboard/communication/emails') }}" class="{{ Request::is('dashboard/communication/emails*') ? 'active' : '' }}"><i class="fas fa-envelope"></i> <span>Emails</span></a></li>
                <li><a href="{{ url('dashboard/communication/sms') }}" class="{{ Request::is('dashboard/communication/sms*') ? 'active' : '' }}"><i class="fas fa-sms"></i> <span>SMS</span></a></li>
            </ul>
        </div>
        @endcan

        @can('View Finances')
        <div data-module="reports" data-title="Reports" data-icon="fas fa-chart-line">
            <ul class="ss-nav">
                <li><a href="{{ url('dashboard/reports') }}" class="{{ Request::is('dashboard/reports') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                <li><a href="{{ url('dashboard/reports/mpesa-logs') }}" class="{{ Request::is('dashboard/reports/mpesa-logs*') ? 'active' : '' }}"><i class="fas fa-mobile-alt"></i> <span>Mpesa Transaction Logs</span></a></li>
            </ul>
        </div>
        @endcan

        @can('View Prayer Requests')
        <div data-module="prayer-requests" data-title="Prayer Requests" data-icon="fas fa-praying-hands">
            <ul class="ss-nav">
                <li><a href="{{ url('dashboard/prayer-requests') }}" class="{{ Request::is('dashboard/prayer-requests') ? 'active' : '' }}"><i class="fas fa-list"></i> <span>All Requests</span></a></li>
                <li><a href="{{ url('dashboard/prayer-requests') }}?filter=moderation" class="{{ Request::is('dashboard/prayer-requests') && request('filter') === 'moderation' ? 'active' : '' }}"><i class="fas fa-gavel"></i> <span>Prayer Wall Moderation</span></a></li>
            </ul>
        </div>
        @endcan

        {{-- Settings moved to navbar dropdown popover --}}

    </div>
    <!-- /Hidden Module Menus -->

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
    <!-- SweetAlert2 -->
    <script src="{{ asset('dashboard/plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.33/moment-timezone-with-data.min.js"></script>
    <script>
        function postAction(url, confirmMessage) {
            if (confirmMessage && !confirm(confirmMessage)) return false;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
            return false;
        }
    </script>
    @stack('js')
    <script>
        toastr.options.closeButton = true;
        toastr.options.closeMethod = 'fadeOut';
        toastr.options.closeDuration = 300;
        toastr.options.closeEasing = 'swing';
    </script>
    @if (\Session::has('success'))
        <script>
            toastr.success("{{ addslashes(e(\Session::get('success'))) }}");
        </script>
    @endif
    @if (\Session::has('error'))
        <script>
            toastr.error("{{ addslashes(e(\Session::get('error'))) }}");
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
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                ]
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ===== Primary sidebar collapse state =====
            let menu = localStorage.getItem('toggleMenu');
            var sidebarWasCollapsed = (menu == '1');

            // Only apply localStorage sidebar state when secondary is NOT open
            // (PHP already set sidebar-collapse + secondary-open for module pages)
            if (!$('body').hasClass('secondary-open')) {
                if (menu != null) {
                    if (menu == 1) {
                        $('body').addClass('sidebar-collapse');
                    } else {
                        $('body').removeClass('sidebar-collapse');
                    }
                }
            }

            // Hamburger menu toggle
            $('.toggleMenu').click(function() {
                // Close secondary panel if open
                if ($('body').hasClass('secondary-open')) {
                    $('body').removeClass('secondary-open');
                }
                // AdminLTE has already toggled sidebar-collapse by now
                // Read the resulting state and save it
                setTimeout(function() {
                    if ($('body').hasClass('sidebar-collapse')) {
                        menu = 1;
                    } else {
                        menu = 0;
                    }
                    sidebarWasCollapsed = (menu == 1);
                    localStorage.setItem('toggleMenu', menu);
                }, 50);
            });

            // ===== Secondary Sidebar (Dynamic Module Loader) =====

            // Load module content into the secondary sidebar
            function loadModuleMenu(module) {
                var $menu = $('#module-menus [data-module="' + module + '"]');
                if (!$menu.length) return false;

                $('#ss-icon').attr('class', $menu.data('icon') + ' mr-2');
                $('#ss-title').text($menu.data('title'));
                $('#ss-content').html($menu.html());
                $('#secondary-sidebar').data('active-module', module);
                return true;
            }

            // Open secondary panel + collapse main sidebar to icons
            function openSecondary(module) {
                if (!loadModuleMenu(module)) return;
                // Remember sidebar state before collapsing
                sidebarWasCollapsed = $('body').hasClass('sidebar-collapse');
                if (!sidebarWasCollapsed) {
                    $('body').addClass('sidebar-collapse');
                }
                $('body').addClass('secondary-open');
            }

            // Close secondary panel + restore main sidebar
            function closeSecondary() {
                $('body').removeClass('secondary-open');
                // Restore sidebar to its original state
                if (!sidebarWasCollapsed) {
                    $('body').removeClass('sidebar-collapse');
                }
            }

            // Sidebar nav items with data-module → open secondary panel
            $('.nav-sidebar .nav-link[data-module]').click(function(e) {
                e.preventDefault();
                e.stopPropagation(); // prevent AdminLTE treeview handling
                var module = $(this).data('module');
                var currentModule = $('#secondary-sidebar').data('active-module');
                var isOpen = $('body').hasClass('secondary-open');

                // Close any open popovers
                $('.nav-dots').popover('hide');

                if (isOpen && currentModule === module) {
                    // Clicking same module → close
                    closeSecondary();
                } else if (isOpen) {
                    // Clicking different module → just swap content
                    loadModuleMenu(module);
                } else {
                    // Not open → open
                    openSecondary(module);
                }
            });

            // Close secondary on overlay or X click
            $('#secondary-overlay, #btn-close-secondary').click(function(e) {
                e.preventDefault();
                closeSecondary();
            });

            // Auto-open secondary sidebar for the active module on page load
            var activeModule = $('body').data('active-module');
            if (activeModule && activeModule !== '') {
                loadModuleMenu(activeModule);
                // secondary-open + sidebar-collapse already set by PHP on body tag
                // sidebarWasCollapsed reflects the user's preferred state from localStorage
            }

            // ===== Three-Dot Popover (Quick Access) =====
            $('.nav-dots').each(function() {
                var $dots = $(this);
                var module = $dots.closest('.nav-link').data('module');
                var $menu = $('#module-menus [data-module="' + module + '"]');
                if (!$menu.length) return;

                $dots.popover({
                    html: true,
                    content: '<div class="popover-nav">' + $menu.html() + '</div>',
                    placement: 'right',
                    trigger: 'manual',
                    container: 'body',
                    boundary: 'viewport'
                });

                $dots.on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    // Close other popovers first
                    $('.nav-dots').not($dots).popover('hide');
                    $dots.popover('toggle');
                });
            });

            // Close popovers on click outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.popover').length && !$(e.target).closest('.nav-dots').length) {
                    $('.nav-dots').popover('hide');
                }
            });

            @can('View Credits')
            // ===== Credits Chip Popover =====
            var $chip = $('#credits-chip');
            var $popover = $('#credits-popover');

            $chip.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $popover.toggleClass('show');
                if ($popover.hasClass('show')) {
                    $('#credits-step-1').show();
                    $('#credits-step-2').hide();
                }
            });

            $('#credits-popover-close, #credits-popover-close-2').on('click', function(e) {
                e.preventDefault();
                $popover.removeClass('show');
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#credits-chip-wrapper').length) {
                    $popover.removeClass('show');
                }
            });

            @can('Buy Credits')
            var $paymentMethod = $('#credits-payment-method');
            var $preview = $('#credits-preview');
            var $nextBtn = $('#credits-next-btn');
            var creditType = 'sms'; // 'sms', 'email', or 'both'

            // Credit type toggle
            $('#credits-type-toggle').on('click', '.credits-type-btn', function() {
                $('#credits-type-toggle .credits-type-btn').removeClass('active');
                $(this).addClass('active');
                creditType = $(this).data('type');

                if (creditType === 'sms') {
                    $('#sms-amount-group').show();
                    $('#email-amount-group').hide();
                    $('#credits-amount-email').val('');
                } else if (creditType === 'email') {
                    $('#sms-amount-group').hide();
                    $('#email-amount-group').show();
                    $('#credits-amount-sms').val('');
                } else {
                    $('#sms-amount-group').show();
                    $('#email-amount-group').show();
                }

                updateCreditsPreview();
            });

            // Update preview when inputs change
            $(document).on('input', '.credits-amount-input', function() {
                updateCreditsPreview();
            });

            $paymentMethod.on('change', function() {
                updateCreditsPreview();
            });

            function updateCreditsPreview() {
                var smsAmt = parseInt($('#credits-amount-sms').val()) || 0;
                var emailAmt = parseInt($('#credits-amount-email').val()) || 0;
                var method = $paymentMethod.val();
                var total = 0;
                var hasAmount = false;

                // Show/hide relevant preview rows
                if (creditType === 'sms' || creditType === 'both') {
                    $('#preview-sms-row').show();
                    $('#preview-sms-credits').text(smsAmt > 0 ? smsAmt.toLocaleString() + ' credits' : '0');
                    total += smsAmt;
                    if (smsAmt > 0) hasAmount = true;
                } else {
                    $('#preview-sms-row').hide();
                }

                if (creditType === 'email' || creditType === 'both') {
                    $('#preview-email-row').show();
                    $('#preview-email-credits').text(emailAmt > 0 ? emailAmt.toLocaleString() + ' credits' : '0');
                    total += emailAmt;
                    if (emailAmt > 0) hasAmount = true;
                } else {
                    $('#preview-email-row').hide();
                }

                if (hasAmount) {
                    $preview.show();
                    $('#preview-total').text(total.toLocaleString());
                } else {
                    $preview.hide();
                }

                // Enable next only when there's an amount and a payment method
                $nextBtn.prop('disabled', !(hasAmount && method));
            }

            // Next → coming soon
            $nextBtn.on('click', function() {
                $('#credits-step-1').hide();
                $('#credits-step-2').show();
            });

            // Back → form
            $('#credits-back-btn').on('click', function() {
                $('#credits-step-2').hide();
                $('#credits-step-1').show();
            });
            @endcan
            @endcan

            // ===== Load real credits balance via AJAX =====
            @can('View Credits')
            (function loadCreditsBalance() {
                $.ajax({
                    url: '{{ url("dashboard/communication/sms/credits-balance") }}',
                    method: 'GET',
                    success: function(data) {
                        var sms   = parseInt(data.sms)   || 0;
                        var email = parseInt(data.email) || 0;
                        $('#sms-credits').text(sms.toLocaleString());
                        $('#email-credits').text(email.toLocaleString());
                        $('#credits-balance-sms').text(sms.toLocaleString());
                        $('#credits-balance-email').text(email.toLocaleString());
                    },
                    error: function() {
                        $('#sms-credits').text('—');
                        $('#email-credits').text('—');
                        $('#credits-balance-sms').text('—');
                        $('#credits-balance-email').text('—');
                    }
                });
            })();
            @endcan

            // ===== Login check =====
            setInterval(() => {
                checkLogin();
            }, 5000);

            function checkLogin() {
                $.ajax({
                    url: '{{ url('check-login') }}',
                    method: 'GET',
                    success: function(response) {
                        if (!response.loggedIn) {
                            location.href = "{{ url('/login') }}";
                        }
                    },
                });
            }

        });
    </script>
</body>

</html>
