@extends('layouts.admin')

@section('title', 'Install ' . $module->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-download"></i> Install {{ $module->name }}</h5>
                </div>
                <div class="card-body">
                    <!-- Module Summary -->
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                        <div class="flex-shrink-0">
                            <div class="bg-white rounded p-3 shadow-sm">
                                <i class="bi {{ $module->icon ?? 'bi-box' }} fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $module->name }}</h5>
                            <p class="text-muted mb-0">{{ $module->short_description }}</p>
                        </div>
                    </div>

                    <form id="installForm" method="POST" action="{{ route('marketplace.install', $module->key) }}">
                        @csrf

                        <!-- Dependencies Alert -->
                        @if(!empty($dependencies))
                            <div class="alert alert-info">
                                <h6><i class="bi bi-diagram-2"></i> Dependencies</h6>
                                <p class="mb-2">This module requires the following dependencies which will be installed automatically:</p>
                                <ul class="mb-0">
                                    @foreach($dependencies as $dep)
                                        <li>
                                            {{ $dep['name'] }}
                                            @if($dep['installed'])
                                                <span class="badge bg-success"><i class="bi bi-check"></i> Installed</span>
                                            @elseif($dep['is_free'])
                                                <span class="badge bg-info">Free</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Paid</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Billing Cycle -->
                        @if(!$price->isFree())
                            <div class="mb-4">
                                <h6>Select Billing Cycle</h6>
                                <div class="row">
                                    @if($price->monthly)
                                    <div class="col-md-6">
                                        <div class="form-check card p-3">
                                            <input class="form-check-input" type="radio" name="billing_cycle" id="monthly" value="monthly" checked>
                                            <label class="form-check-label w-100" for="monthly">
                                                <div class="d-flex justify-content-between">
                                                    <span>Monthly</span>
                                                    <span class="fw-bold">KES {{ number_format($price->monthly) }}</span>
                                                </div>
                                                <small class="text-muted">Billed monthly</small>
                                            </label>
                                        </div>
                                    </div>
                                    @endif
                                    @if($price->yearly)
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 border-success">
                                            <input class="form-check-input" type="radio" name="billing_cycle" id="yearly" value="yearly">
                                            <label class="form-check-label w-100" for="yearly">
                                                <div class="d-flex justify-content-between">
                                                    <span>Yearly</span>
                                                    <div>
                                                        <span class="fw-bold">KES {{ number_format($price->yearly) }}</span>
                                                        @if($price->yearlySavingsPercent)
                                                            <span class="badge bg-success ms-1">Save {{ $price->yearlySavingsPercent }}%</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <small class="text-muted">Billed annually</small>
                                            </label>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Cost Summary -->
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h6>Cost Summary</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Module Price</span>
                                        <span id="modulePrice">KES {{ number_format($price->monthly ?? $price->yearly) }}</span>
                                    </div>
                                    @if($price->setupFee > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Setup Fee</span>
                                            <span>KES {{ number_format($price->setupFee) }}</span>
                                        </div>
                                    @endif
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total Due Today</span>
                                        <span id="totalDue">KES {{ number_format(($price->monthly ?? $price->yearly) + $price->setupFee) }}</span>
                                    </div>
                                    @if($prorated->getPrice('monthly') !== null && $prorated->getPrice('monthly') < ($price->monthly ?? 0))
                                        <div class="alert alert-success mt-2 mb-0">
                                            <small>
                                                <i class="bi bi-info-circle"></i>
                                                Prorated charge: KES {{ number_format($prorated->getPrice('monthly'), 2) }} 
                                                (aligned with your billing cycle)
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="billing_cycle" value="monthly">
                            <div class="alert alert-success">
                                <i class="bi bi-gift"></i> This module is free to install!
                            </div>
                        @endif

                        <!-- Terms -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                <label class="form-check-label" for="agree_terms">
                                    I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                                    authorize the charge to my account.
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('marketplace.show', $module->key) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary" id="installBtn">
                                <i class="bi bi-download"></i> 
                                @if($price->isFree())
                                    Install Now
                                @else
                                    Proceed to Payment
                                @endif
                            </button>
                        </div>
                    </form>

                    <!-- Installation Progress (hidden initially) -->
                    <div id="installProgress" style="display: none;">
                        <hr>
                        <h6>Installation Progress</h6>
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 id="progressBar" 
                                 role="progressbar" 
                                 style="width: 0%"></div>
                        </div>
                        <div id="progressStatus" class="text-muted">Initializing...</div>
                        <div id="progressLog" class="small text-muted mt-2" style="font-family: monospace;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('installForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('installBtn');
    const form = this;
    const progressDiv = document.getElementById('installProgress');
    const progressBar = document.getElementById('progressBar');
    const progressStatus = document.getElementById('progressStatus');
    
    // Disable button
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    
    // Show progress
    progressDiv.style.display = 'block';
    
    // Submit via AJAX
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.requires_payment) {
                window.location.href = data.redirect_url;
            } else {
                // Poll for installation status
                pollInstallationStatus(data.subscription_id, data.redirect_url);
            }
        } else {
            alert(data.error || 'Installation failed');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-download"></i> Try Again';
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-download"></i> Try Again';
    });
});

function pollInstallationStatus(subscriptionId, redirectUrl) {
    const progressBar = document.getElementById('progressBar');
    const progressStatus = document.getElementById('progressStatus');
    
    const interval = setInterval(() => {
        fetch(`/marketplace/installations/${subscriptionId}/status`)
            .then(response => response.json())
            .then(data => {
                progressBar.style.width = data.progress + '%';
                progressStatus.textContent = data.status;
                
                if (data.status === 'active') {
                    clearInterval(interval);
                    window.location.href = redirectUrl;
                } else if (data.status === 'failed') {
                    clearInterval(interval);
                    alert('Installation failed: ' + data.error);
                }
            });
    }, 2000);
}

// Update price display when billing cycle changes
document.querySelectorAll('input[name="billing_cycle"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const monthlyPrice = {{ $price->monthly ?? 0 }};
        const yearlyPrice = {{ $price->yearly ?? 0 }};
        const setupFee = {{ $price->setupFee ?? 0 }};
        
        const price = this.value === 'yearly' ? yearlyPrice : monthlyPrice;
        document.getElementById('modulePrice').textContent = 'KES ' + price.toLocaleString();
        document.getElementById('totalDue').textContent = 'KES ' + (price + setupFee).toLocaleString();
    });
});
</script>
@endsection
