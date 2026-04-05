<?php

/**
 * PayStack Payment Gateway Configuration
 * 
 * @see https://paystack.com/docs/payments/accept-payments/
 */

return [
    /**
     * PayStack API Keys
     * Get these from your PayStack Dashboard: https://dashboard.paystack.com/
     */
    'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
    'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
    
    /**
     * Environment mode
     * Set to true for production, false for test/sandbox
     */
    'is_live' => env('PAYSTACK_IS_LIVE', false),
    
    /**
     * PayStack API Base URL
     */
    'base_url' => env('PAYSTACK_IS_LIVE', false) 
        ? 'https://api.paystack.co' 
        : 'https://api.paystack.co', // Same URL, keys determine environment
    
    /**
     * Webhook Configuration
     */
    'webhook' => [
        'secret' => env('PAYSTACK_WEBHOOK_SECRET', ''),
        'url' => '/webhooks/paystack',
        'tolerance' => 300, // Time tolerance for signature verification (seconds)
    ],
    
    /**
     * Payment Channels
     * Available: card, bank, ussd, qr, mobile_money, bank_transfer, eft
     */
    'channels' => ['card', 'bank', 'ussd', 'mobile_money', 'bank_transfer'],
    
    /**
     * Currency Configuration
     */
    'currency' => env('PAYSTACK_CURRENCY', 'KES'),
    
    /**
     * Transaction Settings
     */
    'transaction' => [
        'reference_prefix' => 'PISTI_',
        'callback_url' => '/payments/callback',
    ],
    
    /**
     * Retry Configuration
     */
    'retry' => [
        'max_attempts' => 3,
        'delay' => 5, // seconds between retries
    ],
];
