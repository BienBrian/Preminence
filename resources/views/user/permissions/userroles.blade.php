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
                            <h3 class="mb-0"><i class="fas fa-users"></i> | User Roles</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->users > 1 || $permissions2->users > 1)
                                <button class="btn btn-sm btn-primary btn-show-role" data-toggle="modal" data-target="#roleModal">Add Role</button>
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
                                <th scope="col">Title</th>
                                <th scope="col" class='text-right'>Action</th>
                            </tr>
                        </thead>
                            <tbody>
                                @if($roles->isEmpty())
                                    <tr><td colspan='4' class='text-center'> <i class='fas fa-ban'></i> No roles yet</td></tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach($roles as $role)
                                <tr>
                                        <td>{{ $count }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td class='text-right'>
                                            @if($permissions1->users > 2 || $permissions2->users > 2)
                                                <a href="{{$role->role}}" class="btn btn-success p-1 pl-2 pr-2 edit-permissions" data-toggle="tooltip" data-placement="bottom" title="Permissions">
                                                    <i class='fas fa-lock'></i>
                                                </a>
                                            @endif
                                            @if($permissions1->users > 1 || $permissions2->users > 1)
                                                <a href="{{$role->id}}" class="btn btn-primary p-1 pl-2 pr-2 edit-role" data-toggle="tooltip" data-placement="bottom" title="Edit">
                                                    <i class='fas fa-edit'></i>
                                                </a>
                                            @endif
                                            @if($permissions1->users > 2 || $permissions2->users > 2)
                                                <a href="{{url('deleterole/'.$role->id)}}" class="btn btn-danger p-1 pl-2 pr-2" data-toggle="tooltip" data-placement="bottom" title="Delete">
                                                    <i class='fas fa-trash'></i>
                                                </a>
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
                {{$roles->links()}}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">Role Name</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="/addrole" method="post" class="role-form">
                            @csrf
                            <input type='hidden' name='id' value='0'>

                            <div class='form-group'>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2"><i class='fas fa-user-lock text-primary'></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="name" placeholder='Enter role name' required>
                                </div>
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary btn-submit-role">Save Role</button>
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
                    <h4 class="modal-title" id="exampleModalLabel">User Group Permissions</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="/addpermissions" method="post" class="row d-flex align-items-center permission-form">
                        @csrf
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <input type='hidden' name='id' value='0'>
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
                        <div class="col-sm-6  col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
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
                        <div class="col-sm-6  col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
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
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="finance" id="financec" type="checkbox" value="4">
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
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="sermon" id="sermonc" type="checkbox" value="5">
                                <label class="custom-control-label" for="sermonc">Sermons</label>
                            </div>
                            <div class='row'>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="sermonr" class="custom-control-input" id="sermonr" type="radio" value="1" checked="">
                                    <label class="custom-control-label mr-1" for="sermonr">View</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="sermonr" class="custom-control-input" id="sermonr2" type="radio" value="2">
                                    <label class="custom-control-label mr-1" for="sermonr2">Edit</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="sermonr" class="custom-control-input" id="sermonr3" type="radio" value="3">
                                    <label class="custom-control-label" for="sermonr3">All</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="event" id="eventc" type="checkbox" value="6">
                                <label class="custom-control-label" for="eventc">Events</label>
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
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="prayer" id="prayerc" type="checkbox" value="7">
                                <label class="custom-control-label" for="prayerc">Prayers</label>
                            </div>
                            <div class='row'>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="prayerr" class="custom-control-input" id="prayerr" type="radio" value="1" checked="">
                                    <label class="custom-control-label mr-1" for="prayerr">View</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="prayerr" class="custom-control-input" id="prayerr2" type="radio" value="2">
                                    <label class="custom-control-label mr-1" for="prayerr2">Edit</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="prayerr" class="custom-control-input" id="prayerr3" type="radio" value="3">
                                    <label class="custom-control-label" for="prayerr3">All</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="notice" id="noticec" type="checkbox" value="8">
                                <label class="custom-control-label" for="noticec">Notices</label>
                            </div>
                            <div class='row'>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="noticer" class="custom-control-input" id="noticer" type="radio" value="1" checked="">
                                    <label class="custom-control-label mr-1" for="noticer">View</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="noticer" class="custom-control-input" id="noticer2" type="radio" value="2">
                                    <label class="custom-control-label mr-1" for="noticer2">Edit</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="noticer" class="custom-control-input" id="noticer3" type="radio" value="3">
                                    <label class="custom-control-label" for="noticer3">All</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 bg-secondary alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="department" id="departmentc" type="checkbox" value="9">
                                <label class="custom-control-label" for="departmentc">Departments</label>
                            </div>
                            <div class='row'>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="departmentr" class="custom-control-input" id="departmentr" type="radio" value="1" checked="">
                                    <label class="custom-control-label mr-1" for="departmentr">View</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="departmentr" class="custom-control-input" id="departmentr2" type="radio" value="2">
                                    <label class="custom-control-label mr-1" for="departmentr2">Edit</label>
                                </div>
                                <div class="custom-control custom-radio mb-3 col-xs-4">
                                    <input name="departmentr" class="custom-control-input" id="departmentr3" type="radio" value="3">
                                    <label class="custom-control-label" for="departmentr3">All</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="user" id="userc" type="checkbox" value="10">
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
                        <div class="col-sm-6 col-md-4 col-lg-3 mb-1 alert alert-primary bg-secondary text-dark border-0">
                            <div class="custom-control custom-checkbox mb-3">
                                <input class="custom-control-input" name="article" id="articlec" type="checkbox" value="11">
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
                            <button type="submit" class="btn btn-primary btn-submit-permissions">Save Permissions</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
