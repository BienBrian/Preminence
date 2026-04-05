<?php

namespace App\DTOs;

use App\Models\TenantModuleSubscription;

/**
 * Data Transfer Object for module installation results.
 * 
 * Provides a structured response for installation operations,
 * including success status, error details, and installation progress.
 */
readonly class InstallationResult
{
    /**
     * @param bool $success Whether installation succeeded
     * @param TenantModuleSubscription $subscription The subscription record
     * @param array $steps Installation step logs
     * @param string|null $error Error message if failed
     * @param string|null $redirectUrl Where to redirect on success
     * @param array $metadata Additional response data
     */
    public function __construct(
        public bool $success,
        public TenantModuleSubscription $subscription,
        public array $steps = [],
        public ?string $error = null,
        public ?string $redirectUrl = null,
        public array $metadata = [],
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(
        TenantModuleSubscription $subscription,
        array $steps = [],
        ?string $redirectUrl = null,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            subscription: $subscription,
            steps: $steps,
            redirectUrl: $redirectUrl,
            metadata: $metadata
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(
        TenantModuleSubscription $subscription,
        string $error,
        array $steps = [],
        array $metadata = []
    ): self {
        return new self(
            success: false,
            subscription: $subscription,
            steps: $steps,
            error: $error,
            metadata: $metadata
        );
    }

    /**
     * Create a queued result (for background processing).
     */
    public static function queued(
        TenantModuleSubscription $subscription,
        ?string $redirectUrl = null
    ): self {
        return new self(
            success: true,
            subscription: $subscription,
            steps: [['step' => 'queued', 'status' => 'pending']],
            redirectUrl: $redirectUrl,
            metadata: ['queued' => true]
        );
    }

    /**
     * Check if installation is still in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->subscription->status, ['pending', 'installing']);
    }

    /**
     * Check if installation requires payment.
     */
    public function requiresPayment(): bool
    {
        return !empty($this->metadata['requires_payment']);
    }

    /**
     * Check if installation requires approval.
     */
    public function requiresApproval(): bool
    {
        return !empty($this->metadata['requires_approval']);
    }

    /**
     * Get installation progress percentage.
     */
    public function getProgressPercent(): int
    {
        return $this->subscription->getInstallationProgress();
    }

    /**
     * Convert to array for API responses.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'subscription_id' => $this->subscription->id,
            'module_key' => $this->subscription->module_key,
            'status' => $this->subscription->status,
            'progress_percent' => $this->getProgressPercent(),
            'steps' => $this->steps,
            'error' => $this->error,
            'redirect_url' => $this->redirectUrl,
            'is_in_progress' => $this->isInProgress(),
            'requires_payment' => $this->requiresPayment(),
            'requires_approval' => $this->requiresApproval(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Convert to JSON response.
     */
    public function toResponse(int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        if (!$this->success && $statusCode === 200) {
            $statusCode = 422;
        }

        return response()->json($this->toArray(), $statusCode);
    }
}
