@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-mail-bulk'></i> Emails</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Emails</li>
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
                    <div class="card">
                        <div class='card-header text-right'>

                            @can('Add Emails')
                                <a href='{{ url('dashboard/emails/send') }}' class='btn btn-primary btn-sm'><i
                                        class='far fa-envelope'></i> Send Email</a>
                            @endcan
                        </div>
                        <div class="card-header">
                            <form id='search-form' class='row mb-2'>
                                <div class='col-sm-6 mb-2'>
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-search'></i></span>
                                        </div>
                                    <input name='search' class='form-control' placeholder="Search" />
                                    </div>
                                </div>
                                <div class='col-sm-6 mb-2'>
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-calendar'></i></span>
                                        </div>
                                    <input name='date' id='date' class='form-control' placeholder="Date" />
                                    </div>
                                </div>
                            </form>
                        </div>
                            <div class="card-body">
                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Username</th>
                                            <th>Subject</th>
                                            <th>Recipients</th>
                                            <th>Date</th>
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
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            $('.btn-filter').click(function(){
                $('.filter-div').toggleClass('d-none');
            });
            flatpickr("#date", {
                enableTime: false,
                dateFormat: "Y-m-d",
                defaultDate: new Date(),
            });

            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/datatable/emails') }}",
                    data: function(d) {
                        d.search = $('#search-form input[name=search]').val();
                        d.date = $('#search-form input[name=date]').val();
                        d.timezone = timeZone;
                    }
                },
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Emails available",
                },
                dom: 'lBtrip', //'lfBtrip'
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'user.username',
                        name: 'user.username',
                        //defaultContent: 'N/A'
                    },
                    {
                        data: 'subject',
                        name: 'subject',
                    },
                    {
                        data: 'recipients',
                        name: 'recipients',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
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
            $('#search-sacco, #date').change(function() {
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
                $('#sacco').val(null).trigger('change');
                $('#role').val(null).trigger('change');
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
                    url: '{{ url('settings/points/add') }}',
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
                        if (data.errors.sacco) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .sacco + "<br>");
                        }
                        if (data.errors.value) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .value + "<br>");
                        }

                        if (data.errors.points_by) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .points_by + "<br>");
                        }

                        if (data.errors.points_on) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .points_on + "<br>");
                        }
                        if (data.errors.role) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .role + "<br>");
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
                var sacco = row.find('.sacco').text();
                var sacco_id = row.find('.sacco_id').text();
                var role = row.find('.role').text();
                var role_id = row.find('.role_id').text();
                var points_on = row.find('.points_on').text();
                var points_type = row.find('.points_type').text();
                var amount = row.find('.amount').text();
                var items = row.find('.items').text();
                //var payment_mode = row.find('.payment_mode').text();
                var status = row.find('.status').text();

                $('#userModal input[name=id]').val(id);
                if (sacco_id > 0) {
                    var data = {
                        id: sacco_id,
                        text: sacco
                    };
                    var newOption = new Option(data.text, data.id, false, false);
                    $('#sacco').append(newOption).trigger('change');
                }
                if (role_id > 0) {
                    var data = {
                        id: role_id,
                        text: role
                    };
                    var newOption = new Option(data.text, data.id, false, false);
                    $('#role').append(newOption).trigger('change');
                }
                $('#userModal input[name=value]').val(amount > 0 ? amount : items);
                $('#userModal select[name=points_by]').val(points_type);
                $('#userModal select[name=points_on]').val(points_on);
                $('#userModal select[name=status]').val(status);
            });
        });
    </script>
@endpush
