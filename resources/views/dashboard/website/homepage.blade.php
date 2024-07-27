@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-globe'></i> Home Page Settings</h5>
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
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <!--
                                <div class="col">
                                    <h3 class="mb-0">Home Page Settings</h3>
                                </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#homepage"><i
                                            class='fas fa-camera-retro'></i> Header Image</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body ">
                            <!-- Projects table -->
                            <form method="POST" action="{{ url('dashboard/website/homepage/add') }}"
                                class='row d-flex align-items-center'>
                                @csrf
                                <div class="d-none d-sm-block col-sm-4">
                                    <img src="{{ $homepage == null ? asset('website/homepage/default.png') : ($homepage->image == '' ? asset('website/homepage/default.png') : asset('website/homepage/' . $homepage->image)) }}"
                                        class="img-fluid" style="max-height: 150px;">
                                </div>
                                <div class='form-group col-sm-8'>
                                    <input type="hidden" name='id'
                                        value="{{ $homepage == null ? '' : $homepage->id }}" />
                                    <label>Message Title</label>
                                    <input name='title' class='form-control' placeholder='Message Title'
                                        value="{{ $homepage == null ? '' : $homepage->title }}" autocomplete="off" required>
                                    <label class='mt-3'>Message Description</label>
                                    <textarea name='description' class='form-control summernote' rows = "8" placeholder='Message Description'>{{ $homepage == null ? '' : $homepage->description }}</textarea>
                                </div>
                                <div class='form-group col-sm-12 text-right'>
                                    <button class='btn btn-primary'>Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="homepage" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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
                        <img id='view-homepage'
                            src='{{ $homepage == null ? asset('website/homepage/default.png') : ($homepage->image == '' ? asset('website/homepage/default.png') : asset('website/homepage/' . $homepage->image)) }}'
                            style="max-height: 150px;">
                    </center>
                    <form action="{{ url('dashboard/website/homepage/upload') }}" method="post"
                        enctype="multipart/form-data" class='d-none homepage-form'>
                        @csrf
                        <input type='text' name='id' value='{{ $homepage == null ? '0' : $homepage->id }}'>
                        <input type="file" class="form-control-file" name="homepage" id="exampleInputFile"
                            aria-describedby="fileHelp">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-homepage"><i
                            class='fas fa-cloud-upload-alt'></i> Upload</button>
                    <button type="button" class="btn btn-primary btn-upload-homepage">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('.btn-upload-homepage').attr('disabled', 'disabled');

            //homepage
            $('.btn-homepage').click(function() {
                $('.homepage-form input[type="file"]').click();
            });
            $('.homepage-form input[type="file"]').change(function() {
                $('.btn-upload-homepage').removeAttr('disabled');
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-homepage').attr('src', e.target.result);
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            $('.btn-upload-homepage').click(function() {
                $('.homepage-form').submit();
            });
        });
    </script>
@endpush
