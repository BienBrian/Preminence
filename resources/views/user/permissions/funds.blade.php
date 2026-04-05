@extends('layouts.user')

@section('content')
<!-- Header -->
<div class="header bg-gradient-primary pb-6 pt-5 pt-md-6">
    <div class="container-fluid">
        <div class="header-body">
        </div>
    </div>
</div>

<!-- Page content -->
<div class="container-fluid mt--5">
    <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Funds</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->finances > 1 || $permissions2->finances > 1)
                                <a href="{{url('users/fundsource')}}" class="btn btn-sm btn-outline-primary">
                                    <span class="d-none d-md-block"><i class='fas fa-plus'></i> Fund Types</span>
                                    <span class="d-md-none"><i class='fas fa-plus'></i></span>
                                </a>
                            @endif
                            @if($permissions1->finances > 1 || $permissions2->finances > 1)
                                <button class="btn btn-sm btn-primary showFundsModal" data-toggle="modal" data-target="#fundsModal">
                                    <span class="d-none d-md-block"><i class='fas fa-plus-circle'></i> Add</span>
                                    <span class="d-md-none"><i class='fas fa-plus-circle'></i></span></a>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class='fas fa-check-circle'></i> {{ \Session::get('success') }}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissable m-1">
                        <a href="#" class="close text-white" data-dismiss="alert" aria-label="close">&times;</a>
                            <i class='fas fa-exclamation-circle'></i> {{ \Session::get('error') }}
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
                                <th scope="col">Amount</th>
                                <th scope="col">Description</th>
                                <th scope="col">Type</th>
                                <th scope="col">Source</th>
                                <th scope="col">User</th>
                                <th scope="col">Date</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($funds->isEmpty())
                                    <tr><td colspan='4' class='text-center'> <i class='fas fa-ban'></i> No funds yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($funds as $fund)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>{{ number_format($fund->amount, 2) }}</td>
                                        <td>{{ str_limit($fund->description, $limit = 30, $end = '...') }}</td>
                                        <td>
                                            @if($fund->ftype == 0)
                                                Collections
                                            @else
                                                Expenditure
                                            @endif
                                        </td>
                                        <td>{{$fund->name}}</td>
                                        <td>{{$fund->firstname." ".$fund->lastname}}</td>
                                        <td>{{\Carbon\Carbon::parse($fund->updated_at)->format('d, M Y')}}</td>
                                        <td class='text-right'>
                                            @if($permissions1->finances > 1 || $permissions2->finances > 1)
                                                <a href="{{$fund->id}}" class='btn btn-primary btn-sm fundsedit'>Edit</a>
                                            @endif
                                            @if($permissions1->finances > 2 || $permissions2->finances > 2)
                                                <a href="{{url('removefund/'.$fund->id)}}" class='btn btn-danger btn-sm'>Delete</a>
                                            @endif
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


    <!-- Modal -->
    <div class="modal fade" id="fundsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header pb-0 mb-0">
                    <h4 class="modal-title" id="exampleModalLabel">Funds</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="/savefunds" method="post">
                        @csrf
                        <input type='hidden' name='id' value=''>
                        <div class='form-group'>
                            <label><small>Amount</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-dollar-sign text-primary'></i></span>
                                </div>
                                <input id="password" type="text" class="form-control" name="amount" placeholder='Enter Amount' required>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Funds Type</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-star text-primary'></i></span>
                                </div>
                                <select class="form-control" name="ftype" placeholder='Enter Name'>
                                    <option value="0">Collection</option>
                                    <option value="1">Expenditure</option>
                                </select>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label><small>Source</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-donate text-primary'></i></span>
                                </div>
                                <select class="form-control" name="source" placeholder='Enter Name'>
                                    @foreach ($sources as $source)
                                        <option value="{{$source->id}}">{{$source->name}}</option>
                                    @endforeach
                                </select>
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
                            <button type="submit" class="btn btn-primary btn-submit-funds">Add Funds</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
