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

                <!-- Navigation -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('home')}}">
                            <i class="ni ni-tv-2 text-primary"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('guests/seminars')}}">
                            <i class="fas fa-calendar-alt text-blue"></i> Seminars
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('guests/events')}}">
                            <i class="fas fa-bell text-orange"></i> Events
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/guests/profile')}}">
                            <i class="fas fa-user text-red"></i> Profile
                        </a>
                    </li>

                </ul>
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
                    <p class='text-white text-center'><i class='fas fa-check-circle'></i> {!! \Session::get('success') !!}</p>
                </div>
            @endif
            @if (\Session::has('error'))
                <div class="notifications bg-danger shadow">
                    <p class='text-white text-center'><i class='fas fa-exclamation-circle'></i> {!! \Session::get('error') !!}</p>
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
          });
        </script>
</body>
</html>
