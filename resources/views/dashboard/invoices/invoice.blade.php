@extends('dashboard.layouts.app')

@section('title', 'Invoice ' . $invoiceNumber)

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Invoice {{ $invoiceNumber }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('invoices.download', $invoiceNumber) }}" class="btn btn-outline-primary">
                <i class="bi bi-download me-1"></i> Download PDF
            </a>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-4">
            {{-- Invoice Header --}}
            <div class="row mb-4">
                <div class="col-6">
                    <h2 class="text-primary fw-bold mb-1">INVOICE</h2>
                    <div class="text-muted small"># {{ $invoiceNumber }}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="text-muted small mb-1">Invoice Date</div>
                    <div class="fw-semibold">
                        {{ $firstItem->created_at ? \Carbon\Carbon::parse($firstItem->created_at)->format('d M Y') : '—' }}
                    </div>
                    <div class="mt-2">
                        @if($firstItem->status === 'paid')
                            <span class="badge bg-success fs-6">PAID</span>
                        @elseif($firstItem->status === 'pending')
                            <span class="badge bg-warning text-dark fs-6">PENDING</span>
                        @else
                            <span class="badge bg-secondary fs-6">{{ strtoupper($firstItem->status) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <hr>

            {{-- Line Items --}}
            <h6 class="fw-bold mb-3">Items</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Period</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ ucfirst($item->module_key) }} Module</div>
                                    <small class="text-muted">{{ $item->type ?? 'Subscription' }}</small>
                                </td>
                                <td>
                                    @if($item->period_start && $item->period_end)
                                        {{ \Carbon\Carbon::parse($item->period_start)->format('d M Y') }}
                                        — {{ \Carbon\Carbon::parse($item->period_end)->format('d M Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">KES {{ number_format($item->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="2" class="text-end">Total</td>
                            <td class="text-end">KES {{ number_format($total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Payment Details --}}
            @if($firstItem->status === 'paid' && $firstItem->paid_at)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Payment received on <strong>{{ \Carbon\Carbon::parse($firstItem->paid_at)->format('d M Y, h:i A') }}</strong>
                    @if($firstItem->payment_method)
                        via <strong>{{ ucfirst($firstItem->payment_method) }}</strong>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
