@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 p-2">
                    <h5 class="m-0 text-header"><i class='fas fa-bell'></i> <b>View</b> Notification</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block p-2">

                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">View Notification</li>
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
                    <div class="card shadow-none">
                        <div class='card-header'><i class='far fa-clock'></i> Posted <b>{{ Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</b></div>
                        <div class='card-body'>
                            {!! strip_tags($notification->notification, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><table><thead><tbody><tr><th><td><div><span><hr>') !!
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
            $('.btn-launch-modal').click(function() {
                $('#userModal .modal-title span').text("New ");
                $('#userModal input[name=id]').val(0);
                $('#userModal textarea[name=notification]').val("");
                $('#userModal select[name=status]').val(1);
            });
            $('#userModal .btnSave').click(function() {
                var btn = $(this);
                btn.attr('disabled', 'disabled');
                $('#userModal .feedback').removeClass('d-none');
                $('#userModal .feedback').removeClass('alert-danger');
                $('#userModal .feedback').removeClass('alert-success');
                $('#userModal .feedback').html(
                    "<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
                var formData = $('#userModal form').serialize();
                $.ajax({
                    url: '{{ url('dashboard/settings/notifications/add') }}',
                    type: 'POST',
                    data: formData
                }).done(function(data) {
                    $('#userModal .feedback').addClass('alert-success');
                    $('#userModal .feedback').html("<i class='fas fa-exclamation-circle'></i> " +
                        data.success);
                    table.draw();
                    setTimeout(() => {
                        $('#userModal .feedback').addClass('d-none');
                    }, 3000);
                    btn.removeAttr('disabled');
                }).fail(function(response) {
                    let data = response.responseJSON;
                    $('#userModal .feedback').addClass('alert-danger');
                    $('#userModal .feedback').html("");
                    if (data.errors) {
                        if (data.errors.notification) {
                            $('#userModal .feedback').html(
                                "<i class='fas fa-exclamation-circle'></i> " + data.errors
                                .notification + "<br>");
                        }

                        if (data.errors.status) {
                            $('#userModal .feedback').html(
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
            $(document).on('click', '.table .btn-edit', function() {
                $('#userModal .modal-title span').text("Edit ");
                var row = $(this).closest('tr');
                var id = row.find('.id').text();
                var notification = row.find('.notification').text();
                var status = row.find('.status').text();

                $('#userModal input[name=id]').val(id);
                $('#userModal textarea[name=notification]').val(notification);
                $('#userModal select[name=status]').val(status);
            });
        });
    </script>
@endpush
