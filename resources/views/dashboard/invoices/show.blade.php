@extends('dashboard.layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('invoices.index') }}" class="btn btn-link text-decoration-none">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <h1 class="h3 mb-0 ms-3">Invoice Details</h1>
        </div>
        <div class="btn-group">
            <a href="{{ route('invoices.download', $invoiceItem->invoice_number ?? $invoiceItem->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-download me-2"></i>Download
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Invoice Card --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $invoiceItem->module?->name ?? $invoiceItem->module_key }}</h5>
                    <span class="badge bg-{{ $invoiceItem->getStatusColor() }}">
                        {{ $invoiceItem->getStatusLabel() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small mb-1">Invoice Number</div>
                            <div class="h5">{{ $invoiceItem->invoice_number ?? 'INV-' . $invoiceItem->id }}</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted small mb-1">Date</div>
                            <div class="h5">{{ $invoiceItem->created_at->format('M d, Y') }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6>Description</h6>
                        <p>{{ $invoiceItem->description }}</p>
                    </div>

                    @if($invoiceItem->proration_details)
                    <div class="mb-4">
                        <h6>Proration Details</h6>
                        <table class="table table-sm table-borderless">
                            @if(isset($invoiceItem->proration_details['days_remaining']))
                            <tr>
                                <td>Days Remaining:</td>
                                <td class="text-end">{{ $invoiceItem->proration_details['days_remaining'] }}</td>
                            </tr>
                            @endif
                            @if(isset($invoiceItem->proration_details['daily_rate']))
                            <tr>
                                <td>Daily Rate:</td>
                                <td class="text-end">KES {{ number_format($invoiceItem->proration_details['daily_rate'], 4) }}</td>
                            </tr>
                            @endif
                            @if(isset($invoiceItem->proration_details['proration_factor']))
                            <tr>
                                <td>Proration Factor:</td>
                                <td class="text-end">{{ number_format($invoiceItem->proration_details['proration_factor'] * 100, 2) }}%</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    @endif

                    <hr>

                    {{-- Amount Breakdown --}}
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Unit Price</td>
                                    <td class="text-end">KES {{ number_format($invoiceItem->unit_price, 2) }}</td>
                                </tr>
                                @if($invoiceItem->quantity != 1)
                                <tr>
                                    <td>Quantity</td>
                                    <td class="text-end">{{ $invoiceItem->quantity }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">KES {{ number_format($invoiceItem->amount, 2) }}</td>
                                </tr>
                                @if($invoiceItem->tax_amount > 0)
                                <tr>
                                    <td>Tax</td>
                                    <td class="text-end">KES {{ number_format($invoiceItem->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-active">
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong class="h5">KES {{ number_format($invoiceItem->total_amount, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Period Info --}}
            @if($invoiceItem->period_start && $invoiceItem->period_end)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Billing Period</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-muted small">Start Date</div>
                            <div>{{ $invoiceItem->period_start->format('M d, Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">End Date</div>
                            <div>{{ $invoiceItem->period_end->format('M d, Y') }}</div>
                        </div>
                    </div>
                    @if($invoiceItem->days_billed)
                    <div class="mt-3">
                        <div class="text-muted small">Days Billed</div>
                        <div>{{ $invoiceItem->days_billed }} days</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Payment Status --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Payment Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Status</div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-{{ $invoiceItem->getStatusColor() }} me-2">
                                {{ $invoiceItem->getStatusLabel() }}
                            </span>
                        </div>
                    </div>

                    @if($invoiceItem->paid_at)
                    <div class="mb-3">
                        <div class="text-muted small">Paid On</div>
                        <div>{{ $invoiceItem->paid_at->format('M d, Y H:i') }}</div>
                    </div>
                    @endif

                    @if($invoiceItem->payment_method)
                    <div class="mb-3">
                        <div class="text-muted small">Payment Method</div>
                        <div class="text-capitalize">{{ $invoiceItem->payment_method }}</div>
                    </div>
                    @endif

                    @if($invoiceItem->transaction_id)
                    <div class="mb-3">
                        <div class="text-muted small">Transaction ID</div>
                        <code>{{ $invoiceItem->transaction_id }}</code>
                    </div>
                    @endif

                    @if($invoiceItem->notes)
                    <div class="mb-3">
                        <div class="text-muted small">Notes</div>
                        <div>{{ $invoiceItem->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Payment Action --}}
            @if($invoiceItem->status === 'pending')
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">Pay Invoice</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Complete payment to activate or continue your subscription.</p>
                    <form action="{{ route('invoices.pay', $invoiceItem) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="mpesa">M-Pesa</option>
                                <option value="card">Credit/Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-credit-card me-2"></i>
                            Pay KES {{ number_format($invoiceItem->total_amount, 2) }}
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
