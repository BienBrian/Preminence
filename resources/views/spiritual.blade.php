@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-4 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./images/spiritual.jpg'); background-size: cover; background-position: center top; min-height: 60vh;">
    <!-- Mask -->
    <span class="mask bg-gradient-dark opacity-8"></span>
    <!-- Header container -->
    <div class="container-fluid">
        <div class="row d-flex align-items-center">
            <div class="col-lg-5 col-md-6">
                <h1 class="display-4 text-white mt-4">We exist to support the spiritual growth of our members</h1>
                <p class="text-white mt-0 mb-5">Get prayer guides, spiritual guidance, and our recorded sermons and sermon notes from past meetings</p>
            </div>
            <div class="col-lg-7 col-md-6 justify-content-center">

                <?php
                    $date = \Carbon\Carbon::now()->setTimezone('Africa/Nairobi');
                    $upcoming = \DB::table('sermons')->where("sermondate", ">=", $date->format('Y-m-d'))->get();
                ?>
                @if(!$upcoming->isEmpty())
                <div id="sermonSlider" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php $count = 0; ?>
                        @foreach($upcoming as $sermon)
                            <li data-target="#sermonSlider" data-slide-to="{{$count}}" class="{{$count==0?'active':''}}"></li>
                            <?php $count++; ?>
                        @endforeach
                    </ol>
                    <div class="carousel-inner">
                        <?php $count=0; ?>
                        @foreach($upcoming as $sermon)
                            <div class="carousel-item {{$count==0?'active':''}}">
                                <img src="{{$sermon->banner == ''?asset('website/default.jpg'):asset('sermon/'.$sermon->banner)}}" class="d-block w-100">
                                <div class="carousel-caption">
                                    <h3 class='display-4 text-white'>UPCOMING SERMON</h3>
                                    --------------
                                    <h4 class='display-5 text-white'>{{$sermon->title}}</h4>
                                    <p class='text-white'>Time: {{\Carbon\Carbon::parse($sermon->sermondate)->format('d M, Y')}} {{$sermon->time}} EAT</p>
                                    <a href="{{url('sermons/'.$sermon->id)}}" class="btn btn-outline-white pt-1 pb-1 mb-3"><i class="fas fa-play"></i> Watch/View</a>
                                </div>
                            </div>
                            <?php $count++; ?>
                        @endforeach
                        <a class="carousel-control-prev" href="#sermonSlider" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#sermonSlider" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
                <!-- End Carousel-->
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Communities -->
<div class="container-fluid pt-4">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 mt-4 mb-4">
                <h3 class="display-4">In our prayer items</h3>
                <p>What do you want to pray for today?</p>
            </div>
            @foreach($prayers as $prayer)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{asset("images/spiritual.jpg")}}" class="img-fluid">

                    <h3 class="display-5">{{$prayer->title}}</h3>
                    <small class='font-weight-bold'>
                    <p class='text-muted'>{{\Str::limit($prayer->description, $limit = 250, $end = '...') }}</p>
                    <p class="font-weight-bold mb-sm-5 text-center">
                        <a href="{{url('prayers/view/'.$prayer->id)}}" class="text-indigo">Learn More...</a>
                    </p>
                </div>
            @endforeach
        </div>
        <div class="row d-flex justify-content-center">
            {{$prayers->links()}}
        </div>
    </div>
</div>

<!-- sermons and sermon notes -->
<div class="container-fluid mt-4 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4">Dive deeper into the Word</h3>
            </div>
            @foreach($sermons as $sermon)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">

                    @if($sermon->youtube != "#")
                        <div class="video-container">
                            <iframe src="https://www.youtube.com/embed/{!!Youtube::parseVidFromURL($sermon->youtube)!!}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    @else
                        <img src="{{$sermon->banner == ''?asset('website/default.jpg'):asset('sermon/'.$sermon->banner)}}" class="img-fluid">
                    @endif
                    <a href="{{url('sermons/'.$sermon->id)}}">
                        <div style="color: rgb(100,100,100);">
                            <small class='text-white'>.</small>
                            <h3 class="display-5 mb-sm-3">{{$sermon->title}}</h3>
                            <span class='p-2 small' style="background-color: #dee; border-radius: 4px;">
                                <strong>Service Time:</strong> {{\Carbon\Carbon::parse($sermon->sermondate)->format('d M, Y')}} {{$sermon->time}}
                            </span><br><br>
                            {!! html_entity_decode(\Str::limit(strip_tags($sermon->description), $limit = 100, $end = '...')) !!}
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="row d-flex justify-content-center">
            {{$sermons->links()}}
        </div>
    </div>
</div>

<!-- Articles -->
<div class="container-fluid mt-4">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4">Learn from our people</h3>
                <p>These articles have been written by our members and affiliate communities for the enlightenment of all</p>
            </div>
            @foreach($articles as $article)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{$article->banner == null ? asset("website/homepage/default.jpg") :  asset("article/".$article->banner)}}" class="img-fluid">
                    <small class='text-white'>.</small>
                    <h3 class="display-5 mb-sm-3">{{$article->title}}</h3>
                    <p class='text-muted'>{{\Str::limit($article->description, $limit = 250, $end = '...') }}</p>
                    <p class="font-weight-bold mb-sm-5 text-center">
                        <a href="{{url('articles/'.$article->id)}}" class="text-indigo">Learn More...</a>
                    </p>
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
