@extends('dashboard.layouts.app')

@section('title', $subscription->module->name ?? $subscription->module_key . ' - Settings')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-3">
            {{-- Sidebar --}}
            <div class="list-group mb-4">
                <a href="{{ route('my-modules.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-arrow-left me-2"></i>Back to My Modules
                </a>
            </div>
            
            <div class="card">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi {{ $module->icon ?? 'bi-box' }} fs-2 text-primary"></i>
                    </div>
                    <h5 class="mb-1">{{ $module->name ?? $subscription->module_key }}</h5>
                    <p class="text-muted small mb-0">{{ $subscription->getBillingPeriodLabel() }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <h2 class="h4 mb-4">Module Settings</h2>
            
            {{-- Features Section --}}
            @if($module && !empty($module->features))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Features</h5>
                </div>
                <div class="card-body">
                    @foreach($module->features as $featureKey => $featureConfig)
                    @php
                        $features = $subscription->features_enabled ?? [];
                        $isEnabled = $features[$featureKey] ?? ($featureConfig['default'] ?? true);
                    @endphp
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div>
                            <h6 class="mb-0">{{ $featureConfig['label'] ?? ucfirst($featureKey) }}</h6>
                            <small class="text-muted">{{ $featureConfig['description'] ?? '' }}</small>
                        </div>
                        <form action="{{ route('my-modules.toggle-feature', [$subscription, 'feature' => $featureKey]) }}" method="POST">
                            @csrf
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                    onchange="this.form.submit()"
                                    {{ $isEnabled ? 'checked' : '' }}>
                            </div>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Limits Section --}}
            @if($module && !empty($module->default_limits))
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Usage Limits</h5>
                </div>
                <div class="card-body">
                    @foreach($module->default_limits as $limitKey => $limitConfig)
                    @php
                        $limits = $subscription->limits ?? [];
                        $currentLimit = $limits[$limitKey] ?? ($limitConfig['default'] ?? 0);
                        $usage = ($subscription->usage_metrics ?? [])[$limitKey] ?? 0;
                        $percentage = $currentLimit > 0 ? min(100, ($usage / $currentLimit) * 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $limitConfig['label'] ?? ucfirst($limitKey) }}</span>
                            <span class="text-muted">{{ $usage }} / {{ $currentLimit > 0 ? $currentLimit : '∞' }}</span>
                        </div>
                        @if($currentLimit > 0)
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success') }}" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Custom Settings Form --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('my-modules.update-settings', $subscription) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Notification Preferences</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                    name="settings[email_notifications]" 
                                    id="email_notifications"
                                    {{ ($subscription->settings['email_notifications'] ?? true) ? 'checked' : '' }}
                                    value="1">
                                <label class="form-check-label" for="email_notifications">
                                    Email notifications for this module
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="custom_settings" class="form-label">Additional Settings (JSON)</label>
                            <textarea class="form-control font-monospace" id="custom_settings" name="settings[custom]" rows="5" placeholder='{"key": "value"}'>{{ json_encode($subscription->settings['custom'] ?? [], JSON_PRETTY_PRINT) }}</textarea>
                            <div class="form-text">Advanced configuration in JSON format</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
