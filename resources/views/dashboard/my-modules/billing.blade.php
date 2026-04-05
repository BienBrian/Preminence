@extends('dashboard.layouts.app')

@section('title', $module->name ?? $subscription->module_key . ' - Billing')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-3">
            {{-- Sidebar --}}
            <div class="list-group mb-4">
                <a href="{{ route('my-modules.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Modules
                </a>
            </div>
        </div>
        
        <div class="col-lg-9">
            <h2 class="h4 mb-4">Billing Settings</h2>
            
            {{-- Current Plan --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Current Billing</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-muted small">Billing Cycle</div>
                            <div class="h5">{{ $subscription->getBillingPeriodLabel() }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Current Price</div>
                            <div class="h5">KES {{ number_format($subscription->price, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Next Billing Date</div>
                            <div class="h5">
                                @if($subscription->next_billing_at)
                                    {{ $subscription->next_billing_at->format('M d, Y') }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Change Billing Cycle --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Change Billing Cycle</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('my-modules.change-billing', $subscription) }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            {{-- Monthly Option --}}
                            <div class="col-md-6">
                                <div class="card h-100 {{ $currentCycle === 'monthly' ? 'border-primary' : '' }}">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                name="billing_cycle" 
                                                id="monthly" 
                                                value="monthly"
                                                {{ $currentCycle === 'monthly' ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="monthly">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="d-block">Monthly</strong>
                                                        <small class="text-muted">Pay every month</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="h5 mb-0">KES {{ number_format($monthlyPrice->monthly, 0) }}</span>
                                                        <small class="text-muted d-block">/month</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Yearly Option --}}
                            <div class="col-md-6">
                                <div class="card h-100 {{ $currentCycle === 'yearly' ? 'border-primary' : '' }}">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                name="billing_cycle" 
                                                id="yearly" 
                                                value="yearly"
                                                {{ $currentCycle === 'yearly' ? 'checked' : '' }}>
                                            <label class="form-check-label w-100" for="yearly">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong class="d-block">Yearly</strong>
                                                        @if($module && $module->getYearlySavingsPercent())
                                                        <span class="badge bg-success">Save {{ $module->getYearlySavingsPercent() }}%</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="h5 mb-0">KES {{ number_format($yearlyPrice->yearly, 0) }}</span>
                                                        <small class="text-muted d-block">/year</small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-2"></i>Update Billing Cycle
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Changes will take effect on your next billing date. Any price difference will be prorated.
                    </small>
                </div>
            </div>
            
            {{-- Cancel Option --}}
            <div class="card border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="mb-0 text-danger">Cancel Subscription</h5>
                </div>
                <div class="card-body">
                    <p>Canceling this module will:</p>
                    <ul>
                        <li>Immediately disable the module features</li>
                        <li>Stop future billing</li>
                        <li>Optionally remove all module data</li>
                    </ul>
                    <a href="{{ route('my-modules.cancel-form', $subscription) }}" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-2"></i>Cancel Module
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
