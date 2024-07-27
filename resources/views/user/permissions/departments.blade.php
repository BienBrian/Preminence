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
                            <h3 class="mb-0"><i class="fas fa-boxes"></i> | Departments</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->departments > 1 || $permissions2->departments > 1)
                                <button class="btn btn-sm btn-primary btn-show-department" data-toggle="modal" data-target="#departmentModal"><i class='fas fa-circle-add'></i> Add New</button>
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
                                <th scope="col">Description</th>
                                <th scope="col">Leader</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($departments->isEmpty())
                                    <tr><td colspan='6' class='text-center'> <i class='fas fa-ban'></i> No departments yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($departments as $department)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>
                                            {{ str_limit($department->name, $limit = 30, $end = '...') }}
                                        </td>
                                        <td>{{ str_limit($department->description, $limit = 30, $end = '...') }}</td>
                                        <td>{{$department->firstname." ".$department->lastname}}</td>
                                        <td>{{$department->contact}}</td>
                                        <td class='text-right'>

                                        @if($permissions1->departments > 1 || $permissions2->departments > 1)
                                            <a href="{{$department->id}}" class='btn btn-primary btn-sm edit-department'>Edit</a>
                                        @endif
                                        @if($permissions1->departments > 2 || $permissions2->departments > 2)
                                            <a href="{{url('deletedepartment/'.$department->id)}}" class='btn btn-danger btn-sm'>Delete</a>
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

            <div class='col-sm-12 mt-2 mb-2'>
                {{$departments->links()}}
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="departmentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header pb-0 mb-0">
                        <h4 class="modal-title" id="exampleModalLabel">Department</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-1 pb-0 mb-0">
                        <form action="/adddepartment" method="post" class="department-form">
                            @csrf
                            <input type='hidden' name='id' value='0'>

                            <div class='form-group'>
                                <label><small>Name</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-angle-right text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="name" placeholder='Department Name' required>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Contact</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-phone text-primary'></i></span>
                                    </div>
                                    <input name="contact" class="form-control" placeholder="Department Contact">
                                </div>
                            </div>
                            <div class='form-group'>
                                <label><small>Users</small></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-users text-primary'></i></span>
                                    </div>
                                    <select name="user_id" class="form-control">
                                        @foreach($users as $user)
                                            <option value="{{$user->id}}">{{$user->firstname." ".$user->lastname}}</option>
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
                                    <textarea name='description' class="form-control" placeholder='Event Description' rows="4">Say Something</textarea>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary btn-submit-departments">Save Department</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
