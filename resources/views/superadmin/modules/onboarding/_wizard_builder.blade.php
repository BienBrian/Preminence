<!-- Setup Wizard Builder -->
<div class="row">
    <!-- Steps Management -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ol"></i> Wizard Steps</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addWizardStep()">
                    <i class="bi bi-plus"></i> Add Step
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted">Create steps for your setup wizard. Each step can contain form fields or informational content.</p>
                
                <div id="wizardStepsContainer" class="wizard-steps-list">
                    @forelse($config->steps as $step)
                    <div class="wizard-step-item card mb-3" data-step-id="{{ $step->id }}">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="drag-handle me-2 text-muted" style="cursor: move;">
                                    <i class="bi bi-grip-vertical"></i>
                                </span>
                                <span class="badge bg-secondary step-number">{{ $step->step_number }}</span>
                                <span class="step-title ms-2 fw-bold">{{ $step->title }}</span>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleStepCollapse(this)">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteWizardStep({{ $step->id }}, this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body collapse show">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Step Title</label>
                                    <input type="text" class="form-control step-title-input" value="{{ $step->title }}" data-field="title" data-step-id="{{ $step->id }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Content Type</label>
                                    <select class="form-select" data-field="content_type" data-step-id="{{ $step->id }}">
                                        <option value="info" {{ $step->content_type === 'info' ? 'selected' : '' }}>Information</option>
                                        <option value="form" {{ $step->content_type === 'form' ? 'selected' : '' }}>Form Fields</option>
                                        <option value="video" {{ $step->content_type === 'video' ? 'selected' : '' }}>Video Tutorial</option>
                                        <option value="confirmation" {{ $step->content_type === 'confirmation' ? 'selected' : '' }}>Confirmation</option>
                                        <option value="completion" {{ $step->content_type === 'completion' ? 'selected' : '' }}>Completion</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Est. Minutes</label>
                                    <input type="number" class="form-control" value="{{ $step->estimated_minutes }}" min="1" max="60" data-field="estimated_minutes" data-step-id="{{ $step->id }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" rows="2" data-field="description" data-step-id="{{ $step->id }}">{{ $step->description }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Content</label>
                                    <textarea class="form-control" rows="3" data-field="content" data-step-id="{{ $step->id }}">{{ $step->content }}</textarea>
                                    <small class="form-text text-muted">Main content for this step (HTML supported)</small>
                                </div>
                                
                                <!-- Form Fields Section (shown when content_type is 'form') -->
                                <div class="col-12 form-fields-section {{ $step->content_type === 'form' ? '' : 'd-none' }}">
                                    <div class="border rounded p-3 bg-light">
                                        <label class="form-label d-flex justify-content-between">
                                            <span>Form Fields (JSON Schema)</span>
                                            <a href="#" class="text-decoration-none small" data-bs-toggle="modal" data-bs-target="#formSchemaHelpModal">
                                                <i class="bi bi-question-circle"></i> Help
                                            </a>
                                        </label>
                                        <textarea class="form-control font-monospace small" rows="6" data-field="form_schema" data-step-id="{{ $step->id }}" placeholder="[{&quot;name&quot;: &quot;field_name&quot;, &quot;type&quot;: &quot;text&quot;, &quot;label&quot;: &quot;Field Label&quot;, &quot;required&quot;: true}]">{{ json_encode($step->form_schema, JSON_PRETTY_PRINT) }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" {{ $step->is_required ? 'checked' : '' }} data-field="is_required" data-step-id="{{ $step->id }}">
                                        <label class="form-check-label">Required</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" {{ $step->is_skippable ? 'checked' : '' }} data-field="is_skippable" data-step-id="{{ $step->id }}">
                                        <label class="form-check-label">Skippable</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" {{ $step->allow_back ? 'checked' : '' }} data-field="allow_back" data-step-id="{{ $step->id }}">
                                        <label class="form-check-label">Allow Back</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted" id="noWizardSteps">
                        <i class="bi bi-magic fs-1"></i>
                        <p class="mt-2">No wizard steps yet. Click "Add Step" to create your first step.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Schema Help Modal -->
<div class="modal fade" id="formSchemaHelpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Schema Reference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Define form fields using JSON schema. Each field is an object with the following properties:</p>
                
                <h6>Common Properties</h6>
                <ul>
                    <li><code>name</code> - Field identifier (required)</li>
                    <li><code>type</code> - Field type: text, textarea, number, email, select, checkbox, date, file</li>
                    <li><code>label</code> - Display label</li>
                    <li><code>required</code> - true/false</li>
                    <li><code>placeholder</code> - Placeholder text</li>
                    <li><code>default</code> - Default value</li>
                </ul>
                
                <h6>Example</h6>
                <pre class="bg-light p-3 rounded"><code>[{
  "name": "church_name",
  "type": "text",
  "label": "Church Name",
  "required": true,
  "placeholder": "e.g., Happy Church"
}, {
  "name": "currency",
  "type": "select",
  "label": "Currency",
  "options": {"KES": "Kenyan Shilling", "USD": "US Dollar"},
  "default": "KES"
}, {
  "name": "enable_notifications",
  "type": "checkbox",
  "label": "Enable Notifications",
  "default": true
}]</code></pre>
            </div>
        </div>
    </div>
</div>

<script>
let stepCounter = {{ $config->steps->count() }};

function addWizardStep() {
    stepCounter++;
    const container = $('#wizardStepsContainer');
    
    // Remove "no steps" message if present
    $('#noWizardSteps').remove();
    
    const stepHtml = `
        <div class="wizard-step-item card mb-3" data-step-id="new_${stepCounter}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="drag-handle me-2 text-muted" style="cursor: move;">
                        <i class="bi bi-grip-vertical"></i>
                    </span>
                    <span class="badge bg-secondary step-number">${container.find('.wizard-step-item').length + 1}</span>
                    <span class="step-title ms-2 fw-bold">New Step</span>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleStepCollapse(this)">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeNewWizardStep(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body collapse show">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Step Title</label>
                        <input type="text" class="form-control step-title-input" value="" data-field="title" data-step-id="new_${stepCounter}" placeholder="e.g., Configure Settings">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Content Type</label>
                        <select class="form-select" data-field="content_type" data-step-id="new_${stepCounter}" onchange="toggleFormFields(this)">
                            <option value="info">Information</option>
                            <option value="form">Form Fields</option>
                            <option value="video">Video Tutorial</option>
                            <option value="confirmation">Confirmation</option>
                            <option value="completion">Completion</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Est. Minutes</label>
                        <input type="number" class="form-control" value="2" min="1" max="60" data-field="estimated_minutes" data-step-id="new_${stepCounter}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" data-field="description" data-step-id="new_${stepCounter}" placeholder="Brief description of this step"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Content</label>
                        <textarea class="form-control" rows="3" data-field="content" data-step-id="new_${stepCounter}" placeholder="Main content for this step..."></textarea>
                    </div>
                    <div class="col-12 form-fields-section d-none">
                        <div class="border rounded p-3 bg-light">
                            <label class="form-label d-flex justify-content-between">
                                <span>Form Fields (JSON Schema)</span>
                                <a href="#" class="text-decoration-none small" data-bs-toggle="modal" data-bs-target="#formSchemaHelpModal">
                                    <i class="bi bi-question-circle"></i> Help
                                </a>
                            </label>
                            <textarea class="form-control font-monospace small" rows="6" data-field="form_schema" data-step-id="new_${stepCounter}" placeholder="[{&quot;name&quot;: &quot;field_name&quot;, &quot;type&quot;: &quot;text&quot;, &quot;label&quot;: &quot;Field Label&quot;, &quot;required&quot;: true}]"></textarea>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" checked data-field="is_required" data-step-id="new_${stepCounter}">
                            <label class="form-check-label">Required</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" data-field="is_skippable" data-step-id="new_${stepCounter}">
                            <label class="form-check-label">Skippable</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" checked data-field="allow_back" data-step-id="new_${stepCounter}">
                            <label class="form-check-label">Allow Back</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.append(stepHtml);
    renumberWizardSteps();
}

function toggleStepCollapse(btn) {
    const card = $(btn).closest('.wizard-step-item');
    const body = card.find('.card-body');
    const icon = $(btn).find('i');
    
    body.collapse('toggle');
    icon.toggleClass('bi-chevron-down bi-chevron-up');
}

function toggleFormFields(select) {
    const card = $(select).closest('.wizard-step-item');
    const formFieldsSection = card.find('.form-fields-section');
    
    if ($(select).val() === 'form') {
        formFieldsSection.removeClass('d-none');
    } else {
        formFieldsSection.addClass('d-none');
    }
}

function deleteWizardStep(stepId, btn) {
    if (!confirm('Delete this step?')) return;
    
    // In a real implementation, this would make an AJAX call to delete
    // For now, just remove from DOM
    $(btn).closest('.wizard-step-item').remove();
    renumberWizardSteps();
    
    // Show "no steps" message if empty
    if ($('#wizardStepsContainer .wizard-step-item').length === 0) {
        $('#wizardStepsContainer').html(`
            <div class="text-center py-5 text-muted" id="noWizardSteps">
                <i class="bi bi-magic fs-1"></i>
                <p class="mt-2">No wizard steps yet. Click "Add Step" to create your first step.</p>
            </div>
        `);
    }
}

function removeNewWizardStep(btn) {
    $(btn).closest('.wizard-step-item').remove();
    renumberWizardSteps();
}

function renumberWizardSteps() {
    $('#wizardStepsContainer .wizard-step-item').each(function(index) {
        $(this).find('.step-number').text(index + 1);
    });
}

// Update step title in header when input changes
$(document).on('input', '.step-title-input', function() {
    const title = $(this).val() || 'New Step';
    $(this).closest('.wizard-step-item').find('.step-title').text(title);
});
</script>
