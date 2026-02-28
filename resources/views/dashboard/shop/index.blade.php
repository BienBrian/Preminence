@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-shopping-bag'></i> Shop</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Shop</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-store fa-4x text-muted"></i>
                            </div>
                            <h3 class="font-weight-bold mb-3">Shop Coming Soon</h3>
                            <p class="text-muted lead mb-4" style="max-width:520px;margin:0 auto;">
                                We're preparing something special for our church family. Soon you'll be able to browse and purchase
                                faith-inspired merchandise right here -- from branded apparel and devotional journals to inspirational artwork
                                and accessories that celebrate our journey together in Christ.
                            </p>
                            <div class="row justify-content-center mt-4 mb-3">
                                <div class="col-sm-4 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body py-3">
                                            <i class="fas fa-tshirt fa-2x text-primary mb-2"></i>
                                            <h6 class="font-weight-bold">Church Apparel</h6>
                                            <small class="text-muted">T-shirts, hoodies & caps with our church branding</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body py-3">
                                            <i class="fas fa-book-open fa-2x text-success mb-2"></i>
                                            <h6 class="font-weight-bold">Devotionals & Books</h6>
                                            <small class="text-muted">Journals, devotional guides & study materials</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body py-3">
                                            <i class="fas fa-cross fa-2x text-danger mb-2"></i>
                                            <h6 class="font-weight-bold">Gifts & Keepsakes</h6>
                                            <small class="text-muted">Mugs, wristbands & inspirational wall art</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small">
                                All proceeds go towards funding church operations, community outreach programmes, and ministry growth.
                                Every purchase is a seed sown into the Kingdom.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
