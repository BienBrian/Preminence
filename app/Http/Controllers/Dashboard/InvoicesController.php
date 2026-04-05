<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ModuleInvoiceItem;
use App\Services\Modules\ModuleInvoiceService;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    private ModuleInvoiceService $invoiceService;

    public function __construct(ModuleInvoiceService $invoiceService)
    {
        $this->middleware(['auth', 'tenant.active']);
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display list of invoices for tenant.
     */
    public function index(Request $request)
    {
        $tenant = tenant();
        
        $query = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->with('module')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $invoices = $query->paginate(20);
        
        // Get summary stats
        $summary = [
            'total_paid' => ModuleInvoiceItem::where('tenant_id', $tenant->id)
                ->where('status', 'paid')
                ->sum('total_amount'),
            'total_pending' => ModuleInvoiceItem::where('tenant_id', $tenant->id)
                ->where('status', 'pending')
                ->sum('total_amount'),
            'total_failed' => ModuleInvoiceItem::where('tenant_id', $tenant->id)
                ->where('status', 'failed')
                ->sum('total_amount'),
        ];

        return view('dashboard.invoices.index', compact('invoices', 'summary'));
    }

    /**
     * Display a specific invoice.
     */
    public function show(ModuleInvoiceItem $invoiceItem)
    {
        // Ensure user can only view their own tenant's invoices
        if ($invoiceItem->tenant_id !== tenant()->id) {
            abort(403);
        }

        $invoiceItem->load(['subscription', 'module']);

        return view('dashboard.invoices.show', compact('invoiceItem'));
    }

    /**
     * Display invoice by invoice number (grouped view).
     */
    public function showByNumber(string $invoiceNumber)
    {
        $tenant = tenant();
        
        $items = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->where('invoice_number', $invoiceNumber)
            ->with(['module', 'subscription'])
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $total = $items->sum('total_amount');
        $firstItem = $items->first();

        return view('dashboard.invoices.invoice', compact('items', 'invoiceNumber', 'total', 'firstItem'));
    }

    /**
     * Download invoice as PDF.
     */
    public function download(string $invoiceNumber)
    {
        $tenant = tenant();
        
        $items = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->where('invoice_number', $invoiceNumber)
            ->with(['module', 'subscription'])
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        // For now, return HTML view - PDF generation can be added later
        $total = $items->sum('total_amount');
        $firstItem = $items->first();
        
        return view('dashboard.invoices.pdf', compact('items', 'invoiceNumber', 'total', 'firstItem'));
    }

    /**
     * Pay a pending invoice.
     */
    public function pay(Request $request, ModuleInvoiceItem $invoiceItem)
    {
        if ($invoiceItem->tenant_id !== tenant()->id) {
            abort(403);
        }

        if (!$invoiceItem->status === 'pending') {
            return redirect()->back()->with('error', 'This invoice is not payable.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:mpesa,card,bank_transfer',
        ]);

        // Process payment (integrate with payment gateway)
        try {
            $result = $this->processPayment($invoiceItem, $validated['payment_method']);
            
            if ($result['success']) {
                $invoiceItem->markAsPaid($validated['payment_method'], $result['transaction_id']);
                
                // Update subscription next billing date if this is a recurring charge
                if ($invoiceItem->isRecurring()) {
                    $subscription = $invoiceItem->subscription;
                    $subscription->update([
                        'last_billed_at' => now(),
                        'next_billing_at' => $this->calculateNextBilling($subscription),
                    ]);
                }

                return redirect()->route('invoices.show', $invoiceItem)
                    ->with('success', 'Payment processed successfully!');
            } else {
                $invoiceItem->markAsFailed();
                return redirect()->back()->with('error', 'Payment failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Payment error: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming billings for the tenant.
     */
    public function upcoming()
    {
        $tenant = tenant();
        
        // Get pending invoice items
        $pending = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->where('status', 'pending')
            ->with('module')
            ->orderBy('period_start')
            ->get();

        // Get subscription-based upcoming billings
        $subscriptions = $tenant->moduleSubscriptions()
            ->where('status', 'active')
            ->whereIn('billing_type', ['addon_monthly', 'addon_yearly'])
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '>=', now())
            ->with('module')
            ->orderBy('next_billing_at')
            ->get();

        $totalUpcoming = $pending->sum('total_amount') + $subscriptions->sum('price');

        return view('dashboard.invoices.upcoming', compact('pending', 'subscriptions', 'totalUpcoming'));
    }

    /**
     * Get billing history.
     */
    public function history(Request $request)
    {
        $tenant = tenant();
        
        $query = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->whereIn('status', ['paid', 'refunded'])
            ->with('module')
            ->orderBy('paid_at', 'desc');

        if ($request->has('year')) {
            $query->whereYear('paid_at', $request->year);
        }

        if ($request->has('module')) {
            $query->where('module_key', $request->module);
        }

        $history = $query->paginate(30);

        // Get years for filter
        $years = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->whereNotNull('paid_at')
            ->selectRaw('YEAR(paid_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get modules for filter
        $modules = ModuleInvoiceItem::where('tenant_id', $tenant->id)
            ->distinct()
            ->pluck('module_key');

        return view('dashboard.invoices.history', compact('history', 'years', 'modules'));
    }

    // ─── Private Methods ──────────────────────────────────────────────────────

    private function processPayment(ModuleInvoiceItem $invoiceItem, string $paymentMethod): array
    {
        // TODO: Integrate with actual payment gateway (M-Pesa, Stripe, etc.)
        // This is a placeholder implementation
        
        return [
            'success' => true,
            'transaction_id' => $paymentMethod . '_' . uniqid(),
            'message' => 'Payment processed',
        ];
    }

    private function calculateNextBilling($subscription): \Carbon\Carbon
    {
        return $subscription->billing_type === 'addon_yearly'
            ? now()->addYear()
            : now()->addMonth();
    }
}
