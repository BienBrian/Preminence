@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                <i class="bi bi-lock-fill text-warning" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-5 mb-3">Feature Not Available</h1>
            <p class="lead text-muted mb-4">
                The <strong>{{ $label }}</strong> feature is not included in your current plan.
            </p>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Want to access this feature?</h5>
                    <p class="text-muted">You have two options:</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-primary">
                                <div class="card-body">
                                    <i class="bi bi-arrow-up-circle text-primary fs-2 mb-3"></i>
                                    <h6>Upgrade Your Plan</h6>
                                    <p class="small text-muted">Upgrade to a higher plan that includes {{ $label }} and other premium features.</p>
                                    <a href="{{ route('billing.upgrade') }}" class="btn btn-primary btn-sm">
                                        Upgrade Now
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-success">
                                <div class="card-body">
                                    <i class="bi bi-envelope text-success fs-2 mb-3"></i>
                                    <h6>Request Access</h6>
                                    <p class="small text-muted">Contact your administrator to request access to this module.</p>
                                    <a href="mailto:{{ $site_settings->support_email ?? 'support@'.config('pisti.platform_domain', 'example.com') }}?subject=Module Access Request: {{ $label }}&body=I would like to request access to the {{ $label }} module for our church.\"" class="btn btn-success btn-sm">
                                        Request Access
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('marketplace.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-shop"></i> Browse Marketplace
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-house"></i> Go to Dashboard
                </a>
            </div>
            
            <p class="mt-4 text-muted small">
                Questions? <a href="mailto:{{ $site_settings->support_email ?? 'support@'.config('pisti.platform_domain', 'example.com') }}">Contact our support team</a>
            </p>
        </div>
    </div>
</div>
@endsection
