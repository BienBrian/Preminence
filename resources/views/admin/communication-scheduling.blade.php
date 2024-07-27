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
                                <span class="d-none d-md-block"><i class='fas fa-cogs'></i> Payment Settings</span>
                                <span class="d-md-none"><i class='fas fa-cogs'></i></span>
                            </a>

                            <a href="{{url('/funds/summary')}}" class="btn btn-sm btn-outline-primary">
                                <span class="d-none d-md-block"><i class='fas fa-stream'></i> summary</span>
                                <span class="d-md-none"><i class='fas fa-stream'></i></span>
                            </a>

                            <button class="btn btn-sm btn-primary showFundsModal" data-toggle="modal" data-target="#fundsModal">
                                <span class="d-none d-md-block"><i class='fas fa-plus-circle'></i> Add</span>
                                <span class="d-md-none"><i class='fas fa-plus-circle'></i></span></a>
                            </button>
                        </div>
                        <div class="col-sm-12">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#general" role="tab" aria-controls="home" aria-selected="true">General</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#daily" role="tab" aria-controls="profile" aria-selected="false">Daily</a>
                                </li>
                            </ul>
                            <div class="tab-content border-1" id="myTabContent">
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="home-tab">
                                    <form class="row search-funds-form pt-3" method='GET' action="{{url('funds/search')}}">
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control form-control-alternative" name="msearch" placeholder="search name, email"/>
                                        </div>
                                        
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control form-control-alternative mydatepicker" name="from" placeholder="From" readonly/>
                                        </div>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control form-control-alternative mydatepicker" name="to" placeholder="To" readonly/>
                                        </div>
                                        <div class="col-sm-3">
                                            <button class='btn btn-primary' style='width: 100%;'><i class='fas fa-search'></i> Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="daily" role="tabpanel" aria-labelledby="profile-tab">
                                    <form class="row daily-search-funds-form pt-3" method='GET' action="{{url('funds/search')}}">
                                        <div class="col">
                                            <select class="form-control" name="sources">
                                                <option value="0">All</option>
                                            @foreach($sources as $source)
                                                <option value="{{$source->id}}">{{$source->name}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control mydatepicker" name="date" placeholder="From" readonly/>
                                        </div>
                                        <div class="col">
                                            <button class='btn btn-primary' style='width: 100%;'><i class='fas fa-search'></i> Search</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--
                <div class="card-body bg-secondary">
                    <form class="row search-funds-form" method='GET' action="{{url('funds/search')}}">
                        <div class="col-sm-3">
                            <input type="text" class="form-control form-control-alternative" name="msearch" placeholder="search name, email"/>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control form-control-alternative mydatepicker" name="from" placeholder="From" readonly/>
                        </div>
                        <div class="col-sm-3">
                            <input type="text" class="form-control form-control-alternative mydatepicker" name="to" placeholder="To" readonly/>
                        </div>
                        <div class="col-sm-3">
                            <button class='btn btn-primary' style='width: 100%;'><i class='fas fa-search'></i> Search</button>
                        </div>
                    </form>
                </div>-->
                <div class="table-responsive">
                    <!-- Projects table -->
                    <table class="table align-items-center table-flush" id="funds">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Member</th>
                                <th scope="col">Amount</th>
                                <th scope="col">through</th>
                                <th scope="col">Type</th>
                                <th scope="col">Source</th>
                                <th scope="col">Date</th>
                                <th scope="col">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                          </tbody>
                          <tfoot>
                              <tr>
                                  <th scope="col">Totals</th>
                                  <th scope="col" colspan='6'></th>
                              </tr>
                          <tfoot>
                    </table>
                    <!--
                    <table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">id</th>
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
                                                <span class='badge badge-success'>Collections</span>
                                            @else
                                                <span class='badge badge-danger'>Expenditure</span>
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
                    </table>-->
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
                <div class="modal-body pt-1 pb-0 mb-0 bg-secondary">
                    <form action="/savefunds" method="post">
                        @csrf
                        <input type='hidden' name='id' value=''>
                        <div class='form-group'>
                            <label class='small'>Amount:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-dollar-sign text-primary'></i></span>
                                </div>
                                <input id="password" type="text" class="form-control" name="amount" placeholder='Enter Amount' required>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='small'>Source:</label>
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
                            <label class='small'>Paid Through:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon2"><i class='fas fa-donate text-primary'></i></span>
                                </div>
                                <select class="custom-select" name="mode" placeholder='Enter Name'>
                                    @foreach ($modes as $mode)
                                        <option value="{{$mode->id}}">{{$mode->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label class='small'>Description:</label>
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
