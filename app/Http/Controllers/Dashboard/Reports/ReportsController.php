<?php

namespace App\Http\Controllers\Dashboard\Reports;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\MpesaPhone;
use App\Models\MpesaTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ReportsController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Finances']);
    }

    public function index()
    {
        $collected = DB::table('funds')->where('sources.ftype', 0)->join("sources", "sources.id", "funds.source")->sum('amount')
            + DB::table('pledges')->where('status', 1)->sum('amount')
            + DB::table('purchases')->where('status', 1)->sum('amount');
        $spent = DB::table('funds')->where('sources.ftype', 1)->join("sources", "sources.id", "funds.source")->sum('amount');
        $donation = DB::table('donations')->sum('amount');
        $sources = DB::table("sources")->get();
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

        return view('dashboard.reports.mpesa_logs', compact(
            'totalTransactions', 'matchedTransactions', 'unmatchedTransactions', 'totalHashes'
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

        // Pre-load ALL mpesa_phones hashes into memory to avoid N+1 per row
        $allMpesaPhones = MpesaPhone::all()->keyBy('phone_hash');
        // Pre-load missing_mpesa_phones hashes
        $missingHashes = DB::table('missing_mpesa_phones')->pluck('phone', 'phone_hash');
        // Pre-load contacts keyed by local phone
        $allContacts = DB::table('contacts')
            ->join('users', 'users.id', '=', 'contacts.user_id')
            ->select('contacts.phone', 'contacts.user_id', 'users.firstname', 'users.lastname')
            ->get()
            ->keyBy('phone');

        return DataTables::of($query->orderBy('created_at', 'DESC'))
            ->addColumn('match_status', function ($row) use ($allMpesaPhones, $allContacts, $missingHashes) {
                $msisdn = $row->MSISDN;

                // Check if MSISDN is a plain numeric phone (e.g. 254712345678)
                if (strlen($msisdn) <= 15 && is_numeric($msisdn)) {
                    $localPhone = '0' . substr($msisdn, 3);
                    if (isset($allContacts[$localPhone])) {
                        $c = $allContacts[$localPhone];
                        $name = $c->firstname . ' ' . $c->lastname;
                        return '<span class="badge bg-success">Matched</span> <small>' . e($name) . '</small>';
                    }
                }

                // Check hash match against pre-loaded mpesa_phones
                if (isset($allMpesaPhones[$msisdn])) {
                    $mp = $allMpesaPhones[$msisdn];
                    $localPhone = '0' . substr($mp->phone, 3);
                    if (isset($allContacts[$localPhone])) {
                        $c = $allContacts[$localPhone];
                        $name = $c->firstname . ' ' . $c->lastname;
                        return '<span class="badge bg-info">Hash Matched</span> <small>' . e($name) . '</small>';
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
            ->addColumn('date_fmt', function ($row) {
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y, H:i') : '-';
            })
            ->addColumn('msisdn_display', function ($row) {
                if (strlen($row->MSISDN) <= 15 && is_numeric($row->MSISDN)) {
                    return $row->MSISDN;
                }
                return '<span class="text-muted" title="' . e($row->MSISDN) . '">' . substr($row->MSISDN, 0, 16) . '...</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-xs btn-outline-primary btn-rehash" data-id="' . $row->id . '" title="Re-check hash matching"><i class="fas fa-sync-alt"></i></button>';
            })
            ->rawColumns(['match_status', 'msisdn_display', 'action'])
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

        // 2. Try existing hash match
        $mpesaPhone = MpesaPhone::where('phone_hash', $msisdn)->first();
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
            if (!MpesaPhone::where('phone_hash', $hash)->exists()) {
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
                if (!MpesaPhone::where('phone_hash', $hash)->exists()) {
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
            if (!MpesaPhone::where('phone_hash', $hash)->exists()) {
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
            if (!MpesaPhone::where('phone_hash', $hash)->exists()) {
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
            $mpesaPhone = MpesaPhone::where('phone_hash', $msisdn)->first();
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
}
