<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Initialize a payment transaction.
     * 
     * @param array $data Payment data including amount, currency, reference, etc.
     * @return array Response with success status and transaction details
     */
    public function initialize(array $data): array;
    
    /**
     * Verify a transaction status.
     * 
     * @param string $transactionId The transaction ID to verify
     * @return array Response with transaction status and details
     */
    public function verify(string $transactionId): array;
    
    /**
     * Handle payment callback/webhook.
     * 
     * @param array $payload The callback payload
     * @return array Response with processed payment details
     */
    public function handleCallback(array $payload): array;
    
    /**
     * Get the gateway name.
     */
    public function getName(): string;
    
    /**
     * Check if the gateway is configured and available.
     */
    public function isAvailable(): bool;
}
