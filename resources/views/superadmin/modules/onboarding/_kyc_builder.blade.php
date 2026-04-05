<!-- KYC Configuration Builder -->
<div class="row">
    <!-- Required Documents -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-files"></i> Required Documents</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addKycDocument()">
                    <i class="bi bi-plus"></i> Add Document
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small">Documents that applicants must upload for verification.</p>
                
                <div id="kycDocumentsContainer">
                    @php $documents = $config->getDocumentsList(); @endphp
                    @forelse($documents as $key => $doc)
                    <div class="kyc-document-item border rounded p-3 mb-3" data-key="{{ $key }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">Document: {{ $key }}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeKycDocument(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label small">Document Key</label>
                                <input type="text" name="required_documents[{{ $key }}][key]" class="form-control form-control-sm" value="{{ $key }}" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Label</label>
                                <input type="text" name="required_documents[{{ $key }}][label]" class="form-control form-control-sm" value="{{ $doc['label'] ?? '' }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Description</label>
                                <input type="text" name="required_documents[{{ $key }}][description]" class="form-control form-control-sm" value="{{ $doc['description'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Accepted Types</label>
                                <input type="text" name="required_documents[{{ $key }}][accepted_types]" class="form-control form-control-sm" value="{{ implode(', ', $doc['accepted_types'] ?? ['pdf', 'jpg', 'png']) }}" placeholder="pdf, jpg, png">
                                <small class="form-text text-muted">Comma-separated file extensions</small>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_documents[{{ $key }}][required]" value="1" {{ ($doc['required'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small">Required</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-3 text-muted" id="noKycDocuments">
                        <i class="bi bi-files fs-1"></i>
                        <p class="mt-2 small">No documents required yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- KYC Form Fields -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-input-cursor"></i> Form Fields</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addKycField()">
                    <i class="bi bi-plus"></i> Add Field
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small">Form fields to collect information from applicants.</p>
                
                <div id="kycFormFieldsContainer">
                    @php $formFields = $config->kyc_form_schema ?? []; @endphp
                    @forelse($formFields as $index => $field)
                    <div class="kyc-field-item border rounded p-3 mb-3" data-index="{{ $index }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">Field #{{ $index + 1 }}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeKycField(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Field Name</label>
                                <input type="text" name="kyc_form_fields[{{ $index }}][name]" class="form-control form-control-sm" value="{{ $field['name'] ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Type</label>
                                <select name="kyc_form_fields[{{ $index }}][type]" class="form-select form-select-sm">
                                    <option value="text" {{ ($field['type'] ?? '') === 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="textarea" {{ ($field['type'] ?? '') === 'textarea' ? 'selected' : '' }}>Textarea</option>
                                    <option value="number" {{ ($field['type'] ?? '') === 'number' ? 'selected' : '' }}>Number</option>
                                    <option value="email" {{ ($field['type'] ?? '') === 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="select" {{ ($field['type'] ?? '') === 'select' ? 'selected' : '' }}>Select</option>
                                    <option value="date" {{ ($field['type'] ?? '') === 'date' ? 'selected' : '' }}>Date</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Label</label>
                                <input type="text" name="kyc_form_fields[{{ $index }}][label]" class="form-control form-control-sm" value="{{ $field['label'] ?? '' }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Placeholder</label>
                                <input type="text" name="kyc_form_fields[{{ $index }}][placeholder]" class="form-control form-control-sm" value="{{ $field['placeholder'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="kyc_form_fields[{{ $index }}][required]" value="1" {{ ($field['required'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label small">Required Field</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-3 text-muted" id="noKycFields">
                        <i class="bi bi-input-cursor fs-1"></i>
                        <p class="mt-2 small">No form fields defined yet.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let kycDocCounter = {{ count($documents) }};
let kycFieldCounter = {{ count($formFields) }};

function addKycDocument() {
    const container = $('#kycDocumentsContainer');
    const key = 'document_' + (++kycDocCounter);
    
    $('#noKycDocuments').remove();
    
    const docHtml = `
        <div class="kyc-document-item border rounded p-3 mb-3" data-key="${key}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">New Document</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeKycDocument(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label small">Document Key</label>
                    <input type="text" name="required_documents[${key}][key]" class="form-control form-control-sm" value="${key}" placeholder="e.g., registration_certificate">
                </div>
                <div class="col-12">
                    <label class="form-label small">Label</label>
                    <input type="text" name="required_documents[${key}][label]" class="form-control form-control-sm" placeholder="e.g., Registration Certificate" required>
                </div>
                <div class="col-12">
                    <label class="form-label small">Description</label>
                    <input type="text" name="required_documents[${key}][description]" class="form-control form-control-sm" placeholder="Brief description of this document">
                </div>
                <div class="col-12">
                    <label class="form-label small">Accepted Types</label>
                    <input type="text" name="required_documents[${key}][accepted_types]" class="form-control form-control-sm" value="pdf, jpg, png" placeholder="pdf, jpg, png">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="required_documents[${key}][required]" value="1" checked>
                        <label class="form-check-label small">Required</label>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.append(docHtml);
}

function removeKycDocument(btn) {
    $(btn).closest('.kyc-document-item').remove();
    
    if ($('#kycDocumentsContainer .kyc-document-item').length === 0) {
        $('#kycDocumentsContainer').html(`
            <div class="text-center py-3 text-muted" id="noKycDocuments">
                <i class="bi bi-files fs-1"></i>
                <p class="mt-2 small">No documents required yet.</p>
            </div>
        `);
    }
}

function addKycField() {
    const container = $('#kycFormFieldsContainer');
    const index = kycFieldCounter++;
    
    $('#noKycFields').remove();
    
    const fieldHtml = `
        <div class="kyc-field-item border rounded p-3 mb-3" data-index="${index}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">New Field</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeKycField(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label small">Field Name</label>
                    <input type="text" name="kyc_form_fields[${index}][name]" class="form-control form-control-sm" placeholder="e.g., church_name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Type</label>
                    <select name="kyc_form_fields[${index}][type]" class="form-select form-select-sm">
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="number">Number</option>
                        <option value="email">Email</option>
                        <option value="select">Select</option>
                        <option value="date">Date</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small">Label</label>
                    <input type="text" name="kyc_form_fields[${index}][label]" class="form-control form-control-sm" placeholder="e.g., Church Name" required>
                </div>
                <div class="col-12">
                    <label class="form-label small">Placeholder</label>
                    <input type="text" name="kyc_form_fields[${index}][placeholder]" class="form-control form-control-sm" placeholder="Enter value...">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="kyc_form_fields[${index}][required]" value="1" checked>
                        <label class="form-check-label small">Required Field</label>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.append(fieldHtml);
}

function removeKycField(btn) {
    $(btn).closest('.kyc-field-item').remove();
    
    if ($('#kycFormFieldsContainer .kyc-field-item').length === 0) {
        $('#kycFormFieldsContainer').html(`
            <div class="text-center py-3 text-muted" id="noKycFields">
                <i class="bi bi-input-cursor fs-1"></i>
                <p class="mt-2 small">No form fields defined yet.</p>
            </div>
        `);
    }
}
</script>
