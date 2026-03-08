@extends('superadmin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Platform Dashboard')

@section('content')
<div class="row">
    <!-- Stats Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Tenants</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['tenants']['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Active Tenants</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['tenants']['active'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Plans Available</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['plans'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-credit-card fa-2x text-gray-300" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            SuperAdmins</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['superadmins'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300" style="font-size: 2rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tenant Status Breakdown -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Tenant Status Overview</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <td><span class="badge bg-success">Active</span></td>
                            <td class="text-end"><strong>{{ $stats['tenants']['active'] }}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-info">Trial</span></td>
                            <td class="text-end"><strong>{{ $stats['tenants']['trial'] }}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning">Suspended</span></td>
                            <td class="text-end"><strong>{{ $stats['tenants']['suspended'] }}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-secondary">Pending</span></td>
                            <td class="text-end"><strong>{{ $stats['tenants']['total'] - $stats['tenants']['active'] - $stats['tenants']['trial'] - $stats['tenants']['suspended'] }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Tenants -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Recently Added Tenants</h6>
                <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTenants as $tenant)
                                <tr>
                                    <td>
                                        <a href="{{ route('superadmin.tenants.show', $tenant->id) }}">
                                            {{ $tenant->name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ $tenant->slug }}</small>
                                    </td>
                                    <td>
                                        @if($tenant->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($tenant->status === 'trial')
                                            <span class="badge bg-info">Trial</span>
                                        @elseif($tenant->status === 'suspended')
                                            <span class="badge bg-warning">Suspended</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($tenant->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $tenant->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No tenants yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-primary btn-block w-100">
                            <i class="bi bi-plus-lg"></i> Add New Tenant
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('superadmin.plans.create') }}" class="btn btn-success btn-block w-100">
                            <i class="bi bi-plus-lg"></i> Create Plan
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('superadmin.admins.create') }}" class="btn btn-info btn-block w-100 text-white">
                            <i class="bi bi-plus-lg"></i> Add SuperAdmin
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/" class="btn btn-outline-secondary btn-block w-100" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Visit Site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
