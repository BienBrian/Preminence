@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-phone'></i> <b>Missing Mpesa Phone</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Missing Mpesa Phones</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class='row'>
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                <div class="col">
                                    <h3 class="mb-0">Missing Mpesa Phones</h3>
                                </div>
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary btn-launch-modal" data-toggle="modal"
                                        data-target="#mpesaMissingModal">
                                        <span class="d-none d-md-block"><i class='fas fa-plus-circle'></i> Add</span>
                                        <span class="d-md-none"><i class='fas fa-plus-circle'></i></span></a>
                                    </button>
                                </div>-->
                                <div class="col-sm-12">
                                    <form class="row search-funds-form pt-3">
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control form-control-alternative"
                                                name="msearch" placeholder="search" />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control datepicker"
                                                name="from" placeholder="From Date" />
                                        </div>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control datepicker"
                                                name="to" placeholder="To Date" />
                                        </div>
                                    </form>

                                </div>
                            </div>
                            <div class="table-responsive">
                                <!-- Projects table -->
                                <table class="table align-items-center table-flush">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col">Trans ID</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Date</th>
                                            <th scope="col" class='text-right'>Action</th>
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
        </div>
    </section>


    <!-- Modal -->
    <div class="modal fade" id="mpesaMissingModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel">Missing Mpesa Phone Update</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/finances/missing_mpesa_phones/add') }}" method="post">
                        @csrf
                        <input type='hidden' name='id' value=''>
                        <div class='form-group'>
                            <label class='small'>Trans ID</label>
                            <input type="text" class="form-control" name="trans_id" placeholder='Trans ID' required
                                readonly>
                        </div>
                        <div class='form-group'>
                            <label class='small'>Name</label>
                            <input type="text" class="form-control" name="name" placeholder='Name' required readonly>
                        </div>
                        <div class='form-group'>
                            <label class='small'>Phone Number</label>
                            <input type="text" class="form-control" name="phone" placeholder='Phone Number' required>
                        </div>
                        <div class='alert feedback border d-none'>
                            <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-submit-funds">Add Funds</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            flatpickr(".datepicker", {
                enableTime: false,
                dateFormat: "Y-m-d",
                //defaultDate: new Date(),
            });
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/finances/datatable/missing_mpesa_phones') }}",
                    data: function(d) {
                        d.msearch = $('input[name=msearch]').val();
                        d.from = $('input[name=from]').val();
                        d.to = $('input[name=to]').val();
                    }
                },

                buttons: [{
                        extend: 'csv',
                        text: '<i class="fas fa-file"></i> CSV',
                        className: 'btn border btn-sm',
                        title: 'Missing Mpesa Phones',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn border btn-sm',
                        title: 'Missing Mpesa Phones',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }, {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn border btn-sm',
                        title: 'Missing Mpesa Phones',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }
                ],
                "lengthMenu": [
                    [20, 100, 250, 500, 1000],
                    [20, 100, 250, 500, 1000]
                ],
                dom: "<'top'B>rt<'bottom'lip><'clear'>", //'lBtrip',
                columns: [{
                        data: 'trans_id',
                        name: 'trans_id'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        defaultContent: "N/A",
                    },
                    {
                        data: 'trans_date',
                        name: 'trans_date',
                        searchable: false,
                        orderable: false,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            $('.search-funds-form').submit(function(e){
                e.preventDefault();
            });
            var timer = null;
            $('.datepicker').change(function(){
                table.draw();
            });
            $('input[name=msearch]').keyup(function(){
                clearTimeout(timer);
                timer = setTimeout(function(){
                    table.draw();
                }, 1000);
            });
            $(document).on('click', '.table .btn-edit', function() {
                $('#mpesaMissingModal .modal-title').text('Editing Missing MPesa Phone');
                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var name = row.find('.name').text();
                var trans_id = row.find('.trans_id').text();

                $('#mpesaMissingModal input[name=id]').val(id);
                $('#mpesaMissingModal input[name=trans_id]').val(trans_id);
                $('#mpesaMissingModal input[name=name]').val(name);
            });
            $('#mpesaMissingModal form').submit(function(e) {
                e.preventDefault();

                var btn = $('#mpesaMissingModal form .btn')
                btn.attr('disabled', 'disabled');
                $('#mpesaMissingModal .feedback').removeClass('d-none');
                $('#mpesaMissingModal .feedback').removeClass('alert-danger');
                $('#mpesaMissingModal .feedback').removeClass('alert-success');
                $('#mpesaMissingModal .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $('#mpesaMissingModal form').serialize();
                $.ajax({
                    url: '{{ url('dashboard/finances/missing_mpesa_phones/add') }}',
                    type: 'POST',
                    data: formData
                }).done(function(data) {
                    $('#mpesaMissingModal .feedback').addClass('alert-success');
                    $('#mpesaMissingModal .feedback').html(
                        "<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    table.draw();
                    setTimeout(() => {
                        $('#mpesaMissingModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#mpesaMissingModal .feedback').addClass('alert-danger');
                    $('#mpesaMissingModal .feedback').html("");
                    if (data.errors) {
                        if (data.errors.id) {
                            $('#mpesaMissingModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .id + "<br>");
                        }

                        if (data.errors.phone) {
                            $('#mpesaMissingModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .phone + "<br>");
                        }
                    } else if (data.error) {
                        $('#mpesaMissingModal .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> " + data.error);
                    } else {
                        $('#mpesaMissingModall .feedback').html(
                            "<i class='fas fa-exclamation-circle'></i> <b>Whoops</b> Something went wrong with the server!"
                        );
                    }
                    setTimeout(() => {
                        $('#mpesaMissingModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                });
            });
        });
    </script>
@endpush
