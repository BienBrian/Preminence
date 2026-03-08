<?php

namespace App\Services\Payment;

use App\Models\Tenant;
use App\Services\Tenant\AutoReactivationService;
use Illuminate\Support\Facades\Log;

class SuspensionPaymentService
{
    /**
     * Process a payment from a suspended tenant.
     * This is called when a tenant on the suspension page makes a payment.
     */
    public static function processPayment(
        Tenant $tenant,
        string $paymentMethod,
        float $amount,
        string $currency,
        array $paymentData
    ): array {
        try {
            // Validate tenant is suspended
            if (!$tenant->isSuspended()) {
                return [
                    'success' => false,
                    'message' => 'Tenant is not suspended.',
                    'code' => 'NOT_SUSPENDED',
                ];
            }
            
            // Validate payment amount covers the due amount
            if ($tenant->suspension_amount_due && $amount < $tenant->suspension_amount_due) {
                return [
                    'success' => false,
                    'message' => 'Payment amount is less than the amount due.',
                    'code' => 'INSUFFICIENT_PAYMENT',
                    'amount_due' => $tenant->suspension_amount_due,
                    'amount_paid' => $amount,
                ];
            }
            
            // Create payment record
            $payment = self::createPaymentRecord($tenant, $paymentMethod, $amount, $currency, $paymentData);
            
            // Attempt auto-reactivation
            $autoReactivated = AutoReactivationService::autoReactivate(
                $tenant,
                $amount,
                $currency,
                $paymentMethod,
                $payment['transaction_reference']
            );
            
            return [
                'success' => true,
                'message' => $autoReactivated 
                    ? 'Payment successful! Your account has been reactivated.' 
                    : 'Payment received. Our team will review and reactivate your account shortly.',
                'code' => $autoReactivated ? 'AUTO_REACTIVATED' : 'PENDING_REVIEW',
                'payment_id' => $payment['id'],
                'reactivated' => $autoReactivated,
            ];
            
        } catch (\Exception $e) {
            Log::error('Suspension payment processing failed', [
                'tenant_id' => $tenant->id,
                'payment_method' => $paymentMethod,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment processing failed. Please contact support.',
                'code' => 'PROCESSING_ERROR',
            ];
        }
    }
    
    /**
     * Create a payment record in the database.
     */
    protected static function createPaymentRecord(
        Tenant $tenant,
        string $paymentMethod,
        float $amount,
        string $currency,
        array $paymentData
    ): array {
        // In production, this would create a record in a payments table
        // For now, return a mock record
        
        $transactionReference = 'SUS-' . strtoupper(uniqid());
        
        // Log the payment
        Log::info('Suspension payment recorded', [
            'tenant_id' => $tenant->id,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'currency' => $currency,
            'transaction_reference' => $transactionReference,
        ]);
        
        return [
            'id' => uniqid(),
            'transaction_reference' => $transactionReference,
            'amount' => $amount,
            'currency' => $currency,
            'method' => $paymentMethod,
            'status' => 'completed',
            'created_at' => now()->toIso8601String(),
        ];
    }
    
    /**
     * Get available payment methods for the tenant.
     */
    public static function getAvailableMethods(Tenant $tenant): array
    {
        $methods = [];
        
        // Check MPESA availability
        $mpesaAvailable = config('services.mpesa.enabled', false) || 
                         config('mpesa.enabled', false);
        
        if ($mpesaAvailable) {
            $methods[] = [
                'id' => 'mpesa',
                'name' => 'M-PESA',
                'icon' => 'mpesa',
                'description' => 'Pay via M-PESA mobile money',
                'supported_currencies' => ['KES'],
                'min_amount' => 10,
                'max_amount' => 150000,
            ];
        }
        
        // Check Card availability
        $cardAvailable = config('services.stripe.enabled', false) ||
                        config('services.paystack.enabled', false) ||
                        config('services.flutterwave.enabled', false);
        
        if ($cardAvailable) {
            $methods[] = [
                'id' => 'card',
                'name' => 'Card Payment',
                'icon' => 'card',
                'description' => 'Pay with Visa, Mastercard, or other cards',
                'supported_currencies' => ['KES', 'USD', 'EUR', 'GBP'],
                'min_amount' => 1,
                'max_amount' => 1000000,
            ];
        }
        
        // Check Airtel Money availability
        $airtelAvailable = config('services.airtel.enabled', false);
        
        if ($airtelAvailable) {
            $methods[] = [
                'id' => 'airtel',
                'name' => 'Airtel Money',
                'icon' => 'airtel',
                'description' => 'Pay via Airtel Money',
                'supported_currencies' => ['KES'],
                'min_amount' => 10,
                'max_amount' => 150000,
            ];
        }
        
        // If no methods are configured, return placeholder methods
        // This allows the UI to still show during development
        if (empty($methods)) {
            return self::getPlaceholderMethods();
        }
        
        return $methods;
    }
    
    /**
     * Get placeholder payment methods for development/testing.
     */
    protected static function getPlaceholderMethods(): array
    {
        return [
            [
                'id' => 'mpesa',
                'name' => 'M-PESA',
                'icon' => 'mpesa',
                'description' => 'Pay via M-PESA mobile money (Demo Mode)',
                'supported_currencies' => ['KES'],
                'min_amount' => 10,
                'max_amount' => 150000,
                'demo_mode' => true,
            ],
            [
                'id' => 'card',
                'name' => 'Card Payment',
                'icon' => 'card',
                'description' => 'Pay with Visa, Mastercard (Demo Mode)',
                'supported_currencies' => ['KES', 'USD'],
                'min_amount' => 1,
                'max_amount' => 1000000,
                'demo_mode' => true,
            ],
            [
                'id' => 'airtel',
                'name' => 'Airtel Money',
                'icon' => 'airtel',
                'description' => 'Pay via Airtel Money (Demo Mode)',
                'supported_currencies' => ['KES'],
                'min_amount' => 10,
                'max_amount' => 150000,
                'demo_mode' => true,
            ],
        ];
    }
    
    /**
     * Calculate the total amount due including any fees.
     */
    public static function calculateTotalDue(Tenant $tenant): array
    {
        $baseAmount = $tenant->suspension_amount_due ?? 0;
        $currency = $tenant->suspension_currency ?? 'KES';
        
        // Add processing fees based on payment method
        $mpesaFee = min(max($baseAmount * 0.01, 10), 100); // 1% capped at 100
        $cardFee = $baseAmount * 0.035; // 3.5%
        
        return [
            'base_amount' => $baseAmount,
            'currency' => $currency,
            'mpesa' => [
                'fee' => $mpesaFee,
                'total' => $baseAmount + $mpesaFee,
            ],
            'card' => [
                'fee' => $cardFee,
                'total' => $baseAmount + $cardFee,
            ],
        ];
    }
}
