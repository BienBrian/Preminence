@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-children'></i> Children</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Children</li>
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

                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                            <div class="col">
                                <h3 class="mb-0">Children</h3>
                            </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#importChildrenModal">
                                        <span class="d-none d-md-block"><i class='fas fa-cloud'></i> Import</span>
                                    </button>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#childModal">
                                        <span class="d-none d-md-block"><i class='fas fa-plus-circle'></i> Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class='row mt-1 mb-1'>
                            <div class='col-2 col-sm-6 col-md-8 col-lg-9'>
                            </div>
                            <div class='col-8 col-sm-6 col-md-4 col-lg-3'>
                                <form class='search-form pl-2 pr-2'>
                                    <input type='search' class='form-control' placeholder="Search" name='search' />
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Parent</th>
                                        <th scope="col">Gender</th>
                                        <!--<th scope="col">DOB</th>
                                    <th scope="col">AGE</th>
                                    <th scope="col">Residence</th>-->
                                        <th scope="col">Sunday School Class</th>
                                        <th scope="col">Action</th>
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


    <!-- Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-calendar-alt'></i> Add Child to Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/children/save/attendance') }}" method="post">
                        @csrf
                        <div class='form-group'>
                            <label>Event Name:</label>
                            <select class='form-control' name='event' id='events'></select>
                            <input type="hidden" name="id" required value='0'>
                        </div>
                        <div class='form-group'>
                            <label>Child Name:</label>
                                <input type="text" class="form-control" name="child" placeholder='Child Name'
                                    required>
                        </div>
                        <div class='form-group'>
                            <label>Guardian Name:</label>
                                <input type="text" class="form-control" name="guardian" placeholder='Guardian Name'
                                    required>
                        </div>
                    </form>
                </div>
                <div class='modal-footer pt-0 pb-0'>
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class='fas fa-cloud'></i> Add to Event
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="importChildrenModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-cloud-upload"></i> Children</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/children/upload') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class='form-group'>
                            <label>Excel File:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i
                                            class='fas fa-cloud text-primary'></i></span>
                                </div>
                                <input type="file" class="form-control" name="import_file"
                                    placeholder='Upload excel file' required>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-submit-funds">Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="childModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-child'></i> Add Child</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/children/save') }}" method="post" class='row'>
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class='form-group col-sm-6'>
                            <label>First Name:</label>
                            <input type="text" class="form-control" name="firstname" placeholder='First Name'>
                        </div>
                        <div class='form-group col-sm-6'>
                            <label>Last Name:</label>
                            <input type="text" class="form-control" name="lastname" placeholder='Last Name'>
                        </div>
                        <div class='form-group col-sm-6'>
                            <label>Surname:</label>
                            <input type="text" class="form-control" name="surname" placeholder='Surname'>
                        </div>
                        <div class='form-group'>
                            <label>Gender:</label>
                            <select class="custom-select" name="gender">
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                                <option value="TRANSGENDER">Transgender</option>
                                <option value="RATHER NOT SAY">Rather Not Say</option>
                            </select>
                        </div>
                        <div class='form-group col-sm-6'>
                            <label>DOB:</label>
                            <input type="date" class="form-control" name="dob" placeholder='Enter DOB' required>
                        </div>
                        <div class='form-group col-sm-6'>
                            <label>Residence:</label>
                            <input type="text" class="form-control" name="residence" placeholder='Residence'
                                required>
                        </div>
                        <div class='form-group col-sm-12'>
                            <label>Sunday School Class:</label>
                            <input type="text" class="form-control" name="sunday_school_class"
                                placeholder='Sunday School Class' required>
                        </div>
                    </form>
                </div>
                <div class='modal-footer pt-0'>
                    <button type="submit" class="btn btn-primary btn-submit">Add Child</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('#events').select2({
                width: '100%',
                placeholder: 'Select Event',
                allowClear: true,
                dropdownParent: $('#addEventModal'),
                ajax: {
                    url: '{{ url('dashboard/children/events/search') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                }
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
                    url: "{{ url('dashboard/datatables/children') }}",
                    data: function(d) {
                        d.search = $('input[name=search').val();
                    }
                },
                columns: [{
                        data: 'name',
                        name: "name",
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
                        data: 'gender',
                        name: "gender",
                        orderable: false,
                        searchable: false
                    },
                    /*{data: 'dob', name: "dob", orderable: false, searchable: false},
                    {data: 'age', name: "age", orderable: false, searchable: false},
                    {data: 'residence', name: "residence", orderable: false, searchable: false},*/
                    {
                        data: 'sunday_school_class',
                        name: "sunday_school_class",
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
            var timer = null;
            $('input[name=search]').keyup(function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    table.draw();
                }, 1000);
            });
            $(document).on('click', '#addEventModal .auto-search a', function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                var name = $(this).find('strong').text();
                $('#addEventModal input[name=event_id]').val(id);
                $('#addEventModal input[name=event]').val(name);
                $('.auto-search').hide();
            });
            $(document).on('click', '.btnEdit', function() {
                var id = $(this).closest('td').find('.id').text();
                var parent = $(this).closest('td').find('.parent').text();
                var child = $(this).closest('td').find('.child').text();
                $('#addEventModal input[name=id]').val(id);
                $('#addEventModal input[name=event_id]').val(0);
                $('#addEventModal input[name=event]').val('');
                $('#addEventModal input[name=child]').val(child);
                $('#addEventModal input[name=guardian]').val(parent);
                $('.auto-search').hide();
            });
            $('#addEventModal .btn-submit').click(function() {
                $('#addEventModal form').submit();
            });
            $('.btnLaunchModal').click(function() {
                $('#eventsModal').modal();
            });
            $('#childModal .btn-submit').click(function() {
                $('#childModal form').submit();
            });
        });
    </script>
@endpush
