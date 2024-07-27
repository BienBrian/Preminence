<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{ $site_settings == null?"favicon.ico":asset('website/'.$site_settings->favicon) }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $site_settings == null?"Church App":$site_settings->name }}</title>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/app.js') }}" defer></script>-->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">

    <!-- Styles -->
    <!--<link href="{{ asset('css/app.css') }}" rel="stylesheet">--><!-- Icons -->
    <link href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="{{asset('assets/css/argon.css?v=1.0.0')}}" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.1/croppie.css" rel="stylesheet">
    <!-- Custom style -->
    <link type="text/css" href="{{asset('css/styles.css')}}" rel="stylesheet">
    <link type="text/css" href="{{ $site_settings == null?"":asset('css/'.$site_settings->theme) }}" rel="stylesheet">
</head>
<body>
    @include('includes.user_header')

<!-- common header ends here -->
<!-- Header -->
<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
        <div class="container-fluid">
            <div class="header-body">
                <!-- Card stats -->
                <div class="row">
                    <div class="col-xl-3 col-lg-6">
                        <div class="card card-stats mb-4 mb-xl-0">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-uppercase text-muted mb-0">Collected</h5>
                                        <span class="h2 font-weight-bold mb-0">{{number_format($collected, 2)}}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                            <i class="fas fa-chart-bar"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3 mb-0 text-muted text-sm">
                                    <span class="text-nowrap">Amount collected in <strong>KSH</strong></span>
                                </p>
                            </div>
                        </div>
                    </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Spent</h5>
                                    <span class="h2 font-weight-bold mb-0">{{number_format($spent, 2)}}</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                <span class="text-nowrap">Amount spent in <strong>KSH</strong></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Balance</h5>
                                    <span class="h2 font-weight-bold mb-0">{{number_format($collected - $spent, 2)}}</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-yellow text-white rounded-circle shadow">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                <span class="text-nowrap">Balance in <strong>KSH</strong></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Donations</h5>
                                    <span class="h2 font-weight-bold mb-0">{{number_format($donation, 2)}}</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-info text-white rounded-circle shadow">
                                        <i class="fas fa-donate"></i>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                <span class="text-nowrap">Amount spent in <strong>KSH</strong></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-8 mb-5 mb-xl-0">
            <div class="card bg-gradient-default shadow">
                <div class="card-header bg-transparent">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-light ls-1 mb-1">Overview</h6>
                            <h2 class="text-white mb-0">Finances Overview (KSH)</h2>
                        </div>
                        <div class="col d-flex justify-content-end">
                            <div class="dropdown myears">
                                <?php $year = date('Y')-10; ?>
                                <button class="btn pt-1 pb-1 pl-2 pr-2 btn-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span>Year: {{date('Y')}}</span><i class='fas fa-angle-down'></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right" aria-labelledby="navbar-default_dropdown_1">
                                    @for($i=$year; $i <= date('Y'); $i++)
                                        <a class="dropdown-item" href="#">{{$i}}</a>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Chart -->
                    <div class="chart">
                        <!-- Chart wrapper -->
                        <canvas id="chart-finances" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header bg-transparent">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted ls-1 mb-1">Balance Overview</h6>
                            <h2 class="mb-0">Expenditure</h2>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Chart -->
                    <div class="chart">
                        <canvas id="doughnut-chart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    @include('includes.user_footer')
    <!-- Argon Scripts -->

    <!-- Core -->
    <script src="{{asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>

    <!-- Optional JS -->
    <script src="{{asset('assets/vendor/chart.js/dist/Chart.min.js')}}"></script>
    <script src="{{asset('assets/vendor/chart.js/dist/Chart.extension.js')}}"></script>

    <!-- Argon JS -->
    <script src="{{asset('assets/js/argon.js?v=1.0.0')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.1/croppie.js"></script>
    <script>
        $(document).ready(function(){
            $('a.logout').click(function(e){
                e.preventDefault();
                $('#logout-form').submit();
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            /*Doughnut chart*/
            new Chart(document.getElementById("doughnut-chart"), {
            type: 'doughnut',
            data: {
                labels: ["Total Spend", "Current Balance"],
                datasets: [
                    {
                        backgroundColor: ["#f5365c","#dde"],
                        data: [<?php echo $spent; ?>,<?php echo $collected - $spent; ?>]
                    }
                ]
                },
                options: {
                    title: {
                        display: true
                    },
                    legend:{
                        display:true,
                        fontFamily: "Roboto",
                        position: 'bottom'
                    }
                }
            });

            var ctx = document.getElementById('chart-finances').getContext('2d');

            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        data: [0,0,0,0,0,0,0,0,0,0,0,0]
                    }]
                },
                options: {
                    fontFamily: "Open Sans",
                    responsive:true,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                color: "rgba(0, 0, 0, 0)",
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                color: "rgba(0, 0, 0, 0)",
                            },
                            ticks: {
                                beginAtZero: false
                            }
                       }]
                    },
                    legend: {
                        fontFamily: "Open Sans"
                    }
                }
            });

            changeChart(<?php echo date('Y'); ?>);
            $('.myears a').click(function(e){
                e.preventDefault();
                $('.myears button span').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                var years = $.trim($(this).text());
                changeChart(years);
            });

            function changeChart(years){
                var myurl = "/dashboard/"+years;
                $.ajax({
                    url: myurl,
                    type: "GET",
                }).done(function(data){
                    var mydata = JSON.parse(data);
                    var chartdata = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $.each(mydata, function(index, value) {
                        var myindex = value.month - 1;
                        chartdata[myindex] = value.totals;
                    });
                    myChart.destroy();
                    myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            datasets: [{
                                data: chartdata
                            }]
                        },
                        options: {
                            fontFamily: "Open Sans",
                            responsive:true,
                            scales: {
                                xAxes: [{
                                    gridLines: {
                                        color: "rgba(0, 0, 0, 0)",
                                    }
                                }],
                                yAxes: [{
                                    gridLines: {
                                        color: "rgba(0, 0, 0, 0)",
                                    },
                                    ticks: {
                                        beginAtZero: false
                                    }
                                }]
                            },
                            legend: {
                                fontFamily: "Open Sans"
                            }
                        }
                    });
                    $('.myears button span').html("Year: "+years);
                });
            }
        });
        </script>
    </body>
    </html>

