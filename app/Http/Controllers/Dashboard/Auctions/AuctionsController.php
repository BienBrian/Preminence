<?php

namespace App\Http\Controllers\Dashboard\Auctions;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Share;
use App\Models\ShareTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use DB;

class AuctionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware(['permission:View Auctions']);
    }

    public function index()
    {
        /*
        $shareTransactions = ShareTransaction::select('share_transactions.id', "share_transactions.user_id", "share_transactions.maturity_id",
            'auctions.status', 'share_transactions.created_at', 'auctions.share_id', DB::Raw('SUM(auctions.amount) as amount'))->join('auctions', 'auctions.share_transaction_id', '=', 'share_transactions.id')
            ->where('auctions.status', 'Confirmed')
            ->with(['user', 'maturity', 'share'])->groupBy('share_transactions.id', "share_transactions.user_id", "share_transactions.maturity_id",
                'auctions.status', 'share_transactions.created_at', 'auctions.share_id')->orderBy('created_at', 'DESC')->get();

        foreach ($shareTransactions as $shareTransaction) {
            $share = Share::where('share_transaction_id', $shareTransaction->id)
                ->where('seller_id', $shareTransaction->share->buyer_id)
                ->where('share_id', $shareTransaction->share_id)->first();
            if ($share == null) {
                $share = new Share;
                $share->status = "Pending";
            }
            $share->amount = $shareTransaction->amount;
            $share->balance = (100 + $shareTransaction->maturity->percentage) * $shareTransaction->amount / 100;
            $share->seller_id = $shareTransaction->share->buyer_id;
            $share->buyer_id = $shareTransaction->user_id;
            $share->maturity_id = $shareTransaction->maturity_id;
            $share->share_transaction_id = $shareTransaction->id;
            $share->share_id = $shareTransaction->share_id;
            if ($share->save()) {
                Share::where('id', $share->id)->update(['created_at' => $shareTransaction->created_at]);
            }
        }*/
        return view('dashboard.auctions.auctions');
    }
    public function getAuctions(Request $request)
    {
        $shareTransactions = ShareTransaction::select(
            'share_transactions.id',
            "share_transactions.user_id",
            "share_transactions.maturity_id",
            'auctions.status',
            'share_transactions.created_at',
            DB::Raw('SUM(auctions.amount) as amount')
        )->join('auctions', 'auctions.share_transaction_id', '=', 'share_transactions.id')
            ->with(['user', 'maturity']);
        if (!auth()->user()->can('View Auctions')) {
            $shareTransactions = $shareTransactions->where('share_transactions.user_id', auth()->user()->id);
        }
        $shareTransactions = $shareTransactions->whereHas('user', function ($query) use ($request) {
            $query->where('username', 'LIKE', '%' . $request->search . '%');
        });
        if ($request->status != "" && $request->status != "All") {
            $shareTransactions = $shareTransactions->where('auctions.status', $request->status);
        }
        if ($request->date != "") {
            $shareTransactions = $shareTransactions->where(DB::Raw('DATE(share_transactions.created_at)'), $request->date);
        }
        $shareTransactions = $shareTransactions->where('share_transactions.amount', '>', 0);
        $shareTransactions = $shareTransactions->groupBy(
            'share_transactions.id',
            "share_transactions.user_id",
            "share_transactions.maturity_id",
            'auctions.status',
            'share_transactions.created_at'
        )->orderBy('created_at', 'DESC');
        return DataTables::of($shareTransactions)
            ->editColumn('created_at', function ($row) use ($request) {
                return Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, y h:i A');
                //return Carbon::parse($row->created_at)->diffForHumans();
            })->editColumn('invoice', function ($row) {
                return sprintf("%04d", $row->id);
            })->editColumn('amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',');
            })->editColumn('returns', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount * (100 + $row->maturity->percentage) / 100, 2, '.', ',');
            })->addColumn('duration', function ($row) {
                return $row->maturity->number_of_days . ($row->maturity->number_of_days > 1 ? ' days' : ' day');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>';
                $actionBtn .= '<a href="' . url('dashboard/auctions/view/' . $row->id) . '" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> View</a>';
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function auction(Request $request)
    {
        $share_transaction = ShareTransaction::find($request->id);
        if ($share_transaction == null) {
            return redirect()->to('dashboard/home');
        }
        return view('dashboard.auctions.auction', @compact('share_transaction'));
    }
    public function getAuctionShares(Request $request)
    {
        $auctions = Auction::select(DB::Raw('sum(amount) as amount'), 'screenshot', 'share_transaction_id', 'user_id', 'maturity_id', 'share_id', 'status')
            ->where('share_transaction_id', $request->id)->with(['maturity', 'share.buyer', 'user'])
            ->groupBy('user_id', 'maturity_id', 'share_id', 'status', 'share_transaction_id', 'screenshot');
        return DataTables::of($auctions)
            ->addColumn('created_at', function ($row) use ($request) {
                $shareTransaction = ShareTransaction::select('created_at')->where('id', $row->share_transaction_id)->first();
                return Carbon::parse($shareTransaction->created_at)->setTimezone($request->timezone)->format('d M, y h:i A');
            })->editColumn('invoice', function ($row) {
                return sprintf("%04d", $row->share_transaction_id);
            })->editColumn('amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',');
            })->editColumn('returns', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount * (100 + $row->maturity->percentage) / 100, 2, '.', ',');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none share_id">' . $row->share_id . '</span>' .
                    '<span class="d-none user_id">' . $row->user_id . '</span>' .
                    '<span class="d-none buyer_id">' . $row->share->buyer->id . '</span>' .
                    '<span class="d-none buyer_username">' . $row->share->buyer->username . '</span>' .
                    '<span class="d-none seller_username">' . $row->user->username . '</span>' .
                    '<span class="d-none share_transaction_id">' . $row->share_transaction_id . '</span>' .
                    '<span class="d-none amount">' . $row->amount . '</span>' .
                    '<span class="d-none screenshot">' . $row->screenshot . '</span>' .
                    '<span class="d-none url">' . asset($row->screenshot != "" ? (file_exists(public_path("images/screenshots/" . $row->screenshot)) ? "images/screenshots/" . $row->screenshot : "images/mobile_payments.png") : "images/mobile_payments.png") . '</span>' .
                    '<span class="d-none status">' . $row->status . '</span>';
                if (auth()->user()->can("Edit Auctions") && ($row->status == 'Pending' || $row->status == 'Completed' || $row->status == "Disputed" || $row->status == "Reversed")) {
                    $actionBtn .= '<button class="btn-edit btn btn-primary btn-sm" data-toggle="modal" data-target="#routeModal"><i class="fas fa-edit"></i> Edit</button> ';
                } else {
                    if ($row->status == 'Pending') // 'Disputed', 'Rejected')
                        $actionBtn .= '<button class="btn btn-secondary btn-sm" disabled><i class="fas fa-history"></i> Pending</button> ';
                    else if ($row->status == 'Completed')
                        $actionBtn .= '<button class="btn btn-primary btn-sm" disabled><i class="fas fa-check"></i> Completed</button> ';
                    else if ($row->status == 'Confirmed')
                        $actionBtn .= '<button class="btn btn-success btn-sm" disabled><i class="fas fa-check-circle"></i> Confirmed</button> ';
                    else if ($row->status == 'Disputed')
                        $actionBtn .= '<button class="btn btn-warning btn-sm" disabled><i class="fas fa-ban"></i> Disputed</button> ';
                    else if ($row->status == 'Reversed')
                        $actionBtn .= '<button class="btn btn-danger btn-sm" disabled><i class="fas fa-close"></i> Reversed</button> ';
                }
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function updateAuctions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:shares,id',
            'user_id' => 'required|exists:users,id',
            'buyer_id' => 'required|exists:users,id',
            'share_transaction_id' => 'required|exists:share_transactions,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string',
            'screenshot' => 'required_if:status,=,"Completed"|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $auctions = Auction::where('user_id', $request->user_id)->where('share_id', $request->id)
            ->where('share_transaction_id', $request->share_transaction_id)->where('status', '<>', 'Confirmed')
            ->with('maturity')->get();
        $share = Share::find($request->id);
        $fileName = "";
        if ($request->status == 'Completed') {
            //upload screenshot;
            if ($request->has('screenshot')) {
                $image = $request->file('screenshot');
                $fileInfo = $image->getClientOriginalName();
                $filename = pathinfo($fileInfo, PATHINFO_FILENAME);
                $extension = pathinfo($fileInfo, PATHINFO_EXTENSION);
                $fileName = $filename . '-' . time() . '.' . $extension;
                if (!$image->move(public_path('images/screenshots'), $fileName)) {
                    return response()->json(['error' => 'Unable to upload screenshot'], 401);
                }
            } else {
                return response()->json(['error' => 'A payment screenshot is required to update payments with completed status'], 401);
            }
        }
        foreach ($auctions as $auction) {
            if ($request->status == 'Reversed') {
                //reverse the shares
                $share->bought = $share->bought - $auction->amount;
                if ($share->bought < $share->balance) {
                    $share->selling += $auction->amount;
                    $share->status = 'Activated';
                }
            }
            if ($auction->status == 'Reversed') {
                //Undo the reverse
                if (($share->balance - $share->bought) >= $auction->amount) {
                    $share->bought = $share->bought + $auction->amount;
                    if ($share->bought >= $share->balance) {
                        $share->selling -= $auction->amount;
                        $share->status = 'Completed';
                    }
                }
            }
            if ($request->status == 'Completed') {
                //remove previous screenshot if there is
                if ($auction->screenshot != "") {
                    if (file_exists(public_path("images/screenshots") . "/" . $auction->screenshot)) {
                        unlink(public_path('images/screenshots') . "/" . $auction->screenshot);
                    }
                }
                //update screenshot
                $auction->screenshot = $fileName;
                $auction->url = public_path('images/screenshots') . '/' . $fileName;
            }
            if ($request->status == 'Confirmed') {
                $mshare = Share::where('share_transaction_id', $request->share_transaction_id)
                    ->where('seller_id', $share->buyer_id)->where('share_id', $auction->share_id)->first();
                if ($mshare == null) {
                    $mshare = new Share;
                    $mshare->status = 'Pending';
                }
                $mshare->amount = $mshare->amount + $auction->amount;
                $mshare->balance = $mshare->balance + ceil($auction->amount * ((100 + $auction->maturity->percentage) / 100));
                $mshare->seller_id = $share->buyer_id;
                $mshare->buyer_id = $auction->user_id;
                $mshare->maturity_id = $auction->maturity->id;
                $mshare->share_transaction_id = $request->share_transaction_id;
                $mshare->share_id = $auction->share_id;
                $mshare->save();
            }
            $auction->status = $request->status;
            $auction->save();
            $share->save();
        }
        return response()->json(['success' => "Payment status updated successfully!"]);
    }
}
