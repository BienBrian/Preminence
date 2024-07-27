@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-calendar-alt'></i> <b>New</b> Attendance</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">New Attendance</li>
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
                <div class="col mb-5 mb-xl-0">
                    <div class="card bg-gradient-white shadow">
                        <div class="card-header bg-transparent">
                            <div class="row align-items-center">
                                <!--
                                            <div class="col">
                                                <h3 class="text-uppercase ls-1 mb-1"><i class='fas fa-calendar-alt'></i> | New
                                                    Attendance</h3>
                                            </div>-->
                                <div class="col d-flex justify-content-end">
                                    <a href='{{ url('dashboard/events_and_notices/attendance') }}' class='btn btn-primary btn-sm'><i
                                            class='fas fa-arrow-left'></i> Go Back</a>
                                </div>
                            </div>
                        </div>
                        <div class='card-body'>
                            <form class='row' action="{{ url('dashboard/events_and_notices/attendance/add') }}" method="POST" id='new-attendance'>
                                @csrf
                                <input type='hidden' name='id' value='0'>
                                <div class='col-sm-6'>
                                    <label>Attendees Group</label>
                                    <div class="form-group">
                                        <div class='input-group'>
                                            <div class='input-group-prepend'>
                                                <span class="input-group-text"
                                                    style='border-color: #ddd; border-width: 2px; border-right: none;'><i
                                                        class='fas fa-users'></i></span>
                                            </div>
                                            <select class='form-control' name="group_id"
                                                style='border-color: #ddd; border-width: 2px; border-left: none;'>
                                                @foreach ($groups as $group)
                                                    <option value='{{ $group->id }}'>{{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-sm-6'>
                                    <label>Attended for:</label>
                                    <small class='text-danger'>*Showing recent events/seminars</small>
                                    <div class="form-group">
                                        <div class='input-group'>
                                            <div class='input-group-prepend'>
                                                <span class="input-group-text"
                                                    style='border-color: #ddd; border-width: 2px; border-right: none;'><i
                                                        class='fas fa-users'></i></span>
                                            </div>
                                            <select class='form-control' name="attended_for"
                                                style='border-color: #ddd; border-width: 2px; border-left: none;'>
                                                <option value='0'>Church Service</option>
                                                <option value='1'>Event</option>
                                                <option value='2'>Seminar</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-sm-6'>
                                    <label class='servname'>Service Name:</label>
                                    <div class="form-group">
                                        <div class='input-group'>
                                            <div class='input-group-prepend'>
                                                <span class="input-group-text"
                                                    style='border-color: #ddd; border-width: 2px; border-right: none;'><i
                                                        class='fas fa-users'></i></span>
                                            </div>
                                            <select class='form-control' name="specific"
                                                style='border-color: #ddd; border-width: 2px; border-left: none;'>
                                                @foreach ($services as $service)
                                                    <option value='{{ $service->id }}'>{{ $service->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-sm-6'>
                                    <label>No. of Attendees</label>
                                    <div class="form-group">
                                        <div class='input-group'>
                                            <div class='input-group-prepend'>
                                                <span class="input-group-text"
                                                    style='border-color: #ddd; border-width: 2px; border-right: none;'><i
                                                        class='fas fa-arrow-right'></i></span>
                                            </div>
                                            <input type='number' class='form-control' name='attendees'
                                                placeholder='No. of attendees'
                                                style='border-color: #ddd; border-width: 2px; border-left: none;' required>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-sm-6'>
                                    <label>Date</label>
                                    <div class="form-group">
                                        <div class='input-group'>
                                            <div class='input-group-prepend'>
                                                <span class="input-group-text"
                                                    style='border-color: #ddd; border-width: 2px; border-right: none;'><i
                                                        class='fas fa-calendar-alt'></i></span>
                                            </div>
                                            <input type='text' class='form-control mydatepicker' name='date'
                                                placeholder='date' required
                                                style='border-color: #ddd; border-width: 2px; border-left: none;'>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 text-right">
                                    <button class='btn btn-primary'>Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            flatpickr(".mydatepicker", {
                enableTime: true,
                dateFormat: "Y-m-d",
                //defaultDate: new Date(),
            });
            $('select[name="attended_for"]').change(function() {
                $('.servname').html("<i class='fas fa-spinner fa-pulse'></i> Loading...");
                var loadfor = $(this).val();
                if (loadfor == 0) {
                    var myurl = "{{ url('dashboard/ajax/services') }}";
                } else if (loadfor == 1) {
                    var myurl = "{{ url('dashboard/ajax/events') }}";
                } else {
                    var myurl = "{{ url('dashboard/ajax/seminars') }}";
                }
                $.ajax({
                    url: myurl,
                    type: "GET",
                }).done(function(data) {
                    var mydata = JSON.parse(data);
                    $('select[name="specific"]').html("");
                    $.each(mydata, function(key, value) {
                        $('select[name="specific"]').append("<option value='" + value
                            .id + "'>" + value.name + "</option>");
                    });
                    if (loadfor == 0) {
                        $('.servname').html("Service Name:");
                    } else if (loadfor == 1) {
                        $('.servname').html("Events Name:");
                    } else {
                        $('.servname').html("Seminar Name:");
                    }
                }).fail(function() {
                    toastr.error("We're Unable to complete your request at this time.");
                });
            });

        });
    </script>
@endpush
