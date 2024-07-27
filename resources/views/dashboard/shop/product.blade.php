@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-shopping-bag'></i> Product Details</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Product Details</li>
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
                                <!--
                                            <div class="col">
                                                <h3 class="mb-0"><i class="fas fa-shopping-bag"></i> | Product Details</h3>
                                            </div>-->
                                <div class="col text-right">
                                    <button class='btn btn-primary btn-sm' data-toggle="modal"
                                        data-target="#editProductModal"><i class='fas fa-edit'></i> Edit Product</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row d-flex align-items-center">
                                @csrf
                                <div class="col-sm-6 text-center">
                                    <img src="{{ asset('images/products/' . $product->image) }}"
                                        class="img-fluid product-default" />
                                    <div id="upload-product" class='d-none'></div>
                                    <input type="file" id="product-upload" style='display: none;'>
                                    <div class='p-2 feedback'></div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="row">
                                        <div class='col-sm-12'>
                                            <label><strong>Product Name</strong></label>
                                            <p>{{ $product->name }}</p>
                                        </div>
                                        <div class='col-sm-12'>
                                            <label><strong>Product Price</strong></label>
                                            <p>KSH {{ $product->price }}</p>
                                        </div>
                                        <div class='col-sm-12'>
                                            <label><strong>No. of Items</strong></label>
                                            <p>{{ $product->items }} Items</p>
                                        </div>
                                        <div class='col-sm-12'>
                                            <label><strong>Available</strong></label>
                                            <p>{{ $product->available }} Items</p>
                                        </div>
                                        <div class='col-sm-12'>
                                            <label><strong>Description</strong></label>
                                            <p>{{ nl2br($product->description) }}</p>
                                        </div>

                                        <div class="col-sm-12 text-right">
                                            <button class="btn btn-outline-primary btn-product-image">Product Image</button>
                                            <button class="btn btn-primary btn-edit-product-image" disabled="disabled">Save
                                                Product</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="exampleModalLabel">Product Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/shop/products/edit') }}" method="post" class="product-form">
                        @csrf
                        <input type='hidden' name='id' value='{{ $product->id }}'>

                        <div class='form-group'>
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" placeholder='Product Name'
                                value='{{ $product->name }}' required>
                        </div>
                        <div class='form-group'>
                            <label>Price</label>
                            <input type="number" name="price" class="form-control" placeholder="Price per Item"
                                value='{{ $product->price }}'>
                        </div>
                        <div class='form-group'>
                            <label>No. of Items</label>
                            <input type="text" name="items" class="form-control" placeholder="No. of Items"
                                value='{{ $product->items }}'>
                        </div>
                        <div class='form-group'>
                            <label>Description</label>
                            <textarea name='description' class="form-control" placeholder='Product Description' rows="4"
                                value='{{ $product->description }}'>Say Something</textarea>
                        </div>

                        <div class="form-group text-right">
                            <!--<a href="#" class="btn btn-outline-primary btn-upload-product">Save Products</a>-->
                            <button type="submit" class="btn btn-primary btn-submit-product">Edit
                                Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var parentW = $('#upload-product').closest('.col-sm-6').width();
            var vwidth = parentW;
            var vheight = vwidth;

            $uploadCrop = $('#upload-product').croppie({
                enableExif: true,
                viewport: {
                    width: vwidth * .9,
                    height: vheight * .9,
                    type: 'canvas'
                },

                boundary: {
                    width: parentW,
                    height: (parentW) * 4 / 4
                }
            });
            $('.btn-product-image').click(function() {
                $('#product-upload').click();
            });

            $('#product-upload').on('change', function() {
                $('.upload-product-result, .btn-edit-product-image').removeAttr('disabled');
                $('#upload-product').removeClass('d-none');
                $('.product-default').addClass('d-none');
                var reader = new FileReader();
                reader.onload = function(e) {
                    $uploadCrop.croppie('bind', {
                        url: e.target.result
                    }).then(function() {
                        console.log('jQuery bind complete');
                    });
                }
                reader.readAsDataURL(this.files[0]);
            });

            $('.upload-product-result').on('click', function(ev) {
                $uploadCrop.croppie('result', {
                    type: 'canvas',
                    size: 'viewport'
                }).then(function(resp) {
                    $('.feedback').html(
                        "<p class='text-center text-info'><i class='fas fa-spinner fa-pulse'></i> Saving... Please wait</p>"
                    );
                    $.ajax({
                        url: "{{ url('dashboard/shop/products/save') }}",
                        type: "POST",
                        data: {
                            "image": resp
                        },
                        success: function(data) {
                            try {
                                json = $.parseJSON(data);
                            } catch (e) {
                                // not json
                            }
                            $('.feedback').html(data.success);
                            window.location.href = "{{ url('dashboard/shop/products') }}";
                        },
                        error: function() {
                            $('.feedback').html(
                                "<p class='text-center text-danger'><i class='fas fa-warning'></i> Unable to save product</p>"
                            );
                        }
                    });
                });
            });

            $('.btn-edit-product-image').on('click', function(ev) {
                var id = $("input[name='id']").val();
                $uploadCrop.croppie('result', {
                    type: 'canvas',
                    size: 'viewport'
                }).then(function(resp) {
                    $('.feedback').html(
                        "<p class='text-center text-info'><i class='fas fa-spinner fa-pulse'></i> Saving... Please wait</p>"
                    );
                    $.ajax({
                        url: "{{ url('dashboard/shop/products/image/edit')}}",
                        type: "POST",
                        data: {
                            "id": id,
                            "image": resp
                        },
                        success: function(data) {
                            try {
                                json = $.parseJSON(data);
                            } catch (e) {
                                // not json
                            }
                            $('.feedback').html(data.success);
                            window.location.reload();
                        },
                        error: function() {
                            $('.feedback').html(
                                "<p class='text-center text-danger'><i class='fas fa-warning'></i> Unable to save product</p>"
                            );
                        }
                    });
                });
            });
        });
    </script>
@endpush
