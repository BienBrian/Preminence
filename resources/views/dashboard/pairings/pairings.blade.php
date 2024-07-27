@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-handshake'></i> <b>Share</b> Parings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">

                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Share Pairings</li>
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
                <div class='col-sm-4 p-2'>
                    <div class="card h-100 my-card text-white shadow-lg">
                        <div class='card-body'><span class='big'><i class="fas fa-rupee-sign"></i>&nbsp;<span
                                    class='running'><i class='fas fa-spinner fa-pulse'></i> Loading...</span></span><br>
                            <b>Running</b> Shares
                        </div>
                    </div>
                </div>
                <div class='col-sm-4 p-2'>
                    <div class="card h-100 my-card text-white shadow-lg">
                        <div class='card-body'><span class='big'><i class="fas fa-rupee-sign"></i>&nbsp;<span
                                    class='bought'><i class='fas fa-spinner fa-pulse'></i> Loading...</span></span><br>
                            <b>Bought</b> Shares
                        </div>
                    </div>
                </div>
                <div class='col-sm-4 p-2'>
                    <div class="card h-100 my-card text-white shadow-lg">
                        <div class='card-body'><span class='big'><i class="fas fa-rupee-sign"></i>&nbsp;<span
                                    class='sold'><i class='fas fa-spinner fa-pulse'></i> Loading...</span></span><br>
                            <b>Sold</b> Shares
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <!-- small box -->
                <div class="card">
                    <div class="card-header">
                        <form class='search-form row d-flex align-items-end' id='search-form'>
                            <div class="col">
                                <label>Search Name</label>
                                <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                            </div>
                            <div class="col">
                                <input type='text' name='date' id='date' class='form-control mb-1'
                                    placeholder="Date"
                                    value="{{ auth()->user()->can('View Running Shares')? date('Y-m-d'): '' }}" />
                            </div>
                        </form>
                    </div>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class='table w-100'>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Buyer</th>
                                        <th>Seller</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Maturity</th>
                                        <th class='text-end'>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan='3'><b>Totals</b></td>
                                        <td class='no-wrap'></td>
                                        <td class='no-wrap'></td>
                                        <td class='no-wrap'></td>
                                        <td></td>
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
                scrollX: true,
                fixedColumns: {
                    //left: 2,
                    right: 1,
                    left: 0
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/shares/datatable/pairings') }}",
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
                    },{
                        data: 'user.username',
                        name: 'user.username'
                    }, {
                        data: 'share.buyer.username',
                        name: 'share.buyer.username'
                    },  {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            switch (data) {
                                case "Pending":
                                    return '<span class="badge bg-secondary">Pending</span>';
                                case "Completed":
                                    return '<span class="badge bg-primary">Completed</span>';
                                case "Confirmed":
                                    return '<span class="badge bg-success">Confirmed</span>';
                                case "Accepted":
                                    return '<span class="badge bg-info">Accepted</span>';
                                case "Disputed":
                                    return '<span class="badge bg-warning">Disputed</span>';
                                default:
                                    return '<span class="badge bg-danger">Reversed</span>';
                            }
                        }
                    }, {
                        data: 'days',
                        name: 'days'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
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
                    totals = api
                        .column(3)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(3).footer()).html(
                        '<span class=""><i class="fas fa-rupee-sign"></i> ' + totals
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

            getStatistics();

            function getStatistics() {
                $.ajax({
                    url: "{{ url('dashboard/shares/active/statistics') }}",
                    type: "GET",
                    data: {
                        timezone: timeZone,
                    },
                }).done(function(data) {
                    $('.running').text(data.running_shares);
                    $('.bought').text(data.bought_shares);
                    $('.pending').text(data.pending_shares);
                    $('.next_auction').text(data.next_auction_shares);
                    bids = JSON.parse(data.bids);
                    $.each(bids, function(index, item) {
                        mydata[item.month - 1] = item.totals;
                    });
                    getLineChart(mydata);

                    if (data.auction_time != null) {

                        var date = new Date(Date.parse(data.auction_time.start_time)).toLocaleString(
                            'en', {
                                timeZone: data.auction_time.name
                            });

                        var closeDate = new Date(Date.parse(data.auction_time.end_time)).toLocaleString(
                            'en', {
                                timeZone: data.auction_time.name
                            });

                        var countDownDate = new Date(date).getTime();
                        var closeTimeDate = new Date(closeDate).getTime();
                        var myNow = new Date().getTime();

                        if (countDownDate <= myNow && closeTimeDate >= myNow) {
                            $('.time').addClass('d-none');
                            $('.auction_status').removeClass('d-none');
                            $('.auction_status').html(
                                "<a href='{{ url('dashboard/shares/buy') }}' class='btn btn-auction btn-sm mt-2'><i class='fas fa-coins'></i> Place a bid</a>"
                            );
                        } else {
                            $('.time').removeClass('d-none');
                            $('.auction_status').addClass('d-none');

                        }
                        var x = setInterval(function() {

                            // Get today's date and time
                            var now = new Date().getTime();

                            // Find the distance between now and the count down date
                            var distance = countDownDate - now;

                            // Time calculations for days, hours, minutes and seconds
                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 *
                                60));
                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            // Display the result in the element with id="demo"
                            $('.day').text((days).toString().padStart(2, "0") + "d");
                            $('.hr').text((hours).toString().padStart(2, "0") + "h");
                            $('.min').text((minutes).toString().padStart(2, "0") + "m");
                            $('.sec').text((seconds).toString().padStart(2, "0") + "s");

                            // If the count down is finished, write some text
                            if (distance < 0) {
                                clearInterval(x);
                                $('.day').text("--d");
                                $('.hr').text("--h");
                                $('.min').text("--m");
                                $('.sec').text("--s");
                            }
                        }, 1000);
                    } else {
                        $('.time').addClass('d-none');
                        $('.auction_status').removeClass('d-none');
                    }

                }).fail(function(data) {
                    console.log(data);
                });
            }

        });
    </script>
@endpush
