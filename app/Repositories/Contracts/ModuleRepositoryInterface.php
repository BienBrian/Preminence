<?php

namespace App\Repositories\Contracts;

use App\Models\Module;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModuleRepositoryInterface
{
    /**
     * Find module by key.
     */
    public function findByKey(string $key): ?Module;

    /**
     * Get all active modules for marketplace.
     */
    public function getMarketplaceModules(): Collection;

    /**
     * Get modules available to a specific tenant.
     */
    public function getAvailableForTenant(Tenant $tenant): Collection;

    /**
     * Get modules with their plan-specific configuration.
     */
    public function getForPlan(int $planId): Collection;

    /**
     * Search modules with filters.
     */
    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Check if module can be installed for tenant.
     */
    public function canInstall(string $moduleKey, Tenant $tenant): bool;

    /**
     * Get module dependencies with status for tenant.
     */
    public function getDependencyStatus(string $moduleKey, Tenant $tenant): array;

    /**
     * Get modules that depend on a given module.
     */
    public function getDependents(string $moduleKey, Tenant $tenant): array;

    /**
     * Get installation statistics for a module.
     */
    public function getStats(string $moduleKey): array;

    /**
     * Invalidate cache for a module.
     */
    public function invalidateModule(string $key): void;

    /**
     * Invalidate cache for a tenant.
     */
    public function invalidateTenant(int $tenantId): void;

    /**
     * Invalidate cache for a plan.
     */
    public function invalidatePlan(int $planId): void;
}
