<?php $settings = \DB::table("settings")->first(); ?>
<?php $verse = \DB::table('weeklyverse')->orderBy('id', 'desc')->first(); ?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{ $settings == null?"favicon.ico":asset('website/'.$settings->favicon) }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords" content="church, church management, church CRM, church events, church funds, church articles">
    <meta name="description" content="This is a church management system with both frontend and backend. It helps the church manage members, 
    other people and news from main and branch churches">


    <title>{{ $settings == null?"Church App":$settings->name }}</title>

    <!-- Scripts -->
    <!--<script src="{{ asset('js/app.js') }}" defer></script>-->

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">

    <!-- Styles -->
    <!--<link href="{{ asset('css/app.css') }}" rel="stylesheet">--><!-- Icons -->
    <link href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
    <!-- Argon CSS -->
    <link type="text/css" href="{{asset('assets/css/argon.css?v=1.0.0')}}" rel="stylesheet">
    <!-- Custom style -->
    <link type="text/css" href="{{asset('css/styles.css')}}" rel="stylesheet">
    <link type="text/css" href="{{ $settings == null?'':asset('css/'.$settings->theme) }}" rel="stylesheet">
    <!--<script src='https://www.google.com/recaptcha/api.js'></script>-->
</head>
<body>
    <div class="main-content">
        <!--Top navbar -->
        <div class="main-menu-contacts-container bg-secondary d-flex justify-content-end">
            <table>
                <tr>
                    <td style="vertical-align:middle; border-right: 1px solid rgb(200,200,200);">
                        <small><a class="nav-link nav-link-icon font-weight-bold" href="{{url('login')}}" style="/*border-right: 2px solid rgba(0,0,0,.5);*/">
                            <span class="nav-link-inner--text"><i class="fa fa-sign-in-alt"></i> Login</span>
                        </a></small>
                    </td>
                    <td style="padding-right: .6em;">
                        <small><a class="nav-link nav-link-icon font-weight-bold" href="{{url('/register')}}">
                            <span class="nav-link-inner--text"><i class='fas fa-user-plus'></i> Register</span>
                        </a></small>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Navbar -->
        <nav class="navbar fixed-top navbar-top navbar-horizontal navbar-expand-md navbar-dark">
            <div class="container-fluid px-4">
                <a class="navbar-brand text-white" href="{{ url('/')}}">
                    <img  src="{{ $settings == null?asset('website/icon.png'):asset('website/'.$settings->icon) }}"> &nbsp;{{ $settings == null?"CHURCH APP":$settings->name}}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse-main" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbar-collapse-main">
                    <!-- Collapse header -->
                    <div class="navbar-collapse-header d-md-none">
                        <div class="row">
                            <div class="col-6 collapse-brand">
                                <a href="{{url('/')}}">
                                    <img  src="{{ $settings == null?asset('website/icon.png'):asset('website/'.$settings->icon) }}"> &nbsp;{{ $settings == null?"CHURCH APP":$settings->name}}
                                </a>
                            </div>
                            <div class="col-6 collapse-close">
                                <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbar-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                                <span></span>
                                <span></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Navbar items -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('/')}}">
                                <span class="nav-link-inner--text">Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('people')}}">
                                <span class="nav-link-inner--text">People</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('/noticeboard')}}">
                                <span class="nav-link-inner--text">Notices</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('/events_seminars')}}">
                                <span class="nav-link-inner--text">Events</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('/spiritual')}}">
                                <span class="nav-link-inner--text">Spiritual</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('/our_gallery')}}">
                                <span class="nav-link-inner--text">Gallery</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('/our_articles')}}">
                                <span class="nav-link-inner--text">Articles</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('/shop')}}">
                                <span class="nav-link-inner--text">Shop</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="#" data-toggle="modal" data-target="#donate">
                                <span class="nav-link-inner--text">Donate</span>
                            </a>
                        </li><!--
                        <li class="nav-item">
                            <a class="nav-link nav-link-icon" href="{{url('login')}}">
                                <span class="nav-link-inner--text">Login</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn bg-indigo mt-1 text-light" style='border-radius:100px;' href="{{url('/register')}}">
                                <span class="nav-link-inner--text"><i class='fas fa-user-plus'></i> Register</span>
                            </a>
                        </li>-->
                    </ul>
                </div>
            </div>
        </nav>
        @yield('content')
    </div>

    <div class="container-fluid pt-7 bg-dark">
        <div class="container">
            <div class="row">
                <div class="col-sm-4 text-white">
                    <h4 class="text-light">Verse of the Day</h4>
                    {!! $verse == null?"Unavailable":html_entity_decode($verse->description) !!}</p>
                    <p class="text-right text-muted">{{ $verse == null?"Unavailable":$verse->verse }}<br>
                        <span class="text-right mt-0 font-weight-bold text-muted">{{ $verse == null?"":$verse->version }}</span>
                    </p>
                </div>
                <div class="col-sm-4 col-6">
                    <h4 class="text-light">Church</h4>
                    <a href="#" class="text-muted font-weight-bold">About Us</a><br>
                    <a href="#" class="text-muted font-weight-bold">Contact Us</a><br>
                    <a href="#" class="text-muted font-weight-bold">Sign In</a><br>
                    <a href="#" class="text-muted font-weight-bold">Register</a><br>
                    <a href="#" class="text-muted font-weight-bold">Terms of Use</a><br>
                    <a href="#" class="text-muted font-weight-bold">Privacy Policy</a><br>
                </div>
                <div class="col-sm-4 col-6">
                    <h4 class="text-light">Quick Links</h4>
                    <a href="#" class="text-muted font-weight-bold">People</a><br>
                    <a href="#" class="text-muted font-weight-bold">Notice Board</a><br>
                    <a href="#" class="text-muted font-weight-bold">Spiritual</a><br>
                    <a href="#" class="text-muted font-weight-bold">Articles</a><br>
                    <a href="#" class="text-muted font-weight-bold">Shop</a><br>
                    <a href="#" class="text-muted font-weight-bold">Gallery</a><br>
                </div>
                <div class="col-sm-12 text-right p-2 font-weight-bold text-light">
                    &copy;{{date('Y')}}
                </div>
            </div>
        </div>
    </div>

    <!-- Donate Modal -->
    <div class="modal fade" id="donate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="exampleModalLabel">Donate Now</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{url('api/stk/push')}}" class="row">
                        <div class='col-sm-6 mt-1'>
                            <label>First Name</label>
                            <input type='text' name="firstname" class="form-control" placeholder="First Name" required>
                        </div>
                        <div class='col-sm-6 mt-1'>
                            <label>Last Name</label>
                            <input type='text' name="lastname" class="form-control" placeholder="Last Name" required>
                        </div>
                        <div class='col-sm-12 mt-2'>
                            <label>Phone Number</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+254</span>
                                </div>
                                <input type='number' name="phone" class="form-control" placeholder="eg 079116...." required>
                            </div>
                        </div>
                        <div class='col-sm-6 mt-2'>
                            <label class='mt-1'>Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">KSH</span>
                                </div>
                                <input type='number' name="amount" class="form-control" placeholder="Enter Amount" required>
                            </div>
                        </div>
                        <div class='col-sm-6 mt-2'>
                            <label class='mt-1'>Account Name</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"></span>
                                </div>
                                <input name="account" class="form-control" placeholder="Account Name" value="Donation">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top">
                    <span class='d-none feedback small text-muted'><i class='fas fa-spinner fa-pulse'></i> Sending request to phone...</span>
                    <button type="button" class="btn btn-primary btn-donate">Donate</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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
    <!-- Argon Scripts -->
    <!-- Core -->
    <script src="{{asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>

    <!-- Argon JS -->
    <script src="{{asset('assets/js/argon.js?v=1.0.0')}}"></script>
    <script>
        $(document).ready(function(){
            $('#donate .btn-donate').click(function(){
                $('#donate form').submit();
            });

            $("#donate form").submit(function(e){
                e.preventDefault();
                $('#donate .btn-donate').attr('disabled', 'disabled');
                $('#donate .feedback').removeClass('d-none');
                $('#donate .feedback').removeClass('text-success');
                $('#donate .feedback').removeClass('text-danger');
                $('#donate .feedback').addClass('text-muted');
                $('#donate .feedback').html("<i class='fas fa-spinner fa-pulse'></i> Sending request to phone...");
                var formData = $(this).serialize();
                $.ajax({
                    url: "{{url('/api/stk/push')}}",
                    data: formData,
                    type: "POST",
                }).done(function(data){
                    $('#donate .feedback').removeClass('text-muted');
                    $('#donate .feedback').addClass('text-success');
                    $('#donate .feedback').html("<i class='fas fa-check'></i> <strong>Request successful.</strong> Thank You for your donation");
                    $('#donate .btn-donate').removeAttr('disabled');
                }).fail(function(data){
                    $('#donate .feedback').removeClass('text-muted');
                    $('#donate .feedback').addClass('text-danger');
                    $('#donate .feedback').html("<i class='fas fa-exclamation-circle'></i> <strong>Somethine went wrong</strong> Make sure you have filled all the fields before proceeding");
                    $('#donate .btn-donate').removeAttr('disabled');
                });
            });
            $('form .seminars').addClass("d-none");
            $('form select[name="registration"]').val(0);

            $('form select[name="registration"]').change(function(){
                var id = parseInt($(this).val());
                if(id > 0){
                    $('form .seminars').removeClass("d-none");
                    $('form .events').addClass("d-none");
                }else{
                    $('form .seminars').addClass("d-none");
                    $('form .events').removeClass("d-none");
                }
            });
            setTimeout(function(){
                $(".notifications").fadeOut();
            }, 4000);
        });
    </script>
    {!! NoCaptcha::renderJs() !!}
</body>
</html>
