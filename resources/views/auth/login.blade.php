@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center background d-flex align-items-center">
            <div class="col-sm-10 col-md-5 col-lg-4">
                <div class='m-3'>

                    <div class="card border shadow-lg bg-white border-0">
                        <div class='card-header bg-white pt-3'>
                            <h4 class='text-muted bold'><i class="fa-solid fa-right-to-bracket"></i> Log<span class='text-primary'
                                    style='font-weight:300'>in</span></h4>
                        </div>
                        <div class="card-body p-4">
                            <!--<div class='ps-4 pe-4 text-center'>
                                    <img src='{{ asset('images/logos/Primary Logo.png') }}' class="img-fluid" />
                                </div>-->
                            <!--
                                <h4 class='text-muted'><i class="fa-solid fa-right-to-bracket"></i> Log<span class='text-primary'
                                            style='font-weight:300'>in</span></h4>-->
                            <form method="POST" action="{{ route('login') }}" id='loginForm'>

                                @csrf
                                <div class="mb-3 text-start">
                                    <label for="email">{{ __('Username/Email Address') }}</label>
                                    <div>
                                        <input id="email" type="text"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" placeholder='Username/Email Address' required
                                            autocomplete="email" autofocus>

                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3 text-start">
                                    <label for="password">{{ __('Password') }}</label>

                                    <div class="">
                                        <input id="password" type="password"
                                            class="form-control @error('password') is-invalid @enderror" name="password"
                                            placeholder='password' required autocomplete="current-password">

                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                                {{ old('remember') ? 'checked' : '' }}>

                                            <label class="form-check-label" for="remember">
                                                {{ __('Remember Me') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if (Route::has('password.request'))
                                            <a class="btn btn-link nav-link text-primary"
                                                href="{{ route('password.request') }}">
                                                {{ __('Forgot Password?') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>


                                <div class="">
                                    <button data-sitekey="{{ config('services.recaptcha.site_key') }}"
                                        data-callback='onSubmit' data-action='submit'
                                        class="g-recaptcha btn btn-primary w-100">
                                        {{ __('Login') }}
                                    </button>
                                </div>
                                <!--
                                            <div class='row d-flex align-items-center'>
                                                <div class='col'>
                                                    <hr>
                                                </div>
                                                <div class='col-2 text-center'>
                                                    OR
                                                </div>
                                                <div class='col'>
                                                    <hr>
                                                </div>
                                            </div>
                                            -->
                            </form>
                        </div>
                    </div>
                    <div class='card mt-3 border-0 shadow-lg'>
                        <div class='card-body'>
                            <div class="mt-2">
                                <a href="{{ url('register') }}" class="btn btn-outline-primary w-100 shadow-sm">
                                    <i class='fas fa-pencil'></i> {{ __('Create a New Account') }}
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        function onSubmit(token) {
            document.getElementById("loginForm").submit();
        }
    </script>
@endpush
