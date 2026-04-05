@extends('layouts.admin')

@section('title', 'Module Marketplace')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3><i class="bi bi-shop"></i> Module Marketplace</h3>
            <p class="text-muted mb-0">Enhance your church management with powerful modules</p>
        </div>
        <a href="{{ route('my-modules.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-grid"></i> My Modules
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                <i class="bi {{ $category->icon }}"></i> {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="price_type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Prices</option>
                        <option value="free" {{ request('price_type') == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="paid" {{ request('price_type') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search modules..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-outline-secondary">Search</button>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <select name="sort_by" class="form-select" onchange="this.form.submit()">
                        <option value="sort_order" {{ request('sort_by') == 'sort_order' ? 'selected' : '' }}>Default</option>
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="price_monthly" {{ request('sort_by') == 'price_monthly' ? 'selected' : '' }}>Price (Low-High)</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="row">
        @forelse($modules as $module)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm {{ $module->is_installed ? 'border-success' : '' }}">
                @if($module->is_installed)
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Installed
                        </span>
                        @if($module->is_in_trial)
                            <span class="badge bg-warning text-dark mt-1 d-block">
                                Trial: {{ $module->trial_ends_at->diffInDays() }} days left
                            </span>
                        @endif
                    </div>
                @endif

                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded p-3">
                                <i class="bi {{ $module->icon ?? 'bi-box' }} fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="card-title mb-1">{{ $module->name }}</h5>
                            <span class="badge bg-light text-dark">{{ $module->category }}</span>
                        </div>
                    </div>

                    <p class="card-text text-muted">{{ $module->short_description ?? Str::limit($module->description, 120) }}</p>

                    @if(!empty($module->highlights))
                        <ul class="list-unstyled mb-3">
                            @foreach(array_slice($module->highlights, 0, 3) as $highlight)
                                <li class="small text-muted"><i class="bi bi-check text-success"></i> {{ $highlight }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if(!$module->is_installed && !empty($module->dependencies))
                        <div class="alert alert-info py-2 px-3 small">
                            <i class="bi bi-info-circle"></i>
                            Requires: {{ implode(', ', array_column($module->dependencies, 'name')) }}
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($module->is_free)
                                <span class="text-success fw-bold">Free</span>
                            @else
                                <div>
                                    @if($module->price_info->monthly)
                                        <span class="fw-bold">KES {{ number_format($module->price_info->monthly) }}</span>
                                        <small class="text-muted">/mo</small>
                                    @endif
                                    @if($module->price_info->yearly && $module->price_info->yearlySavingsPercent)
                                        <br><small class="text-success">
                                            Save {{ $module->price_info->yearlySavingsPercent }}% yearly
                                        </small>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($module->is_installed)
                            <a href="{{ route('my-modules.index') }}" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-gear"></i> Manage
                            </a>
                        @elseif($module->can_install)
                            <a href="{{ route('marketplace.show', $module->key) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Install
                            </a>
                        @else
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="bi bi-lock"></i> Upgrade Required
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-box fs-1 text-muted"></i>
            <p class="text-muted mt-3">No modules found matching your criteria.</p>
            <a href="{{ route('marketplace.index') }}" class="btn btn-outline-primary">Clear Filters</a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $modules->links() }}
    </div>
</div>
@endsection
