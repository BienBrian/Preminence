@extends('superadmin.layouts.app')

@section('title', 'Module Subscriptions')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Module Subscriptions</h1>
        <a href="{{ route('superadmin.billing.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Billing
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="uninstalled" {{ request('status') === 'uninstalled' ? 'selected' : '' }}>Uninstalled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tenant</label>
                    <select name="tenant_id" class="form-select">
                        <option value="">All Tenants</option>
                        @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Billing Type</label>
                    <select name="billing_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="plan_included" {{ request('billing_type') === 'plan_included' ? 'selected' : '' }}>Plan Included</option>
                        <option value="addon_monthly" {{ request('billing_type') === 'addon_monthly' ? 'selected' : '' }}>Monthly Add-on</option>
                        <option value="addon_yearly" {{ request('billing_type') === 'addon_yearly' ? 'selected' : '' }}>Yearly Add-on</option>
                        <option value="one_time" {{ request('billing_type') === 'one_time' ? 'selected' : '' }}>One-time</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-2"></i>Filter
                    </button>
                    <a href="{{ route('superadmin.billing.subscriptions') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats Summary --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small opacity-75">Active</div>
                            <div class="h4 mb-0">{{ $subscriptions->where('status', 'active')->count() }}</div>
                        </div>
                        <i class="bi bi-check-circle fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small opacity-75">Pending</div>
                            <div class="h4 mb-0">{{ $subscriptions->where('status', 'pending')->count() }}</div>
                        </div>
                        <i class="bi bi-clock fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small opacity-75">Suspended</div>
                            <div class="h4 mb-0">{{ $subscriptions->where('status', 'suspended')->count() }}</div>
                        </div>
                        <i class="bi bi-pause-circle fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small opacity-75">Monthly Revenue</div>
                            <div class="h4 mb-0">
                                KES {{ number_format($subscriptions->where('status', 'active')->sum('price'), 0) }}
                            </div>
                        </div>
                        <i class="bi bi-cash-stack fs-2 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscriptions Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tenant</th>
                        <th>Module</th>
                        <th>Billing Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Next Billing</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    <tr>
                        <td>
                            <a href="{{ route('superadmin.billing.tenant', $sub->tenant_id) }}">
                                {{ $sub->tenant->name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi {{ $sub->module?->icon ?? 'bi-box' }} me-2 text-muted"></i>
                                {{ $sub->module?->name ?? $sub->module_key }}
                            </div>
                        </td>
                        <td>{{ $sub->getBillingPeriodLabel() }}</td>
                        <td>KES {{ number_format($sub->price, 2) }}</td>
                        <td>
                            @php
                            $statusColors = [
                                'active' => 'success',
                                'pending' => 'warning',
                                'suspended' => 'danger',
                                'uninstalled' => 'secondary',
                                'failed' => 'danger',
                            ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$sub->status] ?? 'secondary' }}">
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                        <td>
                            @if($sub->next_billing_at)
                                {{ $sub->next_billing_at->format('M d, Y') }}
                                @if($sub->next_billing_at->isPast())
                                <span class="badge bg-danger ms-1">Overdue</span>
                                @elseif($sub->next_billing_at->diffInDays(now()) <= 7)
                                <span class="badge bg-warning ms-1">Soon</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('superadmin.tenant-modules.index', $sub->tenant_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No subscriptions found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
        <div class="card-footer">
            {{ $subscriptions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
