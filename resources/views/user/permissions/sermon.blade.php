@extends('layouts.user')
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
                        <h3 class="mb-0"><i class="fas fa-microphone"></i> | New Sermon</h3>
                    </div>
                    <div class="col text-right">
                        <button class='btn btn-primary btn-sm pt-2 pb-2' data-toggle="modal" data-target="#assetsModal"><i class='fas fa-camera'></i> Add Media</button>
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
                <form method="POST" action="{{url('/addsermon')}}" enctype="multipart/form-data" class="row d-flex align-items-center sermon-form">
                    @csrf

                    <div class='form-group col-sm-12'>
                        <input type="file" name="banner" class="d-none" accept="image/*">
                        <input type="file" name="video" class="d-none" accept="video/*">
                        <input type="file" name="audio" class="d-none" accept="audio/*">
                        <input type="text" name="id" value="0" class="d-none">

                        <label><small>Title</small></label>
                        <input name="title" type="text" placeholder="Sermon Title" class="form-control timepicker" required>
                    </div>
                    <div class='form-group col-sm-6'>
                        <label><small>Date</small></label>
                        <input name="date" type="text" placeholder="Sermon Date" class="form-control datepicker" required readonly>
                    </div>
                    <div class='form-group col-sm-6'>
                        <label><small>Youtube Link</small></label>
                        <input name="youtube" type="text" placeholder="Youtube Link" class="form-control" value="#">
                    </div>
                    <div class="col-sm-12">
                        <label class='mt-3'><small>Description</small></label>
                        <textarea name="description" placeholder="Sermon Content" rows="5" class="form-control"></textarea>
                        <div class='text-right'>
                            <button class="btn btn-primary mt-2">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="assetsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Media Uploads</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-3 pb-0 mb-0">
                    <div class="col-sm-12 d-none video">
                        <video controls>  <source src="{{asset('sermon/default.mp4')}}" type="video/mp4" id="view-video"></video>
                        <small class='text-danger'><strong>Videos may Not play/show before upload</strong></small>
                    </div>
                    <div class="col-sm-12 d-none banner">
                        <img src="{{asset('website/homepage/default.jpg')}}" class="img-fluid" id="view-banner">
                    </div>
                    <div class="col-sm-12 d-none audio">
                        <audio controls>
                            <source src="" id="view-audio">
                            Your browser does not support the audio element.
                        </audio>
                        <small class='text-danger'><strong>Audio may not play before upload</strong></small>
                    </div>
                    <div class="col-sm-12 mt-3 mb-2 text-right">
                        <button class="btn btn-outline-primary btn-add-audio">Add Audio</button>
                        <button class="btn btn-outline-primary btn-add-video">Add Video</button>
                        <button class="btn btn-primary btn-add-banner">Add Banner</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
