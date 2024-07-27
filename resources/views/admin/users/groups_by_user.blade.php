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
                            <h3 class="mb-0"><i class="fas fa-users"></i> | Groups by You</h3>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-primary btn-sm btn-show-group" data-toggle="modal" data-target="#groupModal"><i class='fas fa-users'></i> New Group</button>
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
                                <th scope="col">Activity</th>
                                <th scope="col">Target</th>
                                <th scope="col">Deadline</th>
                                <th class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($groups->isEmpty())
                                    <tr><td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No groups yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($groups as $group)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td>{{$group->name}}</td>
                                        <td>{{$group->activity }}</td>
                                        <td>{{number_format($group->amount,2) }}</td>
                                        <td>
                                            @if(\Carbon\Carbon::parse($group->closes_on) > \Carbon\Carbon::now())
                                            <span class='text-success font-weight-bold'>{{\Carbon\Carbon::parse($group->closes_on)->diffInDays(\Carbon\Carbon::now())}} day(s) remaining</span>
                                            @else
                                            <span class='text-danger font-weight-bold'>{{\Carbon\Carbon::parse($group->closes_on)->format('d M, Y')}}</span>
                                            @endif
                                        </td>
                                        <td class='text-right'>
                                            <a href="{{url('/users/participants/'.$group->id)}}" class='btn btn-outline-default btn-sm' data-toggle="tooltip" data-placement="bottom" title="participants"><i class="fas fa-users"></i></a>
                                            @if($group->status == 0)
                                                <a href="{{url('users/group/remove/'.$group->id)}}" class='btn btn-danger btn-sm' data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash"></i></a>
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
            <div class="col-sm-12 mt-3">
                {{$groups->links()}}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="groupModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header pb-0 mb-0">
                        <h4 class="modal-title" id="exampleModalLabel">New Group</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0">
                        <form action="{{url('/users/addgroup')}}" method="post" class="group-form">
                            @csrf
                            <input type='hidden' name='id' value='0'>
                            <input type='hidden' name='activity' value='0'>
                            <div class='form-group'>
                                <label><small>Group Name</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-users text-primary'></i></span>
                                    </div>
                                    <input name="name" class="form-control" placeholder="Group Name">
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Amount</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-dollar-sign text-primary'></i></span>
                                    </div>
                                    <input name="amount" class="form-control" placeholder="Amount">
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Activity</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-retweet text-primary'></i></span>
                                    </div>
                                    <select name="activity" class="custom-select">
                                        @foreach ($activities as $activity)
                                            <option value="{{$activity->id}}">{{$activity->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
