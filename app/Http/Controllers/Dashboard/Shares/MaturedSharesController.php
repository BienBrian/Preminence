<?php

namespace App\Http\Controllers\Dashboard\Shares;

use App\Http\Controllers\Controller;
use App\Models\Maturity;
use App\Models\Share;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use DB;

class MaturedSharesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware(['permission:View Matured Shares']);
    }

    public function index()
    {
        return view('dashboard.shares.matured_shares');
    }
    public function getMaturedShares(Request $request)
    {
        $shares = Share::select('shares.*', 'maturities.number_of_days',
        DB::Raw('DATE_ADD(CONVERT_TZ(shares.created_at, "+00:00","+00:00"), INTERVAL maturities.number_of_days DAY) as maturity_date'))
        ->join('maturities', 'maturities.id', 'shares.maturity_id')
        ->with(['maturity', 'buyer', 'seller']);
        if (!auth()->user()->can('View Matured Shares')) {
            $shares->where('buyer_id', Auth::user()->id);
        }
        if ($request->status != "") {
            if ($request->status != "All") {
                $shares->where('shares.status', $request->status);
            }
        }
        if ($request->search != "") {
            $shares = $shares->whereHas('buyer', function ($query) use ($request) {
                $query->where('username', 'LIKE', '%' . $request->search . '%');
            });
        }
        $shares = $shares->whereIn('shares.status', ['Activated', 'Pending'])
        ->where(DB::Raw('DATE_ADD(CONVERT_TZ(shares.created_at, "+00:00","+00:00"), INTERVAL maturities.number_of_days DAY)'), '<=', Carbon::now());

        return DataTables::of($shares)
            ->filter(function ($query) use ($request) {

            })->editColumn('created_at', function ($row) use ($request) {
                return "<span class='no-wrap'>" . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, Y') . "</span> " .
                    "<span class='text-muted no-wrap'>" . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('h:i A');
            })->editColumn('amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',');
            })->editColumn('balance', function ($row) {
                return '<span class="text-primary"><i class="fas fa-rupee-sign"></i> ' . number_format($row->balance, 2, '.', ',') . "</span>";
            })->editColumn('bought', function ($row) {
                return '<span class="text-success"><i class="fas fa-rupee-sign"></i> ' . number_format($row->bought, 2, '.', ',') . "</span>";
            })->editColumn('selling', function ($row) {
                return '<span class="text-info"><i class="fas fa-rupee-sign"></i> ' . number_format($row->selling, 2, '.', ',') . "</span>";
            })->addColumn('days', function ($row) use ($request) {
                return "<span class='text-muted no-wrap'>" . Carbon::parse($row->created_at)->addDays($row->maturity->number_of_days)->setTimezone($request->timezone)->format('d M, y h:i A') . '</span><br><span class="countdown" data-start="' . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('Y-m-d H:i:s') . '"
                data-end="' . Carbon::parse($row->created_at)->addDays($row->maturity->number_of_days)->setTimezone($request->timezone)->format('Y-m-d H:i:s') . '"><i class="fas fa-spinner fa-pulse"></i> Loading...</span>';
                //return Carbon::parse($row->created_at)->addDays($row->maturity->number_of_days)->setTimezone($request->timezone)->format('d M, y h:i A');
            })->editColumn('invoice', function ($row) {
                return sprintf("%06d", $row->id);
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>' .
                    '<span class="d-none buyer">' . $row->buyer->username . '</span>' .
                    '<span class="d-none seller">' . $row->seller->username . '</span>' .
                    '<span class="d-none status">' . $row->status . '</span>' .
                    '<span class="d-none balance">' . $row->balance . '</span>'.
                    '<span class="d-none selling_amount">' . $row->selling . '</span>'.
                    '<span class="d-none bought_amount">' . $row->bought . '</span>';
                if (auth()->user()->can('Edit Active Shares'))
                $actionBtn .= "<button class='btn btn-primary btn-sm btn-edit' data-toggle='modal' data-target='#routeModal'><span class='d-none d-sm-block'><i class='fas fa-edit'></i> Edit</span> <span class='d-sm-none d-block'><i class='fas fa-edit'></i></span></button> ";
            else
                $actionBtn .= "<button class='btn btn-primary btn-sm btn-edit' data-toggle='modal' data-target='#routeModal' disabled><span class='d-none d-sm-block'><i class='fas fa-edit'></i> Edit</span> <span class='d-sm-none d-block'><i class='fas fa-edit'></i></span></button> ";
                /*$actionBtn .= '<a href="' . url('dashboard/shares/running/view/' . $row->id) . '" class="btn btn-outline-primary btn-sm"><span class="d-none d-sm-block"><i class="fas fa-eye"></i> View</span><span class="d-sm-none d-block"><i class="fas fa-eye"></i></span></a>'
                    . '</div>';*/
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function getMaturedSharesCard(Request $request){
        $shares = Share::select(DB::raw('sum(balance)-sum(bought) as matured'), DB::raw('sum(selling) as selling'))
        ->join('maturities', 'maturities.id', 'shares.maturity_id');
        if (!auth()->user()->can('View Matured Shares')) {
            $shares->where('buyer_id', Auth::user()->id);
        }
        if ($request->status != "") {
            if ($request->status != "All") {
                $shares->where('shares.status', $request->status);
            }
        }
        if ($request->search != "") {
            $shares = $shares->whereHas('buyer', function ($query) use ($request) {
                $query->where('username', 'LIKE', '%' . $request->search . '%');
            });
        }
        $shares = $shares->whereIn('shares.status', ['Activated', 'Pending'])
        ->where(DB::Raw('DATE_ADD(CONVERT_TZ(shares.created_at, "+00:00","+00:00"), INTERVAL maturities.number_of_days DAY)'), '<=', Carbon::now())
        ->first();
        return response()->json(['shares'=>$shares]);
    }
}
