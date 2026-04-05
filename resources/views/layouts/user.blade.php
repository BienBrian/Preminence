<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ $site_settings == null?"favicon.ico":asset('website/'.$site_settings->favicon) }}">

    <title>{{ $site_settings == null?"Church App":$site_settings->name }}</title>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/app.js') }}" defer></script>-->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">

    <!-- Styles -->
    <!--<link href="{{ asset('css/app.css') }}" rel="stylesheet">--><!-- Icons -->
    <link href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="{{asset('assets/css/argon.css?v=1.0.0')}}" rel="stylesheet">
    <link href="{{asset('datepicker/css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.1/croppie.css" rel="stylesheet">
    <!-- Custom style -->
    <link type="text/css" href="{{asset('css/styles.css')}}" rel="stylesheet">
    <link type="text/css" href="{{ $site_settings == null?"":asset('css/'.$site_settings->theme) }}" rel="stylesheet">
</head>
<body>
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
                
                @if($permissions1 != null || $permissions2 != null)
                    <hr class="my-3">

                <ul class="navbar-nav">
                    @if($permissions1 != null)
                        @if($permissions1->dashboard > 0 || $permissions2->dashboard > 0)
                            <li class="nav-item">
                                <a class="nav-link" href="{{url('users/dashboard')}}">
                                    <i class="ni ni-tv-2 text-primary"></i> Dashboard
                                </a>
                            </li>
                        @endif
                    @else
                    @endif
                    @if($permissions1->dashboard > 0 || $permissions2->dashboard > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('/users/testimonials')}}">
                                <i class="ni ni-planet text-blue"></i> Testimonials
                            </a>
                        </li>
                    @endif
                    @if($permissions1->websites > 0 || $permissions2->websites > 0)
                        <li class="nav-item">
                            <div class="dropdown" style='width: 100%;'>
                                <a class="nav-link dropdown-toggle" style='width: 100%;' id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="fas fa-globe text-orange"></i>
                                    <span style='width: 97%;'>Websites</span>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="width: 100%; border-radius:0;">
                                    <a class="dropdown-item" href="{{url('users/settings')}}">
                                        Settings
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/homepage')}}">
                                        Home Page
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/gallery')}}">
                                        Gallery
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/pastorsmessage')}}">
                                        Pastors Message
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/communities')}}">
                                        Communities
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/orderofservice')}}">
                                        Order of Service
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/weeklyverse')}}">
                                        Weekly Verse
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if($permissions1->finances > 0 || $permissions2->finances > 0)
                        <li class="nav-item">
                            <div class="dropdown" style='width: 100%;'>
                                <a class="nav-link dropdown-toggle" style='width: 100%;' id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="fas fa-coins text-red"></i>
                                    <span style='width: 97%;'>Finances</span>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="width: 100%; border-radius:0;">
                                    <a class="dropdown-item" href="{{url('users/funds')}}">
                                        Funds/Tithes/Offerings
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/donations')}}">
                                        Donations
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/myassets')}}">
                                        Assets
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/expenses')}}">
                                        Expenses
                                    </a>
                                    <a class="dropdown-item" href="{{url('users/permissions/activities')}}">
                                        Activities
                                    </a>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if($permissions1->sermons > 0 || $permissions2->sermons > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/sermons')}}">
                                <i class="fas fa-microphone text-green"></i> Sermons
                            </a>
                        </li>
                    @endif
                    @if($permissions1->events > 0 || $permissions2->events > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/events')}}">
                                <i class="fas fa-bell text-info"></i> Events
                            </a>
                        </li>
                    @endif
                    @if($permissions1->prayers > 0 || $permissions2->prayers > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/permissions/prayers')}}">
                                <i class="fas fa-hands text-blue"></i> Prayers
                            </a>
                        </li>
                    @endif
                    @if($permissions1->notices > 0 || $permissions2->notices > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/notices')}}">
                                <i class="fas fa-bell text-indigo"></i> Notices
                            </a>
                        </li>
                    @endif
                    @if($permissions1->departments > 0 || $permissions2->departments > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/departments')}}">
                                <i class="fas fa-boxes text-red"></i> Departments
                            </a>
                        </li>
                    @endif
                    @if($permissions1->users > 0 || $permissions2->users > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/users')}}">
                                <i class="fas fa-users text-blue"></i> Users
                            </a>
                        </li>
                    @endif
                    @if($permissions1->articles > 0 || $permissions2->articles > 0)
                        <li class="nav-item">
                            <a class="nav-link" href="{{url('users/permissions/articles')}}">
                                <i class="fas fa-comments text-yellow"></i> Articles
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
                @yield('content')

          <!-- Footer -->
          <footer class="footer">
            @if (\Session::has('success'))
                <div class="notifications bg-success shadow">
                    <p class='text-white text-center'><i class='fas fa-check-circle'></i> {{ \Session::get('success') }}</p>
                </div>
            @endif
            @if (\Session::has('error'))
                <div class="notifications bg-danger shadow">
                    <p class='text-white text-center'><i class='fas fa-exclamation-circle'></i> {{ \Session::get('error') }}</p>
                </div>
            @endif
            @if (count($errors) > 0)
                <div class="notifications bg-danger shadow">
                    <p class='text-white text-center'>
                        @foreach ($errors->all() as $error)
                            <br><i class='fas fa-angle-right'></i> {{ $error }}</li>
                        @endforeach
                    </p>
                </div>
            @endif
            <div class="row align-items-center justify-content-xl-between">
              <div class="col-xl-6">
                <div class="copyright text-center text-xl-left text-muted">
                  &copy; {{date('Y')}} <a href="https://www.convenience.co.ke" class="font-weight-bold ml-1" target="_blank">Convenience Designs</a>
                </div>
              </div>
              <!--<div class="col-xl-6">
                <ul class="nav nav-footer justify-content-center justify-content-xl-end">
                  <li class="nav-item">
                    <a href="https://www.creative-tim.com" class="nav-link" target="_blank">Creative Tim</a>
                  </li>
                  <li class="nav-item">
                    <a href="https://www.creative-tim.com/presentation" class="nav-link" target="_blank">About Us</a>
                  </li>
                  <li class="nav-item">
                    <a href="http://blog.creative-tim.com" class="nav-link" target="_blank">Blog</a>
                  </li>
                  <li class="nav-item">
                    <a href="https://github.com/creativetimofficial/argon-dashboard/blob/master/LICENSE.md" class="nav-link" target="_blank">MIT License</a>
                  </li>
                </ul>
              </div>-->
            </div>
          </footer>
        </div>
      </div>
      <!-- Argon Scripts -->
      <!-- Core -->
      <script src="{{asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
      <script src="{{asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
      <!-- Optional JS -->
      <script src="{{asset('assets/vendor/chart.js/dist/Chart.min.js')}}"></script>
      <script src="{{asset('assets/vendor/chart.js/dist/Chart.extension.js')}}"></script>
      <!-- Argon JS -->
      <script src="{{asset('assets/js/argon.js?v=1.0.0')}}"></script>
      <script src="{{asset('datepicker/js/bootstrap-datetimepicker.js')}}"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.1/croppie.js"></script>
      <script>
          $(document).ready(function(){
              $('a.logout').click(function(e){
                  e.preventDefault();
                  $('#logout-form').submit();
              });

            setTimeout(function(){
                $(".notifications").fadeOut();
            }, 4000);

            $('.datepicker').datetimepicker({
                weekStart: 1,
                todayBtn:  1,
                autoclose: 1,
                todayHighlight: 1,
                startView: 2,
                forceParse: 0,
                showMeridian: 1
            });
            $('.prev').html("<i class='fas fa-angle-left'></i>");
            $('.next').html("<i class='fas fa-angle-right'></i>");
            $('.pagination').addClass('justify-content-end');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }

            });
            $('.btn-upload-favicon, .btn-upload-icon, .btn-upload-homepage, .btn-upload-gallery').attr('disabled', 'disabled');

              //favicon
              $('.btn-favicon').click(function(){
                  $('.favicon-form input[type="file"]').click();
              });
              $('.favicon-form input[type="file"]').change(function(){
                    $('.btn-upload-favicon').removeAttr('disabled');
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#view-favicon').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });

              $('.btn-upload-favicon').click(function(){
                  $('.favicon-form').submit();
              });

              //icon
              $('.btn-icon').click(function(){
                  $('.icon-form input[type="file"]').click();
              });
              $('.icon-form input[type="file"]').change(function(){
                    $('.btn-upload-icon').removeAttr('disabled');
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#view-icon').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });

              $('.btn-upload-icon').click(function(){
                  $('.icon-form').submit();
              });


              //homepage
              $('.btn-homepage').click(function(){
                  $('.homepage-form input[type="file"]').click();
              });
              $('.homepage-form input[type="file"]').change(function(){
                    $('.btn-upload-homepage').removeAttr('disabled');
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#view-homepage').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });

              $('.btn-upload-homepage').click(function(){
                  $('.homepage-form').submit();
              });

              //gallery
              $('.btn-gallery').click(function(){
                  $('.gallery-form input[type="file"]').click();
              });
              $('.gallery-form input[type="file"]').change(function(){
                    $('.btn-upload-gallery').removeAttr('disabled');
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#view-gallery').attr('src', e.target.result);
                        $('#view-gallery').removeClass('d-none');
                        $('#gallery-caption').addClass('d-none');
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });

              $('.btn-upload-gallery').click(function(){
                  $('.gallery-form').submit();
              });

              //gallery view
              $('.btn-close-gallery').click(function(e){
                  e.preventDefault();
                  $('.gallery-view').addClass('d-none');
              });

              $('.btn-view').click(function(e){
                  e.preventDefault();
                  var image = $(this).closest('tr').find('th img').attr('src');
                  $('.gallery-view img').attr('src', image);
                  $('.gallery-view').removeClass('d-none');
              });

              //communities photo
              $('.comphoto').click(function(e){
                  e.preventDefault();
                  $(".com-form input[type='file']").click();
              });
              $('.com-form input[type="file"]').change(function(){
                    var input = $(this);
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('.com-form img').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input[0].files[0]);
              });

              //cropping function
              $('#imageModal').on('shown.bs.modal', function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var parentW = $('#upload-demo').closest('.col-sm-12').width();
                    var vwidth = parentW;
                    var vheight = vwidth*3/4;

                    $uploadCrop = $('#upload-demo').croppie({
                        enableExif: true,
                        viewport: {
                            width: vwidth*.9,
                            height: vheight*.9,
                            type: 'canvas'
                        },

                        boundary: {
                            width: parentW,
                            height: (parentW)*4/4
                        }
                    });
                    $('.upload').click(function(){
                        $('#upload').click();
                    });

                    $('#upload').on('change', function () {
                        $('.upload-image').removeAttr('disabled');
                        $('#upload-demo').removeClass('d-none');
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            $uploadCrop.croppie('bind', {
                                url: e.target.result
                            }).then(function(){
                            console.log('jQuery bind complete');
                        });
                    }
                    reader.readAsDataURL(this.files[0]);
                });

                $('.upload-result').on('click', function (ev) {
                    $uploadCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {
                        $('.feedback').html("<p class='text-center text-info'><i class='fas fa-spinner fa-pulse'></i> Saving... Please wait</p>");
                        $.ajax({
                            url: "/pastorsmessageimage",
                            type: "POST",
                            data: {"image":resp},
                            success: function (data) {
                                try {
                                    json = $.parseJSON(data);
                                } catch (e) {
                                    // not json
                                }
                                $('.feedback').html(data.success);
                                window.location.reload();
                            },
                            error: function(){
                                $('.feedback').html("<p class='text-center text-danger'><i class='fas fa-warning'></i> Unable to save image</p>");
                            }
                        });
                    });
                });
            });

            //add services
            $('.showServiceModal').click(function(){
                $('#services input[name="id"]').val(0);
                $('#services input[name="time"]').val("");
                $('#services textarea[name="description"]').val("");
                $('#services select[name="days"]').val("Sunday");
                $('#services input[name="venue"]').val("");
                $('#services .modal-body .btn').text("Add Service");
            });
            //services edit
            $('.btn-edit-service').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                var time = $(this).closest('tr').find('td:nth-child(5)').text();
                var description = $(this).closest('tr').find('td:nth-child(2)').text();
                var venue = $(this).closest('tr').find('td:nth-child(3)').text();
                $('#services input[name="id"]').val(id);
                $('#services input[name="time"]').val(time);
                $('#services textarea[name="description"]').val(description);
                $("input[name='venue']").val(venue);
                $('#services').modal('show');
                $('#services .modal-body .btn').text("Edit Service");
            });

            $('.showFundSource').click(function(){
                $('#fundSourceModal input[name="id"]').val(0);
                $('#fundSourceModal input[name="name"]').val("");
                $('#fundSourceModal textarea[name="description"]').val("");
                $('#fundSourceModal .modal-body .btn').text("Add Type");
            });
            //fundstype edit
            $('.editsource').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                var name = $(this).closest('tr').find('td:nth-child(2)').text();
                var description = $(this).closest('tr').find('td:nth-child(3)').text();
                $('#fundSourceModal input[name="id"]').val(id);
                $('#fundSourceModal input[name="name"]').val(name);
                $('#fundSourceModal textarea[name="description"]').val(description);
                $('#fundSourceModal').modal('show');
                $('#fundSourceModal .modal-body .btn').text("Edit Type");
            });

            //funds edit
            $('.showFundsModal').click(function(){
                $('#fundsModal input[name="id"]').val(0);
                $('#fundsModal input[name="amount"]').val("");
            });

            $('.fundsedit').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                var amount = $(this).closest('tr').find('td:nth-child(2)').text();
                var description = $(this).closest('tr').find('td:nth-child(3)').text();
                $('#fundsModal input[name="id"]').val(id);
                $('#fundsModal input[name="amount"]').val(amount);
                $('#fundsModal select').val(1);
                $('#fundsModal').modal('show');
                $('#fundsModal .modal-body .btn').text("Edit Fund");
            });

            //assets edit
            $('.showAssetsModal').click(function(){
                $('#assetsModal input[name="id"]').val(0);
                $('#assetsModal input[name="amount"]').val("");
                $('#assetsModal input[name="name"]').val();
                $('#assetsModal textarea[name="description"]').text("Say something");
                $('#assetsModal .modal-body .btn').text("Save Asset");
            });

            $('.assetsedit').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                var name = $(this).closest('tr').find('td:nth-child(2)').text();
                var amount = $(this).closest('tr').find('td:nth-child(3)').text();
                var description = $(this).closest('tr').find('td:nth-child(3)').text();
                $('#assetsModal input[name="id"]').val(id);
                $('#assetsModal input[name="amount"]').val(amount);
                $('#assetsModal input[name="name"]').val(name);
                $('#assetsModal').modal('show');
                $('#assetsModal .modal-body .btn').text("Edit Asset");
            });
            $('.edit-prayer').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "/prayers/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.prayer-form input[name="id"]').val(mydata.id);
                        $('.prayer-form input[name="title"]').val(mydata.title);
                        $('.prayer-form textarea[name="description"]').html(mydata.description);
                        $('#prayerModal').modal('show');
                    }else{
                        alert("Invalid request");
                    }
                });
            });
            $('.btn-show-prayer').click(function(){
                $('.prayer-form input[name="id"]').val("0");
                $('.prayer-form input[name="title"]').val("");
                $('.prayer-form textarea[name="description"]').html("");
            });

            $('.btn-edit-pledge').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                var activity = $(this).closest("tr").find("td:nth-child(2) .d-none").text();
                var amount = parseInt($(this).closest("tr").find("td:nth-child(3)").text().replace(',', ''));
                $(".pledges-form input[name='id']").val(id);
                $(".pledges-form select[name='activity'] option[value="+activity+"]").attr("selected", "selected");
                $(".pledges-form input[name='amount']").val(amount);

                $('.pledges-form .btn').html("Edit Pledge");
                $('#pledgesModal').modal("show");
            });

            $('.btn-show-pledges').click(function(){
                $(".pledges-form input[name='id']").val("0");
                $(".pledges-form input[name='amount']").val("");
                $('.pledges-form .btn').html("Add Pledge");
            });



            $('.btn-pledge').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $(".pledge-form input[name='activity']").val(id);
                $('.pledge-form .btn').html("Save Pledge");
                $('#pledgeModal').modal("show");
            });

            //sermon
            $('.btn-add-banner').click(function(){
                $('.sermon-form input[name="banner"]').click();
            });
            $('.sermon-form input[name="banner"]').change(function(){
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-banner').attr('src', e.target.result);
                    $(".banner").removeClass("d-none");
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            $('.btn-add-video').click(function(){
                $('.sermon-form input[name="video"]').click();
            });
            $('.sermon-form input[name="video"]').change(function(){
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-video').attr('src', e.target.result);
                    $(".video").removeClass("d-none");
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            $('.btn-add-audio').click(function(){
                $('.sermon-form input[name="audio"]').click();
            });
            $('.sermon-form input[name="audio"]').change(function(){
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-audio').attr('src', e.target.result);
                    $(".audio").removeClass("d-none");
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            $('.btn-event-banner').click(function(e){
                e.preventDefault();
                $('.event-form input[name="banner"]').click();
            });
            $('.event-form input[name="banner"]').change(function(){
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('#view-banner').attr('src', e.target.result);
                    $(".banner").removeClass("d-none");
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            $('.edit-event').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "/events/"+id,
                    type: "GET",
                    success: function(data){
                        var mydata = $.parseJSON(data);
                        if(mydata != null){
                            $('.event-form input[name="id"]').val(mydata.id);
                            $('.event-form input[name="title"]').val(mydata.title);
                            $('.event-form textarea[name="description"]').html(mydata.description);
                            $('.event-form input[name="date"]').val(mydata.eventdate+" "+mydata.time);
                            $('.event-form input[name="location"]').val(mydata.location);
                            $('.event-form img').attr('src', "/event/"+mydata.banner);
                            $(".event-form .btn-submit-events").html("Edit Event");
                            if(mydata.banner != ""){
                                $('.event-form img').closest('div').removeClass('d-none');
                            }
                            $('#eventsModal').modal('show');
                        }else{
                            alert("Invalid request");
                        }
                    },
                    error: function(data){
                        alert("error");
                    }
                });
            });

            $('.btn-show-event').click(function(){
                $('.event-form input[name="id"]').val("0");
                $('.event-form input[name="title"]').val("");
                $('.event-form textarea[name="description"]').html("");
                $('.event-form input[name="date"]').val("");
                $('.event-form input[name="location"]').val("");
                $('.event-form img').attr('src', "");
                $('.event-form img').closest('div').addClass('d-none');
                $(".event-form .btn-submit-events").html("Add Event");
            });

            $('.view-prayer').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "/prayers/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('#prayerModal .modal-body').html("<strong>Prayer:</strong> "+mydata.title+"<br>");
                        $('#prayerModal .modal-body').append("<strong>Description:</strong> "+mydata.description+"<br>")
                        $('#prayerModal').modal('show');
                    }else{
                        alert("Invalid request");
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });

            $('.edit-notice').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "/notices/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.notices-form input[name="id"]').val(mydata.id);
                        $('.notices-form input[name="title"]').val(mydata.title);
                        $('.notices-form textarea[name="description"]').html(mydata.description);
                        $('.notices-form input[name="date"]').val(mydata.noticedate);
                        $('.btn-submit-notice').html("Edit Notice");
                        $('#noticesModal').modal('show');
                    }else{
                        alert("Invalid request");
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });

            $('.btn-show-notice').click(function(){
                $('.notices-form input[name="id"]').val("0");
                $('.notices-form input[name="title"]').val("");
                $('.notices-form textarea[name="description"]').html("");
                $('.notices-form input[name="date"]').val("");
                $('.btn-submit-notice').html("Save Notice");
            });
            $('.edit-department').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $.ajax({
                    url: "/departments/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.department-form input[name="id"]').val(mydata.id);
                        $('.department-form input[name="name"]').val(mydata.name);
                        $('.department-form textarea[name="description"]').html(mydata.description);
                        $('.department-form input[name="contact"]').val(mydata.contact);
                        $('.department-form select[name="user_id"]').val(mydata.leader);
                        $('.btn-submit-departments').html("Update Department");
                        $('#departmentModal').modal('show');
                    }else{
                        alert("Invalid request");
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });

            $('.btn-show-department').click(function(){
                $('.department-form input[name="id"]').val("");
                $('.department-form input[name="name"]').val("");
                $('.department-form textarea[name="description"]').html("");
                $('.department-form input[name="contact"]').val("");
                $('.btn-submit-departments').html("Save Department");
            });
            //cropping function
            $('#profileModal').on('shown.bs.modal', function (e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var parentW = $('#upload-profile').closest('.col-sm-12').width();
                var vwidth = parentW;
                var vheight = vwidth;

                $uploadCrop = $('#upload-profile').croppie({
                    enableExif: true,
                    viewport: {
                        width: vwidth*.9,
                        height: vheight*.9,
                        type: 'canvas'
                    },

                    boundary: {
                        width: parentW,
                        height: (parentW)*4/4
                    }
                });

                $('.btn-add-profile').click(function(){
                    $('#upload').click();
                });

                $('#upload').on('change', function () {
                    $('.upload-result').removeAttr('disabled');
                    $('#upload-profile').removeClass('d-none');
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $uploadCrop.croppie('bind', {
                            url: e.target.result
                        }).then(function(){
                            console.log('jQuery bind complete');
                        });
                    }
                    reader.readAsDataURL(this.files[0]);
                });

                $('.upload-result').on('click', function (ev) {
                    $uploadCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {
                        $('.feedback').html("<p class='text-center text-info'><i class='fas fa-spinner fa-pulse'></i> Saving... Please wait</p>");
                        $.ajax({
                            url: "/profileimage",
                            type: "POST",
                            data: {"image":resp},
                            success: function (data) {
                                try {
                                    json = $.parseJSON(data);
                                } catch (e) {
                                    // not json
                                }
                                $('.feedback').html(data.success);
                                window.location.reload();
                            },
                            error: function(){
                                $('.feedback').html("<p class='text-center text-danger'><i class='fas fa-warning'></i> Unable to save image</p>");
                            }
                        });
                    });
                });
            });


            //article photo
            $('.articlephoto').click(function(e){
                e.preventDefault();
                $(".article-form input[type='file']").click();
            });
            $('.article-form input[type="file"]').change(function(){
                var input = $(this);
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('.article-form img').attr('src', e.target.result);
                }

                reader.readAsDataURL(input[0].files[0]);
            });

            //activities

            $('.btn-show-activity').click(function(){
                $('.activity-form input[name="id"]').val("0");
                $('.activity-form input[name="name"]').val("");
                $('.activity-form input[name="amount"]').val("");
                $('.activity-form input[name="date"]').val("");
                $('.activity-form textarea[name="description"]').val("Say something");

                $('.btn-submit-activity').removeAttr('disabled');
                $('.btn-submit-activity').html("Add Activity");
            });
            $('.btn-edit-activity').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $('.btn-submit-activity').attr('disabled', 'disabled');
                $('.btn-submit-activity').html('<i class="fas fa-spinner fa-pulse"></i> Loading...');
                $('#activityModal').modal('show');
                $.ajax({
                    url: "/getactivity/"+id,
                    type: "GET",
                    success: function(data){
                        var mydata = $.parseJSON(data);
                        if(mydata != null){
                            $('.activity-form input[name="id"]').val(mydata.id);
                            $('.activity-form input[name="name"]').val(mydata.name);
                            $('.activity-form input[name="amount"]').val(mydata.amount);
                            $('.activity-form input[name="date"]').val(mydata.closes_on);
                            $('.activity-form textarea[name="description"]').val(mydata.description);
                            $('.btn-submit-activity').removeAttr('disabled');
                            $('.btn-submit-activity').html("Edit Activity");
                        }else{
                            alert("invalid request!")
                        }
                    },
                    error: function(data){
                        alert("error");
                    }
                });
            });

            $('.edit-role').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $('#roleModal').modal('show');
                $('.btn-submit-role').attr('disabled', 'disabled');
                $('.btn-submit-role').html('<i class="fas fa-spinner fa-pulse"></i> Please Wait');

                $.ajax({
                    url: "/userroles/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.role-form input[name="id"]').val(mydata.id);
                        $('.role-form input[name="name"]').val(mydata.name);
                        $('.btn-submit-role').html("Edit Role");
                        $('.btn-submit-role').removeAttr('disabled');

                    }else{
                        alert("Invalid request");
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });

            $('.btn-show-role').click(function(){
                $('.role-form input[name="id"]').val("0");
                $('.role-form input[name="name"]').val("");
                $('.btn-role-departments').html("Save Role");
            });

            $('.edit-permissions').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $(".permission-form input[name='id']").val(id);
                $('#permissionModal').modal('show');
                $('#permissionModal .btn-submit-permissions').attr('disabled', 'disabled');
                $('#permissionModal .btn-submit-permissions').html('<i class="fas fa-spinner fa-pulse"></i> Loading... Please wait');
                $.ajax({
                    url: "/permissions/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.permission-form input[name="id"]').val(mydata.role);
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
                        if(mydata.sermons > 0){
                            $('#sermonc').prop("checked", true);
                            if(mydata.sermons == 1){
                                $('#sermonr').prop("checked", true);
                            }else if(mydata.sermons == 2){
                                $('#sermonr2').prop("checked", true);
                            }else{
                                $('#sermonr3').prop("checked", true);
                            }
                        }else{
                            $('#sermonc').prop("checked", true);
                            $('#sermonc').prop("checked", false);
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
                        if(mydata.prayers > 0){
                            $('#prayerc').prop("checked", true);
                            if(mydata.prayers == 1){
                                $('#prayerr').prop("checked", true);
                            }else if(mydata.prayers == 2){
                                $('#prayerr2').prop("checked", true);
                            }else{
                                $('#prayerr3').prop("checked", true);
                            }
                        }else{
                            $('#prayerc').prop("checked", true);
                            $('#prayerc').prop("checked", false);
                        }
                        if(mydata.notices > 0){
                            $('#noticec').prop("checked", true);
                            if(mydata.notices == 1){
                                $('#noticer').prop("checked", true);
                            }else if(mydata.notices == 2){
                                $('#noticer2').prop("checked", true);
                            }else{
                                $('#noticer3').prop("checked", true);
                            }
                        }else{
                            $('#noticec').prop("checked", true);
                            $('#noticec').prop("checked", false);
                        }
                        if(mydata.departments > 0){
                            $('#departmentc').prop("checked", true);
                            if(mydata.departments == 1){
                                $('#departmentr').prop("checked", true);
                            }else if(mydata.notices == 2){
                                $('#department2').prop("checked", true);
                            }else{
                                $('#departmentr3').prop("checked", true);
                            }
                        }else{
                            $('#departmentc').prop("checked", true);
                            $('#departmentc').prop("checked", false);
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
                        $('.btn-submit-permissions').html("Add Permissions");
                        $('.btn-submit-permissions').removeAttr('disabled');
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });

            $('.user-permissions').click(function(e){
                e.preventDefault();
                var id = $(this).attr('href');
                $(".permission-form input[name='id']").val(id);
                $('#permissionModal').modal('show');
                $('#permissionModal .btn-submit-permissions').attr('disabled', 'disabled');
                $('#permissionModal .btn-submit-permissions').html('<i class="fas fa-spinner fa-pulse"></i> Loading... Please wait');
                $.ajax({
                    url: "/permissions/users/"+id,
                    type: "get"
                }).done(function(data){
                    var mydata = $.parseJSON(data);
                    if(mydata != null){
                        $('.permission-form input[name="id"]').val(mydata.user_id);
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
                        if(mydata.sermons > 0){
                            $('#sermonc').prop("checked", true);
                            if(mydata.sermons == 1){
                                $('#sermonr').prop("checked", true);
                            }else if(mydata.sermons == 2){
                                $('#sermonr2').prop("checked", true);
                            }else{
                                $('#sermonr3').prop("checked", true);
                            }
                        }else{
                            $('#sermonc').prop("checked", true);
                            $('#sermonc').prop("checked", false);
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
                        if(mydata.prayers > 0){
                            $('#prayerc').prop("checked", true);
                            if(mydata.prayers == 1){
                                $('#prayerr').prop("checked", true);
                            }else if(mydata.prayers == 2){
                                $('#prayerr2').prop("checked", true);
                            }else{
                                $('#prayerr3').prop("checked", true);
                            }
                        }else{
                            $('#prayerc').prop("checked", true);
                            $('#prayerc').prop("checked", false);
                        }
                        if(mydata.notices > 0){
                            $('#noticec').prop("checked", true);
                            if(mydata.notices == 1){
                                $('#noticer').prop("checked", true);
                            }else if(mydata.notices == 2){
                                $('#noticer2').prop("checked", true);
                            }else{
                                $('#noticer3').prop("checked", true);
                            }
                        }else{
                            $('#noticec').prop("checked", true);
                            $('#noticec').prop("checked", false);
                        }
                        if(mydata.departments > 0){
                            $('#departmentc').prop("checked", true);
                            if(mydata.departments == 1){
                                $('#departmentr').prop("checked", true);
                            }else if(mydata.notices == 2){
                                $('#department2').prop("checked", true);
                            }else{
                                $('#departmentr3').prop("checked", true);
                            }
                        }else{
                            $('#departmentc').prop("checked", true);
                            $('#departmentc').prop("checked", false);
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
                        $('.btn-submit-permissions').html("Add Permissions");
                        $('.btn-submit-permissions').removeAttr('disabled');
                    }
                }).fail(function(data){
                    alert("failed");
                });
            });
          });
        </script>
</body>
</html>
