@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-clock'></i> <b>Running</b> Shares</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    @can('Add Queue Statuses')
                        <button class="btn btn-primary btn-sm btn-launch-modal" data-toggle="modal" data-target="#routeModal"><i
                                class='fas fa-plus'></i> Add Status
                        </button>
                    @else
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Running Shares</li>
                        </ol>
                    @endcan
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
                <div class="col-md-12 mb-3">


                    <!-- small box -->
                    <div class="card">
                        <div class="card-header">
                            <form class='search-form row d-flex align-items-end' id='search-form'>
                                <div class="col mb-1">
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-search'></i></span>
                                        </div>
                                    <input type="text" class="form-control" name="search" placeholder="Search">
                                    </div>
                                </div>
                                <div class="col mb-1">
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-calendar'></i></span>
                                        </div>
                                    <input type='text' name='date' id='date' class='form-control'
                                        placeholder="Date"
                                        value="{{ auth()->user()->can('View Running Shares')? date('Y-m-d'): '' }}" />
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Invoice</th>
                                            <th>Amount</th>
                                            <th>Balance</th>
                                            <th>Bought</th>
                                            <th>Maturity</th>
                                            <th>Status</th>
                                            <!--<th class='text-end'>Action</th>-->
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <td><b>Totals:</b></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <!--<td></td>-->
                                        </tr>
                                    </tfoot>
                                </table>
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
            flatpickr("#date", {
                enableTime: false,
                dateFormat: "Y-m-d",
            });
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Running shares available",
                },
                ajax: {
                    url: "{{ url('dashboard/shares/datatable/running') }}",
                    data: function(d) {
                        d.search = $('.search-form input[name=search]').val();
                        d.date = $('.search-form input[name=date]').val();
                        d.timezone = timeZone;
                    }
                },

                dom: 'lBtrip',
                columns: [
                    /*{
                                            data: 'DT_RowIndex',
                                            name: 'DT_RowIndex',
                                            orderable: false,
                                            searchable: false
                                        },*/
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }, {
                        data: 'buyer.username',
                        name: 'buyer.username'
                    }, {
                        data: 'invoice',
                        name: 'invoice'
                    }, {
                        data: 'amount',
                        name: 'amount'
                    }, {
                        data: 'balance',
                        name: 'balance'
                    }, {
                        data: 'bought',
                        name: 'bought'
                    }, {
                        data: 'days',
                        name: 'days'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            switch (data) {
                                case "Pending":
                                    return '<span class="badge bg-secondary">Pending</span>';
                                case "Completed":
                                    return '<span class="badge bg-success">Completed</span>';
                                case "Activated":
                                    return '<span class="badge bg-primary">Activated</span>';
                                case "Accepted":
                                    return '<span class="badge bg-info">Accepted</span>';
                                case "Disputed":
                                    return '<span class="badge bg-warning">Disputed</span>';
                                default:
                                    return '<span class="badge bg-danger">Blocked</span>';
                            }
                        }
                    },
                    /*
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },*/
                ],
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api(),
                        data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            (i.replace(/(<([^>]+)>)/ig, '')).replace(/[\$,a-zA-Z]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };

                    // Total over all pages
                    amountTotal = api
                        .column(3)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    balanceTotal = api
                        .column(4)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    bought = api
                        .column(5)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(3).footer()).html(
                        '<span class="no-wrap"><i class="fas fa-rupee-sign"></i> ' + amountTotal
                        .toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + "</span>" /*total.toFixed(2)*/
                    );
                    $(api.column(4).footer()).html(
                        '<span class="no-wrap text-primary"><i class="fas fa-rupee-sign"></i> ' +
                        balanceTotal.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + "</span>" /*total.toFixed(2)*/
                    );
                    $(api.column(5).footer()).html(
                        '<span class="no-wrap text-success"><i class="fas fa-rupee-sign"></i> ' + bought
                        .toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + "</span>" /*total.toFixed(2)*/
                    );
                },
                "drawCallback": function(settings, json) {
                    $('.countdown').each(function(index, element) {
                        //var start = moment.utc($(element).data('start')).local();
                        var start = moment($(element).data('start'));
                        var end = moment($(element).data('end'));

                        setInterval(function() {
                            var now = moment();
                            var duration = moment.duration(end.diff(now));
                            var days = Math.floor(duration.asDays());
                            var hours = duration.hours();
                            var minutes = duration.minutes();
                            var seconds = duration.seconds();
                            if (end.diff(now) > 0) {
                                $(element).html((days).toString().padStart(2, "0") +
                                    'd:' + (hours).toString().padStart(2, "0") +
                                    'h:' + (minutes).toString().padStart(2, "0") +
                                    'm:' + (seconds).toString().padStart(2, "0") +
                                    's');
                            } else {
                                $(element).html(
                                    "<span class='text-success'><i class='fas fa-check-circle'></i> Matured</span>"
                                );
                            }
                        }, 1000);
                    });
                }
            });
            var timer = null;

            $('#search-form input[type=text]').keyup('submit', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    table.draw();
                }, 1000);
            });
            $('#search-form select, #date').change(function() {
                table.draw();
            });

        });
    </script>
@endpush
