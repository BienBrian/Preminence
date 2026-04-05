@extends('layouts.admin')

@section('title', $module->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('marketplace.index') }}">Marketplace</a></li>
            <li class="breadcrumb-item active">{{ $module->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-4">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded p-4">
                                <i class="bi {{ $module->icon ?? 'bi-box' }} fs-1 text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="mb-1">{{ $module->name }}</h2>
                                    <span class="badge bg-light text-dark">{{ $module->category }}</span>
                                    @if($isInstalled)
                                        <span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Installed</span>
                                    @endif
                                </div>
                                @if($module->version)
                                    <span class="text-muted">v{{ $module->version }}</span>
                                @endif
                            </div>
                            <p class="lead mt-3">{{ $module->short_description }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5>About this Module</h5>
                    <p class="text-muted">{{ $module->description ?? 'No description available.' }}</p>

                    @if(!empty($module->highlights))
                        <h5 class="mt-4">Key Features</h5>
                        <div class="row">
                            @foreach($module->highlights as $highlight)
                                <div class="col-md-6 mb-2">
                                    <i class="bi bi-check-circle-fill text-success"></i> {{ $highlight }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($module->documentation_url || $module->video_url)
                        <h5 class="mt-4">Resources</h5>
                        <div class="d-flex gap-2">
                            @if($module->documentation_url)
                                <a href="{{ $module->documentation_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-file-text"></i> Documentation
                                </a>
                            @endif
                            @if($module->video_url)
                                <a href="{{ $module->video_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-play-circle"></i> Watch Demo
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Dependencies -->
            @if(!empty($dependencies))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-diagram-2"></i> Dependencies</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">This module requires the following modules to be installed:</p>
                    <div class="list-group">
                        @foreach($dependencies as $dep)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi {{ $dep['icon'] ?? 'bi-box' }}"></i>
                                    {{ $dep['name'] }}
                                    @if($dep['installed'])
                                        <span class="badge bg-success ms-2"><i class="bi bi-check"></i></span>
                                    @endif
                                </div>
                                @if(!$dep['installed'])
                                    @if($dep['can_install'])
                                        <span class="badge bg-warning text-dark">Will be installed</span>
                                    @else
                                        <span class="badge bg-danger">Cannot install</span>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Pricing Card -->
            <div class="card mb-4">
                <div class="card-body">
                    @if($isInstalled)
                        <div class="text-center mb-3">
                            <i class="bi bi-check-circle-fill text-success fs-1"></i>
                            <h5 class="mt-2">Module Installed</h5>
                            @if($subscription && $subscription->isInTrial())
                                <p class="text-warning">
                                    Trial ends {{ $subscription->trial_ends_at->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                        <a href="{{ route('my-modules.index') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-gear"></i> Manage Module
                        </a>
                    @elseif($canInstall)
                        <h5 class="mb-3">Pricing</h5>
                        
                        @if($price->isFree())
                            <div class="text-center mb-3">
                                <span class="display-6 text-success">Free</span>
                            </div>
                        @else
                            @if($price->monthly)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Monthly</span>
                                    <span class="fw-bold">KES {{ number_format($price->monthly) }}</span>
                                </div>
                            @endif
                            @if($price->yearly)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Yearly</span>
                                    <div>
                                        <span class="fw-bold">KES {{ number_format($price->yearly) }}</span>
                                        @if($price->yearlySavingsPercent)
                                            <span class="badge bg-success ms-1">Save {{ $price->yearlySavingsPercent }}%</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($price->setupFee > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2 text-muted">
                                    <small>Setup Fee</small>
                                    <small>KES {{ number_format($price->setupFee) }}</small>
                                </div>
                            @endif
                            <hr>
                        @endif

                        @if($planModule && $planModule->trial_days > 0)
                            <div class="alert alert-info">
                                <i class="bi bi-gift"></i> 
                                {{ $planModule->trial_days }}-day free trial available
                            </div>
                        @endif

                        <a href="{{ route('marketplace.install-form', $module->key) }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg"></i> Install Now
                        </a>
                    @else
                        <div class="text-center py-3">
                            <i class="bi bi-lock fs-1 text-muted"></i>
                            <h5 class="mt-2">Not Available</h5>
                            <p class="text-muted small">This module is not available on your current plan.</p>
                            <a href="{{ route('billing.upgrade') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-arrow-up-circle"></i> Upgrade Plan
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Info Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Module Info</h6>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Category</span>
                        <span class="text-muted">{{ $module->category }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Version</span>
                        <span class="text-muted">{{ $module->version ?? '1.0.0' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Install Time</span>
                        <span class="text-muted">{{ $module->getInstallTimeEstimate() }}</span>
                    </li>
                    @if(!$module->is_free)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Billing</span>
                            <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $module->billing_model)) }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
