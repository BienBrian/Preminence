@extends('layouts.app')

@section('content')
@php
    $communityIcons = ['fa-users', 'fa-hands-helping', 'fa-dove'];
    $communityColors = ['icon-wrap-primary', 'icon-wrap-success', 'icon-wrap-warning'];
    $articleAccents = ['card-accent', 'card-accent-success', 'card-accent-warning'];
    $sermonAccents = ['', 'sermon-accent-success', 'sermon-accent-warning'];
@endphp

<!-- Hero -->
<section class="hero-section" style="background-image: url('./website/homepage/home.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-church mr-1"></i> Welcome to {{ $site_settings->name ?? 'Our Church' }}</span>
        <h1 class="hero-title">{{ ($homepage == null) ? "Experience Faith, Hope & Love" : $homepage->title }}</h1>
        <p class="hero-subtitle">{!! ($homepage == null) ? "Join us every Sunday as we grow together in faith and community." : html_entity_decode($homepage->description) !!}</p>
        <div class="hero-cta">
            <a href="{{ url('/prayer-wall') }}" class="btn btn-light"><i class="fas fa-pray mr-1"></i> Prayer Request</a>
            <a href="#" class="btn btn-outline-light" data-toggle="modal" data-target="#donate"><i class="fas fa-heart mr-1"></i> Donate</a>
        </div>
    </div>
    <a href="#pastor-section" class="hero-scroll"><i class="fas fa-chevron-down"></i></a>
</section>

<!-- Pastor's Message -->
<section id="pastor-section" class="section pastor-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0 text-center">
                <img src="{{ $message == null ? asset('website/default.png') : ($message->image == '' ? asset('website/default.png') : asset('website/pastors/'.$message->image)) }}"
                     class="pastor-image" alt="Senior Pastor">
            </div>
            <div class="col-lg-7">
                <span class="pastor-label"><i class="fas fa-cross mr-1"></i> Pastor's Message</span>
                <h2 class="section-heading">{{ $message == null ? "Pastor's Message" : $message->title }}</h2>
                <div class="mt-3" style="color: #4a5568; line-height: 1.8;">
                    {!! $message == null ? "<p>A warm welcome from our senior pastor.</p>" : html_entity_decode($message->description) !!}
                </div>
                <p class="pastor-signature">&mdash; Senior Pastor</p>
            </div>
        </div>
    </div>
</section>

<!-- Communities -->
<section class="section section-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Our Community</span>
            <h2 class="section-heading">We Are a Community</h2>
            <p class="section-subheading">We have grouped ourselves into communities that serve under the union of Christ</p>
        </div>
        <div class="row">
            @foreach($communities as $community)
                <div class="col-md-4 mb-4">
                    <div class="icon-card">
                        <div class="icon-wrap {{ $communityColors[$loop->index % 3] }}">
                            <i class="fas {{ $communityIcons[$loop->index % 3] }}"></i>
                        </div>
                        <h5>{{ $community->name }}</h5>
                        <p>{{ \Str::words(strip_tags($community->description), 25, '...') }}</p>
                        <a href="{{ url('communities/'.$community->id) }}" class="card-link">Learn More <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Articles -->
<section class="section section-gray">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Insights</span>
            <h2 class="section-heading">Featured Articles</h2>
            <p class="section-subheading">Written by our members and affiliate communities for the enlightenment of all</p>
        </div>
        <div class="row">
            @foreach($articles as $article)
                <div class="col-md-4 mb-4">
                    <div class="article-card-modern">
                        <div class="{{ $articleAccents[$loop->index % 3] }}"></div>
                        <div class="card-body">
                            @if(isset($article->created_at))
                                <div class="card-date"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($article->created_at)->format('M d, Y') }}</div>
                            @endif
                            <h5>{{ \Str::words($article->title, 8, '...') }}</h5>
                            <p>{{ \Str::words(strip_tags($article->description), 25, '...') }}</p>
                            <a href="{{ url('articles/'.$article->id) }}" class="card-link">Read More <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Sermons -->
<section class="section section-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">The Word</span>
            <h2 class="section-heading">Sermons &amp; Notes</h2>
            <p class="section-subheading">Grow deeper in faith through our latest sermons and study notes</p>
        </div>
        <div class="row">
            @foreach($sermons as $sermon)
                <div class="col-md-4 mb-4">
                    <div class="sermon-card {{ $sermonAccents[$loop->index % 3] }}">
                        <div class="sermon-icon"><i class="fas fa-bible"></i></div>
                        <h5>{{ \Str::words($sermon->title, 8, '...') }}</h5>
                        <p>{{ \Str::words(strip_tags($sermon->description), 25, '...') }}</p>
                        <a href="{{ url('sermons/'.$sermon->id) }}" class="card-link">Listen / Read <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Fellowship Programs -->
<section class="section schedule-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label" style="color:rgba(255,255,255,.6);">Join Us</span>
            <h2 class="section-heading" style="color:#fff;">Our Fellowship Programs</h2>
            <p class="section-subheading" style="color:rgba(255,255,255,.6);">Come worship and fellowship with us throughout the week</p>
        </div>
        <div class="row">
            <!-- Sunday Services -->
            <div class="col-lg-6 mb-4">
                <div class="schedule-card">
                    <h4><i class="fas fa-sun mr-2" style="color:#fbb140;"></i> Sunday Services</h4>
                    @foreach($services as $service)
                        @if($service->day == "Sunday")
                            <div class="schedule-row">
                                <span class="schedule-activity">{{ $service->description }}</span>
                                <span class="schedule-time">{{ $service->time }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <!-- Weekday Fellowship -->
            <div class="col-lg-6 mb-4">
                <div class="schedule-card">
                    <h4><i class="fas fa-calendar-week mr-2" style="color:#2dce89;"></i> Weekly Fellowship</h4>
                    @foreach($services as $service)
                        @if($service->day != "Sunday")
                            <div class="schedule-row">
                                <span class="schedule-day">{{ $service->day }}</span>
                                <span class="schedule-activity">{{ $service->description }}</span>
                                <span class="schedule-time">{{ $service->time }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Donate CTA -->
<section class="donate-cta">
    <div class="container">
        <div class="donate-icon"><i class="fas fa-heart"></i></div>
        <h3>Support Our Mission</h3>
        <p>Your generous contributions help us serve the community and spread the gospel to all corners of the earth.</p>
        <a href="#" class="btn btn-light" data-toggle="modal" data-target="#donate"><i class="fas fa-hand-holding-heart mr-1"></i> Donate Now</a>
    </div>
</section>

@endsection
