<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use App\Models\SuperAdmin;
use App\Models\PlatformAuditLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AutoReactivationService
{
    /**
     * Check if a tenant can be auto-reactivated based on a payment.
     * Financial suspensions with cleared payments are eligible.
     */
    public static function canAutoReactivate(Tenant $tenant, float $paymentAmount, string $currency): bool
    {
        // Only financial suspensions can be auto-reactivated
        if ($tenant->suspension_type !== 'financial') {
            return false;
        }
        
        // Check if payment amount covers the amount due
        if ($tenant->suspension_amount_due > 0) {
            if ($currency !== $tenant->suspension_currency) {
                // Simple currency check - in production, use proper conversion
                return false;
            }
            
            if ($paymentAmount < $tenant->suspension_amount_due) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Auto-reactivate a suspended tenant after successful payment.
     */
    public static function autoReactivate(
        Tenant $tenant, 
        float $paymentAmount, 
        string $currency, 
        string $paymentMethod,
        string $transactionReference
    ): bool {
        try {
            if (!self::canAutoReactivate($tenant, $paymentAmount, $currency)) {
                Log::info('Tenant cannot be auto-reactivated', [
                    'tenant_id' => $tenant->id,
                    'payment_amount' => $paymentAmount,
                    'currency' => $currency,
                ]);
                return false;
            }
            
            // Capture suspension details before clearing
            $suspensionDetails = [
                'suspension_type' => $tenant->suspension_type,
                'suspension_reason' => $tenant->suspension_reason,
                'suspension_details' => $tenant->suspension_details,
                'amount_due' => $tenant->suspension_amount_due,
                'currency' => $tenant->suspension_currency,
                'payment_cleared' => [
                    'amount' => $paymentAmount,
                    'currency' => $currency,
                    'method' => $paymentMethod,
                    'transaction_reference' => $transactionReference,
                    'paid_at' => now()->toIso8601String(),
                ],
            ];
            
            // Update tenant status
            $tenant->update([
                'status' => 'active',
                'suspension_type' => null,
                'suspension_reason' => null,
                'suspension_details' => $suspensionDetails, // Keep history
                'suspension_amount_due' => null,
                'suspension_currency' => null,
            ]);
            
            // Log the reactivation
            PlatformAuditLog::create([
                'action' => 'tenant_auto_reactivated',
                'description' => "Tenant {$tenant->name} was automatically reactivated after payment",
                'entity_type' => 'tenant',
                'entity_id' => $tenant->id,
                'old_values' => $suspensionDetails,
                'new_values' => ['status' => 'active'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_by_type' => 'system',
                'created_by_id' => null,
            ]);
            
            // Notify tenant admin
            self::notifyTenantAdmin($tenant, $paymentAmount, $currency, $paymentMethod);
            
            // Notify super admins
            self::notifySuperAdmins($tenant, $paymentAmount, $currency, $paymentMethod);
            
            Log::info('Tenant auto-reactivated successfully', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'payment_amount' => $paymentAmount,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to auto-reactivate tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Manual reactivation by super admin.
     */
    public static function manualReactivate(
        Tenant $tenant, 
        SuperAdmin $admin, 
        ?string $reason = null
    ): bool {
        try {
            if (!$tenant->isSuspended()) {
                return false;
            }
            
            $suspensionDetails = [
                'suspension_type' => $tenant->suspension_type,
                'suspension_reason' => $tenant->suspension_reason,
                'suspension_details' => $tenant->suspension_details,
                'amount_due' => $tenant->suspension_amount_due,
                'currency' => $tenant->suspension_currency,
            ];
            
            $tenant->update([
                'status' => 'active',
                'suspension_type' => null,
                'suspension_reason' => null,
                'suspension_details' => $suspensionDetails,
                'suspension_amount_due' => null,
                'suspension_currency' => null,
            ]);
            
            PlatformAuditLog::create([
                'action' => 'tenant_reactivated',
                'description' => "Tenant {$tenant->name} was manually reactivated by {$admin->name}",
                'entity_type' => 'tenant',
                'entity_id' => $tenant->id,
                'old_values' => $suspensionDetails,
                'new_values' => ['status' => 'active', 'reactivated_by' => $admin->name, 'reason' => $reason],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_by_type' => 'super_admin',
                'created_by_id' => $admin->id,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to manually reactivate tenant', [
                'tenant_id' => $tenant->id,
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    
    /**
     * Get the next available suspension end date.
     */
    public static function calculateSuspensionEndDate(Tenant $tenant): \Carbon\Carbon
    {
        // If tenant has a subscription, suspend until next billing date
        if ($tenant->subscriptions()->active()->exists()) {
            $subscription = $tenant->subscriptions()->active()->first();
            return $subscription->current_period_end ?? now()->addDays(30);
        }
        
        // Default: 30 days from suspension date
        return now()->addDays(30);
    }
    
    /**
     * Notify tenant admin of reactivation.
     */
    protected static function notifyTenantAdmin(
        Tenant $tenant, 
        float $paymentAmount, 
        string $currency, 
        string $paymentMethod
    ): void {
        try {
            $admin = $tenant->users()->whereHas('roles', function ($q) {
                $q->where('name', 'Admin');
            })->first();
            
            if ($admin && $admin->email) {
                // In production, send actual email
                Log::info('Would notify tenant admin of reactivation', [
                    'tenant_id' => $tenant->id,
                    'admin_email' => $admin->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify tenant admin', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Notify super admins of auto-reactivation.
     */
    protected static function notifySuperAdmins(
        Tenant $tenant, 
        float $paymentAmount, 
        string $currency, 
        string $paymentMethod
    ): void {
        try {
            $superAdmins = SuperAdmin::all();
            
            foreach ($superAdmins as $admin) {
                if ($admin->email) {
                    // In production, send actual email
                    Log::info('Would notify super admin of auto-reactivation', [
                        'tenant_id' => $tenant->id,
                        'admin_email' => $admin->email,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify super admins', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
