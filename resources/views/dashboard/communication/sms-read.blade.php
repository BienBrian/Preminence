@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-comment'></i> View <b>SMS</b></h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/communication/sms') }}">SMS(s)</a></li>
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
                                        {!! html_entity_decode($sms->message) !!}
                                        <div class='text-center m-3'>
                                            <a href='{{ url('dashboard/communication/sms/remove/' . $sms->id) }}'
                                                class='btn btn-danger btn-sm'>Remove</a>
                                        </div>
                                    </div>
                                    <div class='col-sm'>
                                        <div class='alert alert-success'>
                                            Message has been sent to <strong><br>{{ $recipients }} Members</strong> <br>
                                            {{ \Carbon\Carbon::parse($sms->sent)->diffForHumans() }}
                                            {!! $sms->name == ''
                                                ? ''
                                                : "<br><strong class='badge badge-default text-white'>Group: " . $sms->name . '</strong>' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        @foreach ($members as $member)
                            <div class='col-sm-4 mt-3'>
                                <div class="card" style="border: 2px solid rgba(0,0,0,0.1);">
                                    <div class='card-body p-2'>
                                        <div class="user-panel d-flex">
                                            <div class="image shadow">
                                                <img alt="Image placeholder"
                                                    src="{{ $member->image == null ? asset('profile_images/default.jpg') : asset('profile_images/' . $member->image) }}"
                                                    class='img-fluid'>
                                            </div>

                                            <div class="media-body ml-4">
                                                <span class="mb-2 font-weight-bold">
                                                    <span style='text-muted font-weight-bold;'
                                                        style='font-size: 1.3em;'>{{ $member->firstname }}
                                                        {{ $member->lastname }}</span><br>
                                                    +254{{ $member->phone }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach

                        <div class='col-sm-12 mt-2'>
                            {{ $members->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
