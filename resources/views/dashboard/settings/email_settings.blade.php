@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-envelope'></i> <b>Email</b> Settings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block p-2">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Email Settings</li>
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
                        <div class='card-body'>
                            <form method="POST" action="{{ url('dashboard/settings/email/add') }}" id='email-settings-form' class='row'>
                                @csrf
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL MAILER:</label>
                                    <input type="text" class='form-control' placeholder="MAIL MAILER" name='mail_mailer' value='{{ env('MAIL_MAILER') }}' readonly/>
                                </div>
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL HOST:</label>
                                    <input type="text" class='form-control' placeholder="MAIL HOST" name='mail_host' value='{{ env('MAIL_HOST') }}' />
                                </div>
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL PORT:</label>
                                    <input type="text" class='form-control' placeholder="MAIL PORT" name='mail_port' value='{{ env('MAIL_PORT') }}' readonly/>
                                </div>
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL USERNAME:</label>
                                    <input type="text" class='form-control' placeholder="MAIL USERNAME" name='mail_username' value='{{ env('MAIL_USERNAME') }}' />
                                </div>
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL PASSWORD:</label>
                                    <input type="text" class='form-control' placeholder="MAIL PASSWORD" name='mail_password' value='{{ env('MAIL_PASSWORD') }}' />
                                </div>
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL ENCRYPTION:</label>
                                    <input type="text" class='form-control' placeholder="MAIL ENCRYPTION" name='mail_encryption' value='{{ env('MAIL_ENCRYPTION') }}' readonly/>
                                </div>
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL FROM ADDRESS:</label>
                                    <input type="text" class='form-control' placeholder="MAIL FROM ADDRESS" name='mail_from_address' value='{{ env('MAIL_FROM_ADDRESS') }}'/>
                                </div>
                                <div class='col-sm-6 form-group'>
                                    <label>MAIL FROM NAME:</label>
                                    <input type="text" class='form-control' placeholder="MAIL FROM NAME" name='mail_from_name' value='{{ env('MAIL_FROM_NAME') }}' readonly/>
                                </div>
                                <div class='col-sm-12 form-group text-right'>
                                    <button class='btn btn-primary'><i class='fas fa-paper-plane'></i> Save Settings</button>
                                </div>
                            </form>
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
            $('#email-settings-form').submit(function(e) {
                e.preventDefault();
                var btn = $(this).find('.btn');
                btn.attr('disabled', 'disabled');
                btn.html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $(this).serialize();
                $.ajax({
                    url: '{{ url('dashboard/settings/email/add') }}',
                    type: 'POST',
                    data: formData
                }).done(function(data) {
                toastr.success("" +
                        data.success);
                btn.html(
                    "<i class='fas fa-paper-plane'></i> Save Settings");
                    btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#userModal .feedback').addClass('alert-danger');
                    $('#userModal .feedback').html("");
                    if (data.errors) {
                        if (data.errors.mail_host) {
                            toastr.error(data.errors
                                .mail_host + "");
                        }
                        if (data.errors.partner_id) {
                            toastr.error(data.errors
                                .partner_id + "");
                        }

                        if (data.errors.sms_api_key) {
                            toastr.error(data.errors
                                .sms_api_key + "");
                        }
                        if (data.error.sms_short_code) {
                            toastr.error(data.errors
                                .sms_short_code + "");
                        }

                    } else if (data.error) {
                        toastr.error("" + data.error);
                    } else {
                        toastr.error(
                            "Whoops! Something went wrong with the server!"
                        );
                    }
                btn.html(
                    "<i class='fas fa-paper-plane'></i> Save Settings");
                    btn.removeAttr('disabled');
                });
            });
        });
    </script>
@endpush
