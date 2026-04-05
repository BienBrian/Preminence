@props([
    'config',
    'module',
    'onboardingId' => null,
    'submitUrl' => null,
])

@php
$steps = $config->steps()->where('is_active', true)->orderBy('step_number')->get();
$totalSteps = $steps->count();
$submitUrl = $submitUrl ?? route('marketplace.onboarding.submit', $onboardingId);
@endphp

<div class="setup-wizard" id="setupWizard-{{ $module->key }}" data-module="{{ $module->key }}">
    <!-- Progress Header -->
    <div class="wizard-progress mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-1">Setting up {{ $module->name }}</h5>
                @if($config->estimated_setup_time_minutes)
                <small class="text-muted">
                    <i class="bi bi-clock"></i> About {{ $config->estimated_setup_time_minutes }} minutes
                </small>
                @endif
            </div>
            <div class="text-end">
                <span class="badge bg-primary" id="stepIndicator-{{ $module->key }}">
                    Step <span class="current-step">1</span> of {{ $totalSteps }}
                </span>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress" style="height: 8px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                 id="progressBar-{{ $module->key }}"
                 role="progressbar" 
                 style="width: {{ $totalSteps > 0 ? (100 / $totalSteps) : 0 }}%"></div>
        </div>
        
        <!-- Step Indicators -->
        <div class="step-indicators d-flex justify-content-between mt-2">
            @foreach($steps as $index => $step)
            <div class="step-dot {{ $index === 0 ? 'active' : '' }} {{ $index < 0 ? 'completed' : '' }}" 
                 data-step="{{ $index + 1 }}"
                 title="{{ $step->title }}">
                <span class="step-number">{{ $index + 1 }}</span>
                <i class="bi bi-check step-check d-none"></i>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Welcome Message -->
    @if($config->welcome_message)
    <div class="alert alert-info mb-4" id="welcomeMessage-{{ $module->key }}">
        <i class="bi bi-info-circle"></i> {{ $config->welcome_message }}
    </div>
    @endif

    <!-- Wizard Steps Container -->
    <form id="wizardForm-{{ $module->key }}" class="wizard-steps-container">
        @csrf
        <input type="hidden" name="current_step" id="currentStepInput-{{ $module->key }}" value="1">
        
        @foreach($steps as $index => $step)
        <div class="wizard-step {{ $index === 0 ? 'active' : 'd-none' }}" 
             data-step="{{ $index + 1 }}"
             data-step-id="{{ $step->id }}">
            
            <div class="step-content">
                <!-- Step Header -->
                <div class="step-header mb-3">
                    @if($step->icon)
                    <i class="bi {{ $step->icon }} fs-2 text-primary mb-2 d-block"></i>
                    @endif
                    <h4 class="step-title">{{ $step->title }}</h4>
                    @if($step->description)
                    <p class="text-muted">{{ $step->description }}</p>
                    @endif
                </div>

                <!-- Video Content -->
                @if($step->isVideo() && $step->video_url)
                <div class="ratio ratio-16x9 mb-3">
                    <iframe src="{{ $step->video_url }}" allowfullscreen></iframe>
                </div>
                @endif

                <!-- Image Content -->
                @if($step->image_url)
                <div class="mb-3">
                    <img src="{{ $step->image_url }}" class="img-fluid rounded" alt="{{ $step->title }}">
                </div>
                @endif

                <!-- Text Content -->
                @if($step->content)
                <div class="step-description mb-3">
                    {!! nl2br(e($step->content)) !!}
                </div>
                @endif

                <!-- Form Fields -->
                @if($step->isForm() && $step->form_schema)
                <div class="step-form-fields">
                    @foreach($step->form_schema as $field)
                    <div class="mb-3">
                        <label for="field_{{ $step->id }}_{{ $field['name'] }}" class="form-label">
                            {{ $field['label'] ?? $field['name'] }}
                            @if($field['required'] ?? false)
                            <span class="text-danger">*</span>
                            @endif
                        </label>
                        
                        @switch($field['type'])
                            @case('select')
                                <select name="form_data[{{ $field['name'] }}]" 
                                        id="field_{{ $step->id }}_{{ $field['name'] }}"
                                        class="form-select"
                                        {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                    <option value="">Select...</option>
                                    @foreach($field['options'] ?? [] as $value => $label)
                                    <option value="{{ $value }}" {{ ($field['default'] ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @break
                                
                            @case('textarea')
                                <textarea name="form_data[{{ $field['name'] }}]"
                                          id="field_{{ $step->id }}_{{ $field['name'] }}"
                                          class="form-control"
                                          rows="3"
                                          placeholder="{{ $field['placeholder'] ?? '' }}"
                                          {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ $field['default'] ?? '' }}</textarea>
                                @break
                                
                            @case('checkbox')
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="form_data[{{ $field['name'] }}]"
                                           id="field_{{ $step->id }}_{{ $field['name'] }}"
                                           class="form-check-input"
                                           value="1"
                                           {{ ($field['default'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="field_{{ $step->id }}_{{ $field['name'] }}">
                                        {{ $field['label'] }}
                                    </label>
                                </div>
                                @break
                                
                            @case('number')
                                <input type="number" 
                                       name="form_data[{{ $field['name'] }}]"
                                       id="field_{{ $step->id }}_{{ $field['name'] }}"
                                       class="form-control"
                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                       value="{{ $field['default'] ?? '' }}"
                                       {{ isset($field['min']) ? 'min='.$field['min'] : '' }}
                                       {{ isset($field['max']) ? 'max='.$field['max'] : '' }}
                                       {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                @break
                                
                            @default
                                <input type="{{ $field['type'] }}" 
                                       name="form_data[{{ $field['name'] }}]"
                                       id="field_{{ $step->id }}_{{ $field['name'] }}"
                                       class="form-control"
                                       placeholder="{{ $field['placeholder'] ?? '' }}"
                                       value="{{ $field['default'] ?? '' }}"
                                       {{ ($field['required'] ?? false) ? 'required' : '' }}>
                        @endswitch
                        
                        @if(isset($field['help']))
                        <small class="form-text text-muted">{{ $field['help'] }}</small>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Document Upload -->
                @if($step->isDocumentUpload())
                <div class="document-upload-area border border-dashed rounded p-4 text-center">
                    <i class="bi bi-cloud-upload fs-2 text-muted"></i>
                    <p class="mt-2 mb-0">Drag and drop files here or click to browse</p>
                    <small class="text-muted">Accepted: PDF, JPG, PNG (max 10MB)</small>
                </div>
                @endif

                <!-- Completion Step -->
                @if($step->isCompletion())
                <div class="text-center py-4">
                    <i class="bi bi-check-circle-fill fs-1 text-success"></i>
                    <h5 class="mt-3">Setup Complete!</h5>
                    @if($config->completion_message)
                    <p class="text-muted">{{ $config->completion_message }}</p>
                    @endif
                </div>
                @endif

                <!-- Estimated Time -->
                @if($step->estimated_minutes)
                <div class="text-muted small mt-3">
                    <i class="bi bi-clock"></i> Takes about {{ $step->estimated_minutes }} minute{{ $step->estimated_minutes > 1 ? 's' : '' }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </form>

    <!-- Navigation Buttons -->
    <div class="wizard-navigation mt-4 pt-3 border-top">
        <div class="d-flex justify-content-between">
            <button type="button" 
                    class="btn btn-outline-secondary btn-back"
                    id="btnBack-{{ $module->key }}"
                    onclick="wizardBack('{{ $module->key }}')"
                    disabled>
                <i class="bi bi-arrow-left"></i> Back
            </button>
            
            <div>
                @if(!$config->steps->first()?->is_required)
                <button type="button" 
                        class="btn btn-link text-muted me-2"
                        onclick="wizardSkip('{{ $module->key }}')">
                    Skip
                </button>
                @endif
                
                <button type="button" 
                        class="btn btn-primary btn-next"
                        id="btnNext-{{ $module->key }}"
                        onclick="wizardNext('{{ $module->key }}')">
                    Next <i class="bi bi-arrow-right"></i>
                </button>
                
                <button type="button" 
                        class="btn btn-success btn-finish d-none"
                        id="btnFinish-{{ $module->key }}"
                        onclick="wizardFinish('{{ $module->key }}', '{{ $submitUrl }}')">
                    <i class="bi bi-check-circle"></i> Finish Setup
                </button>
            </div>
        </div>
    </div>

    <!-- Save Progress Indicator -->
    <div class="autosave-indicator text-muted small mt-2" id="autosave-{{ $module->key }}">
        <i class="bi bi-check-circle"></i> Progress auto-saved
    </div>
</div>

<style>
.setup-wizard {
    max-width: 800px;
    margin: 0 auto;
}

.step-indicators {
    position: relative;
}

.step-indicators::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: #dee2e6;
    z-index: 0;
}

.step-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
    cursor: default;
    transition: all 0.3s ease;
}

.step-dot.active {
    border-color: #0d6efd;
    background: #0d6efd;
    color: #fff;
}

.step-dot.completed {
    border-color: #198754;
    background: #198754;
    color: #fff;
}

.step-dot .step-check {
    display: none;
}

.step-dot.completed .step-number {
    display: none;
}

.step-dot.completed .step-check {
    display: block;
}

.wizard-step {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

.document-upload-area {
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.2s;
}

.document-upload-area:hover {
    background: #e9ecef;
    border-color: #0d6efd !important;
}
</style>

<script>
function wizardNext(moduleKey) {
    const wizard = document.getElementById('setupWizard-' + moduleKey);
    const currentStepEl = wizard.querySelector('.wizard-step.active');
    const currentStep = parseInt(currentStepEl.dataset.step);
    const totalSteps = parseInt(wizard.querySelectorAll('.wizard-step').length);
    
    // Validate current step
    if (!validateStep(currentStepEl)) {
        return;
    }
    
    // Hide current step
    currentStepEl.classList.add('d-none');
    currentStepEl.classList.remove('active');
    
    // Show next step
    const nextStep = currentStep + 1;
    const nextStepEl = wizard.querySelector('.wizard-step[data-step="' + nextStep + '"]');
    if (nextStepEl) {
        nextStepEl.classList.remove('d-none');
        nextStepEl.classList.add('active');
        
        // Update UI
        updateWizardUI(moduleKey, nextStep, totalSteps);
        
        // Auto-save progress
        autoSaveProgress(moduleKey, nextStep);
    }
}

function wizardBack(moduleKey) {
    const wizard = document.getElementById('setupWizard-' + moduleKey);
    const currentStepEl = wizard.querySelector('.wizard-step.active');
    const currentStep = parseInt(currentStepEl.dataset.step);
    const totalSteps = parseInt(wizard.querySelectorAll('.wizard-step').length);
    
    // Hide current step
    currentStepEl.classList.add('d-none');
    currentStepEl.classList.remove('active');
    
    // Show previous step
    const prevStep = currentStep - 1;
    const prevStepEl = wizard.querySelector('.wizard-step[data-step="' + prevStep + '"]');
    if (prevStepEl) {
        prevStepEl.classList.remove('d-none');
        prevStepEl.classList.add('active');
        
        // Update UI
        updateWizardUI(moduleKey, prevStep, totalSteps);
    }
}

function updateWizardUI(moduleKey, currentStep, totalSteps) {
    // Update progress bar
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('progressBar-' + moduleKey).style.width = progress + '%';
    
    // Update step indicator
    document.querySelector('#stepIndicator-' + moduleKey + ' .current-step').textContent = currentStep;
    
    // Update step dots
    const dots = document.querySelectorAll('#setupWizard-' + moduleKey + ' .step-dot');
    dots.forEach((dot, index) => {
        const stepNum = index + 1;
        dot.classList.remove('active', 'completed');
        if (stepNum < currentStep) {
            dot.classList.add('completed');
        } else if (stepNum === currentStep) {
            dot.classList.add('active');
        }
    });
    
    // Update buttons
    const btnBack = document.getElementById('btnBack-' + moduleKey);
    const btnNext = document.getElementById('btnNext-' + moduleKey);
    const btnFinish = document.getElementById('btnFinish-' + moduleKey);
    
    btnBack.disabled = currentStep === 1;
    
    if (currentStep === totalSteps) {
        btnNext.classList.add('d-none');
        btnFinish.classList.remove('d-none');
    } else {
        btnNext.classList.remove('d-none');
        btnFinish.classList.add('d-none');
    }
    
    // Update hidden input
    document.getElementById('currentStepInput-' + moduleKey).value = currentStep;
}

function validateStep(stepEl) {
    const requiredFields = stepEl.querySelectorAll('[required]');
    let valid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            valid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!valid) {
        // Show validation message
        Swal.fire({
            icon: 'warning',
            title: 'Required Fields',
            text: 'Please fill in all required fields before continuing.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }
    
    return valid;
}

function autoSaveProgress(moduleKey, step) {
    // Show saving indicator
    const indicator = document.getElementById('autosave-' + moduleKey);
    indicator.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Saving...';
    
    // Simulate auto-save (in real implementation, make AJAX call)
    setTimeout(() => {
        indicator.innerHTML = '<i class="bi bi-check-circle"></i> Progress saved';
    }, 500);
}

function wizardSkip(moduleKey) {
    Swal.fire({
        title: 'Skip Setup?',
        text: 'You can complete this setup later from module settings.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Skip',
        cancelButtonText: 'Continue Setup'
    }).then((result) => {
        if (result.isConfirmed) {
            // Close modal and mark as skipped
            $('#moduleActivationModal').modal('hide');
        }
    });
}

function wizardFinish(moduleKey, submitUrl) {
    const wizard = document.getElementById('setupWizard-' + moduleKey);
    const form = document.getElementById('wizardForm-' + moduleKey);
    
    // Validate final step
    const currentStepEl = wizard.querySelector('.wizard-step.active');
    if (!validateStep(currentStepEl)) {
        return;
    }
    
    // Collect all form data
    const formData = new FormData(form);
    
    // Show loading
    const btnFinish = document.getElementById('btnFinish-' + moduleKey);
    btnFinish.disabled = true;
    btnFinish.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Completing...';
    
    // Submit
    $.ajax({
        url: submitUrl,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'activated' || response.status === 'pending') {
                $('#moduleActivationModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Setup Complete!',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                setTimeout(() => location.reload(), 2000);
            }
        },
        error: function(xhr) {
            btnFinish.disabled = false;
            btnFinish.innerHTML = '<i class="bi bi-check-circle"></i> Finish Setup';
            
            const error = xhr.responseJSON?.error || 'Setup failed. Please try again.';
            Swal.fire({
                icon: 'error',
                title: 'Setup Failed',
                text: error
            });
        }
    });
}
</script>
