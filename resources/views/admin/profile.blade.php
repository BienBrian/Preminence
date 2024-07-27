@extends('layouts.admin')

@section('content')
<!-- Header -->
<div class="header pb-8 pt-5 pt-lg-8 d-flex align-items-center" style="background-image: url(../assets/img/theme/profile-cover.jpg); background-size: cover; background-position: center top;">
    <!-- Mask -->
    <span class="mask bg-gradient-default opacity-8"></span>
    <!-- Header container -->
    <div class="container-fluid d-flex align-items-center">
      <div class="row">

      </div>
    </div>
  </div>
  <!-- Page content -->
  <div class="container-fluid mt--7">
    <div class="row">
      <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
        <div class="card card-profile shadow">
          <div class="row justify-content-center">
            <div class="col-lg-3 order-lg-2">
              <div class="card-profile-image">
                <a href="#">
                  <?php 
                    $myprofile = \DB::table("profiles")->where("user_id", \Auth::user()->id)->first();
                  ?>
                  <img src="{{$myprofile == null ? asset('profile_images/default.jpg'): asset('profile_images/'.$myprofile->name)}}" class="rounded-circle">
                </a>
              </div>
            </div>
          </div>
          <div class="card-header text-center border-0 pt-8 pt-md-4 pb-0 pb-md-4">
            <div class="d-flex justify-content-between">
              <a href="{{url('dashboard')}}" class="btn btn-sm btn-info mr-4">Dashboard</a>
              <a href="{{url('articles')}}" class="btn btn-sm btn-default float-right">Articles</a>
            </div>
          </div>
          <div class="card-body pt-0 pt-md-4">
            <div class="row">
              <div class="col">
                <div class="card-profile-stats d-flex justify-content-center mt-md-5">
                  <!--<div>
                    <span class="heading">22</span>
                    <span class="description">Friends</span>
                  </div>
                  <div>
                    <span class="heading">10</span>
                    <span class="description">Photos</span>
                  </div>
                  <div>
                    <span class="heading">89</span>
                    <span class="description">Comments</span>
                  </div>-->
                </div>
              </div>
            </div>
            <div class="text-center">
              <h3>
                {{\Auth::user()->firstname}} {{\Auth::user()->lastname}}<span class="font-weight-light">,
                    @if(\Auth::user()->role == 1)
                    admin
                    @else
                    user
                    @endif
                </span>
              </h3>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xl-8 order-xl-1">
        <div class="card bg-secondary shadow">
          <div class="card-header bg-white border-0">
            <div class="row align-items-center">
              <div class="col-8">
                <h3 class="mb-0">My account</h3>
              </div>
              <div class="col-4 text-right">
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#profileModal"><i class="fas fa-camera-alt"></i> Profile Photo</button>
              </div>
            </div>
          </div>
          <div class="card-body">
            <form method="POST" action="{{url('updateprofile')}}">
                @csrf
              <h6 class="heading-small text-muted mb-4">User information</h6>
              <div class="pl-lg-4">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label class="form-control-label" for="input-email">Email address</label>
                      <input type="email" name="email" id="input-email" class="form-control form-control-alternative" value="{{\Auth::user()->email}}">
                    </div>
                  </div>
                </div>
                <div class="row">
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label" for="input-first-name">First name</label>
                            <input type="text" name="firstname" id="input-first-name" class="form-control form-control-alternative" placeholder="First name" value="{{\Auth::user()->firstname}}">
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label" for="input-last-name">Last name</label>
                            <input type="text" name="lastname" id="input-last-name" class="form-control form-control-alternative" placeholder="Last name" value="{{\Auth::user()->lastname}}">
                          </div>
                        </div>

                        <div class="col-lg-12">
                                <div class="form-group">
                                  <label class="form-control-label" for="input-last-name">Current Password</label>
                                  <input name="current_password" type="password" class="form-control form-control-alternative" placeholder="Current Password">
                                </div>
                              </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label" for="input-first-name">New Password</label>
                            <input name="new_password" type="password" class="form-control form-control-alternative" placeholder="New Password">
                          </div>
                        </div>
                        <div class="col-lg-6">
                          <div class="form-group">
                            <label class="form-control-label" for="input-last-name">Confirm New Password</label>
                            <input name="confirm" type="password" class="form-control form-control-alternative" placeholder="Confirm New Password">
                          </div>
                        </div>
                        <div class="col-lg-12">
                          <div class="form-group text-right">
                            <button class="btn btn-primary">Save Changes</button>
                          </div>
                        </div>
                </div>
              </div>
            </form>

            <hr>
            <form method="POST" action="{{url('contacts')}}">
                @csrf
                <h3 class="heading-small text-muted mb-4">Contact Information</h3>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Phone</label>
                        <input type="text" name="phone" class="form-control form-control-alternative" placeholder="Phone Number" value="{{$contacts==null?"":$contacts->phone}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control form-control-alternative" placeholder="Date of Birth" value="{{$contacts==null?"":$contacts->dob}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Baptism</label>
                        <input type="date" name="baptism" class="form-control form-control-alternative" placeholder="Baptism" value="{{$contacts==null?"":$contacts->baptism}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Country</label>
                        <select class="custom-select form-control-alternative" name="country">
                            <option value="1" {{$contacts==null?"":$contacts->country==1?"selected":""}}>Kenya</option>
                            <option value="2" {{$contacts==null?"":$contacts->country==2?"selected":""}}>Uganda</option>
                            <option value="3" {{$contacts==null?"":$contacts->country==3?"selected":""}}>Tanzania</option>
                            <option value="4" {{$contacts==null?"":$contacts->country==4?"selected":""}}>Burundi</option>
                            <option value="5" {{$contacts==null?"":$contacts->country==5?"selected":""}}>Rwanda</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">City</label>
                        <input type="text" name="city" class="form-control form-control-alternative" placeholder="city" value="{{$contacts==null?"":$contacts->city}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Gender</label>
                        <select class="custom-select form-control-alternative" name="gender">
                            <option value="0" {{$contacts==null?"":$contacts->gender==0?"selected":""}}>Male</option>
                            <option value="1" {{$contacts==null?"":$contacts->gender==1?"selected":""}}>Female</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Facebook</label>
                        <input type="text" name="facebook" class="form-control form-control-alternative" placeholder="Facebook" value="{{$contacts==null?"":$contacts->facebook}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">WhatsApp</label>
                        <input type="text" name="whatsapp" class="form-control form-control-alternative" placeholder="WhatsApp" value="{{$contacts==null?"":$contacts->whatsapp}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Instagram</label>
                        <input type="text" name="instagram" class="form-control form-control-alternative" placeholder="Instagram" value="{{$contacts==null?"":$contacts->instagram}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Twitter</label>
                        <input type="text" name="twitter" class="form-control form-control-alternative" placeholder="Twitter" value="{{$contacts==null?"":$contacts->twitter}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Gmail</label>
                        <input type="text" name="gmail" class="form-control form-control-alternative" placeholder="gmail" value="{{$contacts==null?"":$contacts->gmail}}">
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="form-control-label">Youtube</label>
                        <input type="text" name="youtube" class="form-control form-control-alternative" placeholder="youtube" value="{{$contacts==null?"":$contacts->youtube}}">
                    </div>
                    <div class="form-group col-sm-12 text-right">
                        <button type="submit" class="btn btn-primary">Update Bio</button>
                    </div>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header pb-0 mb-0">
                        <h4 class="modal-title" id="exampleModalLabel">Profile Photo</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-3 pb-0 mb-0">
                        <div class="col-sm-12 text-center">
                            <div id="upload-profile" class='d-none'></div>
                            <input type="file" id="upload" style='display: none;'>
                        </div>
                        <div class='col-sm-12 feedback'></div>
                        <div class="col-sm-12 mt-3 mb-2 text-right">
                            <button class="btn btn-outline-primary btn-add-profile"><i class="fas fa-cloud-upload-alt"></i> upload</button>
                            <button class="btn btn-primary upload-result" disabled="disabled">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
