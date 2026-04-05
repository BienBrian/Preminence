<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'tenant_id',
        'invoice_number',
        'invoice_id',
        'module_key',
        'description',
        'type',
        'unit_price',
        'quantity',
        'amount',
        'tax_amount',
        'total_amount',
        'currency',
        'period_start',
        'period_end',
        'days_billed',
        'status',
        'billed_at',
        'paid_at',
        'payment_method',
        'transaction_id',
        'proration_details',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'days_billed' => 'integer',
        'billed_at' => 'datetime',
        'paid_at' => 'datetime',
        'proration_details' => 'array',
        'metadata' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantModuleSubscription::class, 'subscription_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_key', 'key');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInvoiced($query)
    {
        return $query->where('status', 'invoiced');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('period_start', [$start, $end])
                     ->orWhereBetween('period_end', [$start, $end]);
    }

    public function scopeRecurring($query)
    {
        return $query->whereIn('type', ['monthly_recurring', 'yearly_recurring']);
    }

    public function scopeProrated($query)
    {
        return $query->whereIn('type', ['prorated_charge', 'prorated_credit']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isRecurring(): bool
    {
        return in_array($this->type, ['monthly_recurring', 'yearly_recurring']);
    }

    public function isProrated(): bool
    {
        return in_array($this->type, ['prorated_charge', 'prorated_credit']);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markAsInvoiced(string $invoiceNumber): void
    {
        $this->update([
            'status' => 'invoiced',
            'invoice_number' => $invoiceNumber,
            'billed_at' => now(),
        ]);
    }

    public function markAsPaid(?string $paymentMethod = null, ?string $transactionId = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function markAsRefunded(): void
    {
        $this->update(['status' => 'refunded']);
    }

    public function getFormattedAmount(): string
    {
        return sprintf('%s %s', $this->currency, number_format($this->total_amount, 2));
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'monthly_recurring' => 'Monthly Subscription',
            'yearly_recurring' => 'Yearly Subscription',
            'setup_fee' => 'Setup Fee',
            'prorated_charge' => 'Prorated Charge',
            'prorated_credit' => 'Prorated Credit',
            'overage' => 'Usage Overage',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'invoiced' => 'Invoiced',
            'paid' => 'Paid',
            'failed' => 'Payment Failed',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'paid' => 'success',
            'pending', 'invoiced' => 'warning',
            'failed' => 'danger',
            'refunded', 'cancelled' => 'secondary',
            default => 'secondary',
        };
    }
}
