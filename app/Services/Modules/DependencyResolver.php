<?php

namespace App\Services\Modules;

use App\Models\Tenant;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Service for resolving module dependencies.
 * 
 * Handles dependency graph resolution, detecting circular dependencies,
 * and determining installation order for modules with dependencies.
 */
class DependencyResolver
{
    private ModuleRepositoryInterface $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Resolve all dependencies for a module, including nested dependencies.
     * 
     * Returns an ordered list of dependencies that need to be installed,
     * with each dependency appearing before the modules that depend on it.
     *
     * @param string $moduleKey The module to resolve dependencies for
     * @param Tenant $tenant The target tenant
     * @return array Array of dependency info arrays
     * @throws \RuntimeException If circular dependency detected
     */
    public function resolve(string $moduleKey, Tenant $tenant): array
    {
        $resolved = [];
        $visiting = [];
        $visited = [];

        $this->resolveRecursive($moduleKey, $tenant, $resolved, $visiting, $visited);

        return $resolved;
    }

    /**
     * Resolve dependencies recursively with cycle detection.
     */
    private function resolveRecursive(
        string $moduleKey,
        Tenant $tenant,
        array &$resolved,
        array &$visiting,
        array &$visited
    ): void {
        // Already fully processed
        if (isset($visited[$moduleKey])) {
            return;
        }

        // Cycle detected
        if (isset($visiting[$moduleKey])) {
            throw new \RuntimeException(
                "Circular dependency detected involving module: {$moduleKey}"
            );
        }

        $visiting[$moduleKey] = true;

        $module = $this->moduleRepository->findByKey($moduleKey);
        if (!$module) {
            throw new \RuntimeException("Module not found: {$moduleKey}");
        }

        // Process dependencies first (depth-first)
        foreach ($module->dependencies ?? [] as $depKey) {
            $this->resolveRecursive($depKey, $tenant, $resolved, $visiting, $visited);

            // Add dependency to resolved list if not already added
            $exists = collect($resolved)->contains(fn($r) => $r['key'] === $depKey);
            if (!$exists) {
                $depModule = $this->moduleRepository->findByKey($depKey);
                $resolved[] = [
                    'key' => $depKey,
                    'name' => $depModule?->name ?? $depKey,
                    'icon' => $depModule?->icon ?? 'bi-box',
                    'required' => true,
                    'installed' => $tenant->hasModule($depKey),
                    'can_install' => !$tenant->hasModule($depKey) && 
                        $this->moduleRepository->canInstall($depKey, $tenant),
                ];
            }
        }

        unset($visiting[$moduleKey]);
        $visited[$moduleKey] = true;
    }

    /**
     * Get the installation order for a module and its dependencies.
     * 
     * Returns only the modules that need to be installed (not already installed).
     *
     * @param string $moduleKey The target module
     * @param Tenant $tenant The target tenant
     * @return array Ordered list of modules to install
     */
    public function getInstallationOrder(string $moduleKey, Tenant $tenant): array
    {
        $allDependencies = $this->resolve($moduleKey, $tenant);

        // Filter to only modules that need installation
        return array_filter($allDependencies, fn($dep) => !$dep['installed']);
    }

    /**
     * Get modules that depend on a given module.
     * 
     * Useful for checking if a module can be safely uninstalled.
     *
     * @param string $moduleKey The module to check dependents for
     * @param Tenant $tenant The target tenant
     * @return array List of installed modules that depend on this one
     */
    public function getDependents(string $moduleKey, Tenant $tenant): array
    {
        return $this->moduleRepository->getDependents($moduleKey, $tenant);
    }

    /**
     * Check if a module can be safely uninstalled.
     * 
     * Returns false if other installed modules depend on this one.
     *
     * @param string $moduleKey The module to check
     * @param Tenant $tenant The target tenant
     * @return bool
     */
    public function canUninstall(string $moduleKey, Tenant $tenant): bool
    {
        $dependents = $this->getDependents($moduleKey, $tenant);
        return empty($dependents);
    }

    /**
     * Validate that all dependencies can be satisfied.
     * 
     * Checks if all required dependencies are either already installed
     * or can be installed by the tenant.
     *
     * @param string $moduleKey The module to validate
     * @param Tenant $tenant The target tenant
     * @return array ['valid' => bool, 'missing' => array, 'unavailable' => array]
     */
    public function validateDependencies(string $moduleKey, Tenant $tenant): array
    {
        $missing = [];
        $unavailable = [];

        try {
            $dependencies = $this->resolve($moduleKey, $tenant);
        } catch (\RuntimeException $e) {
            return [
                'valid' => false,
                'missing' => [],
                'unavailable' => [],
                'error' => $e->getMessage(),
            ];
        }

        foreach ($dependencies as $dep) {
            if (!$dep['installed'] && !$dep['can_install']) {
                $unavailable[] = $dep['key'];
            }
        }

        return [
            'valid' => empty($missing) && empty($unavailable),
            'missing' => $missing,
            'unavailable' => $unavailable,
        ];
    }

    /**
     * Get dependency tree as nested array.
     * 
     * Useful for visualizing dependency relationships.
     *
     * @param string $moduleKey The root module
     * @param Tenant $tenant The target tenant
     * @param int $maxDepth Maximum depth to traverse
     * @return array Nested dependency tree
     */
    public function getDependencyTree(
        string $moduleKey, 
        Tenant $tenant, 
        int $maxDepth = 10
    ): array {
        return $this->buildTreeRecursive($moduleKey, $tenant, [], $maxDepth, 0);
    }

    /**
     * Build dependency tree recursively.
     */
    private function buildTreeRecursive(
        string $moduleKey,
        Tenant $tenant,
        array &$visited,
        int $maxDepth,
        int $currentDepth
    ): array {
        if ($currentDepth >= $maxDepth || isset($visited[$moduleKey])) {
            return ['key' => $moduleKey, 'truncated' => true];
        }

        $visited[$moduleKey] = true;
        $module = $this->moduleRepository->findByKey($moduleKey);

        if (!$module) {
            return ['key' => $moduleKey, 'error' => 'Module not found'];
        }

        $children = [];
        foreach ($module->dependencies ?? [] as $depKey) {
            $children[] = $this->buildTreeRecursive(
                $depKey, 
                $tenant, 
                $visited, 
                $maxDepth, 
                $currentDepth + 1
            );
        }

        return [
            'key' => $moduleKey,
            'name' => $module->name,
            'icon' => $module->icon,
            'installed' => $tenant->hasModule($moduleKey),
            'children' => $children,
        ];
    }

    /**
     * Calculate total cost including all dependencies.
     *
     * @param string $moduleKey The target module
     * @param Tenant $tenant The target tenant
     * @param string $billingCycle 'monthly' or 'yearly'
     * @return array Cost breakdown
     */
    public function calculateTotalCost(
        string $moduleKey,
        Tenant $tenant,
        string $billingCycle
    ): array {
        $dependencies = $this->resolve($moduleKey, $tenant);
        $calculator = app(PricingCalculator::class);

        $breakdown = [];
        $total = 0;

        foreach ($dependencies as $dep) {
            if ($dep['installed']) {
                continue; // Skip already installed dependencies
            }

            $price = $calculator->calculate($dep['key'], $tenant, $billingCycle);
            $amount = $price->getPrice($billingCycle) ?? 0;
            $total += $amount;

            $breakdown[] = [
                'module' => $dep['key'],
                'name' => $dep['name'],
                'price' => $amount,
                'is_free' => $price->isFree(),
            ];
        }

        // Add the target module
        $targetPrice = $calculator->calculate($moduleKey, $tenant, $billingCycle);
        $targetAmount = $targetPrice->getPrice($billingCycle) ?? 0;
        $total += $targetAmount;

        $breakdown[] = [
            'module' => $moduleKey,
            'name' => $this->moduleRepository->findByKey($moduleKey)?->name,
            'price' => $targetAmount,
            'is_free' => $targetPrice->isFree(),
            'is_target' => true,
        ];

        return [
            'total' => $total,
            'currency' => 'KES',
            'billing_cycle' => $billingCycle,
            'breakdown' => $breakdown,
        ];
    }
}
