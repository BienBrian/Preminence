@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-line'></i> <b>Reports Dashboard</b></h5>
                </div>
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

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
                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow d-flex align-items-center justify-content-center">
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
                                    <div class="icon icon-shape bg-warning text-white rounded-circle shadow d-flex align-items-center justify-content-center">
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
                                    <span class="h5 font-weight-bold mb-0">{{ number_format($collected - $spent, 2) }}</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-yellow text-white rounded-circle shadow d-flex align-items-center justify-content-center">
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
                                    <div class="icon icon-shape bg-info text-white rounded-circle shadow d-flex align-items-center justify-content-center">
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
                                        @php $year = date('Y') - 10; @endphp
                                        <button class="btn pt-1 pb-1 pl-2 pr-2 btn-primary" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span>Year: {{ date('Y') }}</span><i class='fas fa-angle-down'></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                                            @for ($i = $year; $i <= date('Y'); $i++)
                                                <a class="dropdown-item" href="#">{{ $i }}</a>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
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
                            <div class="chart">
                                <canvas id="doughnut-chart" class="chart-canvas"></canvas>
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
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            new Chart(document.getElementById("doughnut-chart"), {
                type: 'doughnut',
                data: {
                    labels: ["Total Spend", "Current Balance"],
                    datasets: [{ backgroundColor: ["#f5365c", "#dde"], data: [@json($spent), @json($collected - $spent)] }]
                },
                options: { title: { display: true }, legend: { display: true, position: 'bottom' } }
            });

            var ctx = document.getElementById('chart-finances').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'line',
                data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], datasets: [{ data: [0,0,0,0,0,0,0,0,0,0,0,0], backgroundColor: ['rgba(255,99,132,0)'], borderColor: ['#4D2DB7'], borderWidth: 3 }] },
                options: { responsive: true, legend: { display: false } }
            });

            changeChart({{ date('Y') }});
            $('.myears a').click(function(e) { e.preventDefault(); changeChart($.trim($(this).text())); });

            function changeChart(years) {
                $.ajax({ url: "{{ url('dashboard/view') }}/" + years, type: "GET" }).done(function(data) {
                    var mydata = JSON.parse(data);
                    var chartdata = [0,0,0,0,0,0,0,0,0,0,0,0];
                    $.each(mydata, function(i, v) { chartdata[v.month - 1] = v.totals; });
                    myChart.destroy();
                    myChart = new Chart(ctx, {
                        type: 'line',
                        data: { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], datasets: [{ data: chartdata, backgroundColor: ['rgba(255,99,132,0)'], borderColor: ['#4D2DB7'], borderWidth: 3 }] },
                        options: { responsive: true, legend: { display: false } }
                    });
                    $('.myears button span').html("Year: " + years);
                });
            }
        });
    </script>
@endpush
