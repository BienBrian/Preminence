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
                            <h3 class="mb-0">Gallery</h3>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#gallery" {{($permissions1->websites > 1 || $permissions2->websites > 1)? "":"disabled"}}><i class='fas fa-camera-retro'></i> Add Image</button>
                        </div>
                    </div>
                </div>
                <div class="card-body mb-0 p-0">
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
                    <div class='table-responsive'>
                        <table class="table align-items-center table-flush">
                            <thead class='thead-light'>
                                <tr>
                                    <th scope="col">Gallery</th>
                                    <th scope="col" class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($gallery->count() == 0)
                                <tr>
                                    <td colspan='2' class='text-center'>
                                        <i class='fas fa-ban'></i> No items yet
                                    </td>
                                </tr>
                                @endif
                                @foreach ($gallery as $gallery)
                               <tr>
                                   <th scope="row">
                                        <div class="media align-items-center">
                                            <img alt="Image placeholder" src="{{asset('website/gallery/'.$gallery->image)}}" height='40' width='40' class='mr-3' style='border-radius: 50%;'>
                                            <div class="media-body">
                                              <span class="mb-0 text-sm">{{ str_limit($gallery->description, $limit = 30, $end = '...') }}</span>
                                            </div>
                                        </div>
                                    </th>
                                    <td class="text-right">
                                        <a href="#" class='btn btn-outline-primary p-1 pl-2 pr-2 btn-view' title='View'><i class='fas fa-eye'></i> View</a>
                                        @if($permissions1->websites > 1 || $permissions2->websites > 1)
                                            <a href="{{url('/gallerydelete/'.$gallery->id)}}" class='btn btn-danger p-1 pl-2 pr-2' title='Delete'><i class='fas fa-trash'></i> Delete</a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            <tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
</div>

    <!-- Modal -->
<div class="modal fade" id="gallery" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel">New Image</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body pt-0 pb-0">
                <center>
                    <img id='view-gallery' src='' class='d-none img-fluid'>
                    <span id="gallery-caption"><i class='fas fa-ban'></i> No image Yet</span>
                </center>
                <form action="/uploadgallery" method="post" enctype="multipart/form-data" class='gallery-form'>
                    @csrf
                    <input type="file" class="form-control-file d-none" name="gallery" id="exampleInputFile" aria-describedby="fileHelp">
                    <div class='form-group mt-2 mb-0 pb-0'>
                        <textarea class='form-control' name="description" rows="3" placeholder="Image Caption"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-primary btn-gallery"><i class='fas fa-cloud-upload-alt'></i> Upload</button>
              <button type="button" class="btn btn-primary btn-upload-gallery">Save changes</button>
            </div>
          </div>
        </div>
      </div>

      <div class="container gallery-view d-none">
            <div class="row d-flex align-items-center">
                <div class='col-sm-12 text-right'>
                    <a href="#" class='btn text-white btn-close-gallery'><i class='fas fa-times fa-2x'></i></a>
                </div>
                <div class='col-sm-12'>
                    <img src='' class='img-fluid' style='max-height: 90vh'>
                </div>
            </div>
      </div>
@endsection
