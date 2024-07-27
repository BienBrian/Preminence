@extends('layouts.user')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
        </div>
    </div>
</div>
<?php
    $permissions1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
    $permissions2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
?>
<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Assets</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->finances > 1 || $permissions2->finances > 1)
                                <button class="btn btn-sm btn-primary showAssetsModal" data-toggle="modal" data-target="#assetsModal"><i class='fas fa-plus-circle'></i> Add</button>
                            @endif
                        </div>
                    </div>
                </div>
                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class='fas fa-check-circle'></i> {!! \Session::get('success') !!}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class='fas fa-exclamation-circle'></i> {!! \Session::get('error') !!}
                    </div>
                @endif
                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong><i class='fas fa-exclamation-circle'></i> Whoops!</strong> There were some problems with your input.
                        @foreach ($errors->all() as $error)
                            <br><i class='fas fa-angle-right'></i> {{ $error }}</li>
                        @endforeach
                    </div>
                @endif
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Name</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Description</th>
                                <th scope="col">Verified by</th>
                                <th scope="col">Date</th>
                                <th scope="col" class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($assets->isEmpty())
                                    <tr><td colspan='7' class='text-center'> <i class='fas fa-ban'></i> No assets yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($assets as $asset)
                                    <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>{{$asset->name}}</td>
                                        <td scop='row'>{{ number_format($asset->amount, 2) }}</td>
                                        <td>{{ str_limit($asset->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{$asset->firstname." ".$asset->lastname}}</td>
                                        <td>{{\Carbon\Carbon::parse($asset->acquired)->format('d, M Y')}}</td>
                                        <td class='text-right'>
                                            @if($permissions1->finances > 1 || $permissions2->finances > 1)
                                                <a href="{{$asset->id}}" class='btn btn-primary btn-sm assetsedit'>Edit</a>
                                            @endif
                                            @if($permissions1->finances > 2 || $permissions2->finances > 2)
                                                <a href="{{url('removeasset/'.$asset->id)}}" class='btn btn-danger btn-sm'>Delete</a>
                                            @endif
                                        </td>
                                    </tr>
                                    <?php $count++; ?>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"><strong>Totals</strong></td>
                                    <td colspan="5"><strong class='text-default'>{{number_format($totals, 2)}}<strong></td>
                                </tr>
                            </tfoot>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="assetsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Assets</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="/saveassets" method="post">
                        @csrf
                        <input type='hidden' name='id' value='0'>
                        <div class='form-group'>
                            <label><small>Name</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-angle-right text-primary'></i></span>
                                </div>
                                <input type="text" class="form-control" name="name" placeholder='Asset Name' required>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Amount</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-dollar-sign text-primary'></i></span>
                                </div>
                                <input type="text" class="form-control" name="amount" placeholder='Enter Amount' required>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Acquired</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-calendar-alt text-primary'></i></span>
                                </div>
                                <input name="acquired" class="form-control datepicker" placeholder="Date Acquired"/>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Description</small></label>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-comments text-primary'></i></span>
                                </div>
                                <textarea name='description' class="form-control" placeholder='Description' rows="4">Say Something</textarea>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary btn-submit-funds">Add Assets</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
