@extends('superadmin.layouts.app')

@section('title', 'View Tenant')
@section('page-title', 'Tenant: ' . $tenant->name)

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Tenant Info Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tenant Details</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ $tenant->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Slug:</strong></td>
                        <td>{{ $tenant->slug }}</td>
                    </tr>
                    <tr>
                        <td><strong>Domain:</strong></td>
                        <td>{{ $tenant->domain ?: 'None' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
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
                    </tr>
                    <tr>
                        <td><strong>Plan:</strong></td>
                        <td>{{ $tenant->plan->name ?? 'No Plan' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                    </tr>
                </table>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('superadmin.tenants.edit', $tenant->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Tenant
                    </a>
                    
                    @if($tenant->status !== 'suspended')
                        <a href="{{ route('superadmin.tenants.suspend.form', $tenant->id) }}" class="btn btn-danger">
                            <i class="bi bi-pause-circle"></i> Suspend Tenant
                        </a>
                    @else
                        <form action="{{ route('superadmin.tenants.activate', $tenant->id) }}" method="POST" class="d-inline w-100">
                            @csrf
                            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Activate tenant: {{ $tenant->name }}?')">
                                <i class="bi bi-play-circle"></i> Activate Tenant
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('superadmin.tenants.impersonate', $tenant->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info w-100" {{ $tenant->status !== 'active' && $tenant->status !== 'trial' ? 'disabled' : '' }}>
                            <i class="bi bi-person-badge"></i> Login as Admin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card stat-card primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['users'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people" style="font-size: 2rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card stat-card success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Funds</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($stats['total_funds'] ?? 0, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-cash-stack" style="font-size: 2rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @if($tenant->status === 'suspended')
        <!-- Suspension Details -->
        <div class="card shadow mb-4 border-danger">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="bi bi-exclamation-triangle"></i> Suspension Details
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Suspension Type:</strong></td>
                        <td>
                            @switch($tenant->suspension_type)
                                @case('financial')
                                    <span class="badge bg-warning">Financial - Payment Required</span>
                                    @break
                                @case('terms_violation')
                                    <span class="badge bg-danger">Terms of Service Violation</span>
                                    @break
                                @case('admin_action')
                                    <span class="badge bg-secondary">Administrative Action</span>
                                    @break
                                @default
                                    <span class="badge bg-info">Other</span>
                            @endswitch
                        </td>
                    </tr>
                    @if($tenant->suspension_type === 'financial' && $tenant->suspension_amount_due)
                    <tr>
                        <td><strong>Amount Due:</strong></td>
                        <td class="text-danger fw-bold">
                            {{ $tenant->suspension_currency }} {{ number_format($tenant->suspension_amount_due, 2) }}
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Reason:</strong></td>
                        <td>{{ $tenant->suspension_reason ?: 'No reason provided' }}</td>
                    </tr>
                    @if($tenant->suspension_ends_at)
                    <tr>
                        <td><strong>Suspension Ends:</strong></td>
                        <td>{{ $tenant->suspension_ends_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Suspended By:</strong></td>
                        <td>
                            @php
                                $suspendedBy = \App\Models\SuperAdmin::find($tenant->suspended_by);
                            @endphp
                            {{ $suspendedBy ? $suspendedBy->name : 'System' }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Suspended At:</strong></td>
                        <td>{{ $tenant->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
        
        <!-- Subscription Info -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Subscription Information</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td><strong>Current Plan:</strong></td>
                        <td>{{ $tenant->plan->name ?? 'No Plan' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Plan Price:</strong></td>
                        <td>
                            @if($tenant->plan)
                                ${{ number_format($tenant->plan->price, 2) }} / {{ $tenant->plan->billing_period }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Trial Ends:</strong></td>
                        <td>{{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Subscription Ends:</strong></td>
                        <td>{{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Grace Period:</strong></td>
                        <td>{{ $tenant->grace_period_days }} days</td>
                    </tr>
                    <tr>
                        <td><strong>Setup Complete:</strong></td>
                        <td>{{ $tenant->setup_complete ? 'Yes' : 'No' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <a href="http://{{ $tenant->slug }}.pisti.co.ke" target="_blank" class="btn btn-outline-primary w-100">
                            <i class="bi bi-box-arrow-up-right"></i> Visit Site
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <a href="{{ route('superadmin.tenants.edit', $tenant->id) }}" class="btn btn-outline-warning w-100">
                            <i class="bi bi-pencil"></i> Edit Tenant
                        </a>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="button" class="btn btn-outline-success w-100" onclick="alert('Feature coming soon!')">
                            <i class="bi bi-envelope"></i> Contact Admin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
