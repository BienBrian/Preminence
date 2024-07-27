@extends('layouts.admin')

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

                            <a href="{{url('/fundsource')}}" class="btn btn-sm btn-outline-primary">
                                <span class="d-none d-md-block"><i class='fas fa-plus'></i> Fund Types</span>
                                <span class="d-md-none"><i class='fas fa-plus'></i></span></a>
                            <button class="btn btn-sm btn-primary showFundsModal" data-toggle="modal" data-target="#fundsModal">
                                <span class="d-none d-md-block"><i class='fas fa-plus-circle'></i> Add</span>
                                <span class="d-md-none"><i class='fas fa-plus-circle'></i></span></a>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-secondary">
                    <form class="row" method='GET' action="{{url('funds/search')}}">
                        <div class="col-sm-4">
                            <input type="text" class="form-control form-control-alternative datepicker" name="from" placeholder="From" value="{{$from}}" readonly/>
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control form-control-alternative datepicker" name="to" placeholder="To" value="{{$to}}"readonly/>
                        </div>
                        <div class="col-sm-4">
                            <button class='btn btn-primary' style='width: 100%;'><i class='fas fa-search'></i> Search</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush" id="funds">
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
                                @if($funds == null)
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
                                        <td>{{\Carbon\Carbon::parse($fund->created_at)->format('d, M Y')}}</td>
                                        <td class='text-right'>
                                            <a href="{{$fund->id}}" class='btn btn-primary btn-sm fundsedit'>Edit</a>
                                            <a href="{{url('removefund/'.$fund->id)}}" class='btn btn-danger btn-sm'>Delete</a>
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
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="exampleModalLabel">Funds</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-1 pb-0 mb-0">
                    <form action="/savefunds" method="post">
                        @csrf
                        <input type='hidden' name='id' value=''>
                        <!--<div class='form-group'>
                            <label><small>User</small></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-donate text-primary'></i></span>
                                </div>
                                <select class="form-control" name="users">
                                    <option>Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{$user->id}}">{{$user->firstname}} {{$user->lastname}} ({{$user->email}})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>-->
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
                                <select class="custom-select" name="ftype" placeholder='Enter Name'>
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
                                <select class="custom-select" name="source" placeholder='Enter Name'>
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
