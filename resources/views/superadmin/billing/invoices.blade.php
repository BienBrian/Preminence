@extends('superadmin.layouts.app')

@section('title', 'All Invoices')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">All Invoices</h1>
        <div class="btn-group">
            <a href="{{ route('superadmin.billing.generate-invoices') }}" class="btn btn-primary" 
               onclick="event.preventDefault(); document.getElementById('generate-form').submit();">
                <i class="bi bi-plus-circle me-2"></i>Generate Invoices
            </a>
            <form id="generate-form" action="{{ route('superadmin.billing.generate-invoices') }}" method="POST" class="d-none">
                @csrf
            </form>
            <a href="{{ route('superadmin.billing.export') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download me-2"></i>Export
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tenant</label>
                    <select name="tenant_id" class="form-select">
                        <option value="">All Tenants</option>
                        @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Invoice #" value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Invoices Table --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Tenant</th>
                        <th>Module</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('superadmin.billing.invoice.show', $invoice) }}">
                                {{ $invoice->invoice_number ?? 'INV-' . $invoice->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('superadmin.billing.tenant', $invoice->tenant_id) }}">
                                {{ $invoice->tenant->name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td>{{ $invoice->module?->name ?? $invoice->module_key }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $invoice->getTypeLabel() }}</span>
                        </td>
                        <td>KES {{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $invoice->getStatusColor() }}">
                                {{ $invoice->getStatusLabel() }}
                            </span>
                        </td>
                        <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('superadmin.billing.invoice.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($invoice->status === 'pending')
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $invoice->id }}">
                                <i class="bi bi-check"></i>
                            </button>
                            @endif
                            @if($invoice->status === 'paid')
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal{{ $invoice->id }}">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No invoices found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Mark as Paid Modals --}}
@foreach($invoices as $invoice)
@if($invoice->status === 'pending')
<div class="modal fade" id="markPaidModal{{ $invoice->id }}" tabindex="-1">
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
                        <label class="form-label">Payment Method</label>
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

@if($invoice->status === 'paid')
<div class="modal fade" id="refundModal{{ $invoice->id }}" tabindex="-1">
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
                            <input type="number" name="amount" class="form-control" step="0.01" max="{{ $invoice->total_amount }}" placeholder="Leave empty for full refund">
                        </div>
                        <div class="form-text">Leave empty to refund full amount (KES {{ number_format($invoice->total_amount, 2) }})</div>
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
@endforeach
@endsection
