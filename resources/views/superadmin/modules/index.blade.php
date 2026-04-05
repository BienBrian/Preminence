@extends('superadmin.layouts.app')

@section('title', 'Module Marketplace')

@section('content')
<div class="container-fluid py-4">
    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Total Modules</h6>
                    <h3>{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Active</h6>
                    <h3>{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Free</h6>
                    <h3>{{ $stats['free'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Paid</h6>
                    <h3>{{ $stats['paid'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Installs</h6>
                    <h3>{{ $stats['total_installs'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Active Addons</h6>
                    <h3>{{ $stats['active_addons'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('superadmin.modules.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Add New Module
            </a>
            <a href="{{ route('superadmin.plan-modules.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-grid-3x3-gap"></i> Plan-Module Matrix
            </a>
        </div>
        <div class="col-md-6">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="price_type" class="form-select">
                        <option value="">All Prices</option>
                        <option value="free" {{ request('price_type') == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="paid" {{ request('price_type') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search modules..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="row">
        @forelse($modules as $module)
        <div class="col-md-4 mb-4">
            <div class="card h-100 {{ $module->is_active ? '' : 'border-danger' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi {{ $module->icon ?? 'bi-box' }} fs-4 me-2"></i>
                        <span class="badge bg-{{ $module->category === 'core' ? 'primary' : ($module->category === 'premium' ? 'dark' : 'info') }}">
                            {{ $module->category }}
                        </span>
                    </div>
                    @if(!$module->is_active)
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $module->name }}</h5>
                    <p class="card-text text-muted small">{{ Str::limit($module->short_description ?? $module->description, 100) }}</p>
                    
                    <div class="mb-2">
                        @if($module->is_free)
                            <span class="badge bg-success">Free</span>
                        @else
                            <span class="badge bg-primary">
                                KES {{ number_format($module->price_monthly ?? 0, 0) }}/mo
                            </span>
                            @if($module->price_yearly)
                                <span class="badge bg-success">
                                    Save {{ $module->getYearlySavingsPercent() }}%
                                </span>
                            @endif
                        @endif
                    </div>

                    @if(!empty($module->dependencies))
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-diagram-2"></i> 
                                Requires: {{ implode(', ', array_slice($module->dependencies, 0, 2)) }}
                                @if(count($module->dependencies) > 2)
                                    +{{ count($module->dependencies) - 2 }} more
                                @endif
                            </small>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            {{ $module->tenantSubscriptions()->active()->count() }} active
                        </small>
                        <div class="btn-group">
                            <a href="{{ route('superadmin.modules.edit', $module) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="{{ route('superadmin.modules.analytics', $module) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-graph-up"></i> Stats
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="button" class="btn btn-sm {{ $module->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                            onclick="confirmDangerousAction(
                                {{ json_encode(route('superadmin.modules.toggle-active', $module)) }},
                                {{ json_encode($module->is_active ? 'Deactivate Module' : 'Activate Module') }},
                                {{ json_encode(($module->is_active ? 'You are about to DEACTIVATE ' : 'You are about to ACTIVATE ') . $module->name . '.') }},
                                'POST'
                            )">
                        <i class="bi {{ $module->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                        {{ $module->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger float-end" 
                            onclick="confirmDangerousAction(
                                {{ json_encode(route('superadmin.modules.destroy', $module)) }},
                                'Delete Module',
                                {{ json_encode('You are about to delete the module "' . $module->name . '". ' . ($module->tenantSubscriptions()->active()->count() > 0 ? 'This module has ' . $module->tenantSubscriptions()->active()->count() . ' active installations and will be deactivated instead. ' : '') . 'This action cannot be undone.') }},
                                'DELETE'
                            )">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-box fs-1 text-muted"></i>
            <p class="text-muted mt-3">No modules found matching your criteria.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $modules->links() }}
    </div>
</div>
@endsection
