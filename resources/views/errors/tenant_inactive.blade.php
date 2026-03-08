@extends('layouts.app')

@section('title', 'Account ' . ucfirst($status))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                @if($status === 'suspended')
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
                @else
                    <i class="bi bi-pause-circle text-muted" style="font-size: 5rem;"></i>
                @endif
            </div>
            
            <h1 class="display-5 mb-3">
                @if($status === 'suspended')
                    Account Suspended
                @elseif($status === 'trial_expired')
                    Trial Expired
                @else
                    Account Inactive
                @endif
            </h1>
            
            <p class="lead text-muted mb-4">
                @if($status === 'suspended')
                    This church's account has been suspended. Please contact the administrator.
                @elseif($status === 'trial_expired')
                    Your trial period has ended. Upgrade to continue using the platform.
                @else
                    This church's account is currently inactive.
                @endif
            </p>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Church Details</h5>
                    <p class="mb-1"><strong>{{ $tenant->name }}</strong></p>
                    <p class="text-muted mb-0">Status: <span class="badge bg-{{ $status === 'suspended' ? 'danger' : 'warning' }}">{{ ucfirst($status) }}</span></p>
                </div>
            </div>
            
            @if($status === 'trial_expired')
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="mailto:support@happychurchruiru.org" class="btn btn-primary btn-lg px-4 gap-3">
                        <i class="bi bi-envelope"></i> Contact Sales
                    </a>
                </div>
            @else
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="mailto:support@happychurchruiru.org" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-envelope"></i> Contact Support
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
