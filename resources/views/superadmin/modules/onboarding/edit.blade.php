@extends('superadmin.layouts.app')

@section('title', 'Onboarding: ' . $module->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.index') }}">Modules</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.edit', $module) }}">{{ $module->name }}</a></li>
                    <li class="breadcrumb-item active">Onboarding Configuration</li>
                </ol>
            </nav>
            <h4>
                <i class="bi bi-rocket-takeoff"></i> 
                Onboarding Configuration
                <span class="badge bg-{{ $config->is_configured ? 'success' : 'warning' }}">
                    {{ $config->is_configured ? 'Configured' : 'Not Configured' }}
                </span>
            </h4>
            <p class="text-muted mb-0">Define how tenants experience this module when they activate it</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('superadmin.modules.onboarding.preview', $module) }}" class="btn btn-outline-info" target="_blank">
                <i class="bi bi-eye"></i> Preview
            </a>
            <a href="{{ route('superadmin.modules.edit', $module) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Module
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Main Configuration Panel -->
        <div class="col-lg-8">
            <form id="onboardingForm" method="POST" action="{{ route('superadmin.modules.onboarding.update', $module) }}">
                @csrf
                @method('PUT')

                <!-- Onboarding Type Selection -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-signpost-split"></i> Onboarding Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check onboarding-type-card p-3 border rounded {{ $config->onboarding_type === 'instant' ? 'border-primary bg-light' : '' }}" data-type="instant">
                                    <input class="form-check-input" type="radio" name="onboarding_type" id="type_instant" value="instant" {{ $config->onboarding_type === 'instant' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="type_instant">
                                        <div class="text-center">
                                            <i class="bi bi-lightning-charge fs-2 text-warning"></i>
                                            <div class="fw-bold mt-2">Instant</div>
                                            <small class="text-muted">No setup needed</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check onboarding-type-card p-3 border rounded {{ $config->onboarding_type === 'guided' ? 'border-primary bg-light' : '' }}" data-type="guided">
                                    <input class="form-check-input" type="radio" name="onboarding_type" id="type_guided" value="guided" {{ $config->onboarding_type === 'guided' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="type_guided">
                                        <div class="text-center">
                                            <i class="bi bi-book fs-2 text-info"></i>
                                            <div class="fw-bold mt-2">Guided</div>
                                            <small class="text-muted">Tutorial steps</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check onboarding-type-card p-3 border rounded {{ $config->onboarding_type === 'setup_wizard' ? 'border-primary bg-light' : '' }}" data-type="setup_wizard">
                                    <input class="form-check-input" type="radio" name="onboarding_type" id="type_setup_wizard" value="setup_wizard" {{ $config->onboarding_type === 'setup_wizard' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="type_setup_wizard">
                                        <div class="text-center">
                                            <i class="bi bi-magic fs-2 text-primary"></i>
                                            <div class="fw-bold mt-2">Setup Wizard</div>
                                            <small class="text-muted">Configuration forms</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check onboarding-type-card p-3 border rounded {{ $config->onboarding_type === 'kyc' ? 'border-primary bg-light' : '' }}" data-type="kyc">
                                    <input class="form-check-input" type="radio" name="onboarding_type" id="type_kyc" value="kyc" {{ $config->onboarding_type === 'kyc' ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="type_kyc">
                                        <div class="text-center">
                                            <i class="bi bi-shield-check fs-2 text-success"></i>
                                            <div class="fw-bold mt-2">KYC/Approval</div>
                                            <small class="text-muted">Document + approval</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Template Selection -->
                        <div class="mt-4 pt-3 border-top">
                            <label class="form-label">Quick Start with Template</label>
                            <div class="row g-2">
                                @foreach($templates as $key => $template)
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-outline-secondary w-100 text-start apply-template-btn" data-template="{{ $key }}">
                                        <i class="bi {{ $template['icon'] }}"></i>
                                        <span class="ms-2">{{ $template['name'] }}</span>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Common Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> General Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Welcome Message</label>
                                <textarea name="welcome_message" class="form-control" rows="2" placeholder="Welcome message shown at the start of onboarding">{{ $config->welcome_message }}</textarea>
                                <small class="form-text text-muted">Shown to users when they start onboarding</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Completion Message</label>
                                <textarea name="completion_message" class="form-control" rows="2" placeholder="Message shown after successful onboarding">{{ $config->completion_message }}</textarea>
                                <small class="form-text text-muted">Shown after onboarding is complete</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estimated Setup Time (minutes)</label>
                                <input type="number" name="estimated_setup_time_minutes" class="form-control" value="{{ $config->estimated_setup_time_minutes }}" min="1" max="120">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Explainer Video URL</label>
                                <input type="url" name="video_url" class="form-control" value="{{ $config->video_url }}" placeholder="https://...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Documentation URL</label>
                                <input type="url" name="documentation_url" class="form-control" value="{{ $config->documentation_url }}" placeholder="https://...">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requires_approval" id="requires_approval" value="1" {{ $config->requires_approval ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_approval">Requires Approval</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="preview_enabled" id="preview_enabled" value="1" {{ $config->preview_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="preview_enabled">Allow Preview</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="auto_redirect_to_module" id="auto_redirect_to_module" value="1" {{ $config->auto_redirect_to_module ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_redirect_to_module">Auto-redirect to Module</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="contextual_help_enabled" id="contextual_help_enabled" value="1" {{ $config->contextual_help_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="contextual_help_enabled">Contextual Help</label>
                                </div>
                            </div>
                        </div>

                        <div id="approvalInstructionsSection" class="mt-3 {{ $config->requires_approval ? '' : 'd-none' }}">
                            <label class="form-label">Approval Instructions for SuperAdmin</label>
                            <textarea name="approval_instructions" class="form-control" rows="3" placeholder="Instructions for SuperAdmin when reviewing applications">{{ $config->approval_instructions }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Type-Specific Configuration -->
                <div id="typeSpecificConfig">
                    <!-- Guided Tutorial Builder -->
                    <div id="guidedConfig" class="config-section {{ $config->onboarding_type === 'guided' ? '' : 'd-none' }}">
                        @include('superadmin.modules.onboarding._guided_builder', ['config' => $config])
                    </div>

                    <!-- Setup Wizard Builder -->
                    <div id="setupWizardConfig" class="config-section {{ $config->onboarding_type === 'setup_wizard' ? '' : 'd-none' }}">
                        @include('superadmin.modules.onboarding._wizard_builder', ['config' => $config])
                    </div>

                    <!-- KYC Configuration -->
                    <div id="kycConfig" class="config-section {{ $config->onboarding_type === 'kyc' ? '' : 'd-none' }}">
                        @include('superadmin.modules.onboarding._kyc_builder', ['config' => $config])
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Save Onboarding Configuration
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Stats & Info -->
        <div class="col-lg-4">
            <!-- Stats Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Onboarding Stats</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Steps</span>
                        <span class="badge bg-primary">{{ $config->getTotalSteps() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Est. Time</span>
                        <span>{{ $config->getEstimatedTimeLabel() ?? 'Not set' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Current Type</span>
                        <span class="badge bg-{{ $config->onboarding_type === 'instant' ? 'secondary' : ($config->onboarding_type === 'kyc' ? 'success' : 'info') }}">
                            {{ ucfirst(str_replace('_', ' ', $config->onboarding_type)) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated</span>
                        <span>{{ $config->updated_at?->diffForHumans() ?? 'Never' }}</span>
                    </div>
                </div>
            </div>

            <!-- Distribution Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-pie-chart"></i> System Overview</h6>
                </div>
                <div class="card-body">
                    @foreach($typeStats as $type => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-circle-fill text-{{ $type === 'instant' ? 'secondary' : ($type === 'guided' ? 'info' : ($type === 'kyc' ? 'success' : 'primary')) }}"></i> {{ ucfirst($type) }}</span>
                        <span class="badge bg-light text-dark">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Help Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-question-circle"></i> Need Help?</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2">
                            <strong>Instant:</strong> Module activates immediately with no setup
                        </li>
                        <li class="mb-2">
                            <strong>Guided:</strong> Shows tutorial steps before activation
                        </li>
                        <li class="mb-2">
                            <strong>Setup Wizard:</strong> Collects configuration data step-by-step
                        </li>
                        <li>
                            <strong>KYC:</strong> Requires documents and SuperAdmin approval
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.onboarding-type-card {
    cursor: pointer;
    transition: all 0.2s;
}
.onboarding-type-card:hover {
    border-color: var(--bs-primary) !important;
    background-color: var(--bs-light) !important;
}
.onboarding-type-card.border-primary {
    border-width: 2px !important;
}
.form-check-input:checked + .form-check-label .onboarding-type-card {
    border-color: var(--bs-primary) !important;
}
</style>

@push('scripts')
<script>
$(function() {
    // Onboarding type selection
    $('input[name="onboarding_type"]').on('change', function() {
        const type = $(this).val();
        
        // Update card styling
        $('.onboarding-type-card').removeClass('border-primary bg-light');
        $(this).closest('.onboarding-type-card').addClass('border-primary bg-light');
        
        // Show/hide relevant config sections
        $('.config-section').addClass('d-none');
        if (type === 'guided') {
            $('#guidedConfig').removeClass('d-none');
        } else if (type === 'setup_wizard') {
            $('#setupWizardConfig').removeClass('d-none');
        } else if (type === 'kyc') {
            $('#kycConfig').removeClass('d-none');
        }
        
        // Update approval instructions visibility
        if (type === 'kyc') {
            $('#requires_approval').prop('checked', true).prop('disabled', true);
            $('#approvalInstructionsSection').removeClass('d-none');
        } else {
            $('#requires_approval').prop('disabled', false);
        }
    });
    
    // Requires approval toggle
    $('#requires_approval').on('change', function() {
        $('#approvalInstructionsSection').toggleClass('d-none', !$(this).is(':checked'));
    });
    
    // Template application
    $('.apply-template-btn').on('click', function() {
        const template = $(this).data('template');
        if (confirm('This will replace your current configuration with the template. Continue?')) {
            const form = $('<form method="POST" action="{{ route('superadmin.modules.onboarding.apply-template', $module) }}">')
                .append('@csrf')
                .append($('<input name="template_key">').val(template));
            $('body').append(form);
            form.submit();
        }
    });
});
</script>
@endpush
@endsection
