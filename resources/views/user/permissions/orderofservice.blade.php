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
            <div class="card bgshadow">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Order of Services</h3>
                        </div>
                        <div class="col text-right">
                            @if($permissions1->websites > 1 || $permissions2->websites > 1)
                                <button class="btn btn-sm btn-primary showServiceModal" data-toggle="modal" data-target="#services"><i class='fas fa-plus-circle'></i> New</button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body mb-0 p-0">
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
                    <!-- Projects table -->
                    <div class='table-responsive'>
                        <table class="table align-items-center table-flush">
                            <thead class='thead-light'>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Venue</th>
                                    <th scope="col">Day</th>
                                    <th scope="col">Time</th>
                                    <th scope="col" class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($services->count() == 0)
                                <tr>
                                    <td colspan='5' class='text-center'>
                                        <i class='fas fa-ban'></i> No items yet
                                    </td>
                                </tr>
                                @endif
                                <?php $count = 1; ?>
                                @foreach ($services as $service)
                                    <tr>
                                        <td>{{$count}}</td>
                                        <td scope="row">{{$service->description}}</td>
                                        <td scope="row">{{$service->venue }}</td>
                                        <td scope="row">{{$service->day }}</td>
                                        <td scope="row">{{$service->time }}</td>
                                        <td class="text-right">
                                            @if($permissions1->websites > 1 || $permissions2->websites > 1)
                                                <a href="{{$service->id}}" class='btn btn-outline-primary p-1 pl-2 pr-2 btn-edit-service' title='Edit'><i class='fas fa-edit'></i> Edit</a>
                                            @endif
                                            @if($permissions1->websites > 2 || $permissions2->websites > 2)
                                                <a href="{{url('removeservice/'.$service->id)}}" class='btn btn-danger p-1 pl-2 pr-2' title='Delete'><i class='fas fa-trash'></i> Delete</a>
                                            @endif
                                        </td>
                                    </tr>
                                    <?php $count++; ?>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="services" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel">Services</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body pt-0 pb-0">
                <form action="/addservice" method="post" class='service-form'>
                    @csrf
                    <input type="hidden" name="id" value="0">
                    <div class='form-group mt-2 mb-0 pb-0'>
                        <label><small>Venue:</small></label>
                        <input class='form-control' name="venue" placeholder="Venue" required>
                    </div>
                    <div class='form-group mt-2 mb-0 pb-0'>
                        <label><small>Day:</small></label>
                        <select class='custom-select' name="days">
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednessday">Wednessday</option>
                            <option value="Thurday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                        </select>
                    </div>
                    <div class='form-group mt-2 mb-0 pb-0'>
                        <label><small>time range:</small></label>
                        <input class='form-control' name="time" placeholder="Time range" required>
                    </div>
                    <div class='form-group mt-2 mb-0 pb-0'>
                        <label><small>Description:</small></label>
                        <textarea class='form-control' name="description" rows="3" placeholder="Enter description" required></textarea>
                    </div>
                    <div class="form-group mt-2 text-right">
                        <button type="submit" class="btn btn-primary btn-submit-service">Save changes</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
      </div>

      <div class="container gallery-view d-none">
            <div class="row d-flex align-items-center">
                <div class='col-sm-12 text-right'>
                    <a href="#" class='btn text-white btn-close-gallery'><i class='fas fa-times fa-2x'></i></a>
                </div>
                <div class='col-sm-12'>
                    <img src='' class='img-fluid' style='max-height: 90vh'>
                </div>
            </div>
      </div>
@endsection
