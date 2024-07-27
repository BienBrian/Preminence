@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-microphone'></i> Sermons</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Sermons</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-xl-12">

                    <div class="card shadow mb-2">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="mb-0"><i class="fas fa-microphone"></i> | Sermons</h3>
                                </div>
                                <div class="col text-right">
                                    <a href="{{ url('dashboard/spiritual/sermons/new') }}" class="btn btn-primary btn-sm"><i
                                            class='fas fa-arrow-right'></i> New</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if ($sermons->isEmpty())
                            <div class='col-sm-12'>
                                <div class="alert alert-warning text-center">
                                    <i class='fas fa-ban'></i> No communities yet
                                </div>
                            </div>
                        @endif
                        @foreach ($sermons as $sermon)
                            <div class="container">
                                <div class="row bg-white shadow mb-2">
                                    <div class="col-sm-4">
                                        @if ($sermon->youtube != '#')
                                            <div class="video-container">
                                                <iframe src="https://www.youtube.com/embed/{!! Youtube::parseVidFromURL($sermon->youtube) !!}"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                            </div>
                                        @else
                                            <img src="{{ $sermon->banner == '' ? asset('website/default.jpg') : asset('sermon/' . $sermon->banner) }}"
                                                class="img-fluid">
                                        @endif

                                    </div>
                                    <div class="col-sm-8 p-3">
                                        <h3>{{ \Str::limit($sermon->title, $limit = 30, $end = '...') }}</h3>
                                        <span>
                                            <strong>Posted:</strong>
                                            {{ \Carbon\Carbon::parse($sermon->sermondate)->format('d, M Y') }}
                                            {{ $sermon->time }}
                                        </span>
                                        {!! html_entity_decode(\Str::words($sermon->description, $limit = 35, $end = '...')) !!}
                                        <p class='text-right'>
                                            <a href="{{ url('dashboard/spiritual/sermons/edit/' . $sermon->id) }}"
                                                class='btn btn-primary btn-sm'>Edit</a>
                                            <a href="{{ url('dashboard/spiritual/sermons/delete/' . $sermon->id) }}"
                                                class='btn btn-danger btn-sm'>Delete</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class='col-sm-12 mt-2 mb-2'>
                        {{ $sermons->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
