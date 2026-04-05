@extends('superadmin.layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('superadmin.billing.invoices') }}" class="btn btn-link text-decoration-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="h3 mb-0 ms-2">Invoice Details</h1>
        </div>
        <div class="btn-group">
            <a href="#" class="btn btn-outline-secondary" onclick="window.print();">
                <i class="bi bi-printer me-2"></i>Print
            </a>
            @if($invoice->status === 'pending')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markPaidModal">
                <i class="bi bi-check-circle me-2"></i>Mark as Paid
            </button>
            @endif
            @if($invoice->status === 'paid')
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                <i class="bi bi-arrow-counterclockwise me-2"></i>Refund
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Invoice Details Card --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice Information</h5>
                    <span class="badge bg-{{ $invoice->getStatusColor() }} fs-6">
                        {{ $invoice->getStatusLabel() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">Invoice Number</div>
                            <div class="h5">{{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}</div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted small">Date Created</div>
                            <div class="h5">{{ $invoice->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="text-muted small">Tenant</div>
                            <div class="h5">
                                <a href="{{ route('superadmin.billing.tenant', $invoice->tenant_id) }}">
                                    {{ $invoice->tenant->name ?? 'Unknown' }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted small">Module</div>
                            <div class="h5">{{ $invoice->module?->name ?? $invoice->module_key }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h6>Description</h6>
                        <p class="mb-0">{{ $invoice->description }}</p>
                    </div>

                    @if($invoice->notes)
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Notes</h6>
                        <p class="mb-0">{{ $invoice->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Amount Details --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Amount Breakdown</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td class="text-muted">Unit Price</td>
                                <td class="text-end">KES {{ number_format($invoice->unit_price, 2) }}</td>
                            </tr>
                            @if($invoice->quantity != 1)
                            <tr>
                                <td class="text-muted">Quantity</td>
                                <td class="text-end">{{ $invoice->quantity }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Subtotal</td>
                                <td class="text-end">KES {{ number_format($invoice->amount, 2) }}</td>
                            </tr>
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td class="text-muted">Tax</td>
                                <td class="text-end">KES {{ number_format($invoice->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-top">
                                <td class="h5">Total</td>
                                <td class="text-end h5">KES {{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Timeline --}}
            @if($timeline)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Transaction Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($timeline as $event)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-circle-fill text-{{ $event['status'] === 'success' ? 'success' : 'secondary' }}"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $event['type'] ?? 'Event' }}</strong>
                                    <small class="text-muted">{{ $event['created_at'] ?? '' }}</small>
                                </div>
                                @if(!empty($event['message']))
                                <p class="text-muted small mb-0">{{ $event['message'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Payment Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Payment Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">
                                <span class="badge bg-{{ $invoice->getStatusColor() }}">
                                    {{ $invoice->getStatusLabel() }}
                                </span>
                            </td>
                        </tr>
                        @if($invoice->payment_method)
                        <tr>
                            <td class="text-muted">Method</td>
                            <td class="text-end text-capitalize">{{ $invoice->payment_method }}</td>
                        </tr>
                        @endif
                        @if($invoice->transaction_id)
                        <tr>
                            <td class="text-muted">Transaction ID</td>
                            <td class="text-end">
                                <code class="small">{{ $invoice->transaction_id }}</code>
                            </td>
                        </tr>
                        @endif
                        @if($invoice->billed_at)
                        <tr>
                            <td class="text-muted">Billed At</td>
                            <td class="text-end">{{ $invoice->billed_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($invoice->paid_at)
                        <tr>
                            <td class="text-muted">Paid At</td>
                            <td class="text-end">{{ $invoice->paid_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Subscription Info --}}
            @if($invoice->subscription)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Subscription Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">
                                <span class="badge bg-{{ $invoice->subscription->isActive() ? 'success' : 'secondary' }}">
                                    {{ ucfirst($invoice->subscription->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Billing Type</td>
                            <td class="text-end">{{ $invoice->subscription->getBillingPeriodLabel() }}</td>
                        </tr>
                        @if($invoice->subscription->next_billing_at)
                        <tr>
                            <td class="text-muted">Next Billing</td>
                            <td class="text-end">{{ $invoice->subscription->next_billing_at->format('M d, Y') }}</td>
                        </tr>
                        @endif
                    </table>
                    <a href="{{ route('superadmin.billing.tenant', $invoice->tenant_id) }}" class="btn btn-outline-primary btn-sm w-100">
                        View Tenant Billing
                    </a>
                </div>
            </div>
            @endif

            {{-- Related Invoices --}}
            @if($invoice->subscription)
            @php
            $related = \App\Models\ModuleInvoiceItem::where('subscription_id', $invoice->subscription_id)
                ->where('id', '!=', $invoice->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            @endphp
            @if($related->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Related Invoices</h6>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($related as $rel)
                    <a href="{{ route('superadmin.billing.invoice.show', $rel) }}" class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span>{{ $rel->created_at->format('M d, Y') }}</span>
                            <span class="badge bg-{{ $rel->getStatusColor() }}">
                                KES {{ number_format($rel->total_amount, 0) }}
                            </span>
                        </div>
                        <small class="text-muted">{{ $rel->getTypeLabel() }}</small>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

{{-- Mark as Paid Modal --}}
@if($invoice->status === 'pending')
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.billing.invoice.mark-paid', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Mark Invoice as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID (Optional)</label>
                        <input type="text" name="transaction_id" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Refund Modal --}}
@if($invoice->status === 'paid')
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.billing.invoice.refund', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Process Refund</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Refund Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">KES</span>
                            <input type="number" name="amount" class="form-control" step="0.01" 
                                   max="{{ $invoice->total_amount }}" 
                                   placeholder="Leave empty for full refund (KES {{ number_format($invoice->total_amount, 2) }})">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Refund <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Process Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
