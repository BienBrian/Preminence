@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-pie'></i> <b>Overview</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Overview</li>
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
                <div class="col-xl-3 col-lg-6">
                    <div class="card border border-primary">
                        <div class="card-body">
                            <h5><i class='fas fa-donate'></i> Collected (KES)</h5>
                            <h6 class="text-primary">{{ number_format($collected, 2) }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card border border-primary">
                        <div class="card-body">
                            <h5><i class='fas fa-receipt'></i> Spent (KES)</h5>
                            <h6 class="text-primary">{{ number_format($spent, 2) }}</h6>
                        </div>
                    </div>
                </div>


                <div class="col-xl-3 col-lg-6">
                    <div class="card border border-primary">
                        <div class="card-body">
                            <h5><i class='fas fa-wallet'></i> Balance (KES)</h5>
                            <h6>{{ number_format($collected - $spent, 2) }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card border border-primary">
                        <div class="card-body">
                            <h5><i class='fas fa-wallet'></i> Donations (KES)</h5>
                            <h6>{{ number_format($donation, 2) }}</h6>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
@push('js')
@endpush
