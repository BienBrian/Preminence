@props([
    'module',
    'config' => null,
])

@php
$config = $config ?? \App\Models\ModuleOnboardingConfig::where('module_key', $module)->first();
$helpItems = $config?->getContextualHelp() ?? [];
$showTour = request()->has('tour') || session('show_module_tour_' . $module);
@endphp

@if($config && $config->contextual_help_enabled && count($helpItems) > 0)
<!-- Contextual Help Container -->
<div id="contextualHelp-{{ $module }}" class="contextual-help-system" data-module="{{ $module }}">
    
    <!-- Help Button (Floating) -->
    <div class="help-button-container">
        <button type="button" 
                class="btn btn-primary btn-lg rounded-circle shadow help-toggle"
                onclick="toggleHelpMenu('{{ $module }}')"
                title="Help & Guidance">
            <i class="bi bi-question-lg"></i>
        </button>
        
        <!-- Help Menu -->
        <div class="help-menu shadow d-none" id="helpMenu-{{ $module }}">
            <div class="help-menu-header">
                <h6 class="mb-0">Need Help?</h6>
                <button type="button" class="btn-close" onclick="toggleHelpMenu('{{ $module }}')"></button>
            </div>
            <div class="help-menu-body">
                <button class="help-menu-item" onclick="startFeatureTour('{{ $module }}')">
                    <i class="bi bi-compass text-primary"></i>
                    <span>Take a Tour</span>
                </button>
                <button class="help-menu-item" onclick="showHelpTooltips('{{ $module }}')">
                    <i class="bi bi-lightbulb text-warning"></i>
                    <span>Show Tips</span>
                </button>
                @if($config->video_url)
                <a href="{{ $config->video_url }}" class="help-menu-item" target="_blank">
                    <i class="bi bi-play-circle text-danger"></i>
                    <span>Watch Tutorial</span>
                </a>
                @endif
                @if($config->documentation_url)
                <a href="{{ $config->documentation_url }}" class="help-menu-item" target="_blank">
                    <i class="bi bi-book text-info"></i>
                    <span>Documentation</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Feature Tour Overlay -->
    <div class="tour-overlay d-none" id="tourOverlay-{{ $module }}">
        <div class="tour-backdrop"></div>
        <div class="tour-spotlight"></div>
        <div class="tour-card">
            <div class="tour-card-header">
                <span class="tour-step-count">Step <span class="current">1</span> of <span class="total">{{ count($helpItems) }}</span></span>
                <button type="button" class="btn-close" onclick="endFeatureTour('{{ $module }}')"></button>
            </div>
            <div class="tour-card-body">
                <h5 class="tour-title"></h5>
                <p class="tour-description text-muted"></p>
            </div>
            <div class="tour-card-footer">
                <button type="button" class="btn btn-link text-muted btn-skip" onclick="endFeatureTour('{{ $module }}')">Skip Tour</button>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-prev me-2" onclick="tourPrev('{{ $module }}')">Previous</button>
                    <button type="button" class="btn btn-primary btn-next" onclick="tourNext('{{ $module }}')">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooltip Templates -->
    @foreach($helpItems as $index => $item)
    @if(isset($item['target']))
    <div class="contextual-tooltip d-none" 
         id="tooltip-{{ $module }}-{{ $index }}"
         data-target="{{ $item['target'] }}"
         data-position="{{ $item['position'] ?? 'top' }}"
         data-title="{{ $item['title'] ?? '' }}"
         data-content="{{ $item['content'] ?? '' }}">
        <div class="tooltip-arrow"></div>
        <div class="tooltip-content">
            <h6 class="tooltip-title">{{ $item['title'] ?? '' }}</h6>
            <p class="tooltip-text">{{ $item['content'] ?? '' }}</p>
            <button class="btn btn-sm btn-link p-0" onclick="hideTooltip('{{ $module }}', {{ $index }})">Got it</button>
        </div>
    </div>
    @endif
    @endforeach
</div>

<style>
.contextual-help-system {
    position: relative;
}

/* Floating Help Button */
.help-button-container {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1040;
}

.help-toggle {
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}

.help-toggle:hover {
    transform: scale(1.1);
}

.help-toggle i {
    font-size: 1.5rem;
}

/* Help Menu */
.help-menu {
    position: absolute;
    bottom: 70px;
    right: 0;
    background: white;
    border-radius: 12px;
    width: 220px;
    overflow: hidden;
    animation: slideUp 0.2s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.help-menu-header {
    padding: 12px 16px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.help-menu-body {
    padding: 8px;
}

.help-menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}

.help-menu-item:hover {
    background: #f8f9fa;
}

.help-menu-item i {
    font-size: 1.2rem;
}

/* Tour Overlay */
.tour-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
}

.tour-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
}

.tour-spotlight {
    position: absolute;
    border-radius: 8px;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.7);
    transition: all 0.3s ease;
}

.tour-card {
    position: absolute;
    background: white;
    border-radius: 12px;
    width: 360px;
    max-width: 90vw;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

.tour-card-header {
    padding: 12px 16px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.tour-step-count {
    font-size: 0.875rem;
    color: #6c757d;
}

.tour-card-body {
    padding: 16px;
}

.tour-card-footer {
    padding: 12px 16px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Contextual Tooltips */
.contextual-tooltip {
    position: absolute;
    z-index: 1060;
    max-width: 280px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    animation: tooltipPop 0.3s ease;
}

@keyframes tooltipPop {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.tooltip-arrow {
    position: absolute;
    width: 12px;
    height: 12px;
    background: white;
    transform: rotate(45deg);
}

.tooltip-content {
    padding: 12px 16px;
    position: relative;
    z-index: 1;
    background: white;
    border-radius: 8px;
}

.tooltip-title {
    margin-bottom: 4px;
    font-weight: 600;
}

.tooltip-text {
    margin-bottom: 8px;
    font-size: 0.875rem;
    color: #6c757d;
}

/* Pulsing highlight for tour */
.tour-highlight {
    position: relative;
    z-index: 1051;
}

.tour-highlight::after {
    content: '';
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border: 2px solid #0d6efd;
    border-radius: 8px;
    animation: pulse-border 2s infinite;
}

@keyframes pulse-border {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

<script>
// Tour state
let tourState = {
    module: null,
    currentStep: 0,
    items: [],
    isActive: false
};

function toggleHelpMenu(module) {
    const menu = document.getElementById('helpMenu-' + module);
    menu.classList.toggle('d-none');
}

function startFeatureTour(module) {
    // Hide help menu
    document.getElementById('helpMenu-' + module).classList.add('d-none');
    
    // Get help items
    const container = document.getElementById('contextualHelp-' + module);
    const tooltips = container.querySelectorAll('.contextual-tooltip');
    
    tourState.module = module;
    tourState.items = Array.from(tooltips).map((el, index) => ({
        index: index,
        target: el.dataset.target,
        title: el.dataset.title,
        content: el.dataset.content,
        position: el.dataset.position
    }));
    tourState.currentStep = 0;
    tourState.isActive = true;
    
    // Show overlay
    document.getElementById('tourOverlay-' + module).classList.remove('d-none');
    
    // Show first step
    showTourStep(module, 0);
}

function showTourStep(module, stepIndex) {
    const step = tourState.items[stepIndex];
    if (!step) return;
    
    // Update card content
    const overlay = document.getElementById('tourOverlay-' + module);
    overlay.querySelector('.tour-step-count .current').textContent = stepIndex + 1;
    overlay.querySelector('.tour-step-count .total').textContent = tourState.items.length;
    overlay.querySelector('.tour-title').textContent = step.title;
    overlay.querySelector('.tour-description').textContent = step.content;
    
    // Update buttons
    overlay.querySelector('.btn-prev').disabled = stepIndex === 0;
    overlay.querySelector('.btn-next').textContent = stepIndex === tourState.items.length - 1 ? 'Finish' : 'Next';
    
    // Position spotlight and card
    const targetEl = document.querySelector(step.target);
    if (targetEl) {
        const rect = targetEl.getBoundingClientRect();
        const spotlight = overlay.querySelector('.tour-spotlight');
        const card = overlay.querySelector('.tour-card');
        
        // Position spotlight around target
        spotlight.style.left = (rect.left - 8) + 'px';
        spotlight.style.top = (rect.top - 8) + 'px';
        spotlight.style.width = (rect.width + 16) + 'px';
        spotlight.style.height = (rect.height + 16) + 'px';
        
        // Position card based on available space
        const cardRect = card.getBoundingClientRect();
        let cardTop = rect.bottom + 20;
        let cardLeft = rect.left;
        
        // Adjust if would go off screen
        if (cardTop + cardRect.height > window.innerHeight) {
            cardTop = rect.top - cardRect.height - 20;
        }
        if (cardLeft + 360 > window.innerWidth) {
            cardLeft = window.innerWidth - 380;
        }
        
        card.style.top = Math.max(20, cardTop) + 'px';
        card.style.left = Math.max(20, cardLeft) + 'px';
        
        // Add highlight to target
        document.querySelectorAll('.tour-highlight').forEach(el => el.classList.remove('tour-highlight'));
        targetEl.classList.add('tour-highlight');
        
        // Scroll target into view
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function tourNext(module) {
    if (tourState.currentStep < tourState.items.length - 1) {
        tourState.currentStep++;
        showTourStep(module, tourState.currentStep);
    } else {
        endFeatureTour(module);
    }
}

function tourPrev(module) {
    if (tourState.currentStep > 0) {
        tourState.currentStep--;
        showTourStep(module, tourState.currentStep);
    }
}

function endFeatureTour(module) {
    tourState.isActive = false;
    document.getElementById('tourOverlay-' + module).classList.add('d-none');
    document.querySelectorAll('.tour-highlight').forEach(el => el.classList.remove('tour-highlight'));
    
    // Mark tour as seen
    localStorage.setItem('tour_seen_' + module, 'true');
}

function showHelpTooltips(module) {
    // Hide help menu
    document.getElementById('helpMenu-' + module).classList.add('d-none');
    
    // Show all tooltips with delay
    const container = document.getElementById('contextualHelp-' + module);
    const tooltips = container.querySelectorAll('.contextual-tooltip');
    
    tooltips.forEach((tooltip, index) => {
        setTimeout(() => {
            positionTooltip(module, index);
            tooltip.classList.remove('d-none');
        }, index * 200);
    });
}

function positionTooltip(module, index) {
    const tooltip = document.getElementById('tooltip-' + module + '-' + index);
    if (!tooltip) return;
    
    const target = document.querySelector(tooltip.dataset.target);
    if (!target) return;
    
    const targetRect = target.getBoundingClientRect();
    const position = tooltip.dataset.position || 'top';
    
    let top, left;
    
    switch (position) {
        case 'top':
            top = targetRect.top - tooltip.offsetHeight - 10;
            left = targetRect.left + (targetRect.width - tooltip.offsetWidth) / 2;
            break;
        case 'bottom':
            top = targetRect.bottom + 10;
            left = targetRect.left + (targetRect.width - tooltip.offsetWidth) / 2;
            break;
        case 'left':
            top = targetRect.top + (targetRect.height - tooltip.offsetHeight) / 2;
            left = targetRect.left - tooltip.offsetWidth - 10;
            break;
        case 'right':
            top = targetRect.top + (targetRect.height - tooltip.offsetHeight) / 2;
            left = targetRect.right + 10;
            break;
    }
    
    tooltip.style.top = Math.max(10, top + window.scrollY) + 'px';
    tooltip.style.left = Math.max(10, left) + 'px';
}

function hideTooltip(module, index) {
    const tooltip = document.getElementById('tooltip-' + module + '-' + index);
    if (tooltip) {
        tooltip.classList.add('d-none');
    }
}

// Auto-show tour on first visit (if requested)
document.addEventListener('DOMContentLoaded', function() {
    @if($showTour)
    setTimeout(() => {
        startFeatureTour('{{ $module }}');
    }, 1000);
    @endif
});

// Handle window resize for tooltips
window.addEventListener('resize', function() {
    document.querySelectorAll('.contextual-tooltip:not(.d-none)').forEach(tooltip => {
        const parts = tooltip.id.split('-');
        const module = parts[1];
        const index = parts[2];
        positionTooltip(module, index);
    });
});
</script>
@endif
