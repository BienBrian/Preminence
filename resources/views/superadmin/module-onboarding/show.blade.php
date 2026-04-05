@extends('superadmin.layouts.app')

@section('title', 'Review: ' . $submission->tenant->name . ' - ' . $submission->module->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('superadmin.module-onboarding.index') }}">Onboarding Review</a></li>
                    <li class="breadcrumb-item active">Review Submission</li>
                </ol>
            </nav>
            <h4>
                <i class="bi bi-clipboard-check"></i> 
                Review Module Activation Request
            </h4>
        </div>
        <div>
            @if($submission->isPending())
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#approveModal">
                <i class="bi bi-check-lg"></i> Approve & Activate
            </button>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#needsInfoModal">
                <i class="bi bi-question-circle"></i> Request Info
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-lg"></i> Reject
            </button>
            @endif
            <a href="{{ route('superadmin.module-onboarding.index', ['status' => $submission->status]) }}" 
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Submission Details -->
        <div class="col-md-8">
            <!-- Tenant Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Church Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="120">Church Name:</td>
                                    <td><strong>{{ $submission->tenant->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Slug:</td>
                                    <td>{{ $submission->tenant->slug }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Plan:</td>
                                    <td>
                                        <span class="badge bg-info">{{ $submission->tenant->plan?->name ?? 'None' }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted" width="120">Status:</td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'draft' => 'bg-secondary',
                                                'submitted' => 'bg-warning text-dark',
                                                'under_review' => 'bg-info',
                                                'approved' => 'bg-success',
                                                'rejected' => 'bg-danger',
                                                'needs_info' => 'bg-primary',
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$submission->status] ?? 'bg-secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Submitted:</td>
                                    <td>{{ $submission->submitted_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Current Modules:</td>
                                    <td>{{ count($tenantModules) }} active</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($submission->reviewed_by)
                    <hr>
                    <div class="alert alert-light border">
                        <strong>Reviewed by:</strong> {{ $submission->reviewer?->name ?? 'Unknown' }}
                        <br><strong>Reviewed at:</strong> {{ $submission->reviewed_at?->format('M d, Y H:i') }}
                        @if($submission->review_notes)
                        <br><strong>Notes:</strong> {{ $submission->review_notes }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- KYC Form Data -->
            @if($config->isKyc() && $submission->form_data)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> KYC Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($config->kyc_form_schema as $field)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">{{ $field['label'] ?? $field['name'] }}</label>
                            <div class="form-control bg-light">
                                @php
                                    $value = $submission->form_data[$field['name']] ?? null;
                                @endphp
                                
                                @if(is_array($value))
                                    {{ implode(', ', $value) }}
                                @elseif($field['type'] === 'textarea')
                                    <pre class="mb-0" style="white-space: pre-wrap;">{{ $value ?? 'N/A' }}</pre>
                                @else
                                    {{ $value ?? 'N/A' }}
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Documents -->
            @if(count($documents) > 0)
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-files"></i> Uploaded Documents</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($documents as $key => $doc)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $doc['label'] }}</h6>
                                            <small class="text-muted text-uppercase">{{ $doc['extension'] }}</small>
                                        </div>
                                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="{{ route('superadmin.module-onboarding.preview-document', [$submission->id, $key]) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Preview
                                    </a>
                                    <a href="{{ route('superadmin.module-onboarding.download-document', [$submission->id, $key]) }}" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Rejection Reason (if rejected) -->
            @if($submission->isRejected())
            <div class="alert alert-danger">
                <h6><i class="bi bi-x-circle"></i> Rejection Reason</h6>
                <p class="mb-0">{{ $submission->rejection_reason }}</p>
            </div>
            @endif

            <!-- Review Notes (if any) -->
            @if($submission->review_notes)
            <div class="alert alert-light border">
                <h6><i class="bi bi-sticky"></i> Review Notes</h6>
                <p class="mb-0">{{ $submission->review_notes }}</p>
            </div>
            @endif
        </div>

        <!-- Right Column: Module Info & Actions -->
        <div class="col-md-4">
            <!-- Module Info -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Module Details</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="bi {{ $submission->module->icon ?? 'bi-box' }} fs-1 text-primary"></i>
                        <h5 class="mt-2">{{ $submission->module->name }}</h5>
                    </div>
                    
                    <p class="text-muted">{{ $submission->module->description }}</p>
                    
                    <hr>
                    
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Pricing:</td>
                            <td>
                                @if($submission->module->is_free)
                                    <span class="badge bg-success">Free</span>
                                @else
                                    KES {{ number_format($submission->module->price_monthly) }}/mo
                                    <br><small class="text-muted">or KES {{ number_format($submission->module->price_yearly) }}/yr</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Setup Fee:</td>
                            <td>
                                @if($submission->module->setup_fee > 0)
                                    KES {{ number_format($submission->module->setup_fee) }}
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Category:</td>
                            <td><span class="badge bg-info">{{ ucfirst($submission->module->category) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Onboarding:</td>
                            <td>{{ ucfirst($config->onboarding_type) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Network Opt-in:</td>
                            <td>
                                @if($submission->network_participation_opt_in)
                                    <span class="text-success"><i class="bi bi-check-circle"></i> Yes</span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Approval Instructions -->
            @if($config->approval_instructions)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Approval Guidelines</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $config->approval_instructions }}</p>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            @if($submission->isPending())
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bi bi-check-lg"></i> Approve
                    </button>
                    <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#needsInfoModal">
                        <i class="bi bi-question-circle"></i> Request Info
                    </button>
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-lg"></i> Reject
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
@if($submission->isPending())
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('superadmin.module-onboarding.approve', $submission->id) }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Module Activation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to approve <strong>{{ $submission->module->name }}</strong> for <strong>{{ $submission->tenant->name }}</strong>.</p>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> The module will be automatically activated after approval.
                    </div>

                    @if(!$submission->module->is_free)
                    <div class="mb-3">
                        <label class="form-label">Trial Period (Days)</label>
                        <select name="trial_days" class="form-select">
                            <option value="0">No Trial - Start Billing Immediately</option>
                            <option value="7" selected>7 Days</option>
                            <option value="14">14 Days</option>
                            <option value="30">30 Days</option>
                        </select>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Approval Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Approve & Activate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('superadmin.module-onboarding.reject', $submission->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Module Activation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>You are about to reject <strong>{{ $submission->module->name }}</strong> for <strong>{{ $submission->tenant->name }}</strong>.</p>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> The tenant will be notified and can reapply.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required
                                  placeholder="Explain clearly why this application is being rejected..."></textarea>
                        <small class="form-text text-muted">This will be shown to the tenant.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Internal Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Internal notes not shown to tenant..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg"></i> Reject Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Needs Info Modal -->
<div class="modal fade" id="needsInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('superadmin.module-onboarding.request-info', $submission->id) }}">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Request More Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Request additional information from <strong>{{ $submission->tenant->name }}</strong>.</p>
                    
                    <div class="mb-3">
                        <label class="form-label">What information is needed? <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="4" required
                                  placeholder="e.g., Please provide a clearer copy of your registration certificate..."></textarea>
                        <small class="form-text text-muted">Be specific about what documents or information are needed.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send"></i> Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
