@extends('superadmin.layouts.app')

@section('title', 'SuperAdmin Login')

@section('content')
<div class="login-page">
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock-fill text-primary" style="font-size: 4rem;"></i>
            <h2 class="mt-3 mb-1">Platform Admin</h2>
            <p class="text-muted">Sign in to manage Pisti</p>
        </div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('superadmin.login') }}">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="admin@pisti.co.ke"
                           required 
                           autofocus>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password"
                           placeholder="••••••••"
                           required>
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </div>
        </form>
        
        <hr class="my-4">
        
        <div class="text-center text-muted small">
            <p class="mb-0"><i class="bi bi-info-circle"></i> This is for platform administrators only.</p>
            <p class="mb-0">Church members should use the regular <a href="/login">login page</a>.</p>
        </div>
    </div>
</div>
@endsection
