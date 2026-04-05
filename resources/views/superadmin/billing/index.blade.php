@extends('superadmin.layouts.app')

@section('title', 'Billing & Payments')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Billing & Payments</h1>
        <div class="btn-group">
            <a href="{{ route('superadmin.billing.invoices') }}" class="btn btn-outline-primary">
                <i class="bi bi-receipt me-2"></i>All Invoices
            </a>
            <a href="{{ route('superadmin.billing.subscriptions') }}" class="btn btn-outline-primary">
                <i class="bi bi-collection me-2"></i>Subscriptions
            </a>
            <a href="{{ route('superadmin.billing.settings') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-2"></i>Settings
            </a>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="bi bi-cash-stack fs-3 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Revenue (Period)</div>
                            <div class="h4 mb-0">KES {{ number_format($stats['total_revenue'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="bi bi-clock fs-3 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Pending</div>
                            <div class="h4 mb-0">KES {{ number_format($stats['total_pending'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="bi bi-x-circle fs-3 text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Failed</div>
                            <div class="h4 mb-0">KES {{ number_format($stats['total_failed'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="bi bi-people fs-3 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="text-muted small">Active Subscriptions</div>
                            <div class="h4 mb-0">{{ number_format($stats['active_subscriptions']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($stats['overdue_subscriptions'] > 0)
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
        <div>
            <strong>{{ $stats['overdue_subscriptions'] }} subscriptions</strong> are overdue for payment.
            <a href="{{ route('superadmin.billing.subscriptions') }}?status=active" class="alert-link">View subscriptions</a>
        </div>
    </div>
    @endif

    <div class="row">
        {{-- Revenue by Module --}}
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Revenue by Module</h5>
                </div>
                <div class="card-body">
                    @if($revenueByModule->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bar-chart fs-2 d-block mb-2"></i>
                        No data for this period
                    </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($revenueByModule as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $item->module_key }}</span>
                            <span class="badge bg-primary">KES {{ number_format($item->total, 0) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Tenants --}}
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Top Paying Tenants</h5>
                </div>
                <div class="card-body">
                    @if($revenueByTenant->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-building fs-2 d-block mb-2"></i>
                        No data for this period
                    </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($revenueByTenant as $item)
                        <a href="{{ route('superadmin.billing.tenant', $item->tenant_id) }}" class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $item->tenant->name ?? 'Unknown' }}</span>
                            <span class="badge bg-success">KES {{ number_format($item->total, 0) }}</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    @if($recentTransactions->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                        No recent transactions
                    </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($recentTransactions as $transaction)
                        <a href="{{ route('superadmin.billing.invoice.show', $transaction) }}" class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span class="text-truncate" style="max-width: 60%;">
                                    {{ $transaction->tenant->name ?? 'Unknown' }}
                                </span>
                                <span class="badge bg-{{ $transaction->getStatusColor() }}">
                                    KES {{ number_format($transaction->total_amount, 0) }}
                                </span>
                            </div>
                            <small class="text-muted">{{ $transaction->created_at->diffForHumans() }}</small>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh stats every 5 minutes
    setInterval(function() {
        fetch('{{ route('superadmin.billing.api.stats') }}')
            .then(response => response.json())
            .then(data => {
                // Update stats on page
                console.log('Stats updated:', data);
            });
    }, 300000);
</script>
@endpush
