@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center background d-flex align-items-center">
            <!--<div class="col-md-8 col-md-6 col-lg-7">
                    <div class='row justify-content-center'>
                        <div class='col-sm-6 p-5'>
                            <img src='{{ asset('images/logos/Primary Logo.png') }}' class="img-fluid"/>
                        </div>
                    </div>
                </div>-->
            <div class="col-md-8 col-md-6 col-lg-5 mt-5">
                <h4 class='big bold text-white text-center'>Account Status</h4>
                <div class="card border bg-white border-0 mb-4">

                    <div class="card-body p-4">
                        <div class='ps-4 pe-4 text-center'>
                            <img src='{{ asset('images/logos/primary_icon.png') }}' class="img-fluid" />
                            @php
                            $status = "Pending";
                            $class = 'text-muted';
                            if(auth()->user()->approval_status == 2){
                                $status = "Rejected";
                                $class = "text-danger";
                            }
                            @endphp
                            <i class='fas {{ $status=='Pending'?'fa-history':'fa-exclamation-triangle' }} fa-3x {{ $class }}'></i><br>
                            <h4 style='font-weight:bold;'>Your account is <span class="{{ $class }}">{{ $status }}<span></h4>
                            @if($status == 'Pending')
                                We're reviewing your account. This usually takes some few minutes but can last for a maximum of
                                24 hours. We will notify you via email <b>({{ auth()->user()->email }})</b> once verification is complete.
                            @else
                                We are sorry! You did not pass the verification process and your account has been <b>suspended</b>.
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
