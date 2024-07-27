@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-pie'></i> View <b>Bought</b> Share</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">View Bought Share</li>
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
                            <!--
                                    <form class='search-form row d-flex align-items-end' id='search-form'>
                                        <div class="col-sm-4">
                                            <label>Search Name</label>
                                            <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Status</label>
                                            <select name="status" class="form-control mb-1">
                                                <option value=''>All</option>
                                                <option value='Pending'>Pending</option>
                                                <option value='Active'>Active</option>
                                                <option value='Suspended'>Suspended</option>
                                                <option value='Cancelled'>Cancelled</option>
                                                <option value='Completed'>Completed</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label>Active</label>
                                            <select name="active" class="form-control mb-1">
                                                <option value='1'>Yes</option>
                                                <option value='0'>No</option>
                                            </select>
                                        </div>
                                    </form>-->


                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <!--<th>#</th>-->
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Returns</th>
                                            <th>User</th>
                                            <th>Name</th>
                                            <th>Phone</th>
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

    <!-- Profile Modal -->
    <div class="modal fade" id="routeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-money-check'></i> <span
                            class='text-header'>Confirm </span>
                        Payments</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('dashboard/shares/bought/pay') }}" class="row"
                        enctype="multipart/form-data">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <input type='hidden' name='user_id' value='{{ auth()->user()->id }}'>
                        <input type='hidden' name='buyer_id' value='0'>
                        <input type='hidden' name='amount' value='0'>
                        <input type='hidden' name='share_transaction_id' value='0'>
                        <div class='alert border my-bg text-white'>
                            <span style='opacity:0.8'><i class='fas fa-money-check-alt'></i> <span class='my_payment_method'>Supported</span></span> Payment
                            Platforms
                            <hr style='opacity:0.8; background-color:#fff'>
                            <!--twint, PayPal, Google Pay, Apple Pay, and Samsung Pay, Amazon Pay, Sofort, Ratepay, and Klarna-->
                            <span class='payment_details'><b>UPI (Unified Payment Interface)</b> & <b>Bank Payments</b> payments Supported!</span>
                            <!--<hr style='opacity:0.8'>-->
                        </div>
                        <div class="col-sm-6 form-group">
                            <label>Select Payment Method</label>
                            <select name='payment_method' class='form-control mb-1' id='payment_methods'>
                                <option value='upi'>UPI</option>
                                <option value='bank'>Bank</option>
                            </select>
                        </div>

                        <div class="col-sm-6 form-group">
                            <label class='my_payment_method'>UPI</label>
                            <select name='my_payment_method' class='form-control mb-1' id='my_payment_methods'>
                            </select>
                        </div>

                        <!--<div class='col-sm-6 form-group'>
                                    <label>Seller Phone Number</label><br>
                                    <span class='phone'>xxxx xxx xxx xxx</span>
                                </div>

                                <div class='col-sm-6 form-group'>
                                    <label>Seller Email Address</label><br>
                                    <span class='email'>example@gmail.com</span>
                                </div>-->

                        <div class='col-sm-12 form-group'>
                            <label>Upload Transaction Screenshot</label>
                            <input type="file" name="screenshot" id="imageInput" class='form-control' accept="image/*">
                        </div>
                        <div class='alert feedback border d-none'>
                            <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i
                            class='fas fa-times'></i> Close
                    </button>
                    <button type="button" class="btn btn-primary btn-sm btnSave"><i class='fas fa-paper-plane'></i> Save
                        changes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            var table = $('.table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('dashboard/shares/bought/view/datatable/auctions/' . $share_transaction->id) }}",
                    data: function(d) {
                        d.search = $('.search-form input[name=search]').val();
                        d.status = $('.search-form select[name=status]').val();
                        d.active = $('.search-form select[name=active]').val();
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
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'returns',
                        name: 'returns'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
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

            $('#payment_methods').select2({
                width: '100%',
                placeholder: 'Select Maturities',
                dropdownParent: $('#routeModal'),
                allowClear: false,
            });
            let payment_method = "upi";
            let buyer_id = 0;
            let damount = 0;
            $('#my_payment_methods').select2();
            $('#payment_methods').change(function() {
                $('#my_payment_methods').empty();
                $('#my_payment_methods').select2('destroy');
                payment_method = $('#payment_methods').val();
                if (payment_method == 'upi') {
                    $('.my_payment_method').text("UPI");
                } else {
                    $('.my_payment_method').text("Bank");
                }
                updatePaymentMethodSelect();
            });

            $('#my_payment_methods').change(function(){
                var payment_details = $('#my_payment_methods').select2('data');
                if(payment_details.length > 0){
                    var details = (payment_details[0].text).split('|');
                    if(payment_method == 'upi'){
                        //3 items
                        $('.payment_details').html('<table class="w-100"><tr><td><b>UPI ID:</b></td><td class="p-1">'+details[0]+"</td></tr>"+
                            '<tr><td><b>UPI Phone:</b></td><td class="p-1">+91'+details[1]+"</td></tr>"+
                            '<tr><td><b>Amount:</b></td><td class="p-1"><i class="fas fa-rupee-sign"></i> '+damount+"</td></tr>"+
                            '<tr><td><b>User:</b></td><td class="p-1">'+details[2]+"</td></tr>"+
                            "</table>");
                    }else{
                        //5 items
                        $('.payment_details').html('<table class="w-100"><tr><td><b>Account Number:</b></td><td class="p-1">'+details[0]+"</td></tr>"+
                            '<tr><td><b>Account Holder Name:</b></td><td class="p-1">'+details[1]+"</td></tr>"+
                            '<tr><td><b>Bank Name:</b></td><td class="p-1">'+details[2]+"</td></tr>"+
                            '<tr><td><b>IFSC:</b></td><td class="p-1">'+details[3]+"</td></tr>"+
                            '<tr><td><b>Amount:</b></td><td class="p-1"><i class="fas fa-rupee-sign"></i> '+damount+"</td></tr>"+
                            '<tr><td><b>User:</b></td><td class="p-1">'+details[4]+"</td></tr>"+
                            "</table>");

                    }
                }
            });

            function updatePaymentMethodSelect() {
                $('.payment_details').html("<b>UPI (Unified Payment Interface)</b> & <b>Bank Payments</b> payments Supported!");
                $('#my_payment_methods').select2({
                    width: '100%',
                    placeholder: 'Select Account',
                    dropdownParent: $('#routeModal'),
                    allowClear: false,
                    ajax: {
                        url: '{{ url('dashboard/search/payments/') }}/' + payment_method + "/" +
                            buyer_id,
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
                $('#my_payment_methods').empty();

            }
            var timer = null;

            $('#search-form input[type=text]').keyup('submit', function() {
                clearTimeout(timer);
                timer = setTimeout(function() {
                    table.draw();
                }, 1000);
            });
            $('#search-form select[name=status]').change(function() {
                table.draw();
            });
            $('#search-form select[name=active]').change(function() {
                table.draw();
            });
            $('#imageInput').on('change', function() {
                var fileSize = this.files[0].size; // in bytes
                var maxSize = 2048 * 1024; // 2048 KB (2 MB)

                if (fileSize > maxSize) {
                    $('#routeModal .feedback').removeClass('d-none');
                    $('#routeModal .feedback').removeClass('alert-success');
                    $('#routeModal .feedback').addClass('alert-danger');
                    $('#routeModal .feedback').html(
                        "<i class='fas fa-exclamation-triangle'></i> File size exceeds the limit (2 MB). Please choose a smaller file."
                    );
                    $(this).val('');
                    setTimeout(() => {
                        $('#routeModal .feedback').addClass('d-none');
                    }, 3000);
                }
            });

            $('#routeModal .btnSave').click(function() {
                var btn = $(this);
                btn.attr('disabled', 'disabled');
                $('#routeModal .feedback').removeClass('d-none');
                $('#routeModal .feedback').removeClass('alert-danger');
                $('#routeModal .feedback').removeClass('alert-success');
                $('#routeModal .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                //var formData = $('#routeModal form').serialize();

                var formData = new FormData($('#routeModal form')[0]);
                $.ajax({
                    url: '{{ url('dashboard/shares/bought/pay') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                }).done(function(data) {
                    $('#routeModal .feedback').addClass('alert-success');
                    $('#routeModal .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    table.draw();
                    setTimeout(() => {
                        $('#routeModal .feedback').addClass('d-none');
                    }, 3000);
                    //btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#routeModal .feedback').addClass('alert-danger');
                    $('#routeModal .feedback').html("");
                    if (data.errors) {
                        if (data.errors.id) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .id + "<br>");
                        }
                        if (data.errors.user_id) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .user_id + "<br>");
                        }
                        if (data.errors.buyer_id) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .buyer_id + "<br>");
                        }
                        if (data.errors.share_transaction_id) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .share_transaction_id + "<br>");
                        }
                        if (data.errors.screenshot) {
                            $('#routeModal .feedback').append(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .screenshot + "<br>");
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

                var row = $(this).closest('tr');
                var share_id = row.find('.share_id').text();
                buyer_id = row.find('.buyer_id').text();
                var buyer_email = row.find('.buyer_email').text();
                var buyer_phone = row.find('.buyer_phone').text();
                var amount = row.find('.amount').text();
                var share_transaction_id = row.find('.share_transaction_id').text();
                damount = row.find('.damount').text();
                var status = row.find('.status').text();
                updatePaymentMethodSelect();
                $('#routeModal input[name=id]').val(share_id);
                $('#routeModal input[name=buyer_id]').val(buyer_id);
                $('#routeModal input[name=share_transaction_id]').val(share_transaction_id);
                /*$('#routeModal .email').text(buyer_email);
                $('#routeModal .phone').text("(+91) " + buyer_phone);*/
                $('#routeModal input[name=amount]').val(amount);

                $('#routeModal select[name=status]').val(status);
            });

        });
    </script>
@endpush
