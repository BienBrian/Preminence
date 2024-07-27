@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-leaf'></i> <b>Matured</b> Shares</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">

                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Matured Shares</li>
                    </ol>

                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <div class="col-md-12 mb-3">
                <!-- Small boxes (Stat box) -->
                <div class="row">
                    <div class='col-6 p-2'>
                        <div class='card border h-100'>
                            <div class='card-body p-3'>
                                <div class='row'>
                                    <div class='col-8'>
                                        <p class="text-sm mb-0 text-capitalize font-weight-bold text-muted">Pending Shares
                                        </p>
                                        <h5 class="font-weight-bolder mb-0">
                                            <i class='fas fa-rupee-sign'></i><span class='matured'>loading...</span>
                                            <!--<span class="text-success text-sm font-weight-bolder">+55%</span>-->
                                        </h5>
                                    </div>
                                    <div class='col-4 d-flex justify-content-end'>
                                        <div
                                            class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md d-flex align-items-center justify-content-center">
                                            <i class="fas fa-check-circle text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='col-6 p-2'>
                        <div class='card border h-100'>
                            <div class='card-body p-3'>
                                <div class='row'>
                                    <div class='col-8'>
                                        <p class="text-sm mb-0 text-capitalize font-weight-bold text-muted">Selling Shares
                                        </p>
                                        <h5 class="font-weight-bolder mb-0">
                                            <i class='fas fa-rupee-sign'></i><span class='selling'>loading...</span>
                                            <!--<span class="text-success text-sm font-weight-bolder">+55%</span>-->
                                        </h5>
                                    </div>
                                    <div class='col-4 d-flex justify-content-end'>
                                        <div
                                            class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md d-flex align-items-center justify-content-center">
                                            <i class="fas fa-wallet text-lg opacity-10" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- small box -->
                    <div class="card">
                        <div class="card-header">
                            <form class='row' id='search-form'>
                                <div class="col-sm-6 mb-1">
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-calendar'></i></span>
                                        </div>
                                        <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-circle'></i></span>
                                        </div>
                                        <select name="status" class="form-control">
                                            <option value='All'>All</option>
                                            <option value='Pending'>Pending</option>
                                            <option value='Activated'>Activated</option>
                                        </select>
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
                                            <th>Buyer</th>
                                            <th>Seller</th>
                                            <th>Invoice No.</th>
                                            <th>Amount</th>
                                            <th>Balance</th>
                                            <th>Bought</th>
                                            <th>Selling</th>
                                            <th>Status</th>
                                            <th>Maturity</th>
                                            <th class='text-end'>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan='4'><b>Totals</b></td>
                                            <td class='no-wrap'></td>
                                            <td class='no-wrap'></td>
                                            <td class='no-wrap'></td>
                                            <td class='no-wrap'></td>
                                            <td></td>
                                            <td></td>
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

    <!-- Edit Share Modal -->
    <div class="modal fade" id="routeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-hand-holding-usd'></i> <span
                            class='text-header'>Edit </span>
                        Shares</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('dashboard/shares/active/edit') }}" class="row"
                        enctype="multipart/form-data">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class="col-sm-6 form-group">
                            <label>Buyer</label>
                            <input type='text' name='buyer' class='form-control' placeholder="Buyer" readonly>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Seller</label>
                            <input type='text' name='seller' class='form-control' placeholder="Seller" readonly>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Amount</label>
                            <input type='text' name='amount' class='form-control' placeholder="Amount" readonly>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Bought</label>
                            <input type='text' name='bought' class='form-control' placeholder="Bought" readonly>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Selling</label>
                            <input type='numeric' name='selling' class='form-control' placeholder="Selling">
                        </div>

                        <div class="col-sm-6 form-group">
                            <label>Status</label>
                            <select name='status' class='form-control mb-1'>
                                <option value='Pending'>Pending</option>
                                <option value='Activated'>Activated</option>
                            </select>
                        </div>
                        <div class='alert feedback border d-none'>
                            <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                            class='fas fa-times'></i> Close
                    </button>
                    <button type="button" class="btn btn-primary btn-sm btnSave"><i class='fas fa-paper-plane'></i> Save
                        changes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            //$('select[name=status]').select2({width: '100%'});
            flatpickr("#date", {
                enableTime: false,
                dateFormat: "Y-m-d",
            });
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            $('.btn-filter').click(function() {
                $('.filter-div').toggleClass('d-none');
            });
            var table = $('.table').DataTable({
                scrollX: true,
                fixedColumns: {
                    //left: 2,
                    right: 1,
                    left: 0
                },
                processing: true,
                serverSide: true,
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Matured shares available",
                },
                ajax: {
                    url: "{{ url('dashboard/shares/datatable/matured') }}",
                    data: function(d) {
                        d.search = $('#search-form input[name=search]').val();
                        d.status = $('#search-form select[name=status]').val();
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
                        data: 'seller.username',
                        name: 'seller.username'
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
                        data: 'selling',
                        name: 'selling'
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
                    clientTotal = api
                        .column(4)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    invoiceTotal = api
                        .column(5)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    total = api
                        .column(6)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    selling = api
                        .column(7)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(4).footer()).html(
                        '<span class=""><i class="fas fa-rupee-sign"></i> ' + clientTotal
                        .toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + "</span>" /*total.toFixed(2)*/
                    );
                    $(api.column(5).footer()).html(
                        '<span class="text-primary"><i class="fas fa-rupee-sign"></i> ' +
                        invoiceTotal.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + "</span>" /*total.toFixed(2)*/
                    );
                    $(api.column(6).footer()).html(
                        '<span class="text-success"><i class="fas fa-rupee-sign"></i> ' + total
                        .toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + "</span>" /*total.toFixed(2)*/
                    );
                    $(api.column(7).footer()).html(
                        '<span class="text-info"><i class="fas fa-rupee-sign"></i> ' + selling
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
                    getCard();
                    table.draw();
                }, 1000);
            });
            $('#search-form select, #date').change(function() {
                getCard();
                table.draw();
            });
            $('#routeModal .btnSave').click(function() {
                var btn = $(this);
                btn.attr('disabled', 'disabled');
                $('#routeModal .feedback').removeClass('d-none');
                $('#routeModal .feedback').removeClass('alert-danger');
                $('#routeModal .feedback').removeClass('alert-success');
                $('#routeModal .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $('#routeModal form').serialize();

                //var formData = new FormData($('#routeModal form')[0]);
                $.ajax({
                    url: '{{ url('dashboard/shares/active/edit') }}',
                    type: 'POST',
                    data: formData,
                    /*contentType: false,
                    processData: false,*/
                }).done(function(data) {
                    $('#routeModal .feedback').addClass('alert-success');
                    $('#routeModal .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    table.draw();
                    getCard();
                    setTimeout(() => {
                        $('#routeModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#routeModal .feedback').addClass('alert-danger');
                    $('#routeModal .feedback').html("");
                    if (data.errors) {
                        if (data.errors.id) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .id + "<br>");
                        }
                        if (data.errors.buyer) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .buyer + "<br>");
                        }
                        if (data.errors.seller) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .seller + "<br>");
                        }
                        if (data.errors.amount) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .amount + "<br>");
                        }
                        if (data.errors.bought) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .bought + "<br>");
                        }
                        if (data.errors.selling) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .selling + "<br>");
                        }
                        if (data.errors.status) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .status + "<br>");
                        }
                    } else if (data.error) {
                        $('#routeModal .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> " + data.error);
                    } else {
                        $('#routeModal .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> <b>Whoops</b> Something went wrong with the server!"
                        );
                    }
                    setTimeout(() => {
                        $('#routeModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                });
            });
            $(document).on('click', '.table .btn-edit', function() {
                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var buyer = row.find('.buyer').text();
                var seller = row.find('.seller').text();
                var balance = row.find('.balance').text();
                var bought = row.find('.bought_amount').text();
                var selling = row.find('.selling_amount').text();
                var status = row.find('.status').text();

                $('#routeModal input[name=id]').val(id);
                $('#routeModal input[name=buyer]').val(buyer);
                $('#routeModal input[name=seller]').val(seller);
                $('#routeModal input[name=amount]').val(balance);
                $('#routeModal input[name=bought]').val(bought);
                $('#routeModal input[name=selling]').val(selling);
                $('#routeModal select[name=status]').val(status);
            });

            getCard();

            function getCard() {
                var search = $('#search-form input[name=search]').val();
                var status = $('#search-form select[name=status]').val();
                $('.matured').html("<i class='fas fa-spinner fa-pulse'></i> Loading...</span>");
                $('.selling').html("<i class='fas fa-spinner fa-pulse'></i> Loading...</span>");

                $.ajax({
                    url: "{{ url('dashboard/shares/matured/card') }}",
                    type: "GET",
                    data: {
                        timezone: timeZone,
                        search: search,
                        status: status
                    },
                }).done(function(data) {
                    $('.matured').text(data
                        .shares.matured
                        .toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    $('.selling').text(data
                        .shares.selling
                        .toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                });
            }

        });
    </script>
@endpush
