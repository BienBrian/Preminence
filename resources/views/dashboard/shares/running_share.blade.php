@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-pie'></i> View <b>Running</b> Share</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    @can('Add Queue Statuses')
                        <button class="btn btn-primary btn-sm btn-launch-modal" data-toggle="modal" data-target="#routeModal"><i
                                class='fas fa-plus'></i> Add Status
                        </button>
                    @else
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">View Running Share</li>
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
                    <!--<div class="card">
                        <div class="card-body">
                            <div class='search-form row d-flex align-items-end'>
                                <div class="col-6 col-sm-3 col-md-3">
                                    <label>Share Amount</label><br>
                                    <i class="fas fa-rupee-sign"></i> {{ number_format($share->amount,2,'.',',') }}
                                </div>
                                <div class="col-6 col-sm-3 col-md-3">
                                    <label>Balance Amount</label><br>
                                    <i class="fas fa-rupee-sign"></i> {{ number_format($share->balance,2,'.',',') }}
                                </div>
                                <div class="col-6 col-sm-3 col-md-2">
                                    <label>Status</label><br>
                                    {{ $share->status}}
                                </div>
                                <div class="col-6 col-sm-3 col-md-2">
                                    <label>Maturity</label><br>
                                    {{ $share->maturity->number_of_days }} Day(s)
                                </div>
                                <div class="col-6 col-sm-3 col-md-2">
                                    <label>Created</label><br>
                                    <span class='created_at'>{{ \Carbon\Carbon::parse($share->created_at)->diffForHumans() }}</span>
                                </div>
                            </div>

                        </div>
                    </div>-->


                    <!-- small box -->
                    <div class="card">
                        <div class='card-header'>
                            <h6><i class='fas fa-history'></i> <b>Share</b> Transaction Histories</h6>
                        </div>
                        <div class="card-body">
                            <!--<form class='search-form row d-flex align-items-end' id='search-form'>
                                <div class="col-sm-4">
                                    <label>Search Name</label>
                                    <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                                </div>
                                <div class="col-sm-4">
                                    <label>Status</label>
                                    <select name="status" class="form-control mb-1">
                                        <option value=''>All</option>
                                        <option value='Pending'>Pending</option>
                                        <option value='Active'>Active</option>
                                        <option value='Suspended'>Suspended</option>
                                        <option value='Cancelled'>Cancelled</option>
                                        <option value='Completed'>Completed</option>
                                    </select>
                                </div>
                                <div class="col-sm-4">
                                    <label>Active</label>
                                    <select name="active" class="form-control mb-1">
                                        <option value='1'>Yes</option>
                                        <option value='0'>No</option>
                                    </select>
                                </div>
                            </form>-->


                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <!--<th>#</th>-->
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>User</th>
                                            <th>Status</th>
                                            <th class='text-right notexport'>Action</th>
                                        </tr>
                                    </thead>
                                </table>
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

    <!-- Profile Modal -->
    <div class="modal fade" id="routeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-money-check'></i> <span class='text-header'>Confirm </span>
                        Payments</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('dashboard/shares/bought/confirm') }}" class="row" enctype="multipart/form-data">
                        @csrf
                        <input type='hidden' name='id' value='{{ $share->id }}'>
                        <input type='hidden' name='user_id' value='0'>
                        <input type='hidden' name='share_transaction_id' value='0'>
                        <div class="col-sm-12 form-group">
                            <label>Status</label>
                            <select name="status" class="form-control mb-1" id='status'>
                                <option value='Confirmed'>Confirmed</option>
                                <option value='Disputed'>Disputed</option>
                            </select>
                        </div>
                        <div class='col-sm-12 img-div form-group'>
                            <label>Payments Screenshot</label>
                            <img src='{{ asset('images/bg2.jpg') }}' class='img-fluid img-thumbnail'/>
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
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/shares/running/view/datatable/auctions/'.$share->id) }}",
                    data: function(d) {
                        d.search = $('.search-form input[name=search]').val();
                        d.status = $('.search-form select[name=status]').val();
                        d.active = $('.search-form select[name=active]').val();
                        d.timezone = timeZone;
                    }
                },

                dom: 'lBtrip',
                columns: [/*{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },*/
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'user.username',
                        name: 'user.username'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            switch (data) {
                                case "Pending":
                                    return '<span class="badge bg-secondary">Pending</span>';
                                case "Completed":
                                    return '<span class="badge bg-info">Completed</span>';
                                case "Confirmed":
                                    return '<span class="badge bg-success">Confirmed</span>';
                                case "Activated":
                                    return '<span class="badge bg-primary">Activated</span>';
                                case "Accepted":
                                    return '<span class="badge bg-info">Accepted</span>';
                                case "Disputed":
                                    return '<span class="badge bg-warning">Disputed</span>';
                                default:
                                    return '<span class="badge bg-danger">Rejected</span>';
                            }
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            var timer = null;

            $('#search-form input[type=text]').keyup('submit', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    table.draw();
                }, 1000);
            });
            $('#search-form select[name=status]').change(function() {
                table.draw();
            });
            $('#search-form select[name=active]').change(function() {
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
                $.ajax({
                    url: '{{ url('dashboard/shares/bought/confirm') }}',
                    type: 'POST',
                    data: formData,
                }).done(function(data) {
                    $('#routeModal .feedback').addClass('alert-success');
                    $('#routeModal .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    table.draw();
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
                        if (data.errors.user_id) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .user_id + "<br>");
                        }
                        if (data.errors.buyer_id) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .buyer_id + "<br>");
                        }
                        if (data.errors.screenshot) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .screenshot + "<br>");
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
            $('#status').select2({
                width: '100%',
                placeholder: 'Select Maturities',
                dropdownParent: $('#routeModal'),
                //allowClear: true,
            });

            $(document).on('click', '.table .btn-edit', function() {
                var row = $(this).closest('tr');
                var user_id = row.find('.user_id').text();
                var share_transaction_id = row.find('.share_transaction_id').text();
                var url = row.find('.url').text();
                $('.img-div img').attr('src',url);
                $('#routeModal input[name=user_id]').val(user_id);
                $('#routeModal input[name=share_transaction_id]').val(share_transaction_id);
            });

        });
    </script>
@endpush
