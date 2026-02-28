@extends('layouts.app')

@section('content')
@php
    $eventColors = ['bg-ev-primary', 'bg-ev-success', 'bg-ev-warning', 'bg-ev-info'];
    $seminarAccents = ['', 'sermon-accent-success', 'sermon-accent-warning'];
@endphp

<!-- Hero -->
<section class="hero-section hero-short" style="background-image: url('./images/events.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-calendar-alt mr-1"></i> Get Involved</span>
        <h1 class="hero-title">Events &amp; Seminars</h1>
        <p class="hero-subtitle">All information you need about upcoming seminars and events in our church.</p>
    </div>
</section>

<!-- Events -->
<section class="section section-gray">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Upcoming</span>
            <h2 class="section-heading">Church Events</h2>
        </div>
        <div class="row">
            @foreach($events as $event)
                @php
                    $eventDate = \Carbon\Carbon::parse($event->eventdate);
                    $isPast = \Carbon\Carbon::now() > \Carbon\Carbon::parse($event->eventdate.' '.($event->time ?? ''));
                @endphp
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="event-card">
                        <div class="event-header {{ $eventColors[$loop->index % 4] }}">
                            <div class="event-date-box">
                                <span class="day">{{ $eventDate->format('d') }}</span>
                                <span class="month">{{ $eventDate->format('M') }}</span>
                            </div>
                            <div class="event-title-wrap">
                                <h5>{{ \Str::words($event->title, 5, '...') }}</h5>
                                @if($isPast)
                                    <span>{{ \Carbon\Carbon::parse($event->eventdate.' '.($event->time ?? ''))->diffForHumans() }}</span>
                                @else
                                    <span>{{ \Carbon\Carbon::now()->diffInDays($eventDate) }} day(s) remaining</span>
                                @endif
                            </div>
                        </div>
                        <div class="event-body">
                            <p>{{ \Str::words(strip_tags($event->description), 25, '...') }}</p>
                            <a href="{{ url('events/view/'.$event->id) }}" class="card-link">Learn More <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if($events->hasPages())
            <div class="pagination-modern mt-3">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</section>

<!-- Seminars -->
@if(count($seminars) > 0)
<section class="section section-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Learning</span>
            <h2 class="section-heading">Seminars</h2>
            <p class="section-subheading">Seminars hosted by us for spiritual and personal growth</p>
        </div>
        <div class="row">
            @foreach($seminars as $seminar)
                <div class="col-md-4 mb-4">
                    <div class="sermon-card {{ $seminarAccents[$loop->index % 3] }}">
                        <div class="sermon-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h5>{{ \Str::words($seminar->title, 8, '...') }}</h5>
                        <p>{{ \Str::words(strip_tags($seminar->description), 25, '...') }}</p>
                        <a href="{{ url('seminar/view/'.$seminar->id) }}" class="card-link">Learn More <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                </div>
            @endforeach
        </div>
        @if($seminars->hasPages())
            <div class="pagination-modern mt-3">
                {{ $seminars->links() }}
            </div>
        @endif
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
