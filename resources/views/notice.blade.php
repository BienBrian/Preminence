@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./../../website/homepage/home.jpg'); background-size: cover; background-position: center top;">
    <!-- Mask -->
    <span class="mask bg-gradient-primary opacity-8"></span>
    <!-- Header container -->
    <div class="container d-flex align-items-center">
        <div class="row">
        </div>
    </div>
</div>

<!-- Item Details -->
<div class="container mt-4">
    <div class="row">
        <div class="col-xl-6 order-xl-2 mb-5">
            <img src="{{asset("images/calendar.jpg")}}" class="img-fluid">
        </div>
        <div class="col-xl-6 order-xl-2 mb-5">
            <h3 class="display-5">{{$notice->title}}</h3>
            <small class='font-weight-bold'>
                @if(\Carbon\Carbon::now() > \Carbon\Carbon::parse($notice->noticedate))
                    <span class="text-success text-muted">
                        {{\Carbon\Carbon::parse($notice->noticedate)->diffForHumans()}}
                    </span>
                @else
                    <span class="text-success">
                        {{\Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($notice->noticedate))}} day(s) Remaining
                    </span>
                @endif
            </small>
            <p class='text-muted'>{{$notice->description}}</p>
        </div>
    </div>

    <div class="separator separator-bottom separator-skew zindex-100">
        <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
            <polygon class="fill-white" points="2560 0 2560 100 0 100"></polygon>
        </svg>
    </div>
</div>

@endsection
