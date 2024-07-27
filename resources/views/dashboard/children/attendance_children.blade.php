@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-calendar-alt'></i> Children Checkin</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/children') }}">Children</a></li>
                        <li class="breadcrumb-item active">Checkin</li>
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
                <!-- Card stats -->
                <div class='col-sm-6 col-lg-4'>
                    <div class="card border border-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-warning text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-child"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="card-title text-uppercase text-muted mb-0">This week</h6><br>
                                    <span class="h2 font-weight-bold mb-0">{{ number_format($thisweek, 0) }}</span>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                @if ($thisweek >= $lastweek)
                                    <span class="text-nowrap">
                                        <span class='text-success font-weight-bold'><i class='fas fa-arrow-up'></i>
                                            {{ number_format($thisweek - $lastweek, 0) }}</span> From previous week
                                    </span>
                                @else
                                    <span class="text-nowrap">
                                        <span class='text-danger font-weight-bold'><i class='fas fa-arrow-down'></i>
                                            {{ number_format($lastweek - $thisweek, 0) }}</span> From previous week
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class='col-sm-6 col-lg-4'>
                    <div class="card border border-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-info text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-child"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="card-title text-uppercase text-muted mb-0">This month</h6><br>
                                    <span class="h2 font-weight-bold mb-0">{{ number_format($thismonth, 0) }}</span>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                @if ($thismonth >= $lastmonth)
                                    <span class="text-nowrap">
                                        <span class='text-success font-weight-bold'><i class='fas fa-arrow-up'></i>
                                            {{ number_format($thismonth - $lastmonth, 0) }}</span> From previous month
                                    </span>
                                @else
                                    <span class="text-nowrap">
                                        <span class='text-danger font-weight-bold'><i class='fas fa-arrow-down'></i>
                                            {{ number_format($lastmonth - $thismonth, 0) }}</span> From previous month
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class='col-sm-6 col-lg-4'>
                    <div class="card border border-primary">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-primary text-white rounded-circle shadow d-flex justify-content-center align-items-center">
                                        <i class="fas fa-child"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h6 class="card-title text-uppercase text-muted mb-0">This Year</h6><br>
                                    <span class="h2 font-weight-bold mb-0">{{ number_format($thisyear, 0) }}</span>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                @if ($thisyear >= $lastyear)
                                    <span class="text-nowrap">
                                        <span class='text-success font-weight-bold'><i class='fas fa-arrow-up'></i>
                                            {{ number_format($thisyear - $lastyear, 0) }}</span> From previous year
                                    </span>
                                @else
                                    <span class="text-nowrap">
                                        <span class='text-danger font-weight-bold'><i class='fas fa-arrow-down'></i>
                                            {{ number_format($lastyear - $thisyear, 0) }}</span> From previous week year
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class='card shadow mt-4'>
                        <div class='card-header border-0'>
                            <div class="row">
                                <div class="col">
                                    <strong><i class='fas fa-child'></i> Children Events</strong>
                                </div>
                                <div class="col text-right">
                                    <button class='btn btn-primary btn-sm btnLaunchModal'>Add Event</button>
                                </div>
                            </div>
                        </div>
                        <div class='table-responsive'>
                            <table class='table'>
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>In Attendance</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Modal -->
    <div class="modal fade" id="eventsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel">Add/Edit event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/children/save/checkin') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Name</label>
                            <input name="name" class='form-control' placeholder='Event Name'>
                            <input type='hidden' name="id" class='form-control' value="0">
                        </div>
                        <div class="form-group">
                            <label>From</label>
                            <input name="from" class='form-control mydatepicker' placeholder='From' autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>To</label>
                            <input name="to" class='form-control mydatepicker' placeholder='to' autocomplete="off">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-submit">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            flatpickr(".mydatepicker", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                //defaultDate: new Date(),
            });
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                oLanguage: {
                    sProcessing: "<i class='fas fa-spinner fa-pulse'></i> Processing..."
                },
                "language": {
                    "paginate": {
                        "previous": "<i class='fas fa-angle-left'></i>",
                        "next": "<i class='fas fa-angle-right'></i>"
                    }
                },
                dom: 'lBrtip',
                buttons: [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm text-white',
                    exportOptions: {
                        columns: ':not(.notexport)'
                    }
                }, {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-default btn-sm text-white',
                    exportOptions: {
                        columns: ':not(.notexport)'
                    }
                }],
                ajax: //"{{ url('children/datatable/events') }}",
                {
                    url: "{{ url('dashboard/children/datatable/events') }}",
                    data: function(d) {
                        /*d.group = groups;
                        d.time = time;*/
                    }
                },
                columns: [{
                        data: 'name',
                        name: "name"
                    },
                    {
                        data: 'from',
                        name: "from",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'to',
                        name: "to",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'children',
                        name: "children",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: "action",
                        orderable: false,
                        searchable: false
                    },
                    /*{data: 'attendance_type', name: "attendance_type", orderable: false, searchable: false},
                    {data: 'attendance_name', name:"name", orderable: false, searchable: false},
                    {data: 'attendance', name:"attendees", orderable: false, searchable: true},
                    {data: 'date', name: 'date', orderable:false, searchable:false},
                    {data: 'action', name: 'action', orderable:false, searchable:false},*/
                ]
            });

            $('.btnLaunchModal').click(function() {
                $('#eventsModal').modal();
            });
            $('.btn-submit').click(function() {
                $('#eventsModal form').submit();
            });
        });
    </script>
@endpush
