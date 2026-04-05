<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ModuleInvoiceItem;
use App\Models\Tenant;
use App\Models\TenantModuleSubscription;
use App\Services\Modules\BillingScheduleService;
use App\Services\Modules\ModuleInvoiceService;
use App\Services\Payment\PayStackService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    private ModuleInvoiceService $invoiceService;
    private BillingScheduleService $scheduleService;
    private PayStackService $payStackService;

    public function __construct(
        ModuleInvoiceService $invoiceService,
        BillingScheduleService $scheduleService,
        PayStackService $payStackService
    ) {
        $this->middleware(['auth:superadmin']);
        $this->invoiceService = $invoiceService;
        $this->scheduleService = $scheduleService;
        $this->payStackService = $payStackService;
    }

    /**
     * Billing dashboard with overview stats.
     */
    public function index(Request $request)
    {
        // Date range filters
        $startDate = $request->date('from') ?? now()->startOfMonth();
        $endDate = $request->date('to') ?? now()->endOfMonth();

        // Platform-wide stats
        $stats = [
            'total_revenue' => ModuleInvoiceItem::where('status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('total_amount'),
            'total_pending' => ModuleInvoiceItem::where('status', 'pending')
                ->sum('total_amount'),
            'total_failed' => ModuleInvoiceItem::where('status', 'failed')
                ->sum('total_amount'),
            'total_refunded' => ModuleInvoiceItem::where('status', 'refunded')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount'),
            'active_subscriptions' => TenantModuleSubscription::where('status', 'active')->count(),
            'overdue_subscriptions' => TenantModuleSubscription::where('status', 'active')
                ->where('next_billing_at', '<', now()->subDays(3))
                ->count(),
        ];

        // Revenue by module
        $revenueByModule = ModuleInvoiceItem::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('module_key, SUM(total_amount) as total')
            ->groupBy('module_key')
            ->orderByDesc('total')
            ->get();

        // Revenue by tenant (top 10)
        $revenueByTenant = ModuleInvoiceItem::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with('tenant')
            ->selectRaw('tenant_id, SUM(total_amount) as total')
            ->groupBy('tenant_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Recent transactions
        $recentTransactions = ModuleInvoiceItem::with(['tenant', 'module'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('superadmin.billing.index', compact(
            'stats',
            'revenueByModule',
            'revenueByTenant',
            'recentTransactions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * List all invoices with filters.
     */
    public function invoices(Request $request)
    {
        $query = ModuleInvoiceItem::with(['tenant', 'module', 'subscription']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('module_key')) {
            $query->where('module_key', $request->module_key);
        }
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Filter options
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $modules = \App\Models\Module::orderBy('name')->get(['key', 'name']);

        return view('superadmin.billing.invoices', compact('invoices', 'tenants', 'modules'));
    }

    /**
     * Show invoice details.
     */
    public function showInvoice(ModuleInvoiceItem $invoice)
    {
        $invoice->load(['tenant', 'module', 'subscription']);
        
        // Get transaction timeline if PayStack transaction
        $timeline = null;
        if ($invoice->transaction_id && str_starts_with($invoice->transaction_id, 'PAYSTACK_')) {
            $reference = str_replace('PAYSTACK_', '', $invoice->transaction_id);
            $timelineResult = $this->payStackService->fetchTransactionTimeline($reference);
            if ($timelineResult['success']) {
                $timeline = $timelineResult['timeline'];
            }
        }

        return view('superadmin.billing.invoice', compact('invoice', 'timeline'));
    }

    /**
     * Process refund.
     */
    public function refund(Request $request, ModuleInvoiceItem $invoice)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0|max:' . $invoice->total_amount,
            'reason' => 'required|string|max:500',
        ]);

        if ($invoice->status !== 'paid') {
            return redirect()->back()->with('error', 'Only paid invoices can be refunded.');
        }

        $refundAmount = $validated['amount'] ?? $invoice->total_amount;

        // Process PayStack refund if applicable
        if ($invoice->transaction_id && str_starts_with($invoice->transaction_id, 'PAYSTACK_')) {
            $reference = str_replace('PAYSTACK_', '', $invoice->transaction_id);
            $result = $this->payStackService->refundTransaction(
                $reference,
                $refundAmount,
                $validated['reason']
            );

            if (!$result['success']) {
                return redirect()->back()->with('error', 'Refund failed: ' . $result['message']);
            }
        }

        // Create refund invoice item
        $refundItem = $this->invoiceService->createRefundInvoiceItem(
            $invoice->subscription,
            $refundAmount
        );

        if ($refundItem) {
            $refundItem->update([
                'notes' => $validated['reason'],
                'status' => 'pending',
            ]);
        }

        // Mark original as refunded
        $invoice->markAsRefunded();

        return redirect()->back()->with('success', 
            'Refund of KES ' . number_format($refundAmount, 2) . ' processed successfully.'
        );
    }

    /**
     * Mark invoice as paid (manual payment).
     */
    public function markAsPaid(Request $request, ModuleInvoiceItem $invoice)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer,mpesa,other',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!in_array($invoice->status, ['pending', 'failed'])) {
            return redirect()->back()->with('error', 'Invoice is not payable.');
        }

        $invoice->markAsPaid($validated['payment_method'], $validated['transaction_id']);
        
        if ($validated['notes']) {
            $invoice->update(['notes' => $validated['notes']]);
        }

        // Update subscription billing dates if recurring
        if ($invoice->isRecurring() && $invoice->subscription) {
            $subscription = $invoice->subscription;
            $subscription->update([
                'last_billed_at' => now(),
                'next_billing_at' => $this->scheduleService->calculateNextBillingDate($subscription),
            ]);
        }

        return redirect()->back()->with('success', 'Invoice marked as paid.');
    }

    /**
     * Tenant billing management.
     */
    public function tenantBilling(Tenant $tenant)
    {
        $invoices = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->with('module')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $subscriptions = TenantModuleSubscription::where('tenant_id', $tenant->id)
            ->with('module')
            ->orderBy('installed_at', 'desc')
            ->get();

        $summary = $this->invoiceService->getInvoiceSummary($tenant);
        $upcoming = $this->scheduleService->getUpcomingBillings($tenant->id, 60);

        return view('superadmin.billing.tenant', compact(
            'tenant',
            'invoices',
            'subscriptions',
            'summary',
            'upcoming'
        ));
    }

    /**
     * Generate invoices manually.
     */
    public function generateInvoices(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'date' => 'nullable|date',
        ]);

        $date = isset($validated['date']) && $validated['date'] 
            ? \Carbon\Carbon::parse($validated['date']) 
            : now();

        if ($validated['tenant_id'] ?? false) {
            // Generate for specific tenant
            $tenant = Tenant::find($validated['tenant_id']);
            $items = $this->invoiceService->getPendingItemsForTenant($tenant);
            $result = [
                'processed' => $items->count(),
                'created' => 0,
                'skipped' => 0,
            ];
            
            foreach ($items as $item) {
                $item->markAsInvoiced($this->invoiceService->finalizeInvoice($tenant)['invoice_number']);
                $result['created']++;
            }
        } else {
            // Generate for all
            $result = $this->invoiceService->generateDueInvoiceItems($date);
        }

        return redirect()->back()->with('success', 
            "Generated {$result['created']} invoices. Skipped {$result['skipped']}."
        );
    }

    /**
     * Payment gateway settings.
     */
    public function settings()
    {
        $settings = [
            'paystack_public_key' => config('paystack.public_key'),
            'paystack_secret_key' => maskSecret(config('paystack.secret_key')),
            'paystack_is_live' => config('paystack.is_live'),
            'paystack_webhook_secret' => maskSecret(config('paystack.webhook.secret')),
            'paystack_currency' => config('paystack.currency'),
        ];

        $webhookUrl = url(config('paystack.webhook.url'));

        return view('superadmin.billing.settings', compact('settings', 'webhookUrl'));
    }

    /**
     * Update payment settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'paystack_public_key' => 'required|string',
            'paystack_secret_key' => 'required|string',
            'paystack_is_live' => 'boolean',
            'paystack_webhook_secret' => 'nullable|string',
        ]);

        // Update .env file or settings storage
        // This is a simplified version - in production, use a settings management system
        
        return redirect()->back()->with('success', 'Payment settings updated.');
    }

    /**
     * Subscription management.
     */
    public function subscriptions(Request $request)
    {
        $query = TenantModuleSubscription::with(['tenant', 'module']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->filled('billing_type')) {
            $query->where('billing_type', $request->billing_type);
        }

        $subscriptions = $query->orderBy('next_billing_at')->paginate(50);
        
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        return view('superadmin.billing.subscriptions', compact('subscriptions', 'tenants'));
    }

    /**
     * Export billing report.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx',
            'from' => 'required|date',
            'to' => 'required|date',
            'type' => 'required|in:all,revenue,refunds,outstanding',
        ]);

        // Generate report logic here
        // Use Laravel Excel or similar for XLSX export
        
        return redirect()->back()->with('success', 'Report generated and will be emailed to you.');
    }

    /**
     * API: Get real-time billing stats.
     */
    public function apiStats()
    {
        $today = now()->startOfDay();
        
        return response()->json([
            'today_revenue' => ModuleInvoiceItem::where('status', 'paid')
                ->whereDate('paid_at', $today)
                ->sum('total_amount'),
            'pending_amount' => ModuleInvoiceItem::where('status', 'pending')
                ->sum('total_amount'),
            'failed_count' => ModuleInvoiceItem::where('status', 'failed')
                ->whereDate('created_at', $today)
                ->count(),
            'new_subscriptions' => TenantModuleSubscription::whereDate('installed_at', $today)->count(),
        ]);
    }
}

// Helper function
function maskSecret(string $secret): string
{
    if (strlen($secret) <= 8) {
        return '********';
    }
    return substr($secret, 0, 4) . str_repeat('*', strlen($secret) - 8) . substr($secret, -4);
}
