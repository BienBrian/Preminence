 <!-- Sidenav -->
 <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
        <div class="container-fluid">
            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Brand -->
            <a class="navbar-brand pt-0" href="{{url('/home')}}">
                <img  src="{{ $site_settings == null?asset('website/icon.png'):asset('website/'.$site_settings->icon) }}"> &nbsp;<small>{{ $site_settings == null?"CHURCH APP":$site_settings->name}}</small>
            </a>
            <!-- User -->
            <ul class="nav align-items-center d-md-none">
                <!--<li class="nav-item dropdown">
                    <a class="nav-link nav-link-icon" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ni ni-bell-55"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right" aria-labelledby="navbar-default_dropdown_1">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </li>-->
                <?php $user_profile = \DB::table('profiles')->where("user_id", \Auth::user()->id)->first() ?>
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="media align-items-center">
                            <span class="avatar avatar-sm rounded-circle">
                                <img alt="Image placeholder" src="{{$user_profile == null ? asset('profile_images/default.jpg'): asset('profile_images/'.$user_profile->name)}}">
                            </span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                        <a href="{{url('/users/profile')}}" class="dropdown-item">
                            <i class="ni ni-single-02"></i>
                            <span>My profile</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#!" class="dropdown-item logout">
                            <i class="ni ni-user-run"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </li>
            </ul>
            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
                <!-- Collapse header -->
                <div class="navbar-collapse-header d-md-none">
                    <div class="row">
                        <div class="col-6 collapse-brand">
                            <a href="{{url('/home')}}">
                                <img  src="{{ $site_settings == null?asset('website/icon.png'):asset('website/'.$site_settings->icon) }}"> &nbsp;<small>{{ $site_settings == null?"CHURCH APP":$site_settings->name}}</small>
                            </a>
                        </div>
                        <div class="col-6 collapse-close">
                            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Form -->
                <!--<form class="mt-4 mb-3 d-md-none">
                    <div class="input-group input-group-rounded input-group-merge">
                        <input type="search" class="form-control form-control-rounded form-control-prepended" placeholder="Search" aria-label="Search">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <span class="fa fa-search"></span>
                            </div>
                        </div>
                    </div>
                </form>-->
                <!-- Navigation -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('home')}}">
                            <i class="ni ni-tv-2 text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('testimonials')}}">
                            <i class="ni ni-planet text-blue"></i> Testimonials
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('prayers')}}">
                            <i class="fas fa-hands text-orange"></i> Prayer Requests
                        </a>
                    </li>

                    <li class="nav-item">
                        <div class="dropdown" style='width: 100%;'>
                            <a class="nav-link dropdown-toggle" style='width: 100%;' id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                <i class="fas fa-coins text-green"></i>
                                <span style='width: 97%;'>Finances</span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="width: 100%; border-radius:0;">
                                <a class="dropdown-item" href="{{url('/users/activities')}}">
                                    Activities
                                </a>
                                <a class="dropdown-item" href="{{url('/users/pledges')}}">
                                    Pledges
                                </a>
                                <a class="dropdown-item" href="{{url('/users/groups')}}">
                                    Groups
                                </a>
                                <a class="dropdown-item" href="{{url('/users/groups_by_you')}}">
                                    Groups By You
                                </a>
                             </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/users/profile')}}">
                            <i class="fas fa-user text-red"></i> Profile
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{url('users/articles')}}">
                            <i class="fas fa-comments text-info"></i> Articles
                        </a>
                    </li>
                    <!--<li class="nav-item">
                        <a class="nav-link" href="./examples/tables.html">
                        <i class="ni ni-bullet-list-67 text-red"></i> Tables
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./examples/login.html">
                        <i class="ni ni-key-25 text-info"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./examples/register.html">
                        <i class="ni ni-circle-08 text-pink"></i> Register
                        </a>
                    </li>-->
                </ul>
                <!-- Divider -->
                <?php
                    $permissions1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
                    $permissions2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
                    return (json_encode($permissions));
                ?>
                @if($permissions1 != null || $permissions2 != null)
                    <hr class="my-3">

                <ul class="navbar-nav">
                    @if($permissions1->dashboard > 0 || $permissions1->dashboard > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/dashboard')}}">
                                <i class="ni ni-tv-2 text-primary"></i> Dashboard
                            </a>
                        </li>
                    @endif
                </ul>
                @endif
            </div>
        </div>
    </nav>
        <!-- Main content -->
        <div class="main-content">
            <!-- Top navbar -->
            <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
                <div class="container-fluid">
                    <!-- Brand -->
                    <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="{{url('home')}}">Dashboard</a>
                    <!-- Form -->
                    <!--<form class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">
                      <div class="form-group mb-0">
                          <div class="input-group input-group-alternative">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input class="form-control" placeholder="Search" type="text">
                          </div>
                      </div>
                    </form>-->
                    <!--logout form-->
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <!-- User -->
                    <ul class="navbar-nav align-items-center d-none d-md-flex">
                      <li class="nav-item dropdown">
                        <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <div class="media align-items-center">
                            <span class="avatar avatar-sm rounded-circle">
                                <img alt="Image placeholder" src="{{$user_profile == null ? asset('profile_images/default.jpg'): asset('profile_images/'.$user_profile->name)}}">
                            </span>
                            <div class="media-body ml-2 d-none d-lg-block">
                            <span class="mb-0 text-sm  font-weight-bold">{{Auth::user()->firstname}}  {{Auth::user()->lastname}}</span>
                            </div>
                          </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                          <div class=" dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome!</h6>
                          </div>
                          <a href="{{url('users/profile')}}" class="dropdown-item">
                            <i class="ni ni-single-02"></i>
                            <span>My profile</span>
                          </a>
                          <div class="dropdown-divider"></div>
                          <a href="{{url('logout')}}" class="dropdown-item logout">
                            <i class="ni ni-user-run"></i>
                            <span>Logout</span>
                          </a>
                        </div>
                      </li>
                    </ul>
                  </div>
                </nav>
