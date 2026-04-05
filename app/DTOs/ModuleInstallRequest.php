<?php

namespace App\DTOs;

use App\Models\Tenant;
use App\Models\User;

/**
 * Data Transfer Object for module installation requests.
 * 
 * This immutable DTO carries all necessary information for a module
 * installation, ensuring type safety and data validation.
 */
readonly class ModuleInstallRequest
{
    /**
     * @param string $moduleKey The module identifier (e.g., 'finance', 'sms')
     * @param Tenant $tenant The target tenant
     * @param User $requestedBy The user initiating the installation
     * @param string $billingCycle 'monthly' or 'yearly'
     * @param string|null $idempotencyKey Prevents duplicate processing
     * @param bool $autoApprove Skip approval workflow if true
     * @param array|null $settings Initial module configuration
     * @param int|null $trialDays Override default trial period
     * @param string $currency Currency code (default: KES)
     */
    public function __construct(
        public string $moduleKey,
        public Tenant $tenant,
        public User $requestedBy,
        public string $billingCycle = 'monthly',
        public ?string $idempotencyKey = null,
        public bool $autoApprove = false,
        public ?array $settings = null,
        public ?int $trialDays = null,
        public string $currency = 'KES',
    ) {}

    /**
     * Get or generate idempotency key.
     */
    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey ?? $this->generateIdempotencyKey();
    }

    /**
     * Generate a deterministic idempotency key.
     */
    private function generateIdempotencyKey(): string
    {
        return sprintf(
            'install:%d:%s:%s:%s',
            $this->tenant->id,
            $this->moduleKey,
            $this->billingCycle,
            now()->format('Y-m-d-H')
        );
    }

    /**
     * Create a new instance with modified properties.
     */
    public function with(
        ?string $moduleKey = null,
        ?Tenant $tenant = null,
        ?User $requestedBy = null,
        ?string $billingCycle = null,
        ?string $idempotencyKey = null,
        ?bool $autoApprove = null,
        ?array $settings = null,
        ?int $trialDays = null,
        ?string $currency = null,
    ): self {
        return new self(
            moduleKey: $moduleKey ?? $this->moduleKey,
            tenant: $tenant ?? $this->tenant,
            requestedBy: $requestedBy ?? $this->requestedBy,
            billingCycle: $billingCycle ?? $this->billingCycle,
            idempotencyKey: $idempotencyKey ?? $this->idempotencyKey,
            autoApprove: $autoApprove ?? $this->autoApprove,
            settings: $settings ?? $this->settings,
            trialDays: $trialDays ?? $this->trialDays,
            currency: $currency ?? $this->currency,
        );
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'module_key' => $this->moduleKey,
            'tenant_id' => $this->tenant->id,
            'requested_by' => $this->requestedBy->id,
            'billing_cycle' => $this->billingCycle,
            'idempotency_key' => $this->getIdempotencyKey(),
            'auto_approve' => $this->autoApprove,
            'settings' => $this->settings,
            'trial_days' => $this->trialDays,
            'currency' => $this->currency,
        ];
    }
}
