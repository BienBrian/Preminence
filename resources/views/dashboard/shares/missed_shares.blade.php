@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-business-time'></i> <b>Missed</b> Shares</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Missed Shares</li>
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
                        <div class="card-body">
                            <form class='search-form row d-flex align-items-end' id='search-form'>
                                <div class="col-sm-6">
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-search'></i></span>
                                        </div>
                                    <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-calendar'></i></span>
                                        </div>
                                    <input type="text" class="form-control mb-1" name="date" id='date'
                                        placeholder="Search Date" value='{{ date('Y-m-d') }}'>
                                    </div>
                                </div>
                            </form>


                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <!--<th>#</th>-->
                                            <th>User</th>
                                            <th>Missed Amount</th>
                                            <th>Bought Amount</th>
                                            <th>Date</th>
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

            flatpickr("#date", {
                enableTime: false,
                dateFormat: "Y-m-d",
                //defaultDate: new Date(),
            });
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Missed shares available",
                },
                ajax: {
                    url: "{{ url('dashboard/shares/datatable/missed') }}",
                    data: function(d) {
                        d.search = $('.search-form input[name=search]').val();
                        d.date = $('.search-form input[name=date]').val();
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
                        data: 'user.username',
                        name: 'user.username'
                    },
                    {
                        data: 'missed_amount',
                        name: 'missed_amount'
                    },
                    {
                        data: 'bought_amount',
                        name: 'bought_amount'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
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
            $('#search-form input[name=date]').change(function() {
                table.draw();
            });
            $('#search-form select[name=active]').change(function() {
                table.draw();
            });
            $('.btn-launch-modal').click(function() {
                $('#routeModal .modal-title span').text("New ");
                $('#routeModal input[name=id]').val(0);
                $('#routeModal input[name=name]').val("");
                $('#routeModal select[name=status]').val("Pending");
                $('#routeModal select[name=active]').val(1);
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
                    url: '{{ url('queues/status/add') }}',
                    type: 'POST',
                    data: formData
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
                        if (data.errors.name) {
                            $('#routeModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .name + "<br>");
                        }
                        if (data.errors.status) {
                            $('#routeModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .status + "<br>");
                        }
                        if (data.errors.active) {
                            $('#routeModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .active + "<br>");
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
                $('#routeModal .modal-title span').text("Edit ");
                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var name = row.find('.name').text();
                var place = row.find('.place').text();
                var place_id = row.find('.place_id').text();
                var status = row.find('.status').text();

                $('#routeModal input[name=id]').val(id);
                $('#routeModal input[name=name]').val(name);

                var data = {
                    id: place_id,
                    text: place
                };
                var newOption = new Option(data.text, data.id, false, false);
                $('#place').append(newOption).trigger('change');
                $('#routeModal select[name=status]').val(status);
            });

        });
    </script>
@endpush
