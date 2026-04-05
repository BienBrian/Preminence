@extends('superadmin.layouts.app')

@section('title', 'Tenant Billing - ' . $tenant->name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('superadmin.billing.index') }}" class="btn btn-link text-decoration-none">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="h3 mb-0 ms-2">{{ $tenant->name }} - Billing</h1>
        </div>
        <a href="{{ route('superadmin.tenants.show', $tenant->id) }}" class="btn btn-outline-primary">
            <i class="bi bi-building me-2"></i>View Tenant
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="text-muted small">Total Paid</div>
                    <div class="h4 mb-0">KES {{ number_format($summary['total_paid'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="text-muted small">Pending</div>
                    <div class="h4 mb-0">KES {{ number_format($summary['total_pending'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="text-muted small">Active Subscriptions</div>
                    <div class="h4 mb-0">{{ $subscriptions->where('status', 'active')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="text-muted small">Upcoming (60 days)</div>
                    <div class="h4 mb-0">KES {{ number_format($upcoming->sum('price'), 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Invoices --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Invoice History</h5>
                    <a href="{{ route('superadmin.billing.invoices', ['tenant_id' => $tenant->id]) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
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
                                <td>{{ $invoice->getTypeLabel() }}</td>
                                <td>KES {{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->getStatusColor() }}">
                                        {{ $invoice->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No invoices found</td>
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

        {{-- Subscriptions --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Active Subscriptions</h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($subscriptions->where('status', 'active') as $sub)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center">
                                    <i class="bi {{ $sub->module?->icon ?? 'bi-box' }} me-2 text-muted"></i>
                                    <strong>{{ $sub->module?->name ?? $sub->module_key }}</strong>
                                </div>
                                <small class="text-muted">{{ $sub->getBillingPeriodLabel() }}</small>
                            </div>
                            <div class="text-end">
                                <div>KES {{ number_format($sub->price, 2) }}</div>
                                @if($sub->next_billing_at)
                                <small class="text-muted">
                                    Next: {{ $sub->next_billing_at->format('M d') }}
                                </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center py-3 text-muted">
                        No active subscriptions
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Upcoming Billings --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Upcoming Billings</h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($upcoming as $sub)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span>{{ $sub->module?->name ?? $sub->module_key }}</span>
                            <span>KES {{ number_format($sub->price, 0) }}</span>
                        </div>
                        <small class="text-muted">
                            {{ $sub->next_billing_at->format('M d, Y') }}
                            @if($sub->next_billing_at->diffInDays(now()) <= 7)
                            <span class="badge bg-warning ms-1">Soon</span>
                            @endif
                        </small>
                    </div>
                    @empty
                    <div class="list-group-item text-center py-3 text-muted">
                        No upcoming billings
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
