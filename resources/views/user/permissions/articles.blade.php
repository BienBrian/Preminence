@extends('layouts.user')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
        </div>
    </div>
</div>

<?php
    $permissions1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
    $permissions2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
?>
<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fas fa-comments"></i> | Articles</h3>
                        </div>
                        <div class="col text-right">

                        </div>
                    </div>
                </div>
                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class='fas fa-check-circle'></i> {{ \Session::get('success') }}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class='fas fa-exclamation-circle'></i> {{ \Session::get('error') }}
                    </div>
                @endif
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong><i class='fas fa-exclamation-circle'></i> Whoops!</strong> There were some problems with your input.
                        @foreach ($errors->all() as $error)
                            <br><i class='fas fa-angle-right'></i> {{ $error }}</li>
                        @endforeach
                    </div>
                @endif
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Title</th>
                                <th scope="col">Article</th>
                                <th scope="col">Article By</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date</th>
                                <th scope="col" class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($articles->isEmpty())
                                    <tr><td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No articles yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($articles as $article)
                                <tr>
                                    <td>{{ $count }}</td>
                                    <td scope='row'>
                                        <div class="media align-items-center">
                                            <img alt="Image placeholder" src="{{$article->banner == ""?asset('article/default.png'):asset('article/'.$article->banner)}}" height='40' width='40' class='mr-3' style='border-radius: 50%;'>
                                            <div class="media-body">
                                                <span class="mb-0 text-sm">{{str_limit($article->title, $limit = 20, $end = '...')}}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ str_limit($article->description, $limit = 20, $end = '...') }}</td>
                                    <td>{{ $article->firstname." ".$article->lastname }}</td>
                                    <td>
                                        @if($article->status == 1)
                                            <span class="badge badge-dot mr-4">
                                                <i class="bg-success"></i> Active
                                            </span>
                                        @else
                                            <span class="badge badge-dot mr-4">
                                                <i class="bg-danger"></i> In-active
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{\Carbon\Carbon::parse($article->article_date)->format('d, M Y')}}</td>
                                    <td class='text-right'>
                                        @if($permissions1->articles > 1 || $permissions2->articles > 1)
                                            @if($article->status == 0)
                                                <a href="{{url('articles/activate/'.$article->id)}}" class="btn btn-success p-1 pr-2 pl-2" title='Activate'><i class="fas fa-sync"></i></a>
                                            @else
                                                <a href="{{url('articles/deactivate/'.$article->id)}}" class="btn btn-outline-danger p-1 pr-2 pl-2" title='Deactivate'><i class="fas fa-ban"></i></a>
                                            @endif
                                        @endif
                                        @if($permissions1->articles > 2 || $permissions2->articles > 2)
                                            <a href="{{url('removearticle/'.$article->id)}}" class='btn btn-danger p-1 pr-2 pl-2' title='Delete'><i class="fas fa-trash-alt"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                <?php $count++; ?>
                                @endforeach
                            </tbody>
                        </thead>
                    </table>
                </div>
            </div>

            <div class='col-sm-12 mt-2 mb-2'>
                {{$articles->links()}}
            </div>
        </div>
    </div>
@endsection
