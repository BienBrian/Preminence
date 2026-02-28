@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 d-flex align-items-center">
                <div class="col">
                    <h5 class="m-0 text-header"><i class='fas fa-image'></i> <b>View</b> Gallery</h5>
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
                                        <h3 class="mb-0">Details</h3>
                                    </div>
                                    <div class="col text-right">
                                        <button class="btn btn-sm btn-primary" data-toggle="modal"
                                            data-target="#addGalleryCategories"><i class='fas fa-camera-retro'></i> Edit
                                            Category</button>
                                    </div>-->
                            </div>
                        </div>
                        <div class="card-body mb-0 p-0">
                            <!-- Projects table -->
                            <div class='table-responsive'>
                                <table class="table align-items-center table-flush">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong>Name:</strong>
                                            </td>
                                            <td>
                                                {{ nl2br($category->name) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <strong>Description:</strong>
                                            </td>
                                            <td>
                                                {{ nl2br($category->description) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="2">
                                                <a href="#" onclick="return postAction('{{ url('dashboard/website/gallery/category/delete/' . $category->id) }}', 'Delete this category?')"
                                                    class='btn btn-danger btn-sm' title='Delete'><i
                                                        class='fas fa-trash'></i> Delete</a>
                                            </td>
                                        </tr>
                                    <tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

            <!-- Modal -->
            <div class="modal fade" id="addGalleryCategories" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header border-bottom">
                            <h4 class="modal-title" id="exampleModalLabel">Categories</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body bg-secondary mb-0 pb-0">
                            <form action="{{ url('addgallerycategory') }}" method="post">
                                @csrf
                                <input type="hidden" name="id" value="0">
                                <label><small><strong>Name</strong></small></label>
                                <div class='form-group mb-2 mb-0 pb-0'>
                                    <input class='form-control form-control-alternative' name="name" placeholder="Name">
                                </div>
                                <label><small><strong>Description</strong></small></label>
                                <div class='form-group mb-2 mb-0 pb-0'>
                                    <textarea class='form-control form-control-alternative' name="description" rows="4" placeholder="Description"></textarea>
                                </div>
                                <div class='form-group text-right mt-2'>
                                    <button class='btn btn-primary'>Add Category</button>
                                </div>
                            </form>
                        </div>
                        <!--
                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-primary btn-gallery"><i class='fas fa-cloud-upload-alt'></i> Upload</button>
                      <button type="button" class="btn btn-primary btn-upload-gallery">Save changes</button>
                    </div>-->
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
