@extends('layouts.admin')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fas fa-comments"></i> | New Article</h3>
                        </div>
                    </div>
                </div>
                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class='fas fa-check-circle'></i> {!! \Session::get('success') !!}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class='fas fa-exclamation-circle'></i> {!! \Session::get('error') !!}
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
                <div class="card-body">
                    <form method="POST" action="{{url('/addarticle')}}" enctype="multipart/form-data" class="row d-flex align-items-center article-form">
                        @csrf
                        <div class="col-lg-6">
                            <img src="{{asset('article/default.jpg')}}" class="img-fluid">
                            <div class='text-center'>
                                <a href="#" class="btn btn-outline-primary mt-2 articlephoto">Upload Image</a>
                            </div>
                        </div>
                        <div class='form-group col-lg-12'>
                            <label><small>Title</small></label>
                            <input type="file" name="banner" class="d-none">
                            <input type="text" name="id" value="0" class="d-none">
                            <input name="title" type="text" placeholder="Article Title" class="form-control">
                            <label class='mt-3'><small>Article</small></label>
                            <textarea name="description" placeholder="Article Content" rows="15" class="form-control"></textarea>
                            <div class='text-right'>
                                <button class="btn btn-primary mt-2">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
