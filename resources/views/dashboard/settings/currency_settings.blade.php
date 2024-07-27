@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-leaf'></i> Share <b>Maturities</b> Settings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block p-2">

                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Share Maturity Settings</li>
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
                <div class="col-md-12 mb-3">

                    <!-- small box -->
                    <div class="card shadow-none">
                        <div class='card-header text-right'><button class='btn btn-dark btn-sm btn-filter'><i class='fas fa-filter'></i>&nbsp; Filter &nbsp;<i class='fas fa-angle-down'></i></button>&nbsp;
                            <button class="btn btn-primary btn-sm btn-launch-modal" data-toggle="modal"
                                data-target="#userModal"><i class='fas fa-plus'></i> Add</button>
                        </div>
                        <div class="card-header d-none filter-div">
                            <form id='search-form' class='row mb-2'>
                                <div class='col-sm-6 mb-2'>
                                    <label>Search</label>
                                    <input name='search' class='form-control' placeholder="Search Days" />
                                </div>
                                <div class='col-sm-6 mb-2'>
                                    <label>Status</label>
                                    <select name='status' id='search_status' class='form-control'>
                                        <option value="-1">All</option>
                                        <option value="1">Active</option>
                                        <option value="0">In-Active</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class='card-body'>
                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Number of Bids</th>
                                            <th>Percentage</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class='text-right'>Action</th>
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
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-plus'></i> <span>New</span> Maturity
                    </h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('dashboard/settings/share_maturities/add') }}" class="row">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class='col-sm-12 form-group'>
                            <label>Number Of Bids</label>
                            <input type='number' name="number_of_bids" class='form-control' placeholder="Number of Bids"
                                required min='1' />
                        </div>
                        <div class='col-sm-12 form-group'>
                            <label>Percentage(%)</label>
                            <input type='number' name="percentage" class='form-control' placeholder="Percentage" required
                                min='1' />
                        </div>
                        <div class='col-sm-12 form-group'>
                            <label>Status</label>
                            <select name='status' class='form-control'>
                                <option value="1">Active</option>
                                <option value="0">In-Active</option>
                            </select>
                        </div>
                        <div class='alert feedback border d-none'>
                            <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                            class='fas fa-times'></i> Close</button>
                    <button type="button" class="btn btn-primary btn-sm btnSave"><i class='fas fa-paper-plane'></i> Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('.btn-filter').click(function(){
                $('.filter-div').toggleClass('d-none');
            });

            flatpickr("#auction_date", {
                //altInput: true,
                //altFormat: "F j, Y",
                enableTime: false,
                dateFormat: "Y-m-d",
                //defaultDate: new Date(),
            });
            $('#search_status').select2({
                width: '100%',
                placeholder: 'Select Status'
            });

            var table = $('.table').DataTable({
                scrollX: true,
                fixedColumns: {
                    left: 0,
                    right: 1
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/settings/datatable/share_maturities') }}",
                    data: function(d) {
                        d.search = $('#search-form input[name=search]').val();
                        d.status = $('#search-form select[name=status]').val();
                    }
                },
                dom: 'lBtrip', //'lfBtrip'
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'bids',
                        name: 'bids',
                    },
                    {
                        data: 'percentage',
                        name: 'percentage',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }, {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            switch (data) {
                                case 1:
                                    return '<span class="badge bg-primary">Active</span>';
                                default:
                                    return '<span class="badge bg-secondary">Inactive</span>';
                            }
                        }
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });
            var timer = null;
            $('#search_status').change(function() {
                table.draw();
            });

            $('#search-form input[name=search]').keyup(function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    table.draw();
                }, 1000)
            });
            $('.btn-launch-modal').click(function() {
                $('#userModal .modal-title span').text("New ");
                $('#userModal input[name=id]').val(0);
                $('#userModal input[name=number_of_bids]').val("");
                $('#userModal input[name=percentage]').val("");
                $('#userModal select[name=status]').val(1);
            });
            $('#userModal .btnSave').click(function() {
                var btn = $(this);
                btn.attr('disabled', 'disabled');
                $('#userModal .feedback').removeClass('d-none');
                $('#userModal .feedback').removeClass('alert-danger');
                $('#userModal .feedback').removeClass('alert-success');
                $('#userModal .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $('#userModal form').serialize();
                $.ajax({
                    url: '{{ url('dashboard/settings/share_maturities/add') }}',
                    type: 'POST',
                    data: formData
                }).done(function(data) {
                    $('#userModal .feedback').addClass('alert-success');
                    $('#userModal .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    table.draw();
                    setTimeout(() => {
                        $('#userModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#userModal .feedback').addClass('alert-danger');
                    $('#userModal .feedback').html("");
                    if (data.errors) {
                        if (data.errors.number_of_bids) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .number_of_bids + "<br>");
                        }
                        if (data.errors.percentage) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .percentage + "<br>");
                        }

                        if (data.errors.status) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .status + "<br>");
                        }

                    } else if (data.error) {
                        $('#userModal .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> " + data.error);
                    } else {
                        $('#userModal .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> <b>Whoops</b> Something went wrong with the server!"
                        );
                    }
                    setTimeout(() => {
                        $('#userModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                });
            });
            $(document).on('click', '.table .btn-edit', function() {
                $('#userModal .modal-title span').text("Edit ");
                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var bids = row.find('.bids').text();
                var percentage = row.find('.percentage').text();
                var status = row.find('.status').text();

                $('#userModal input[name=id]').val(id);
                $('#userModal input[name=number_of_bids]').val(bids);
                $('#userModal input[name=percentage]').val(percentage);
                $('#userModal select[name=status]').val(status);
            });
        });
    </script>
@endpush
