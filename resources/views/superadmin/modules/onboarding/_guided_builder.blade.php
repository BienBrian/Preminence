<!-- Guided Tutorial Builder -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-book"></i> Tutorial Steps</h5>
        <button type="button" class="btn btn-sm btn-primary" onclick="addTutorialStep()">
            <i class="bi bi-plus"></i> Add Step
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted">Create tutorial steps to explain the module to users before they activate it.</p>
        
        <div id="tutorialStepsContainer">
            @php $tutorialSteps = $config->getTutorialSteps(); @endphp
            @forelse($tutorialSteps as $index => $step)
            <div class="tutorial-step-item border rounded p-3 mb-3" data-index="{{ $index }}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary step-number">{{ $index + 1 }}</span>
                        <h6 class="mb-0 ms-2">Step {{ $index + 1 }}</h6>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTutorialStep(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Title</label>
                        <input type="text" name="tutorial_steps[{{ $index }}][title]" class="form-control" value="{{ $step['title'] ?? '' }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Icon</label>
                        <select name="tutorial_steps[{{ $index }}][icon]" class="form-select">
                            <option value="bi-circle" {{ ($step['icon'] ?? '') === 'bi-circle' ? 'selected' : '' }}>Circle</option>
                            <option value="bi-check-circle" {{ ($step['icon'] ?? '') === 'bi-check-circle' ? 'selected' : '' }}>Check</option>
                            <option value="bi-info-circle" {{ ($step['icon'] ?? '') === 'bi-info-circle' ? 'selected' : '' }}>Info</option>
                            <option value="bi-lightbulb" {{ ($step['icon'] ?? '') === 'bi-lightbulb' ? 'selected' : '' }}>Lightbulb</option>
                            <option value="bi-star" {{ ($step['icon'] ?? '') === 'bi-star' ? 'selected' : '' }}>Star</option>
                            <option value="bi-heart" {{ ($step['icon'] ?? '') === 'bi-heart' ? 'selected' : '' }}>Heart</option>
                            <option value="bi-rocket" {{ ($step['icon'] ?? '') === 'bi-rocket' ? 'selected' : '' }}>Rocket</option>
                            <option value="bi-trophy" {{ ($step['icon'] ?? '') === 'bi-trophy' ? 'selected' : '' }}>Trophy</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Content</label>
                        <textarea name="tutorial_steps[{{ $index }}][content]" class="form-control" rows="3" required>{{ $step['content'] ?? '' }}</textarea>
                        <small class="form-text text-muted">Explain what this feature does and how to use it</small>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-muted" id="noTutorialSteps">
                <i class="bi bi-book fs-1"></i>
                <p class="mt-2">No tutorial steps yet. Add your first step above.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function addTutorialStep() {
    const container = $('#tutorialStepsContainer');
    const index = container.find('.tutorial-step-item').length;
    
    // Remove "no steps" message if present
    $('#noTutorialSteps').remove();
    
    const stepHtml = `
        <div class="tutorial-step-item border rounded p-3 mb-3" data-index="${index}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center">
                    <span class="badge bg-secondary step-number">${index + 1}</span>
                    <h6 class="mb-0 ms-2">Step ${index + 1}</h6>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTutorialStep(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Title</label>
                    <input type="text" name="tutorial_steps[${index}][title]" class="form-control" placeholder="e.g., Welcome to Feature X" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Icon</label>
                    <select name="tutorial_steps[${index}][icon]" class="form-select">
                        <option value="bi-circle">Circle</option>
                        <option value="bi-check-circle">Check</option>
                        <option value="bi-info-circle">Info</option>
                        <option value="bi-lightbulb" selected>Lightbulb</option>
                        <option value="bi-star">Star</option>
                        <option value="bi-heart">Heart</option>
                        <option value="bi-rocket">Rocket</option>
                        <option value="bi-trophy">Trophy</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Content</label>
                    <textarea name="tutorial_steps[${index}][content]" class="form-control" rows="3" placeholder="Explain what this feature does and how to use it..." required></textarea>
                </div>
            </div>
        </div>
    `;
    
    container.append(stepHtml);
    renumberTutorialSteps();
}

function removeTutorialStep(btn) {
    $(btn).closest('.tutorial-step-item').remove();
    renumberTutorialSteps();
    
    // Show "no steps" message if empty
    if ($('#tutorialStepsContainer .tutorial-step-item').length === 0) {
        $('#tutorialStepsContainer').html(`
            <div class="text-center py-4 text-muted" id="noTutorialSteps">
                <i class="bi bi-book fs-1"></i>
                <p class="mt-2">No tutorial steps yet. Add your first step above.</p>
            </div>
        `);
    }
}

function renumberTutorialSteps() {
    $('#tutorialStepsContainer .tutorial-step-item').each(function(index) {
        $(this).attr('data-index', index);
        $(this).find('.step-number').text(index + 1);
        $(this).find('h6').text('Step ' + (index + 1));
        
        // Update input names
        $(this).find('input, textarea, select').each(function() {
            const name = $(this).attr('name');
            if (name) {
                const newName = name.replace(/tutorial_steps\[\d+\]/, `tutorial_steps[${index}]`);
                $(this).attr('name', newName);
            }
        });
    });
}
</script>
