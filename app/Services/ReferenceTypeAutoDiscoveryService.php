<?php

namespace App\Services;

use App\Models\MpesaTransaction;
use App\Models\ReferenceTypeMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceTypeAutoDiscoveryService
{
    /**
     * Discover and track new reference types from MPESA transactions.
     * This should be called when new transactions are imported or periodically.
     */
    public function discoverNewReferenceTypes(): array
    {
        $stats = [
            'scanned' => 0,
            'new_found' => 0,
            'added' => 0,
        ];

        try {
            // Get all unique reference types from recent transactions (last 30 days)
            $recentRefs = MpesaTransaction::select('BillRefNumber')
                ->whereNotNull('BillRefNumber')
                ->where('BillRefNumber', '!=', '')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->distinct()
                ->pluck('BillRefNumber')
                ->filter()
                ->map(function ($ref) {
                    return strtolower(trim($ref));
                })
                ->unique()
                ->values();

            $stats['scanned'] = $recentRefs->count();

            // Get all existing mappings
            $existingRefs = ReferenceTypeMapping::pluck('original_ref')
                ->map(function ($ref) {
                    return strtolower(trim($ref));
                })
                ->flip()
                ->toArray();

            // Find new references
            $newRefs = $recentRefs->filter(function ($ref) use ($existingRefs) {
                return !isset($existingRefs[$ref]);
            });

            $stats['new_found'] = $newRefs->count();

            // Auto-create placeholder mappings for new references
            foreach ($newRefs as $ref) {
                $this->createPlaceholderMapping($ref);
                $stats['added']++;
            }

            Log::info('Reference type auto-discovery completed', $stats);

        } catch (\Exception $e) {
            Log::error('Reference type auto-discovery failed: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Create a placeholder mapping for a new reference type.
     */
    private function createPlaceholderMapping(string $originalRef): void
    {
        try {
            // Normalize the reference for suggested mapping
            $suggestedMappedRef = $this->suggestMappedRef($originalRef);

            ReferenceTypeMapping::create([
                'tenant_id' => config('app.tenant_id', 1),
                'original_ref' => $originalRef,
                'mapped_ref' => $suggestedMappedRef,
                'summary_category_id' => null, // Unassigned - needs admin attention
                'description' => 'Auto-discovered from MPESA transactions. Please assign to appropriate category.',
                'is_active' => true,
                'auto_discovered' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create placeholder mapping for: ' . $originalRef, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Suggest a mapped reference based on common patterns.
     */
    private function suggestMappedRef(string $originalRef): string
    {
        $ref = strtolower($originalRef);

        // Common patterns
        $patterns = [
            'offering' => ['offering', 'offe', 'offr', 'offer', 'ofring', 'ofering'],
            'tithe' => ['tithe', 'tith', 'tite', 'milk', 'milik'],
            'building' => ['building', 'build', 'bldg', 'project', 'construction'],
            'charity' => ['charity', 'help', 'aid', 'donation', 'support'],
            'welfare' => ['welfare', 'pastor', 'support', 'appreciation'],
            'thanksgiving' => ['thanks', 'thanksgiving', 'gratitude', 'appreciation'],
            'seed' => ['seed', 'sowing', 'planting'],
            'youth' => ['youth', 'young', 'youths'],
            'children' => ['children', 'child', 'kids', 'sunday school'],
            'mission' => ['mission', 'missions', 'evangelism', 'outreach'],
        ];

        foreach ($patterns as $mapped => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($ref, $keyword) !== false) {
                    return $mapped;
                }
            }
        }

        // If no pattern matches, return the original (cleaned)
        return preg_replace('/[^a-z0-9]/', '', $ref);
    }

    /**
     * Get all unmapped references that need categorization.
     */
    public function getUnmappedReferences(): array
    {
        return ReferenceTypeMapping::whereNull('summary_category_id')
            ->orWhere('auto_discovered', true)
            ->select('original_ref', 'mapped_ref', 'description', 'created_at')
            ->get()
            ->map(function ($mapping) {
                // Get transaction stats for this reference
                $stats = MpesaTransaction::where('BillRefNumber', $mapping->original_ref)
                    ->selectRaw('COUNT(*) as count, SUM(TransAmount) as total, MAX(created_at) as last_transaction')
                    ->first();

                return [
                    'reference' => $mapping->original_ref,
                    'suggested_mapping' => $mapping->mapped_ref,
                    'transaction_count' => $stats->count ?? 0,
                    'total_amount' => $stats->total ?? 0,
                    'last_transaction' => $stats->last_transaction,
                    'discovered_at' => $mapping->created_at,
                ];
            })
            ->sortByDesc('total_amount')
            ->values()
            ->toArray();
    }

    /**
     * Check if a reference type needs categorization.
     */
    public function isReferenceUncategorized(string $reference): bool
    {
        $ref = strtolower(trim($reference));

        $mapping = ReferenceTypeMapping::where('original_ref', $ref)
            ->where('is_active', true)
            ->first();

        if (!$mapping) {
            return true; // No mapping exists
        }

        return is_null($mapping->summary_category_id);
    }

    /**
     * Assign a category to a reference type.
     */
    public function categorizeReference(string $originalRef, int $categoryId, ?string $mappedRef = null): bool
    {
        try {
            $mapping = ReferenceTypeMapping::where('original_ref', strtolower(trim($originalRef)))
                ->first();

            if ($mapping) {
                $mapping->update([
                    'summary_category_id' => $categoryId,
                    'mapped_ref' => $mappedRef ?: $mapping->mapped_ref,
                    'auto_discovered' => false,
                    'description' => 'Manually categorized',
                ]);
            } else {
                ReferenceTypeMapping::create([
                    'tenant_id' => config('app.tenant_id', 1),
                    'original_ref' => strtolower(trim($originalRef)),
                    'mapped_ref' => $mappedRef ?: strtolower(trim($originalRef)),
                    'summary_category_id' => $categoryId,
                    'is_active' => true,
                    'auto_discovered' => false,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to categorize reference: ' . $e->getMessage());
            return false;
        }
    }
}
