@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-4 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./images/shop.png'); background-size: cover; background-position: center top; min-height: 100vh;">
    <!-- Mask -->
    <span class="mask bg-gradient-dark opacity-3"></span>
    <!-- Header container -->
    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-md-5 col-lg-4">
                <h1 class="display-3 text-white mt-4">We are launching very soon.</h1>
                <p class="text-light">Proceeds from purchases of products in the shop will be used to support charity works, church projects, and spreading the gospel further.</p>
                <p><a href="{{url('myshop')}}" class="btn btn-white" style="border-radius: 100px;">Get Notified When We Launch</a></p>
            </div>
        </div>
    </div>
    <div class="separator separator-bottom separator-skew zindex-100">
        <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
            <polygon class="fill-dark" points="2560 0 2560 100 0 100"></polygon>
        </svg>
    </div>
</div>

@endsection
