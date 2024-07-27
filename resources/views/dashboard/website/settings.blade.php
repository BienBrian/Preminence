@extends('layouts.dashboard')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-globe'></i> Page Settings</h5>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card bgshadow">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <!--
                                <div class="col">
                                    <h3 class="mb-0">Page Settings</h3>
                                </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                        data-target="#favicon"><i class='fas fa-camera-retro'></i> Favicon</button>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#icon"><i
                                            class='fas fa-camera'></i> Icon</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ url('dashboard/website/settings/add') }}" class='row'>
                                @csrf
                                <input type="hidden" name="id" value="{{ $settings == null ? '' : $settings->id }}">
                                <input type="hidden" name="tid" value="{{ $twilio == null ? '' : $twilio->id }}">
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Site Name</label>
                                    <input name='name' class='form-control' placeholder='Site Name'
                                        value="{{ $settings == null ? '' : $settings->name }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Slogan/Tag Line</label>
                                    <input name='slogan' class='form-control' placeholder='Slogan/Tag line'
                                        value="{{ $settings == null ? '' : $settings->slogan }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Google Map Address</label>
                                    <input name='address' class='form-control'
                                        placeholder='Google Map Address'value="{{ $settings == null ? '' : $settings->address }}"
                                        autocomplete="off">
                                </div><!--
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>TWILIO SID</label>
                                    <input name='sid' class='form-control' placeholder='TWILIO SID for SMS API' value="{{ $twilio == null ? '' : $twilio->sid }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>TWILIO TOKEN</label>
                                    <input name='token' class='form-control' placeholder='TWILIO TOKEN for SMS API' value="{{ $twilio == null ? '' : $twilio->token }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>TWILIO NUMBER</label>
                                    <input name='number' class='form-control' placeholder='TWILIO NUMBER for SMS API' value="{{ $twilio == null ? '' : $twilio->number }}" autocomplete="off">
                                </div>-->
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Theme</label>
                                    <select name='theme' class='form-control'>
                                        <option value='blue.css'
                                            {{ $settings == null ? '' : ($settings->theme == 'blue.css' ? 'selected' : '') }}>
                                            Blue
                                            Theme</option>
                                        <option value='red.css'
                                            {{ $settings == null ? '' : ($settings->theme == 'red.css' ? 'selected' : '') }}>
                                            Red
                                            Theme</option>
                                        <option value='black.css'
                                            {{ $settings == null ? '' : ($settings->theme == 'black.css' ? 'selected' : '') }}>
                                            Black Theme</option>
                                        <option value='dark.css'
                                            {{ $settings == null ? '' : ($settings->theme == 'dark.css' ? 'selected' : '') }}>
                                            Dark
                                            Blue Theme</option>
                                        <option value='green.css'
                                            {{ $settings == null ? '' : ($settings->theme == 'green.css' ? 'selected' : '') }}>
                                            Green Theme</option>
                                        <option value='light.css'
                                            {{ $settings == null ? '' : ($settings->theme == 'light.css' ? 'selected' : '') }}>
                                            Light Blue Theme</option>
                                    </select>
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Facebook</label>
                                    <input name='facebook' class='form-control' placeholder='Facebook'
                                        value="{{ $settings == null ? '' : $settings->facebook }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Twitter</label>
                                    <input name='twitter' class='form-control' placeholder='Twitter'
                                        value="{{ $settings == null ? '' : $settings->twitter }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Google Plus</label>
                                    <input name='google' class='form-control' placeholder='Google+'
                                        value="{{ $settings == null ? '' : $settings->google }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Linkedin</label>
                                    <input name='linkedin' class='form-control' placeholder='Linkedin'
                                        value="{{ $settings == null ? '' : $settings->linkedin }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Youtube</label>
                                    <input name='youtube' class='form-control' placeholder='Youtube'
                                        value="{{ $settings == null ? '' : $settings->youtube }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Pinterest</label>
                                    <input name='pinterest' class='form-control' placeholder='pinterest'
                                        value="{{ $settings == null ? '' : $settings->pinterest }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>Instagram</label>
                                    <input name='instagram' class='form-control' placeholder='Instagram'
                                        value="{{ $settings == null ? '' : $settings->instagram }}" autocomplete="off">
                                </div>
                                <div class='form-group col-sm-6 col-lg-4'>
                                    <label>WhatsApp</label>
                                    <input name='whatsapp' class='form-control' placeholder='WhatsApp'
                                        value="{{ $settings == null ? '' : $settings->whatsapp }}" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label>About Us</label>
                                    <textarea name="aboutus" class="form-control summernote" rows="4" placeholder="About Us">{{ $settings == null ? '' : $settings->aboutus }}</textarea>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label>Contact Us</label>
                                    <textarea name="contactus" class="form-control summernote" rows="4" placeholder="Contact Us">{{ $settings == null ? '' : $settings->contactus }}</textarea>
                                </div>
                                <div class='form-group col-sm-12 text-right'>
                                    <button class='btn btn-primary'>Update Site</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="favicon" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
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
                            <img id='view-favicon'
                                src='{{ $settings == null ? asset('website/default.png') : ($settings->favicon == '' ? asset('website/default.png') : asset('website/' . $settings->favicon)) }}'
                                class='img-fluid' style='max-height:100px'>
                        </center>
                        <form action="{{ url('dashboard/website/settings/uploadfavicon')}}" method="post" enctype="multipart/form-data"
                            class='d-none favicon-form'>
                            @csrf
                            <input type='text' name='id' value='{{ $settings == null ? '0' : $settings->id }}'>
                            <input type="file" class="form-control-file" name="favicon" id="exampleInputFile"
                                aria-describedby="fileHelp">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary btn-favicon"><i
                                class='fas fa-cloud-upload-alt'></i> Upload</button>
                        <button type="button" class="btn btn-primary btn-upload-favicon">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="icon" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
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
                            <img id='view-icon'
                                src='{{ $settings == null ? asset('website/default.png') : ($settings->icon == '' ? asset('website/default.png') : asset('website/' . $settings->icon)) }}'
                                class='img-fluid' style='max-height:100px'>
                        </center>
                        <form action="{{ url('dashboard/website/settings/uploadicon')}}" method="post" enctype="multipart/form-data"
                            class='d-none icon-form'>
                            @csrf
                            <input type='text' name='id' value='{{ $settings == null ? '0' : $settings->id }}'>
                            <input type="file" class="form-control-file" name="icon" id="exampleInputFile"
                                aria-describedby="fileHelp">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary btn-icon"><i
                                class='fas fa-cloud-upload-alt'></i> Upload</button>
                        <button type="button" class="btn btn-primary btn-upload-icon">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('.btn-upload-favicon, .btn-upload-icon, .btn-upload-homepage, .btn-upload-gallery').attr('disabled', 'disabled');
            $('.btn-favicon').click(function(){
                  $('.favicon-form input[type="file"]').click();
              });
              $('.favicon-form input[type="file"]').change(function(){
                    $('.btn-upload-favicon').removeAttr('disabled');
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#view-favicon').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });

              $('.btn-upload-favicon').click(function(){
                  $('.favicon-form').submit();
              });

              //icon
              $('.btn-icon').click(function(){
                  $('.icon-form input[type="file"]').click();
              });
              $('.icon-form input[type="file"]').change(function(){
                    $('.btn-upload-icon').removeAttr('disabled');
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#view-icon').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });

              $('.btn-upload-icon').click(function(){
                  $('.icon-form').submit();
              });
        });
    </script>
@endpush
