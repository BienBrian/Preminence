@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-shopping-cart'></i> Products</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Products</li>
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
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                <div class="col">
                                    <h3 class="mb-0"><i class="fas fa-shopping-cart"></i> | Products</h3>
                                </div>-->
                                <div class="col text-right">
                                    <button class="btn btn-primary btn-sm btn-show-product" data-toggle="modal"
                                        data-target="#productModal"><i class='fas fa-shopping-cart'></i> New</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Available</th>
                                        <th scope="col">Posted</th>
                                        <th class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($products->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No Products
                                                yet</td>
                                        </tr>
                                    @endif
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>
                                                <div class="user-panel d-flex">
                                                    <div class="image">
                                                        <img src="{{ asset('images/products/' . $product->image) }}"
                                                            class='img-circle'>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ \Str::limit($product->name, $limit = 30, $end = '...') }}</td>
                                            <td>{{ \Str::limit($product->description, $limit = 30, $end = '...') }}</td>
                                            <td>{{ number_format($product->price, 2) }}</td>
                                            <td>{{ $product->available }} items</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($product->date_posted)->setTimezone('Africa/Nairobi')->format('d M, Y h:i A') }}</span>
                                            </td>
                                            <td class='text-right'>
                                                <a href="{{ url('dashboard/shop/products/' . $product->id) }}"
                                                    class='btn btn-primary btn-sm'>Details</a>
                                                <a href="#" onclick="return postAction('{{ url('dashboard/shop/products/remove/' . $product->id) }}', 'Remove this product?')"
                                                    class='btn btn-danger btn-sm'>Remove</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="col-sm-12 mt-3">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/shop/products/add') }}" method="post" class="product-form">
                        @csrf
                        <input type='hidden' name='id' value='0'>

                        <div class='form-group'>
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" placeholder='Product Name' required>
                        </div>
                        <div class='form-group'>
                            <label>Price</label>
                            <input type="number" name="price" class="form-control" placeholder="Price per Item">
                        </div>
                        <div class='form-group'>
                            <label>No. of Items</label>
                            <input type="text" name="items" class="form-control" placeholder="No. of Items">
                        </div>
                        <div class='form-group'>
                            <label>Description</label>
                            <textarea name='description' class="form-control" placeholder='Product Description' rows="4">Say Something</textarea>
                        </div>

                        <div class="form-group text-right">
                            <!--<a href="#" class="btn btn-outline-primary btn-upload-product">Save Products</a>-->
                            <button type="submit" class="btn btn-primary btn-submit-product">Save Products</button>
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

        });
    </script>
@endpush
