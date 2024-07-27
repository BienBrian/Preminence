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
            <div class="card bgshadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Pastor's Message</h3>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#imageModal" {{($permissions1->websites > 1 || $permissions2->websites > 1)? "":"disabled"}}><i class='fas fa-camera-retro'></i> Add Image</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
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
                    <!-- Projects table -->
                    <form method="POST" action="{{url('/savepastorsmessage')}}" class='row d-flex align-items-center'>
                        @csrf
                        <div class="d-none d-sm-block col-sm-6">
                            <img src="{{$message == null ? asset("website/default.png") : $message->image == "" ? asset("website/default.png") : asset("website/pastors/".$message->image)}}" class="img-fluid">
                        </div>
                        <div class='form-group col-sm-6'>
                            <input type="hidden" name='id' value="{{$message==null?'0':$message->id}}"/>
                            <label>Message Title</label>
                            <input name='title' class='form-control' placeholder='Message Title' value="{{$message==null?'':$message->title}}" autocomplete="off" required {{($permissions1->websites > 1 || $permissions2->websites > 1)? "":"disabled"}}>
                            <label class='mt-3'>Message Description</label>
                            <textarea name='description' class='form-control' rows = "8" placeholder='Message Description' {{($permissions1->websites > 1 || $permissions2->websites > 1)? "":"disabled"}}>{{$message==null?'':$message->description}}</textarea>
                        </div>
                        <div class='form-group col-sm-12 text-right'>
                            <button class='btn btn-primary' {{($permissions1->websites > 1 || $permissions2->websites > 1)? "":"disabled"}}>Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style='border: none;'>
                    <h5 class="modal-title" id="exampleModalLabel"><i class='fas fa-camera'></i> | Message Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class='fas fa-times'></i></span>
                    </button>
                </div>

                <div class="modal-body row">

                    <div class="col-sm-12 text-center">
                        <div id="upload-demo" class='d-none'></div>
                        <input type="file" id="upload" style='display: none;'>
                    </div>
                    <div class='col-sm-12 feedback'></div>
                </div>

                <div class="modal-footer" style='border: none;'>
                    <button type="button" class="btn btn-outline-primary upload">Choose Image</button><!--data-dismiss="modal"-->
                    <button type="button" class="btn btn-primary upload-result">Save Image</button>
                </div>
            </div>
        </div>
    </div>
    <!-- -->
@endsection
