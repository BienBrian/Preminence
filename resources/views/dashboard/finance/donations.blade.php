@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-donate'></i> <b>Donations</b></h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Donations</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class='row'>
                <div class="col-xl-12 mb-5 mb-xl-0">
                    <div class="card shadow">
                        <!--<div class="card-header border-0">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="mb-0"><i class="fas fa-donate"></i> | Donations</h3>
                                        </div>
                                    </div>
                                </div>-->

                        <div class="table-responsive">
                            <!-- Projects table -->
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Source</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($donations->isEmpty())
                                        <tr>
                                            <td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No donations
                                                yet</td>
                                        </tr>
                                    @endif
                                    <?php $count = 1; ?>
                                    @foreach ($donations as $fund)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            <td scope='row'>{{ number_format($fund->amount, 2) }}</td>
                                            <td>{{ str_limit($fund->description, $limit = 30, $end = '...') }}</td>
                                            <td>
                                                @if ($fund->ftype == 0)
                                                    Collections
                                                @else
                                                    Expenditure
                                                @endif
                                            </td>
                                            <td>{{ $fund->name }}</td>
                                            <td>{{ $fund->firstname . ' ' . $fund->lastname }}</td>
                                            <td>{{ \Carbon\Carbon::parse($fund->updated_at)->format('d, M Y') }}</td>
                                            <td class='text-right'>
                                                <a href="{{ $fund->id }}"
                                                    class='btn btn-primary btn-sm fundsedit'>Edit</a>
                                                <a href="{{ url('removefund/' . $fund->id) }}"
                                                    class='btn btn-danger btn-sm'>Delete</a>
                                            </td>
                                        </tr>
                                        <?php $count++; ?>
                                    @endforeach
                                </tbody>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
