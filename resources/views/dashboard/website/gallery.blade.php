@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-image'></i> Gallery</h5>
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
                                    <h3 class="mb-0">Gallery</h3>
                                </div>-->
                                <div class="col text-right">
                                    <a href="{{ url('dashboard/website/gallery/categories') }}"
                                        class="btn btn-sm btn-outline-primary"><i class='fas fa-angle-right'></i>
                                        Categories</a>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#gallery"><i
                                            class='fas fa-camera-retro'></i> Add Image</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mb-0 p-0">
                            <!-- Projects table -->
                            <div class='table-responsive'>
                                <table class="table align-items-center table-flush">
                                    <thead class='thead-light'>
                                        <tr>
                                            <th scope="col">Gallery</th>
                                            <th scope="col">Category</th>
                                            <th scope="col">Uploaded On</th>
                                            <th scope="col" class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($galleries->count() == 0)
                                            <tr>
                                                <td colspan='4' class='text-center'>
                                                    <i class='fas fa-ban'></i> No items yet
                                                </td>
                                            </tr>
                                        @endif
                                        @foreach ($galleries as $gallery)
                                            <tr>
                                                <th scope="row">
                                                    <div class="media align-items-center">
                                                        <img alt="Image placeholder"
                                                            src="{{ asset('website/gallery/' . $gallery->image) }}"
                                                            height='40' width='40' class='mr-3'
                                                            style='border-radius: 50%;'>
                                                        <div class="media-body">
                                                            <span
                                                                class="mb-0 text-sm">{{ \Str::limit($gallery->description, $limit = 30, $end = '...') }}</span>
                                                        </div>
                                                    </div>
                                                </th>
                                                <td>{{ Str::words($gallery->name, 5, '...') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($gallery->date_uploaded)->diffForHumans() }}
                                                </td>
                                                <td class="text-right">
                                                    <a href="#" class='btn text-primary btn-sm btn-view'
                                                        title='View'><i class='fas fa-eye'></i></a>
                                                    <a href="#" onclick="return postAction('{{ url('dashboard/website/gallery/delete/' . $gallery->id) }}', 'Delete this image?')"
                                                        class='btn text-danger btn-sm' title='Delete'><i
                                                            class='fas fa-trash'></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    <tbody>
                                </table>
                            </div>
                        </div>
                        <div class='col-sm-12 mt-2 mb-2'>
                            {{ $galleries->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="gallery" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">New Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <center>
                        <img id='view-gallery' src='' class='d-none img-fluid'>
                        <span id="gallery-caption"><i class='fas fa-ban'></i> No image Yet</span>
                    </center>
                    <form action="{{ url('dashboard/website/gallery/upload') }}" method="post"
                        enctype="multipart/form-data" class='gallery-form'>
                        @csrf
                        <input type="file" class="form-control-file d-none" name="gallery" id="exampleInputFile"
                            aria-describedby="fileHelp">
                        <div class='form-group mt-2 mb-0 pb-0'>
                            <textarea class='form-control' name="description" rows="3" placeholder="Image Caption"></textarea>
                        </div>
                        <div class='form-group mt-2 mb-0 pb-0'>
                            <select class='custom-select' name="category">
                                @foreach ($categories as $category)
                                    <option value='{{ $category->id }}'>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-gallery"><i
                            class='fas fa-cloud-upload-alt'></i> Upload</button>
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
@push('js')
    <script>
        $(document).ready(function() {

            $('.btn-upload-gallery').attr('disabled', 'disabled');

            //gallery
            $('.btn-gallery').click(function() {
                $('.gallery-form input[type="file"]').click();
            });
            $('.gallery-form input[type="file"]').change(function() {
                $('.btn-upload-gallery').removeAttr('disabled');
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-gallery').attr('src', e.target.result);
                    $('#view-gallery').removeClass('d-none');
                    $('#gallery-caption').addClass('d-none');
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            $('.btn-upload-gallery').click(function() {
                $('.gallery-form').submit();
            });//gallery view
              $('.btn-close-gallery').click(function(e){
                  e.preventDefault();
                  $('.gallery-view').addClass('d-none');
              });

              $('.btn-view').click(function(e){
                  e.preventDefault();
                  var image = $(this).closest('tr').find('th img').attr('src');
                  $('.gallery-view img').attr('src', image);
                  $('.gallery-view').removeClass('d-none');
              });
        });
    </script>
@endpush
