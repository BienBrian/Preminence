@extends('layouts.dashboard')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 bold text-header"><i class='fas fa-user'></i> View <b>User</b></h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/users/all') }}">Users</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('phone_conflict_user'))
                @php $conflict = session('phone_conflict_user'); @endphp
                <div class="alert alert-warning fade show" role="alert">
                    <h6 class="alert-heading font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i> Phone Number Conflict</h6>
                    <p class="mb-2">
                        The phone number <strong>{{ $conflict['phone'] }}</strong> is already assigned to
                        <a href="{{ url('dashboard/users/view/' . $conflict['id']) }}" class="font-weight-bold">{{ $conflict['name'] }}</a> (ID: {{ $conflict['id'] }}).
                    </p>
                    <p class="mb-3 text-muted small">If these are the same person, you can merge <strong>{{ $conflict['name'] }}</strong> into <strong>{{ $conflict['current_user_name'] }}</strong>. This will transfer all records (contributions, pledges, tags, etc.) from the duplicate to this user and archive the duplicate.</p>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-warning btn-sm mr-2" id="btn-merge-users"
                            data-keep-id="{{ $conflict['current_user_id'] }}"
                            data-merge-id="{{ $conflict['id'] }}"
                            data-merge-name="{{ $conflict['name'] }}">
                            <i class="fas fa-compress-arrows-alt mr-1"></i> Merge {{ $conflict['name'] }} into this user
                        </button>
                        <a href="{{ url('dashboard/users/view/' . $conflict['id']) }}" class="btn btn-outline-secondary btn-sm mr-2">
                            <i class="fas fa-eye mr-1"></i> View {{ $conflict['name'] }}
                        </a>
                        <button type="button" class="btn btn-outline-dark btn-sm" data-dismiss="alert">
                            <i class="fas fa-times mr-1"></i> Dismiss
                        </button>
                    </div>
                </div>
            @elseif($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    @foreach($errors->all() as $error)
                        <i class="fas fa-exclamation-circle"></i> {{ $error }}<br>
                    @endforeach
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <!-- Profile Summary Header -->
            @if($user)
            @php
                $initials = strtoupper(substr($user->firstname, 0, 1) . substr($user->lastname, 0, 1));
                $colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#6f42c1','#e83e8c','#fd7e14'];
                $color = $colors[$user->id % count($colors)];
            @endphp
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center flex-wrap">
                        <div style="width:64px;height:64px;border-radius:50%;background:{{ $color }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:24px;flex-shrink:0;" class="mr-3">
                            {{ $initials }}
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{ $user->firstname }} {{ $user->surname }} {{ $user->lastname }}</h4>
                            <div class="text-muted">
                                @if(isset($userRole))
                                    <span class="badge bg-primary mr-2">{{ $userRole->name }}</span>
                                @endif
                                @if($user->member_since)
                                    <small class="mr-3"><i class="fas fa-calendar-alt mr-1"></i> Joined {{ \Carbon\Carbon::parse($user->member_since)->format('d M, Y') }}</small>
                                @endif
                                @if($user->phone)
                                    <i class="fas fa-phone mr-1"></i> {{ $user->phone }}
                                    <a href="#" class="badge badge-{{ $user->phone_verified_at ? 'success' : 'secondary' }} ml-1 btn-toggle-verify" data-user-id="{{ $user->id }}" data-type="phone" title="Click to toggle verification">
                                        <i class="fas fa-{{ $user->phone_verified_at ? 'check-circle' : 'times-circle' }} mr-1"></i>{{ $user->phone_verified_at ? 'Verified' : 'Unverified' }}
                                    </a>
                                @endif
                                @if($user->email)
                                    <span class="ml-3"><i class="fas fa-envelope mr-1"></i> {{ $user->email }}
                                    <a href="#" class="badge badge-{{ $user->email_verified_at ? 'success' : 'secondary' }} ml-1 btn-toggle-verify" data-user-id="{{ $user->id }}" data-type="email" title="Click to toggle verification">
                                        <i class="fas fa-{{ $user->email_verified_at ? 'check-circle' : 'times-circle' }} mr-1"></i>{{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                    </a>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="ml-auto text-right" id="verification-status-area">
                            @if($user->phone_verified_at)
                                <span class="badge badge-success px-3 py-2" style="font-size:0.9rem;">
                                    <i class="fas fa-check-circle mr-1"></i> Verified
                                </span>
                            @else
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown" data-user-id="{{ $user->id }}" id="btn-send-verification">
                                        <i class="fas fa-paper-plane mr-1"></i> Send Verification Request
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        @if($user->phone)
                                        <a href="#" class="dropdown-item btn-send-verify-via" data-method="sms" data-user-id="{{ $user->id }}">
                                            <i class="fas fa-sms text-success mr-2"></i> via SMS
                                        </a>
                                        @endif
                                        @if($user->email)
                                        <a href="#" class="dropdown-item btn-send-verify-via" data-method="email" data-user-id="{{ $user->id }}">
                                            <i class="fas fa-envelope text-primary mr-2"></i> via Email
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tags Section -->
            @can('Manage Tags')
            @if($user)
            <div class="card shadow-sm mb-4">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center flex-wrap">
                        <strong class="mr-2"><i class="fas fa-tags text-muted"></i> Tags:</strong>
                        <span id="user-tags-list">
                            @foreach($userTags as $tag)
                                <span class="badge mr-1 mb-1" style="background:{{ $tag->color }};color:#fff;padding:4px 10px;">
                                    {{ $tag->name }}
                                    <a href="#" class="text-white ml-1 btn-remove-tag" data-tag-id="{{ $tag->id }}" data-user-id="{{ $user->id }}" style="text-decoration:none;">&times;</a>
                                </span>
                            @endforeach
                        </span>
                        <select id="tag-assign-select" class="form-control form-control-sm ml-2" style="width:180px;display:inline-block;"></select>
                    </div>
                </div>
            </div>
            @endif
            @endcan

            <!-- Tabs -->
            <div class="card shadow-sm">
                <div class="card-header p-0">
                    <ul class="nav nav-pills nav-fill flex-nowrap" id="userTabs" role="tablist" style="overflow-x:auto;overflow-y:hidden;">
                        <li class="nav-item">
                            <a class="nav-link active" id="basic-tab" data-toggle="tab" href="#basic" role="tab">
                                <i class='fas fa-user text-indigo'></i> <span class="d-none d-sm-inline">Basic</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab">
                                <i class='fas fa-phone text-primary'></i> <span class="d-none d-sm-inline">Contacts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="church-tab" data-toggle="tab" href="#church" role="tab">
                                <i class='fas fa-university text-success'></i> <span class="d-none d-sm-inline">Church</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="family-tab" data-toggle="tab" href="#family" role="tab">
                                <i class='fas fa-users text-danger'></i> <span class="d-none d-sm-inline">Family</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="work-tab" data-toggle="tab" href="#work" role="tab">
                                <i class='fas fa-graduation-cap text-muted'></i> <span class="d-none d-sm-inline">Work & Edu</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body tab-content p-0" id="userTabContent">
                    <!-- ========== BASIC TAB ========== -->
                    <div class="tab-pane fade show active p-3" id="basic" role="tabpanel">
                        <form method="POST" action="{{ url('dashboard/users/update/basic') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user == null ? '0' : $user->id }}">

                            <!-- Personal Information Card -->
                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-id-card mr-1"></i> Personal Information</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" name="fname" value="{{ $user == null ? '' : $user->firstname }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Surname <small class="text-muted">(Middle)</small></label>
                                            <input type="text" class="form-control" name="surname" value="{{ $user == null ? '' : $user->surname }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" name="lname" value="{{ $user == null ? '' : $user->lastname }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Role</label>
                                            <select class="form-select form-control" name="role">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ isset($userRole) && $userRole->id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">Email Address <small class="text-muted">(Optional)</small></label>
                                            <input type="email" class="form-control" name="email" value="{{ $user == null ? '' : $user->email }}">
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">Phone</label>
                                            @php $phoneCode = $site_settings->phone_code ?? '254'; @endphp
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+{{ $phoneCode }}</span>
                                                </div>
                                                <input type="text" class="form-control" name="phone" value="{{ $user == null ? '' : ($user->phone != null && strlen($user->phone) >= strlen($phoneCode) && substr($user->phone, 0, strlen($phoneCode)) === $phoneCode ? substr($user->phone, strlen($phoneCode)) : $user->phone) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select form-control" name="gender">
                                                <option value="0" {{ $user != null && $user->gender == 0 ? 'selected' : '' }}>Male</option>
                                                <option value="1" {{ $user != null && $user->gender == 1 ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Demographics Card -->
                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Demographics</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Marital Status</label>
                                            <select class="form-select form-control" name="marital">
                                                <option value="1" {{ $user != null && $user->marital == 1 ? 'selected' : '' }}>Single</option>
                                                <option value="2" {{ $user != null && $user->marital == 2 ? 'selected' : '' }}>Married</option>
                                                <option value="3" {{ $user != null && $user->marital == 3 ? 'selected' : '' }}>Divorced</option>
                                                <option value="4" {{ $user != null && $user->marital == 4 ? 'selected' : '' }}>Separated</option>
                                                <option value="5" {{ $user != null && $user->marital == 5 ? 'selected' : '' }}>Widow(er)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Nationality</label>
                                            <select class="form-select form-control" name="country">
                                                <option value="1" {{ $user != null && $user->country == 1 ? 'selected' : '' }}>Kenya</option>
                                                <option value="2" {{ $user != null && $user->country == 2 ? 'selected' : '' }}>Uganda</option>
                                                <option value="3" {{ $user != null && $user->country == 3 ? 'selected' : '' }}>Tanzania</option>
                                                <option value="4" {{ $user != null && $user->country == 4 ? 'selected' : '' }}>Burundi</option>
                                                <option value="5" {{ $user != null && $user->country == 5 ? 'selected' : '' }}>Rwanda</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="text" class="form-control datepicker1" name="dob" value="{{ $user == null ? '' : $user->dob }}" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Date Joined</label>
                                            <input type="text" class="form-control datepicker1" name="joined" value="{{ $user == null ? '' : $user->joined }}" readonly>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Profile Photo</label>
                                            <div class="mb-2">
                                                <img src="{{ $user == null ? asset('profile_images/default.jpg') : ($user->name == '' || $user->name == null ? asset('profile_images/default.jpg') : asset('profile_images/'.$user->name)) }}" class="img-fluid rounded" style="max-width: 150px;">
                                            </div>
                                            <input type="file" class="form-control" name="image">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mb-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Basic Info</button>
                            </div>
                        </form>
                    </div>

                    <!-- ========== CONTACTS TAB ========== -->
                    <div class="tab-pane fade p-3" id="contact" role="tabpanel">
                        <form method="POST" action="{{ url('dashboard/users/update/contacts') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user == null ? '0' : $user->id }}">

                            <!-- Secondary Contact Card -->
                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-phone-alt mr-1"></i> Secondary Contact</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Secondary Contact Phone</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+{{ $site_settings->phone_code ?? '254' }}</span>
                                                </div>
                                                <input type="text" class="form-control" name="phone" value="{{ $scontact == null ? '' : $scontact->secondary_phone }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Home Town</label>
                                            <input type="text" class="form-control" name="town" value="{{ $scontact == null ? '' : $scontact->town }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Card -->
                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-ambulance mr-1"></i> Emergency Contact</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Emergency Contact Person</label>
                                            <div id="searchdiv">
                                                <input type="text" class="form-control" name="emergency" value="{{ $emergency == null ? '' : $emergency->firstname.', '.$emergency->lastname.', '.$emergency->email.', '.$emergency->phone }}" autocomplete="off">
                                                <input type="hidden" name="emergency_user_id" value="{{ $emergency == null ? '' : $emergency->user_id2 }}">
                                                <div class="searchdiv shadow bg-white border list-group" id="searchdiv1" style="display:none;">
                                                    <p><i class="fas fa-spinner fa-pulse"></i> Searching</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Emergency Contact Phone</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+{{ $site_settings->phone_code ?? '254' }}</span>
                                                </div>
                                                @php $ePhoneCode = $site_settings->phone_code ?? '254'; @endphp
                                                <input type="text" class="form-control" name="emergency-no" value="{{ $emergency == null ? '' : (strlen($emergency->phone) > strlen($ePhoneCode) && substr($emergency->phone, 0, strlen($ePhoneCode)) === $ePhoneCode ? substr($emergency->phone, strlen($ePhoneCode)) : $emergency->phone) }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Postal Address</label>
                                            <textarea class="form-control" name="postal" rows="2">{{ $scontact == null ? '' : $scontact->address }}</textarea>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Residential Location / Nearest Landmark</label>
                                            <select class="form-control" name="resident" id="residentSelect">
                                                @if($scontact && $scontact->resident)
                                                    <option value="{{ $scontact->resident }}" selected>{{ $scontact->resident }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mb-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Contacts</button>
                            </div>
                        </form>
                    </div>

                    <!-- ========== CHURCH TAB ========== -->
                    <div class="tab-pane fade p-3" id="church" role="tabpanel">
                        <form method="POST" action="{{ url('dashboard/users/update/church') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user == null ? '0' : $user->id }}">

                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-church mr-1"></i> Church Information</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Community</label>
                                            <select class="form-select form-control" name="community">
                                                <option value="0">Select Community</option>
                                                @foreach($communities as $community)
                                                    <option value="{{ $community->id }}" {{ $user != null && $user->communities == $community->id ? 'selected' : '' }}>{{ $community->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Department</label>
                                            <select class="form-select form-control" name="department">
                                                <option value="0">Select Department</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}" {{ $user != null && $user->departments == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">Year Joined</label>
                                            <select class="form-select form-control" name="joined">
                                                @php $years = date('Y'); @endphp
                                                @for($y = $years; $y >= $years - 10; $y--)
                                                    <option value="{{ $y }}" {{ $user != null && $user->yearjoined == $y ? 'selected' : '' }}>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">Special Gifts / Ministry</label>
                                            <input type="text" class="form-control" name="gifts" value="{{ $user == null ? '' : $user->gifts }}">
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">Previous Church</label>
                                            <input type="text" class="form-control" name="previous" value="{{ $user == null ? '' : $user->previous_church }}">
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Position Held</label>
                                            <input type="text" class="form-control" name="position" value="{{ $user == null ? '' : $user->position }}">
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2">{{ $user == null ? '' : $user->remarks }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mb-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Church Info</button>
                            </div>
                        </form>
                    </div>

                    <!-- ========== FAMILY TAB ========== -->
                    <div class="tab-pane fade p-3" id="family" role="tabpanel">
                        <form method="POST" action="{{ url('dashboard/users/update/family') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user == null ? '0' : $user->id }}">

                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-users mr-1"></i> Family Member</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Relationship</label>
                                            <select class="form-select form-control" name="relationship">
                                                <option value="">-- Select Relationship --</option>
                                                <option value="1" {{ $user != null && $user->relationship == "1" ? 'selected' : '' }}>Spouse</option>
                                                <option value="2" {{ $user != null && $user->relationship == "2" ? 'selected' : '' }}>Child</option>
                                                <option value="3" {{ $user != null && $user->relationship == "3" ? 'selected' : '' }}>Sibling</option>
                                                <option value="4" {{ $user != null && $user->relationship == "4" ? 'selected' : '' }}>Parent</option>
                                                <option value="5" {{ $user != null && $user->relationship == "5" ? 'selected' : '' }}>Guardian</option>
                                                <option value="6" {{ $user != null && $user->relationship == "6" ? 'selected' : '' }}>Next of Kin</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Name</label>
                                            <div id="searchdiva">
                                                <input type="text" class="form-control" name="name" autocomplete="off" value="{{ $user != null && $user->familyname ? $user->familyname : '' }}">
                                                <div class="searchdiv shadow bg-white border list-group" id="searchdiv2" style="display:none;">
                                                    <p class="list-group-item"><i class="fas fa-spinner"></i> Searching...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Phone</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">+{{ $site_settings->phone_code ?? '254' }}</span>
                                                </div>
                                                <input type="text" class="form-control" name="phone" value="{{ $user != null && $user->familyphone ? $user->familyphone : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="{{ $user != null && $user->familyemail ? $user->familyemail : '' }}">
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Current Photo</label>
                                            <div>
                                                <img src="{{ $user != null && $user->image ? asset('relatives/'.$user->image) : asset('profile_images/default.jpg') }}" class="img-fluid rounded" style="max-width: 150px;">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">Upload Photo</label>
                                            <input type="file" class="form-control" name="profile_image">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mb-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Family Info</button>
                            </div>
                        </form>
                    </div>

                    <!-- ========== WORK & EDU TAB ========== -->
                    <div class="tab-pane fade p-3" id="work" role="tabpanel">
                        <!-- Employment Card -->
                        <form method="POST" action="{{ url('dashboard/users/update/profession') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user == null ? '0' : $user->id }}">

                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-briefcase mr-1"></i> Employment</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Occupation Industry</label>
                                            <select class="form-select form-control" name="occupation">
                                                <option value="">-- Select Industry --</option>
                                                <option value="1" {{ $user != null && $user->occupation == 1 ? 'selected' : '' }}>Accommodation & Food Services</option>
                                                <option value="2" {{ $user != null && $user->occupation == 2 ? 'selected' : '' }}>Arts and Entertainment</option>
                                                <option value="3" {{ $user != null && $user->occupation == 3 ? 'selected' : '' }}>Corporate Administration and Management</option>
                                                <option value="4" {{ $user != null && $user->occupation == 4 ? 'selected' : '' }}>Educational Services</option>
                                                <option value="5" {{ $user != null && $user->occupation == 5 ? 'selected' : '' }}>Engineering, Scientific and Technical</option>
                                                <option value="6" {{ $user != null && $user->occupation == 6 ? 'selected' : '' }}>Finance and Insurance</option>
                                                <option value="7" {{ $user != null && $user->occupation == 7 ? 'selected' : '' }}>Food, Agriculture and Forestry</option>
                                                <option value="8" {{ $user != null && $user->occupation == 8 ? 'selected' : '' }}>Government</option>
                                                <option value="9" {{ $user != null && $user->occupation == 9 ? 'selected' : '' }}>Health Care and Social Assistance</option>
                                                <option value="10" {{ $user != null && $user->occupation == 10 ? 'selected' : '' }}>Information and Communication Technology</option>
                                                <option value="11" {{ $user != null && $user->occupation == 11 ? 'selected' : '' }}>Janitorial, Waste Management</option>
                                                <option value="12" {{ $user != null && $user->occupation == 12 ? 'selected' : '' }}>Manufacturing and Processing</option>
                                                <option value="13" {{ $user != null && $user->occupation == 13 ? 'selected' : '' }}>Mining, Quarrying, Oil and Gas</option>
                                                <option value="14" {{ $user != null && $user->occupation == 14 ? 'selected' : '' }}>Public Administration</option>
                                                <option value="15" {{ $user != null && $user->occupation == 15 ? 'selected' : '' }}>Public Utility Services</option>
                                                <option value="16" {{ $user != null && $user->occupation == 16 ? 'selected' : '' }}>Real Estate, Rental and Leasing</option>
                                                <option value="17" {{ $user != null && $user->occupation == 17 ? 'selected' : '' }}>Religion</option>
                                                <option value="18" {{ $user != null && $user->occupation == 18 ? 'selected' : '' }}>Retail Trade</option>
                                                <option value="19" {{ $user != null && $user->occupation == 19 ? 'selected' : '' }}>Sports and Recreation</option>
                                                <option value="20" {{ $user != null && $user->occupation == 20 ? 'selected' : '' }}>Security and Armed Forces</option>
                                                <option value="21" {{ $user != null && $user->occupation == 21 ? 'selected' : '' }}>Transportation and Warehousing</option>
                                                <option value="22" {{ $user != null && $user->occupation == 22 ? 'selected' : '' }}>Wholesale Trade</option>
                                                <option value="23" {{ $user != null && $user->occupation == 23 ? 'selected' : '' }}>None</option>
                                                <option value="24" {{ $user != null && $user->occupation == 24 ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Specific Job</label>
                                            <input type="text" class="form-control" name="specific" value="{{ $user == null ? '' : $user->specific }}">
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">Institution</label>
                                            <input type="text" class="form-control" name="institution" value="{{ $user == null ? '' : $user->institution }}">
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">From</label>
                                            <input type="text" class="form-control datepicker1" name="from" value="{{ $user == null ? '' : $user->from }}" readonly>
                                        </div>
                                        <div class="col-md-4 mt-3">
                                            <label class="form-label">To</label>
                                            <input type="text" class="form-control datepicker1" name="to" value="{{ $user == null ? '' : $user->to }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mb-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Employment</button>
                            </div>
                        </form>

                        <!-- Education Card -->
                        <form method="POST" action="{{ url('dashboard/users/update/education') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user == null ? '0' : $user->id }}">

                            <div class="card mb-3">
                                <div class="card-header"><h6 class="mb-0"><i class="fas fa-graduation-cap mr-1"></i> Education</h6></div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Education Level</label>
                                            <select class="form-select form-control" name="education">
                                                <option value="0" {{ $user != null && $user->level == 0 ? 'selected' : '' }}>-- Select Level --</option>
                                                <option value="1" {{ $user != null && $user->level == 1 ? 'selected' : '' }}>Basic</option>
                                                <option value="2" {{ $user != null && $user->level == 2 ? 'selected' : '' }}>O'Level</option>
                                                <option value="3" {{ $user != null && $user->level == 3 ? 'selected' : '' }}>A'Level</option>
                                                <option value="4" {{ $user != null && $user->level == 4 ? 'selected' : '' }}>Sec./Tech/Vocational</option>
                                                <option value="5" {{ $user != null && $user->level == 5 ? 'selected' : '' }}>Post Secondary Non-tertiary</option>
                                                <option value="6" {{ $user != null && $user->level == 6 ? 'selected' : '' }}>Diploma or Equivalent</option>
                                                <option value="7" {{ $user != null && $user->level == 7 ? 'selected' : '' }}>Degree or Equivalent</option>
                                                <option value="8" {{ $user != null && $user->level == 8 ? 'selected' : '' }}>Masters or Equivalent</option>
                                                <option value="9" {{ $user != null && $user->level == 9 ? 'selected' : '' }}>Doctoral or Equivalent</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Institution</label>
                                            <input type="text" class="form-control" name="institution" value="{{ $user == null ? '' : $user->inst }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Certification</label>
                                            <input type="text" class="form-control" name="certification" value="{{ $user == null ? '' : $user->certification }}">
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">From</label>
                                            <input type="text" class="form-control datepicker1" name="from" value="{{ $user == null ? '' : $user->fr }}" readonly>
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <label class="form-label">To</label>
                                            <input type="text" class="form-control datepicker1" name="to" value="{{ $user == null ? '' : $user->t }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mb-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Education</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        flatpickr(".datepicker1", { dateFormat: "Y-m-d" });

        // Residence Select2
        $('#residentSelect').select2({
            width: '100%',
            placeholder: 'Select Residence',
            allowClear: true,
            tags: true,
            ajax: {
                url: '{{ url("dashboard/settings/residences/search") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term }; },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return { text: item.name, id: item.name };
                        })
                    };
                },
                cache: true
            }
        });

        // Tag assignment Select2
        @can('Manage Tags')
        $('#tag-assign-select').select2({
            placeholder: 'Add tag...',
            allowClear: true,
            width: '180px',
            ajax: {
                url: '{{ url("dashboard/settings/tags/search") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) {
                    return { results: $.map(data, function (item) { return { text: item.name, id: item.id }; }) };
                },
                cache: true
            }
        });

        $('#tag-assign-select').on('select2:select', function (e) {
            var tagId = e.params.data.id;
            $.ajax({
                url: '{{ url("dashboard/settings/tags/assign") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', tag_id: tagId, user_id: {{ $user ? $user->id : 0 }} }
            }).done(function () {
                location.reload();
            });
        });

        // Toggle verification badge
        $(document).on('click', '.btn-toggle-verify', function (e) {
            e.preventDefault();
            var el = $(this);
            var userId = el.data('user-id');
            var type = el.data('type');
            $.ajax({
                url: '{{ url("dashboard/users/toggle-verification") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', user_id: userId, type: type }
            }).done(function (res) {
                if (res.verified) {
                    el.removeClass('badge-secondary').addClass('badge-success');
                    el.html('<i class="fas fa-check-circle mr-1"></i>Verified');
                } else {
                    el.removeClass('badge-success').addClass('badge-secondary');
                    el.html('<i class="fas fa-times-circle mr-1"></i>Unverified');
                }
                toastr.success(res.success);
            }).fail(function (xhr) {
                toastr.error(xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred');
            });
        });

        // Send verification request via SMS or Email
        $(document).on('click', '.btn-send-verify-via', function (e) {
            e.preventDefault();
            var el = $(this);
            var method = el.data('method');
            var userId = el.data('user-id');
            var btn = $('#btn-send-verification');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-pulse mr-1"></i> Sending...');
            $.ajax({
                url: '{{ url("dashboard/users/send-verification-request") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', user_id: userId, method: method }
            }).done(function (res) {
                toastr.success(res.success);
                btn.html('<i class="fas fa-check mr-1"></i> Sent!');
                setTimeout(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Send Verification Request');
                    btn.addClass('dropdown-toggle').attr('data-toggle', 'dropdown');
                }, 3000);
            }).fail(function (xhr) {
                toastr.error(xhr.responseJSON ? xhr.responseJSON.error : 'Failed to send');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Send Verification Request');
                btn.addClass('dropdown-toggle').attr('data-toggle', 'dropdown');
            });
        });

        $(document).on('click', '.btn-remove-tag', function (e) {
            e.preventDefault();
            var el = $(this);
            $.ajax({
                url: '{{ url("dashboard/settings/tags/remove") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', tag_id: el.data('tag-id'), user_id: el.data('user-id') }
            }).done(function () {
                el.closest('.badge').fadeOut(200, function() { $(this).remove(); });
            });
        });
        @endcan

        // Merge users
        $(document).on('click', '#btn-merge-users', function () {
            var keepId = $(this).data('keep-id');
            var mergeId = $(this).data('merge-id');
            var mergeName = $(this).data('merge-name');
            Swal.fire({
                title: 'Confirm Merge',
                html: 'All records belonging to <strong>' + mergeName + '</strong> (contributions, pledges, tags, etc.) will be transferred to this user.<br><br>The duplicate will be archived.<br><br><strong>This action cannot be undone.</strong>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e6a817',
                confirmButtonText: 'Yes, merge them',
                cancelButtonText: 'Cancel',
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("dashboard/users/merge") }}',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', keep_id: keepId, merge_id: mergeId }
                    }).done(function (res) {
                        Swal.fire('Merged!', res.success, 'success').then(function () {
                            if (res.redirect) window.location.href = res.redirect;
                            else location.reload();
                        });
                    }).fail(function (xhr) {
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.error : 'Merge failed', 'error');
                    });
                }
            });
        });
    });
</script>
@endpush
