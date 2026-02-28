@extends('layouts.app')

@section('content')
@php
    $noticeAccents = ['', 'notice-accent-success', 'notice-accent-warning', 'notice-accent-info'];
@endphp

<!-- Hero -->
<section class="hero-section hero-short" style="background-image: url('./images/calendar.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-bullhorn mr-1"></i> Stay Informed</span>
        <h1 class="hero-title">Notice Board</h1>
        <p class="hero-subtitle">All information you need to know about upcoming activities in our church.</p>
    </div>
</section>

<!-- Notices -->
<section class="section section-gray">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Announcements</span>
            <h2 class="section-heading">Latest Notices</h2>
        </div>
        <div class="row">
            @foreach($notices as $notice)
                <div class="col-md-4 mb-4">
                    <div class="notice-card {{ $noticeAccents[$loop->index % 4] }}">
                        <div class="notice-date">
                            <i class="far fa-calendar-alt mr-1"></i>
                            {{ \Carbon\Carbon::parse($notice->noticedate)->format('M d, Y') }}
                        </div>
                        @if(\Carbon\Carbon::now() > \Carbon\Carbon::parse($notice->noticedate))
                            <span class="notice-badge past"><i class="fas fa-check mr-1"></i> {{ \Carbon\Carbon::parse($notice->noticedate)->diffForHumans() }}</span>
                        @else
                            <span class="notice-badge upcoming"><i class="fas fa-clock mr-1"></i> {{ \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($notice->noticedate)) }} day(s) remaining</span>
                        @endif
                        <h5>{{ $notice->title }}</h5>
                        <p>{{ \Str::words(strip_tags($notice->description), 25, '...') }}</p>
                        <a href="{{ url('notices/view/'.$notice->id) }}" class="card-link">Read More <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
        @if($notices->hasPages())
            <div class="pagination-modern mt-3">
                {{ $notices->links() }}
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
            @php $articleAccents = ['card-accent', 'card-accent-success', 'card-accent-warning']; @endphp
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
