@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./website/homepage/home.jpg'); background-size: cover; background-position: center top; min-height: 90vh;">
    <!-- Mask -->
    <span class="mask bg-gradient-primary opacity-8"></span>
    <!-- Header container -->
    <div class="container d-flex align-items-center">
        <div class="row">
            <div class="col-lg-6 col-md-8">
                <h1 class="text-white big">{{($homepage == null)?"Default Header":$homepage->title}}</h1>
                <p class="text-white mt-0 mb-5">{!!($homepage == null)?"Default Header":html_entity_decode($homepage->description)!!}</p>
                <a href="{{url('login')}}" class="btn bg-indigo text-light" style="border-radius: 100px;">Send prayer request</a>
            </div>
        </div>
    </div>
</div>

<!-- Pastors Message -->
<div class="container mt-4">
    <div class="row d-flex align-items-center">
        <div class="col-xl-6 order-xl-2 mb-5 mb-xl-0">
            <img src="{{$message == null ? asset('website/default.png') : ($message->image == '' ? asset('website/default.png') : asset('website/pastors/'.$message->image))}}" class="img-fluid">
        </div>
        <div class="col-xl-6 order-xl-2 mb-5 mb-xl-0">
            <h4 class="display-4 mb-sm-5 mt-sm-5">{{$message == null ? "Pastor's Message Here" : $message->title}}</h4>
            {!!$message == null ? "Pastor's Message Here" : html_entity_decode($message->description)!!}
            <p class="font-weight-bold mb-sm-5 text-right">Senior Pastor</p>
        </div>
    </div>
</div>

<!-- Communities -->
<div class="container-fluid mt-4 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4">We are a community</h3>
                <p>We have grouped ourselves into communities that serve under the union of Christ</p>
            </div>
            @foreach($communities as $community)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{$community->banner == null ? asset('website/default.jpg') :  asset('peoples/'.$community->banner)}}" class="img-fluid">
                    <small class='text-white'>.</small>
                    <h3 class="display-5 mb-sm-3">{{$community->name}}</h3>
                    <p class='text-dark'>{!! html_entity_decode(\Str::words($community->description, 35, '...')) !!}</p>
                    <p class="font-weight-bold mb-sm-5 text-center">
                        <a href="{{url('communities/'.$community->id)}}" class="text-indigo">Learn More...</a>
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Articles -->
<div class="container-fluid mt-4">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4 text-center">Featured Articles</h3>
            </div>
            @foreach($articles as $article)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{$article->banner == null ? asset('website/default.jpg') :  asset('article/'.$article->banner)}}" class="img-fluid">
                    <small class='text-white'>.</small>
                    <h3 class="display-5 mb-sm-3">{{$article->title}}</h3>
                    <p>{!! html_entity_decode(\Str::words($article->description, 35, '...')) !!}</p>
                    <p class="font-weight-bold mb-sm-5 text-center">
                        <a href="{{url('articles/'.$article->id)}}" class="text-indigo">Learn More...</a>
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- sermons and sermon notes -->
<div class="container-fluid mt-4 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4 text-center">Sermon and Sermon Notes</h3>
            </div>
            @foreach($sermons as $sermon)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{$sermon->banner == null ? asset('website/default.jpg') :  asset('sermon/'.$sermon->banner)}}" class="img-fluid">
                    <small class='text-white'>.</small>
                    <h3 class="display-5 mb-sm-3">{{$sermon->title}}</h3>
                    {!! html_entity_decode(\Str::words($sermon->description, 35, '...')) !!}
                    <p class="font-weight-bold mb-sm-5 text-center">
                        <a href="{{url('sermons/'.$sermon->id)}}" class="text-indigo">Learn More...</a>
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Order of Services -->
<div class="container-fluid pt-4 text-white pb-4 bg-primary">
    <div class="container mb-4">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4 text-center text-white">Our fellowship programs</h3>
            </div>
            <div class="col-md-6 col-md-3 order-xl-2 mb-5 mb-xl-0">
                <h2 class="mt-3 mb-3 text-white">What we do on Sunday</h2>
                <div class="row">
                    <div class="col-6 font-weight-bold text-white">
                        Time
                    </div>
                    <div class="col-6 font-weight-bold text-white">
                        Activity
                    </div>
                    @foreach($services as $service)
                        @if($service->day == "Sunday")
                        <div class="col-6">
                            <p class='text-white'>{{$service->time}}</p>
                        </div>
                        <div class="col-6">
                            <p class='text-white font-weight-bold'>{{$service->description}}</p>
                        </div>
                        @endif
                     @endforeach
                </div>
            </div>

            <div class="col-md-6 col-md-3 order-xl-2 mb-5 mb-xl-0 border-left">
                <h2 class="mt-3 mb-4 text-white">Weekly Fellowship</h2>
                <div class="row">
                    <div class="col-4 text-white font-weight-bold">
                        Day
                    </div>
                    <div class="col-4 text-white font-weight-bold">
                        Time
                    </div>
                    <div class="col-4 text-white font-weight-bold">
                        Activity
                    </div>
                    @foreach($services as $service)
                        @if($service->day != "Sunday")
                        <div class="col-4">
                            <p class='text-white'>{{$service->day}}</p>
                        </div>
                        <div class="col-4">
                            <p class='text-white'>{{$service->time}}</p>
                        </div>
                        <div class="col-4">
                            <p class='text-white'>{{$service->description}}</p>
                        </div>
                        @endif
                     @endforeach
                </div>
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
