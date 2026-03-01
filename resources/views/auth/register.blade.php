@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center background d-flex align-items-center">
            <div class="col-md-8 col-md-6 col-lg-5 mt-5 ">
                <div class="card border bg-white border-0 mb-4 shadow-lg">
                    <div class="card-header p-3 br-white">
                        <h3 class='bold'><i class="fa-sharp fa-regular fa-user text-muted"></i> {{ __('Register') }}</h3>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('register') }}" class="row" id='registerForm'>
                            @csrf
                            <div class="col-sm-6 mb-3">
                                <label for="firstname">{{ __('First Name') }}</label>
                                <div>
                                    <input id="firstname" type="text"
                                        class="form-control @error('firstname') is-invalid @enderror" name="firstname"
                                        value="{{ old('firstname') }}" placeholder='First Name' required autocomplete="given-name"
                                        autofocus>
                                    @error('firstname')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="lastname">{{ __('Last Name') }}</label>
                                <div>
                                    <input id="lastname" type="text"
                                        class="form-control @error('lastname') is-invalid @enderror" name="lastname"
                                        value="{{ old('lastname') }}" placeholder='Last Name' required autocomplete="family-name">
                                    @error('lastname')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="phone">{{ __('Phone Number') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+{{ $site_settings->phone_code ?? '254' }}</span>
                                    </div>
                                    <input id="phone" type="text"
                                        class="form-control @error('phone') is-invalid @enderror" name="phone"
                                        value="{{ old('phone') }}" placeholder='712345678' required
                                        autocomplete="tel">
                                    @error('phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="email">{{ __('Email Address') }} <small class="text-muted">(Optional)</small></label>
                                <div>
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" placeholder='Email Address'
                                        autocomplete="email">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="password">{{ __('Password') }}</label>
                                <div>
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required placeholder='Password' autocomplete="new-password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-sm-6 mb-3">
                                <label for="password-confirm">{{ __('Confirm Password') }}</label>
                                <div>
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" placeholder='Confirm Password' required
                                        autocomplete="new-password">
                                </div>
                            </div>

                            <div class='col-sm-12 mb-3'>
                                <div class="form-check">
                                    <input class="form-check-input @error('terms_and_conditions') is-invalid @enderror"
                                        type="checkbox" value="1" id="flexCheckDefault"
                                        name="terms_and_conditions" {{ old('terms_and_conditions') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="flexCheckDefault">
                                        I Accept <a href='{{ url('terms_and_conditions') }}'
                                            style="text-decoration: none;"><b>Terms and Conditions</b></a> and <a
                                            href='{{ url('privacy_policy') }}' style="text-decoration: none;"><b>Privacy
                                                Policy</b></a>
                                    </label>
                                    @error('terms_and_conditions')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            @if(($site_settings->recaptcha_enabled ?? false) && ($site_settings->recaptcha_site_key ?? ''))
                            <div class="form-group mb-4">
                                @error('g-recaptcha-response')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-sm-12 mb-1">
                                <button data-sitekey="{{ $site_settings->recaptcha_site_key }}"
                                    data-callback='onSubmit' data-action='submit'
                                    class="g-recaptcha btn btn-primary w-100">
                                    <i class="fa-solid fa-pencil"></i> {{ __('Register') }}
                                </button>
                            </div>
                            @else
                            <div class="col-sm-12 mb-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa-solid fa-pencil"></i> {{ __('Register') }}
                                </button>
                            </div>
                            @endif
                            <div class="col-sm-12 mt-2">
                                <a class="btn btn-link nav-link text-primary" href="{{ route('login') }}">
                                    {{ __('Already Registered? Login here') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        toastr.options.closeButton = true;
        toastr.options.closeMethod = 'fadeOut';
        toastr.options.closeEasing = 'swing';
    </script>
    @foreach ($errors->all() as $error)
        <script>
            toastr.error('{{ $error }}');
        </script>
    @endforeach
    @if(($site_settings->recaptcha_enabled ?? false) && ($site_settings->recaptcha_site_key ?? ''))
    <script>
        function onSubmit(token) {
            document.getElementById("registerForm").submit();
        }
    </script>
    @endif
@endpush
