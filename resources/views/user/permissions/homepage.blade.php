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
            <div class="card bgshadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Home Page Settings</h3>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#homepage" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}><i class='fas fa-camera-retro'></i> Header Image</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
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
                    <!-- Projects table -->
                    <form method="POST" action="{{url('/savehomepage')}}" class='row d-flex align-items-center'>
                        @csrf
                        <div class="d-none d-sm-block col-sm-6">
                            <img src="{{$homepage == null ? asset("website/homepage/default.jpg") : $homepage->image == "" ? asset("website/homepage/default.jpg") : asset("website/homepage/".$homepage->image)}}" class="img-fluid">
                        </div>
                        <div class='form-group col-sm-6'>
                            <input type="hidden" name='id' value="{{$homepage==null?'':$homepage->id}}"/>
                            <label>Message Title</label>
                            <input name='title' class='form-control' placeholder='Message Title' value="{{$homepage==null?'':$homepage->title}}" autocomplete="off" required {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                            <label class='mt-3'>Message Description</label>
                            <textarea name='description' class='form-control' rows = "8" placeholder='Message Description' {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>{{$homepage==null?'':$homepage->description}}</textarea>
                        </div>
                        <div class='form-group col-sm-12 text-right'>
                            <button class='btn btn-primary' {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
<div class="modal fade" id="homepage" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLabel">Upload Home Page Image</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <center>
                <img id='view-homepage' src='{{$homepage == null ? asset("website/homepage/default.jpg") : $homepage->image == "" ? asset("website/homepage/default.jpg") : asset("website/homepage/".$homepage->image)}}' class='img-fluid'>
            </center>
            <form action="/uploadhomepage" method="post" enctype="multipart/form-data" class='d-none homepage-form'>
                @csrf
                <input type='text' name='id' value='{{$homepage==null?"0":$homepage->id}}'>
                <input type="file" class="form-control-file" name="homepage" id="exampleInputFile" aria-describedby="fileHelp">
            </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-primary btn-homepage"><i class='fas fa-cloud-upload-alt'></i> Upload</button>
          <button type="button" class="btn btn-primary btn-upload-homepage">Save changes</button>
        </div>
      </div>
    </div>
  </div>

@endsection
