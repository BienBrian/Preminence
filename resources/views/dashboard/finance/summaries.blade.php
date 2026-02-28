@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-chart-bar'></i> Daily Summaries (Today)</h5>
                </div><!-- /.col -->
                <div class="d-none d-sm-block col-sm-6 text-right">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Daily Summaries</li>
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
                        <div class="card-header border-0">
                            <div class="row align-items-center">
                                <!--
                                                <div class="col">
                                                    <h3>Daily Summaries (Today)</h3>
                                                </div>-->
                                <div class="col-sm-12 text-right">

                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#summaryModal">
                                        <span class="d-none d-md-block"><i class='fas fa-cog'></i> Settings</span>
                                        <span class="d-md-none"><i class='fas fa-cog'></i></span></a>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert bg-white">
                        <h3>{{ $name != null ? $name->name : 'Totals' }}</h3>
                        <?php $addition = 0;
                        $subtraction = 0; ?>
                        @foreach ($funds as $fund)
                            <?php $op = $operations->where('name', $fund->name); ?>
                            @foreach ($op as $p)
                                @if ($p->operation == 0 || $p->operation == null)
                                    @if ($fund->ftype == 0)
                                        <?php $addition += $fund->totals; ?>
                                    @else
                                        <?php $addition -= $fund->totals; ?>
                                    @endif
                                @else
                                    @if ($fund->ftype == 0)
                                        <?php $subtraction += $fund->totals; ?>
                                    @else
                                        <?php $subtraction -= $fund->totals; ?>
                                    @endif
                                @endif
                            @endforeach
                        @endforeach

                        {{ number_format($addition, 2) }} - {{ number_format(abs($subtraction), 2) }} =
                        {{ number_format($addition - abs($subtraction), 2) }}
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    @foreach ($sources as $source)
                                        <th colspan="2">{{ $source->name }}</th>
                                    @endforeach
                                    <th colspan="2">Totals</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modes as $mode)
                                    <tr>
                                        <td><strong>{{ $mode->name }}</strong></td>
                                        <?php $expenses = 0;
                                        $coll = 0;
                                        $totals = []; ?>
                                        @foreach ($sources as $source)
                                            <?php
                                            $fund = $funds
                                                ->where('source', $source->id)
                                                ->where('name', $mode->name)
                                                ->first();
                                            ?>
                                            @if ($fund != null)
                                                @if ($fund->ftype == 0)
                                                    <?php $coll += doubleval($fund->totals); ?>
                                                    <td class='text-success'>{{ number_format($fund->totals, 2) }}</td>
                                                    <td>0.00</td>
                                                @else
                                                    <?php $expenses += doubleval($fund->totals); ?>
                                                    <td>0.00</td>
                                                    <td class='text-danger'>{{ number_format($fund->totals, 2) }}</td>
                                                @endif
                                            @else
                                                <td>0.00</td>
                                                <td>0.00</td>
                                            @endif
                                        @endforeach
                                        <td class='text-success'><strong>{{ number_format($coll, 2) }}</strong></td>
                                        <td class='text-danger'><strong>{{ number_format($expenses, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <!--<tr class="text-success">
                                                        <td>Totals</td>
                                                    </tr>-->
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- Modal -->
    <div class="modal fade" id="summaryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="exampleModalLabel">Settings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/finances/summaries/settings/add') }}" method="post">
                        @csrf
                        <input type='hidden' name='id' value="{{ $name != null ? $name->id : '0' }}">
                        <div class='form-group'>
                            <label>Summary Name:</label>
                            <input type="text" class="form-control" name="summary" placeholder='Enter Summary Name'
                                value="{{ $name != null ? $name->name : '' }}" required>
                        </div>
                        <div class="form-group">
                            <h5>Add</h5>
                            @foreach ($operations as $op)
                                @if ($op->operation != null)
                                    @if ($op->operation == 0)
                                        <span class='badge badge-primary'>{{ $op->name }}</span>
                                    @endif
                                @else
                                    <span class='badge badge-primary'>{{ $op->name }}</span>
                                @endif
                            @endforeach
                            <h5 class='mt-3'>Subtract</h5>
                            @foreach ($operations as $op)
                                @if ($op->operation != null)
                                    @if ($op->operation == 1)
                                        <span class='badge badge-warning'>{{ $op->name }}</span>
                                    @endif
                                @endif
                            @endforeach
                        </div>

                        <div class='form-group'>
                            <label>Fund Mode: </label>
                            <select class="custom-select" name="mode">
                                @foreach ($modes as $mode)
                                    <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class='form-group'>
                            <label>Operation: </label>
                            <select class="custom-select" name="operation">
                                <option value="0">Add</option>
                                <option value="1">Subtract</option>
                            </select>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
