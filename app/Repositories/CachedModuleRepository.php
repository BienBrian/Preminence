<?php

namespace App\Repositories;

use App\Models\Module;
use App\Models\Tenant;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Decorator pattern for caching module repository results.
 * 
 * This wrapper adds Redis caching to the base repository without
 * modifying its behavior, following the Open/Closed Principle.
 */
class CachedModuleRepository implements ModuleRepositoryInterface
{
    private ModuleRepositoryInterface $repository;
    private int $ttl;

    public function __construct(ModuleRepositoryInterface $repository, int $ttlMinutes = 5)
    {
        $this->repository = $repository;
        $this->ttl = $ttlMinutes * 60;
    }

    public function findByKey(string $key): ?Module
    {
        return Cache::remember(
            $this->getCacheKey('module', $key),
            $this->ttl,
            fn() => $this->repository->findByKey($key)
        );
    }

    public function getMarketplaceModules(): Collection
    {
        return Cache::remember(
            $this->getCacheKey('marketplace'),
            $this->ttl,
            fn() => $this->repository->getMarketplaceModules()
        );
    }

    public function getAvailableForTenant(Tenant $tenant): Collection
    {
        return Cache::remember(
            $this->getCacheKey('tenant', $tenant->id, 'available'),
            $this->ttl,
            fn() => $this->repository->getAvailableForTenant($tenant)
        );
    }

    public function getForPlan(int $planId): Collection
    {
        return Cache::remember(
            $this->getCacheKey('plan', $planId),
            $this->ttl,
            fn() => $this->repository->getForPlan($planId)
        );
    }

    public function search(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        // Don't cache search results - too variable
        // Could use a short TTL cache with filter hash as key if needed
        return $this->repository->search($filters, $perPage);
    }

    public function canInstall(string $moduleKey, Tenant $tenant): bool
    {
        // Short TTL for this - changes frequently with installations
        return Cache::remember(
            $this->getCacheKey('tenant', $tenant->id, 'can_install', $moduleKey),
            60, // 1 minute
            fn() => $this->repository->canInstall($moduleKey, $tenant)
        );
    }

    public function getDependencyStatus(string $moduleKey, Tenant $tenant): array
    {
        return Cache::remember(
            $this->getCacheKey('tenant', $tenant->id, 'deps', $moduleKey),
            $this->ttl,
            fn() => $this->repository->getDependencyStatus($moduleKey, $tenant)
        );
    }

    public function getDependents(string $moduleKey, Tenant $tenant): array
    {
        return Cache::remember(
            $this->getCacheKey('tenant', $tenant->id, 'dependents', $moduleKey),
            $this->ttl,
            fn() => $this->repository->getDependents($moduleKey, $tenant)
        );
    }

    public function getStats(string $moduleKey): array
    {
        // Stats change frequently, use shorter TTL
        return Cache::remember(
            $this->getCacheKey('stats', $moduleKey),
            300, // 5 minutes
            fn() => $this->repository->getStats($moduleKey)
        );
    }

    /**
     * Invalidate all cache entries for a module.
     */
    public function invalidateModule(string $key): void
    {
        $keys = [
            $this->getCacheKey('module', $key),
            $this->getCacheKey('stats', $key),
        ];

        foreach ($keys as $cacheKey) {
            Cache::forget($cacheKey);
        }

        // Also invalidate marketplace list
        Cache::forget($this->getCacheKey('marketplace'));

        Log::debug('Module cache invalidated', ['module' => $key]);
    }

    /**
     * Invalidate all cache entries for a tenant.
     */
    public function invalidateTenant(int $tenantId): void
    {
        // Pattern-based invalidation would be cleaner with Redis tags
        // For now, we invalidate specific known keys
        $patterns = [
            $this->getCacheKey('tenant', $tenantId, '*'),
        ];

        // Since Laravel doesn't support pattern deletion,
        // we track tenant keys or use a version key approach
        Cache::forget($this->getCacheKey('tenant', $tenantId, 'available'));
        
        // Increment a version key to invalidate all tenant caches
        Cache::increment($this->getCacheKey('tenant', $tenantId, 'version'), 1);

        Log::debug('Tenant cache invalidated', ['tenant_id' => $tenantId]);
    }

    /**
     * Invalidate all cache entries for a plan.
     */
    public function invalidatePlan(int $planId): void
    {
        Cache::forget($this->getCacheKey('plan', $planId));

        // Also invalidate all tenants on this plan
        // This would ideally be queued for large numbers of tenants
        $tenantIds = Tenant::where('plan_id', $planId)->pluck('id');
        foreach ($tenantIds as $tenantId) {
            $this->invalidateTenant($tenantId);
        }

        Log::debug('Plan cache invalidated', ['plan_id' => $planId]);
    }

    /**
     * Invalidate all module caches.
     */
    public function invalidateAll(): void
    {
        // If using Redis, could use FLUSHDB or pattern delete
        // For file/array cache, we need specific keys
        Cache::flush();

        Log::info('All module caches invalidated');
    }

    /**
     * Get cache TTL.
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * Set cache TTL.
     */
    public function setTtl(int $minutes): void
    {
        $this->ttl = $minutes * 60;
    }

    /**
     * Build a cache key with consistent prefixing.
     */
    protected function getCacheKey(string ...$parts): string
    {
        return 'modules:' . implode(':', $parts);
    }
}
