@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-clock'></i> <b>Timezones</b> Settings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block p-2">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Timezones Settings</li>
                    </ol>
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
                                <form id='search-form' class='row mb-2'>
                                    <div class='col-sm-12 mb-2'>
                                        <div class='input-group border'>
                                            <div class='input-group-prepend'>
                                                <span class='input-group-text'><i
                                                    class='fas fa-search'></i></span>
                                            </div>
                                        <input name='search' class='form-control' placeholder="Search" />
                                        </div>
                                    </div>
                                    <!--
                                        <div class='col-sm-6 mb-2'>
                                            <label>Date</label>
                                            <input name='date' id='date' class='form-control' placeholder="Date" />
                                        </div>-->
                                </form>
                            </div>
                            <div class='card-body'>
                                <div class="table-responsive">
                                    <table class='table w-100'>
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>name</th>
                                                <th>UTC Offset</th>
                                                <th>Status</th>
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
                /*flatpickr("#date", {
                    enableTime: false,
                    dateFormat: "d/m/Y",
                    defaultDate: new Date(),
                });*/

                var table = $('.table').DataTable({
                    processing: true,
                    serverSide: true,
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Timezones available",
                },
                    ajax: {
                        url: "{{ url('dashboard/settings/datatable/timezones') }}",
                        data: function(d) {
                            d.search = $('#search-form input[name=search]').val();
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
                            defaultContent: 'N/A'
                        },
                        {
                            data: 'utc_offset',
                            name: 'utc_offset',
                            defaultContent: 'N/A'
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
                            data: 'created_at',
                            name: 'created_at'
                        },
                    ]
                });
                var timer = null;

                $('#search-form input[name=search]').keyup(function() {
                    clearTimeout(timer);
                    timer = setTimeout(function() {
                        table.draw();
                    }, 1000)
                });
            });
        </script>
    @endpush
