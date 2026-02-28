@extends('layouts.app')

@section('content')
@php
    $ministryIcons = ['fa-users', 'fa-hands-helping', 'fa-dove', 'fa-praying-hands', 'fa-church', 'fa-cross'];
    $ministryColors = ['icon-wrap-primary', 'icon-wrap-success', 'icon-wrap-warning', 'icon-wrap-info', 'icon-wrap-indigo', 'icon-wrap-teal'];
    $deptIcons = ['fa-music', 'fa-microphone-alt', 'fa-book-reader', 'fa-hand-holding-heart', 'fa-child', 'fa-globe-africa'];
    $deptColors = ['icon-wrap-danger', 'icon-wrap-purple', 'icon-wrap-info', 'icon-wrap-success', 'icon-wrap-warning', 'icon-wrap-primary'];
    $articleAccents = ['card-accent', 'card-accent-success', 'card-accent-warning'];
@endphp

<!-- Hero -->
<section class="hero-section hero-short" style="background-image: url('./images/people.jpg');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-badge"><i class="fas fa-users mr-1"></i> Our People</span>
        <h1 class="hero-title">We Are a Strong Community</h1>
        <p class="hero-subtitle">Embracing all people without discrimination. We walk together in faith, love and hope.</p>
    </div>
</section>

<!-- Pastors (images kept) -->
<section class="section section-gray">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Leadership</span>
            <h2 class="section-heading">Church Leaders</h2>
            <p class="section-subheading">A team of God-fearing and supportive leaders</p>
        </div>
        <div class="row align-items-center">
            <!-- Senior Pastor -->
            <div class="col-lg-5 mb-4">
                <div class="senior-pastor-card">
                    <img src="{{ $senior == null ? asset('images/people.jpg') : ($senior->image == '' ? asset('images/people.jpg') : asset('profile_images/'.$senior->image)) }}" alt="Senior Pastor">
                    <div class="pastor-overlay">
                        <h4>{{ $senior == null ? "Not Set" : $senior->firstname." ".$senior->lastname }}</h4>
                        <span>{{ $senior->title ?? 'Senior Pastor' }}</span>
                    </div>
                </div>
            </div>
            <!-- Other Pastors -->
            <div class="col-lg-7">
                <div class="row justify-content-center">
                    @foreach($pastors as $pastor)
                        <div class="col-6 col-sm-4 col-md-3 mb-4">
                            <div class="pastor-thumb">
                                <img src="{{ $pastor->image == '' ? asset('images/people.jpg') : asset('profile_images/'.$pastor->image) }}" alt="{{ $pastor->firstname }}">
                                <h6>{{ $pastor->firstname }} {{ $pastor->lastname }}</h6>
                                <span>{{ $pastor->title ?? 'Pastor' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Ministries (icon hlist-cards, no images) -->
<section class="section section-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Serve Together</span>
            <h2 class="section-heading">Church Ministries</h2>
            <p class="section-subheading">We encourage our members to join at least one ministry and share the good news</p>
        </div>
        <div class="row">
            @foreach($communities as $community)
                <div class="col-md-6 mb-4">
                    <a href="{{ url('communities/'.$community->id) }}" class="hlist-card">
                        <div class="hlist-icon">
                            <div class="icon-wrap icon-wrap-sm {{ $ministryColors[$loop->index % 6] }}">
                                <i class="fas {{ $ministryIcons[$loop->index % 6] }}"></i>
                            </div>
                        </div>
                        <div class="hlist-body">
                            <h5>{{ $community->name }}</h5>
                            <div class="hlist-meta">
                                @if($community->leader_firstname)
                                    <span><i class="far fa-user"></i> {{ $community->leader_firstname }} {{ $community->leader_lastname }}</span>
                                @endif
                                <span><i class="fas fa-users"></i> {{ $community->member_count }} members</span>
                            </div>
                            <p>{{ \Str::words(strip_tags($community->description), 20, '...') }}</p>
                            <span class="card-link">Learn More <i class="fas fa-arrow-right ml-1"></i></span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Departments (icon hlist-cards, no images) -->
<section class="section section-gray">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Organization</span>
            <h2 class="section-heading">Church Departments</h2>
            <p class="section-subheading">Together, these departments enhance efficiency in the delivery of the Word of God</p>
        </div>
        <div class="row">
            @foreach($departments as $department)
                <div class="col-md-6 mb-4">
                    <a href="{{ url('departments/view/'.$department->id) }}" class="hlist-card">
                        <div class="hlist-icon">
                            <div class="icon-wrap icon-wrap-sm {{ $deptColors[$loop->index % 6] }}">
                                <i class="fas {{ $deptIcons[$loop->index % 6] }}"></i>
                            </div>
                        </div>
                        <div class="hlist-body">
                            <h5>{{ \Str::words($department->name, 7, '...') }}</h5>
                            <div class="hlist-meta">
                                @if($department->leader_firstname)
                                    <span><i class="far fa-user"></i> {{ $department->leader_firstname }} {{ $department->leader_lastname }}</span>
                                @endif
                                <span><i class="fas fa-users"></i> {{ $department->member_count }} members</span>
                            </div>
                            <p>{{ \Str::words(strip_tags($department->description), 20, '...') }}</p>
                            <span class="card-link">Learn More <i class="fas fa-arrow-right ml-1"></i></span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Articles -->
<section class="section section-white">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Insights</span>
            <h2 class="section-heading">From Our People</h2>
            <p class="section-subheading">Articles written by our members and affiliate communities for the enlightenment of all</p>
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

<!-- Testimonials -->
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
                    <a href="#testimonial-carousel" data-slide="prev" class="text-indigo mr-3" style="font-size:1.2rem;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    @for($i = 0; $i < count($testimonials); $i++)
                        <span class="d-inline-block mx-1" style="width:10px; height:10px; border-radius:50%; background: {{ $i == 0 ? '#5e72e4' : '#d1d5db' }}; cursor:pointer;"
                              data-target="#testimonial-carousel" data-slide-to="{{ $i }}"></span>
                    @endfor
                    <a href="#testimonial-carousel" data-slide="next" class="text-indigo ml-3" style="font-size:1.2rem;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
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

@endsection
