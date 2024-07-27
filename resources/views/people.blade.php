@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('./images/people.jpg'); background-size: cover; background-position: center top; min-height: 60vh;">
    <!-- Mask -->
    <span class="mask bg-gradient-dark opacity-8"></span>
    <!-- Header container -->
    <div class="container d-flex align-items-center">
        <div class="row">
            <div class="col-lg-5 col-md-8">
                <h1 class="display-4 text-white mt-4">We are a strong community embracing all people without discrimination</h1>
                <p class="text-white mt-0 mb-5">We have different communities that support each other in the journey of faith. The Book of Amon encourages us to walk together.</p>
            </div>
        </div>
    </div>
</div>

<!-- Departments -->
<div class="container-fluid pt-4">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4">Church Leaders</h3>
                <p>We have a team of God-fearing and supportive leaders</p>
            </div>
            <!--
            <div class="col-sm-6">
                <div id="carouselExampleCaptions" class="carousel slide mb-4" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="{{asset('images/Rev. Hosea.png')}}" class="d-block w-100">
                            <div class="carousel-caption">
                                <span class="mask bg-gradient-dark opacity-3"></span>
                                <p class="">Rev. Hosea</p>
                                <p class="font-weight-bold">Senior Pastor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div id="carouselExampleCaptions" class="carousel slide mb-4" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="{{asset('images/Pst. Zipporah.png')}}" class="d-block w-100">
                            <div class="carousel-caption">
                                <span class="mask bg-gradient-dark opacity-3"></span>
                                <p class="">Pst. Zipporah</p>
                                <p class="font-weight-bold">Pastor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>-->
            <div class="col-sm-6">
                <div id="carouselExampleCaptions" class="carousel slide mb-4" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="{{$senior == null?asset('images/people.jpg'):($senior->image == ''?asset('images/people.jpg'):asset('profile_images/'.$senior->image))}}" class="d-block w-100">
                            <div class="carousel-caption">
                                <p><span class='badge text-white' style='background-color: rgba(0,0,0,0.3);'>{{$senior == null?"Not Set":$senior->firstname." ".$senior->lastname}}
                                <br><br>Senior Pastor</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="row">
                    @foreach($pastors as $pastor)
                    <div class="col-sm-6 mb-4">
                        <div id="carouselExampleCaptions" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="{{$pastor->image == ''?asset('images/people.jpg'):asset('profile_images/'.$pastor->image)}}" class="d-block w-100">
                                    <div class="carousel-caption">
                                        <p><span class='badge text-white' style='background-color: rgba(0,0,0,0.3);'>{{$pastor->firstname}} {{$pastor->lastname}}<br><br>Pastor</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Communities -->
<div class="container-fluid mt-4 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4">Church Ministries</h3>
                <p>We encourage our members to join at least one ministry and share the good news</p>
            </div>
            @foreach($communities as $community)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{$community->banner == null ? asset('website/default.jpg') :  asset('peoples/'.$community->banner)}}" class="img-fluid">
                    <small class='text-white'>.</small>
                    <h3 class="display-5 mb-sm-3">{{$community->name}}</h3>
                    <p>{!!html_entity_decode(\Str::words($community->description, 35, '...')) !!}</p>
                    <p class="font-weight-bold mb-sm-5 text-center">
                        <a href="{{url('communities/'.$community->id)}}" class="text-indigo">Learn More...</a>
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Departments -->
<div class="container-fluid mt-4">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4">Church Departments</h3>
                <p>Together, these departments enhance efficiency in the delivery of the Word of God</p>
            </div>
            @foreach($departments as $department)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{$department->banner == null ? asset('website/default.jpg') :  asset('peoples/'.$department->banner)}}" class="img-fluid">
                    <small class='text-white'>.</small>
                    <h3 class="display-5 mb-sm-3">{{\Str::words($department->name, 7, '...')}}</h3>
                    <p>{!!html_entity_decode(\Str::words($department->description, 35, '...')) !!}</p>
                    <p class="font-weight-bold mb-sm-5 text-center">
                        <a href="{{url('departments/view/'.$department->id)}}" class="text-indigo">Learn More...</a>
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Articles -->
<div class="container-fluid mt-4 bg-white">
    <div class="container">
        <div class="row">
            <div class="col-12 mt-4 mb-4">
                <h3 class="display-4">Insights from our people</h3>
                <p>These articles have been written by our members and affiliate communities for the enlightenment of all</p>
            </div>
            @foreach($articles as $article)
                <div class="col-md-4 col-md-3 order-xl-2 mb-5 mb-xl-0 seminar">
                    <img src="{{$article->banner == null ? asset('website/default.jpg') :  asset('article/'.$article->banner)}}" class="img-fluid">
                    <small class='text-white'>.</small>
                    <h3 class="display-5 mb-sm-3">{{\Str::words($article->title, 7, '...')}}</h3>
                    {!! html_entity_decode(\Str::words($article->description, 35, '...')) !!}
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
                <p class=" text-center">We strive to impact lives and help people to reach their destinies. We've done it in the past.</p>
            </div>
            <div class="col-md-8 col-lg-6 mt-4 mb-4">

                <div id="demo" class="carousel slide" data-ride="carousel">
                    <!-- Indicators -->
                    <ul class="carousel-indicators">
                        <?php $i = 0; ?>
                        @foreach($testimonials as $testimonial)
                            <li data-target="#demo" data-slide-to="{{$i}}" class="{{$i == 0?'active':''}}"></li>
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
