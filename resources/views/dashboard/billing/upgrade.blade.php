@extends('dashboard.layouts.app')

@section('title', 'Upgrade Plan')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Upgrade Your Plan</h1>
        <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Billing
        </a>
    </div>

    <p class="text-muted mb-4">Choose the plan that best fits your church. You can upgrade or downgrade at any time.</p>

    @if($tenant->status === 'trial')
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            You are currently on a <strong>free trial</strong>.
            @if($tenant->trial_ends_at)
                Your trial ends on <strong>{{ $tenant->trial_ends_at->format('d M Y') }}</strong>.
            @endif
            Selecting a plan will activate your subscription.
        </div>
    @endif

    <div class="row g-4">
        @foreach($plans as $plan)
            @php $isCurrent = $currentPlan && $currentPlan->id === $plan->id; @endphp
            <div class="col-md-4">
                <div class="card h-100 {{ $isCurrent ? 'border-primary shadow' : 'border' }}">
                    @if($isCurrent)
                        <div class="card-header bg-primary text-white text-center py-2">
                            <small class="fw-bold">CURRENT PLAN</small>
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">{{ $plan->name }}</h4>
                        <div class="fs-2 fw-bold text-primary mb-1">
                            KES {{ number_format($plan->price) }}
                        </div>
                        <div class="text-muted small mb-3">per month</div>

                        @if($plan->price_yearly)
                            <div class="alert alert-light py-2 small mb-3">
                                <i class="bi bi-tag me-1"></i>
                                Annual: <strong>KES {{ number_format($plan->price_yearly) }}/yr</strong>
                                (save {{ round((1 - $plan->price_yearly / ($plan->price * 12)) * 100) }}%)
                            </div>
                        @endif

                        <p class="text-muted small flex-grow-1">{{ $plan->description }}</p>
                    </div>
                    <div class="card-footer bg-transparent">
                        @if($isCurrent)
                            <button class="btn btn-success w-100" disabled>
                                <i class="bi bi-check-circle me-1"></i> Active Plan
                            </button>
                        @else
                            <button class="btn btn-primary w-100" type="button"
                                    onclick="alert('To change plans, please contact support or reach out to your platform administrator.')">
                                {{ $currentPlan && $plan->price > $currentPlan->price ? 'Upgrade to' : 'Switch to' }} {{ $plan->name }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="alert alert-secondary mt-4">
        <i class="bi bi-headset me-2"></i>
        Need help choosing a plan or have a custom requirement?
        <strong>Contact your platform administrator</strong> or reach out to Pisti support.
    </div>
</div>
@endsection
