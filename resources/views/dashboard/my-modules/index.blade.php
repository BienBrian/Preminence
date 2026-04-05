@extends('layouts.admin')

@section('title', 'My Modules')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3><i class="bi bi-grid"></i> My Modules</h3>
            <p class="text-muted mb-0">Manage your installed modules and subscriptions</p>
        </div>
        <a href="{{ route('marketplace.index') }}" class="btn btn-primary">
            <i class="bi bi-shop"></i> Browse Marketplace
        </a>
    </div>

    <!-- Cost Summary -->
    @if($addonCost['total_monthly_equivalent'] > 0)
    <div class="alert alert-info mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Monthly Addon Cost:</strong> KES {{ number_format($addonCost['total_monthly_equivalent'], 2) }}
                <small class="text-muted d-block">
                    {{ count($addonCost['breakdown']) }} active addon module(s)
                </small>
            </div>
            <a href="#billing-details" class="btn btn-sm btn-outline-info">View Details</a>
        </div>
    </div>
    @endif

    <!-- Pending Installations -->
    @if($pending->count() > 0)
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="bi bi-hourglass-split"></i> Pending Installations ({{ $pending->count() }})</h6>
        </div>
        <div class="card-body">
            @foreach($pending as $sub)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>{{ $sub->module_info?->name ?? $sub->module_key }}</strong>
                        <span class="badge bg-{{ $sub->status === 'installing' ? 'primary' : 'secondary' }} ms-2">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </div>
                    <div class="progress" style="width: 200px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             style="width: {{ $sub->getInstallationProgress() }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Active Modules -->
    <h5 class="mb-3"><i class="bi bi-check-circle-fill text-success"></i> Active Modules</h5>
    <div class="row mb-4">
        @forelse($active as $sub)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 {{ $sub->isInTrial() ? 'border-warning' : 'border-success' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi {{ $sub->module_info?->icon ?? 'bi-box' }} me-2"></i>
                        <span>{{ $sub->module_info?->name ?? $sub->module_key }}</span>
                    </div>
                    @if($sub->isInTrial())
                        <span class="badge bg-warning text-dark">
                            {{ $sub->daysRemainingInTrial() }} days left
                        </span>
                    @elseif($sub->billing_type === 'plan_included')
                        <span class="badge bg-success">Included</span>
                    @elseif($sub->billing_type === 'complimentary')
                        <span class="badge bg-info">Complimentary</span>
                    @else
                        <span class="badge bg-primary">Active</span>
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted small">{{ $sub->module_info?->short_description ?? '' }}</p>
                    
                    @if($sub->price > 0)
                        <div class="mb-2">
                            <small class="text-muted">
                                {{ $sub->getBillingPeriodLabel() }}: 
                                <strong>KES {{ number_format($sub->price, 2) }}</strong>
                            </small>
                        </div>
                    @endif

                    @if($sub->next_billing_at)
                        <div class="mb-2">
                            <small class="text-muted">
                                Next billing: {{ $sub->next_billing_at->format('M d, Y') }}
                            </small>
                        </div>
                    @endif

                    @if($sub->trial_ends_at && $sub->isInTrial())
                        <div class="alert alert-warning py-2 px-3 small mb-0">
                            <i class="bi bi-clock"></i>
                            Trial converts to paid on {{ $sub->trial_ends_at->format('M d, Y') }}
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100">
                        @if($sub->billing_type !== 'plan_included' && $sub->billing_type !== 'complimentary')
                            <a href="{{ route('my-modules.billing', $sub) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-credit-card"></i> Billing
                            </a>
                        @endif
                        <a href="{{ route('my-modules.settings', $sub) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                        @if($sub->billing_type !== 'plan_included')
                            <a href="{{ route('my-modules.cancel-form', $sub) }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-box fs-1 text-muted"></i>
            <p class="text-muted mt-3">No active modules yet.</p>
            <a href="{{ route('marketplace.index') }}" class="btn btn-outline-primary">
                Browse Marketplace
            </a>
        </div>
        @endforelse
    </div>

    <!-- Suspended Modules -->
    @if($suspended->count() > 0)
    <h5 class="mb-3"><i class="bi bi-pause-circle-fill text-warning"></i> Suspended</h5>
    <div class="row mb-4">
        @foreach($suspended as $sub)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ $sub->module_info?->name ?? $sub->module_key }}</span>
                        <span class="badge bg-dark">Suspended</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small">{{ $sub->suspension_reason ?? 'Payment issue or admin action' }}</p>
                    @if($sub->suspended_at)
                        <small class="text-muted">Since {{ $sub->suspended_at->format('M d, Y') }}</small>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('billing.index') }}" class="btn btn-sm btn-warning w-100">
                        <i class="bi bi-credit-card"></i> Resolve Payment
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Billing Details -->
    @if(count($addonCost['breakdown']) > 0)
    <div id="billing-details" class="card">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-receipt"></i> Billing Breakdown</h6>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Billing Type</th>
                        <th class="text-end">Monthly Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($addonCost['breakdown'] as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item['billing_type'])) }}</td>
                        <td class="text-end">KES {{ number_format($item['monthly_equivalent'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-group-divider">
                    <tr class="fw-bold">
                        <td colspan="2">Total Monthly Equivalent</td>
                        <td class="text-end">KES {{ number_format($addonCost['total_monthly_equivalent'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
