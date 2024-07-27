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
                            <h3 class="mb-0"><i class="fas fa-users"></i> | {{$name->name}}</h3>
                        </div>
                        <div class="col text-right">
                            <button class="btn btn-primary btn-sm btn-show-group" data-toggle="modal" data-target="#groupModal"><i class='fas fa-users'></i> Add User</button>
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
                                <th scope="col">email</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Deadline</th>
                                <th scope="col">Status</th>
                                <th class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($participants->isEmpty())
                                    <tr><td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No participants yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($participants as $participant)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td>{{$participant->firstname}} {{$participant->lastname}}</td>
                                        <td>{{ $participant->name }}</td>
                                        <td>{{number_format($participant->amount, 2)}}</td>
                                        <td>
                                            @if(\Carbon\Carbon::parse($participant->closes_on) > \Carbon\Carbon::now())
                                                <span class='text-success font-weight-bold'>{{\Carbon\Carbon::parse($participant->closes_on)->diffInDays(\Carbon\Carbon::now())}} day(s) remaining</span>
                                            @else
                                                <span class='text-danger font-weight-bold'>{{\Carbon\Carbon::parse($participant->closes_on)->format('d M, Y')}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($participant->status == 0)
                                            <span class='text-muted font-weight-bold'>Uncollected</span>
                                            @else
                                            <span class='text-success'>collected</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($participant->status == 0)
                                                <a href="{{url('users/participant/remove/'.$participant->id)}}" class='btn btn-danger btn-sm' data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash"></i></a>
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
                {{$participants->links()}}
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
                        <form action="{{url('/users/addparticipant')}}" method="post" class="group-form">
                            @csrf
                            <input type='hidden' name='id' value='0'>
                            <input type='hidden' name='group_id' value='{{$name->id}}'>

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
                                <label><small>User</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-user text-primary'></i></span>
                                    </div>
                                    <select name="user_id" class="custom-select">
                                        @foreach ($users as $user)
                                            <option value="{{$user->id}}">{{$user->firstname}} {{$user->lastname}} ({{$user->email}})</option>
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
