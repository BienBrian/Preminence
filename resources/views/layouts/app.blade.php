@php
    $settings = \DB::table("settings")->first();
    $verse = \DB::table('weeklyverse')->orderBy('id', 'desc')->first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{ $settings == null ? 'favicon.ico' : asset('website/'.$settings->favicon) }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords" content="church, church management, church CRM, church events, church funds, church articles">
    <meta name="description" content="This is a church management system with both frontend and backend.">

    <title>{{ $settings == null ? "Church App" : $settings->name }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600,700,800,900&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{asset('assets/vendor/nucleo/css/nucleo.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css')}}" rel="stylesheet">
    <link type="text/css" href="{{asset('assets/css/argon.css?v=1.0.0')}}" rel="stylesheet">
    <link type="text/css" href="{{asset('css/styles.css')}}" rel="stylesheet">
    <link type="text/css" href="{{ $settings == null ? '' : asset('css/'.$settings->theme) }}" rel="stylesheet">
    @stack('css')
</head>
<body>

    <!-- Topbar -->
    <div class="site-topbar d-none d-lg-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                @if($settings && $settings->specific_location)
                    <i class="fas fa-map-marker-alt"></i> {{ $settings->specific_location }}@if($settings->city), {{ $settings->city }}@endif
                @endif
            </div>
            <div>
                @auth
                    <a href="{{ url('/dashboard/home') }}" class="mr-3"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                @else
                    <a href="{{ url('login') }}" class="mr-3"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="{{ url('/register') }}"><i class="fas fa-user-plus"></i> Register</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Mobile Auth Links (visible only on mobile/tablet) -->
    @guest
    <div class="d-lg-none mobile-auth-bar">
        <div class="container py-2 text-right">
            <a href="{{ url('login') }}" class="btn btn-sm btn-outline-light mr-2"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="{{ url('/register') }}" class="btn btn-sm btn-primary"><i class="fas fa-user-plus"></i> Register</a>
        </div>
    </div>
    @endguest

    <!-- Navbar -->
    <nav class="navbar navbar-site navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ $settings == null ? asset('website/icon.png') : asset('website/'.$settings->icon) }}">
                {{ $settings == null ? "CHURCH APP" : $settings->name }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-main" aria-controls="navbar-main" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbar-main">
                <button type="button" class="nav-close-btn d-lg-none" data-toggle="collapse" data-target="#navbar-main" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
                <ul class="navbar-nav ml-auto align-items-lg-center">

                    <li class="nav-item"><a class="nav-link" href="{{ url('people') }}">People</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/noticeboard') }}">Notices</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/events_seminars') }}">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/spiritual') }}">Spiritual</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/our_gallery') }}">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/our_articles') }}">Articles</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/prayer-wall') }}">Prayer Wall</a></li>

                    <li class="nav-item ml-lg-2">
                        <a class="nav-link btn-donate-nav" href="#" data-toggle="modal" data-target="#donate">
                            <i class="fas fa-heart mr-1"></i> Donate
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="row">
                <!-- Brand + Verse -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-brand">
                        <img src="{{ $settings == null ? asset('website/icon.png') : asset('website/'.$settings->icon) }}" alt="">
                        <p class="mt-2">{{ $settings->slogan ?? '' }}</p>
                    </div>
                    @if($verse)
                        <div class="footer-verse">
                            {!! strip_tags($verse->description, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><blockquote>') !!}
                            <div class="mt-1" style="color:rgba(255,255,255,.4); font-size:.78rem;">
                                &mdash; {{ $verse->verse }} {{ $verse->version ?? '' }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Navigate -->
                <div class="col-lg-2 col-md-3 col-6 mb-4">
                    <h5>Navigate</h5>
                    <ul class="footer-links">

                        <li><a href="{{ url('people') }}">People</a></li>
                        <li><a href="{{ url('/noticeboard') }}">Notices</a></li>
                        <li><a href="{{ url('/events_seminars') }}">Events</a></li>
                        <li><a href="{{ url('/our_gallery') }}">Gallery</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="col-lg-2 col-md-3 col-6 mb-4">
                    <h5>Resources</h5>
                    <ul class="footer-links">
                        <li><a href="{{ url('/spiritual') }}">Spiritual</a></li>
                        <li><a href="{{ url('/prayer-wall') }}">Prayer Wall</a></li>
                        <li><a href="{{ url('/our_articles') }}">Articles</a></li>

                        @auth
                            <li><a href="{{ url('/dashboard/home') }}">Dashboard</a></li>
                        @else
                            <li><a href="{{ url('login') }}">Sign In</a></li>
                            <li><a href="{{ url('/register') }}">Register</a></li>
                        @endauth
                    </ul>
                </div>

                <!-- Contact + Social -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5>Contact</h5>
                    @if($settings && $settings->specific_location)
                        <p style="font-size:.9rem;"><i class="fas fa-map-marker-alt mr-1"></i> {{ $settings->specific_location }}@if($settings->city), {{ $settings->city }}@endif</p>
                    @endif
                    <div class="footer-social">
                        @if($settings && $settings->facebook)
                            <a href="{{ $settings->facebook }}" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if($settings && $settings->twitter)
                            <a href="{{ $settings->twitter }}" target="_blank"><i class="fab fa-twitter"></i></a>
                        @endif
                        @if($settings && $settings->youtube)
                            <a href="{{ $settings->youtube }}" target="_blank"><i class="fab fa-youtube"></i></a>
                        @endif
                        @if($settings && $settings->instagram)
                            <a href="{{ $settings->instagram }}" target="_blank"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if($settings && $settings->whatsapp)
                            <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        @endif
                        @if($settings && $settings->linkedin)
                            <a href="{{ $settings->linkedin }}" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="footer-bottom text-center">
                &copy; {{ date('Y') }} {{ $settings->name ?? 'Church App' }}. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Donate Modal -->
    <div class="modal fade" id="donate" tabindex="-1" role="dialog" aria-labelledby="donateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title" id="donateModalLabel">Donate Now</h4>
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
                                    <span class="input-group-text">+{{ $settings->phone_code ?? '254' }}</span>
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
                    <br><i class='fas fa-angle-right'></i> {{ $error }}
                @endforeach
            </p>
        </div>
    @endif

    <!-- Scripts -->
    <script src="{{asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{asset('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/js/argon.js?v=1.0.0')}}"></script>
    <script>
        $(document).ready(function(){
            // Navbar scroll effect
            $(window).on('scroll', function(){
                if ($(this).scrollTop() > 80) {
                    $('.navbar-site').addClass('scrolled');
                } else {
                    $('.navbar-site').removeClass('scrolled');
                }
            });

            // Donate modal
            $('#donate .btn-donate').click(function(){
                $('#donate form').submit();
            });

            $("#donate form").submit(function(e){
                e.preventDefault();
                $('#donate .btn-donate').attr('disabled', 'disabled');
                $('#donate .feedback').removeClass('d-none text-success text-danger').addClass('text-muted');
                $('#donate .feedback').html("<i class='fas fa-spinner fa-pulse'></i> Sending request to phone...");
                var formData = $(this).serialize();
                $.ajax({
                    url: "{{url('/api/stk/push')}}",
                    data: formData,
                    type: "POST",
                }).done(function(data){
                    $('#donate .feedback').removeClass('text-muted').addClass('text-success');
                    $('#donate .feedback').html("<i class='fas fa-check'></i> <strong>Request successful.</strong> Thank You for your donation");
                    $('#donate .btn-donate').removeAttr('disabled');
                }).fail(function(data){
                    $('#donate .feedback').removeClass('text-muted').addClass('text-danger');
                    $('#donate .feedback').html("<i class='fas fa-exclamation-circle'></i> <strong>Something went wrong.</strong> Make sure you have filled all the fields before proceeding");
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
    @if(($settings->recaptcha_enabled ?? false) && ($settings->recaptcha_site_key ?? ''))
    <script src="https://www.google.com/recaptcha/api.js?render={{ $settings->recaptcha_site_key }}"></script>
    @endif
    @stack('js')
</body>
</html>
