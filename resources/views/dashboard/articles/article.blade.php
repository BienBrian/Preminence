@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-blog'></i> New <b>Article</b></h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/articles') }}">Articles</a></li>
                        <li class="breadcrumb-item active">New</li>
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
                                    <h3 class="mb-0"><i class="fas fa-comments"></i> | New Article</h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ url('dashboard/articles/add') }}" enctype="multipart/form-data"
                                class="row d-flex align-items-center article-form">
                                @csrf
                                <div class='form-group col-lg-12'>
                                    <label><small>Title</small></label>
                                    <input type="file" name="banner" class="d-none">
                                    <input type="text" name="id" value="0" class="d-none">
                                    <input name="title" type="text" placeholder="Article Title" class="form-control">
                                    <label class='mt-3'><small>Article</small></label>
                                    <textarea name="description" id="summernote" placeholder="Article Content" class="form-control"></textarea>
                                    <div class='row d-flex align-items-end'>
                                        <div class="col-sm-6 p-2">
                                            <img src="" class="img-fluid" placeholder='No article image'>
                                        </div>
                                        <div class='col-sm-6 text-right'>
                                            <a href="#" class="btn btn-outline-primary mt-2 articlephoto">Upload
                                                Image</a>
                                            <button class="btn btn-primary mt-2">Save</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                height: 150,
                toolbar: [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']]
                ]
            });

            //article photo
            $('.articlephoto').one('click', function(e) {
                e.preventDefault();
                $(".article-form input[type='file']").click();
            });
            $('.article-form input[type="file"]').change(function() {
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('.article-form img').attr('src', e.target.result);
                }

                reader.readAsDataURL(input[0].files[0]);
            });
        });
    </script>
@endpush
