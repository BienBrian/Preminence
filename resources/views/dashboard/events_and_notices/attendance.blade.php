@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-calendar-alt'></i> Attendance</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Attendance</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <!-- Card stats -->
                <div class='col-sm-6 col-lg-4'>
                    <div class="card border border-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-primary text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Attendance this week</h5><br>
                                    <span class="h2 font-weight-bold mb-0">{{ number_format($thisweek, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-sm-6 col-lg-4'>
                    <div class="card border border-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-primary text-white rounded-circle shadow d-flex justify-content-center align-items-center">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Attendance this month</h5><br>
                                    <span class="h2 font-weight-bold mb-0">{{ number_format($thismonth, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-sm-6 col-lg-4'>
                    <div class="card border border-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-primary text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Attendance this Year</h5><br>
                                    <span class="h2 font-weight-bold mb-0">{{ number_format($thisyear, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header text-right">
                            <table>
                                <tr>
                                    <td>
                                        <div class="dropdown group_filter">
                                            <button class="btn btn-primary btn-sm" href="#" role="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class='g-show'>All Groups</span><i class='fas fa-angle-down'></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right"
                                                aria-labelledby="navbar-default_dropdown_1">
                                                <a class="dropdown-item" href="0">All Groups</a>
                                                @foreach ($groups as $group)
                                                    <a class="dropdown-item"
                                                        href="{{ $group->id }}">{{ $group->name }}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="dropdown attendance_filter">
                                            <button class="btn btn-primary btn-sm" href="#" role="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class='d-show'>Last 7 Days</span><i class='fas fa-angle-down'></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right"
                                                aria-labelledby="navbar-default_dropdown_1">
                                                <a class="dropdown-item" href="0">Latest 7 Days</a>
                                                <a class="dropdown-item" href="1">Latest 1 Month</a>
                                                <a class="dropdown-item" href="2">Latest 6 Month(s)</a>
                                                <a class="dropdown-item" href="3">Latest 1 Year</a>
                                                <a class="dropdown-item" href="4">Latest 5 Years</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ url('dashboard/events_and_notices/attendance/groups') }}"
                                            class="btn btn-outline-primary btn-sm">Groups</a>
                                    </td>
                                    <td>
                                        <a href='{{ url('dashboard/events_and_notices/attendance/new') }}' class='btn btn-primary btn-sm'><i
                                                class='fas fa-plus'></i> Add</a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-body">
                            <!-- Chart -->
                            <div class="chart">
                                <!-- Chart wrapper -->
                                <canvas id="attendance" class="chart-canvas"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='card shadow mt-4'>
                <div class='card-header border-0'>
                    <strong><i class='fas fa-clock'></i> Attendance Statistics</strong>
                </div>
                <div class='table-responsive'>
                    <table class='table' id="attendance-table">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Attendance Type</th>
                                <th>Name</th>
                                <th>Attendees</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <!--
                    <div class='pt-3 pb-3'>
                        {{ $attendance->links() }}
                    </div>-->
                </div>
            </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
                var groups = 0;
                var time = 0;
                var attendancetable;
                //Collections Chart
                var ctx = document.getElementById('attendance').getContext('2d');

                var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: '',
                            data: [0, 0, 0, 0, 0, 0, 0],
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
                                    drawOnChartArea: true
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
                        }
                    }
                });

                loadChart(groups, time, "Last 7 Days", "All Groups", 0);
                $('.attendance_filter a').click(function(e) {
                    e.preventDefault();
                    $(".d-show").html("<i class='fas fa-circle-notch fa-spin'></i> Loading...");
                    var mytext = $(this).html();
                    time = $(this).attr('href');
                    loadChart(groups, time, mytext, mytext, 1);
                    attendancetable.draw();
                });

                $('.group_filter a').click(function(e) {
                    e.preventDefault();
                    $(".g-show").html("<i class='fas fa-circle-notch fa-spin'></i> Loading...");
                    var mytext = $(this).html();
                    groups = $(this).attr('href');
                    loadChart(groups, time, mytext, mytext, 2);
                    attendancetable.draw();
                });

                function loadChart(groups, time, time_name, group_name, changed) {
                    $.ajax({
                        url: "{{ url('dashboard/ajax/attendance') }}/" + groups + "/" + time,
                        method: "GET",
                    }).done(function(data) {
                        var mydata = JSON.parse(data);
                        var xaxis = new Array();
                        var yaxis = new Array();

                        $.each(mydata, function(key, value) {
                            xaxis.push(value.days);
                            yaxis.push(value.att);
                        });
                        myChart.destroy();

                        myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: xaxis,
                                datasets: [{
                                    label: '',
                                    data: yaxis,
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
                                            drawOnChartArea: true
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
                                }
                            }
                        });
                        if (changed == 1) {
                            $(".d-show").html(time_name);
                        } else if (changed == 2) {
                            $(".g-show").html(group_name);
                        }
                    }).fail(function() {
                        toastr.error("Unable to complete request");
                        if (changed == 1) {
                            $(".d-show").html(time_name);
                        } else if (changed == 2) {
                            $(".g-show").html(group_name);
                        }
                    });
                } //End Load Chart


                //Allow filtering
                attendancetable = $('#attendance-table').DataTable({
                    processing: true,
                    serverSide: true,
                    oLanguage: {
                        sProcessing: "<i class='fas fa-spinner fa-pulse'></i> Processing..."
                    },
                    "language": {
                        "paginate": {
                            "previous": "<i class='fas fa-angle-left'></i>",
                            "next": "<i class='fas fa-angle-right'></i>"
                        }
                    },
                    dom: 'lBrtip',
                    buttons: [{
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success text-white',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }, {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-primary text-white',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }],
                    ajax: //"{{ url('datatables/users') }}",
                    {
                        url: "{{ url('dashboard/datatables/attendance') }}",
                        data: function(d) {
                            d.group = groups;
                            d.time = time;
                        }
                    },
                    columns: [{
                            data: 'name',
                            name: "group",
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'attendance_type',
                            name: "attendance_type",
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'attendance_name',
                            name: "name",
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'attendance',
                            name: "attendees",
                            orderable: false,
                            searchable: true
                        },
                        {
                            data: 'date',
                            name: 'date',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        },
                    ]
                });


        });
    </script>
@endpush
