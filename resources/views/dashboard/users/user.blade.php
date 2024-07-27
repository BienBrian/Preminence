@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 bold text-header"><i class='fas fa-user'></i> View <b>User</b></h5>
                </div><!-- /.col -->
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/users') }}">Users</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
        <div class="col-xl-12 mb-5 mb-xl-0">
            <div class="card mt-3 tab-card shadow">
                <div class="card-header tab-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" id="one-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true"><i class='fas fa-user text-indigo'></i> Basic</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="two-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false"><i class='fas fa-phone text-primary'></i> Contacts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="three-tab" data-toggle="tab" href="#church" role="tab" aria-controls="church" aria-selected="false"><i class='fas fa-university text-success'></i> Church</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="two-tab" data-toggle="tab" href="#family" role="tab" aria-controls="Two" aria-selected="false"><i class='fas fa-users text-danger'></i> Family</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="three-tab" data-toggle="tab" href="#work" role="tab" aria-controls="Three" aria-selected="false"><i class='fas fa-graduation-cap text-muted'></i> Work & Edu</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active p-3" id="basic" role="tabpanel" aria-labelledby="one-tab">
                        <form method="POST" action="{{url('user/add/basic')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{$user == null?'0':$user->id}}">
                            <h4>Basic user info</h4>
                            <div class="row">
                            <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>User Level</small></label>
                                        <select class="custom-select" name="role" id='roles'>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>First Name</small></label>
                                        <input type="text" class="form-control" placeholder="First Name" name="fname" value="{{$user == null?'0':$user->firstname}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Last Name</small></label>
                                        <input type="text" class="form-control" placeholder="Last Name" name="lname" value="{{$user == null?'0':$user->lastname}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Email address</small></label>
                                        <input type="email" class="form-control" placeholder="Email Address" name="email" value="{{$user == null?'0':$user->email}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Phone</small></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">+254</div>
                                            </div>
                                            <input type="text" class="form-control" placeholder="Mobile Phone" name="phone" value="{{$user == null?'0':substr($user->phone, 1)}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Gender</small></label>
                                        <select class="custom-select" name="gender">
                                            <option value="0" {{$user == null?'':($user->gender==0?'selected':'')}}>Male</option>
                                            <option value="1" {{$user == null?'':($user->gender==1?'selected':'')}}>Female</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Marital Status</small></label>
                                        <select class="custom-select" name="marital">
                                            <option value="1" {{$user == null?'':($user->marital==1?'selected':'')}}>Single</option>
                                            <option value="2" {{$user == null?'':($user->marital==2?'selected':'')}}>Married</option>
                                            <option value="3" {{$user == null?'':($user->marital==3?'selected':'')}}>Divorced</option>
                                            <option value="4" {{$user == null?'':($user->marital==4?'selected':'')}}>Separated</option>
                                            <option value="5" {{$user == null?'':($user->marital==5?'selected':'')}}>Widow(er)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Nationality</small></label>
                                        <select class="custom-select" name="country">
                                            <option value="1" {{$user == null?'':($user->country==1?'selected':'')}}>Kenya</option>
                                            <option value="2" {{$user == null?'':($user->country==2?'selected':'')}}>Uganda</option>
                                            <option value="3" {{$user == null?'':($user->country==3?'selected':'')}}>Tanzania</option>
                                            <option value="4" {{$user == null?'':($user->country==4?'selected':'')}}>Burundi</option>
                                            <option value="5" {{$user == null?'':($user->country==5?'selected':'')}}>Rwanda</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label><small>Date of Birth</small></label>
                                        <input type="text" class="form-control datepicker1" placeholder="Date of Birth" name="dob" value="{{$user == null?'':$user->dob}}" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label><small>Date Joined</small></label>
                                        <input type="text" class="form-control datepicker1" placeholder="Date Joined" name="joined" value="{{$user == null?'':$user->joined}}" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6 p-3">
                                    <center><img src="{{$user == null?asset('profile_images/default.jpg'):($user->name == ''?asset('profile_images/default.jpg'):asset('profile_images/'.$user->name))}}" class="img-fluid" style="max-width: 200px;"></center>
                                    <input type="file" class="form-control form-control-alternative" name="image">
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group p-2 text-right">
                                        <button type="submit" class="btn btn-primary">Save and Continue</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade p-3 bg-secondary" id="contact" role="tabpanel" aria-labelledby="two-tab">
                        <form method="POST" action="{{url('user/add/contacts')}}">
                            @csrf
                            <h4>Member Contact Info</h4>
                            <input type="hidden" name='userid' value="0">
                            <input type="hidden" name="id" value="{{$user == null?'0':$user->id}}">
                            <input type="hidden" name="sid" value="{{$scontact == null?'0':$scontact->id}}">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Secondary Contact Phone</small></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">+254</div>
                                            </div>
                                            <input type="text" class="form-control" placeholder="Secondary contact phone" name="phone" value="{{$scontact == null?'':$scontact->secondary_phone}}">
                                        </div>
                                    </div>
                                </div>
                            <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Member Home Town</small></label>
                                        <input type="text" class="form-control" placeholder="Member Home Town" name="town" value="{{$scontact == null?'':$scontact->town}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group" id="searchdiv">
                                        <label><small>Emergency Contact Person</small></label>
                                        <input type="text" class="form-control" placeholder="Search by name, email" name="emergency" value="{{$emergency == null?'':$emergency->firstname.', '.$emergency->lastname.', '.$emergency->email.', '.$emergency->phone}}" autocomplete="off">
                                        <div class="searchdiv shadow bg-white border list-group" id="searchdiv1">
                                            <p><i class="fas fa-spinner fa-pulse"></i> Searching</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Phone No. of Emergency Contact Person</small></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">+254</div>
                                            </div>
                                            <input type="text" class="form-control" placeholder="Emergency Contact" name="emergency-no" value="{{$emergency == null?'':substr($emergency->phone, 3)}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Postal Address</small></label>
                                        <textarea class="form-control" placeholder="Postal Address" name="postal">{{$scontact == null?'':$scontact->address}}</textarea>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Residential Location/Nearest Landmark</small></label>
                                        <textarea class="form-control" placeholder="Describe your resident location" name="resident">{{$scontact == null?'':$scontact->resident}}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class='col-sm-12 text-right'>
                                <button class='btn btn-primary' type="submit">Save and Continue</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade p-3 bg-secondary" id="church" role="tabpanel" aria-labelledby="three-tab">
                        <form method="POST" action="{{url('user/add/church')}}">
                            @csrf
                            <input type="hidden" name="id" value="{{$user == null?'0':$user->id}}">
                            <h4>Member Church Data</h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Member Active Groups (communities)</small></label>
                                        <select class="custom-select" name="community">
                                            <option value="0">Select User Community</option>
                                            @foreach($communities as $community)
                                                <option value="{{$community->id}}" {{$user == null?"":($user->communities == $community->id?"selected":"")}}>{{$community->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Member Active Groups (departments)</small></label>
                                        <select class="custom-select" name="department">
                                            <option value="0">Select User Department</option>
                                            @foreach($departments as $department)
                                                <option value="{{$department->id}}" {{$user == null?"":($user->departments == $department->id?"selected":"")}}>{{$department->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Year of Joining Church</small></label>
                                        <select class="custom-select" name="joined">
                                            <?php $years = date('Y'); ?>
                                            @for($years; $years >= date('Y')-10; $years--)
                                                <option value="{{$years}}" {{$user == null?"":($user->yearjoined == $years?"selected":"")}}>{{$years}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Special Gifts/Ministry</small></label>
                                        <input type="text" class="form-control" placeholder="Special Gifts/Ministry" value="{{$user == null?'':$user->gifts}}" name="gifts">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Previous Church (If Any)</small></label>
                                        <input type="text" class="form-control" placeholder="Previous Church" value="{{$user == null?'':$user->previous_church}}" name="previous">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Position held</small></label>
                                        <input type="text" class="form-control" placeholder="members position in previous church" value="{{$user == null?'':$user->position}}" name="position">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Other remarks</small></label>
                                        <textarea rows="4" class="form-control" placeholder="Other remarks" name="remarks">{{$user == null?'':$user->remarks}}</textarea>
                                    </div>
                                    <div class="form-group text-right">
                                        <button class="btn btn-primary">Save and Continue</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade p-3" id="family" role="tabpanel" aria-labelledby="three-tab">
                        <form method="POST" action="{{url('user/add/relationship')}}"  enctype="multipart/form-data">
                            @csrf
                            <h4>Member Family</h4>
                            <input type="hidden" name="id" value="{{$user == null?'0':$user->id}}">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Family relationship</small></label>
                                        <select class="custom-select" placeholder="Secondary contact phone" name="relationship">
                                            <option>--Select Family Relationship--</option>
                                            <option value="1" {{$user->relationship != ""?($user->relationship=="1"?"selected":""):""}}>Spouse</option>
                                            <option value="2" {{$user->relationship != ""?($user->relationship=="2"?"selected":""):""}}>Child</option>
                                            <option value="3" {{$user->relationship != ""?($user->relationship=="3"?"selected":""):""}}>Sibling</option>
                                            <option value="4" {{$user->relationship != ""?($user->relationship=="4"?"selected":""):""}}>Parent</option>
                                            <option value="5" {{$user->relationship != ""?($user->relationship=="5"?"selected":""):""}}>Guardian</option>
                                            <option value="6" {{$user->relationship != ""?($user->relationship=="6"?"selected":""):""}}>Next of Kin</option>
                                        </select>
                                    </div>
                                </div>
                            <div class="col-sm-6">
                                    <div class="form-group" id="searchdiva">
                                        <label><small>Relations Name</small></label>
                                        <input type="text" name="name" placeholder="Relation's name" class="form-control" autocomplete="off" required value='{{$user->familyname != ""?$user->familyname:""}}'>
                                        <div class="searchdiv shadow bg-white border list-group" id="searchdiv2">
                                            <p class="list-group-item"><i class="fas fa-spinner"></i> Searching...</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Relation's phone number</small></label>
                                        <div class='input-group'>
                                            <div class='input-group-prepend'>
                                                <span class='input-group-text'>+254</span>
                                            </div>
                                            <input type="text" class="form-control" placeholder="Phone Number" name="phone" value='{{$user->familyphone != ""?$user->familyphone:""}}'>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Relation's email</small></label>
                                        <input type="email" class="form-control" placeholder="Email Address" name="email" value='{{$user->familyemail != ""?$user->familyemail:""}}'>
                                    </div>
                                </div>
                                <div class="col-sm-6 p-3">
                                    <p><small>Relative's Photo</small></p>
                                    <img src="{{$user->image != ''?asset('relatives/'.$user->image):asset('profile_images/default.jpg')}}" class="img-fluid" style="max-width: 200px;">
                                </div>
                                <div class="col-sm-6 p-3">
                                    <p><small>Upload Relative's Photo</small></p>
                                    <img src="{{asset('profile_images/default.jpg')}}" id="previewimage" class="img-fluid" style="display:none;">
                                    <input type="file" name="profile_image" class="image">
                                    <input type="hidden" name="x1" value="" />
                                    <input type="hidden" name="y1" value="" />
                                    <input type="hidden" name="w" value="" />
                                    <input type="hidden" name="h" value="" />
                                </div>
                                <div class='col-sm-12'>
                                    <div class="form-group text-right">
                                        <button class="btn btn-primary">Save and Continue</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade p-3" id="work" role="tabpanel" aria-labelledby="three-tab">
                        <form method="POST" action="{{url('user/add/profession')}}">
                            <h4>Member Education and Work</h4>
                            @csrf
                            <input type="hidden" name="id" value="{{$user == null?'0':$user->id}}">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Member's Occupation Industry</small></label>
                                        <select class="custom-select" name="occupation">
                                            <option>--Select Field in which member is employed--</option>
                                            <option value="1" {{$user->occupation == 1?"selected":""}}>Accommodation & Food Services</option>
                                            <option value="2" {{$user->occupation == 2?"selected":""}}>Arts and Entertainment</option>
                                            <option value="3" {{$user->occupation == 3?"selected":""}}>Corporate Administration and Management</option>
                                            <option value="4" {{$user->occupation == 4?"selected":""}}>Educational Services</option>
                                            <option value="5" {{$user->occupation == 5?"selected":""}}>Engineering, Scientific and Technical services</option>
                                            <option value="6" {{$user->occupation == 6?"selected":""}}>Finance and Insurance</option>
                                            <option value="7" {{$user->occupation == 7?"selected":""}}>Food, Agriculture and Forestry</option>
                                            <option value="8" {{$user->occupation == 8?"selected":""}}>Government</option>
                                            <option value="9" {{$user->occupation == 9?"selected":""}}>Health Care and Social Assistance</option>
                                            <option value="10" {{$user->occupation == 10?"selected":""}}>Information and Communication Technology</option>
                                            <option value="11" {{$user->occupation == 11?"selected":""}}>Janitorial, Waste Management and Remediation Services</option>
                                            <option value="12" {{$user->occupation == 12?"selected":""}}>Manufacturing and Processing</option>
                                            <option value="13" {{$user->occupation == 13?"selected":""}}>Mining, Quarrying, Oil and Gas Extraction</option>
                                            <option value="14" {{$user->occupation == 14?"selected":""}}>Public Administration</option>
                                            <option value="15" {{$user->occupation == 15?"selected":""}}>Public Utility Services</option>
                                            <option value="16" {{$user->occupation == 16?"selected":""}}>Real Estate, Rental and Leasing</option>
                                            <option value="17" {{$user->occupation == 17?"selected":""}}>Religion</option>
                                            <option value="18" {{$user->occupation == 18?"selected":""}}>Retail Trade</option>
                                            <option value="19" {{$user->occupation == 19?"selected":""}}>Sports and Recreation</option>
                                            <option value="20" {{$user->occupation == 20?"selected":""}}>Security and Armed Forces</option>
                                            <option value="21" {{$user->occupation == 21?"selected":""}}>Transportation and Warehousing</option>
                                            <option value="22" {{$user->occupation == 22?"selected":""}}>Wholesale Trade</option>
                                            <option value="23" {{$user->occupation == 23?"selected":""}}>None</option>
                                            <option value="24" {{$user->occupation == 24?"selected":""}}>Other</option>
                                        </select>
                                    </div>
                                </div>
                            <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Member's Specific Job</small></label>
                                        <input type="text" class="form-control" name="specific" placeholder="Specific Job" value="{{$user->specific}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Institution of employment</small></label>
                                        <input type="text" class="form-control" placeholder="Institution/Organization where member is employed" name="institution" value="{{$user->institution}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>From</small></label>
                                        <input type="text" class="form-control datepicker1" placeholder="From" name="from" value="{{$user->from}}" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>To</small></label>
                                        <input type="text" class="form-control datepicker1" placeholder="To" name="to" value="{{$user->to}}" readonly>
                                    </div>
                                </div>
                                <div class='col-sm-12'>
                                    <div class="form-group text-right">
                                        <button class="btn btn-primary">Save and Continue</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form method="POST" action="{{url('user/add/education')}}">
                            @csrf
                            <input type="hidden" name="id" value="{{$user == null?'0':$user->id}}">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Education Level</small></label>
                                        <select class="form-control" name="education">
                                            <option value="0" {{$user->level == 0?"selected":""}}>Education Level</option>
                                            <option value="1" {{$user->level == 1?"selected":""}}>Basic</option>
                                            <option value="2" {{$user->level == 2?"selected":""}}>O'Level</option>
                                            <option value="3" {{$user->level == 3?"selected":""}}>A'Level</option>
                                            <option value="4" {{$user->level == 4?"selected":""}}>Sec./Tech/Vocational</option>
                                            <option value="5" {{$user->level == 5?"selected":""}}>Post Secondary Non-tertiary</option>
                                            <option value="6" {{$user->level == 6?"selected":""}}>Diploma or Equivalent</option>
                                            <option value="7" {{$user->level == 7?"selected":""}}>Degree or Equivalent</option>
                                            <option value="8" {{$user->level == 8?"selected":""}}>Masters or Equivalent</option>
                                            <option value="9" {{$user->level == 9?"selected":""}}>Doctoral or Equivalent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Institution</small></label>
                                        <input type="text" class="form-control" name="institution" placeholder="institution" value="{{$user->inst}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>Certification</small></label>
                                        <input type="text" class="form-control" placeholder="certification obtained" name="certification" value="{{$user->certification}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>From</small></label>
                                        <input type="text" class="form-control datepicker1" placeholder="From" name="from" value="{{$user->fr}}" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><small>To</small></label>
                                        <input type="text" class="form-control datepicker1" placeholder="To" name="to" value="{{$user->t}}" readonly>
                                    </div>
                                </div>
                                <div class='col-sm-12'>
                                    <div class="form-group text-right">
                                        <button class="btn btn-primary">Save and Continue</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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

        <!-- Email Modal -->
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
                                <button type='submit' class="btn btn-primary">Save User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
