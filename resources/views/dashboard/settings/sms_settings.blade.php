@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-comment'></i> <b>SMS</b> Settings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block p-2">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">SMS Settings</li>
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
                            <form method="POST" action="{{ url('dashboard/settings/sms/add') }}" id='sms-settings-form'>
                                @csrf
                                <div class='form-group'>
                                    <label>SMS URL:</label>
                                    <input type="text" class='form-control' placeholder="SMS URL" name='sms_url' value='{{ env('SMS_URL') }}'/>
                                </div>
                                <div class='form-group'>
                                    <label>API KEY:</label>
                                    <input type="text" class='form-control' placeholder="SMS API KEY" name='sms_api_key' value='{{ env('SMS_API_KEY') }}' />
                                </div>
                                <div class='form-group'>
                                    <label>PARTNER ID:</label>
                                    <input type="text" class='form-control' placeholder="SMS PARTNER ID" name='sms_partner_id' value='{{ env('SMS_PARTNER_ID') }}' />
                                </div>
                                <div class='form-group'>
                                    <label>SHORT CODE:</label>
                                    <input type="text" class='form-control' placeholder="SMS SHORTCODE" name='sms_short_code' value='{{ env('SMS_SHORT_CODE') }}' />
                                </div>
                                <div class='form-group text-right'>
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
            $('#sms-settings-form').submit(function(e) {
                e.preventDefault();
                var btn = $(this).find('.btn');
                btn.attr('disabled', 'disabled');
                btn.html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $(this).serialize();
                $.ajax({
                    url: '{{ url('dashboard/settings/sms/add') }}',
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
                        if (data.errors.sms_url) {
                            toastr.error(data.errors
                                .sms_url + "");
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
