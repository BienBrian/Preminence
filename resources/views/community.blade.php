@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./../website/homepage/home.jpg'); background-size: cover; background-position: center top;">
    <!-- Mask -->
    <span class="mask bg-gradient-primary opacity-8"></span>
    <!-- Header container -->
    <div class="container d-flex align-items-center">
        <div class="row">
        </div>
    </div>
</div>

<!-- Item Details -->
<div class="container mt--6">
    <div class="row justify-content-center">
        <div class="col-md-10 mb-5">
            <div class='card' style="border-radius: 0;">
                <div class='card-header'>
                    <h4 class="display-4">{{$community->name}}</h4>
                </div>
                <img src="{{$community->banner == "" ? asset("website/homepage/default.jpg") : asset("peoples/".$community->banner)}}" class="img-fluid">
                <div class='card-body'>
                    <p>{!! html_entity_decode(e($community->description)) !!}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="separator separator-bottom separator-skew zindex-100">
        <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
            <polygon class="fill-white" points="2560 0 2560 100 0 100"></polygon>
        </svg>
    </div>
</div>

@endsection
