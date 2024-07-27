@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-children'></i> Child</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/children') }}">Children</a></li>
                        <li class="breadcrumb-item active">Child</li>
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
                    <div class="card mt-3 tab-card shadow">
                        <div class="card-header tab-card-header">
                            <div class='text-right'>
                                <button class='btn btn-primary btn-sm' data-toggle="modal" data-target='#guardianModal'>Add
                                    Guardian</button>
                            </div>
                            <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link" id="one-tab" data-toggle="tab" href="#details" role="tab"
                                        aria-controls="basic" aria-selected="true">
                                        <i class='fas fa-child text-indigo'></i> Details
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="two-tab" data-toggle="tab" href="#contact" role="tab"
                                        aria-controls="contact" aria-selected="false">
                                        <i class='fas fa-user text-primary'></i> Guardian
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active p-3" id="details" role="tabpanel"
                                aria-labelledby="one-tab">
                                <div class='row'>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        <strong>Name: </strong>
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        {{ $child->firstname }} {{ $child->lastname }} {{ $child->surname }}
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        <strong>Gender: </strong>
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        {{ $child->gender }}
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        <strong>Date of birth: </strong>
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        {{ $child->dob }}
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        <strong>Residence: </strong>
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        {{ $child->residence }}
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        <strong>Sunday School Class: </strong>
                                    </div>
                                    <div class='col-6 col-sm-5 col-md-4 col-lg-3 alert'>
                                        {{ $child->sunday_school_class }}
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade p-3" id="contact" role="tabpanel" aria-labelledby="two-tab">
                                <div class='table-responsive'>
                                    <table class='table w-100'>
                                        <thead>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Email Address</th>
                                            <th>Phone</th>
                                            <th>Action</th>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Message Modal -->
    <div class="modal fade" id="guardianModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-plus'></i> Add Guardian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action='{{ url('dashboard/children/parent/add') }}' method='post' class="sms-form">
                        @csrf
                        <div class="form-group">
                            <label>Child Name</label>
                            <input type='text' class="form-control" name='name' placeholder="Child Name"
                                value='{{ $child->firstname }} {{ $child->lastname }} {{ $child->surname }}' readonly>
                            <input type='hidden' name='child_id' value={{ $child->id }}>
                        </div>

                        <div class="form-group">
                            <label>Parent Name</label>
                            <select class='form-control' name='parent' id='parents'></select>
                        </div>
                        <div class="form-group">
                            <label><small>Relationship:</small></label>
                            <select class='form-control' name='gender'>
                                <option disabled>Choose Relationship</option>
                                <option value='MOTHER'>MOTHER</option>
                                <option value='FATHER'>FATHER</option>
                                <option value='RELATIVE'>RELATIVE</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class='modal-footer pt-0'>
                    <button type='submit' class="btn btn-primary btn-submit">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('#parents').select2({
                width: '100%',
                placeholder: 'Select Parent',
                allowClear: true,
                dropdownParent: $('#guardianModal'),
                ajax: {
                    url: '{{ url('dashboard/children/parents/search') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.firstname+" "+item.lastname+" ("+item.email+"|"+item.phone+")",
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
                    url: "{{ url('dashboard/children/datatables/guardians/' . $child->id) }}",
                    data: function(d) {
                        d.search = $('input[name=search').val();
                    }
                },
                columns: [{
                        data: 'guardian',
                        name: "guardian",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'relationship',
                        name: "relationship",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'email',
                        name: "email",
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
            $('#guardianModal input[name=parent]').keyup(function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    var search = $('input[name=parent]').val();
                    $('.auto-search').show();
                    $.ajax({
                        url: "{{ url('children/parents/search') }}?search=" + search,
                        type: 'GET',
                    }).done(function(data) {
                        var mydata = JSON.parse(data);
                        if (mydata.length > 0) {
                            $('.auto-search .list-group').html('');
                            $.each(mydata, function(index, item) {
                                var lastname = item.lastname == null ? '' : item
                                    .lastname;
                                var surname = item.surname == null ? '' : item
                                    .surname;
                                var phone = item.phone == null ? '' : item.phone;
                                $('.auto-search .list-group').append(
                                    '<a class="list-group-item text-muted" href="' +
                                    item.id + '">' +
                                    '<strong>' + item.firstname + ' ' +
                                    lastname + ' ' + surname +
                                    '</strong><br><span class="small">' +
                                    item.email + ' - ' + phone + '</span>' +
                                    '</a>');
                            });
                        } else {
                            $('.auto-search .list-group').html(
                                '<p class="list-group-item text-danger">' +
                                '<i class="fas fa-ban"></i> No parent found!</p>');
                        }
                    }).fail(function(data) {
                        $('.auto-search .list-group').html(
                            '<p class="list-group-item text-danger">' +
                            '<i class="fas fa-exclamation-circle"></i> <strong>Oops</strong> Something went wrong</p>'
                        );
                    });
                }, 1000);
            });

            $(document).on('click', '.auto-search a', function(e) {
                e.preventDefault();
                var id = $(this).attr('href');
                var name = $(this).find('strong').text();
                $('#guardianModal input[name=parent_id]').val(id);
                $('#guardianModal input[name=parent]').val(name);
                $('.auto-search').hide();
            });
            $('#guardianModal .btn-submit').click(function() {
                $('#guardianModal form').submit();
            });
        });
    </script>
@endpush
