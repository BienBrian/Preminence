<?php

namespace App\DTOs;

/**
 * Value Object representing module pricing information.
 * 
 * This immutable DTO encapsulates all pricing details for a module,
 * including savings calculations and formatting utilities.
 */
readonly class ModulePrice
{
    /**
     * @param float|null $monthly Monthly price (null = not available)
     * @param float|null $yearly Yearly price (null = not available)
     * @param float $setupFee One-time setup fee
     * @param int|null $yearlySavingsPercent Percentage saved with yearly billing
     * @param string $currency ISO currency code
     */
    public function __construct(
        public ?float $monthly = null,
        public ?float $yearly = null,
        public float $setupFee = 0,
        public ?int $yearlySavingsPercent = null,
        public string $currency = 'KES',
    ) {}

    /**
     * Get price for specified billing cycle.
     */
    public function getPrice(string $billingCycle): ?float
    {
        return $billingCycle === 'yearly' ? $this->yearly : $this->monthly;
    }

    /**
     * Check if module is completely free.
     */
    public function isFree(): bool
    {
        return $this->monthly === null 
            && $this->yearly === null 
            && $this->setupFee === 0;
    }

    /**
     * Check if module has any cost (including setup fee).
     */
    public function hasCost(): bool
    {
        return $this->monthly !== null 
            || $this->yearly !== null 
            || $this->setupFee > 0;
    }

    /**
     * Get formatted price string.
     */
    public function format(string $billingCycle): string
    {
        $price = $this->getPrice($billingCycle);
        
        if ($price === null) {
            return 'Free';
        }
        
        return sprintf('%s %s', $this->currency, number_format($price, 2));
    }

    /**
     * Get formatted monthly price.
     */
    public function formatMonthly(): string
    {
        return $this->format('monthly');
    }

    /**
     * Get formatted yearly price.
     */
    public function formatYearly(): string
    {
        return $this->format('yearly');
    }

    /**
     * Get formatted setup fee.
     */
    public function formatSetupFee(): string
    {
        if ($this->setupFee === 0) {
            return 'Free';
        }
        
        return sprintf('%s %s', $this->currency, number_format($this->setupFee, 2));
    }

    /**
     * Get monthly equivalent of yearly price for comparison.
     */
    public function getMonthlyEquivalent(): ?float
    {
        if ($this->yearly === null) {
            return $this->monthly;
        }
        
        return $this->yearly / 12;
    }

    /**
     * Compare savings between monthly and yearly billing.
     */
    public function getYearlySavingsAmount(): ?float
    {
        if ($this->monthly === null || $this->yearly === null) {
            return null;
        }
        
        $monthlyCost = $this->monthly * 12;
        return $monthlyCost - $this->yearly;
    }

    /**
     * Convert to array for API responses or serialization.
     */
    public function toArray(): array
    {
        return [
            'monthly' => $this->monthly,
            'yearly' => $this->yearly,
            'setup_fee' => $this->setupFee,
            'yearly_savings_percent' => $this->yearlySavingsPercent,
            'yearly_savings_amount' => $this->getYearlySavingsAmount(),
            'monthly_equivalent' => $this->getMonthlyEquivalent(),
            'currency' => $this->currency,
            'is_free' => $this->isFree(),
            'has_cost' => $this->hasCost(),
            'formatted' => [
                'monthly' => $this->formatMonthly(),
                'yearly' => $this->formatYearly(),
                'setup_fee' => $this->formatSetupFee(),
            ],
        ];
    }

    /**
     * Create from a Module model.
     */
    public static function fromModule(\App\Models\Module $module): self
    {
        $savingsPercent = null;
        
        if ($module->price_monthly && $module->price_yearly) {
            $monthlyCost = $module->price_monthly * 12;
            $savings = $monthlyCost - $module->price_yearly;
            $savingsPercent = (int) round(($savings / $monthlyCost) * 100);
        }
        
        return new self(
            monthly: $module->price_monthly,
            yearly: $module->price_yearly,
            setupFee: $module->setup_fee ?? 0,
            yearlySavingsPercent: $savingsPercent,
        );
    }
}
