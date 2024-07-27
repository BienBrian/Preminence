@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-sort-numeric-up'></i> <b>Game Level</b> Settings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block p-2">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Game Level Settings</li>
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
                        <div class="card-header">
                            <table class='w-100'>
                                <tr>
                                    <td>
                                        <form id='search-form' class='row d-flex align-items-center'>
                                            <!--<div class='col mb-2'>
                                                                            <label>Search</label>
                                                                            <input name='search' class='form-control' placeholder="Search" />
                                                                        </div>-->
                                            <div class='col-sm-12 mb-2'>
                                                <input name='search' class='form-control'
                                                    placeholder="Search" />
                                            </div>
                                            <!--
                                                <div class='col-sm-4 mb-2'>
                                                        <input name='search-to' id='search-to' class='form-control'
                                                            placeholder="To Time" />
                                                </div>
                                                <div class='col-sm-4 mb-2'>
                                                        <select name="timezone" class='form-control border' id='search-timezones'>
                                                        </select>
                                                </div>-->
                                        </form>
                                    </td>
                                    <td class='text-end'>
                                        <button class="btn btn-primary btn-sm btn-launch-modal mb-2" data-toggle="modal"
                                            data-target="#userModal"><i class='fas fa-plus'></i> Add</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class='card-body'>
                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Win</th>
                                            <th>Lose</th>
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
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-plus'></i> <span>New</span> Game Level
                    </h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('dashboard/settings/game_levels/add') }}" class="row">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class='col-sm-6 form-group'>
                            <label>Level Name</label>
                            <input type='text' name="name" class='form-control' placeholder="Level Name" required />
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Win (%)</label>
                            <input type='number' min='0' name="win" class='form-control' placeholder="Win(%)" required/>
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Lose (%)</label>
                            <input type='number' min='0' name="lose" class='form-control' placeholder="Lose(%)" required/>
                        </div>
                        <div class='col-sm-6 form-group'>
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

            flatpickr("#search-date", {
                //altInput: true,
                //altFormat: "F j, Y",
                enableTime: false,
                dateFormat: "Y-m-d",
                //defaultDate: new Date(),
            });
            var table = $('.table').DataTable({
                scrollX: true,
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No <b>Game Levels</b> available"
                },
                fixedColumns: {
                    left: 0,
                    right: 1
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/settings/datatable/game_levels') }}",
                    data: function(d) {
                        d.search_from = $('#search-form input[name=search-from]').val();
                        d.search_to = $('#search-form input[name=search-to]').val();
                        d.timezone = $('#search-form select[name=timezone]').val();
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
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'win',
                        name: 'win',
                    },
                    {
                        data: 'lose',
                        name: 'lose',
                        //defaultContent: 'N/A'
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
            $('#search-from, #search-to, #search-timezones, #search-day, #search-date').change(function() {
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
                $('#userModal input[name=name]').val("");
                $('#userModal input[name=win]').val("");
                $('#userModal input[name=lose]').val("");
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
                    url: '{{ url('dashboard/settings/game_levels/add') }}',
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
                        if (data.errors.name) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .name + "<br>");
                        }
                        if (data.errors.win) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .win + "<br>");
                        }

                        if (data.errors.lose) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .lose + "<br>");
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
                var name = row.find('.name').text();
                var win = row.find('.win').text();
                var lose = row.find('.lose').text();
                var status = row.find('.status').text();
                $('#userModal input[name=id]').val(id);
                $('#userModal input[name=name]').val(name);
                $('#userModal input[name=win]').val(win);
                $('#userModal input[name=lose]').val(lose);
                $('#userModal select[name=status]').val(status);
            });
        });
    </script>
@endpush
