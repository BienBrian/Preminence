@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-blog'></i> {{ isset($article) ? 'Edit' : 'New' }} <b>Article</b></h5>
                </div>
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/articles') }}">Articles</a></li>
                        <li class="breadcrumb-item active">{{ isset($article) ? 'Edit' : 'New' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ url('dashboard/articles/add') }}" enctype="multipart/form-data" class="article-form">
                @csrf
                <input type="hidden" name="id" value="{{ isset($article) ? $article->id : 0 }}">
                <div class="row">
                    <!-- Left Column - Content -->
                    <div class="col-lg-8">
                        <!-- Title -->
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <input name="title" type="text" placeholder="Article Title" class="form-control form-control-lg font-weight-bold"
                                        value="{{ isset($article) ? $article->title : old('title') }}" style="font-size:1.4rem;">
                                </div>

                                <!-- Excerpt / Summary -->
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Excerpt / Summary</label>
                                    <textarea name="excerpt" class="form-control" rows="3" maxlength="500"
                                        placeholder="Brief summary that appears on article cards and previews...">{{ isset($article) ? $article->excerpt : old('excerpt') }}</textarea>
                                    <small class="text-muted">Max 500 characters. If left empty, the first few lines of the article will be used.</small>
                                </div>

                                <!-- Article Body -->
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted">Article Body</label>
                                    <textarea name="description" id="summernote" class="form-control">{{ isset($article) ? $article->description : old('description') }}</textarea>
                                </div>

                                <!-- Scripture Reference -->
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted">Scripture Reference</label>
                                    <input name="scripture_reference" type="text" class="form-control"
                                        placeholder="e.g. John 3:16, Psalm 23:1-6"
                                        value="{{ isset($article) ? $article->scripture_reference : old('scripture_reference') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="col-lg-4">
                        <!-- Publish Card -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 font-weight-bold"><i class="fas fa-paper-plane"></i> Publish</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($article))
                                    <p class="mb-2">
                                        <span class="small text-muted">Status:</span>
                                        @if($article->status == 1)
                                            <span class="badge badge-success">Published</span>
                                        @else
                                            <span class="badge badge-warning">Draft</span>
                                        @endif
                                    </p>
                                    @if($article->views > 0)
                                        <p class="mb-2"><span class="small text-muted">Views:</span> <strong>{{ $article->views }}</strong></p>
                                    @endif
                                    <p class="mb-2"><span class="small text-muted">Reading time:</span> <strong>{{ $article->reading_time }} min</strong></p>
                                @endif
                                <div class="d-flex justify-content-between mt-3">
                                    <a href="{{ url('dashboard/articles') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> {{ isset($article) ? 'Update' : 'Save' }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Category Card -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 font-weight-bold"><i class="fas fa-folder"></i> Category</h6>
                            </div>
                            <div class="card-body">
                                <select name="category_id" class="form-control">
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ (isset($article) && $article->category_id == $cat->id) ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Tags Card -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 font-weight-bold"><i class="fas fa-tags"></i> Tags</h6>
                            </div>
                            <div class="card-body">
                                <input name="tags" type="text" class="form-control"
                                    placeholder="e.g. prayer, hope, grace"
                                    value="{{ isset($tags) ? $tags : old('tags') }}">
                                <small class="text-muted">Separate tags with commas</small>
                            </div>
                        </div>

                        <!-- Author Card -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 font-weight-bold"><i class="fas fa-user"></i> Author</h6>
                            </div>
                            <div class="card-body">
                                <select name="user_id" class="form-control select2-author">
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}"
                                            {{ (isset($article) ? $article->user_id == $u->id : $u->id == Auth::user()->id) ? 'selected' : '' }}>
                                            {{ $u->firstname }} {{ $u->lastname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Featured Image Card -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0 font-weight-bold"><i class="fas fa-image"></i> Featured Image</h6>
                            </div>
                            <div class="card-body text-center">
                                <input type="file" name="banner" class="d-none" accept="image/jpeg,image/png,image/jpg">
                                <div class="mb-2" style="min-height:120px;background:#f8f9fc;border:2px dashed #ddd;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;" id="image-drop-zone">
                                    @if(isset($article) && $article->banner != '')
                                        <img src="{{ asset('article/' . $article->banner) }}" class="img-fluid rounded" style="max-height:200px;" id="preview-image">
                                    @else
                                        <div class="text-center p-3" id="upload-placeholder">
                                            <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                            <p class="small text-muted mb-0 mt-1">Click to upload image</p>
                                        </div>
                                        <img src="" class="img-fluid rounded d-none" style="max-height:200px;" id="preview-image">
                                    @endif
                                </div>
                                <a href="#" class="btn btn-outline-primary btn-sm btn-block articlephoto"><i class="fas fa-upload"></i> {{ isset($article) && $article->banner != '' ? 'Change Image' : 'Upload Image' }}</a>

                                <!-- Image Caption -->
                                <div class="form-group mt-2 mb-0">
                                    <input name="image_caption" type="text" class="form-control form-control-sm"
                                        placeholder="Photo credit / caption"
                                        value="{{ isset($article) ? $article->image_caption : old('image_caption') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $('.select2-author').select2({ placeholder: 'Select author', width: '100%' });

            $('#summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            $('.articlephoto, #image-drop-zone').click(function(e) {
                e.preventDefault();
                $(".article-form input[type='file']").click();
            });
            $('.article-form input[type="file"]').change(function() {
                var input = $(this);
                if (input[0].files && input[0].files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview-image').attr('src', e.target.result).removeClass('d-none');
                        $('#upload-placeholder').addClass('d-none');
                    }
                    reader.readAsDataURL(input[0].files[0]);
                }
            });
        });
    </script>
@endpush
