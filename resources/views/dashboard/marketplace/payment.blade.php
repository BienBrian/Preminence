@extends('dashboard.layouts.app')

@section('title', 'Complete Payment - ' . $module->name)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('marketplace.show', $module->key) }}" class="btn btn-link text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <h1 class="h3 mb-0 ms-3">Complete Your Purchase</h1>
            </div>

            {{-- Module Summary --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="bi {{ $module->icon ?? 'bi-box' }} fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">{{ $module->name }}</h5>
                            <p class="text-muted mb-0">{{ $module->short_description }}</p>
                        </div>
                        <div class="text-end">
                            <div class="h4 mb-0">
                                @if($subscription->billing_type === 'addon_yearly')
                                    KES {{ number_format($module->price_yearly, 0) }}<small>/year</small>
                                @else
                                    KES {{ number_format($module->price_monthly, 0) }}<small>/month</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Module Price</span>
                        <span>KES {{ number_format($price->getPrice($subscription->billing_type === 'addon_yearly' ? 'yearly' : 'monthly'), 2) }}</span>
                    </div>
                    @if($module->setup_fee > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Setup Fee</span>
                        <span>KES {{ number_format($module->setup_fee, 2) }}</span>
                    </div>
                    @endif
                    @if($price->prorationCredit > 0)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Proration Credit</span>
                        <span>-KES {{ number_format($price->prorationCredit, 2) }}</span>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total Due Today</strong>
                        <strong class="h5">KES {{ number_format($price->total, 2) }}</strong>
                    </div>
                </div>
            </div>

            {{-- Payment Form --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('marketplace.process-payment', $subscription) }}" method="POST">
                        @csrf
                        
                        {{-- Payment Method Selection --}}
                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="mpesa" value="mpesa" checked>
                                <label class="form-check-label d-flex align-items-center" for="mpesa">
                                    <i class="bi bi-phone me-2"></i>
                                    <div>
                                        <strong>M-Pesa</strong>
                                        <small class="text-muted d-block">Pay via M-Pesa mobile money</small>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                                <label class="form-check-label d-flex align-items-center" for="card">
                                    <i class="bi bi-credit-card me-2"></i>
                                    <div>
                                        <strong>Card Payment</strong>
                                        <small class="text-muted d-block">Visa, Mastercard</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Terms --}}
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" target="_blank">Terms of Service</a> and 
                                    <a href="#" target="_blank">Subscription Agreement</a>
                                </label>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-lock me-2"></i>
                                Pay KES {{ number_format($price->total, 2) }}
                            </button>
                            <a href="{{ route('marketplace.show', $module->key) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex align-items-center justify-content-center text-muted">
                        <i class="bi bi-shield-check me-2"></i>
                        <small>Secure payment processed by Pisti</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
