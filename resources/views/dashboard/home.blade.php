@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-th-large'></i> Dashboard</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Welcome Banner -->
            <div class="card bg-gradient-primary mb-4">
                <div class="card-body py-3">
                    <h5 class="text-white font-weight-bolder mb-0">
                        Hi, {{ \Auth::user()->firstname }}! Welcome to
                        <span style='font-weight: 300;'>{{ $site_settings == null ? 'Church App' : $site_settings->name }}</span>
                    </h5>
                </div>
            </div>

            <!-- Quick Access Grid -->
            <div class="row">

                @can('View Website Settings')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Website Settings</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/website/settings') }}" class="text-dark"><i class="fas fa-cogs text-muted mr-1"></i> General Settings</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/website/homepage') }}" class="text-dark"><i class="fas fa-home text-muted mr-1"></i> Home Page</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/website/gallery') }}" class="text-dark"><i class="fas fa-images text-muted mr-1"></i> Gallery</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/website/pastorsmessage') }}" class="text-dark"><i class="fas fa-comment-alt text-muted mr-1"></i> Pastor's Message</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/website/orderofservice') }}" class="text-dark"><i class="fas fa-list-ol text-muted mr-1"></i> Order of Service</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/website/weeklyverse') }}" class="text-dark"><i class="fas fa-scroll text-muted mr-1"></i> Weekly Verse</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                @can('View Finances')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Finances</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/finances/overview') }}" class="text-dark"><i class="fas fa-chart-pie text-muted mr-1"></i> Overview</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/finances/funds') }}" class="text-dark"><i class="fas fa-hand-holding-usd text-muted mr-1"></i> Funds/Tithe/Offering</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/finances/tithing/individual') }}" class="text-dark"><i class="fas fa-user-check text-muted mr-1"></i> Individual Tithing</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/finances/budgets') }}" class="text-dark"><i class="fas fa-file-invoice-dollar text-muted mr-1"></i> Budgets</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/finances/donations') }}" class="text-dark"><i class="fas fa-donate text-muted mr-1"></i> Donations</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/finances/assets') }}" class="text-dark"><i class="fas fa-building text-muted mr-1"></i> Assets</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                @can('View People')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">People</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/users/all') }}" class="text-dark"><i class="fas fa-users text-muted mr-1"></i> Users</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/people/pastors') }}" class="text-dark"><i class="fas fa-user-tie text-muted mr-1"></i> Pastors</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/people/communities') }}" class="text-dark"><i class="fas fa-people-arrows text-muted mr-1"></i> Communities</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/people/departments') }}" class="text-dark"><i class="fas fa-sitemap text-muted mr-1"></i> Departments</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                @can('View Children Checkin')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-warning text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-child"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Children</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/children') }}" class="text-dark"><i class="fas fa-child text-muted mr-1"></i> Children</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/children/attendance') }}" class="text-dark"><i class="fas fa-clipboard-check text-muted mr-1"></i> Children Check-in</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                @can('View Events & Notices')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Events & Notices</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/events_and_notices/events') }}" class="text-dark"><i class="fas fa-calendar-alt text-muted mr-1"></i> Events</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/events_and_notices/notices') }}" class="text-dark"><i class="fas fa-bullhorn text-muted mr-1"></i> Notices</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/events_and_notices/seminars') }}" class="text-dark"><i class="fas fa-chalkboard-teacher text-muted mr-1"></i> Seminars</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/events_and_notices/attendance') }}" class="text-dark"><i class="fas fa-clipboard-list text-muted mr-1"></i> Attendance</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                @can('View Spiritual')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-bible"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Spiritual</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/spiritual/sermons') }}" class="text-dark"><i class="fas fa-podcast text-muted mr-1"></i> Sermons</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/spiritual/prayers') }}" class="text-dark"><i class="fas fa-praying-hands text-muted mr-1"></i> Prayers</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/spiritual/testimonials') }}" class="text-dark"><i class="fas fa-quote-left text-muted mr-1"></i> Testimonials</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                @can('View Communication')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-indigo text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Communication</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/communication/emails') }}" class="text-dark"><i class="fas fa-envelope-open-text text-muted mr-1"></i> Emails</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/communication/sms') }}" class="text-dark"><i class="fas fa-sms text-muted mr-1"></i> SMS</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/communication/schedule/sms') }}" class="text-dark"><i class="fas fa-clock text-muted mr-1"></i> Scheduled SMS</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                @can('View Articles')
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-teal text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-blog"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Articles</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/articles') }}" class="text-dark"><i class="fas fa-newspaper text-muted mr-1"></i> All Articles</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endcan

                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-orange text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-photo-video"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">File Manager</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/file-manager') }}" class="text-dark"><i class="fas fa-folder-open text-muted mr-1"></i> Manage Files</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                @if (auth()->user()->can('View Payment Settings') || auth()->user()->can('View Roles'))
                <div class="col-sm-6 col-lg-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center mr-3" style="width:42px;height:42px;flex-shrink:0;">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h6 class="font-weight-bold mb-0">Settings</h6>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1"><a href="{{ url('dashboard/settings/general') }}" class="text-dark"><i class="fas fa-sliders-h text-muted mr-1"></i> General Settings</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/settings/funds/sources') }}" class="text-dark"><i class="fas fa-money-check-alt text-muted mr-1"></i> Fund Sources</a></li>
                                @can('View Roles')
                                <li class="mb-1"><a href="{{ url('dashboard/users/roles') }}" class="text-dark"><i class="fas fa-user-shield text-muted mr-1"></i> Roles</a></li>
                                @endcan
                                <li class="mb-1"><a href="{{ url('dashboard/settings/integrations') }}" class="text-dark"><i class="fas fa-plug text-muted mr-1"></i> Integrations</a></li>
                                <li class="mb-1"><a href="{{ url('dashboard/settings/lookups') }}" class="text-dark"><i class="fas fa-search text-muted mr-1"></i> Lookups</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        });
    </script>
@endpush
