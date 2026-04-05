@extends('superadmin.layouts.app')

@section('title', 'Payment Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Payment Settings</h1>
        <a href="{{ route('superadmin.billing.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Billing
        </a>
    </div>

    <div class="row">
        {{-- PayStack Configuration --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>PayStack Configuration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('superadmin.billing.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Get your API keys from <a href="https://dashboard.paystack.com/" target="_blank">PayStack Dashboard</a>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Public Key</label>
                            <input type="text" name="paystack_public_key" class="form-control font-monospace" 
                                   value="{{ $settings['paystack_public_key'] }}" required>
                            <div class="form-text">Starts with pk_test_ (sandbox) or pk_live_ (production)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Secret Key</label>
                            <input type="password" name="paystack_secret_key" class="form-control font-monospace" 
                                   value="{{ $settings['paystack_secret_key'] }}" required>
                            <div class="form-text">Starts with sk_test_ (sandbox) or sk_live_ (production)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Webhook Secret</label>
                            <input type="password" name="paystack_webhook_secret" class="form-control font-monospace" 
                                   value="{{ $settings['paystack_webhook_secret'] }}">
                            <div class="form-text">Used to verify webhook requests from PayStack</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="paystack_is_live" 
                                       name="paystack_is_live" value="1" 
                                       {{ $settings['paystack_is_live'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="paystack_is_live">
                                    Live Mode (Production)
                                </label>
                            </div>
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Only enable live mode when ready to process real payments
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select name="paystack_currency" class="form-select">
                                <option value="KES" {{ $settings['paystack_currency'] === 'KES' ? 'selected' : '' }}>
                                    KES - Kenyan Shilling
                                </option>
                                <option value="NGN" {{ $settings['paystack_currency'] === 'NGN' ? 'selected' : '' }}>
                                    NGN - Nigerian Naira
                                </option>
                                <option value="USD" {{ $settings['paystack_currency'] === 'USD' ? 'selected' : '' }}>
                                    USD - US Dollar
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Webhook Info --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-webhook me-2"></i>Webhook URL</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Configure this URL in your PayStack Dashboard to receive payment notifications.</p>
                    
                    <div class="input-group mb-3">
                        <input type="text" class="form-control font-monospace" value="{{ $webhookUrl }}" readonly id="webhookUrl">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyWebhookUrl()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>

                    <h6 class="mt-4">Required Events:</h6>
                    <ul class="list-unstyled small">
                        <li><i class="bi bi-check-circle text-success me-2"></i>charge.success</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>charge.failed</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>refund.processed</li>
                    </ul>

                    <a href="https://dashboard.paystack.com/#/settings/webhooks" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-box-arrow-up-right me-2"></i>Open PayStack Dashboard
                    </a>
                </div>
            </div>

            {{-- Environment Status --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear-wide-connected me-2"></i>Environment</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Mode:</td>
                            <td>
                                @if($settings['paystack_is_live'])
                                <span class="badge bg-success">Live</span>
                                @else
                                <span class="badge bg-warning">Test/Sandbox</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Public Key:</td>
                            <td>
                                @if(!empty($settings['paystack_public_key']))
                                <span class="badge bg-success">Configured</span>
                                @else
                                <span class="badge bg-danger">Missing</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Secret Key:</td>
                            <td>
                                @if(!empty($settings['paystack_secret_key']))
                                <span class="badge bg-success">Configured</span>
                                @else
                                <span class="badge bg-danger">Missing</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Webhook:</td>
                            <td>
                                @if(!empty($settings['paystack_webhook_secret']))
                                <span class="badge bg-success">Configured</span>
                                @else
                                <span class="badge bg-warning">Not Set</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyWebhookUrl() {
    const copyText = document.getElementById('webhookUrl');
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    // Show feedback
    const button = document.querySelector('button[onclick="copyWebhookUrl()"]');
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="bi bi-check"></i>';
    setTimeout(() => {
        button.innerHTML = originalHtml;
    }, 2000);
}
</script>
@endpush
@endsection
