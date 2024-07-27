@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-microphone'></i> Edit Sermon</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/spiritual/sermons') }}">Sermons</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
            <div class="card shadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fas fa-microphone"></i> | Edit Sermon</h3>
                        </div>
                        <div class="col text-right">
                            <!--<button class='btn btn-primary btn-sm pt-2 pb-2' data-toggle="modal" data-target="#assetsModal"><i class='fas fa-camera'></i> Add Media</button>-->
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{url('/dashboard/spiritual/sermons/edit')}}" enctype="multipart/form-data" class="row d-flex align-items-center sermon-form">
                        @csrf
                        <div class='form-group col-sm-12'>
                            <input type="file" name="banner" class="d-none" accept="image/*">
                            <input type="file" name="video" class="d-none" accept="video/*">
                            <input type="file" name="audio" class="d-none" accept="audio/*">
                            <input type="text" name="id" value="{{$sermon->id}}" class="d-none">

                            <label style="font-weight: 200; font-size: 0.9em;">Sermon Title</label>
                            <input name="title" type="text" placeholder="Sermon Title" class="form-control" value="{{$sermon->title}}" style="max-width: 500px">
                        </div>

                        <div class='form-group col-sm-12'>
                            <label style="font-weight: 200; font-size: 0.9em;">Banner Image</label><br>
                            <div class="{{$sermon->banner == ''?'d-none':''}} banner">
                                <img src="{{asset('sermon/'.$sermon->banner)}}" class="img-fluid" id="view-banner" style="max-width: 500px">
                            </div>
                            <span class="btn btn-white btn-add-banner">Change Banner</span> <span class='text-primary'>No file choosen</span>
                            <p class='small alert mt-4' style="background-color: #dee; max-width: 500px;">This is the image that appears when sharing the message on the website</p>
                        </div>

                        <div class='form-group col-sm-12'>
                            <label style="font-weight: 200; font-size: 0.9em;">Duration</label><br>
                            <table style="max-width: 500px;">
                                <tr>
                                    <td class='pr-2'>Hours:</td>
                                    <td><input name="hours" class="form-control" value="{{intval($sermon->duration/60)}}" style="max-width: 80px;"></td>
                                    <td class='pl-2 pr-2'>Minutes:</td>
                                    <td><input name="minutes" class="form-control" value="{{(($sermon->duration/60)-intval($sermon->duration/60))*60}}" style="max-width: 80px;"></td>
                                </tr>
                            </table>
                        </div>
                        <div class='form-group col-sm-12'>
                            <label><small>Sermon Day and Time</small></label>
                            <input name="date" type="text" placeholder="Sermon Date" class="form-control datepicker" value="{{\Carbon\Carbon::parse($sermon->sermondate.' '.$sermon->time)}}"required readonly style="max-width: 500px;">
                            <p class='small alert mt-4' style="background-color: #dee; max-width: 500px;">Future sermons will be treated as upcoming services</p>
                        </div>
                        <div class='form-group col-sm-12'>
                            <label style="font-weight: 200; font-size: 0.9em;">Youtube Link</label>
                            <input name="youtube" type="text" placeholder="Youtube Link" class="form-control" value="{{$sermon->youtube}}" style="max-width: 500px;">
                        </div>
                        <div class='form-group col-sm-12'>
                            <label style="font-weight: 200; font-size: 0.9em;">Audio (Sound Cloud Embed)</label>
                            <input name="audio_link" type="text" placeholder="Audio Link" class="form-control" value="{{$sermon->audio!=''?$sermon->audio:'#'}}" style="max-width: 500px;">
                        </div>
                        <div class="col-sm-12">
                            <div style="max-width: 500px;">
                                <label style="font-weight: 200; font-size: 0.9em;">Notes</label>
                                <textarea name="description" placeholder="Sermon Content" rows="5" class="form-control summernote">{{$sermon->description}}</textarea>
                                <div class='text-right'>
                                    <button class="btn btn-primary mt-2">Save Edit</button>
                                </div>
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
                        <div class="col-sm-12 {{$sermon->video == ''?'d-none':''}} video">
                            <video controls>  <source src="{{asset('sermon/video/'.$sermon->video)}}" type="video/mp4" id="view-video"></video>
                            <small class='text-danger'><strong>Videos may Not play/show before upload</strong></small>
                        </div>
                        <div class="col-sm-12 {{$sermon->banner == ''?'d-none':''}} banner">
                            <img src="{{asset('sermon/'.$sermon->banner)}}" class="img-fluid" id="view-banner">
                        </div>
                        <div class="col-sm-12 {{$sermon->audio == ''?'d-none':''}} audio">
                            <audio controls>
                                <source src="{{asset('sermon/audio/'.$sermon->audio)}}" id="view-audio">
                                Your browser does not support the audio element.
                            </audio>
                            <small class='text-danger'><strong>Audio may not play before upload</strong></small>
                        </div>
                        <div class="col-sm-12 mt-3 mb-2 text-right">
                            <button class="btn btn-outline-primary btn-add-audio">New Audio</button>
                            <button class="btn btn-outline-primary btn-add-video">New Video</button>
                            <button class="btn btn-primary btn-add-banner">New Banner</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
