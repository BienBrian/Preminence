<?php

namespace App\Http\Controllers\Dashboard\Shares;

use App\Http\Controllers\Controller;
use App\Models\MissedAuction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use DB;

class MissedSharesController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        return view('dashboard.shares.missed_shares');
    }
    public function getMissedShares(Request $request)
    {
        $missedAuction = MissedAuction::with(['user', 'auction_time.timezone'])
        ->where(DB::raw('DATE(created_at)'), Carbon::parse($request->date)->format('Y-m-d'));
        if(!auth()->user()->can('View Missed Shares')){
            $missedAuction->where('user_id',auth()->user()->id);
        }
        $missedAuction = $missedAuction->whereHas('user', function ($q) use ($request) {
            $q->where('username', 'LIKE', '%' . $request->search . '%');
        });
        return DataTables::of($missedAuction->get())
            ->editColumn('created_at', function ($row) use ($request) {
                return Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, Y h:i A');
            })->editColumn('missed_amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->missed_amount, 2, '.', ',');
            })->editColumn('bought_amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->bought_amount, 2, '.', ',');
            })->addIndexColumn()->escapeColumns([])->make();
    }
}
