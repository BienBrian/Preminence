@extends('dashboard.layouts.app')

@section('title', 'Upcoming Billings')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Upcoming Billings</h1>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-receipt me-2"></i>All Invoices
        </a>
    </div>

    {{-- Total Summary --}}
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Total Upcoming</strong>
                <p class="mb-0">You have <strong>KES {{ number_format($totalUpcoming, 2) }}</strong> in upcoming charges.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            {{-- Pending Invoice Items --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Pending Invoice Items</h5>
                </div>
                <div class="card-body">
                    @if($pending->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle fs-2 d-block mb-2"></i>
                        No pending invoice items
                    </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($pending as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi {{ $item->module?->icon ?? 'bi-box' }} me-2 text-muted"></i>
                                    <strong>{{ $item->module?->name ?? $item->module_key }}</strong>
                                </div>
                                <small class="text-muted d-block">{{ $item->description }}</small>
                                @if($item->period_start && $item->period_end)
                                <small class="text-muted">
                                    {{ $item->period_start->format('M d') }} - {{ $item->period_end->format('M d, Y') }}
                                </small>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="h5 mb-0">KES {{ number_format($item->total_amount, 2) }}</div>
                                <span class="badge bg-warning">Pending</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            {{-- Subscription-Based Billings --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recurring Subscriptions</h5>
                </div>
                <div class="card-body">
                    @if($subscriptions->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                        No active subscriptions
                    </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($subscriptions as $sub)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi {{ $sub->module?->icon ?? 'bi-box' }} me-2 text-muted"></i>
                                        <strong>{{ $sub->module?->name ?? $sub->module_key }}</strong>
                                    </div>
                                    <small class="text-muted d-block">
                                        {{ $sub->getBillingPeriodLabel() }}
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="h5 mb-0">KES {{ number_format($sub->price, 2) }}</div>
                                </div>
                            </div>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    Next billing: {{ $sub->next_billing_at->format('M d, Y') }}
                                    @if($sub->next_billing_at->isPast())
                                    <span class="badge bg-danger ms-1">Overdue</span>
                                    @elseif($sub->next_billing_at->diffInDays(now()) <= 7)
                                    <span class="badge bg-warning ms-1">Soon</span>
                                    @endif
                                </small>
                                <a href="{{ route('my-modules.billing', $sub) }}" class="btn btn-sm btn-outline-primary">
                                    Manage
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
