<?php

namespace App\Http\Controllers\Dashboard\Pairings;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\ShareTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use DB;

class PairingsController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    public function index()
    {
        return view('dashboard.pairings.pairings');
    }

    public function getSharePairings(Request $request)
    {
        $pairings = Auction::select(DB::Raw('sum(amount) as amount'), 'share_transaction_id', 'user_id', 'maturity_id', 'share_id', 'status')
        ->with(['maturity', 'share.buyer', 'user'])
        ->groupBy('user_id', 'maturity_id', 'share_id', 'status', 'share_transaction_id')->skip(0)->take(100);
        /*
        if ($request->date != "") {
            $shares->where(DB::Raw('DATE(created_at)'), $request->date);
        }
        if ($request->search != "") {
            $auctions = $auctions->whereHas('buyer', function ($query) use ($request) {
                $query->where('username', 'LIKE', '%' . $request->search . '%');
            });
        }
        $auctions = $auctions->orderBy('created_at', 'ASC');*/
        return DataTables::of($pairings)
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
            })->addColumn('days', function ($row) use ($request) {
                return $row->maturity->number_of_days.' '.($row->maturity->number_of_days>1?'days':'day');
            })->editColumn('invoice', function ($row) {
                return sprintf("%06d", $row->id);
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none share_id">' . $row->share_id . '</span>' .
                    '<span class="d-none buyer_id">' . $row->share->buyer->id . '</span>' .
                    '<span class="d-none buyer_username">' . $row->share->buyer->username . '</span>' .
                    '<span class="d-none buyer_email">' . $row->share->buyer->email . '</span>' .
                    '<span class="d-none buyer_phone">' . $row->share->buyer->phone . '</span>' .
                    '<span class="d-none share_transaction_id">' . $row->share_transaction_id . '</span>' .
                    '<span class="d-none amount">' . $row->amount . '</span>' .
                    '<span class="d-none damount">' . number_format($row->amount, 2, '.', ',') . '</span>';
                if (auth()->user()->id == $row->user_id && $row->status == 'Pending')
                    $actionBtn .= '<button class="btn-edit btn btn-primary btn-sm" data-toggle="modal" data-target="#routeModal"><i class="fas fa-money-check"></i> Pay</button> ';
                else if ($row->status == 'Pending') // 'Disputed', 'Rejected')
                    $actionBtn .= '<button class="btn-edit btn btn-secondary btn-sm" disabled><i class="fas fa-history"></i> Pending</button> ';
                else if ($row->status == 'Completed')
                    $actionBtn .= '<button class="btn-edit btn btn-primary btn-sm" disabled><i class="fas fa-check"></i> Completed</button> ';
                else if ($row->status == 'Confirmed')
                    $actionBtn .= '<button class="btn-edit btn btn-success btn-sm" disabled><i class="fas fa-check-circle"></i> Confirmed</button> ';
                else if ($row->status == 'Disputed')
                    $actionBtn .= '<button class="btn-edit btn btn-warning btn-sm" disabled><i class="fas fa-ban"></i> Disputed</button> ';
                else if ($row->status == 'Rejected')
                    $actionBtn .= '<button class="btn-edit btn btn-danger btn-sm" disabled><i class="fas fa-close"></i> Rejected</button> ';

                $actionBtn .= "<button class='btn btn-outline-primary btn-sm'><i class='fas fa-image'></i></button>";
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }
}
