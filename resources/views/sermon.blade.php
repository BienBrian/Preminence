@extends('layouts.app')

@section('content')

<!-- Hero Header -->
<section class="hero-section hero-short" style="background-image: url('{{ empty($sermon->banner) ? asset('images/spiritual.jpg') : asset('sermon/'.$sermon->banner) }}');">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-transparent p-0 justify-content-center">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white" style="opacity:0.8;">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/spiritual') }}" class="text-white" style="opacity:0.8;">Spiritual</a></li>
                <li class="breadcrumb-item text-white active" style="opacity:0.6;" aria-current="page">Sermon</li>
            </ol>
        </nav>
        <span class="hero-badge"><i class="fas fa-bible mr-1"></i> Sermon</span>
        <h1 class="hero-title">{{ $sermon->title }}</h1>
        @if(isset($sermon->sermondate))
            <p class="hero-subtitle">
                <i class="far fa-calendar-alt mr-1"></i> 
                {{ \Carbon\Carbon::parse($sermon->sermondate)->format('d M, Y') }}
                @if(isset($sermon->time) && $sermon->time)
                    &bull; {{ $sermon->time }}
                @endif
                @if(isset($sermon->duration) && $sermon->duration)
                    &bull; {{ $sermon->duration }} min
                @endif
            </p>
        @endif
    </div>
</section>

<!-- Sermon Content -->
<section class="section section-white">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- YouTube Video -->
                @if(isset($sermon->youtube) && $sermon->youtube && $sermon->youtube != '#')
                    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px; overflow:hidden;">
                        <div class="card-body p-0">
                            <div style="position:relative; padding-bottom:56.25%; height:0;">
                                <iframe src="https://www.youtube.com/embed/{!! Youtube::parseVidFromURL($sermon->youtube) !!}"
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen 
                                    style="position:absolute; top:0; left:0; width:100%; height:100%;">
                                </iframe>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Audio Player -->
                @if(isset($sermon->audio) && $sermon->audio)
                    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                        <div class="card-body">
                            <h5 class="font-weight-bold mb-3"><i class="fas fa-headphones mr-2"></i> Listen to Sermon</h5>
                            <audio controls style="width:100%;">
                                <source src="{{ asset('sermon/audio/'.$sermon->audio) }}" type="audio/mpeg">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    </div>
                @endif

                <!-- Video Download -->
                @if(isset($sermon->video) && $sermon->video)
                    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                        <div class="card-body">
                            <h5 class="font-weight-bold mb-3"><i class="fas fa-video mr-2"></i> Download Video</h5>
                            <a href="{{ asset('sermon/video/'.$sermon->video) }}" class="btn btn-primary" download>
                                <i class="fas fa-download mr-1"></i> Download Sermon Video
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Sermon Description -->
                <div class="card border-0 shadow-sm" style="border-radius:16px;">
                    <div class="card-body p-5">
                        <h3 class="font-weight-bold mb-4">About This Sermon</h3>
                        <div class="sermon-content" style="font-size:1.1rem;line-height:1.9;color:#333;">
                            {!! strip_tags($sermon->description, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote>') !!}
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
