@extends('superadmin.layouts.app')

@section('title', 'Edit Tenant')
@section('page-title', 'Edit Tenant: ' . $tenant->name)

@section('content')
<div class="row">
    {{-- Tenant Information --}}
    <div class="col-md-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tenant Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.tenants.update', $tenant->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Church/Organization Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $tenant->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Subdomain Slug *</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                   id="slug" name="slug" value="{{ old('slug', $tenant->slug) }}" required>
                            <span class="input-group-text">.{{ pisti_platform_domain() }}</span>
                        </div>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="domain" class="form-label">Custom Domain (Optional)</label>
                        <input type="text" class="form-control @error('domain') is-invalid @enderror" 
                               id="domain" name="domain" value="{{ old('domain', $tenant->domain) }}">
                        @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="plan_id" class="form-label">Plan</label>
                        <select class="form-select @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id">
                            <option value="">-- Select Plan --</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ old('plan_id', $tenant->plan_id) == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - KES {{ number_format($plan->price, 2) }}/{{ $plan->billing_period }}
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $tenant->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="trial" {{ old('status', $tenant->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                            <option value="pending" {{ old('status', $tenant->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="suspended" {{ old('status', $tenant->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Update Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="col-md-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('superadmin.billing.tenant', $tenant->id) }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-credit-card me-2"></i> View Billing
                </a>
                <a href="{{ route('superadmin.tenants.impersonate', $tenant->id) }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-person-fill me-2"></i> Impersonate Tenant
                </a>
                <a href="{{ route('superadmin.dns.index') }}?tenant={{ $tenant->id }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-globe me-2"></i> DNS Management
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Active Modules Section --}}
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Active Modules</h6>
                <a href="{{ route('superadmin.tenant-modules.index', $tenant->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Grant Module
                </a>
            </div>
            <div class="card-body">
                @if($moduleSubscriptions->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                        No modules assigned to this tenant.
                        <br>
                        <a href="{{ route('superadmin.tenant-modules.index', $tenant->id) }}" class="btn btn-primary mt-3">
                            Grant First Module
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Category</th>
                                    <th>Billing Type</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Installed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($moduleSubscriptions as $subscription)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi {{ $subscription->module?->icon ?? 'bi-box' }} me-2 text-primary"></i>
                                            <strong>{{ $subscription->module?->name ?? $subscription->module_key }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $subscription->module?->category ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ $subscription->getBillingPeriodLabel() }}</td>
                                    <td>KES {{ number_format($subscription->price, 2) }}</td>
                                    <td>
                                        @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'pending' => 'warning',
                                            'suspended' => 'danger',
                                            'uninstalled' => 'secondary',
                                            'installing' => 'info',
                                        ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$subscription->status] ?? 'secondary' }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $subscription->installed_at?->format('M d, Y') ?? 'Pending' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($subscription->status === 'active')
                                                <button type="button" class="btn btn-warning" title="Suspend"
                                                        onclick="confirmDangerousAction(
                                                            {{ json_encode(route('superadmin.tenant-modules.toggle-suspension', ['tenant' => $tenant->id, 'module_key' => $subscription->module_key])) }},
                                                            'Suspend Module',
                                                            {{ json_encode('You are about to SUSPEND ' . ($subscription->module?->name ?? $subscription->module_key) . ' for ' . $tenant->name . '. The tenant will lose access to this module immediately.') }},
                                                            'POST'
                                                        )">
                                                    <i class="bi bi-pause-fill"></i>
                                                </button>
                                            @elseif($subscription->status === 'suspended')
                                                <button type="button" class="btn btn-success" title="Activate"
                                                        onclick="confirmDangerousAction(
                                                            {{ json_encode(route('superadmin.tenant-modules.toggle-suspension', ['tenant' => $tenant->id, 'module_key' => $subscription->module_key])) }},
                                                            'Activate Module',
                                                            {{ json_encode('You are about to ACTIVATE ' . ($subscription->module?->name ?? $subscription->module_key) . ' for ' . $tenant->name . '. The tenant will regain access to this module.') }},
                                                            'POST'
                                                        )">
                                                    <i class="bi bi-play-fill"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-danger" title="Revoke"
                                                    onclick="confirmDangerousAction(
                                                        {{ json_encode(route('superadmin.tenant-modules.revoke', ['tenant' => $tenant->id, 'module_key' => $subscription->module_key])) }},
                                                        'Revoke Module Access',
                                                        {{ json_encode('You are about to revoke ' . ($subscription->module?->name ?? $subscription->module_key) . ' from ' . $tenant->name . '. This action cannot be undone.') }},
                                                        'POST'
                                                    )">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Available Modules to Grant --}}
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Grant Module</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.tenant-modules.grant', $tenant->id) }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Select Module</label>
                            <select name="module_key" class="form-select" required>
                                <option value="">-- Choose Module --</option>
                                @foreach($modules as $module)
                                    @if(!$tenant->hasModule($module->key))
                                    <option value="{{ $module->key }}">
                                        {{ $module->name }} ({{ $module->category }})
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Billing Type</label>
                            <select name="billing_type" class="form-select" required>
                                <option value="plan_included">Plan Included</option>
                                <option value="complimentary">Complimentary (Free)</option>
                                <option value="trial">Trial</option>
                                <option value="addon_monthly">Monthly Add-on</option>
                                <option value="addon_yearly">Yearly Add-on</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Price (KES)</label>
                            <input type="number" name="price" class="form-control" value="0" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Trial Days</label>
                            <input type="number" name="trial_days" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Reason/Notes (Optional)</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Why is this module being granted?"></textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
