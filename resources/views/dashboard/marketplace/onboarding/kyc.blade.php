<div class="modal-header bg-primary text-white">
    <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i> Module Activation</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Verification Required</strong>
        <p class="mb-0 small">This module requires document verification. Your application will be reviewed within 1-2 business days.</p>
    </div>
    
    @if($config->welcome_message)
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> {{ $config->welcome_message }}
    </div>
    @endif

    <form id="kycOnboardingForm" enctype="multipart/form-data">
        <input type="hidden" name="onboarding_id" value="{{ $onboarding->id }}">
        
        <!-- Progress -->
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted">Application Progress</span>
                <span class="small text-muted">Step 1 of 2</span>
            </div>
            <div class="progress" style="height: 6px;">
                <div class="progress-bar bg-primary" style="width: 50%"></div>
            </div>
        </div>

        <!-- Required Documents -->
        <h6 class="border-bottom pb-2 mb-3">
            <i class="bi bi-files me-1"></i> Required Documents
        </h6>
        
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
                        <p class="text-muted small mb-1">{{ $doc['description'] ?? '' }}</p>
                        <small class="text-muted">Accepted: {{ implode(', ', $doc['accepted_types'] ?? ['pdf', 'jpg', 'png']) }}</small>
                    </div>
                    <div>
                        <input type="file" 
                               name="documents[{{ $key }}]" 
                               class="d-none" 
                               id="doc_{{ $key }}"
                               accept=".{{ implode(',.', $doc['accepted_types'] ?? ['pdf', 'jpg', 'png']) }}"
                               {{ ($doc['required'] ?? true) ? 'required' : '' }}
                               onchange="handleKycFileUpload(this, '{{ $key }}')">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('doc_{{ $key }}').click()">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                        <div class="file-name small mt-1 text-success" id="filename_{{ $key }}"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Form Fields -->
        @if($config->kyc_form_schema && count($config->kyc_form_schema) > 0)
        <h6 class="border-bottom pb-2 mb-3 mt-4">
            <i class="bi bi-input-cursor me-1"></i> Additional Information
        </h6>
        
        <div class="row g-3">
            @foreach($config->kyc_form_schema as $field)
            <div class="col-md-6">
                <label class="form-label">
                    {{ $field['label'] ?? $field['name'] }}
                    @if($field['required'] ?? false)
                    <span class="text-danger">*</span>
                    @endif
                </label>
                
                @if($field['type'] === 'select')
                <select name="form_data[{{ $field['name'] }}]" class="form-select" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                    <option value="">Select...</option>
                    @foreach($field['options'] ?? [] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @elseif($field['type'] === 'textarea')
                <textarea name="form_data[{{ $field['name'] }}]" class="form-control" rows="3" {{ ($field['required'] ?? false) ? 'required' : '' }}></textarea>
                @else
                <input type="{{ $field['type'] }}" name="form_data[{{ $field['name'] }}]" class="form-control" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Network Participation -->
        @if($config->network_participation_enabled)
        <div class="mt-4">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="networkOptIn" name="network_opt_in">
                        <label class="form-check-label" for="networkOptIn">
                            <strong>Join Network Participation</strong>
                        </label>
                    </div>
                    <p class="text-muted small mb-0 mt-1">
                        Share your content with other churches and receive content from the network.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </form>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancel</button>
    <button type="button" class="btn btn-primary" onclick="submitKycApplication({{ $onboarding->id }})">
        <i class="bi bi-send"></i> Submit Application
    </button>
</div>

<script>
function handleKycFileUpload(input, docKey) {
    if (input.files && input.files[0]) {
        const filename = input.files[0].name;
        document.getElementById('filename_' + docKey).textContent = filename;
        
        // Upload file immediately
        const formData = new FormData();
        formData.append('document', input.files[0]);
        formData.append('document_key', docKey);
        formData.append('_token', '{{ csrf_token() }}');
        
        $.ajax({
            url: '{{ url("dashboard/marketplace/onboarding") }}/{{ $onboarding->id }}/upload',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                document.getElementById('filename_' + docKey).innerHTML = '<i class="bi bi-check-circle"></i> ' + filename;
            },
            error: function() {
                alert('Upload failed. Please try again.');
                input.value = '';
                document.getElementById('filename_' + docKey).textContent = '';
            }
        });
    }
}

function submitKycApplication(onboardingId) {
    const form = document.getElementById('kycOnboardingForm');
    const formData = new FormData(form);
    
    // Validate
    const requiredFields = form.querySelectorAll('[required]');
    let valid = true;
    requiredFields.forEach(field => {
        if (!field.value) {
            field.classList.add('is-invalid');
            valid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    if (!valid) {
        Swal.fire({
            icon: 'warning',
            title: 'Required Fields',
            text: 'Please fill in all required fields and upload documents.'
        });
        return;
    }
    
    // Submit
    $.ajax({
        url: '{{ url("dashboard/marketplace/onboarding") }}/' + onboardingId + '/submit',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#moduleActivationModal').modal('hide');
            
            if (response.status === 'pending') {
                Swal.fire({
                    icon: 'info',
                    title: 'Application Submitted',
                    text: response.message,
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                setTimeout(() => location.reload(), 2000);
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.error || 'Submission failed. Please try again.';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error
            });
        }
    });
}
</script>
