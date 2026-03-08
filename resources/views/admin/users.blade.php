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
                        <div class="col-sm-4">
                            <h3 class="mb-0"><i class="fas fa-users"></i> | Users</h3>
                        </div>
                        <div class="col-sm-8 text-right">
                            <a href="{{url('export/users')}}" class='btn btn-outline-primary btn-sm mb-1 '><span class="d-block d-lg-none"><i class="fas fa-download"></i></span><span class="d-none d-lg-block"> Download</span></a>
                            <button class='btn btn-primary btn-sm mb-1' data-toggle="modal" data-target="#importUsersModal"><span class="d-block d-lg-none"><i class="fas fa-cloud"></i></span><span class="d-none d-lg-block"> Import Users</span></button>
                            <button class='btn btn-primary btn-sm mb-1 btn-add-user' data-toggle="modal" data-target="#addUserModal"><span class="d-block d-lg-none"><i class="fas fa-user-plus"></i></span><span class="d-none d-lg-block"> Add User</span></button>
                            <a href="{{url('userroles')}}" class="btn btn-sm btn-primary mb-1"><span class="d-block d-lg-none"><i class="fas fa-lock"></i></span><span class="d-none d-lg-block">User Roles</span></a>
                        </div>
                    </div>
                </div>
                <div class='card-body border-top'>
                    <div class="row">
                        <div class='col-sm-6'>
                            <button class='btn btn-success btn-sm pl-2 pr-2 pt-1 pb-1 mb-2 refresh-users-table' data-toggle='tooltip' title='Refresh Users Table'><span class="d-block d-lg-none"><i class="fas fa-sync"></i></span><span class="d-none d-lg-block">Refresh</span></button>
                            <button class='btn btn-primary btn-sm pl-2 pr-2 pt-1 pb-1 mb-2 activate-users' data-toggle='tooltip' title='Activate users'><span class="d-block d-lg-none"><i class="fas fa-retweet"></i></span><span class="d-none d-lg-block">Activate</span></button>
                            <button class='btn btn-warning btn-sm pl-2 pr-2 pt-1 pb-1 mb-2 deactivate-users' data-toggle='tooltip' title='Deactivate Users'><span class="d-block d-lg-none"><i class="fas fa-ban"></i></span><span class="d-none d-lg-block">Deactivate</span></button>
                            <button class='btn btn-danger btn-sm pl-2 pr-2 pt-1 pb-1 mb-2 delete-users' data-toggle='tooltip' title='Delete Users Permanently'><span class="d-block d-lg-none"><i class="fas fa-trash"></i></span><span class="d-none d-lg-block">Delete</span></button>
                        </div>
                        <form class="col-sm-6 search-users-form">
                            <div class="form-group">
                                <div class="input-group">
                                    <input class="form-control" name="msearch" placeholder="search name, email, phone">
                                    <div class="input-group-append">
                                        <button class='btn btn-primary'><i class='fas fa-search'></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">

                    <table class="table" id="users-table">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col" class='notexport'>id</th>
                                <th scope="col" class='notexport'>profile</th>
                                <th scope="col">firstname</th>
                                <th scope="col">lastname</th>
                                <th scope="col">email</th>
                                <th scope="col">joined</th>
                                <th scope="col" class='notexport'>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <!-- Projects table -->
                    <!--<table class="table align-items-center table-flush">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">user</th>
                                <th scope="col">Email</th>
                                <th scope="col">Status</th>
                                <th scope="col">Role</th>
                                <th scope="col">Joined</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($users->isEmpty())
                                    <tr><td colspan='7' class='text-center'> <i class='fas fa-ban'></i> No users yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($users as $user)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td scope='row'>
                                            <div class="media align-items-center">
                                                <a href="#" class="avatar rounded-circle mr-3">
                                                    <img alt="Image placeholder" src="{{$user->image == "" ? asset('profile_images/default.jpg'): asset('profile_images/'.$user->image)}}">
                                                </a>
                                                <div class="media-body">
                                                    <span class="mb-0 text-sm">{{ $user->firstname." ".$user->lastname}}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->status == 1)
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-success"></i> Activated
                                                </span>
                                            @else
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-warning"></i> Deactivated
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->role == 0)
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-default"></i> {{$user->name}}
                                                </span>
                                            @elseif($user->role == 1)
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-danger"></i> {{$user->name}}
                                                </span>
                                            @else
                                                <span class="badge badge-dot mr-4">
                                                    <i class="bg-primary"></i> {{$user->name}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{\Carbon\Carbon::parse($user->created_at)->format('d M, Y')}}
                                        </td>
                                        <td class='text-right'>
                                            @if($user->role !=1)
                                                <a href="{{$user->id}}" class="btn btn-success p-1 pl-2 pr-2 user-permissions" data-toggle="tooltip" data-placement="bottom" title="Permissions">
                                                    <i class='fas fa-lock'></i>
                                                </a>
                                                <div class="dropdown">
                                                    <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-placement="bottom" title="user roles">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                        @foreach($roles as $role)
                                                            <a class="dropdown-item" href="{{url('users/userrole/'.$user->id.'/'.$role->role)}}">{{$role->name}}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <a href="{{$user->id}}" class="btn btn-primary p-1 pr-2 pl-2 btn-edit-user" data-toggle="tooltip" data-placement="bottom" title='Edit User'>
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                                |
                                                @if($user->status == 1 )
                                                    <a href="{{url('users/deactivate/'.$user->id)}}" class="btn btn-danger p-1 pr-2 pl-2" data-toggle="tooltip" data-placement="bottom" title='de-activate'>
                                                        <i class='fas fa-ban'></i>
                                                    </a>
                                                @else
                                                    <a href="{{url('users/activate/'.$user->id)}}" class="btn btn-success p-1 pr-2 pl-2" data-toggle="tooltip" data-placement="bottom" title='activate'>
                                                        <i class='fas fa-sync-alt'></i>
                                                    </a>

                                                    <a href="{{url('users/delete/'.$user->id)}}" class="btn btn-danger p-1 pr-2 pl-2" data-toggle="tooltip" data-placement="bottom" title='Permanently Delete'>
                                                        <i class='fas fa-trash'></i>
                                                    </a>

                                                @endif
                                            @endif
                                            |
                                            <a href="{{$user->id}}" class="btn btn-primary p-1 pr-2 pl-2 btn-sms" data-toggle="tooltip" data-placement="bottom" title='Send SMS'>
                                                <i class='fas fa-comments'></i>
                                            </a>
                                            <a href="{{$user->id}}" class="btn btn-outline-primary p-1 pr-2 pl-2 btn-email" data-toggle="tooltip" data-placement="bottom" title='Send Email' style="border-width: 2px;">
                                                <i class='fas fa-envelope'></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php $count++; ?>
                                @endforeach
                            </tbody>
                        </thead>
                    </table>-->
                </div>
            </div>

            <!--<div class='col-sm-12 mt-2 mb-2'>
                {{$users->links()}}
            </div>-->
        </div>
    </div>



    <!-- Import Modal -->
    <div class="modal fade" id="importUsersModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="exampleModalLabel">Import from Excel File</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{url('import/users')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <p>Upload CSV or Excel with the following format</p>
                        <table class='table'>
                            <tr>
                                <td>First Name</td>
                                <td>Last Name</td>
                                <td>Email Address</td>
                                <td>Phone</td>
                            </tr>
                        </table>
                        <p class='text-danger'>*No headings required</p>
                        <div class="form-group text-right">
                            <input type="file" name="import_file" class="form-control">
                        </div>
                        <div class="text-right">
                            <button class='btn btn-primary'>Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="permissionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">User Permissions</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{url('/adduserpermissions')}}" method="post" class="row d-flex align-items-center permission-form">
                            @csrf
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <input type='hidden' name='userid' value='0'>
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="dashboard" id="dashboardc" type="checkbox" value="1">
                                    <label class="custom-control-label" for="dashboardc">Dashboard</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="dashboardr" class="custom-control-input" id="dashboardr" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="dashboardr">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="dashboardr" class="custom-control-input" id="dashboardr2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="dashboardr2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="dashboardr" class="custom-control-input" id="dashboardr3" type="radio" value="3">
                                        <label class="custom-control-label" for="dashboardr3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6  col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="testimonial" id="testimonialc" type="checkbox" value="2">
                                    <label class="custom-control-label" for="testimonialc">Testimonials</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="testimonialr" class="custom-control-input" id="testimonialr" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="testimonialr">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="testimonialr" class="custom-control-input" id="testimonialr2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="testimonialr2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="testimonialr" class="custom-control-input" id="testimonialr3" type="radio" value="3">
                                        <label class="custom-control-label" for="testimonialr3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6  col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="website" id="websitec" type="checkbox" value="2">
                                    <label class="custom-control-label" for="websitec">Website</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="websiter" class="custom-control-input" id="websiter" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="websiter">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="websiter" class="custom-control-input" id="websiter2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="websiter2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="websiter" class="custom-control-input" id="websiter3" type="radio" value="3">
                                        <label class="custom-control-label" for="websiter3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="finance" id="financec" type="checkbox" value="3">
                                    <label class="custom-control-label" for="financec">Finances</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="financer" class="custom-control-input" id="financer" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="financer">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="financer" class="custom-control-input bg-danger" id="financer2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="financer2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="financer" class="custom-control-input" id="financer3" type="radio" value="3">
                                        <label class="custom-control-label" for="financer3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="spiritual" id="spiritualc" type="checkbox" value="4">
                                    <label class="custom-control-label" for="spiritualc">Spiritual</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="spiritualr" class="custom-control-input" id="spiritualr" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="spiritualr">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="spiritualr" class="custom-control-input" id="spiritualr2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="spiritualr2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="spiritualr" class="custom-control-input" id="spiritualr3" type="radio" value="3">
                                        <label class="custom-control-label" for="spiritualr3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="event" id="eventc" type="checkbox" value="5">
                                    <label class="custom-control-label" for="eventc">Events & Notices</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-6">
                                        <input name="eventr" class="custom-control-input" id="eventr" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="eventr">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-6">
                                        <input name="eventr" class="custom-control-input" id="eventr2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="eventr2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="eventr" class="custom-control-input" id="eventr3" type="radio" value="3">
                                        <label class="custom-control-label" for="eventr3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="shop" id="shopc" type="checkbox" value="6">
                                    <label class="custom-control-label" for="shopc">Shop</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="shopr" class="custom-control-input" id="shopr" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="shopr">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="shopr" class="custom-control-input" id="shopr2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="shopr2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="shopr" class="custom-control-input" id="shopr3" type="radio" value="3">
                                        <label class="custom-control-label" for="shopr3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="communication" id="communicationc" type="checkbox" value="7">
                                    <label class="custom-control-label" for="communicationc">Communication</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="communicationr" class="custom-control-input" id="communicationr" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="communicationr">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="communicationr" class="custom-control-input" id="communicationr2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="communicationr2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="communicationr" class="custom-control-input" id="communicationr3" type="radio" value="3">
                                        <label class="custom-control-label" for="communicationr3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="user" id="userc" type="checkbox" value="8">
                                    <label class="custom-control-label" for="userc">Users</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="userr" class="custom-control-input" id="userr" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="userr">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="userr" class="custom-control-input" id="userr2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="userr2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-4">
                                        <input name="userr" class="custom-control-input" id="userr3" type="radio" value="3">
                                        <label class="custom-control-label" for="userr3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4 mb-1 alert alert-primary bg-secondary text-dark border-0">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input class="custom-control-input" name="article" id="articlec" type="checkbox" value="9">
                                    <label class="custom-control-label" for="articlec">Articles</label>
                                </div>
                                <div class='row'>
                                    <div class="custom-control custom-radio mb-3 col-xs-6">
                                        <input name="articler" class="custom-control-input" id="articler" type="radio" value="1" checked="">
                                        <label class="custom-control-label mr-1" for="articler">View</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3 col-xs-6">
                                        <input name="articler" class="custom-control-input" id="articler2" type="radio" value="2">
                                        <label class="custom-control-label mr-1" for="articler2">Edit</label>
                                    </div>
                                    <div class="custom-control custom-radio mb-3">
                                        <input name="articler" class="custom-control-input" id="articler3" type="radio" value="3">
                                        <label class="custom-control-label" for="articler3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 mt-1 form-group text-right">
                                <span class='d-none feedback'></span>
                                <button type="submit" class="btn btn-primary btn-submit-permissions">Save Permissions</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <div class="modal fade" id="smsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">Message Modal</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-3 pb-0 mb-0 bg-secondary">
                        <form action='{{url("users/sendsms")}}' method='post' class="sms-form">
                            @csrf
                            <div class="form-group">
                                <label><small>User Number</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type='text' class="form-control form-control-alternative" name='numbers' placeholder="+254791162496" readonly/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><small>Message</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-comments"></i></span>
                                    </div>
                                    <textarea name='message' class="form-control form-control-alternative" placeholder="Your text Message here" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="form-group text-right">
                                <span class='feedback d-none'></span>
                                <button type='submit' class="btn btn-primary">Send SMS</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Modal -->
        <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">Message Modal</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-3 pb-0 mb-0 bg-secondary">
                        <form action='{{url("users/sendemail")}}' method='post' class="email-form">
                            @csrf
                            <div class="form-group">
                                <label><small>Email Address</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    </div>
                                    <input type='text' class="form-control form-control-alternative" name='email' placeholder="email address" readonly/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><small>Subject</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type='text' class="form-control form-control-alternative" name='subject' placeholder="Email Subject"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><small>Message</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-comments"></i></span>
                                    </div>
                                    <textarea name='message' class="form-control form-control-alternative" placeholder="Your Message here" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="form-group text-right">
                                <button type='submit' class="btn btn-primary">Send Email</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">Add New User</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-3 pb-0 mb-0 bg-secondary">
                        <form action='{{url("users/add")}}' method='post' class='form-add-user'>
                            @csrf
                            <input type="hidden" name="id" value="0">
                            <div class="form-group">
                                <label><small>First Name</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type='text' class="form-control form-control-alternative" name='fname' placeholder="First Name"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><small>Last Name</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type='text' class="form-control form-control-alternative" name='lname' placeholder="Last Name"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><small>Email Address</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" name="email" class="form-control form-control-alternative" placeholder="Email Adress"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><small>Phone Number</small></label>
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+254</span>
                                    </div>
                                    <input type='text' name='phone' class="form-control form-control-alternative" placeholder="Phone Number"/>
                                </div>
                            </div>
                            <div class="form-group text-right">
                                <span class='d-none feedback'></span>
                                <button type='submit' class="btn btn-primary">Save User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
@push('js')
<script>
    $(document).ready(function(){
        var userstable = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                oLanguage: {sProcessing: "<i class='fas fa-spinner fa-pulse'></i> Processing..."},
                dom: 'lBrtip',
                buttons: [
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file"></i> CSV',
                        className: '',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: '',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    },{
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: '',
                        exportOptions: {
                            columns: ':not(.notexport)'
                        }
                    }],
                "lengthMenu": [ [100, 250, 500, 1000, -1], [100, 250, 500, 1000, "All"] ],
                ajax: //"{{ url('datatables/users') }}",
                    {
                    url: "{{ url('datatables/users') }}",
                    data: function (d) {
                            d.msearch = $('input[name=msearch]').val();
                        }
                    },
                columns: [
                    {data: 'checkbox', name: 'id', searchable:false, orderable:false},
                    {data: "profile", name: "profile", orderable: false, searchable: false},
                    {data: 'firstname', name: "firstname"},
                    {data: "lastname", name: "lastname"},
                    {data: "myemail", name: "email"},
                    {data: 'joined', name: 'joined', orderable: false, searchable: false},
                    {data: "action", name: 'action', orderable: false, searchable: false},
                    //{data: 'action', name: 'action', orderable: false, searchable: false},
                ]
            });
            $('.search-users-form').submit(function(e){
                e.preventDefault();
                userstable.draw();
            });
            $("input[name='checkall']").on("change", function(){
                if($(this).prop("checked") == true){
                    $("#users-table tbody input").prop("checked", true);
                }else{
                    $("#users-table tbody input").prop("checked", false);
                }
            });

            $('.refresh-users-table').click(function(){
                $('input[name="msearch"]').val("");
                userstable.draw();
            });
            $('.activate-users').click(function () {
                var success = 0;
                var failed = 0;
                var count = 0;
                var totals = 0;
                $('#users-table').find('input[type="checkbox"]:checked').each(function () {
                    count++;
                });

                $('.user-not').html("<p class='text-white'><i class='fas fa-spinner fa-pulse'></i> Activating <strong>"+(count-success)+"</strong> of <strong>"+count+"</strong> User(s)...</p>");
                $('.user-not').show();
                if(count == 0){
                    $('.user-not').html("<p class='text-white'><i class='fas fa-exclamation-triangle'></i> No user(s) selected for activation!</p>");
                    setTimeout(() => {
                        $('.user-not').hide();
                    }, 2000);
                }
                $('#users-table').find('input[type="checkbox"]:checked').each(function () {
                    var i = $(this).val();
                    var myurl = "{{url('users/activate/')}}/"+i;
                    $.ajax({
                        url: myurl,
                        type: "GET",
                    }).done(function(data){
                        success++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Activated<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                userstable.draw();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    }).fail(function(){
                        failed++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Activated<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                userstable.draw();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    });
                });
            });
            $('.deactivate-users').click(function () {
                var success = 0;
                var failed = 0;
                var count = 0;
                var totals = 0;
                $('#users-table').find('input[type="checkbox"]:checked').each(function () {
                    count++;
                });

                $('.user-not').html("<p class='text-white'><i class='fas fa-spinner fa-pulse'></i> Deactivating <strong>"+(count-success)+"</strong> of <strong>"+count+"</strong> User(s)...</p>");
                $('.user-not').show();
                if(count == 0){
                    $('.user-not').html("<p class='text-white'><i class='fas fa-exclamation-triangle'></i> No user(s) selected for deactivation!</p>");
                    setTimeout(() => {
                        $('.user-not').hide();
                    }, 2000);
                }
                $('#users-table').find('input[type="checkbox"]:checked').each(function () {
                    var i = $(this).val();
                    var myurl = "{{url('users/deactivate/')}}/"+i;
                    $.ajax({
                        url: myurl,
                        type: "GET",
                    }).done(function(data){
                        success++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deactivated<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                userstable.draw();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    }).fail(function(){
                        failed++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deactivated<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                userstable.draw();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    });
                });
            });

            $('.delete-users').click(function () {
                var success = 0;
                var failed = 0;
                var count = 0;
                var totals = 0;
                $('#users-table').find('input[type="checkbox"]:checked').each(function () {
                    count++;
                });

                $('.user-not').html("<p class='text-white'><i class='fas fa-spinner fa-pulse'></i> Deleting <strong>"+(count-success)+"</strong> of <strong>"+count+"</strong> User(s)...</p>");
                $('.user-not').show();
                if(count == 0){
                    $('.user-not').html("<p class='text-white'><i class='fas fa-exclamation-triangle'></i> No user(s) selected for deleting!</p>");
                    setTimeout(() => {
                        $('.user-not').hide();
                    }, 2000);
                }
                $('#users-table').find('input[type="checkbox"]:checked').each(function () {
                    var i = $(this).val();
                    var myurl = "{{url('users/delete/')}}/"+i;
                    $.ajax({
                        url: myurl,
                        type: "GET",
                    }).done(function(data){
                        success++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                userstable.draw();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    }).fail(function(){
                        failed++;
                        totals = success + failed;
                        if(count == totals){
                            $('.user-not').html("<p class='text-white'><i class='fas fa-check-circle'></i> <strong>"+success+"</strong> Deleted<br><i class='fas fa-exclamation-circle'></i> <strong>"+failed+"</strong> Failed</p>");
                            setTimeout(() => {
                                userstable.draw();
                                $('.user-not').hide();
                            }, 2000);
                        }
                    });
                });
            });

            $(document).on('click', '#users-table .user-permissions', function(e){
                e.preventDefault();
                baseurl = "<?php echo url('/'); ?>";
                var id = $(this).attr('href');
                $(".permission-form input[name='userid']").val(id);
                $('#permissionModal').modal('show');
                $('#permissionModal .btn-submit-permissions').attr('disabled', 'disabled');
                $('#permissionModal .btn-submit-permissions').html('<i class="fas fa-spinner fa-pulse"></i> Loading... Please wait');
                $.ajax({
                    url: baseurl+"/permissions/users/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.permission-form input[name="userid"]').val(mydata.user_id);
                        if(mydata.dashboard > 0){
                            $('#dashboardc').prop("checked", true);
                            if(mydata.dashboard == 1){
                                $('#dashboardr').prop("checked", true);
                            }else if(mydata.dashboard == 2){
                                $('#dashboardr2').prop("checked", true);
                            }else{
                                $('#dashboardr3').prop("checked", true);
                            }
                        }else{
                            $('#dashboardr').prop("checked", true);
                            $('#dashboardc').prop("checked", false);
                        }
                        if(mydata.testimonials > 0){
                            $('#testimonialc').prop("checked", true);
                            if(mydata.testimonials == 1){
                                $('#testimonialr').prop("checked", true);
                            }else if(mydata.testimonials == 2){
                                $('#testimonialr2').prop("checked", true);
                            }else{
                                $('#testimonialr3').prop("checked", true);
                            }
                        }else{
                            $('#testimonialr').prop("checked", true);
                            $('#testimonialc').prop("checked", false);
                        }
                        if(mydata.websites > 0){
                            $('#websitec').prop("checked", true);
                            if(mydata.websites == 1){
                                $('#websiter').prop("checked", true);
                            }else if(mydata.websites == 2){
                                $('#websiter2').prop("checked", true);
                            }else{
                                $('#websiter3').prop("checked", true);
                            }
                        }else{
                            $('#websiter').prop("checked", true);
                            $('#websitec').prop("checked", false);
                        }
                        if(mydata.finances > 0){
                            $('#financec').prop("checked", true);
                            if(mydata.finances == 1){
                                $('#financer').prop("checked", true);
                            }else if(mydata.finances == 2){
                                $('#financer2').prop("checked", true);
                            }else{
                                $('#financer3').prop("checked", true);
                            }
                        }else{
                            $('#financer').prop("checked", true);
                            $('#financec').prop("checked", false);
                        }
                        if(mydata.spiritual > 0){
                            $('#spiritualc').prop("checked", true);
                            if(mydata.spiritual == 1){
                                $('#spiritualr').prop("checked", true);
                            }else if(mydata.spiritual == 2){
                                $('#spiritualr2').prop("checked", true);
                            }else{
                                $('#spiritualr3').prop("checked", true);
                            }
                        }else{
                            $('#spiritualc').prop("checked", true);
                            $('#spiritualc').prop("checked", false);
                        }

                        if(mydata.events > 0){
                            $('#eventc').prop("checked", true);
                            if(mydata.events == 1){
                                $('#eventr').prop("checked", true);
                            }else if(mydata.events == 2){
                                $('#eventr2').prop("checked", true);
                            }else{
                                $('#eventr3').prop("checked", true);
                            }
                        }else{
                            $('#eventc').prop("checked", true);
                            $('#eventc').prop("checked", false);
                        }
                        if(mydata.shop > 0){
                            $('#shopc').prop("checked", true);
                            if(mydata.shop == 1){
                                $('#shopr').prop("checked", true);
                            }else if(mydata.shop == 2){
                                $('#shopr2').prop("checked", true);
                            }else{
                                $('#shopr3').prop("checked", true);
                            }
                        }else{
                            $('#shopc').prop("checked", true);
                            $('#shopc').prop("checked", false);
                        }

                        if(mydata.communication > 0){
                            $('#communicationc').prop("checked", true);
                            if(mydata.communication == 1){
                                $('#communicationr').prop("checked", true);
                            }else if(mydata.communication == 2){
                                $('#communicationr2').prop("checked", true);
                            }else{
                                $('#communicationr3').prop("checked", true);
                            }
                        }else{
                            $('#communicationc').prop("checked", true);
                            $('#communicationc').prop("checked", false);
                        }
                        if(mydata.articles > 0){
                            $('#articlec').prop("checked", true);
                            if(mydata.articles == 1){
                                $('#articler').prop("checked", true);
                            }else if(mydata.articles == 2){
                                $('#articler2').prop("checked", true);
                            }else{
                                $('#articler3').prop("checked", true);
                            }
                        }else{
                            $('#articlec').prop("checked", true);
                            $('#articlec').prop("checked", false);
                        }
                        if(mydata.users > 0){
                            $('#userc').prop("checked", true);
                            if(mydata.users == 1){
                                $('#userr').prop("checked", true);
                            }else if(mydata.users == 2){
                                $('#userr2').prop("checked", true);
                            }else{
                                $('#userr3').prop("checked", true);
                            }
                        }else{
                            $('#userc').prop("checked", true);
                            $('#userc').prop("checked", false);
                        }
                        $('.btn-submit-permissions').html("Edit Permissions");
                        $('.btn-submit-permissions').removeAttr('disabled');

                    }else{
                        $('#dashboardc').prop("checked", false);
                        $('#testimonialc').prop("checked", false);
                        $('#websitec').prop("checked", false);
                        $('#financec').prop("checked", false);
                        $('#spiritualc').prop("checked", false);
                        $('#eventc').prop("checked", false);
                        $('#shopc').prop("checked", false);
                        $('#communicationc').prop("checked", false);
                        $('#articlec').prop("checked", false);
                        $('#userc').prop("checked", false);

                        $('.btn-submit-permissions').html("Add Permissions");
                        $('.btn-submit-permissions').removeAttr('disabled');
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });
        //save permissions
        $('#permissionModal form').submit(function(e){
            $('#permissionModal .btn').attr('disabled', 'disabled');
            $('#permissionModal .feedback').removeClass("d-none");
            $('#permissionModal .feedback').removeClass("text-success");
            $('#permissionModal .feedback').removeClass("text-danger");
            $('#permissionModal .feedback').addClass("text-muted");
            $('#permissionModal .feedback').html('<i class="fas fa-spinner fa-pulse"></i> Saving Changes...');
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: "{{url('/adduserpermissions')}}",
                type: "POST",
                data: formData,
            }).done(function(data){
                if(data.success){
                    $('#permissionModal .feedback').removeClass("text-muted");
                    $('#permissionModal .feedback').addClass("text-success");
                    $('#permissionModal .feedback').html('<i class="far fa-check-circle"></i> '+data.success);
                }else{
                    $('#permissionModal .feedback').removeClass("text-muted");
                    $('#permissionModal .feedback').addClass("text-danger");
                    $('#permissionModal .feedback').html('<i class="fas fa-exclamation-circle"></i> '+data.error);
                }
                setTimeout(function(){
                    $('#permissionModal .btn').removeAttr('disabled');
                    $('#permissionModal .feedback').addClass('d-none');
                }, 3000);
            }).fail(function(){
                $('#permissionModal .feedback').removeClass("text-muted");
                $('#permissionModal .feedback').addClass("text-danger");
                $('#permissionModal .feedback').html('<i class="fas fa-exclamation-circle"></i> '
                + "<strong>OOPS!</strong> Something went wrong.");
                setTimeout(function(){
                    $('#permissionModal .btn').removeAttr('disabled');
                    $('#permissionModal .feedback').addClass('d-none');
                }, 3000);
            });
        });

        //Change User roles
        $(document).on('click', '#users-table .dropdown-menu a', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            var role = $(this).text();
            var profile = $(this).closest('tr').find("td:nth-child(2) .badge");
            profile.html("<i class='fas fa-spinner fa-pulse'></i> Saving...");
            $.ajax({
                url: url,
                type: 'GET'
            }).done(function(data){
                profile.html(role);
            }).fail(function(data){
                alert("Oops! Something went wrong");
            });
        });

        //Edit user
        $('#addUserModal form').submit(function(e){
            e.preventDefault();
            $('#addUserModal .btn').attr('disabled', 'disabled');
            $('#addUserModal .feedback').removeClass("d-none");
            $('#addUserModal .feedback').removeClass("text-success");
            $('#addUserModal .feedback').removeClass("text-danger");
            $('#addUserModal .feedback').addClass("text-muted");
            $('#addUserModal .feedback').html('<i class="fas fa-spinner fa-pulse"></i> Saving Changes...');

            var id = $("#addUserModal input[name=id]").val();
            var fname = $("#addUserModal input[name=fname]").val();
            var lname = $("#addUserModal input[name=lname]").val();
            var email = $("#addUserModal input[name=email]").val();
            var phone = $("#addUserModal input[name=phone]").val();
            var formData = $(this).serialize();
            $.ajax({
                url:"{{url('users/add')}}",
                type: "POST",
                data: formData,
            }).done(function(data){
                if(data.success){
                    $('#addUserModal .feedback').removeClass("text-muted");
                    $('#addUserModal .feedback').addClass("text-success");
                    $('#addUserModal .feedback').html('<i class="far fa-check-circle"></i> '+data.success+" <strong>Refresh</strong> to view changes");
                    if(id == 0){
                        userstable.draw();
                    }
                }else{
                    $('#addUserModal .feedback').removeClass("text-muted");
                    $('#addUserModal .feedback').addClass("text-danger");
                    $('#addUserModal .feedback').html('<i class="far fa-exclamation-circle"></i> '+data.error);
                }
                $('#addUserModal .btn').removeAttr('disabled');
                setTimeout(function(){
                    $('#addUserModal .feedback').addClass('d-none');
                }, 3000);
            }).fail(function(){
                alert("Something went wrong");
            });
        });

        //Deactivate User
        $(document).on('click', '#users-table .btn-deactivate', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            var profile = $(this).closest('tr').find("td:nth-child(2) .text-success");
            var btn = $(this);
            profile.html("<i class='fas fa-spinner fa-pulse'></i> Deactivating...");
            $.ajax({
                url: url,
                type: 'GET'
            }).done(function(data){
                if(data.success){
                    var newurl = url.replace('deactivate', 'activate');
                    btn.attr('href', newurl);
                    btn.removeClass('btn-deactivate').removeClass('btn-danger').addClass('btn-success').addClass('btn-activate').html("<i class='fas fa-sync-alt'></i>");
                    profile.removeClass('text-success').addClass('text-danger').text("Inactive");
                }else{
                    profile.text("Active");
                    alert(data.error);
                }
            }).fail(function(data){
                profile.text("Active");
                alert("Oops! Something went wrong");
            });
        });

        //Activate User
        $(document).on('click', '#users-table .btn-activate', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            var profile = $(this).closest('tr').find("td:nth-child(2) .text-danger");
            var btn = $(this);
            profile.html("<i class='fas fa-spinner fa-pulse'></i> Activating...");
            $.ajax({
                url: url,
                type: 'GET'
            }).done(function(data){
                if(data.success){
                    var newurl = url.replace('activate', 'deactivate');
                    btn.attr('href', newurl);
                    btn.removeClass('btn-activate').removeClass('btn-success').addClass('btn-danger').addClass('btn-deactivate').html("<i class='fas fa-ban'></i>");
                    profile.removeClass('text-danger').addClass('text-success').text("Active");
                }else{
                    profile.text("Inactive");
                    alert(data.error);
                }
            }).fail(function(data){
                profile.text("Inactive");
                alert("Oops! Something went wrong");
            });
        });

        //Load SMS Modal
        $(document).on('click', '#users-table .btn-sms', function(e){
            baseurl = "<?php echo url('/'); ?>";
            e.preventDefault();
            var username = $(this).closest("tr").find("td:nth-child(3)").text() +" "+
            $(this).closest("tr").find("td:nth-child(4)").text();
            $('#smsModal form .btn').html("<i class='fas fa-spinner fa-pulse'></i> Please wait...");
            $('#smsModal form .btn').attr("disabled", "disabled");
            $('#smsModal').modal("show");
            var id = $(this).attr('href');
            $('#smsModal .modal-header h4').html("Sending to <strong class='text-primary'>"+username+"</strong>")
            $.ajax({
                url: baseurl+"/user/phone/"+id,
                type: "GET",
            }).done(function(data){
                mydata = $.parseJSON(data);
                if(mydata != null){
                    $('#smsModal form input[name="numbers"]').val(mydata.phone);
                    $('#smsModal form .btn').addClass("bg-primary").removeClass("bg-danger").html("<i class='fas fa-paper-plane'></i> Send");
                    $('#smsModal form .btn').removeAttr("disabled");
                }else{
                    $('#smsModal form .btn').removeClass("bg-primary").addClass("bg-danger").html("<i class='fas fa-exclamation-triangle'></i> Contact Unavailable");
                }
            }).fail(function(data){
                $('#smsModal form .btn').removeClass("bg-primary").addClass("bg-danger").html("<i class='fas fa-exclamation-triangle'></i> Error");
            });
        });

        //Send SMS
        $('#smsModal form').submit(function(e){
            e.preventDefault();
            $('#smsModal .btn').attr('disabled', 'disabled');
            $('#smsModal .feedback').removeClass("d-none");
            $('#smsModal .feedback').removeClass("text-success");
            $('#smsModal .feedback').removeClass("text-danger");
            $('#smsModal .feedback').addClass("text-muted");
            $('#smsModal .feedback').html('<i class="fas fa-spinner fa-pulse"></i> Sending SMS...');

            var formData = $(this).serialize();
            $.ajax({
                url:"{{url('users/sendsms')}}",
                type: "POST",
                data: formData,
            }).done(function(data){
                if(data.success){
                    $('#smsModal .feedback').removeClass("text-muted");
                    $('#smsModal .feedback').addClass("text-success");
                    $('#smsModal .feedback').html('<i class="far fa-check-circle"></i> '+data.success);
                }else{
                    $('#smsModal .feedback').removeClass("text-muted");
                    $('#smsModal .feedback').addClass("text-danger");
                    $('#smsModal .feedback').html('<i class="far fa-exclamation-circle"></i> '+data.error);
                }
                $('#smsModal .btn').removeAttr('disabled');
                setTimeout(function(){
                    $('#smsModal .feedback').addClass('d-none');
                }, 3000);
                
                // Refresh credits display after successful SMS send
                if (typeof refreshCreditsBalance === 'function') {
                    refreshCreditsBalance();
                }
            }).fail(function(){
                alert("Something went wrong");
            });
        });
    });
</script>
@endpush
