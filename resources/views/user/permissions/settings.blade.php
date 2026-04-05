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
                            <h3 class="mb-0">Page Settings</h3>
                        </div>
                        
                        <div class="col text-right">
                            <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#favicon" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}><i class='fas fa-camera-retro'></i> Favicon</button>
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#icon" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}><i class='fas fa-camera'></i> Icon</button>
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
                    <form method="POST" action="{{url('/savesettings')}}" class='row'>
                        @csrf
                        <input type="hidden" name="id" value="{{$settings == null?"":$settings->id}}">
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Site Name</label>
                            <input name='name' class='form-control' placeholder='Site Name' value="{{$settings == null?"":$settings->name}}" autocomplete="off" required {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Slogan/Tag Line</label>
                            <input name='slogan' class='form-control' placeholder='Slogan/Tag line' value="{{$settings == null?"":$settings->slogan}}" autocomplete="off" required {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Google Map Address</label>
                            <input name='address' class='form-control' placeholder='Google Map Address'value="{{$settings == null?"":$settings->address}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Theme</label>
                            <select name='theme' class='form-control' {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                                <option value='blue.css' {{$settings == null?"":$settings->theme == 'blue.css'?'selected':''}} >Blue Theme</option>
                                <option value='red.css' {{$settings == null?"":$settings->theme == 'red.css'?'selected':''}}>Red Theme</option>
                                <option value='black.css' {{$settings == null?"":$settings->theme == 'black.css'?'selected':''}}>Black Theme</option>
                                <option value='dark.css' {{$settings == null?"":$settings->theme == 'dark.css'?'selected':''}}>Dark Blue Theme</option>
                                <option value='green.css' {{$settings == null?"":$settings->theme == 'green.css'?'selected':''}}>Green Theme</option>
                                <option value='light.css' {{$settings == null?"":$settings->theme == 'light.css'?'selected':''}}>Light Blue Theme</option>
                            </select>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Facebook</label>
                            <input name='facebook' class='form-control' placeholder='Facebook' value="{{$settings == null?"":$settings->facebook}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Twitter</label>
                            <input name='twitter' class='form-control' placeholder='Twitter' value="{{$settings == null?"":$settings->twitter}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Google Plus</label>
                            <input name='google' class='form-control' placeholder='Google+' value="{{$settings == null?"":$settings->google}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Linkedin</label>
                            <input name='linkedin' class='form-control' placeholder='Linkedin' value="{{$settings == null?"":$settings->linkedin}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Youtube</label>
                            <input name='youtube' class='form-control' placeholder='Youtube' value="{{$settings == null?"":$settings->youtube}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Pinterest</label>
                            <input name='pinterest' class='form-control' placeholder='pinterest' value="{{$settings == null?"":$settings->pinterest}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>Instagram</label>
                            <input name='instagram' class='form-control' placeholder='Instagram' value="{{$settings == null?"":$settings->instagram}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-6 col-lg-4'>
                            <label>WhatsApp</label>
                            <input name='whatsapp' class='form-control' placeholder='WhatsApp' value="{{$settings == null?"":$settings->whatsapp}}" autocomplete="off" {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>
                        </div>
                        <div class='form-group col-sm-12 text-right'>
                            <button class='btn btn-primary' {{($permissions1->websites > 1 || $permissions2->websites > 1) ? "":"disabled"}}>Update Site</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
<div class="modal fade" id="favicon" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLabel">Upload a Favicon</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <center>
                <img id='view-favicon' src='{{$settings == null ?asset('website/default.png'):$settings->favicon == ""?asset('website/default.png'):asset('website/'.$settings->favicon)}}' class='img-fluid' style='max-height:100px'>
            </center>
            <form action="/uploadfavicon" method="post" enctype="multipart/form-data" class='d-none favicon-form'>
                @csrf
                <input type='text' name='id' value='{{$settings == null?"0":$settings->id}}'>
                <input type="file" class="form-control-file" name="favicon" id="exampleInputFile" aria-describedby="fileHelp">
            </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-primary btn-favicon"><i class='fas fa-cloud-upload-alt'></i> Upload</button>
          <button type="button" class="btn btn-primary btn-upload-favicon">Save changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
<div class="modal fade" id="icon" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Upload Website icon</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <center>
                <img id='view-icon' src='{{$settings == null ?asset('website/default.png'):$settings->icon == ""?asset('website/default.png'):asset('website/'.$settings->icon)}}' class='img-fluid' style='max-height:100px'>
            </center>
            <form action="/uploadicon" method="post" enctype="multipart/form-data" class='d-none icon-form'>
                @csrf
                <input type='text' name='id' value='{{$settings == null?"0":$settings->id}}'>
                <input type="file" class="form-control-file" name="icon" id="exampleInputFile" aria-describedby="fileHelp">
            </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-primary btn-icon"><i class='fas fa-cloud-upload-alt'></i> Upload</button>
          <button type="button" class="btn btn-primary btn-upload-icon">Save changes</button>
        </div>
      </div>
    </div>
  </div>
@endsection
