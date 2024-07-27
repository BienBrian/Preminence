@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-pie'></i> <b>Bought</b> Shares</h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">

                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Bought Shares</li>
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
                            <form class='search-form row d-flex align-items-end' id='search-form'>
                                <div class="col-6 mb-1">
                                    <div class='input-group border'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'><i class='fas fa-search'></i></span>
                                        </div>
                                    <input type="text" class="form-control" name="search" placeholder="Search">
                                </div>
                                </div>
                                <div class="col-6 mb-1"><div class='input-group border'>
                                    <div class='input-group-prepend'>
                                        <span class='input-group-text'><i class='fas fa-calendar'></i></span>
                                    </div>
                                    <input name='date' class='form-control' id='date' placeholder='Search Date'
                                        value='{{ auth()->user()->can('View Bought Shares') ? date('Y-m-d') : '' }}' />
                                    </div></div>
                            </form>
                        </div>
                        <div class='card-body'>

                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Duration</th>
                                            <th>Returns</th>
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
                scrollX: true,
                fixedColumns: {
                    //left: 2,
                    right: 1,
                    left: 0
                },
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Bought shares available",
                },
                ajax: {
                    url: "{{ url('dashboard/shares/datatable/bought') }}",
                    data: function(d) {
                        d.search = $('.search-form input[name=search]').val();
                        //d.status = $('.search-form select[name=status]').val();
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
                    },
                    {
                        data: 'user.username',
                        name: 'user.username'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'returns',
                        name: 'returns'
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
                                case "Activated":
                                    return '<span class="badge bg-primary">Activated</span>';
                                case "Confirmed":
                                    return '<span class="badge bg-success">Confirmed</span>';
                                case "Disputed":
                                    return '<span class="badge bg-warning">Disputed</span>';
                                default:
                                    return '<span class="badge bg-danger">Reversed</span>';
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
            $('#search-form select[name=status], #date').change(function() {
                table.draw();
            });
            $('#search-form select[name=active]').change(function() {
                table.draw();
            });
        });
    </script>
@endpush
