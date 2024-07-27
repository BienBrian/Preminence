<?php

namespace App\Http\Controllers\Dashboard\Shares;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Emails\GlobalEmailController;
use App\Models\Auction;
use App\Models\Share;
use App\Models\ShareTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use DB;

class RunningSharesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        return view('dashboard.shares.running_shares');
    }


    public function getRunningShares(Request $request)
    {
        $shares = Share::with(['maturity', 'buyer']);
        if (!auth()->user()->can('View Running Shares')) {
            $shares->where('buyer_id', Auth::user()->id);
        }
        if(!auth()->user()->can('Add Shares')){
            $shares = $shares->where(function($query){
                $query->where('hide', false)->orWhere('buyer_id', auth()->user()->id);
            });
        }
        if ($request->date != "") {
            $shares->where(DB::Raw('DATE(created_at)'), $request->date);
        }
        if ($request->search != "") {
            $shares = $shares->whereHas('buyer', function ($query) use ($request) {
                $query->where('username', 'LIKE', '%' . $request->search . '%');
            });
        }
        //$shares->where('status', 'Activated');
        return DataTables::of($shares)
            ->filter(function ($query) use ($request) {

            })->editColumn('created_at', function ($row) use ($request) {
                return "<span class='no-wrap'>" . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, Y') . "</span> " .
                    "<span class='text-muted no-wrap'>" . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('h:i A');
            })->editColumn('amount', function ($row) {
                return '<span class="no-wrap"><i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',') . "</span>";
            })->editColumn('balance', function ($row) {
                return '<span class="no-wrap text-primary"><i class="fas fa-rupee-sign"></i> ' . number_format($row->balance, 2, '.', ',') . "</span>";
            })->editColumn('bought', function ($row) {
                return '<span class="no-wrap text-success"><i class="fas fa-rupee-sign"></i> ' . number_format($row->bought, 2, '.', ',') . "</span>";
            })->addColumn('days', function ($row) use ($request) {
                return "<span class='text-muted no-wrap'>" . Carbon::parse($row->created_at)->addDays($row->maturity->number_of_days)->setTimezone($request->timezone)->format('d M, y h:i A') . '</span><br><span class="countdown" data-start="' . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('Y-m-d H:i:s') . '"
                data-end="' . Carbon::parse($row->created_at)->addDays($row->maturity->number_of_days)->setTimezone($request->timezone)->format('Y-m-d H:i:s') . '"><i class="fas fa-spinner fa-pulse"></i> Loading...</span>';
                //return Carbon::parse($row->created_at)->addDays($row->maturity->number_of_days)->setTimezone($request->timezone)->format('d M, y h:i A');
            })->editColumn('invoice', function ($row) {
                return sprintf("%06d", $row->id);
            })->addColumn('action', function ($row) {
                /*$actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>';
                $actionBtn .= '<a href="' . url('dashboard/shares/running/view/' . $row->share_transaction_id) . '" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> View</a>'
                    . '</div>';
                return $actionBtn;*/
                return "";
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function runningShare(Request $request)
    {
        $share = ShareTransaction::where('id', $request->id);
        /*if (!auth()->user()->can('View Running Shares')) {
            $share = $share->where('buyer_id', auth()->user()->id);
        }*/
        $share = $share->first();
        if ($share == null) {
            return redirect()->to('dashboard/home');
        }
        return view('dashboard.shares.running_share', @compact('share'));
    }

    public function getAuctionShares(Request $request)
    {
        $auction = Auction::select(
            'auctions.screenshot',
            'auctions.url',
            'auctions.user_id',
            'auctions.status',
            'shares.buyer_id',
            'auctions.share_transaction_id',
            DB::Raw('SUM(auctions.amount) as amount')
        )->join('shares', 'shares.id', 'auctions.share_id')->join('users', 'users.id', 'shares.buyer_id')
        ->where('auctions.share_transaction_id', $request->id);
        if(!auth()->user()->can('View Running Shares') && !auth()->user()->can('View Sold Shares')){
            $auction = $auction->where('shares.buyer_id', auth()->user()->id);
        }
        $auction = $auction->with(['user'])
            ->groupBy('auctions.user_id', 'auctions.status', 'auctions.screenshot', 'auctions.url',
            'auctions.share_transaction_id','shares.buyer_id');
        return DataTables::of($auction)
            ->editColumn('created_at', function ($row) use ($request) {
                $shareTransaction = ShareTransaction::where('id', $row->share_transaction_id)->first();
                return Carbon::parse($shareTransaction->created_at)->setTimezone($request->timezone)->format('d M, y h:i A');
            })->editColumn('amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',');
            })->editColumn('phone', function ($row) {
                return '+91'.$row->user->phone;
            })->editColumn('user_id', function ($row) {
                return 'USR-' . sprintf("%03d", $row->user_id);
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                '<span class="d-none user_id">' . $row->user_id . '</span>' .
                '<span class="d-none buyer_id">' . $row->buyer_id . '</span>' .
                    '<span class="d-none url">' . asset($row->screenshot != "" ? (file_exists(public_path("images/screenshots/" . $row->screenshot)) ? "images/screenshots/" . $row->screenshot : "images/mobile_payments.png") : "images/mobile_payments.png") . '</span>' .
                    '<span class="d-none share_transaction_id">' . $row->share_transaction_id . '</span>';
                if ($row->buyer_id == auth()->user()->id && $row->status == 'Completed')
                    $actionBtn .= '<button class="btn btn-primary btn-edit btn-sm" data-toggle="modal" data-target="#routeModal"><i class="fas fa-money-check"></i> Update</button>';
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
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function confirmShares(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'id' => 'required|exists:shares,id',
            'share_transaction_id' => 'required|exists:share_transactions,id',
            'user_id' => 'required|exists:users,id',
            'buyer_id' => 'required|exists:users,id',
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        if (
            Auction::whereHas('share', function($query) use($request){
                $query->where('buyer_id', $request->buyer_id);
            })->where('user_id', $request->user_id)->where('share_transaction_id', $request->share_transaction_id)
                ->where('status', 'Completed')->update(['status' => $request->status, "updated_at" => Carbon::now()])
        ) {
            if ($request->status == 'Confirmed') {
                $auctions = Auction::select('auctions.maturity_id', 'auctions.user_id', 'shares.buyer_id', 'auctions.share_transaction_id',
                DB::Raw('SUM(auctions.amount) as total_amount'))->join('shares', 'shares.id', 'auctions.share_id')
                ->with(['maturity'])
                    ->where('shares.buyer_id', $request->buyer_id)
                    ->where('auctions.user_id', $request->user_id)
                    ->where('auctions.share_transaction_id', $request->share_transaction_id)
                    ->where('auctions.status', 'Confirmed')
                    ->groupBy('auctions.maturity_id', 'shares.buyer_id', 'auctions.share_transaction_id', 'auctions.user_id')->get();
                //\Log::info(json_encode($auctions));
                foreach ($auctions as $auction) {
                    $share = Share::where('share_transaction_id', $request->share_transaction_id)
                        ->where('seller_id', $auction->buyer_id)->where('buyer_id', $request->user_id)
                        ->first();
                    if ($share == null) {
                        $share = new Share;
                        $share->status = 'Pending';
                    }
                    $share->amount = $auction->total_amount;
                    $share->balance = ceil($auction->total_amount * ((100 + $auction->maturity->percentage) / 100));
                    $share->seller_id = $auction->buyer_id;
                    $share->buyer_id = $auction->user_id;
                    $share->maturity_id = $auction->maturity->id;
                    $share->share_transaction_id = $request->share_transaction_id;
                    //$share->share_id = $auction->share_id;
                    $share->save();
                    $share_transaction = ShareTransaction::find($request->share_transaction_id);
                    if ($share_transaction != null) {
                        Share::where('id', $share->id)->update(['created_at' => $share_transaction->created_at]);
                    }
                }
            }
            $globalEmaiController = new GlobalEmailController;
            //send email to buyer on their payment status
            $emailSubject = "Share Payments " . $request->status;
            $emailBody = "User (" . auth()->user()->username . ") has <b>" . $request->status . "</b> payments of INR " . number_format(Auction::where('share_id', $request->id)->where('user_id', $request->user_id)->where('share_transaction_id', $request->share_transaction_id)->sum('amount'), 2, '.', ',')
                . " invoice number <b> " . sprintf("%04d", $request->share_transaction_id) . "</b>.";
            if ($request->status != "Confirmed") {
                $emailBody .= " If you think the Seller has made a mistake in disputing the payments, raise a ticket with our team. ";
            }

            $emailBody .= "<a href='" . url('/dashboard/shares/bought/view/' . $request->share_transaction_id) . "'>Click here</a> to view payment status.";
            $globalEmaiController->sendMail($emailSubject, $emailBody, $request->user_id);
            return response()->json(['success' => "Payments updated successfully!"]);
        } else {
            return response()->json(['error' => "Unable to update payments!"], 401);
        }
    }
}
