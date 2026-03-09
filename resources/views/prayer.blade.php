@extends('layouts.app')

@section('content')

<!-- Hero Header -->
<section class="hero-section hero-short" style="background-image: url('{{ asset('images/prayer.jpg') }}');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-transparent p-0 justify-content-center">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white" style="opacity:0.8;">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/spiritual') }}" class="text-white" style="opacity:0.8;">Spiritual</a></li>
                <li class="breadcrumb-item text-white active" style="opacity:0.6;" aria-current="page">Prayer</li>
            </ol>
        </nav>
        <span class="hero-badge"><i class="fas fa-praying-hands mr-1"></i> Prayer</span>
        <h1 class="hero-title">{{ $prayer->title }}</h1>
    </div>
</section>

<!-- Prayer Content -->
<section class="section section-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="icon-wrap icon-wrap-primary icon-wrap-lg mx-auto mb-3">
                                <i class="fas fa-praying-hands"></i>
                            </div>
                            <h2 class="font-weight-bold">{{ $prayer->title }}</h2>
                            <p class="text-muted">
                                <i class="far fa-calendar-alt mr-1"></i> 
                                {{ \Carbon\Carbon::parse($prayer->created_at)->format('d M, Y') }}
                            </p>
                        </div>
                        
                        <div class="prayer-content" style="font-size:1.1rem;line-height:1.9;color:#333;">
                            {!! strip_tags($prayer->description, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote>') !!}
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <a href="{{ url('/spiritual') }}" class="btn btn-outline-primary" style="border-radius:50px;">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Spiritual
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
