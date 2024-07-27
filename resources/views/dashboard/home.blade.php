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
                                <h5 class="text-white font-weight-bolder mb-4 pt-2">Hi, {{ \Auth::user()->firstname }}! Welcome to <span style='font-weight: 300;'>{{ $site_settings == null?"Church App":$site_settings->name }}</span></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-sm-6 mb-2'>
                    <div class="card p-3 h-100">
                        <div class="card-body p-3">
                            <div class="row">

                                <div class="col-lg-6">
                                    <div class="d-flex flex-column h-100">
                                        <h5 class="font-weight-bolder">Manage <b>Websites</b> Module</h5>
                                        <a class='nav-link text-dark' href='{{ url("dashboard/settings") }}'><i class='fas fa-link'></i> Settings</a>
                                        <a class='nav-link text-dark' href='{{ url("dashboard/homepage") }}'><i class='fas fa-link'></i> Home Page</a>
                                        <a class='nav-link text-dark' href='{{ url("dashboard/gallery") }}'><i class='fas fa-link'></i> Gallery</a>
                                        <a class='nav-link text-dark' href='{{ url("dashboard/pastorsmessage") }}'><i class='fas fa-link'></i> Message</a>
                                    </div>
                                </div>

                                <div class="col-lg-5 ms-auto text-center mt-5 mt-lg-0">
                                    <div class="bg-gradient-primary border-radius-lg h-100">
                                        <!--<img src="{{ asset('images/waves-white.svg')}}"
                                            class="position-absolute h-100 w-50 top-0 d-lg-block d-none" alt="waves">-->
                                        <div
                                            class="position-relative d-flex align-items-center justify-content-center h-100">
                                            <img class="w-100 position-relative z-index-2 pt-4"
                                                src="{{ asset('images/connected_world.svg')}}" alt="rocket">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



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
