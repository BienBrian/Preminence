@extends('superadmin.layouts.app')

@section('title', 'Tenant Modules: ' . $tenant->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Module Management: {{ $tenant->name }}</h4>
            <p class="text-muted mb-0">
                Plan: <span class="badge bg-primary">{{ $tenant->plan?->name ?? 'None' }}</span>
                | Total Addon Cost: <span class="badge bg-info">KES {{ number_format($addonCost, 2) }}/mo</span>
            </p>
        </div>
        <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Tenant
        </a>
    </div>

    <!-- Module Grid -->
    <div class="row">
        @foreach($availableModules as $module)
            @php
                $status = $moduleStatus[$module->key] ?? ['installed' => false, 'status' => 'not_installed'];
                $subscription = $status['subscription'];
            @endphp
            <div class="col-md-4 mb-4">
                <div class="card h-100 {{ $status['installed'] ? ($status['status'] === 'active' ? 'border-success' : 'border-warning') : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi {{ $module->icon ?? 'bi-box' }} fs-4 me-2"></i>
                            <span>{{ $module->name }}</span>
                        </div>
                        @if($status['is_included'])
                            <span class="badge bg-success">Plan Included</span>
                        @elseif($status['installed'])
                            @if($status['status'] === 'active')
                                <span class="badge bg-primary">Active</span>
                            @elseif($status['status'] === 'suspended')
                                <span class="badge bg-warning">Suspended</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($status['status']) }}</span>
                            @endif
                        @endif
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">{{ $module->short_description ?? Str::limit($module->description, 100) }}</p>
                        
                        @if($status['installed'])
                            <table class="table table-sm">
                                <tr>
                                    <td>Billing:</td>
                                    <td>{{ $subscription?->getBillingPeriodLabel() ?? 'N/A' }}</td>
                                </tr>
                                @if($subscription?->price > 0)
                                    <tr>
                                        <td>Price:</td>
                                        <td>KES {{ number_format($subscription->price, 2) }}</td>
                                    </tr>
                                @endif
                                @if($subscription?->trial_ends_at)
                                    <tr>
                                        <td>Trial Ends:</td>
                                        <td>{{ $subscription->trial_ends_at->format('M d, Y') }}</td>
                                    </tr>
                                @endif
                            </table>
                        @else
                            <div class="text-muted">
                                @if($module->is_free)
                                    <span class="text-success">Free</span>
                                @else
                                    KES {{ number_format($module->price_monthly ?? 0, 0) }}/mo
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent">
                        @if($status['installed'])
                            <div class="btn-group w-100">
                                @if($status['status'] === 'active')
                                    <button type="button" class="btn btn-sm btn-warning"
                                            onclick="confirmDangerousAction(
                                                {{ json_encode(route('superadmin.tenant-modules.toggle-suspension', ['tenant' => $tenant, 'module_key' => $module->key])) }},
                                                'Suspend Module',
                                                {{ json_encode('You are about to SUSPEND ' . $module->name . ' for ' . $tenant->name . '. The tenant will lose access immediately.') }},
                                                'POST'
                                            )">
                                        <i class="bi bi-pause"></i> Suspend
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-success"
                                            onclick="confirmDangerousAction(
                                                {{ json_encode(route('superadmin.tenant-modules.toggle-suspension', ['tenant' => $tenant, 'module_key' => $module->key])) }},
                                                'Activate Module',
                                                {{ json_encode('You are about to ACTIVATE ' . $module->name . ' for ' . $tenant->name . '. The tenant will regain access.') }},
                                                'POST'
                                            )">
                                        <i class="bi bi-play"></i> Activate
                                    </button>
                                @endif
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPricingModal{{ $module->key }}">
                                    <i class="bi bi-currency-dollar"></i> Pricing
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDangerousAction(
                                            {{ json_encode(route('superadmin.tenant-modules.revoke', ['tenant' => $tenant, 'module_key' => $module->key])) }},
                                            'Revoke Module Access',
                                            {{ json_encode('You are about to revoke "' . $module->name . '" from ' . $tenant->name . '. This will remove the module from this tenant and may delete their data. This action cannot be undone.') }},
                                            'POST'
                                        )">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        @else
                            <button class="btn btn-sm btn-primary w-100" data-bs-toggle="modal" data-bs-target="#grantModal{{ $module->key }}">
                                <i class="bi bi-plus-lg"></i> Grant Access
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Grant Modal -->
            @if(!$status['installed'])
            <div class="modal fade" id="grantModal{{ $module->key }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('superadmin.tenant-modules.grant', $tenant) }}">
                            @csrf
                            <input type="hidden" name="module_key" value="{{ $module->key }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Grant Access: {{ $module->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Billing Type</label>
                                    <select name="billing_type" class="form-select">
                                        <option value="plan_included">Plan Included</option>
                                        <option value="addon_monthly">Add-on (Monthly)</option>
                                        <option value="addon_yearly">Add-on (Yearly)</option>
                                        <option value="complimentary">Complimentary</option>
                                        <option value="trial">Trial</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Custom Price (optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" name="price" class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Trial Days</label>
                                    <input type="number" name="trial_days" class="form-control" value="0" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason (optional)</label>
                                    <textarea name="reason" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Grant Access</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
