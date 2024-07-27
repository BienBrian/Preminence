@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-6 pt-5 d-flex align-items-center" style="background-image: url('./../website/homepage/home.jpg'); background-size: cover; background-position: center top;">
    <!-- Mask -->
    <span class="mask bg-gradient-primary opacity-8"></span>
    <!-- Header container -->
    <div class="container d-flex align-items-center">
        <div class="row">
        </div>
    </div>
</div>

<!-- Item Details -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 mb-5 mt-3">
            <div class='card' style='border-radius: 0;'>
                <div class='card-header'>
                    <h4 class="display-4">{{$article->title}}</h4>
                </div>
                <img src="{{$article->banner == "" ? asset("website/homepage/default.jpg") : asset("article/".$article->banner)}}" class="img-fluid">
                <div class='card-body'>
                    <p>{!! html_entity_decode(e($article->description))!!}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
