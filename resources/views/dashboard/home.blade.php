@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-th-large'></i> Dashboard</h5>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">

                <div class='col-sm-12 mb-2'>
                    <div class='card p-3 h-100'>
                        <div class="overflow-hidden position-relative border-radius-lg bg-cover h-100">
                            <span class="mask bg-gradient-dark"></span>
                            <div class="card-body position-relative z-index-1 d-flex flex-column h-100 p-3">
                                <h5 class="text-white font-weight-bolder mb-4 pt-2">Hi, {{ \Auth::user()->firstname }}!
                                    Welcome to <span
                                        style='font-weight: 300;'>{{ $site_settings == null ? 'Church App' : $site_settings->name }}</span>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                @can('View Website Settings')
                    <div class='col-sm-6 mb-2'>
                        <div class="card p-3 h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column h-100">
                                            <h5 class="font-weight-bolder">Manage <b>Websites</b></h5>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/settings') }}'><i
                                                    class='fas fa-link'></i> General Settings</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/homepage') }}'><i
                                                    class='fas fa-link'></i> Home Page</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/gallery') }}'><i
                                                    class='fas fa-link'></i> Gallery</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/pastorsmessage') }}'><i
                                                    class='fas fa-link'></i> Pastor's Message</a>
                                            <a class='nav-link text-dark'
                                                href='{{ url('dashboard/website/orderofservice') }}'><i class='fas fa-link'></i>
                                                Order Of Service</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/website/weeklyverse') }}'><i
                                                    class='fas fa-link'></i> Weekly Verse</a>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                        <div class="bg-gradient-primary border-radius-lg h-100">
                                            <!--<img src="{{ asset('images/waves-white.svg') }}"
                                                                            class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">-->
                                            <div
                                                class="position-relative d-flex align-items-center justify-content-center h-100">
                                                <img class="w-100 position-relative z-index-2 pt-4"
                                                    src="{{ asset('images/connected_world.svg') }}" alt="rocket">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('View Finances')
                    <div class='col-sm-6 mb-2'>
                        <div class="card p-3 h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column h-100">
                                            <h5 class="font-weight-bolder">Manage <b>Finances</b></h5>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/finances/overview') }}'><i
                                                    class='fas fa-link'></i> Overview</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/finances/funds') }}'><i
                                                    class='fas fa-link'></i> Funds/Tithe/Offering</a>
                                            <a class='nav-link text-dark'
                                                href='{{ url('dashboard/finances/tithing/individual') }}'><i
                                                    class='fas fa-link'></i> Individual Tithing</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/finances/budgets') }}'><i
                                                    class='fas fa-link'></i> Budgets</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/finances/donations') }}'><i
                                                    class='fas fa-link'></i> Donations</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/website/assets') }}'><i
                                                    class='fas fa-link'></i> Assets</a>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                        <div class="bg-gradient-primary border-radius-lg h-100">
                                            <!--<img src="{{ asset('images/waves-white.svg') }}"
                                                                            class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">-->
                                            <div
                                                class="position-relative d-flex align-items-center justify-content-center h-100">
                                                <img class="w-100 position-relative z-index-2 pt-4"
                                                    src="{{ asset('images/finance.svg') }}" alt="rocket">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('View People')
                    <div class='col-sm-6 mb-2'>
                        <div class="card p-3 h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column h-100">
                                            <h5 class="font-weight-bolder">Manage <b>People</b></h5>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/people/new') }}'><i
                                                    class='fas fa-link'></i> New</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/people/pastors') }}'><i
                                                    class='fas fa-link'></i> Pastors</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/people/communities') }}'><i
                                                    class='fas fa-link'></i> Communities</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/people/departments') }}'><i
                                                    class='fas fa-link'></i> Departments</a>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                        <div class="bg-gradient-primary border-radius-lg h-100">
                                            <!--<img src="{{ asset('images/waves-white.svg') }}"
                                                                            class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">-->
                                            <div
                                                class="position-relative d-flex align-items-center justify-content-center h-100">
                                                <img class="w-100 position-relative z-index-2 pt-4"
                                                    src="{{ asset('images/people_connected.svg') }}" alt="rocket">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('View Children Checkin')
                    <div class='col-sm-6 mb-2'>
                        <div class="card p-3 h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column h-100">
                                            <h5 class="font-weight-bolder">Manage <b>Children</b> Checkin</h5>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/children') }}'><i
                                                    class='fas fa-link'></i> Children</a>
                                            <a class='nav-link text-dark'
                                                href='{{ url('dashboard/children/attendance') }}'><i class='fas fa-link'></i>
                                                Children Checkin</a>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                        <div class="bg-gradient-primary border-radius-lg h-100">
                                            <!--<img src="{{ asset('images/waves-white.svg') }}"
                                                                            class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">-->
                                            <div
                                                class="position-relative d-flex align-items-center justify-content-center h-100">
                                                <img class="w-100 position-relative z-index-2 pt-4"
                                                    src="{{ asset('images/true_friends.svg') }}" alt="rocket">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('View Spiritual')
                    <div class='col-sm-6 mb-2'>
                        <div class="card p-3 h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column h-100">
                                            <h5 class="font-weight-bolder">Manage <b>Spiritual</b></h5>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/spiritual/sermons') }}'><i
                                                    class='fas fa-link'></i> Sermons</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/spiritual/prayers') }}'><i
                                                    class='fas fa-link'></i> Prayers</a>
                                            <a class='nav-link text-dark'
                                                href='{{ url('dashboard/spiritual/testimonials') }}'><i
                                                    class='fas fa-link'></i> Testimonials</a>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                        <div class="bg-gradient-primary border-radius-lg h-100">
                                            <!--<img src="{{ asset('images/waves-white.svg') }}"
                                                                            class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">-->
                                            <div
                                                class="position-relative d-flex align-items-center justify-content-center h-100">
                                                <img class="w-100 position-relative z-index-2 pt-4"
                                                    src="{{ asset('images/bibliophile.svg') }}" alt="rocket">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
<!--
                @can('View Shop')
                    <div class='col-sm-6 mb-2'>
                        <div class="card p-3 h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column h-100">
                                            <h5 class="font-weight-bolder">Manage <b>Shop</b></h5>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/shop/products') }}'><i
                                                    class='fas fa-link'></i> Products</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/shop/purchases') }}'><i
                                                    class='fas fa-link'></i> Purchases</a>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                        <div class="bg-gradient-primary border-radius-lg h-100">
                                            <div
                                                class="position-relative d-flex align-items-center justify-content-center h-100">
                                                <img class="w-100 position-relative z-index-2 pt-4"
                                                    src="{{ asset('images/empty_cart.svg') }}" alt="rocket">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
                -->
                @can('View Communication')
                    <div class='col-sm-6 mb-2'>
                        <div class="card p-3 h-100">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex flex-column h-100">
                                            <h5 class="font-weight-bolder">Manage <b>Communication</b></h5>
                                            <a class='nav-link text-dark'
                                                href='{{ url('dashboard/communication/emails') }}'><i
                                                    class='fas fa-link'></i> Emails</a>
                                            <a class='nav-link text-dark' href='{{ url('dashboard/communication/sms') }}'><i
                                                    class='fas fa-link'></i> SMS</a>
                                            <a class='nav-link text-dark'
                                                href='{{ url('dashboard/communication/schedule/sms') }}'><i
                                                    class='fas fa-link'></i> Scheduled SMS</a>
                                        </div>
                                    </div>

                                    <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                        <div class="bg-gradient-primary border-radius-lg h-100">
                                            <!--<img src="{{ asset('images/waves-white.svg') }}"
                                                                            class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">-->
                                            <div
                                                class="position-relative d-flex align-items-center justify-content-center h-100">
                                                <img class="w-100 position-relative z-index-2 pt-4"
                                                    src="{{ asset('images/typing.svg') }}" alt="rocket">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endcan



                <!-- ./col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        });
    </script>
@endpush
