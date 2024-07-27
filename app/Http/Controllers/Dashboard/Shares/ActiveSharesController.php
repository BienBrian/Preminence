<?php

namespace App\Http\Controllers\Dashboard\Shares;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionTime;
use App\Models\Share;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ActiveSharesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware(['permission:View Active Shares']);
    }
    public function index()
    {
        /*
        $activeShares = Share::whereIn('status', ['Pending', 'Activated'])->whereNotNull('share_transaction_id')
        ->whereNotNull('share_id')->orderBy('id', 'asc')->get();
        foreach($activeShares as $activeShare){
            $auction = Auction::where('share_id', $activeShare->share_id)->where('share_transaction_id', $activeShare->share_transaction_id)->first();
            Share::where('id', $activeShare->id)->update(['created_at'=>$auction->updated_at]);
        }*/
        return view('dashboard.shares.active_shares');
    }
    public function getActiveShares(Request $request)
    {
        $shares = Share::with(['maturity', 'buyer', 'seller']);
        if (!auth()->user()->can('View Running Shares')) {
            $shares->where('buyer_id', Auth::user()->id);
        }
        /*
        if ($request->date != "") {
            $shares->where(DB::Raw('DATE(created_at)'), $request->date);
        }*/
        if ($request->search != "") {
            $shares = $shares->whereHas('buyer', function ($query) use ($request) {
                $query->where('username', 'LIKE', '%' . $request->search . '%');
            });
        }
        $shares->whereIn('status', ['Activated', 'Pending']);
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
                $actionBtn .= '<a href="' . url('dashboard/shares/running/view/' . $row->id) . '" class="btn btn-outline-primary btn-sm"><span class="d-none d-sm-block"><i class="fas fa-eye"></i> View</span><span class="d-sm-none d-block"><i class="fas fa-eye"></i></span></a>'
                    . '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function getActiveSharesStatistics(Request $request)
    {/*
        $current_time = Carbon::now()->setTimezone($request->timezone != "" ? $request->timezone : 'Asia/Kolkata')->format('Y-m-d H:i:s');
        $current_date = Carbon::now()->setTimezone($request->timezone != "" ? $request->timezone : 'Asia/Kolkata')->format('Y-m-d');

        $auction_time = AuctionTime::select(
            'auction_times.id',
            'time_zones.name',
            DB::Raw('CASE WHEN CONCAT("' . $current_date . '", " ",to_time) >= "' . $current_time . '" THEN CONCAT("' . $current_date . '", " ",from_time) ELSE DATE_ADD(CONCAT("' . $current_date . '", " ",from_time), INTERVAL 1 DAY) END as start_time'),
            DB::Raw('CASE WHEN CONCAT("' . $current_date . '", " ",to_time) >= "' . $current_time . '" THEN CONCAT("' . $current_date . '", " ",to_time) ELSE DATE_ADD(CONCAT("' . $current_date . '", " ",to_time), INTERVAL 1 DAY) END as end_time'),
        )
            ->join('time_zones', 'time_zones.id', 'auction_times.time_zone_id')
            ->where(function ($query) use ($current_date, $current_time) {
                $query->where(DB::Raw('CASE WHEN CONCAT("' . $current_date . '", " ",to_time) >= "' . $current_time . '" THEN CONCAT("' . $current_date . '", " ",from_time) ELSE DATE_ADD(CONCAT("' . $current_date . '", " ",from_time), INTERVAL 1 DAY) END'), '>=', $current_time)
                    ->orWhere(DB::Raw('CASE WHEN CONCAT("' . $current_date . '", " ",to_time) >= "' . $current_time . '" THEN CONCAT("' . $current_date . '", " ",from_time) ELSE DATE_ADD(CONCAT("' . $current_date . '", " ",from_time), INTERVAL 1 DAY) END'), '<=', $current_time);
            })->orderBy('start_time', 'ASC')->first();
        $next_auction_shares = 0;
        if ($auction_time != null) {
            $search_date = Carbon::parse($auction_time->start_time)
                ->subDays(4)->format('Y-m-d H:i:s');
            $shares = Share::where('created_at', '<=', $search_date)->where('status', 'Pending');
            $next_auction_shares = $shares->sum('balance') - $shares->sum('bought');
        }*/

        $sellingShares = Share::whereIn('status', ['Activated'])->sum('selling');
        $runningShares = Share::whereIn('status', ['Activated'])->sum('balance');
        $boughtShares = Share::whereIn('status', ['Activated'])->sum('bought');
        $pendingShares = Share::where('status', 'Pending')->sum('balance') - Share::where('status', 'Pending')->sum('bought');
        //$original_date = Carbon::parse($auction_time->start_time)->format('Y-m-d H:i:s');
        return response()->json([
            'running_shares' => number_format($runningShares, 2, '.', ','),
            'bought_shares' => number_format($boughtShares, 2, '.', ','),
            'pending_shares' => number_format($pendingShares, 2, '.', ','),
            'selling_shares' => number_format($sellingShares, 2, '.', ','),
            //'next_auction_shares' => number_format($next_auction_shares, 2, '.', ','),
            //'next_auction_time' => Carbon::parse($original_date, $auction_time->name)->setTimezone($request->timezone != "" ? $request->timezone : 'Asia/Kolkata')->format('Y-m-d H:i:s'),
        ]);
    }
    public function editShare(Request $request)
    {
        if (auth()->user()->can('Edit Active Shares')) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:shares,id',
                'amount' => 'required|numeric|min:0',
                'bought'=>'required|numeric|min:0',
                'selling'=>'required|numeric|min:0',
                'buyer' => 'required|string',
                'seller' => 'required|string',
                'status' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()], 400);
            }
            if ($request->status == 'Pending' || $request->status == 'Activated') {
                $share = Share::find($request->id);
                if(($share->balance-$share->bought) >= $request->selling){
                    $share->selling = $request->selling;
                }else{
                    return response()->json(['error' => 'Selling amount <b>'.$request->selling.'</b> is greator than <b>'.($share->balance-$share->bought).'</b> available for selling'], 401);
                }
                $share->status = $request->status;
                if ($share->save()) {
                    return response()->json(['success' => "Shares updated successfully!"]);
                } else {
                    return response()->json(['error' => 'Unable to update share'], 401);
                }
            } else {
                return response()->json(['error' => 'Invalid share status'], 401);
            }
        } else {
            return response()->json(['error' => 'You do not have permission to edit active shares'], 401);
        }
    }

}
