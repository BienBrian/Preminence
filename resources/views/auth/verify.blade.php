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
            <div class="col-md-8 col-md-6 col-lg-5 mt-5 ">
                <h4 class='big bold text-white text-center'>Verify Email</h4>
                <div class="card border bg-white border-0 mb-4">
                    <!--
                        <div class="card-header p-3 br-white">
                            <h3 class='text-primary'><i class="fa-sharp fa-regular fa-user text-muted"></i> {{ __('Register') }}</h3>
                        </div>-->

                    <div class="card-body p-4">
                        <div class='ps-4 pe-4 text-center'>
                            <img src='{{ asset('images/logos/primary_icon.png') }}' class="img-fluid" />
                            <h4 style='font-weight:bold;'>{{ __('Verify Your Email Address') }}</h4>

                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif

                            {{ __('Before proceeding, please check your email for a verification link.') }}
                            {{ __('If you did not receive the email') }},
                            <br><br>
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit"
                                    class="btn btn-primary align-baseline">{{ __('click here to request another') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
