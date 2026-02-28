@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center background d-flex align-items-center">
            <div class="col-sm-10 col-md-5 col-lg-4">
                <div class='m-3'>
                    <div class="card border shadow-lg bg-white border-0">
                        <div class='card-header bg-white pt-3'>
                            <h4 class='text-muted bold'><i class="fas fa-lock"></i> Change <span class='text-primary' style='font-weight:300'>Password</span></h4>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-3">You must set a new password before continuing.</p>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ url('password/force-change') }}">
                                @csrf

                                <div class="mb-3 text-start">
                                    <label for="current_password">Current Password</label>
                                    <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required autofocus placeholder="Enter current password">
                                </div>

                                <div class="mb-3 text-start">
                                    <label for="password">New Password</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required placeholder="Min. 12 characters, mixed case + number">
                                </div>

                                <div class="mb-3 text-start">
                                    <label for="password-confirm">Confirm New Password</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="Confirm password">
                                </div>

                                <div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        Set New Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
