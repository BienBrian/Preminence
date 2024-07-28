@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-pie'></i> <b>Overview</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Overview</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class='row'>
                <div class="col-xl-3 col-lg-6">
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Collected</h5><br>
                                    <span class="h5 font-weight-bold mb-0">{{ number_format($collected, 2) }}</span>
                                </div>
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-danger text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Spent</h5><br>
                                    <span class="h5 font-weight-bold mb-0">{{ number_format($spent, 2) }}</span>
                                </div>
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-warning text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-arrow-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Balance</h5><br>
                                    <span
                                        class="h5 font-weight-bold mb-0">{{ number_format($collected - $spent, 2) }}</span>
                                </div>
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-yellow text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Donations</h5><br>
                                    <span class="h5 font-weight-bold mb-0">{{ number_format($donation, 2) }}</span>
                                </div>
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-info text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-donate"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header bg-transparent">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase ls-1 mb-1">Overview</h6>
                                    <h5 class="mb-0">Collections History (KSH)</h5>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <div class="dropdown myears">
                                        <?php $year = date('Y') - 10; ?>
                                        <button class="btn pt-1 pb-1 pl-2 pr-2 btn-primary" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span>Year: {{ date('Y') }}</span><i class='fas fa-angle-down'></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right"
                                            aria-labelledby="navbar-default_dropdown_1">
                                            @for ($i = $year; $i <= date('Y'); $i++)
                                                <a class="dropdown-item" href="#">{{ $i }}</a>
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
                                    <h5 class="mb-0">Expenditure</h5>
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

                <div class="col-xl-6 mt-4 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header bg-transparent">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase ls-1 mb-1">Overview</h6>
                                    <h5 class="mb-0">Expenditure (KSH)</h5>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <div class="dropdown eyears">
                                        <?php $year = date('Y'); ?>
                                        <button class="btn pt-1 pb-1 pl-2 pr-2 btn-primary" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span>Year: {{ date('Y') }}</span><i class='fas fa-angle-down'></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right"
                                            aria-labelledby="navbar-default_dropdown_1">
                                            @for ($i = $year; $i >= date('Y') - 10; $i--)
                                                <a class="dropdown-item" href="#">{{ $i }}</a>
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
                                <canvas id="echart-finances" class="chart-canvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 mt-4 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header bg-transparent">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase ls-1 mb-1">Overview</h6>
                                    <h5 class="mb-0">Sources (KSH)</h5>
                                </div>
                                <div class="col d-flex justify-content-end">
                                    <div class="dropdown sources">
                                        <input name="sources" type="hidden"
                                            value="{{ $sources->first() != null ? $sources->first()->id : '0' }}" />
                                        <button class="btn pt-1 pb-1 pl-2 pr-2 btn-primary"
                                            href="{{ $sources->first() != null ? $sources->first()->id : '0' }}"
                                            role="button" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <span>{{ $sources->first() != null ? $sources->first()->name : 'No Items' }}</span><i
                                                class='fas fa-angle-down'></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right"
                                            aria-labelledby="navbar-default_dropdown_1">
                                            @foreach ($sources as $source)
                                                <a class="dropdown-item"
                                                    href="{{ $source->id }}">{{ $source->name }}</a>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="dropdown byears">
                                        <?php $year = date('Y'); ?>
                                        <button class="btn pt-1 pb-1 pl-2 pr-2 btn-outline-primary" href="#"
                                            role="button" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <span>{{ date('Y') }}</span><i class='fas fa-angle-down'></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right"
                                            aria-labelledby="navbar-default_dropdown_1">
                                            @for ($i = $year; $i >= date('Y') - 10; $i--)
                                                <a class="dropdown-item" href="#">{{ $i }}</a>
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
                                <canvas id="bchart-finances" class="chart-canvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
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
                    datasets: [{
                        backgroundColor: ["#f5365c", "#dde"],
                        data: [<?php echo $spent; ?>, <?php echo $collected - $spent; ?>]
                    }]
                },
                options: {
                    title: {
                        display: true,
                        fontFamily: "QuickSand",
                    },
                    legend: {
                        display: true,
                        fontFamily: "QuickSand",
                        position: 'bottom'
                    }
                }
            });

            //Collections Chart
            var ctx = document.getElementById('chart-finances').getContext('2d');

            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    datasets: [{
                        labels: '',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0)'
                        ],
                        borderColor: [
                            '#4D2DB7'
                        ],
                        borderWidth: 3
                    }]
                },
                options: {
                    fontFamily: "QuickSand",
                    responsive: true,
                    legend: {
                        display: false
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                drawOnChartArea: false
                                //display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: "",
                                fontColor: '#000',
                                fontFamily: "QuickSand",
                            },
                            ticks: {
                                beginAtZero: false,
                                fontFamily: "QuickSand",
                                fontColor: "#000",
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                drawOnChartArea: false
                                //display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '(Attendees)',
                                fontColor: '#000',
                                fontFamily: "QuickSand",
                            },
                            ticks: {
                                beginAtZero: false,
                                fontFamily: "QuickSand",
                                fontColor: "#000",
                            }
                        }]
                    },
                }
            });

            changeChart(<?php echo date('Y'); ?>);
            $('.myears a').click(function(e) {
                e.preventDefault();
                $('.myears button span').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                var years = $.trim($(this).text());
                changeChart(years);
            });

            function changeChart(years) {
                $.ajax({
                    url: "{{ url('dashboard/view') }}/" + years,
                    type: "GET",
                }).done(function(data) {
                    var mydata = JSON.parse(data);
                    var chartdata = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    $.each(mydata, function(index, value) {
                        var myindex = value.month - 1;
                        chartdata[myindex] = value.totals;
                    });
                    myChart.destroy();
                    myChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
                                'Oct', 'Nov', 'Dec'
                            ],
                            datasets: [{
                                data: chartdata,
                                labels: '',
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0)'
                                ],
                                borderColor: [
                                    '#4D2DB7'
                                ],
                                borderWidth: 3
                            }]
                        },
                        options: {
                            fontFamily: "QuickSand",
                            legend: {
                                display: false
                            },
                            responsive: true,
                            scales: {
                                xAxes: [{
                                    gridLines: {
                                        drawOnChartArea: false
                                        //display: false
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: "Months",
                                        fontColor: '#000',
                                        fontFamily: "QuickSand",
                                    },
                                    ticks: {
                                        beginAtZero: false,
                                        fontFamily: "QuickSand",
                                        fontColor: "#000",
                                    }
                                }],
                                yAxes: [{
                                    gridLines: {
                                        drawOnChartArea: true
                                        //display: false
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: '(Ksh)',
                                        fontColor: '#000',
                                        fontFamily: "QuickSand",
                                    },
                                    ticks: {
                                        beginAtZero: false,
                                        fontFamily: "QuickSand",
                                        fontColor: "#000",
                                    }
                                }]
                            },
                        }
                    });
                    $('.myears button span').html("Year: " + years);
                });
            }

            //expenditure Chart
            var ectx = document.getElementById('echart-finances').getContext('2d');

            var emyChart = new Chart(ectx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    datasets: [{
                        label: '',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0)'
                        ],
                        borderColor: [
                            '#4D2DB7'
                        ],
                        borderWidth: 3
                    }]
                },
                options: {
                    fontFamily: "QuickSand",
                    legend: {
                        display: false
                    },
                    responsive: true,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                drawOnChartArea: false
                                //display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: "Months",
                                fontColor: '#000',
                                fontFamily: "QuickSand",
                            },
                            ticks: {
                                beginAtZero: false,
                                fontFamily: "QuickSand",
                                fontColor: "#000",
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                drawOnChartArea: true
                                //display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '(Ksh)',
                                fontColor: '#000',
                                fontFamily: "QuickSand",
                            },
                            ticks: {
                                beginAtZero: false,
                                fontFamily: "QuickSand",
                                fontColor: "#000",
                            }
                        }]
                    }
                }
            });

            changeeChart(<?php echo date('Y'); ?>);
            $('.eyears a').click(function(e) {
                e.preventDefault();
                $('.eyears button span').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                var years = $.trim($(this).text());
                changeeChart(years);
            });

            function changeeChart(years) {
                $.ajax({
                    url: "{{ url('dashboard/expenditure') }}/" + years,
                    type: "GET",
                }).done(function(data) {
                    var mydata = JSON.parse(data);
                    var chartdata = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    $.each(mydata, function(index, value) {
                        var myindex = value.month - 1;
                        chartdata[myindex] = value.totals;
                    });
                    emyChart.destroy();
                    emyChart = new Chart(ectx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
                                'Oct', 'Nov', 'Dec'
                            ],
                            datasets: [{
                                label: '',
                                data: chartdata,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0)'
                                ],
                                borderColor: [
                                    '#4D2DB7'
                                ],
                                borderWidth: 3
                            }]
                        },
                        options: {
                            fontFamily: "QuickSand",
                            legend: {
                                display: false
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: {
                                        drawOnChartArea: false
                                        //display: false
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: "Months",
                                        fontColor: '#000',
                                        fontFamily: "QuickSand",
                                    },
                                    ticks: {
                                        beginAtZero: false,
                                        fontFamily: "QuickSand",
                                        fontColor: "#000",
                                    }
                                }],
                                yAxes: [{
                                    gridLines: {
                                        drawOnChartArea: true
                                        //display: false
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: '(Ksh)',
                                        fontColor: '#000',
                                        fontFamily: "QuickSand",
                                    },
                                    ticks: {
                                        beginAtZero: false,
                                        fontFamily: "QuickSand",
                                        fontColor: "#000",
                                    }
                                }]
                            },
                        }
                    });
                    $('.eyears button span').html("Year: " + years);
                });
            }

            //Others Chart
            var bctx = document.getElementById('bchart-finances').getContext('2d');

            var bmyChart = new Chart(bctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                        'Dec'
                    ],
                    datasets: [{
                        label: '',
                        data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0)'
                        ],
                        borderColor: [
                            '#4D2DB7'
                        ],
                        borderWidth: 3
                    }]
                },
                options: {
                    fontFamily: "QuickSand",
                    legend: {
                        display: false
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                drawOnChartArea: false
                                //display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: "Months",
                                fontColor: '#000',
                                fontFamily: "QuickSand",
                            },
                            ticks: {
                                beginAtZero: false,
                                fontFamily: "QuickSand",
                                fontColor: "#000",
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                drawOnChartArea: true
                                //display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: '(KSh)',
                                fontColor: '#000',
                                fontFamily: "QuickSand",
                            },
                            ticks: {
                                beginAtZero: false,
                                fontFamily: "QuickSand",
                                fontColor: "#000",
                            }
                        }]
                    }
                }
            });

            $('.sources a').click(function(e) {
                e.preventDefault();
                var name = $(this).text();
                var id = $(this).attr('href');
                var years = $('.byears button span').text();
                $('.sources button span').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                changeOthersChart(id, name, years);
            });

            $('.byears a').click(function(e) {
                e.preventDefault();
                $('.byears button span').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                var years = $.trim($(this).text());
                changeOthersChart($("input[name='sources']").val(), $('.sources button span').text(),
                    years);
            });

            changeOthersChart($("input[name='sources']").val(), $('.sources button span').text(), $(
                '.byears button span').text());

            function changeOthersChart(id, name, years) {
                $.ajax({
                    url: "{{ url('/dashboard/view') }}/" + years + "/" + id,
                    type: "GET",
                }).done(function(data) {
                    var mydata = JSON.parse(data);
                    var chartdata = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                    $.each(mydata, function(index, value) {
                        var myindex = value.month - 1;
                        chartdata[myindex] = value.totals;
                    });
                    bmyChart.destroy();
                    bmyChart = new Chart(bctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep',
                                'Oct', 'Nov', 'Dec'
                            ],
                            datasets: [{
                                label: '',
                                data: chartdata,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0)'
                                ],
                                borderColor: [
                                    '#4D2DB7'
                                ],
                                borderWidth: 3

                            }]
                        },
                        options: {
                            fontFamily: "QuickSand",
                            legend: {
                                display: false
                            },
                            scales: {
                                xAxes: [{
                                gridLines: {
                                    drawOnChartArea: false
                                    //display: false
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: "Month(s)",
                                    fontColor: '#000',
                                    fontFamily: "QuickSand",
                                },
                                ticks: {
                                    beginAtZero: false,
                                    fontFamily: "QuickSand",
                                    fontColor: "#000",
                                }
                                }],
                                yAxes: [{
                                    gridLines: {
                                    drawOnChartArea: true
                                    //display: false
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: '(Ksh.)',
                                    fontColor: '#000',
                                    fontFamily: "QuickSand",
                                },
                                ticks: {
                                    beginAtZero: false,
                                    fontFamily: "QuickSand",
                                    fontColor: "#000",
                                }
                                }]
                            }
                        }
                    });
                    $('.byears button span').html(years);
                    $('.sources button span').html(name);
                    $("input[name='sources']").val(id);
                });
            }

        });
    </script>
@endpush
