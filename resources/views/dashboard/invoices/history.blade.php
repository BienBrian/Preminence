@extends('dashboard.layouts.app')

@section('title', 'Billing History')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Billing History</h1>
        <div class="btn-group">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-receipt me-1"></i> All Invoices
            </a>
            <a href="{{ route('invoices.upcoming') }}" class="btn btn-outline-primary">
                <i class="bi bi-calendar-event me-1"></i> Upcoming
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small">Year</label>
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small">Module</label>
                    <select name="module" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Modules</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                        @endforeach
                    </select>
                </div>
                @if(request('year') || request('module'))
                    <div class="col-auto">
                        <a href="{{ route('invoices.history') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x"></i> Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- History Table --}}
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Module</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Paid On</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold">
                                        {{ $invoice->invoice_number ?? '#' . $invoice->id }}
                                    </a>
                                </td>
                                <td>{{ ucfirst($invoice->module_key) }}</td>
                                <td>
                                    @if($invoice->period_start && $invoice->period_end)
                                        {{ \Carbon\Carbon::parse($invoice->period_start)->format('d M Y') }}
                                        — {{ \Carbon\Carbon::parse($invoice->period_end)->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>KES {{ number_format($invoice->total_amount, 2) }}</td>
                                <td>
                                    @if($invoice->status === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($invoice->status === 'refunded')
                                        <span class="badge bg-info">Refunded</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') : '—' }}</td>
                                <td>
                                    @if($invoice->invoice_number)
                                        <a href="{{ route('invoices.download', $invoice->invoice_number) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-clock-history fs-2 opacity-25 d-block mb-2"></i>
                                    No payment history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($history->hasPages())
            <div class="card-footer">{{ $history->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
