@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                <i class="bi bi-lock-fill text-warning" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-5 mb-3">Feature Locked</h1>
            <p class="lead text-muted mb-4">
                The <strong>{{ $label }}</strong> feature is not included in your current plan.
                Upgrade your subscription to unlock this and other powerful features.
            </p>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">What's included in higher plans?</h5>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Advanced {{ $label }} features</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Priority support</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> More users and storage</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success"></i> Additional integrations</li>
                    </ul>
                </div>
            </div>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('billing.upgrade') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-up-circle"></i> Upgrade Now
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-house"></i> Go to Dashboard
                </a>
            </div>
            
            <p class="mt-4 text-muted small">
                Questions? <a href="mailto:support@happychurchruiru.org">Contact our support team</a>
            </p>
        </div>
    </div>
</div>
@endsection
