@extends('dashboard.layouts.app')

@section('title', 'Billing & Subscription')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Billing & Subscription</h1>
        <a href="{{ route('billing.upgrade') }}" class="btn btn-primary">
            <i class="bi bi-arrow-up-circle me-1"></i> Upgrade Plan
        </a>
    </div>

    {{-- Current Plan --}}
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Current Plan</h5>
        </div>
        <div class="card-body">
            @if($currentPlan)
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h4 class="mb-1">{{ $currentPlan->name }}</h4>
                        <p class="text-muted mb-0">{{ $currentPlan->description }}</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="fs-3 fw-bold text-primary">KES {{ number_format($currentPlan->price) }}</div>
                        <div class="text-muted small">per month</div>
                    </div>
                    <div class="col-md-3 text-end">
                        @if($tenant->status === 'trial')
                            <span class="badge bg-info fs-6">Trial</span>
                            @if($tenant->trial_ends_at)
                                <p class="small text-muted mt-1">Ends {{ $tenant->trial_ends_at->format('d M Y') }}</p>
                            @endif
                        @else
                            <span class="badge bg-success fs-6">Active</span>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-muted mb-0">No plan assigned. <a href="{{ route('billing.upgrade') }}">Choose a plan</a></p>
            @endif
        </div>
    </div>

    {{-- Active Modules --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Active Modules</h5>
            <a href="{{ route('marketplace.onboarding.render', ['onboardingId' => 0]) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i> Add Modules
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($moduleDetails as $mod)
                    <div class="col-md-3 col-sm-6">
                        <div class="card h-100 {{ $mod['enabled'] ? 'border-success' : 'border-light text-muted' }}">
                            <div class="card-body text-center py-3">
                                <i class="bi {{ $mod['icon'] }} fs-2 {{ $mod['enabled'] ? 'text-success' : 'text-secondary opacity-50' }}"></i>
                                <div class="mt-2 fw-semibold small">{{ $mod['name'] }}</div>
                                <div class="mt-1">
                                    @if($mod['enabled'])
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Not Active</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Available Plans --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-grid me-2"></i>Available Plans</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($availablePlans as $plan)
                    <div class="col-md-4">
                        <div class="card h-100 {{ $currentPlan && $currentPlan->id === $plan->id ? 'border-primary' : '' }}">
                            <div class="card-body">
                                <h5 class="card-title">{{ $plan->name }}</h5>
                                <div class="fs-4 fw-bold text-primary mb-2">
                                    KES {{ number_format($plan->price) }}<small class="fs-6 text-muted">/mo</small>
                                </div>
                                <p class="card-text text-muted small">{{ $plan->description }}</p>
                            </div>
                            <div class="card-footer bg-transparent">
                                @if($currentPlan && $currentPlan->id === $plan->id)
                                    <button class="btn btn-success w-100" disabled>Current Plan</button>
                                @else
                                    <a href="{{ route('billing.upgrade') }}" class="btn btn-outline-primary w-100">
                                        Switch to This Plan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
