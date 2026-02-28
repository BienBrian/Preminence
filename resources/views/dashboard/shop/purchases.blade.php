@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-shopping-cart'></i> Purchases</h5>
                </div><!-- /.col -->
                <div class="col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Purchases</li>
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
                                    <h3 class="mb-0"><i class="fas fa-shopping-cart"></i> | Purchases</h3>
                                </div>-->
                                <div class="col text-right">
                                    <!--<a href="{{ url('products') }}" class="btn btn-primary btn-sm btn-show-product"><i class='fas fa-shopping-cart'></i> Products</a>-->
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Item</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Totals</th>
                                        <th scope="col">Purchased on</th>
                                        <th class='text-right'>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($purchases->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No Purchases
                                                yet</td>
                                        </tr>
                                    @endif
                                    @foreach ($purchases as $purchase)
                                        <tr>
                                            <td>
                                                <div class="media align-items-center">
                                                    <span class="avatar avatar-sm rounded-circle">
                                                        <img src="{{ asset('images/products/' . $purchase->image) }}">
                                                    </span>
                                                </div>
                                            </td>
                                            <td>{{ $purchase->firstname }} {{ $purchase->lastname }}</td>
                                            <td>{{ str_limit($purchase->name, $limit = 30, $end = '...') }}</td>
                                            <td>{{ $purchase->items }}</td>
                                            <td>{{ $purchase->quantity }}</td>
                                            <td>{{ number_format($purchase->quantity * $purchase->price, 2) }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($purchase->date_bought)->setTimezone('Africa/Nairobi')->format('d M, Y h:i A') }}</span>
                                            </td>
                                            <td class='text-right'>
                                                <a href="{{ url('products/' . $purchase->id) }}"
                                                    class='btn btn-primary btn-sm'>View Product</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="col-sm-12 mt-3">
                        {{ $purchases->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
