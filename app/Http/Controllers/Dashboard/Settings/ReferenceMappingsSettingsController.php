<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\ReferenceTypeMapping;
use App\Models\SummaryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ReferenceMappingsSettingsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'verified']);
        $this->middleware(['permission:View MPESA Reports|Map MPESA References|View Finances']);
        
        // Require mapping permission for modification actions
        $this->middleware(['permission:Map MPESA References|Manage MPESA Categories'], ['only' => [
            'store', 'update', 'destroy', 'bulkImport'
        ]]);
    }

    /**
     * Display the reference mappings settings page.
     */
    public function index()
    {
        $categories = SummaryCategory::active()->orderBy('name')->get();
        return view('dashboard.settings.reference_mappings', compact('categories'));
    }

    /**
     * Get reference mappings for DataTable.
     */
    public function getMappings(Request $request)
    {
        $query = ReferenceTypeMapping::query()
            ->with('summaryCategory')
            ->select('reference_type_mappings.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('category_name', function ($mapping) {
                return $mapping->summaryCategory ? $mapping->summaryCategory->name : '<span class="text-muted">None</span>';
            })
            ->addColumn('category_color', function ($mapping) {
                return $mapping->summaryCategory ? $mapping->summaryCategory->color : '#6c757d';
            })
            ->addColumn('status_badge', function ($mapping) {
                if ($mapping->is_active) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-secondary">Inactive</span>';
            })
            ->addColumn('action', function ($mapping) {
                return '<button type="button" class="btn btn-sm btn-primary btn-edit" data-id="' . $mapping->id . '">' .
                    '<i class="fas fa-edit"></i> Edit</button> ' .
                    '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $mapping->id . '">' .
                    '<i class="fas fa-trash"></i> Delete</button>';
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('original_ref', 'like', "%{$search}%")
                          ->orWhere('mapped_ref', 'like', "%{$search}%")
                          ->orWhereHas('summaryCategory', function ($sq) use ($search) {
                              $sq->where('name', 'like', "%{$search}%");
                          });
                    });
                }
                if ($request->has('status') && $request->status !== '-1') {
                    $query->where('is_active', $request->status);
                }
                if ($request->has('category') && $request->category !== 'all') {
                    $query->where('summary_category_id', $request->category);
                }
            })
            ->rawColumns(['category_name', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Store a new reference mapping.
     */
    public function store(Request $request)
    {
        $request->validate([
            'original_ref' => 'required|string|max:255|unique:reference_type_mappings,original_ref',
            'mapped_ref' => 'required|string|max:255',
            'summary_category_id' => 'nullable|exists:summary_categories,id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            $mapping = ReferenceTypeMapping::create([
                'tenant_id' => config('app.tenant_id', 1),
                'original_ref' => strtolower(trim($request->original_ref)),
                'mapped_ref' => strtolower(trim($request->mapped_ref)),
                'summary_category_id' => $request->summary_category_id,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reference mapping created successfully.',
                'data' => $mapping
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create mapping: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a reference mapping.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'original_ref' => 'required|string|max:255|unique:reference_type_mappings,original_ref,' . $id,
            'mapped_ref' => 'required|string|max:255',
            'summary_category_id' => 'nullable|exists:summary_categories,id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        try {
            $mapping = ReferenceTypeMapping::findOrFail($id);
            $mapping->update([
                'original_ref' => strtolower(trim($request->original_ref)),
                'mapped_ref' => strtolower(trim($request->mapped_ref)),
                'summary_category_id' => $request->summary_category_id,
                'description' => $request->description,
                'is_active' => $request->is_active ?? $mapping->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reference mapping updated successfully.',
                'data' => $mapping
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update mapping: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a reference mapping.
     */
    public function destroy($id)
    {
        try {
            $mapping = ReferenceTypeMapping::findOrFail($id);
            $mapping->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reference mapping deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete mapping: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk import mappings from unmapped references.
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.original_ref' => 'required|string|max:255',
            'mappings.*.mapped_ref' => 'required|string|max:255',
            'mappings.*.category_id' => 'nullable|exists:summary_categories,id',
            'mappings.*.description' => 'nullable|string|max:500',
            'mappings.*.is_active' => 'boolean',
        ]);

        $created = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->mappings as $mappingData) {
                $originalRef = strtolower(trim($mappingData['original_ref']));
                
                // Skip if already exists
                if (ReferenceTypeMapping::where('original_ref', $originalRef)->exists()) {
                    $skipped++;
                    continue;
                }

                ReferenceTypeMapping::create([
                    'tenant_id' => config('app.tenant_id', 1),
                    'original_ref' => $originalRef,
                    'mapped_ref' => strtolower(trim($mappingData['mapped_ref'])),
                    'summary_category_id' => $mappingData['category_id'] ?? null,
                    'description' => $mappingData['description'] ?? null,
                    'is_active' => $mappingData['is_active'] ?? true,
                ]);
                $created++;
            }
            DB::commit();

            $message = "{$created} mappings created successfully.";
            if ($skipped > 0) {
                $message .= " {$skipped} skipped (already exist).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unmapped references from mpesa transactions.
     */
    public function getUnmappedReferences()
    {
        try {
            // Get mapped references as a lookup array
            $mappedRefs = ReferenceTypeMapping::where('is_active', true)
                ->pluck('original_ref')
                ->map(fn($ref) => strtolower($ref))
                ->flip()
                ->toArray();

            // Get all unique reference types with aggregated stats
            $refStats = DB::table('mpesa_transactions')
                ->select('BillRefNumber', 
                    DB::raw('COUNT(*) as count'), 
                    DB::raw('SUM(TransAmount) as total'))
                ->whereNotNull('BillRefNumber')
                ->groupBy('BillRefNumber')
                ->orderByDesc('count')
                ->limit(100)
                ->get();

            // Filter unmapped references
            $unmapped = [];
            foreach ($refStats as $refData) {
                $ref = strtolower($refData->BillRefNumber ?? '');
                if (!isset($mappedRefs[$ref])) {
                    $unmapped[] = [
                        'reference' => $refData->BillRefNumber,
                        'transaction_count' => $refData->count,
                        'total_amount' => $refData->total,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'unmapped' => $unmapped,
                'total_unmapped' => count($unmapped)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unmapped references: ' . $e->getMessage()
            ], 500);
        }
    }
}
