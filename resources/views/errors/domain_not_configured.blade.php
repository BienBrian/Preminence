@extends('layouts.app')

@section('title', 'Domain Not Configured')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                <i class="bi bi-globe text-muted" style="font-size: 5rem;"></i>
            </div>
            <h1 class="display-5 mb-3">Domain Not Configured</h1>
            <p class="lead text-muted mb-4">
                The domain <strong>{{ $host }}</strong> is not configured in our system.
            </p>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Are you looking for:</h5>
                    <div class="list-group list-group-flush text-start mt-3">
                        <a href="https://happychurchruiru.org" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Happy Church Ruiru</h6>
                                <i class="bi bi-arrow-right"></i>
                            </div>
                            <small class="text-muted">happychurchruiru.org</small>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info" role="alert">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Future Updates</h5>
                <p class="mb-0">
                    We're working on a new platform where churches can easily register and get their own 
                    subdomain. Stay tuned for updates!
                </p>
            </div>
            
            <p class="text-muted small mt-4">
                If you believe this is an error, please contact support at 
                <a href="mailto:support@happychurchruiru.org">support@happychurchruiru.org</a>
            </p>
        </div>
    </div>
</div>
@endsection
