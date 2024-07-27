@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-envelope-open'></i> View <b>Scheduled SMS</b></h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/communication/schedule/sms') }}">Scheduled SMS</a></li>
                        <li class="breadcrumb-item active">View</li>
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

                <div class='card-body'>
                    <div class='container'>
                        <div class='row'>
                            <div class='col-sm-8'>
                                <strong>Message</strong><br>
                                <hr style='width: 20px; border: 2px solid #ccc' class='m-0'>
                                <br>
                                {!! html_entity_decode($schedule->message) !!}
                                <div class='pt-3 pb-3'>
                                    <a href='{{url("dashboard/communication/schedule/sms/cancel/".$schedule->id)}}' class='text-danger'>Cancel Message</a>
                                </div>
                            </div>
                            <div class='col-sm'>
                                <div class='alert alert-success'>
                                    Message Will be sent to: <strong><br> {{$schedule->firstname == null?($schedule->name==null?"ALL MEMBERS":$schedule->name):$schedule->firstname." ".$schedule->lastname." (".$schedule->phone.")"}}</strong> <br>
                                    ON: <strong class='text-dark'>{{\Carbon\Carbon::parse($schedule->schedule)->format('d M, Y h:i A')}}</strong>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
@endsection
