@extends('layouts.app')

@section('content')

<!-- Hero Header -->
<div class="header pb-6 pt-5 pt-lg-8 d-flex align-items-end" style="background-image: url('{{ empty($article->banner) ? asset("website/homepage/home.jpg") : asset("article/".$article->banner) }}'); background-size: cover; background-position: center; min-height: 450px;">
    <span class="mask" style="background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.1) 100%);"></span>
    <div class="container" style="position:relative;z-index:2;">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-2">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white" style="opacity:0.8;">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('/our_articles') }}" class="text-white" style="opacity:0.8;">Articles</a></li>
                        <li class="breadcrumb-item text-white active" style="opacity:0.6;" aria-current="page">{{ \Str::limit($article->title, 40) }}</li>
                    </ol>
                </nav>
                @if($article->category_name)
                    <a href="{{ url('/our_articles/category/' . $article->category_slug) }}"
                        class="badge mb-3 d-inline-block" style="background-color:{{ $article->category_color }};color:#fff;font-size:0.8rem;padding:6px 14px;">
                        {{ $article->category_name }}
                    </a>
                @endif
                <h1 class="text-white font-weight-bold mb-3" style="font-size:2.5rem;line-height:1.2;">{{ $article->title }}</h1>
                <div class="d-flex align-items-center mb-4">
                    @if($article->avatar)
                        <img src="{{ asset('profiles/' . $article->avatar) }}" class="rounded-circle mr-3" style="width:44px;height:44px;object-fit:cover;border:2px solid rgba(255,255,255,0.3);">
                    @else
                        <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center text-white" style="width:44px;height:44px;font-size:0.9rem;background:rgba(255,255,255,0.2);border:2px solid rgba(255,255,255,0.3);">
                            {{ strtoupper(substr($article->firstname ?? '',0,1)) }}{{ strtoupper(substr($article->lastname ?? '',0,1)) }}
                        </div>
                    @endif
                    <div>
                        <span class="text-white font-weight-bold">{{ $article->firstname ?? '' }} {{ $article->lastname ?? '' }}</span><br>
                        <span class="text-white small" style="opacity:0.7;">
                            <i class="far fa-calendar mr-1"></i> {{ \Carbon\Carbon::parse($article->article_date)->format('d M, Y') }}
                            &nbsp;&bull;&nbsp;
                            <i class="fas fa-clock mr-1"></i> {{ $article->reading_time }} min read
                            &nbsp;&bull;&nbsp;
                            <i class="fas fa-eye mr-1"></i> {{ $article->views }} views
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Explicit Featured Image -->
@if(!empty($article->banner))
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 mt-4">
            <img src="{{ asset('article/' . $article->banner) }}" class="img-fluid rounded shadow-sm w-100" alt="{{ $article->title }}" style="max-height:500px;object-fit:cover;">
        </div>
    </div>
</div>
@endif

<!-- Image Caption -->
@if($article->image_caption)
<div class="container">
    <p class="small text-muted text-right mt-2 mb-0" style="font-style:italic;">
        <i class="fas fa-camera mr-1"></i> {{ $article->image_caption }}
    </p>
</div>
@endif

<!-- Article Content -->
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 mb-5 mt-4">

            <!-- Excerpt / Lead Paragraph -->
            @if($article->excerpt)
            <div class="mb-4 pl-3" style="border-left:4px solid #4e73df;">
                <p class="lead text-muted" style="font-size:1.15rem;line-height:1.7;">{{ $article->excerpt }}</p>
            </div>
            @endif

            <!-- Scripture Reference Callout -->
            @if($article->scripture_reference)
            <div class="card border-0 mb-4" style="background:linear-gradient(135deg,#fff8f0 0%,#fff3e6 100%);border-radius:12px;">
                <div class="card-body d-flex align-items-center">
                    <div class="mr-3">
                        <i class="fas fa-bible fa-2x" style="color:#d4a574;"></i>
                    </div>
                    <div>
                        <span class="small text-muted font-weight-bold text-uppercase">Scripture Reference</span><br>
                        <span class="font-weight-bold" style="color:#8b6914;font-size:1.05rem;">{{ $article->scripture_reference }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Article Body -->
            <div class="article-content" style="font-size:1.1rem;line-height:1.9;color:#333;">
                {!! strip_tags($article->description, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><table><thead><tbody><tr><th><td><div><span><sub><sup><pre><code><hr>') !!}
            </div>

            <!-- Tags -->
            @if(isset($tags) && $tags->count() > 0)
            <div class="mt-4 mb-4 pt-3" style="border-top:1px solid #eee;">
                <i class="fas fa-tags text-muted mr-2"></i>
                @foreach($tags as $tag)
                    <span class="badge badge-light mr-1 mb-1" style="font-size:0.85rem;padding:6px 12px;border-radius:50px;">{{ $tag }}</span>
                @endforeach
            </div>
            @endif

            <!-- Social Sharing -->
            <div class="d-flex align-items-center justify-content-between mt-4 mb-4 p-3" style="background:#f8f9fc;border-radius:12px;">
                <span class="small font-weight-bold text-muted">Share this article:</span>
                <div>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('articles/'.$article->id.'/'.$article->slug)) }}"
                        target="_blank" class="btn btn-sm btn-outline-primary mr-1" style="border-radius:50px;">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(url('articles/'.$article->id.'/'.$article->slug)) }}"
                        target="_blank" class="btn btn-sm btn-outline-info mr-1" style="border-radius:50px;">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($article->title . ' - ' . url('articles/'.$article->id.'/'.$article->slug)) }}"
                        target="_blank" class="btn btn-sm btn-outline-success mr-1" style="border-radius:50px;">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" style="border-radius:50px;" onclick="navigator.clipboard.writeText('{{ url('articles/'.$article->id.'/'.$article->slug) }}');this.innerHTML='<i class=\'fas fa-check\'></i> Copied';setTimeout(()=>{this.innerHTML='<i class=\'fas fa-link\'></i>';},2000);">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>

            <!-- Author Bio Card -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                <div class="card-body d-flex align-items-center">
                    @if($article->avatar)
                        <img src="{{ asset('profiles/' . $article->avatar) }}" class="rounded-circle mr-3" style="width:60px;height:60px;object-fit:cover;">
                    @else
                        <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center bg-primary text-white" style="width:60px;height:60px;font-size:1.2rem;">
                            {{ strtoupper(substr($article->firstname ?? '',0,1)) }}{{ strtoupper(substr($article->lastname ?? '',0,1)) }}
                        </div>
                    @endif
                    <div>
                        <span class="small text-muted">Written by</span><br>
                        <h6 class="mb-0 font-weight-bold">{{ $article->firstname ?? '' }} {{ $article->lastname ?? '' }}</h6>
                    </div>
                </div>
            </div>

            <a href="{{ url('/our_articles') }}" class="btn btn-outline-primary" style="border-radius:50px;">
                <i class="fas fa-arrow-left mr-1"></i> Back to Articles
            </a>
        </div>
    </div>
</div>

<!-- Related Articles -->
@if(isset($related) && $related->count() > 0)
<div class="container-fluid pt-5 pb-5" style="background:#f8f9fc;">
    <div class="container">
        <h4 class="mb-4 font-weight-bold">Related Articles</h4>
        <div class="row">
            @foreach($related as $rel)
                <div class="col-md-4 mb-4">
                    <a href="{{ url('articles/'.$rel->id.'/'.$rel->slug) }}" class="text-dark text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;transition:transform 0.2s;overflow:hidden;"
                            onmouseenter="this.style.transform='translateY(-4px)';"
                            onmouseleave="this.style.transform='';">
                            <div style="position:relative;">
                                <img src="{{ empty($rel->banner) ? asset('website/default.jpg') : asset('article/'.$rel->banner) }}"
                                    class="card-img-top" style="height:180px;object-fit:cover;">
                                @if($rel->category_name)
                                    <span class="badge" style="position:absolute;top:12px;left:12px;background-color:{{ $rel->category_color }};color:#fff;font-size:0.7rem;">
                                        {{ $rel->category_name }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-body">
                                <h6 class="font-weight-bold">{{ $rel->title }}</h6>
                                <p class="text-muted small mb-0">
                                    {{ $rel->firstname }} {{ $rel->lastname }}
                                    &bull; {{ \Carbon\Carbon::parse($rel->article_date)->format('d M, Y') }}
                                    &bull; {{ $rel->reading_time }} min
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection
