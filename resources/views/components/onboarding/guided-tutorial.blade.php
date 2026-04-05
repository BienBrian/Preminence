@props([
    'config',
    'module',
    'onboardingId' => null,
])

@php
$steps = $config->getTutorialSteps();
$totalSteps = count($steps);
@endphp

<div class="guided-tutorial" id="guidedTutorial-{{ $module->key }}" data-module="{{ $module->key }}">
    <!-- Progress Indicator -->
    <div class="tutorial-progress mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                @foreach($steps as $index => $step)
                <div class="progress-dot {{ $index === 0 ? 'active' : '' }}" data-step="{{ $index + 1 }}"></div>
                @endforeach
            </div>
            <span class="text-muted small">
                <span class="current-step">1</span> / {{ $totalSteps }}
            </span>
        </div>
    </div>

    <!-- Welcome Message -->
    @if($config->welcome_message)
    <div class="alert alert-info mb-4 tutorial-welcome">
        <i class="bi bi-info-circle"></i> {{ $config->welcome_message }}
    </div>
    @endif

    <!-- Video (if exists) -->
    @if($config->video_url)
    <div class="tutorial-video mb-4">
        <div class="ratio ratio-16x9">
            <iframe src="{{ $config->video_url }}" allowfullscreen></iframe>
        </div>
    </div>
    @endif

    <!-- Tutorial Steps -->
    <div class="tutorial-steps-container">
        @foreach($steps as $index => $step)
        <div class="tutorial-step {{ $index === 0 ? 'active' : 'd-none' }}" data-step="{{ $index + 1 }}">
            <div class="step-content text-center py-3">
                @if(isset($step['icon']))
                <div class="step-icon mb-3">
                    <i class="bi {{ $step['icon'] }} fs-1 text-primary"></i>
                </div>
                @endif
                
                <h4 class="step-title mb-3">{{ $step['title'] }}</h4>
                
                <div class="step-description text-muted">
                    {{ $step['content'] }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Completion Message -->
    <div class="tutorial-completion text-center py-4 d-none" id="tutorialCompletion-{{ $module->key }}">
        <i class="bi bi-check-circle-fill fs-1 text-success mb-3 d-block"></i>
        <h4>Ready to Go!</h4>
        @if($config->completion_message)
        <p class="text-muted">{{ $config->completion_message }}</p>
        @endif
    </div>

    <!-- Navigation -->
    <div class="tutorial-navigation mt-4 pt-3 border-top">
        <div class="d-flex justify-content-between">
            <button type="button" 
                    class="btn btn-outline-secondary btn-prev"
                    id="btnTutorialPrev-{{ $module->key }}"
                    onclick="tutorialPrev('{{ $module->key }}')"
                    disabled>
                <i class="bi bi-arrow-left"></i> Previous
            </button>
            
            <div>
                <button type="button" 
                        class="btn btn-link text-muted me-2"
                        onclick="tutorialSkip('{{ $module->key }}')">
                    Skip Tutorial
                </button>
                
                <button type="button" 
                        class="btn btn-primary btn-next"
                        id="btnTutorialNext-{{ $module->key }}"
                        onclick="tutorialNext('{{ $module->key }}')">
                    Next <i class="bi bi-arrow-right"></i>
                </button>
                
                <button type="button" 
                        class="btn btn-success btn-activate d-none"
                        id="btnTutorialActivate-{{ $module->key }}"
                        onclick="tutorialActivate('{{ $module->key }}')">
                    <i class="bi bi-check-circle"></i> Activate Module
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.guided-tutorial {
    max-width: 600px;
    margin: 0 auto;
}

.progress-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #dee2e6;
    transition: all 0.3s ease;
}

.progress-dot.active {
    background: #0d6efd;
    transform: scale(1.3);
}

.progress-dot.completed {
    background: #198754;
}

.tutorial-step {
    animation: slideIn 0.4s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.step-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>

<script>
function tutorialNext(moduleKey) {
    const container = document.getElementById('guidedTutorial-' + moduleKey);
    const currentStepEl = container.querySelector('.tutorial-step.active');
    const currentStep = parseInt(currentStepEl.dataset.step);
    const totalSteps = container.querySelectorAll('.tutorial-step').length;
    
    // Hide current step
    currentStepEl.classList.add('d-none');
    currentStepEl.classList.remove('active');
    
    // Show next step
    const nextStep = currentStep + 1;
    if (nextStep <= totalSteps) {
        const nextStepEl = container.querySelector('.tutorial-step[data-step="' + nextStep + '"]');
        nextStepEl.classList.remove('d-none');
        nextStepEl.classList.add('active');
        
        // Update UI
        updateTutorialUI(moduleKey, nextStep, totalSteps);
    } else {
        // Show completion
        document.getElementById('tutorialCompletion-' + moduleKey).classList.remove('d-none');
        document.getElementById('btnTutorialNext-' + moduleKey).classList.add('d-none');
        document.getElementById('btnTutorialActivate-' + moduleKey).classList.remove('d-none');
        document.getElementById('btnTutorialPrev-' + moduleKey).disabled = false;
    }
}

function tutorialPrev(moduleKey) {
    const container = document.getElementById('guidedTutorial-' + moduleKey);
    const currentStepEl = container.querySelector('.tutorial-step.active') || 
                          document.getElementById('tutorialCompletion-' + moduleKey);
    const totalSteps = container.querySelectorAll('.tutorial-step').length;
    
    // Check if we're on completion screen
    if (currentStepEl.id === 'tutorialCompletion-' + moduleKey) {
        currentStepEl.classList.add('d-none');
        
        // Show last step
        const lastStepEl = container.querySelector('.tutorial-step[data-step="' + totalSteps + '"]');
        lastStepEl.classList.remove('d-none');
        lastStepEl.classList.add('active');
        
        // Restore buttons
        document.getElementById('btnTutorialNext-' + moduleKey).classList.remove('d-none');
        document.getElementById('btnTutorialActivate-' + moduleKey).classList.add('d-none');
        
        updateTutorialUI(moduleKey, totalSteps, totalSteps);
        return;
    }
    
    const currentStep = parseInt(currentStepEl.dataset.step);
    
    // Hide current step
    currentStepEl.classList.add('d-none');
    currentStepEl.classList.remove('active');
    
    // Show previous step
    const prevStep = currentStep - 1;
    const prevStepEl = container.querySelector('.tutorial-step[data-step="' + prevStep + '"]');
    prevStepEl.classList.remove('d-none');
    prevStepEl.classList.add('active');
    
    // Update UI
    updateTutorialUI(moduleKey, prevStep, totalSteps);
}

function updateTutorialUI(moduleKey, currentStep, totalSteps) {
    // Update dots
    const dots = document.querySelectorAll('#guidedTutorial-' + moduleKey + ' .progress-dot');
    dots.forEach((dot, index) => {
        const stepNum = index + 1;
        dot.classList.remove('active', 'completed');
        if (stepNum === currentStep) {
            dot.classList.add('active');
        } else if (stepNum < currentStep) {
            dot.classList.add('completed');
        }
    });
    
    // Update counter
    document.querySelector('#guidedTutorial-' + moduleKey + ' .current-step').textContent = currentStep;
    
    // Update buttons
    document.getElementById('btnTutorialPrev-' + moduleKey).disabled = currentStep === 1;
}

function tutorialSkip(moduleKey) {
    Swal.fire({
        title: 'Skip Tutorial?',
        text: 'You can always access help documentation later.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Skip',
        cancelButtonText: 'Continue'
    }).then((result) => {
        if (result.isConfirmed) {
            tutorialActivate(moduleKey);
        }
    });
}

function tutorialActivate(moduleKey) {
    // Call the activation endpoint
    $.ajax({
        url: '{{ url("dashboard/marketplace/modules") }}/' + moduleKey + '/activate',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            $('#moduleActivationModal').modal('hide');
            
            if (response.status === 'activated') {
                Swal.fire({
                    icon: 'success',
                    title: 'Module Activated!',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                setTimeout(() => location.reload(), 2000);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Activation failed';
            Swal.fire({
                icon: 'error',
                title: 'Activation Failed',
                text: error
            });
        }
    });
}
</script>
