@extends('superadmin.layouts.app')

@section('title', 'Edit Module: ' . $module->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Module: {{ $module->name }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('superadmin.modules.update', $module) }}">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Module Key</label>
                                <input type="text" class="form-control" value="{{ $module->key }}" disabled>
                                <small class="text-muted">Unique identifier (cannot change)</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Display Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $module->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->slug }}" {{ old('category', $module->category) == $category->slug ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icon Class</label>
                                <input type="text" name="icon" class="form-control" 
                                       value="{{ old('icon', $module->icon) }}" placeholder="bi-box">
                                <small class="text-muted">Bootstrap Icons class (e.g., bi-box, bi-cash)</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Short Description</label>
                            <input type="text" name="short_description" class="form-control" 
                                   value="{{ old('short_description', $module->short_description) }}" maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', $module->description) }}</textarea>
                        </div>

                        <!-- Pricing Section -->
                        <h6 class="mt-4 mb-3 border-bottom pb-2">Pricing</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_free" class="form-check-input" id="is_free" 
                                           value="1" {{ old('is_free', $module->is_free) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_free">Free Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Monthly Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" name="price_monthly" class="form-control" step="0.01" min="0"
                                           value="{{ old('price_monthly', $module->price_monthly) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Yearly Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" name="price_yearly" class="form-control" step="0.01" min="0"
                                           value="{{ old('price_yearly', $module->price_yearly) }}">
                                </div>
                                @if($module->yearlySavingsPercent)
                                    <small class="text-success">Save {{ $module->getYearlySavingsPercent() }}% with yearly</small>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Setup Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">KES</span>
                                    <input type="number" name="setup_fee" class="form-control" step="0.01" min="0"
                                           value="{{ old('setup_fee', $module->setup_fee) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Billing Model</label>
                                <select name="billing_model" class="form-select">
                                    <option value="flat" {{ old('billing_model', $module->billing_model) == 'flat' ? 'selected' : '' }}>Flat Rate</option>
                                    <option value="per_user" {{ old('billing_model', $module->billing_model) == 'per_user' ? 'selected' : '' }}>Per User</option>
                                    <option value="usage_based" {{ old('billing_model', $module->billing_model) == 'usage_based' ? 'selected' : '' }}>Usage Based</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dependencies Section -->
                        <h6 class="mt-4 mb-3 border-bottom pb-2">Dependencies</h6>
                        <div class="mb-3">
                            <label class="form-label">Required Modules</label>
                            <select name="dependencies[]" class="form-select" multiple size="4">
                                @foreach($allModules as $key => $name)
                                    @if($key !== $module->key)
                                        <option value="{{ $key }}" {{ in_array($key, old('dependencies', $module->dependencies ?? [])) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Conflicting Modules</label>
                            <select name="conflicts[]" class="form-select" multiple size="3">
                                @foreach($allModules as $key => $name)
                                    @if($key !== $module->key)
                                        <option value="{{ $key }}" {{ in_array($key, old('conflicts', $module->conflicts ?? [])) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Settings Section -->
                        <h6 class="mt-4 mb-3 border-bottom pb-2">Settings</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                                           value="1" {{ old('is_active', $module->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_public" class="form-check-input" id="is_public" 
                                           value="1" {{ old('is_public', $module->is_public) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_public">Public</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="requires_approval" class="form-check-input" id="requires_approval" 
                                           value="1" {{ old('requires_approval', $module->requires_approval) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_approval">Requires Approval</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('superadmin.modules.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Module</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Stats -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Module Statistics</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Active Installations:</td>
                            <td class="text-end"><strong>{{ $tenantStats['active_tenants'] ?? 0 }}</strong></td>
                        </tr>
                        <tr>
                            <td>Total Installations:</td>
                            <td class="text-end"><strong>{{ $tenantStats['total_installs'] ?? 0 }}</strong></td>
                        </tr>
                        <tr>
                            <td>Trial Installations:</td>
                            <td class="text-end"><strong>{{ $tenantStats['trial_installs'] ?? 0 }}</strong></td>
                        </tr>
                        <tr>
                            <td>Recent (30 days):</td>
                            <td class="text-end"><strong>{{ $tenantStats['recent_installs'] ?? 0 }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Onboarding Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Onboarding</h6>
                </div>
                <div class="card-body">
                    @php
                        $onboardingConfig = \App\Models\ModuleOnboardingConfig::where('module_key', $module->key)->first();
                        $isConfigured = $onboardingConfig?->is_configured ?? false;
                    @endphp
                    
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-{{ $isConfigured ? 'success' : 'warning' }} me-2">
                            {{ $isConfigured ? 'Configured' : 'Not Configured' }}
                        </span>
                        @if($onboardingConfig)
                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $onboardingConfig->onboarding_type)) }}</small>
                        @endif
                    </div>
                    
                    <a href="{{ route('superadmin.modules.onboarding.edit', $module) }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-rocket-takeoff"></i> 
                        {{ $isConfigured ? 'Edit Onboarding' : 'Configure Onboarding' }}
                    </a>
                    @if($isConfigured)
                    <a href="{{ route('superadmin.modules.onboarding.preview', $module) }}" class="btn btn-outline-info w-100 mt-2" target="_blank">
                        <i class="bi bi-eye"></i> Preview
                    </a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Version Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label small">Current Version</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $module->version }}" disabled>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Min Platform Version</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $module->min_platform_version ?? 'N/A' }}" disabled>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
