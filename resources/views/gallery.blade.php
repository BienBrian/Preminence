@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./images/gallery.jpg'); background-size: cover; background-position: center top; min-height: 60vh;">
    <!-- Mask -->
    <span class="mask bg-gradient-dark opacity-8"></span>
    <!-- Header container -->
    <div class="container d-flex align-items-center">
        <div class="row">
            <div class="col-sm-12">
                <h1 class="display-4 text-white mt-4">Church Photo Gallery</h1>
            </div>
        </div>
    </div>
</div>

<!-- Communities -->
<div class="container-fluid pt-4">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-10 col-md-8 col-lg-6 mt-4 mb-4">
                <h3 class="display-3 text-center">See our church and activities of the past in pictures.</h3>
                <p class="text-center">We update our gallery every week to reflect all the updates and the fun activities that we pursue as a community</p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class='col-sm-12 gallery'>
            @foreach($galleries as $gallery)
                <img src="{{asset('website/gallery/'.$gallery->image)}}" alt="{{$gallery->description}}" class="img-fluid">
                <!--<p class='text-muted small font-weight-bold'>
                    {{$gallery->description}}
                </p>-->
            @endforeach
            </div>
        </div>
        <div class="row d-flex justify-content-center">
            {{$galleries->links()}}
        </div>
    </div>
</div>

<!-- Articles -->
<div class="container-fluid mt-4 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4 text-center">Learn from our people</h3>
                <p class="text-center">These articles have been written by our members and affiliate communities for the enlightenment of all</p>
            </div>
            @foreach($articles as $article)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <a href="{{url('articles/'.$article->id)}}" class="text-dark">
                        <img src="{{$article->banner == null ? asset('website/default.jpg') :  asset('article/'.$article->banner)}}" class="img-fluid">
                        <small class='text-white'>.</small>
                        <h3 class="display-5 mb-sm-3">{{$article->title}}</h3>
                        <p class='text-muted'>{!! html_entity_decode(\Str::limit($article->description, $limit = 250, $end = '...')) !!}</p>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Order of Services -->
<div class="container-fluid pt-4 pb-4">
    <div class="container mb-4">
        <div class="row d-flex justify-content-center">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4 text-center">Testimonials</h3>
                <p class="text-center">We strive to impact lives and help people to reach their destinies. We've done it in the past.</p>
            </div>
            <div class="col-md-8 col-lg-6 mt-4 mb-4">

                <div id="demo" class="carousel slide" data-ride="carousel">
                    <!-- Indicators -->
                    <ul class="carousel-indicators">
                        <?php $i = 0; ?>
                        @foreach($testimonials as $testimonial)
                            <li data-target="#demo" data-slide-to="{{$i}}" class="bg-primary {{$i == 0?'active':''}}"></li>
                        <?php $i++ ?>
                        @endforeach
                    </ul>

                    <!-- The slideshow -->
                    <div class="carousel-inner">
                        <?php $i = 0; ?>
                        @foreach($testimonials as $testimonial)
                            <div class="carousel-item {{$i == 0?' active':''}}">
                                <div class="row">
                                    <div class="col-sm-12 d-flex justify-content-center">
                                        <img src="{{$testimonial->image == "" ? asset('profile_images/default.jpg'): asset('profile_images/'.$testimonial->image)}}" class="rounded-circle">
                                    </div>
                                    <div class="col-sm-12 p-4 text-center mb-5">
                                        <p><i class="fas fa-quote-left"></i> {{$testimonial->testimonial}} <i class="fas fa-quote-right"></i></p>
                                        <p class="font-weight-bold">{{$testimonial->firstname}} {{$testimonial->lastname}}</p>
                                        <p>Member/User</p>
                                    </div>
                                </div>
                            </div>
                            <?php $i++ ?>
                        @endforeach

                    </div>
                </div>

                <div class="text-center">
                    <a href="{{url('login')}}" class="btn btn-primary" style="border-radius: 100px;">Share your experience</a>
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
