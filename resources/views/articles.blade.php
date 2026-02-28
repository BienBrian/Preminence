@extends('layouts.app')

@section('content')

<!-- Header -->
<div class="header pb-4 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url('{{ asset("images/article.jpg") }}'); background-size: cover; background-position: center top;">
    <span class="mask bg-gradient-dark opacity-8"></span>
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-sm-10 col-md-8 col-lg-7">
                <h1 class="display-4 text-white mt-4 text-center">Articles & Insights</h1>
                <p class="text-white text-center" style="opacity:0.85;">Discover thoughtful articles on faith, family, ministry and more from our church community.</p>
            </div>
        </div>
    </div>
</div>

<!-- Category Filter Bar -->
<div class="container-fluid bg-white shadow-sm" style="position:sticky;top:0;z-index:100;">
    <div class="container">
        <div class="d-flex align-items-center py-2" style="overflow-x:auto;white-space:nowrap;-webkit-overflow-scrolling:touch;">
            <a href="{{ url('/our_articles') }}"
                class="btn btn-sm mr-2 {{ !isset($category) ? 'btn-primary' : 'btn-outline-secondary' }}"
                style="border-radius:50px;font-size:0.85rem;">
                All
            </a>
            @foreach($categories as $cat)
                <a href="{{ url('/our_articles/category/' . $cat->slug) }}"
                    class="btn btn-sm mr-2 {{ (isset($category) && $category->id == $cat->id) ? '' : 'btn-outline-secondary' }}"
                    style="border-radius:50px;font-size:0.85rem;{{ (isset($category) && $category->id == $cat->id) ? 'background-color:'.$cat->color.';border-color:'.$cat->color.';color:#fff;' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Featured Article Hero -->
@if(isset($featured) && $featured)
<div class="container mt-4">
    <div class="card shadow border-0 mb-4" style="overflow:hidden;border-radius:12px;">
        <div class="row no-gutters">
            <div class="col-md-7">
                <img src="{{ $featured->banner == null ? asset('website/default.jpg') : asset('article/'.$featured->banner) }}"
                    class="img-fluid" style="height:100%;object-fit:cover;width:100%;min-height:300px;">
            </div>
            <div class="col-md-5 d-flex align-items-center">
                <div class="p-4">
                    @if($featured->category_name)
                        <a href="{{ url('/our_articles/category/' . $featured->category_slug) }}"
                            class="badge mb-2" style="background-color:{{ $featured->category_color }};color:#fff;font-size:0.75rem;">
                            {{ $featured->category_name }}
                        </a>
                    @else
                        <span class="badge badge-primary mb-2">Featured</span>
                    @endif
                    <h2 class="mb-2 font-weight-bold">{{ $featured->title }}</h2>
                    <div class="d-flex align-items-center mb-3">
                        @if($featured->avatar)
                            <img src="{{ asset('profiles/' . $featured->avatar) }}" class="rounded-circle mr-2" style="width:32px;height:32px;object-fit:cover;">
                        @else
                            <div class="rounded-circle mr-2 d-flex align-items-center justify-content-center bg-primary text-white" style="width:32px;height:32px;font-size:0.8rem;">
                                {{ strtoupper(substr($featured->firstname,0,1)) }}{{ strtoupper(substr($featured->lastname,0,1)) }}
                            </div>
                        @endif
                        <div>
                            <span class="small font-weight-bold">{{ $featured->firstname }} {{ $featured->lastname }}</span><br>
                            <span class="small text-muted">
                                {{ \Carbon\Carbon::parse($featured->article_date)->format('d M, Y') }}
                                &bull; {{ $featured->reading_time }} min read
                            </span>
                        </div>
                    </div>
                    <p class="text-muted">
                        @if($featured->excerpt)
                            {{ $featured->excerpt }}
                        @else
                            {{ \Str::words(strip_tags($featured->description), 35, '...') }}
                        @endif
                    </p>
                    <a href="{{ url('articles/'.$featured->id.'/'.$featured->slug) }}" class="btn btn-primary" style="border-radius:50px;">
                        Read Article <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Articles Grid -->
<div class="container-fluid mt-4 pt-4 pb-5" style="background:#f8f9fc;">
    <div class="container">
        @if(isset($category))
            <h4 class="mb-4 font-weight-bold">{{ $category->name }} Articles</h4>
        @endif
        <div class="row">
            @forelse($articles as $article)
                <div class="col-md-4 mb-4">
                    <a href="{{ url('articles/'.$article->id.'/'.$article->slug) }}" class="text-dark text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;transition:transform 0.2s,box-shadow 0.2s;overflow:hidden;"
                            onmouseenter="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';"
                            onmouseleave="this.style.transform='';this.style.boxShadow='';">
                            <div style="position:relative;">
                                <img src="{{ $article->banner == null ? asset('website/default.jpg') : asset('article/'.$article->banner) }}"
                                    class="card-img-top" style="height:200px;object-fit:cover;">
                                @if($article->category_name)
                                    <span class="badge" style="position:absolute;top:12px;left:12px;background-color:{{ $article->category_color }};color:#fff;font-size:0.7rem;">
                                        {{ $article->category_name }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-body">
                                <h5 class="card-title font-weight-bold mb-2">{{ $article->title }}</h5>
                                <div class="d-flex align-items-center mb-2">
                                    @if($article->avatar)
                                        <img src="{{ asset('profiles/' . $article->avatar) }}" class="rounded-circle mr-2" style="width:24px;height:24px;object-fit:cover;">
                                    @else
                                        <div class="rounded-circle mr-2 d-flex align-items-center justify-content-center bg-secondary text-white" style="width:24px;height:24px;font-size:0.65rem;">
                                            {{ strtoupper(substr($article->firstname,0,1)) }}{{ strtoupper(substr($article->lastname,0,1)) }}
                                        </div>
                                    @endif
                                    <span class="small text-muted">
                                        {{ $article->firstname }} {{ $article->lastname }}
                                        &bull; {{ \Carbon\Carbon::parse($article->article_date)->format('d M, Y') }}
                                    </span>
                                </div>
                                <p class="card-text text-muted small">
                                    @if($article->excerpt)
                                        {{ \Str::limit($article->excerpt, 120) }}
                                    @else
                                        {{ \Str::words(strip_tags($article->description), 20, '...') }}
                                    @endif
                                </p>
                            </div>
                            <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center">
                                <span class="text-primary font-weight-bold small">Read More <i class="fas fa-arrow-right"></i></span>
                                <span class="small text-muted"><i class="fas fa-clock"></i> {{ $article->reading_time }} min</span>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No articles found</h5>
                    <p class="text-muted">Check back soon for new content.</p>
                </div>
            @endforelse
        </div>
        <div class="row d-flex justify-content-center mb-4">
            {{ $articles->links() }}
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
