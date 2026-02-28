@extends('layouts.app')

@section('content')
@php
    $prayerIcons = ['fa-praying-hands', 'fa-cross', 'fa-dove', 'fa-hands', 'fa-bible', 'fa-church'];
    $prayerColors = ['icon-wrap-primary', 'icon-wrap-success', 'icon-wrap-warning', 'icon-wrap-info', 'icon-wrap-indigo', 'icon-wrap-teal'];
    $sermonAccents = ['', 'sermon-accent-success', 'sermon-accent-warning'];
    $articleAccents = ['card-accent', 'card-accent-success', 'card-accent-warning'];
    $date = \Carbon\Carbon::now()->setTimezone('Africa/Nairobi');
    $upcoming = \DB::table('sermons')->where("sermondate", ">=", $date->format('Y-m-d'))->orderBy('sermondate')->limit(3)->get();
@endphp

<!-- Hero -->
<section class="hero-section hero-short" style="background-image: url('./images/spiritual.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-dove mr-1"></i> Spiritual Growth</span>
        <h1 class="hero-title">Grow Deeper in Faith</h1>
        <p class="hero-subtitle">Get prayer guides, spiritual guidance, and our recorded sermons and sermon notes from past meetings.</p>
    </div>
</section>

<!-- Upcoming Sermons Banner -->
@if(!$upcoming->isEmpty())
<section class="section-sm section-gray">
    <div class="container">
        <div class="text-center mb-4">
            <span class="section-label">Coming Up</span>
            <h2 class="section-heading">Upcoming Sermons</h2>
        </div>
        <div class="row">
            @foreach($upcoming as $u)
                <div class="col-lg-4 mb-3">
                    <div class="upcoming-sermon-banner" style="flex-direction:column; text-align:center;">
                        <div>
                            <span style="font-size:.72rem; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; opacity:.7;">UPCOMING</span>
                            <h4 class="mt-1">{{ \Str::words($u->title, 6, '...') }}</h4>
                            <p class="sermon-meta mb-2"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($u->sermondate)->format('d M, Y') }} {{ $u->time }} EAT</p>
                            <a href="{{ url('sermons/'.$u->id) }}" class="btn btn-sm btn-outline-light"><i class="fas fa-play mr-1"></i> Watch / View</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Prayer Items -->
<section class="section section-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Prayer</span>
            <h2 class="section-heading">In Our Prayer Items</h2>
            <p class="section-subheading">What do you want to pray for today?</p>
        </div>
        <div class="row">
            @foreach($prayers as $prayer)
                <div class="col-md-4 mb-4">
                    <div class="prayer-card">
                        <div class="prayer-icon">
                            <i class="fas {{ $prayerIcons[$loop->index % 6] }}"></i>
                        </div>
                        <h5>{{ $prayer->title }}</h5>
                        <p>{{ \Str::words(strip_tags($prayer->description), 25, '...') }}</p>
                        <a href="{{ url('prayers/view/'.$prayer->id) }}" class="card-link">Read More <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
        @if($prayers->hasPages())
            <div class="pagination-modern mt-3">
                {{ $prayers->links() }}
            </div>
        @endif
    </div>
</section>

<!-- Sermons -->
<section class="section section-gray">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">The Word</span>
            <h2 class="section-heading">Dive Deeper Into the Word</h2>
            <p class="section-subheading">Sermons and sermon notes from our services</p>
        </div>
        <div class="row">
            @foreach($sermons as $sermon)
                <div class="col-md-4 mb-4">
                    <div class="sermon-card {{ $sermonAccents[$loop->index % 3] }}">
                        @if(isset($sermon->youtube) && $sermon->youtube != "#")
                            <div class="mb-3" style="border-radius:10px; overflow:hidden; aspect-ratio:16/9;">
                                <iframe src="https://www.youtube.com/embed/{!! Youtube::parseVidFromURL($sermon->youtube) !!}"
                                    frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen style="width:100%; height:100%;"></iframe>
                            </div>
                        @else
                            <div class="sermon-icon"><i class="fas fa-bible"></i></div>
                        @endif
                        <h5>{{ \Str::words($sermon->title, 8, '...') }}</h5>
                        @if(isset($sermon->sermondate))
                            <p class="mb-2" style="font-size:.78rem; color:#8898aa;">
                                <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($sermon->sermondate)->format('d M, Y') }}
                                @if(isset($sermon->time)) {{ $sermon->time }} @endif
                            </p>
                        @endif
                        <p>{{ \Str::words(strip_tags($sermon->description), 20, '...') }}</p>
                        <a href="{{ url('sermons/'.$sermon->id) }}" class="card-link">Listen / Read <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
        @if($sermons->hasPages())
            <div class="pagination-modern mt-3">
                {{ $sermons->links() }}
            </div>
        @endif
    </div>
</section>

<!-- Articles -->
@if(count($articles) > 0)
<section class="section section-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Insights</span>
            <h2 class="section-heading">From Our People</h2>
            <p class="section-subheading">Articles written by our members for the enlightenment of all</p>
        </div>
        <div class="row">
            @foreach($articles as $article)
                <div class="col-md-4 mb-4">
                    <div class="article-card-modern">
                        <div class="{{ $articleAccents[$loop->index % 3] }}"></div>
                        <div class="card-body">
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
@endif

<!-- Testimonials -->
@if(count($testimonials) > 0)
<section class="section section-gray">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Stories</span>
            <h2 class="section-heading">Testimonials</h2>
            <p class="section-subheading">We strive to impact lives and help people to reach their destinies</p>
        </div>
        <div id="testimonial-carousel" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                @php $ti = 0; @endphp
                @foreach($testimonials as $testimonial)
                    <div class="carousel-item {{ $ti == 0 ? 'active' : '' }}">
                        <div class="testimonial-card">
                            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                            <img src="{{ $testimonial->image == '' ? asset('profile_images/default.jpg') : asset('profile_images/'.$testimonial->image) }}"
                                 class="testimonial-avatar" alt="{{ $testimonial->firstname }}">
                            <p class="testimonial-quote">{{ $testimonial->testimonial }}</p>
                            <p class="testimonial-name">{{ $testimonial->firstname }} {{ $testimonial->lastname }}</p>
                            <p class="testimonial-role">Church Member</p>
                        </div>
                    </div>
                    @php $ti++; @endphp
                @endforeach
            </div>
            @if(count($testimonials) > 1)
                <div class="text-center mt-4">
                    <a href="#testimonial-carousel" data-slide="prev" class="text-indigo mr-3"><i class="fas fa-chevron-left"></i></a>
                    <a href="#testimonial-carousel" data-slide="next" class="text-indigo ml-3"><i class="fas fa-chevron-right"></i></a>
                </div>
            @endif
        </div>
        <div class="text-center mt-4">
            <a href="{{ url('login') }}" class="btn btn-primary" style="border-radius:50px; padding:.6rem 2rem; font-weight:600;">
                <i class="fas fa-pen mr-1"></i> Share Your Experience
            </a>
        </div>
    </div>
</section>
@endif

@endsection
