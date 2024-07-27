@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-calendar-alt'></i> {{ $event->name }}</h5>
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
                <div class='col-sm-12'>
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-auto">
                                    <div
                                        class="icon icon-shape bg-warning text-white rounded-circle shadow d-flex align-items-center justify-content-center">
                                        <i class="fas fa-child"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">{{ $event->name }}</h5><br>
                                    <span class="h2 font-weight-bold mb-0">{{ number_format($children, 0) }}</span>
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted text-sm">
                                <span class="text-nowrap">
                                    <span class='text-info'><i class='fas fa-calendar-alt'></i>
                                        <strong>{{ \Carbon\Carbon::parse($event->from_date)->format('D d M, Y h:i A') }}</strong>
                                        to
                                        <strong>{{ \Carbon\Carbon::parse($event->to_date)->format('D d M, Y h:i A') }}</strong>
                                    </span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12">
                    <div class='card shadow mt-4'>
                        <div class='card-header border-0'>
                            <div class="row">
                                <div class="col">
                                    <strong><i class='fas fa-child'></i> Children Attendance</strong>
                                </div>
                                <div class="col text-right">
                                    <!--<button class='btn btn-primary btn-sm btnLaunchModal'>Add Attendance</button>-->
                                </div>
                            </div>
                        </div>
                        <div class='table-responsive'>
                            <table class='table'>
                                <thead>
                                    <tr>
                                        <th>Child</th>
                                        <th>Guardian</th>
                                        <th>Phone</th>
                                        <th>Checkin</th>
                                        <th>Checkout</th>
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
                    <form action="{{ url('dashboard/children/save/attendance') }}" method="POST" class="row">
                        @csrf
                        <div class="col-sm-6 form-group">
                            <label>Guardian First Name</label>
                            <input name="guardianfirstname" class='form-control' placeholder='Guardian First Name' required>
                            <input type='hidden' name="id" class='form-control' value="{{ $event->id }}">
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Guardian Last Name</label>
                            <input name="guardianlastname" class='form-control' placeholder='Guardian Last Name' required>
                        </div>
                        <div class=" col-sm-12 form-group">
                            <label>Guardian Phone</label>
                            <div class='input-group'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>+254</span>
                                </div>
                                <input name="guardianphone" class='form-control' placeholder='Guardian Phone' required>
                            </div>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>First Name</label>
                            <input name="firstname" class='form-control' placeholder='First Name'>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Last Name</label>
                            <input name="lastname" class='form-control' placeholder='Last Name'>
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
                    url: "{{ url('dashboard/children/datatable/attendance/' . $event->id) }}",
                    data: function(d) {
                        /*d.group = groups;
                        d.time = time;*/
                    }
                },
                columns: [{
                        data: 'child',
                        name: "child",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'guardian',
                        name: "guardian",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'phone',
                        name: "phone",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'timein',
                        name: "checkin",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'timeout',
                        name: "checkout",
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
