<?php

namespace App\Http\Controllers\Dashboard\Reports;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\MpesaPhone;
use App\Models\MpesaTransaction;
use App\Models\ReferenceTypeMapping;
use App\Models\Source;
use App\Models\SummaryCategory;
use App\Services\ReferenceTypeAutoDiscoveryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ReportsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        // Base permission for accessing reports module
        $this->middleware(['permission:View MPESA Reports|View Finances']);
        
        // Specific permissions for different actions
        $this->middleware(['permission:Map MPESA References'], ['only' => [
            'saveReferenceMapping', 'inlineMapReference', 'bulkImportMappings'
        ]]);
        
        $this->middleware(['permission:Manage MPESA Categories'], ['only' => [
            'saveSummaryCategory', 'deleteSummaryCategory', 'assignCategoryMapping',
            'removeCategoryMapping', 'syncFundSourcesToCategories'
        ]]);
        
        $this->middleware(['permission:Export MPESA Reports'], ['only' => [
            'printMpesaReport'
        ]]);
        
        $this->middleware(['permission:Rehash MPESA Transactions'], ['only' => [
            'rehashTransaction', 'bulkRehash'
        ]]);
        
        $this->middleware(['permission:Auto-discover MPESA References'], ['only' => [
            'autoDiscoverReferences'
        ]]);
    }

    public function index()
    {
        $tid = config('app.tenant_id');
        $collected = DB::table('funds')->where('funds.tenant_id', $tid)->where('sources.ftype', 0)->join("sources", "sources.id", "funds.source")->sum('funds.amount')
            + DB::table('pledges')->where('tenant_id', $tid)->where('status', 1)->sum('amount')
            + DB::table('purchases')->where('tenant_id', $tid)->where('status', 1)->sum('amount');
        $spent = DB::table('funds')->where('funds.tenant_id', $tid)->where('sources.ftype', 1)->join("sources", "sources.id", "funds.source")->sum('funds.amount');
        $donation = DB::table('donations')->where('tenant_id', $tid)->sum('amount');
        $sources = DB::table("sources")->where('tenant_id', $tid)->get();
        return view('dashboard.reports.index', compact('collected', 'spent', 'donation', 'sources'));
    }

    public function mpesaLogs()
    {
        $totalTransactions = MpesaTransaction::count();
        // Use funds table directly — any fund with source=1 (mpesa) and user_id > 0 is matched
        $matchedTransactions = DB::table('funds')
            ->where('source', 1)
            ->where('user_id', '>', 0)
            ->count();
        $unmatchedTransactions = DB::table('funds')
            ->where('source', 1)
            ->where('user_id', 0)
            ->count();
        $totalHashes = MpesaPhone::count();

        // Get unique reference types from transactions
        $referenceTypes = MpesaTransaction::select('BillRefNumber')
            ->distinct()
            ->whereNotNull('BillRefNumber')
            ->where('BillRefNumber', '!=', '')
            ->orderBy('BillRefNumber')
            ->pluck('BillRefNumber');

        // Get active reference mappings for grouping options
        $referenceMappings = ReferenceTypeMapping::where('is_active', true)
            ->orderBy('mapped_ref')
            ->get()
            ->groupBy('mapped_ref');

        // Get collection fund sources for syncing
        $fundSources = Source::collections()->get();

        // Auto-sync fund sources to summary_categories on first visit — avoids requiring
        // a manual "Sync" button click before the category dropdown works.
        if (SummaryCategory::active()->doesntExist()) {
            $this->performFundSourceSync($fundSources);
        }

        // Get summary categories - only collection sources (ftype=0) for MPESA
        $summaryCategories = SummaryCategory::active()
            ->collections() // Only show collection categories, not expenses
            ->with(['referenceMappings' => function ($q) {
                $q->where('is_active', true);
            }])
            ->get();

        return view('dashboard.reports.mpesa_logs', compact(
            'totalTransactions', 'matchedTransactions', 'unmatchedTransactions', 'totalHashes',
            'referenceTypes', 'referenceMappings', 'summaryCategories', 'fundSources'
        ));
    }

    public function mpesaLogsDataTable(Request $request)
    {
        $query = MpesaTransaction::query();

        // Default to last 90 days if no date filter to prevent full-table scans / timeout
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::now()->subDays(90)->toDateString());
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
        }

        // Status filter
        if ($request->filled('match_status')) {
            if ($request->match_status === 'matched') {
                $query->whereIn('MSISDN', function ($q) {
                    $q->select('phone')->from('mpesa_phones')
                        ->unionAll(
                            DB::table('mpesa_phones')->select('phone_hash')
                        );
                });
            } elseif ($request->match_status === 'unmatched') {
                $query->whereNotIn('MSISDN', function ($q) {
                    $q->select('phone_hash')->from('mpesa_phones');
                })->where(function ($q) {
                    $q->where('MSISDN', 'NOT REGEXP', '^[0-9]{10,15}$')
                        ->orWhereNotIn('MSISDN', function ($sq) {
                            $sq->select(DB::raw("CONCAT('254', SUBSTRING(phone, 2))"))->from('contacts')
                                ->where('phone', '<>','')->whereNotNull('phone');
                        });
                });
            }
        }

        // Reference type filter
        if ($request->filled('reference_type')) {
            $query->where('BillRefNumber', $request->reference_type);
        }

        // Summary category filter - filter by category which groups multiple reference types
        if ($request->filled('summary_category')) {
            $category = SummaryCategory::find($request->summary_category);
            if ($category) {
                $referenceTypes = $category->referenceTypes;
                if (!empty($referenceTypes)) {
                    $query->whereIn('BillRefNumber', $referenceTypes);
                }
            }
        }

        // Group references filter - apply mapping to normalize references
        if ($request->boolean('group_references')) {
            $mappings = ReferenceTypeMapping::getAllMappings();
            // We'll apply grouping logic in the collection after retrieval for complex cases
            // For simple filtering, we can filter by mapped ref if specified
            if ($request->filled('mapped_reference')) {
                $originalRefs = array_keys(array_filter($mappings, function($mapped) use ($request) {
                    return $mapped === $request->mapped_reference;
                }));
                $query->whereIn('BillRefNumber', array_merge($originalRefs, [$request->mapped_reference]));
            }
        }

        // Pre-load ALL mpesa_phones hashes into memory to avoid N+1 per row
        $allMpesaPhones = MpesaPhone::all()->keyBy('phone_hash');
        // Pre-load missing_mpesa_phones hashes
        $missingHashes = DB::table('missing_mpesa_phones')->pluck('phone', 'phone_hash');
        // Pre-load contacts keyed by local phone (0XXXXXXXXX format)
        $allContacts = DB::table('contacts')
            ->join('users', 'users.id', '=', 'contacts.user_id')
            ->select('contacts.phone', 'contacts.user_id', 'users.firstname', 'users.lastname')
            ->get()
            ->keyBy('phone');

        // Pre-load users keyed by phone in 254 international format as fallback for
        // users who have no contacts row (phone lives only in users.phone).
        $allUsers = DB::table('users')
            ->select('id', 'firstname', 'lastname', 'phone')
            ->whereNotNull('phone')
            ->where('phone', '<>', '')
            ->get()
            ->keyBy(function ($u) {
                $phone = trim($u->phone);
                // Normalise to 254XXXXXXXXX so hash-branch lookups ($mp->phone) align
                if (strlen($phone) === 10 && $phone[0] === '0') {
                    return '254' . substr($phone, 1);
                }
                if (strlen($phone) === 9) {
                    return '254' . $phone;
                }
                if ($phone[0] === '+') {
                    return substr($phone, 1);
                }
                return $phone;
            });

        // Load reference mappings for grouping
        $mappings = $request->boolean('group_references') ? ReferenceTypeMapping::getAllMappings() : [];
        
        // Load category mappings for ALL requests so category column is always populated
        $categoryMappings = [];
        $categoryColors = [];
        $allMappings = ReferenceTypeMapping::with('summaryCategory')
            ->where('is_active', true)
            ->whereNotNull('summary_category_id')
            ->get();
        foreach ($allMappings as $mapping) {
            if ($mapping->summaryCategory) {
                $categoryMappings[strtolower(trim($mapping->original_ref))] = $mapping->summaryCategory->name;
                $categoryColors[strtolower(trim($mapping->original_ref))] = $mapping->summaryCategory->color;
            }
        }

        // Get all filtered records for summary calculation
        $allRecords = $query->orderBy('created_at', 'DESC')->get();
        
        // Calculate grouped summary data
        $groupedData = [];
        if ($request->boolean('group_by_category')) {
            // Group by category
            foreach ($allRecords as $record) {
                $originalRef = strtolower(trim($record->BillRefNumber ?? ''));
                $categoryName = $categoryMappings[$originalRef] ?? 'Uncategorized';
                
                if (!isset($groupedData[$categoryName])) {
                    $groupedData[$categoryName] = ['count' => 0, 'total' => 0];
                }
                $groupedData[$categoryName]['count']++;
                $groupedData[$categoryName]['total'] += $record->TransAmount;
            }
        } elseif ($request->boolean('group_references') && !empty($mappings)) {
            foreach ($allRecords as $record) {
                $originalRef = strtolower(trim($record->BillRefNumber ?? ''));
                $mappedRef = $mappings[$originalRef] ?? $record->BillRefNumber ?? 'Unknown';
                
                if (!isset($groupedData[$mappedRef])) {
                    $groupedData[$mappedRef] = ['count' => 0, 'total' => 0];
                }
                $groupedData[$mappedRef]['count']++;
                $groupedData[$mappedRef]['total'] += $record->TransAmount;
            }
        } else {
            foreach ($allRecords as $record) {
                $ref = $record->BillRefNumber ?? 'Unknown';
                if (!isset($groupedData[$ref])) {
                    $groupedData[$ref] = ['count' => 0, 'total' => 0];
                }
                $groupedData[$ref]['count']++;
                $groupedData[$ref]['total'] += $record->TransAmount;
            }
        }

        return DataTables::of($query->orderBy('created_at', 'DESC'))
            ->addColumn('name', function ($row) use ($allMpesaPhones, $allContacts, $allUsers) {
                $msisdn = $row->MSISDN;
                $name = null;

                // Check if MSISDN is a plain numeric phone (e.g. 254712345678)
                if (strlen($msisdn) <= 15 && is_numeric($msisdn)) {
                    $localPhone = '0' . substr($msisdn, 3);
                    if (isset($allContacts[$localPhone])) {
                        $name = $allContacts[$localPhone]->firstname . ' ' . $allContacts[$localPhone]->lastname;
                    } elseif (isset($allUsers[$msisdn])) {
                        // Phone is stored in users.phone (no contacts row)
                        $name = $allUsers[$msisdn]->firstname . ' ' . $allUsers[$msisdn]->lastname;
                    }
                }

                // Check hash match
                if (!$name && isset($allMpesaPhones[$msisdn])) {
                    $mp = $allMpesaPhones[$msisdn];
                    $localPhone = '0' . substr($mp->phone, 3);
                    if (isset($allContacts[$localPhone])) {
                        $name = $allContacts[$localPhone]->firstname . ' ' . $allContacts[$localPhone]->lastname;
                    } elseif (isset($allUsers[$mp->phone])) {
                        // User found via users.phone (no contacts row)
                        $name = $allUsers[$mp->phone]->firstname . ' ' . $allUsers[$mp->phone]->lastname;
                    }
                }

                return $name ?: '<span class="text-muted">-</span>';
            })
            ->addColumn('match_status', function ($row) use ($allMpesaPhones, $allContacts, $allUsers, $missingHashes) {
                $msisdn = $row->MSISDN;

                // Check if MSISDN is a plain numeric phone (e.g. 254712345678)
                if (strlen($msisdn) <= 15 && is_numeric($msisdn)) {
                    $localPhone = '0' . substr($msisdn, 3);
                    if (isset($allContacts[$localPhone]) || isset($allUsers[$msisdn])) {
                        return '<span class="badge bg-success">Matched</span>';
                    }
                }

                // Check hash match against pre-loaded mpesa_phones
                if (isset($allMpesaPhones[$msisdn])) {
                    $mp = $allMpesaPhones[$msisdn];
                    $localPhone = '0' . substr($mp->phone, 3);
                    if (isset($allContacts[$localPhone]) || isset($allUsers[$mp->phone])) {
                        return '<span class="badge bg-info">Hash Matched</span>';
                    }
                    return '<span class="badge bg-warning">Hash Found (No Contact)</span>';
                }

                // Check missing phones
                if (isset($missingHashes[$msisdn]) && $missingHashes[$msisdn]) {
                    return '<span class="badge bg-secondary">Manually Assigned</span>';
                }

                return '<span class="badge bg-danger">Unidentified</span>';
            })
            ->addColumn('amount_fmt', function ($row) {
                return number_format($row->TransAmount, 2);
            })
            ->addColumn('grouped_ref', function ($row) use ($mappings) {
                if (empty($mappings)) {
                    return null;
                }
                $originalRef = strtolower(trim($row->BillRefNumber ?? ''));
                return $mappings[$originalRef] ?? null;
            })
            ->addColumn('category_name', function ($row) use ($categoryMappings, $categoryColors) {
                $originalRef = strtolower(trim($row->BillRefNumber ?? ''));
                $categoryName = $categoryMappings[$originalRef] ?? 'Uncategorized';
                $categoryColor = $categoryColors[$originalRef] ?? '#ffc107';
                
                // Only make clickable if user has mapping permission
                $canMap = auth()->user()->can('Map MPESA References');
                $isUncategorized = $categoryName === 'Uncategorized';
                $badgeClass = $isUncategorized ? 'mapping-badge-uncategorized' : 'mapping-badge-mapped';
                $icon = $isUncategorized ? '<i class="fas fa-exclamation-triangle"></i> ' : '<i class="fas fa-check"></i> ';
                
                if ($canMap) {
                    return '<span class="mapping-badge ' . $badgeClass . ' btn-inline-map" data-ref="' . e($row->BillRefNumber) . '" title="Click to ' . ($isUncategorized ? 'categorize' : 'edit category') . '">' .
                        $icon . e($categoryName) .
                    '</span>';
                } else {
                    return '<span class="mapping-badge ' . $badgeClass . '" title="' . ($isUncategorized ? 'Uncategorized' : 'Category: ' . $categoryName) . '">' .
                        $icon . e($categoryName) .
                    '</span>';
                }
            })
            ->addColumn('category_color', function ($row) use ($categoryColors) {
                $originalRef = strtolower(trim($row->BillRefNumber ?? ''));
                return $categoryColors[$originalRef] ?? '#6c757d';
            })
            ->addColumn('date_fmt', function ($row) {
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') : '-';
            })
            ->addColumn('msisdn_display', function ($row) {
                if (strlen($row->MSISDN) <= 15 && is_numeric($row->MSISDN)) {
                    return $row->MSISDN;
                }
                return '<span class="text-muted" title="' . e($row->MSISDN) . '">' . substr($row->MSISDN, 0, 16) . '...</span>';
            })
            ->addColumn('action', function ($row) {
                // Build actions based on user permissions
                $actions = '';
                
                if (auth()->user()->can('Rehash MPESA Transactions')) {
                    $actions .= '<a class="dropdown-item btn-rehash" href="#" data-id="' . $row->id . '">' .
                        '<i class="fas fa-sync-alt text-primary mr-2"></i> Re-check Hash Match' .
                    '</a>';
                }
                
                if (auth()->user()->can('Map MPESA References')) {
                    $actions .= '<a class="dropdown-item btn-inline-map" href="#" data-ref="' . e($row->BillRefNumber) . '">' .
                        '<i class="fas fa-map-signs text-warning mr-2"></i> Categorize Reference' .
                    '</a>';
                }
                
                if (empty($actions)) {
                    return '<span class="text-muted">-</span>';
                }
                
                return '<div class="dropdown">' .
                    '<button class="btn btn-xs btn-light dropdown-toggle border" type="button" data-toggle="dropdown" aria-expanded="false" title="Actions">' .
                        '<i class="fas fa-ellipsis-v"></i>' .
                    '</button>' .
                    '<div class="dropdown-menu dropdown-menu-right dropdown-action-menu shadow-sm">' .
                        '<h6 class="dropdown-header">Actions</h6>' .
                        $actions .
                    '</div>' .
                '</div>';
            })
            ->rawColumns(['name', 'match_status', 'msisdn_display', 'action', 'grouped_ref', 'category_name'])
            ->with('groupedData', $groupedData)
            ->make(true);
    }

    public function rehashTransaction(Request $request)
    {
        $request->validate(['id' => 'required|integer']);

        $transaction = MpesaTransaction::findOrFail($request->id);
        $msisdn = $transaction->MSISDN;
        $result = ['status' => 'unidentified', 'message' => 'No match found.'];

        // 1. Try direct phone lookup
        if (strlen($msisdn) <= 15 && is_numeric($msisdn)) {
            $contact = DB::table('contacts')->where('phone', '0' . substr($msisdn, 3))->first();
            if ($contact) {
                // Update corresponding fund record
                $this->matchFundToUser($transaction, $contact->user_id);
                $user = DB::table('users')->where('id', $contact->user_id)->first();
                $result = [
                    'status' => 'matched',
                    'message' => 'Directly matched to ' . ($user ? $user->firstname . ' ' . $user->lastname : 'User #' . $contact->user_id),
                ];
                return response()->json($result);
            }
        }

        // 2. Try existing hash match — withoutTenantScope() so this works cross-tenant
        $mpesaPhone = MpesaPhone::withoutTenantScope()->where('phone_hash', $msisdn)->first();
        if ($mpesaPhone) {
            $contact = DB::table('contacts')
                ->where('phone', '0' . substr($mpesaPhone->phone, 3))
                ->orWhere('phone', $mpesaPhone->phone)
                ->first();
            if ($contact) {
                $this->matchFundToUser($transaction, $contact->user_id);
                $user = DB::table('users')->where('id', $contact->user_id)->first();
                $result = [
                    'status' => 'hash_matched',
                    'message' => 'Hash matched to ' . ($user ? $user->firstname . ' ' . $user->lastname : 'User #' . $contact->user_id),
                ];
                return response()->json($result);
            }
        }

        // 3. Try re-hashing all known phones against this MSISDN
        $allPhones = DB::table('contacts')
            ->whereNotNull('contacts.phone')->where('contacts.phone', '<>', '')
            ->join('users', 'users.id', '=', 'contacts.user_id')
            ->select('contacts.phone', 'contacts.user_id', 'users.firstname', 'users.lastname')
            ->get();

        foreach ($allPhones as $phoneRecord) {
            $phone = trim($phoneRecord->phone);
            if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
                $phone = '254' . substr($phone, 1);
            } elseif (strlen($phone) == 9) {
                $phone = '254' . $phone;
            } elseif (substr($phone, 0, 1) == '+') {
                $phone = substr($phone, 1);
            }
            if (strlen($phone) != 12) continue;

            $hash = hash('sha256', $phone);

            // Ensure this hash is in mpesa_phones table
            if (!MpesaPhone::withoutTenantScope()->where('phone_hash', $hash)->exists()) {
                MpesaPhone::create([
                    'name' => $phoneRecord->firstname . ' ' . $phoneRecord->lastname,
                    'phone' => $phone,
                    'phone_hash' => $hash,
                ]);
            }

            if ($hash === $msisdn) {
                $this->matchFundToUser($transaction, $phoneRecord->user_id);
                $result = [
                    'status' => 'newly_matched',
                    'message' => 'Newly matched via hash to ' . $phoneRecord->firstname . ' ' . $phoneRecord->lastname,
                ];
                return response()->json($result);
            }
        }

        // Also try users.phone field directly
        $allUsers = DB::table('users')
            ->whereNotNull('phone')->where('phone', '<>', '')
            ->select('id', 'phone', 'firstname', 'lastname')
            ->get();

        foreach ($allUsers as $userRecord) {
            $phone = trim($userRecord->phone);
            if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
                $phone = '254' . substr($phone, 1);
            }
            if (strlen($phone) != 12) continue;

            $hash = hash('sha256', $phone);
            if ($hash === $msisdn) {
                $this->matchFundToUser($transaction, $userRecord->id);

                // Ensure hash is stored
                if (!MpesaPhone::withoutTenantScope()->where('phone_hash', $hash)->exists()) {
                    MpesaPhone::create([
                        'name' => $userRecord->firstname . ' ' . $userRecord->lastname,
                        'phone' => $phone,
                        'phone_hash' => $hash,
                    ]);
                }

                $result = [
                    'status' => 'newly_matched',
                    'message' => 'Newly matched via hash to ' . $userRecord->firstname . ' ' . $userRecord->lastname,
                ];
                return response()->json($result);
            }
        }

        return response()->json($result);
    }

    public function bulkRehash(Request $request)
    {
        // 1. Populate hashes for all known phone numbers
        $contacts = DB::table('contacts')
            ->whereNotNull('contacts.phone')->where('contacts.phone', '<>', '')
            ->join('users', 'users.id', '=', 'contacts.user_id')
            ->select('contacts.phone', 'contacts.user_id', 'users.firstname', 'users.lastname')
            ->get();

        $hashesAdded = 0;
        foreach ($contacts as $contact) {
            $phone = trim($contact->phone);
            if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
                $phone = '254' . substr($phone, 1);
            } elseif (strlen($phone) == 9) {
                $phone = '254' . $phone;
            } elseif (substr($phone, 0, 1) == '+') {
                $phone = substr($phone, 1);
            }
            if (strlen($phone) != 12) continue;

            $hash = hash('sha256', $phone);
            if (!MpesaPhone::withoutTenantScope()->where('phone_hash', $hash)->exists()) {
                MpesaPhone::create([
                    'name' => $contact->firstname . ' ' . $contact->lastname,
                    'phone' => $phone,
                    'phone_hash' => $hash,
                ]);
                $hashesAdded++;
            }
        }

        // Also hash from users.phone
        $users = DB::table('users')
            ->whereNotNull('phone')->where('phone', '<>', '')
            ->select('id', 'phone', 'firstname', 'lastname')
            ->get();

        foreach ($users as $user) {
            $phone = trim($user->phone);
            if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
                $phone = '254' . substr($phone, 1);
            }
            if (strlen($phone) != 12) continue;

            $hash = hash('sha256', $phone);
            if (!MpesaPhone::withoutTenantScope()->where('phone_hash', $hash)->exists()) {
                MpesaPhone::create([
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'phone' => $phone,
                    'phone_hash' => $hash,
                ]);
                $hashesAdded++;
            }
        }

        // 2. Re-match all unmatched funds (user_id=0, source=1) using new hashes
        $unmatched = DB::table('funds')
            ->where('user_id', 0)
            ->where('source', 1)
            ->get();

        $matched = 0;
        foreach ($unmatched as $fund) {
            $transaction = DB::table('mpesa_transactions')
                ->where('TransAmount', $fund->amount)
                ->whereDate('created_at', \Carbon\Carbon::parse($fund->created_at)->toDateString())
                ->first();

            if (!$transaction) continue;

            $msisdn = $transaction->MSISDN;

            // Direct match
            if (strlen($msisdn) <= 15 && is_numeric($msisdn)) {
                $contact = DB::table('contacts')
                    ->where('phone', '0' . substr($msisdn, 3))
                    ->orWhere('phone', $msisdn)
                    ->first();
                if ($contact) {
                    DB::table('funds')->where('id', $fund->id)->update(['user_id' => $contact->user_id]);
                    $matched++;
                    continue;
                }
                // Also check users.phone
                $user = DB::table('users')->where('phone', $msisdn)->first();
                if ($user) {
                    DB::table('funds')->where('id', $fund->id)->update(['user_id' => $user->id]);
                    $matched++;
                    continue;
                }
            }

            // Hash match
            $mpesaPhone = MpesaPhone::withoutTenantScope()->where('phone_hash', $msisdn)->first();
            if ($mpesaPhone) {
                $contact = DB::table('contacts')
                    ->where('phone', '0' . substr($mpesaPhone->phone, 3))
                    ->orWhere('phone', $mpesaPhone->phone)
                    ->first();
                if ($contact) {
                    DB::table('funds')->where('id', $fund->id)->update(['user_id' => $contact->user_id]);
                    $matched++;
                    continue;
                }
                $user = DB::table('users')->where('phone', $mpesaPhone->phone)->first();
                if ($user) {
                    DB::table('funds')->where('id', $fund->id)->update(['user_id' => $user->id]);
                    $matched++;
                }
            }
        }

        return response()->json([
            'success' => "Bulk re-hash complete. {$hashesAdded} new hashes added, {$matched} fund records matched.",
            'hashes_added' => $hashesAdded,
            'funds_matched' => $matched,
        ]);
    }

    private function matchFundToUser(MpesaTransaction $transaction, int $userId)
    {
        // Find the fund record that matches this transaction by amount + date
        $fund = DB::table('funds')
            ->where('user_id', 0)
            ->where('source', 1)
            ->where('amount', $transaction->TransAmount)
            ->whereDate('created_at', \Carbon\Carbon::parse($transaction->created_at)->toDateString())
            ->first();

        if ($fund) {
            DB::table('funds')->where('id', $fund->id)->update(['user_id' => $userId]);
        }

        // Also remove from missing_mpesa_phones if present
        DB::table('missing_mpesa_phones')
            ->where('trans_id', $transaction->TransID)
            ->delete();
    }

    /**
     * Get unique reference types for filter dropdown.
     */
    public function getReferenceTypes(Request $request)
    {
        $types = MpesaTransaction::select('BillRefNumber')
            ->distinct()
            ->whereNotNull('BillRefNumber')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('BillRefNumber', 'like', '%' . $request->search . '%');
            })
            ->pluck('BillRefNumber')
            ->filter()
            ->sort()
            ->values();

        return response()->json($types);
    }

    /**
     * Save a new reference type mapping.
     */
    public function saveReferenceMapping(Request $request)
    {
        $request->validate([
            'original_ref' => 'required|string|max:255',
            'mapped_ref' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'summary_category_id' => 'nullable|exists:summary_categories,id',
        ]);

        $mapping = ReferenceTypeMapping::updateOrCreate(
            [
                'tenant_id' => config('app.tenant_id', 1),
                'original_ref' => strtolower(trim($request->original_ref)),
            ],
            [
                'mapped_ref' => strtolower(trim($request->mapped_ref)),
                'summary_category_id' => $request->summary_category_id,
                'description' => $request->description,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Reference mapping saved successfully.',
            'mapping' => $mapping,
        ]);
    }

    /**
     * Delete a reference type mapping.
     */
    public function deleteReferenceMapping($id)
    {
        $mapping = ReferenceTypeMapping::findOrFail($id);
        $mapping->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reference mapping deleted successfully.',
        ]);
    }

    /**
     * Get all reference mappings.
     */
    public function getReferenceMappings()
    {
        $mappings = ReferenceTypeMapping::with('summaryCategory')
            ->where('is_active', true)
            ->orderBy('mapped_ref')
            ->orderBy('original_ref')
            ->get();

        return response()->json($mappings);
    }

    /**
     * Get all summary categories.
     */
    public function getSummaryCategories()
    {
        $categories = SummaryCategory::active()
            ->withCount('referenceMappings')
            ->get();

        return response()->json($categories);
    }

    /**
     * Save a summary category.
     */
    public function saveSummaryCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:summary_categories,code,' . $request->id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        $category = SummaryCategory::updateOrCreate(
            ['id' => $request->id],
            [
                'tenant_id' => config('app.tenant_id', 1),
                'name' => $request->name,
                'code' => strtolower(trim($request->code)),
                'description' => $request->description,
                'color' => $request->color ?? '#007bff',
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Summary category saved successfully.',
            'category' => $category,
        ]);
    }

    /**
     * Delete a summary category.
     */
    public function deleteSummaryCategory($id)
    {
        $category = SummaryCategory::findOrFail($id);
        
        // Remove category association from mappings
        ReferenceTypeMapping::where('summary_category_id', $id)->update(['summary_category_id' => null]);
        
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Summary category deleted successfully.',
        ]);
    }

    /**
     * Assign reference mappings to a category.
     */
    public function assignMappingsToCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:summary_categories,id',
            'mapping_ids' => 'required|array',
            'mapping_ids.*' => 'exists:reference_type_mappings,id',
        ]);

        ReferenceTypeMapping::whereIn('id', $request->mapping_ids)
            ->update(['summary_category_id' => $request->category_id]);

        return response()->json([
            'success' => true,
            'message' => 'Mappings assigned to category successfully.',
        ]);
    }

    /**
     * Remove mappings from a category.
     */
    public function removeMappingsFromCategory(Request $request)
    {
        $request->validate([
            'mapping_ids' => 'required|array',
            'mapping_ids.*' => 'exists:reference_type_mappings,id',
        ]);

        ReferenceTypeMapping::whereIn('id', $request->mapping_ids)
            ->update(['summary_category_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Mappings removed from category.',
        ]);
    }

    /**
     * Get category summary data for a date range.
     * Optimized to use database aggregation.
     */
    public function getCategorySummary(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        // Build reference to category lookup cache
        static $refToCategoryCache = null;
        if ($refToCategoryCache === null) {
            $refToCategoryCache = [];
            $categories = SummaryCategory::active()
                ->select('id', 'name', 'color')
                ->with(['referenceMappings' => function ($q) {
                    $q->select('summary_category_id', 'original_ref')->where('is_active', true);
                }])
                ->get();
            
            foreach ($categories as $category) {
                foreach ($category->referenceMappings as $mapping) {
                    $refToCategoryCache[strtolower($mapping->original_ref)] = [
                        'name' => $category->name,
                        'color' => $category->color,
                    ];
                }
            }
        }

        // Use database aggregation instead of loading all transactions
        $query = MpesaTransaction::query();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Group by BillRefNumber and aggregate
        $refTotals = $query->select('BillRefNumber', 
                DB::raw('COUNT(*) as count'), 
                DB::raw('SUM(TransAmount) as total'))
            ->groupBy('BillRefNumber')
            ->get();

        $totalCount = 0;
        $totalAmount = 0;
        $summary = [];

        foreach ($refTotals as $refData) {
            $ref = strtolower($refData->BillRefNumber ?? '');
            $categoryName = $refToCategoryCache[$ref]['name'] ?? 'Uncategorized';
            $categoryColor = $refToCategoryCache[$ref]['color'] ?? '#6c757d';

            if (!isset($summary[$categoryName])) {
                $summary[$categoryName] = [
                    'count' => 0,
                    'total' => 0,
                    'color' => $categoryColor,
                ];
            }
            $summary[$categoryName]['count'] += $refData->count;
            $summary[$categoryName]['total'] += $refData->total;
            
            $totalCount += $refData->count;
            $totalAmount += $refData->total;
        }

        // Sort by total amount descending
        uasort($summary, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        return response()->json([
            'summary' => $summary,
            'total_count' => $totalCount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Sync fund sources (collection only) to summary categories.
     * This ensures categories match the fund sources available in settings.
     */
    public function syncFundSourcesToCategories()
    {
        $sources = Source::collections()
            ->select('id', 'name', 'description')
            ->limit(100)
            ->get();

        $synced = $this->performFundSourceSync($sources);

        return response()->json([
            'success' => true,
            'message' => "{$synced} new fund sources synced to categories.",
            'synced' => $synced,
        ]);
    }

    /**
     * Shared sync logic — create summary_category records for any fund source
     * that doesn't already have one. Returns the count of newly created records.
     *
     * @param \Illuminate\Support\Collection $sources  Collection of Source models
     */
    private function performFundSourceSync($sources): int
    {
        $existingIds = array_flip(
            SummaryCategory::withoutTenantScope()
                ->whereIn('fund_source_id', $sources->pluck('id'))
                ->where('tenant_id', config('app.tenant_id'))
                ->pluck('fund_source_id')
                ->toArray()
        );

        $synced = 0;
        foreach ($sources as $source) {
            if (isset($existingIds[$source->id])) {
                continue;
            }
            SummaryCategory::create([
                'tenant_id'       => config('app.tenant_id'),
                'fund_source_id'  => $source->id,
                'name'            => $source->name,
                'code'            => 'fund_' . $source->id,
                'description'     => $source->description ?? '',
                'color'           => $this->generateCategoryColor($source->id),
                'sort_order'      => $source->id,
                'is_active'       => true,
                'is_default'      => true,
            ]);
            $synced++;
        }
        return $synced;
    }

    /**
     * Generate a consistent color for a category based on ID.
     */
    private function generateCategoryColor(int $id): string
    {
        $colors = [
            '#007bff', // Blue
            '#28a745', // Green
            '#dc3545', // Red
            '#ffc107', // Yellow
            '#17a2b8', // Cyan
            '#6f42c1', // Purple
            '#e83e8c', // Pink
            '#fd7e14', // Orange
            '#20c997', // Teal
            '#6c757d', // Gray
        ];
        return $colors[$id % count($colors)];
    }

    /**
     * Print MPESA transactions report.
     */
    public function printMpesaReport(Request $request)
    {
        $query = MpesaTransaction::query();

        // Default to last 90 days if no date filter
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::now()->subDays(90)->toDateString());
            $dateFrom = \Carbon\Carbon::now()->subDays(90)->format('d M Y');
            $dateTo = \Carbon\Carbon::now()->format('d M Y');
        } else {
            // Date filters
            $dateFrom = 'All time';
            $dateTo = 'All time';
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
                $dateFrom = \Carbon\Carbon::parse($request->date_from)->format('d M Y');
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
                $dateTo = \Carbon\Carbon::parse($request->date_to)->format('d M Y');
            }
        }

        // Reference type filter
        if ($request->filled('reference_type')) {
            $query->where('BillRefNumber', $request->reference_type);
        }

        // Summary category filter - for fund source filtering
        $filteredCategory = null;
        if ($request->filled('summary_category')) {
            $category = SummaryCategory::find($request->summary_category);
            if ($category) {
                $filteredCategory = $category;
                $referenceTypes = $category->referenceTypes;
                if (!empty($referenceTypes)) {
                    $query->whereIn('BillRefNumber', $referenceTypes);
                }
            }
        }

        // Load all reference mappings for categorization
        $mappings = ReferenceTypeMapping::getAllMappings();
        
        // Load all categories with their colors
        $allCategories = SummaryCategory::active()->get()->keyBy('id');
        
        // Build category mappings - map original_ref to category info
        $categoryMappings = [];
        $categoryColors = [];
        $categoryFundSources = []; // Track which fund source each category belongs to
        
        $allReferenceMappings = ReferenceTypeMapping::with('summaryCategory')
            ->where('is_active', true)
            ->get();
            
        foreach ($allReferenceMappings as $mapping) {
            $originalRef = strtolower($mapping->original_ref);
            
            if ($mapping->summaryCategory) {
                $categoryMappings[$originalRef] = $mapping->summaryCategory->name;
                $categoryColors[$originalRef] = $mapping->summaryCategory->color;
                $categoryFundSources[$originalRef] = [
                    'category_name' => $mapping->summaryCategory->name,
                    'category_id' => $mapping->summaryCategory->id,
                    'color' => $mapping->summaryCategory->color,
                ];
            } else {
                // Use mapped_ref as category if no category assigned
                $categoryMappings[$originalRef] = ucfirst($mapping->mapped_ref);
                $categoryColors[$originalRef] = '#6c757d';
                $categoryFundSources[$originalRef] = [
                    'category_name' => ucfirst($mapping->mapped_ref),
                    'category_id' => null,
                    'color' => '#6c757d',
                ];
            }
        }

        // Get transactions with user matching info
        $transactions = $query->orderBy('created_at', 'DESC')->get();

        // Pre-load all mpesa_phones hashes
        $allMpesaPhones = MpesaPhone::all()->keyBy('phone_hash');
        $allContacts = DB::table('contacts')
            ->join('users', 'users.id', '=', 'contacts.user_id')
            ->select('contacts.phone', 'contacts.user_id', 'users.firstname', 'users.lastname')
            ->get()
            ->keyBy('phone');

        // Process transactions with match status and grouped references
        $processedTransactions = $transactions->map(function ($row) use ($allMpesaPhones, $allContacts, $mappings) {
            $msisdn = $row->MSISDN;
            $matchStatus = 'Unidentified';
            $matchedUser = null;

            // Check direct match
            if (strlen($msisdn) <= 15 && is_numeric($msisdn)) {
                $localPhone = '0' . substr($msisdn, 3);
                if (isset($allContacts[$localPhone])) {
                    $c = $allContacts[$localPhone];
                    $matchStatus = 'Matched';
                    $matchedUser = $c->firstname . ' ' . $c->lastname;
                }
            }

            // Check hash match
            if ($matchStatus === 'Unidentified' && isset($allMpesaPhones[$msisdn])) {
                $mp = $allMpesaPhones[$msisdn];
                $localPhone = '0' . substr($mp->phone, 3);
                if (isset($allContacts[$localPhone])) {
                    $c = $allContacts[$localPhone];
                    $matchStatus = 'Hash Matched';
                    $matchedUser = $c->firstname . ' ' . $c->lastname;
                }
            }

            // Apply reference grouping
            $originalRef = $row->BillRefNumber;
            $groupedRef = $originalRef;
            if (!empty($mappings) && isset($mappings[strtolower($originalRef)])) {
                $groupedRef = $mappings[strtolower($originalRef)];
            }

            return (object) [
                'TransID' => $row->TransID,
                'FirstName' => $row->FirstName,
                'MiddleName' => $row->MiddleName,
                'LastName' => $row->LastName,
                'TransAmount' => $row->TransAmount,
                'BillRefNumber' => $originalRef,
                'GroupedRef' => $groupedRef,
                'MSISDN' => $msisdn,
                'match_status' => $matchStatus,
                'matched_user' => $matchedUser,
                'created_at' => $row->created_at,
            ];
        });

        // Add category/fund source info to each transaction
        $processedTransactions = $processedTransactions->map(function ($tx) use ($categoryMappings, $categoryColors, $categoryFundSources) {
            $originalRef = strtolower($tx->BillRefNumber ?? '');
            $tx->CategoryName = $categoryMappings[$originalRef] ?? 'Uncategorized';
            $tx->CategoryColor = $categoryColors[$originalRef] ?? '#6c757d';
            $tx->FundSource = $categoryFundSources[$originalRef] ?? [
                'category_name' => 'Uncategorized',
                'category_id' => null,
                'color' => '#6c757d',
            ];
            return $tx;
        });

        // Group by category/fund source by default, or based on request
        if ($request->boolean('group_by_category')) {
            $groupedData = $processedTransactions->groupBy('CategoryName')->sortKeys();
        } elseif ($request->boolean('group_references')) {
            $groupedData = $processedTransactions->groupBy('GroupedRef')->sortKeys();
        } elseif ($request->boolean('group_by_fund_source')) {
            // Group by fund source - order by total amount
            $groupedData = $processedTransactions->groupBy('CategoryName')->sort(function($a, $b) {
                return $b->sum('TransAmount') <=> $a->sum('TransAmount');
            });
        } else {
            // Default: group by category (fund source) if no specific grouping requested
            $groupedData = $processedTransactions->groupBy('CategoryName')->sortKeys();
        }
        
        // Calculate fund source summaries
        $fundSourceSummary = [];
        foreach ($processedTransactions as $tx) {
            $categoryName = $tx->CategoryName;
            if (!isset($fundSourceSummary[$categoryName])) {
                $fundSourceSummary[$categoryName] = [
                    'count' => 0,
                    'total' => 0,
                    'color' => $tx->CategoryColor,
                    'category_id' => $tx->FundSource['category_id'],
                ];
            }
            $fundSourceSummary[$categoryName]['count']++;
            $fundSourceSummary[$categoryName]['total'] += $tx->TransAmount;
        }
        
        // Sort by total amount descending
        uasort($fundSourceSummary, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Calculate totals
        $totals = [];
        foreach ($groupedData as $ref => $items) {
            $totals[$ref] = $items->sum('TransAmount');
        }
        $grandTotal = $processedTransactions->sum('TransAmount');

        // Column selection for print - handle both array and comma-separated string
        $defaultColumns = ['trans_id', 'name', 'amount', 'account', 'category', 'match_status', 'date'];
        $selectedColumns = $request->input('columns', $defaultColumns);
        
        // Convert to array if it's a comma-separated string
        if (is_string($selectedColumns)) {
            $selectedColumns = explode(',', $selectedColumns);
            $selectedColumns = array_map('trim', $selectedColumns);
        }
        
        // Ensure it's a valid array
        if (!is_array($selectedColumns) || empty($selectedColumns)) {
            $selectedColumns = $defaultColumns;
        }
        
        // Define all available columns
        $availableColumns = [
            'trans_id' => 'Trans ID',
            'name' => 'Name',
            'amount' => 'Amount',
            'account' => 'Account',
            'category' => 'Category',
            'grouped_ref' => 'Grouped As',
            'msisdn' => 'MSISDN',
            'match_status' => 'Match Status',
            'matched_user' => 'Matched User',
            'date' => 'Date',
        ];

        return view('dashboard.reports.mpesa_report_print', compact(
            'groupedData', 'totals', 'grandTotal', 'dateFrom', 'dateTo', 'request',
            'selectedColumns', 'availableColumns', 'fundSourceSummary', 'filteredCategory'
        ));
    }

    /**
     * Auto-create reference mappings based on similar spellings and categories.
     */
    public function suggestReferenceMappings(Request $request)
    {
        // Get all unique reference types
        $refs = MpesaTransaction::select('BillRefNumber')
            ->distinct()
            ->whereNotNull('BillRefNumber')
            ->pluck('BillRefNumber')
            ->filter()
            ->values();

        // Get existing categories for matching
        $categories = SummaryCategory::active()->get();
        $categoryNames = $categories->pluck('name')->map(function($name) {
            return strtolower($name);
        })->toArray();

        $suggestions = [];
        $commonTypes = ['offering', 'tithe', 'milik', 'building', 'charity', 'welfare', 'thanksgiving', 'seed'];

        foreach ($refs as $ref) {
            $refLower = strtolower($ref);
            
            // Skip if already has a mapping
            if (ReferenceTypeMapping::where('original_ref', $refLower)->exists()) {
                continue;
            }

            // Find closest match in common types
            $bestMatch = null;
            $bestScore = PHP_INT_MAX;

            foreach ($commonTypes as $type) {
                $distance = levenshtein($refLower, $type);
                if ($distance < $bestScore && $distance <= 3) {
                    $bestScore = $distance;
                    $bestMatch = $type;
                }
            }

            // Also check against category names for better matching
            $matchedCategory = null;
            foreach ($categoryNames as $categoryName) {
                $distance = levenshtein($refLower, $categoryName);
                if ($distance < $bestScore && $distance <= 3) {
                    $bestScore = $distance;
                    $bestMatch = $categoryName;
                    $matchedCategory = $categories->first(function($cat) use ($categoryName) {
                        return strtolower($cat->name) === $categoryName;
                    });
                }
            }

            if ($bestMatch && $bestMatch !== $refLower) {
                $suggestions[] = [
                    'original' => $ref,
                    'suggested' => $bestMatch,
                    'confidence' => max(0, 100 - ($bestScore * 20)),
                    'category_id' => $matchedCategory ? $matchedCategory->id : null,
                    'category_name' => $matchedCategory ? $matchedCategory->name : null,
                    'category_color' => $matchedCategory ? $matchedCategory->color : null,
                ];
            }
        }

        return response()->json($suggestions);
    }

    /**
     * Get unmapped reference types for admin notification.
     */
    public function getUnmappedReferences(Request $request)
    {
        // Get mapped references as a lookup array for O(1) access
        $mappedRefs = ReferenceTypeMapping::where('is_active', true)
            ->pluck('original_ref')
            ->map(fn($ref) => strtolower($ref))
            ->flip() // Use flip for faster isset() lookup
            ->toArray();

        // Get all unique reference types with aggregated stats in a single query
        $refStats = MpesaTransaction::select('BillRefNumber', 
                DB::raw('COUNT(*) as count'), 
                DB::raw('SUM(TransAmount) as total'))
            ->whereNotNull('BillRefNumber')
            ->groupBy('BillRefNumber')
            ->get();

        // Filter unmapped references in memory
        $unmapped = [];
        foreach ($refStats as $refData) {
            $ref = $refData->BillRefNumber;
            if (!isset($mappedRefs[strtolower($ref)])) {
                $unmapped[] = [
                    'reference' => $ref,
                    'transaction_count' => $refData->count,
                    'total_amount' => $refData->total,
                ];
            }
        }

        // Sort by transaction count descending
        usort($unmapped, function($a, $b) {
            return $b['transaction_count'] <=> $a['transaction_count'];
        });

        return response()->json([
            'unmapped' => $unmapped,
            'total_unmapped' => count($unmapped),
            'needs_attention' => count($unmapped) > 0,
        ]);
    }

    /**
     * Auto-discover new reference types from MPESA transactions.
     */
    public function autoDiscoverReferences()
    {
        $service = new ReferenceTypeAutoDiscoveryService();
        $stats = $service->discoverNewReferenceTypes();

        return response()->json([
            'success' => true,
            'message' => "Discovery complete. Found {$stats['new_found']} new references, added {$stats['added']} mappings.",
            'stats' => $stats,
        ]);
    }

    /**
     * Inline map a reference type from the MPESA logs table.
     */
    public function inlineMapReference(Request $request)
    {
        $request->validate([
            'original_ref' => 'required|string|max:255',
            'summary_category_id' => 'required|integer|exists:summary_categories,id',
            'mapped_ref' => 'nullable|string|max:255',
        ]);

        try {
            $service = new ReferenceTypeAutoDiscoveryService();
            $success = $service->categorizeReference(
                $request->original_ref,
                $request->summary_category_id,
                $request->mapped_ref
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reference mapped successfully.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to map reference.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Inline mapping failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get mapping suggestions for a reference type.
     */
    public function getReferenceSuggestions(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:255',
        ]);

        $reference = strtolower(trim($request->reference));
        
        // Check if already mapped
        $existing = ReferenceTypeMapping::where('original_ref', $reference)
            ->with('summaryCategory')
            ->first();

        if ($existing && $existing->summary_category_id) {
            return response()->json([
                'mapped' => true,
                'mapping' => $existing,
                'category_name' => $existing->summaryCategory?->name,
                'category_color' => $existing->summaryCategory?->color,
            ]);
        }

        // Get all categories for selection
        $categories = SummaryCategory::active()->orderBy('name')->get();

        // Suggest based on similarity
        $suggestions = [];
        $allMappings = ReferenceTypeMapping::whereNotNull('summary_category_id')
            ->with('summaryCategory')
            ->get();

        foreach ($allMappings as $mapping) {
            similar_text($reference, strtolower($mapping->original_ref), $percent);
            if ($percent > 60) {
                $suggestions[] = [
                    'reference' => $mapping->original_ref,
                    'mapped_to' => $mapping->mapped_ref,
                    'category' => $mapping->summaryCategory?->name,
                    'category_id' => $mapping->summary_category_id,
                    'similarity' => round($percent, 1),
                ];
            }
        }

        // Sort by similarity
        usort($suggestions, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return response()->json([
            'mapped' => false,
            'reference' => $request->reference,
            'suggested_mapped_ref' => $this->suggestMappedRef($reference),
            'categories' => $categories,
            'similar_mappings' => array_slice($suggestions, 0, 5),
        ]);
    }

    /**
     * Suggest a mapped reference based on patterns.
     */
    private function suggestMappedRef(string $originalRef): string
    {
        $ref = strtolower($originalRef);
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

        return preg_replace('/[^a-z0-9]/', '', $ref);
    }
}
