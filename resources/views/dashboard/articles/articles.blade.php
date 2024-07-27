@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-blog'></i> Articles</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Articles</li>
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
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow mb-3">
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                    <div class="col">
                                        <h3 class="mb-0"><i class="fas fa-comments"></i> | Articles</h3>
                                    </div>-->
                                <div class="col text-right">
                                    <a href="{{ url('dashboard/articles/new') }}" class="btn btn-primary btn-sm"><i
                                            class='fas fa-arrow-right'></i> New</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @if ($articles->isEmpty())
                            <div class='col-sm-12'>
                                <div class='alert alert-warning text-center'>
                                    <i class='fas fa-ban'></i> No articles Yet
                                </div>
                            </div>
                        @endif
                        <div class="col-sm-12">
                            @foreach ($articles as $article)
                                <div class="container">
                                    <div class="row shadow bg-white mb-4">
                                        <div class="col-sm-4">
                                            <img alt="Image placeholder"
                                                src="{{ $article->banner == '' ? asset('website/default.jpg') : asset('article/' . $article->banner) }}"
                                                class="img-fluid">
                                        </div>
                                        <div class="col-sm-8 p-3">
                                            <h3>{{ \Str::limit($article->title, $limit = 20, $end = '...') }}</h3>
                                            <span
                                                class='font-weight-bold'>{{ $article->firstname . ' ' . $article->lastname }}</span>
                                            &nbsp;
                                            @if ($article->status == 1)
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-success"></i> Active
                                                </span>
                                            @else
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-danger"></i> Pending
                                                </span>
                                            @endif
                                            {{ \Carbon\Carbon::parse($article->article_date)->format('d, M Y') }}<br><br>
                                            {!! html_entity_decode(\Str::words($article->description, 50, '...')) !!}
                                            <p class='text-right'>
                                                @if ($article->status == 0)
                                                    <a href="{{ url('dashboard/articles/activate/' . $article->id) }}"
                                                        class="btn btn-success p-1 pr-2 pl-2" title='Activate'><i
                                                            class="fas fa-sync"></i> Activate</a>
                                                @else
                                                    <a href="{{ url('dashboard/articles/deactivate/' . $article->id) }}"
                                                        class="btn btn-warning p-1 pr-2 pl-2" title='Deactivate'><i
                                                            class="fas fa-ban"></i> Deactivate</a>
                                                @endif
                                                <a href="{{ url('dashboard/articles/remove/' . $article->id) }}"
                                                    class='btn btn-danger p-1 pr-2 pl-2' title='Delete'><i
                                                        class="fas fa-trash-alt"></i> Delete</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class='col-sm-12 mt-2 mb-2'>
                            {{ $articles->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
