@extends('superadmin.layouts.app')

@section('title', 'Suspend Tenant')
@section('page-title', "Suspend: {$tenant->name}")

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle-fill"></i> 
                    Suspend Tenant Account
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Warning:</strong> Suspending this tenant will immediately block access to their dashboard. 
                    They will see a suspension page with the details you provide below.
                </div>

                <form action="{{ route('superadmin.tenants.suspend', $tenant->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <input type="text" class="form-control" value="{{ $tenant->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Suspension Type <span class="text-danger">*</span></label>
                        <select name="suspension_type" class="form-select @error('suspension_type') is-invalid @enderror" required id="suspensionType">
                            <option value="">Select type...</option>
                            <option value="financial">Financial - Payment Required</option>
                            <option value="terms_violation">Terms of Service Violation</option>
                            <option value="admin_action">Administrative Action</option>
                            <option value="other">Other</option>
                        </select>
                        @error('suspension_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            <strong>Financial:</strong> Tenant can pay to reactivate automatically.<br>
                            <strong>Other types:</strong> Tenant must contact admin to resolve.
                        </small>
                    </div>

                    {{-- Financial Details (shown only for financial type) --}}
                    <div id="financialDetails" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount Due <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="suspension_currency" class="form-select" style="max-width: 100px;">
                                        <option value="KES">KES</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                    </select>
                                    <input type="number" name="suspension_amount_due" class="form-control" 
                                           placeholder="0.00" min="0" step="0.01">
                                </div>
                                <small class="text-muted">Amount tenant needs to pay to reactivate</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Deadline</label>
                                <input type="datetime-local" name="suspension_ends_at" class="form-control">
                                <small class="text-muted">Optional. Tenant will see countdown timer.</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Suspension Reason <span class="text-danger">*</span></label>
                        <textarea name="suspension_reason" class="form-control @error('suspension_reason') is-invalid @enderror" 
                                  rows="4" required placeholder="Explain why this tenant is being suspended...">{{ old('suspension_reason') }}</textarea>
                        @error('suspension_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">This message will be displayed to the tenant on their suspension page.</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('superadmin.tenants.show', $tenant->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-ban"></i> Suspend Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('suspensionType').addEventListener('change', function() {
        const financialDetails = document.getElementById('financialDetails');
        if (this.value === 'financial') {
            financialDetails.style.display = 'block';
            financialDetails.querySelectorAll('input, select').forEach(el => el.required = true);
        } else {
            financialDetails.style.display = 'none';
            financialDetails.querySelectorAll('input, select').forEach(el => el.required = false);
        }
    });
</script>
@endsection
