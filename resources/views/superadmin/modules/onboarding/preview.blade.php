@extends('superadmin.layouts.app')

@section('title', 'Preview: ' . $module->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.index') }}">Modules</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.edit', $module) }}">{{ $module->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.modules.onboarding.edit', $module) }}">Onboarding</a></li>
                    <li class="breadcrumb-item active">Preview</li>
                </ol>
            </nav>
            <h4>
                <i class="bi bi-eye"></i> 
                Onboarding Preview
            </h4>
            <p class="text-muted mb-0">This is how tenants will see the onboarding experience</p>
        </div>
        <div>
            <a href="{{ route('superadmin.modules.onboarding.edit', $module) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Editor
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Preview Info -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Preview Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Module</td>
                            <td class="fw-bold">{{ $module->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Onboarding Type</td>
                            <td>
                                <span class="badge bg-{{ $config->onboarding_type === 'instant' ? 'secondary' : ($config->onboarding_type === 'kyc' ? 'success' : 'info') }}">
                                    {{ ucfirst(str_replace('_', ' ', $config->onboarding_type)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Steps</td>
                            <td>{{ $config->getTotalSteps() }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Est. Time</td>
                            <td>{{ $config->getEstimatedTimeLabel() ?? 'Not set' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Requires Approval</td>
                            <td>{{ $config->requires_approval ? 'Yes' : 'No' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Preview Mode</strong>
                <p class="mb-0 small">This preview shows how the onboarding will appear to tenants. No data is saved.</p>
            </div>
        </div>

        <!-- Preview Container -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-phone"></i> 
                        Tenant View Simulation
                    </h6>
                </div>
                <div class="card-body p-0">
                    <!-- Onboarding Preview -->
                    <div id="onboardingPreview" class="p-4">
                        @if($config->onboarding_type === 'instant')
                            <!-- Instant Preview -->
                            <div class="text-center py-5">
                                <i class="bi bi-lightning-charge fs-1 text-warning"></i>
                                <h5 class="mt-3">Module Activated!</h5>
                                <p class="text-muted">This module activates instantly with no setup required.</p>
                                <button class="btn btn-primary" disabled>
                                    <i class="bi bi-check-circle"></i> Activated
                                </button>
                            </div>

                        @elseif($config->onboarding_type === 'guided')
                            <!-- Guided Tutorial Preview -->
                            <div class="guided-preview">
                                @if($config->welcome_message)
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle"></i> {{ $config->welcome_message }}
                                </div>
                                @endif

                                @if($config->video_url)
                                <div class="ratio ratio-16x9 mb-4">
                                    <iframe src="{{ $config->video_url }}" allowfullscreen></iframe>
                                </div>
                                @endif

                                <h5 class="mb-3">Getting Started</h5>
                                
                                @foreach($config->getTutorialSteps() as $index => $step)
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary rounded-circle" style="width: 32px; height: 32px; line-height: 24px;">
                                            {{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6><i class="bi {{ $step['icon'] ?? 'bi-circle' }}"></i> {{ $step['title'] }}</h6>
                                        <p class="text-muted mb-0">{{ $step['content'] }}</p>
                                    </div>
                                </div>
                                @endforeach

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                                    <button class="btn btn-outline-secondary" disabled>
                                        Skip Tutorial
                                    </button>
                                    <button class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Activate Module
                                    </button>
                                </div>

                                @if($config->completion_message)
                                <div class="alert alert-success mt-4 d-none" id="completionMessage">
                                    <i class="bi bi-check-circle"></i> {{ $config->completion_message }}
                                </div>
                                @endif
                            </div>

                        @elseif($config->onboarding_type === 'setup_wizard')
                            <!-- Setup Wizard Preview -->
                            <div class="wizard-preview">
                                <!-- Progress Bar -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Setup Progress</span>
                                        <span>Step 1 of {{ $config->steps->count() }}</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: {{ $config->steps->count() > 0 ? (100 / $config->steps->count()) : 0 }}%"></div>
                                    </div>
                                </div>

                                @if($config->welcome_message)
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle"></i> {{ $config->welcome_message }}
                                </div>
                                @endif

                                @php $firstStep = $config->steps->first(); @endphp
                                @if($firstStep)
                                <div class="wizard-step active">
                                    <h5>{{ $firstStep->title }}</h5>
                                    @if($firstStep->description)
                                    <p class="text-muted">{{ $firstStep->description }}</p>
                                    @endif
                                    
                                    @if($firstStep->content)
                                    <div class="mb-3">{{ $firstStep->content }}</div>
                                    @endif

                                    @if($firstStep->isForm() && $firstStep->form_schema)
                                    <div class="border rounded p-3 bg-light">
                                        @foreach($firstStep->form_schema as $field)
                                        <div class="mb-3">
                                            <label class="form-label">
                                                {{ $field['label'] ?? $field['name'] }}
                                                @if($field['required'] ?? false)
                                                <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            @if($field['type'] === 'select')
                                            <select class="form-select" disabled>
                                                <option>Select...</option>
                                                @foreach($field['options'] ?? [] as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($field['type'] === 'textarea')
                                            <textarea class="form-control" rows="3" disabled placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                                            @elseif($field['type'] === 'checkbox')
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" disabled {{ ($field['default'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $field['label'] }}</label>
                                            </div>
                                            @else
                                            <input type="{{ $field['type'] }}" class="form-control" disabled placeholder="{{ $field['placeholder'] ?? '' }}">
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="bi bi-arrow-left"></i> Back
                                    </button>
                                    <button class="btn btn-primary">
                                        Next <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>
                            </div>

                        @elseif($config->onboarding_type === 'kyc')
                            <!-- KYC Preview -->
                            <div class="kyc-preview">
                                <div class="alert alert-warning">
                                    <i class="bi bi-shield-exclamation"></i>
                                    <strong>Verification Required</strong>
                                    <p class="mb-0 small">This module requires document verification. Your application will be reviewed within 1-2 business days.</p>
                                </div>

                                @if($config->welcome_message)
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> {{ $config->welcome_message }}
                                </div>
                                @endif

                                <h5 class="mb-3">Required Documents</h5>
                                
                                @foreach($config->getDocumentsList() as $key => $doc)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                    {{ $doc['label'] }}
                                                    @if($doc['required'] ?? true)
                                                    <span class="text-danger">*</span>
                                                    @endif
                                                </h6>
                                                <p class="text-muted small mb-0">{{ $doc['description'] ?? '' }}</p>
                                                <small class="text-muted">Accepted: {{ implode(', ', $doc['accepted_types'] ?? ['pdf', 'jpg', 'png']) }}</small>
                                            </div>
                                            <button class="btn btn-outline-primary btn-sm" disabled>
                                                <i class="bi bi-upload"></i> Upload
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                @if($config->kyc_form_schema && count($config->kyc_form_schema) > 0)
                                <h5 class="mb-3 mt-4">Additional Information</h5>
                                <div class="border rounded p-3 bg-light">
                                    @foreach($config->kyc_form_schema as $field)
                                    <div class="mb-3">
                                        <label class="form-label">
                                            {{ $field['label'] ?? $field['name'] }}
                                            @if($field['required'] ?? false)
                                            <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        @if($field['type'] === 'select')
                                        <select class="form-select" disabled>
                                            <option>Select...</option>
                                            @foreach($field['options'] ?? [] as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @elseif($field['type'] === 'textarea')
                                        <textarea class="form-control" rows="3" disabled placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
                                        @else
                                        <input type="{{ $field['type'] }}" class="form-control" disabled placeholder="{{ $field['placeholder'] ?? '' }}">
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                                @endif

                                <div class="d-grid gap-2 mt-4 pt-3 border-top">
                                    <button class="btn btn-primary btn-lg" disabled>
                                        <i class="bi bi-send"></i> Submit for Review
                                    </button>
                                    <p class="text-muted small text-center mb-0">
                                        <i class="bi bi-clock"></i> Estimated review time: 1-2 business days
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
