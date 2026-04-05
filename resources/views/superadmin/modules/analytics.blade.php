@extends('superadmin.layouts.app')

@section('title', 'Module Analytics: ' . $module->name)
@section('page-title', 'Module Analytics')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $module->name }}</h4>
            <span class="badge bg-secondary">{{ $module->key }}</span>
            <span class="badge {{ $module->is_active ? 'bg-success' : 'bg-danger' }} ms-1">
                {{ $module->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <a href="{{ route('superadmin.modules.edit', $module) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Edit Module
        </a>
    </div>

    <!-- Key Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-primary">{{ $stats['total_installs'] ?? 0 }}</div>
                    <div class="text-muted small">Total Installs</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-success">{{ $stats['active_installs'] ?? 0 }}</div>
                    <div class="text-muted small">Active Installs</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-info">KES {{ number_format($revenueStats['monthly_revenue'] ?? 0) }}</div>
                    <div class="text-muted small">Monthly Revenue</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning h-100">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-warning">{{ $stats['churn_rate'] ?? '0' }}%</div>
                    <div class="text-muted small">Churn Rate</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Adoption Trend -->
        <div class="col-md-8 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">Adoption Trend (Last 12 Months)</h6>
                </div>
                <div class="card-body">
                    @if(!empty($adoptionTrend))
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-end">New Installs</th>
                                        <th class="text-end">Uninstalls</th>
                                        <th class="text-end">Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adoptionTrend as $row)
                                        <tr>
                                            <td>{{ $row['month'] ?? '—' }}</td>
                                            <td class="text-end text-success">+{{ $row['installs'] ?? 0 }}</td>
                                            <td class="text-end text-danger">-{{ $row['uninstalls'] ?? 0 }}</td>
                                            <td class="text-end fw-bold">{{ ($row['installs'] ?? 0) - ($row['uninstalls'] ?? 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-graph-up fs-1 opacity-25"></i>
                            <p class="mt-2">No adoption data available yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Revenue Breakdown -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">Revenue Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Monthly Plans</span>
                            <strong>KES {{ number_format($revenueStats['monthly_subscriptions'] ?? 0) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Yearly Plans</span>
                            <strong>KES {{ number_format($revenueStats['yearly_subscriptions'] ?? 0) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 border-top pt-3">
                        <div class="d-flex justify-content-between">
                            <strong>Total (All Time)</strong>
                            <strong class="text-success">KES {{ number_format($revenueStats['total_revenue'] ?? 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Tenants -->
    <div class="card shadow">
        <div class="card-header">
            <h6 class="mb-0">Top Tenants Using This Module</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tenant</th>
                            <th>Status</th>
                            <th>Installed</th>
                            <th>Billing</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topTenants as $tenant)
                            <tr>
                                <td>
                                    <a href="{{ route('superadmin.tenants.show', $tenant['id'] ?? 0) }}">
                                        {{ $tenant['name'] ?? '—' }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $tenant['status'] === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($tenant['status'] ?? 'unknown') }}
                                    </span>
                                </td>
                                <td>{{ isset($tenant['installed_at']) ? \Carbon\Carbon::parse($tenant['installed_at'])->format('d M Y') : '—' }}</td>
                                <td>{{ $tenant['billing_type'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No tenants have installed this module yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
