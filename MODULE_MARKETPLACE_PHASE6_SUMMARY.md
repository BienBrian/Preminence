# Phase 6: Billing Integration - Implementation Summary

## Overview
Phase 6 implements comprehensive billing integration for the Module Marketplace, including prorated billing, invoice itemization, recurring billing schedules, and automated billing commands.

## Database Migration

### New Table: `module_invoice_items`
Created to track all billable items with full audit trail:

| Column | Description |
|--------|-------------|
| `subscription_id` | Link to tenant module subscription |
| `tenant_id` | Tenant reference |
| `invoice_number` | Grouped invoice identifier |
| `module_key` | Module being billed |
| `type` | monthly_recurring, yearly_recurring, setup_fee, prorated_charge, prorated_credit, overage, refund, adjustment |
| `unit_price`, `quantity`, `amount` | Pricing breakdown |
| `tax_amount`, `total_amount` | Tax and final amount |
| `period_start`, `period_end` | Billing period |
| `days_billed` | For prorated calculations |
| `status` | pending, invoiced, paid, failed, refunded, cancelled |
| `proration_details` | JSON with calculation details |

## New Models

### ModuleInvoiceItem
- Full audit trail for all billing items
- Scopes: `pending()`, `paid()`, `failed()`, `forPeriod()`, `prorated()`
- Methods: `markAsPaid()`, `markAsFailed()`, `getStatusLabel()`, `getFormattedAmount()`

## New Services

### 1. ModuleInvoiceService
Handles invoice generation and management:
- `generateDueInvoiceItems()` - Creates items for due subscriptions
- `createInitialInvoiceItem()` - Handles installation billing with proration
- `createRefundInvoiceItem()` - Cancellation refunds
- `createBillingCycleChangeItem()` - Upgrade/downgrade adjustments
- `finalizeInvoice()` - Groups pending items into invoice

### 2. ProrationCalculator
Calculates fair prorated amounts:
- `calculateInstallProration()` - Mid-cycle installations
- `calculateCancellationRefund()` - Prorated refunds on cancellation
- `calculateBillingCycleChange()` - Monthly/yearly switch adjustments
- `calculatePlanUpgradeProration()` - Plan change proration

### 3. BillingScheduleService
Manages recurring billing schedules:
- `calculateNextBillingDate()` - Determines next billing date
- `getUpcomingBillings()` - Forecast upcoming charges
- `getOverdueBillings()` - Find overdue payments
- `alignToTenantBilling()` - Align module to tenant billing cycle
- `generateBillingCalendar()` - Future billing forecast

## Console Commands

| Command | Schedule | Purpose |
|---------|----------|---------|
| `modules:generate-invoices` | Daily 8:00 AM | Create invoice items for due subscriptions |
| `modules:process-trials` | Daily 9:00 AM | Convert expired trials to paid or suspend |
| `modules:process-overdue --suspend` | Daily 10:00 AM | Handle overdue payments, suspend after retries |
| `modules:billing-report` | Weekly Monday 6:00 AM | Generate billing reports |

### Usage Examples
```bash
# Generate invoices for today
php artisan modules:generate-invoices

# Dry run to see what would be generated
php artisan modules:generate-invoices --dry-run

# Process expired trials
php artisan modules:process-trials --dry-run
php artisan modules:process-trials --notify

# Handle overdue payments
php artisan modules:process-overdue --grace-period=5 --suspend

# Generate report
php artisan modules:billing-report --period=30
php artisan modules:billing-report --format=csv
```

## InvoicesController

### Routes
| Method | Route | Name | Description |
|--------|-------|------|-------------|
| GET | `/invoices` | `invoices.index` | List all invoices with filters |
| GET | `/invoices/upcoming` | `invoices.upcoming` | Upcoming billings preview |
| GET | `/invoices/history` | `invoices.history` | Paid/refunded history |
| GET | `/invoices/{invoiceItem}` | `invoices.show` | Single invoice details |
| GET | `/invoices/invoice/{invoiceNumber}` | `invoices.show-by-number` | Grouped invoice view |
| GET | `/invoices/invoice/{invoiceNumber}/download` | `invoices.download` | Download invoice |
| POST | `/invoices/{invoiceItem}/pay` | `invoices.pay` | Process payment |

## Views Created
- `dashboard/invoices/index.blade.php` - Invoice list with summary cards
- `dashboard/invoices/show.blade.php` - Detailed invoice view with payment
- `dashboard/invoices/upcoming.blade.php` - Upcoming billings preview
- `dashboard/invoices/invoice.blade.php` - Grouped invoice view (for multiple items)

## Scheduler Configuration

Added to `app/Console/Kernel.php`:
```php
// Daily at 8 AM: Generate invoices for due subscriptions
$schedule->command('modules:generate-invoices')->dailyAt('08:00');

// Daily at 9 AM: Process expired trials
$schedule->command('modules:process-trials')->dailyAt('09:00');

// Daily at 10 AM: Process overdue payments
$schedule->command('modules:process-overdue --suspend')->dailyAt('10:00');

// Weekly on Mondays at 6 AM: Generate billing report
$schedule->command('modules:billing-report')->weeklyOn(1, '06:00');
```

## Proration Examples

### Installation (Mid-Cycle)
- Tenant billing period: Jan 1 - Jan 31
- Install module on Jan 15
- Days remaining: 17
- Monthly price: KES 1,000
- Prorated charge: KES 1,000 × (17/31) = KES 548.39

### Cancellation (Mid-Cycle)
- Last billed: Jan 1
- Next billing: Feb 1
- Cancel on Jan 15
- Days used: 14
- Days remaining: 17
- Refund: KES 1,000 × (17/31) = KES 548.39

### Billing Cycle Change
- Monthly: KES 1,000
- Yearly: KES 10,000 (save KES 2,000)
- Change on day 15 of monthly cycle
- Remaining value: KES 500
- New yearly value for remaining period: ~KES 458
- Credit: KES 42

## Next Steps (Phase 7)

- Health checks for module installations
- Usage analytics and reporting
- Module performance metrics
- Automated health monitoring alerts

## Testing Billing Flow

```bash
# 1. Run migrations
php artisan migrate

# 2. Start queue worker
php artisan queue:work

# 3. Install a module via marketplace
# This creates initial invoice items with proration

# 4. Check invoices
php artisan tinker --execute="print_r(App\Models\ModuleInvoiceItem::all()->toArray())"

# 5. Generate invoices manually
php artisan modules:generate-invoices --dry-run
php artisan modules:generate-invoices

# 6. Check scheduled tasks
php artisan schedule:list
```
