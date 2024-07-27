@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-4 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./images/article.jpg'); background-size: cover; background-position: center top;">
    <!-- Mask -->
    <span class="mask bg-gradient-dark opacity-8"></span>
    <!-- Header container -->
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-10 col-md-8 col-lg-7">
                <h1 class="display-4 text-white mt-4 text-center">We have a generous community sharing ideas of spirituality and life through articles.</h1>
            </div>
        </div>
    </div>
</div>

<!-- Pastors Message -->
<div class="container mt-4">
    <div class="row d-flex justify-content-center">
        <div class="col-sm-6 order-xl-2 mb-5 mb-xl-0">
            <p class="text-center">These articles may be updated either daily or weekly. We encourage of members to regularly check out new ideas for enlightenment.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 text-center">
            <a href="{{url('login')}}" class="btn bg-indigo text-light" style="border-radius: 100px;">Write your own</a>
        </div>
    </div>
</div>

<!-- Articles -->
<div class="container-fluid mt-6 pt-4 bg-white">
    <div class="container">
        <div class="row">
            @foreach($articles as $article)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <a href="{{url('articles/'.$article->id)}}" class='text-dark'>
                        <img src="{{$article->banner == null ? asset('website/default.jpg') :  asset('article/'.$article->banner)}}" class="img-fluid">
                        <small class='text-white'>.</small>
                        <h3 class="display-5 mb-sm-3">{{$article->title}}</h3>
                        <p>{!! html_entity_decode(\Str::words(strip_tags($article->description), 35, '...')) !!}</p>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="row d-flex justify-content-center">
            {{$articles->links()}}
        </div>
    </div>
</div>

<!-- Donate-->
<div class="container-fluid pt-4 text-white pb-4 bg-primary">
    <div class="container mb-4">
        <div class="row d-flex justify-content-center">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4 text-center text-white">Support Our Ministry</h3>
            </div>
            <div class="col-sm-10 col-md-8 col-lg-6 order-xl-2 mb-5 mb-xl-0 text-center">
                <p class="text-center mb-4">Our Ministry Goes Beyond Spreading the Good News to supporting the needy, the disabled, and other individuals with reasonable needs</p>
                <button class="btn btn-indigo text-primary mt-4 mb-4" data-toggle="modal" data-target="#donate">Donate</button>
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
