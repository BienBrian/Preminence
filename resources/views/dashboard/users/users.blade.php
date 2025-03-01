@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> Users</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Users</li>
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
                            <div class='row'>
                                <div class='col'>
                                </div>
                                <div class='col text-right'>
                                    @can('Add Users')
                                        <button class="btn btn-primary btn-sm btn-launch-modal" data-toggle="modal"
                                            data-target="#userModal"><i class='fas fa-user-plus'></i> Add User</button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-header">
                            <form class='row' id='search-form'>
                                <input type='hidden' value='1' name='visible'>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                                </div>
                                <div class='col-sm-4'>
                                    <select name="role" class='form-control mb-2' id='search-roles'></select>
                                </div>
                                <div class="col-sm-4">
                                    <select name="status" class="form-control mb-1">
                                        <option value='1'>Active</option>
                                        <option value='0'>In-Active</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class='card-body'>

                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <!--<th>#</th>-->
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th class='text-end'>Action</th>
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
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-user-plus'></i> <span>New User</span>
                    </h5><button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('users/add') }}" class="row">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class='col-sm-6 form-group'>
                            <label>First Name</label>
                            <input type='text' placeholder="First Name" name="firstname" class='form-control' autofocus
                                required />
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Last Name</label>
                            <input type='text' placeholder="Last Name" name="lastname" class='form-control' autofocus
                                required />
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Email Address</label>
                            <input type='email' placeholder="Email Address" name="email" class='form-control' required />
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Phone Number</label>
                            <input type='text' placeholder="Phone Number" name="phone" class='form-control' required />
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Role</label>
                            <select name="role" class='form-control' id='roles'></select>
                        </div>
                        <div class='col-sm-6 form-group'>
                            <label>Status</label>
                            <select name="status" class='form-control'>
                                <option disabled>Status</option>
                                <option value='1'>Active</option>
                                <option value='0'>In-Active</option>
                            </select>
                        </div>
                        <div class='col-sm-12'>
                            <div class='alert feedback border d-none'>
                                <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class='fas fa-times'></i>
                        Close</button>
                    <button type="button" class="btn btn-primary btn-sm btnSave"><i class='fas fa-paper-plane'></i> Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            $('.btn-filter').click(function () {
                $('.filter-div').toggleClass('d-none');
            });
            flatpickr("#from_date, #to_date", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                //defaultDate: new Date(),
            });

            var table = $('.table').DataTable({
                scrollX: true,
                fixedColumns: {
                    //left: 2,
                    right: 1,
                    //left: 0
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/users/datatable') }}",
                    data: function (d) {
                        d.search = $('#search-form input[name=search]').val();
                        d.role = $('#search-form select[name=role]').val();
                        d.status = $('#search-form select[name=status]').val();
                        d.timezone = timeZone;
                    }
                },
                dom: 'lBtrip', //'lfBtrip'
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Users available",
                },

                columns: [/*{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },*/
                    {
                        data: 'name',
                        name: 'name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'email',
                        name: 'email',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'role',
                        name: 'role',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false,
                        searchable: false
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
            $('#search-form input[name=search]').keyup(function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    table.draw();
                }, 1000);
            });
            $('#search-form select, #from_date, #to_date').change(function () {
                table.draw();
            });
            $('#search-roles').select2({
                //width: '100%',
                placeholder: 'Select Role',
                //dropdownParent: $('#userModal'),
                allowClear: true,
                ajax: {
                    url: '{{ url('dashboard/search/roles') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
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
            $('#roles').select2({
                width: '100%',
                placeholder: 'Select Role',
                dropdownParent: $('#userModal'),
                allowClear: true,
                language: {
                    emptyTable: "<i class='fas fa-ban'></i> No Users available",
                },
                ajax: {
                    url: '{{ url('dashboard/search/roles') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
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

            $('.btn-launch-modal').click(function () {
                $('#userModal .modal-title span').text("New User");
                $('#userModal input[name=email], #userModal input[name=phone]')
                    .removeAttr('readonly', 'readonly');

                $('#userModal input[name=id]').val(0);
                $('#userModal input[name=firstname]').val("");
                $('#userModal input[name=lastname]').val("");
                $('#userModal input[name=email').val("");
                $('#userModal input[name=phone]').val("");
                $('#userModal select[name=status]').val(1);
                $('#roles').empty();

            });
            $('#userModal .btnSave').click(function () {
                var btn = $(this);
                btn.attr('disabled', 'disabled');
                $('#userModal .feedback').removeClass('d-none');
                $('#userModal .feedback').removeClass('alert-danger');
                $('#userModal .feedback').removeClass('alert-success');
                $('#userModal .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $('#userModal form').serialize();
                $.ajax({
                    url: '{{ url('dashboard/users/add') }}',
                    type: 'POST',
                    data: formData
                }).done(function (data) {
                    $('#userModal .feedback').addClass('alert-success');
                    $('#userModal .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    table.draw();
                    setTimeout(() => {
                        $('#userModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                }).fail(function (response) {
                    let data = response.responseJSON;
                    $('#userModal .feedback').addClass('alert-danger');
                    $('#userModal .feedback').html("");
                    if (data.errors) {
                        if (data.errors.firstname) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                    .firstname + "<br>");
                        }
                        if (data.errors.lastname) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                    .lastname + "<br>");
                        }
                        if (data.errors.email) {
                            $('#userModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                    .email + "<br>");
                        }
                        if (data.errors.phone) {
                            $('#userModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                    .phone + "<br>");
                        }
                        if (data.errors.role) {
                            $('#userModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                    .role + "<br>");
                        }
                        if (data.errors.status) {
                            $('#userModal .feedback').append(
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
            $(document).on('click', '.table .btn-edit', function () {
                $('#roles').empty();
                $('#userModal .modal-title span').text("Edit User");
                $('#userModal input[name=email], #userModal input[name=phone]')
                    .attr('readonly', 'readonly');

                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var firstname = row.find('.firstname').text();
                var lastname = row.find('.lastname').text();
                var role_id = row.find('.role_id').text();
                var role = row.find('.role_name').text();
                var email = row.find('.email').text();
                var phone = row.find('.phone').text();
                var status = row.find('.status').text();
                $('#userModal input[name=id]').val(id);
                $('#userModal input[name=firstname]').val(firstname);
                $('#userModal input[name=lastname]').val(lastname);
                if (role_id > 0) {
                    var data = {
                        id: role_id,
                        text: role
                    };
                    var newOption = new Option(data.text, data.id, false, false);
                    $('#roles').append(newOption).trigger('change');
                }
                $('#userModal input[name=email').val(email);
                $('#userModal input[name=phone]').val(phone);
                $('#userModal select[name=status]').val(status);
            });
        });
    </script>
@endpush
