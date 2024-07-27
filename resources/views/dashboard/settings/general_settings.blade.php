@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-cog'></i> <b>General</b> Settings</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block p-2">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">General Settings</li>
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
                    <form method="POST" action="{{ url('dashboard/settings/general/add') }}" id='general-settings-form'>
                        @csrf
                        <div class='card'>
                            <div class='card-body'>
                                <!-- Toggle Button -->
                                <label class="toggle-btn">
                                    <input type="checkbox" name='show_auction_shares' value='1'
                                        {{ $generalSettings != null ? ($generalSettings->show_auction_shares ? 'checked' : '') : '' }}>
                                    <span class="slider"></span>
                                </label>&nbsp;
                                <span>Show</span> Shares on Auction & Shares Card

                            </div>
                        </div>
                        <div class='card'>
                            <div class='card-body text-right'>
                                <button class='btn btn-primary btn-sm btnSave'><i class='fas fa-paper-plane'></i> Save
                                    Settings</button>
                            </div>
                        </div>
                    </form>

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
            $('#general-settings-form').submit(function(e) {
                e.preventDefault();
                var btn = $(this).find('.btnSave');
                btn.attr('disabled', 'disabled');
                btn.html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving...");
                var formData = $(this).serialize();
                $.ajax({
                    url: '{{ url('dashboard/settings/general/add') }}',
                    type: 'POST',
                    data: formData
                }).done(function(data) {
                    toastr.success(data.success);
                    btn.html(
                        "<i class='fas fa-paper-plane'></i> Save Settings");
                    btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    if (data.errors) {
                        if (data.errors.show_auction_shares) {
                            toastr.error(data.errors.show_auction_shares);
                        }
                    } else if (data.error) {
                        toastr.error(data.error);
                    } else {
                        toastr.error("<b>Whoops</b> Something went wrong with the server!");
                    }
                    btn.html(
                        "<i class='fas fa-paper-plane'></i> Save Settings");
                    btn.removeAttr('disabled');
                });
            });
            $(document).on('click', '.table .btn-edit', function() {
                $('#timezones').empty();
                $('#weekday').empty();
                $('#userModal .modal-title span').text("Edit ");
                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var from_time = row.find('.from_time').text();
                var to_time = row.find('.to_time').text();
                var timezone = row.find('.timezone').text();
                var timezone_id = row.find('.timezone_id').text();
                var weekday = row.find('.day').text();
                var dayname = row.find('.day_name').text();
                var auction_date = row.find('.auction_date').text();
                var status = row.find('.status').text();
                $('#userModal input[name=id]').val(id);
                $('#userModal input[name=from_time]').val(from_time);
                $('#userModal input[name=to_time]').val(to_time);
                var data = {
                    id: timezone_id,
                    text: timezone
                };
                var newOption = new Option(data.text, data.id, false, false);
                $('#timezones').append(newOption).trigger('change');
                if (dayname != "") {
                    var data1 = {
                        id: weekday,
                        text: dayname
                    };
                    var newOption = new Option(data1.text, data1.id, false, false);
                    $('#weekday').append(newOption).trigger('change');
                }
                //$('#userModal select[name=day]').val(weekday);
                $('#userModal input[name=auction_date]').val(auction_date);
                $('#userModal select[name=status]').val(status);
            });
        });
    </script>
@endpush
