<?php

namespace App\Http\Controllers\Dashboard\Shares;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Emails\GlobalEmailController;
use App\Models\Auction;
use App\Models\AuctionTime;
use App\Models\Bank;
use App\Models\Maturity;
use App\Models\MissedAuction;
use App\Models\Share;
use App\Models\ShareTransaction;
use App\Models\UPI;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class BoughtSharesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {
        return view('dashboard.shares.bought_shares');
    }
    public function buyShares()
    {

        $auction = Auction::where('user_id', auth()->user()->id)->latest()->first();
        return view('dashboard.shares.buy_shares', @compact('auction'));
    }
    public function addShares(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auction_time_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:500|max:1000',
            'maturity' => 'required|exists:maturities,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $maturity = Maturity::find($request->maturity);
        $globalEmaiController = new GlobalEmailController;

        $shareTransaction = new ShareTransaction;
        $shareTransaction->user_id = auth()->user()->id;
        $shareTransaction->maturity_id = $request->maturity;
        $shareTransaction->amount = $request->amount;
        $shareTransaction->balance = $request->amount * ($maturity->percentage + 100) / 100;
        $shareTransaction->status = "Pending";
        if ($shareTransaction->save()) {
            $limit = 5;
            //500-1000 (2)//1000-2000(3)//2000-3000(4)
            if ($request->amount >= 500 && $request->amount <= 1000) {
                $limit = 2;
            } else if ($request->amount > 1000 && $request->amount <= 2000) {
                $limit = 3;
            } else if ($request->amount > 2000 && $request->amount <= 3000) {
                $limit = 4;
            }
            $amount = $request->amount;
            $sharesAvailable = true;
            $chunkAmount = floor($amount / $limit);
            $balanceChunk = $amount - ($chunkAmount * $limit);
            $count = 0;
            while ($amount > 0 && $sharesAvailable) {
                $thisAuctionIds = Auction::where('auctions.share_transaction_id', $shareTransaction->id)
                    ->join('shares', 'shares.id', 'auctions.share_id')->groupBy('shares.buyer_id')->pluck('shares.buyer_id');

                $shareBuyersId = Share::select('buyer_id', DB::Raw('sum(selling) as selling'))
                    ->where('buyer_id', '<>', auth()->user()->id);
                if (count($thisAuctionIds) > 0) {
                    $shareBuyersId = $shareBuyersId->whereNotIn('buyer_id', $thisAuctionIds);
                }
                $shareBuyersId = $shareBuyersId->groupBy('buyer_id')->orderBy('selling', 'DESC')
                    ->skip(0)->take($limit)->pluck('buyer_id');

                $shares = Share::where('status', 'Activated')->where('buyer_id', '<>', auth()->user()->id)
                    ->where('selling', '>', 0);
                if (count($thisAuctionIds) > 0) {
                    $shares = $shares->whereNotIn('buyer_id', $thisAuctionIds);
                }
                if (count($shareBuyersId) > 0) {
                    $shares = $shares->whereIn('buyer_id', $shareBuyersId);
                }
                $shares = $shares   /*->withSum('auction', 'amount')*/->orderBy('share_id', 'ASC')->orderBy('selling', 'DESC')
                    ->skip(0)->take($limit)->get();

                if ($shares->count() <= 0) {
                    $shares = Share::where('status', 'Activated')->where('buyer_id', '<>', auth()->user()->id)
                        ->where('selling', '>', 0);

                if (count($shareBuyersId) > 0) {
                    $shares = $shares->whereIn('buyer_id', $shareBuyersId);
                }
                    $shares = $shares/*->withSum('auction', 'amount')*/    ->orderBy('share_id', 'ASC')->orderBy('selling', 'DESC')
                        ->skip(0)->take($limit)->get();
                }
                if ($shares->count() <= 0) {
                    $sharesAvailable = false;
                    $missedAuction = new MissedAuction;
                    $missedAuction->user_id = Auth::user()->id;
                    $missedAuction->auction_time_id = $request->auction_time_id;
                    $missedAuction->bought_amount = $request->amount - $amount;
                    $missedAuction->missed_amount = $amount;
                    $missedAuction->save();
                    $securedAmount = Auction::where('share_transaction_id', $shareTransaction->id)->sum('amount');

                    $emailSubject = "Bought Shares";
                    $emailBody = "";
                    if ($securedAmount > 0) {
                        $emailBody = "You have <b>secured</b> in bidding <b>INR " . number_format($securedAmount, 2, '.', ',') . "</b> shares out of INR " . number_format($request->amount, 2, '.', ',')
                            . " bidded. The Invoice Number is <b>" . sprintf("%04d", $shareTransaction->id) . "</b>. <a href='" . url('dashboard/shares/bought/view/' . $shareTransaction->id) . "'>Click here</a> to finalize on the payments.";
                    } else {
                        $emailSubject = "Share Bidding Unsuccessful";
                        $emailBody = "You have <b>NOT</b> succeeded in the purchase of <b>INR " . number_format($request->amount, 2, '.', ',')
                            . "</b> worth of shares. Check out our next auction. Best luck next time.";
                    }
                    $shareTransaction->amount = $securedAmount;
                    $shareTransaction->balance = $securedAmount * (100 + $maturity->percentage) / 100;
                    $shareTransaction->save();
                    $globalEmaiController->sendMail($emailSubject, $emailBody, Auth::user()->id);

                    return response()->json(['error' => 'Purchase of full amount failed! You\'ve secured INR ' . number_format($request->amount - $amount, 2, '.', ',') . '!'], 401);
                }

                foreach ($shares as $share) {
                    //\Log::info('buyer_id:' . $share->buyer_id . ', user_id' . auth()->user()->id);
                    $pairshares = Share::where('buyer_id', $share->buyer_id)->where('selling', '>', 0)
                        ->where('status', 'Activated')->where('buyer_id', '<>', auth()->user()->id)->get();
                    $theChunk = 0;
                    $theChunkBalance = $chunkAmount + $balanceChunk;
                    foreach ($pairshares as $pairshare) {
                        if ($amount > 0) {
                            if ($theChunk < ($chunkAmount + $balanceChunk)) {
                                if ($pairshare->selling <= $theChunkBalance) {
                                    $theChunkBalance -= $pairshare->selling;
                                    $balanceChunk = 0;
                                    $theChunk += $pairshare->selling;
                                    $selling = $pairshare->selling;
                                    $amount -= $selling;
                                    if ($amount < 0) {
                                        //extra shares
                                        $theChunk += $amount;
                                        $selling += $amount;
                                        $amount = 0;
                                    }
                                    $pairshare->bought += $selling;
                                    $pairshare->selling -= $selling;
                                    if ($pairshare->save()) {
                                        $auction = new Auction;
                                        $auction->user_id = auth()->user()->id;
                                        $auction->share_id = $pairshare->id;
                                        $auction->maturity_id = $request->maturity;
                                        $auction->amount = $selling;
                                        $auction->share_transaction_id = $shareTransaction->id;
                                        $auction->status = "Pending";
                                        $auction->save();
                                        if ($pairshare->bought >= $pairshare->balance) {
                                            $pairshare->status = 'Completed';
                                            $pairshare->save();
                                        }
                                    }
                                } else {
                                    $remaining = $pairshare->selling - $theChunkBalance;
                                    $selling = $theChunkBalance;
                                    $amount -= $selling;
                                    $theChunk += $theChunkBalance;
                                    $theChunkBalance = 0;
                                    $balanceChunk = 0;

                                    $pairshare->bought += $selling;
                                    $pairshare->selling = $remaining;
                                    if ($pairshare->save()) {
                                        $auction = new Auction;
                                        $auction->user_id = auth()->user()->id;
                                        $auction->share_id = $pairshare->id;
                                        $auction->maturity_id = $request->maturity;
                                        $auction->amount = $selling;
                                        $auction->share_transaction_id = $shareTransaction->id;
                                        $auction->status = "Pending";
                                        $auction->save();
                                        if ($pairshare->bought >= $pairshare->balance) {
                                            $pairshare->status = 'Completed';
                                            $pairshare->save();
                                        }
                                    }
                                }
                            } else {
                                $balanceChunk = 0;
                            }
                        } else {
                            $sharesAvailable = false;
                            if($amount < 0){
                                $toremove = $amount * -1;
                                $auctionToRemove = Auction::where('share_transaction_id', $shareTransaction->id)
                                ->where('amount', '>=', $toremove)->orderBy('id', 'DESC')->first();
                                $auctionToRemove->amount -= $toremove;
                                if($auctionToRemove->save()){
                                    $shareToRemove = Share::find($auctionToRemove->share_id);
                                    $shareToRemove->selling += $toremove;
                                    $shareToRemove->status='Activated';
                                    $shareToRemove->save();
                                    $amount = 0;
                                }
                            }
                            break;
                        }
                    }
                    if ($amount <= 0) {
                        $sharesAvailable = false;
                        break;
                    }/*
                    if($amount < 0){
                        $toremove = $amount * -1;
                        $auctionToRemove = Auction::where('share_transaction_id', $shareTransaction->id)
                        ->where('amount', '>=', $toremove)->orderBy('id', 'DESC')->first();
                        $auctionToRemove->amount -= $toremove;
                        if($auctionToRemove->save()){
                            $shareToRemove = Share::find($auctionToRemove->share_id);
                            $shareToRemove->selling += $toremove;
                            $shareToRemove->status='Activated';
                            $shareToRemove->save();
                            $amount = 0;
                        }
                    }*/

                    //\Log::info('the Chunk: ' . $theChunk . ', theChunkBalance:' . $theChunkBalance . 'Amount:' . $amount . ', balanceChunk:' . $balanceChunk);
                    /*return;
                    $shareAmount = $share->selling;//$share->balance - $share->bought;//$share->auction_sum_amount;
                    $amountToSave = 0;
                    if ($shareAmount <= 0) {
                        if(($share->balance-$share->bought)<=0){
                            $share->status = 'Completed';
                        }else{
                            $share->selling = 0;
                        }
                        $share->save();
                        continue;
                    } else {
                        if ($shareAmount <= $chunkAmount) {
                            $amountToSave = $shareAmount;
                            $balanceChunk += $chunkAmount - $shareAmount;
                            //$share->status = 'Completed';
                            //$share->save();
                        } else if ($shareAmount >= ($balanceChunk + $chunkAmount)) {
                            $amountToSave = $balanceChunk + $chunkAmount;
                            $balanceChunk = 0;
                        } else {
                            $amountToSave = $shareAmount;
                        }
                    }

                    if ($amount <= $amountToSave) {
                        $amountToSave = $amount;
                    }
                    $amount -= $amountToSave;
                    if ($amount <= 0) {
                        $sharesAvailable = false;
                    }
                    if ($amountToSave <= 0) {
                        break;
                    }
                    $auction = new Auction;
                    $auction->user_id = auth()->user()->id;
                    $auction->share_id = $share->id;
                    $auction->maturity_id = $request->maturity;
                    $auction->amount = $amountToSave;
                    $auction->share_transaction_id = $shareTransaction->id;
                    $auction->status = "Pending";
                    if ($auction->save()) {
                        $updateBought = Auction::where('share_id', $share->id)->where('status', '<>', 'Reversed')
                        ->sum('amount');
                        if ($updateBought > 0) {
                            $share->bought = $updateBought;
                            $share->selling -= $amountToSave;
                            $share->save();
                        }
                    }*/
                }
            }
            //shares buying successful
            $emailSubject = "Share Bidding Successful";
            $emailBody = "Your bid for INR " . number_format($request->amount, 2, '.', ',')
                . " worth of shares is <b>SUCCESSFUL</b>. The Invoice Number is <b>" . sprintf("%04d", $shareTransaction->id) . "</b>. <a href='" . url('dashboard/shares/bought/view/' . $shareTransaction->id) . "'>Click here</a> to complete payments.";
            $globalEmaiController->sendMail($emailSubject, $emailBody, Auth::user()->id);

            Auth::logout();
            return response()->json(['success' => "Shares bought successfully!"]);
        } else {
            return response()->json(['error' => 'Unable to buy create share invoice'], 401);
        }
    }
    public function getBoughtShares(Request $request)
    {
        $shareTransactions = ShareTransaction::select(
            'share_transactions.id',
            "share_transactions.user_id",
            "share_transactions.maturity_id",
            'share_transactions.status as sstatus',
            'auctions.status',
            'share_transactions.created_at',
            DB::Raw('CASE WHEN share_transactions.status = "Activated" THEN SUM(share_transactions.amount) ELSE SUM(auctions.amount) END as amount')
        )->leftJoin('auctions', 'auctions.share_transaction_id', '=', 'share_transactions.id')
            ->with(['user', 'maturity']);
        if (!auth()->user()->can('View Bought Shares')) {
            $shareTransactions = $shareTransactions->where('share_transactions.user_id', auth()->user()->id);
        }
        $shareTransactions = $shareTransactions->whereHas('user', function ($query) use ($request) {
            $query->where('username', 'LIKE', '%' . $request->search . '%');
        });
        if ($request->status != "" && $request->status != "All") {
            $shareTransactions = $shareTransactions->where(DB::Raw('CASE WHEN share_transactions.status ="Activated" THEN share_transactions.status ELSE auctions.status END'), $request->status);
        }
        if ($request->date != "") {
            $shareTransactions = $shareTransactions->where(DB::Raw('DATE(share_transactions.created_at)'), $request->date);
        }

        $shareTransactions = $shareTransactions->where('share_transactions.amount', '>', 0);
        if (!auth()->user()->can('Add Shares')) {
            $shareTransactions = $shareTransactions->where(function ($query) {
                $query->where('hide', false)->orWhere('share_transactions.user_id', auth()->user()->id);
            });
        }
        $shareTransactions = $shareTransactions->groupBy(
            'share_transactions.id',
            "share_transactions.user_id",
            "share_transactions.maturity_id",
            'share_transactions.status',
            'auctions.status',
            'share_transactions.created_at'
        )->orderBy('created_at', 'DESC');
        return DataTables::of($shareTransactions)
            ->editColumn('created_at', function ($row) use ($request) {
                return "<span class='no-wrap'>" . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, y') . "</span> " .
                    "<span class='no-wrap'>" . Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('h:i A') . "</span>";
                //return Carbon::parse($row->created_at)->diffForHumans();
            })->editColumn('invoice', function ($row) {
                return sprintf("%04d", $row->id);
            })->editColumn('amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',');
            })->editColumn('returns', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format(($row->status != null ? $row->amount * (100 + $row->maturity->percentage) / 100 : $row->amount), 2, '.', ',');
            })->editColumn('status', function ($row) {
                return $row->status != null ? $row->status : $row->sstatus;
            })->addColumn('duration', function ($row) {
                return $row->maturity->number_of_days . ($row->maturity->number_of_days > 1 ? ' days' : ' day');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>';
                $actionBtn .= '<a href="' . url('dashboard/shares/bought/view/' . $row->id) . '" class="btn btn-outline-primary btn-sm"><span class="d-none d-sm-block"><i class="fas fa-eye"></i> View</span> <span class="d-block d-sm-none"><i class="fas fa-eye"></i></span></a>';
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function boughtShare(Request $request)
    {
        $share_transaction = ShareTransaction::where('id', $request->id);
        if (!auth()->user()->can('View Bought Shares')) {
            $share_transaction = $share_transaction->where('user_id', auth()->user()->id);
        }
        $share_transaction = $share_transaction->first();
        if ($share_transaction == null) {
            return redirect()->to('dashboard/home');
        }
        return view('dashboard.shares.bought_share', @compact('share_transaction'));
    }


    public function getAuctionShares(Request $request)
    {
        $auctions = Auction::select(
            DB::Raw('sum(auctions.amount) as amount'),
            'auctions.share_transaction_id',
            'auctions.user_id',
            'auctions.maturity_id',
            'auctions.status',
            'shares.buyer_id',
            'users.username',
            'users.phone',
            'users.email',
            'users.name'
        )
            ->join('shares', 'shares.id', 'auctions.share_id')->join('users', 'users.id', 'shares.buyer_id')
            ->where('auctions.share_transaction_id', $request->id);
        if (!auth()->user()->can('View Bought Shares')) {
            $auctions = $auctions->where('auctions.user_id', auth()->user()->id);
        }

        $auctions = $auctions->with(['maturity']);

        $auctions = $auctions->groupBy(
            'auctions.user_id',
            'auctions.maturity_id',
            'auctions.status',
            'auctions.share_transaction_id',
            'shares.buyer_id',
            'users.username',
            'users.phone',
            'users.email',
            'users.name'
        );
        return DataTables::of($auctions)
            ->addColumn('created_at', function ($row) use ($request) {
                $shareTransaction = ShareTransaction::select('created_at')->where('id', $row->share_transaction_id)->first();
                return Carbon::parse($shareTransaction->created_at)->setTimezone($request->timezone)->format('d M, y h:i A');
            })->editColumn('invoice', function ($row) {
                return sprintf("%04d", $row->share_transaction_id);
            })->editColumn('phone', function ($row) {
                return '+91' . $row->phone;
            })->editColumn('amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',');
            })->editColumn('returns', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount * (100 + $row->maturity->percentage) / 100, 2, '.', ',');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    /*'<span class="d-none share_id">' . $row->share_id.  '</span>' .*/
                    '<span class="d-none buyer_id">' . $row->buyer_id . '</span>' .
                    '<span class="d-none buyer_username">' . $row->username . '</span>' .
                    '<span class="d-none buyer_email">' . $row->email . '</span>' .
                    '<span class="d-none buyer_phone">' . $row->phone . '</span>' .
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
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function payShares(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'id' => 'required|exists:shares,id',
            'user_id' => 'required|exists:users,id',
            'buyer_id' => 'required|exists:users,id',
            'share_transaction_id' => 'required|exists:share_transactions,id',
            'amount' => 'required|numeric|min:0',
            'my_payment_method' => 'required|integer',
            'payment_method' => 'required|string',
            'screenshot' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        /*$path = $request->file('screenshot')->store(path: 'images/screenshots', options: 's3');
        Storage::disk(name: 's3')->setVisibility($path, 'public');*/
        $image = $request->file('screenshot');
        $fileInfo = $image->getClientOriginalName();
        $filename = pathinfo($fileInfo, PATHINFO_FILENAME);
        $extension = pathinfo($fileInfo, PATHINFO_EXTENSION);
        $fileName = $filename . '-' . time() . '.' . $extension;
        if ($image->move(public_path('images/screenshots'), $fileName)) {
            if (
                Auction::whereHas('share', function ($query) use ($request) {
                    $query->where('buyer_id', $request->buyer_id);
                })->where('user_id', $request->user_id)
                    ->where('auctions.share_transaction_id', $request->share_transaction_id)
                    ->update([
                        'status' => 'Completed',
                        "screenshot" => $fileName,
                        "url" => public_path("images/screenshots") . '/' . $fileName,
                        "updated_at" => Carbon::now()
                    ])
            ) {
                $globalEmaiController = new GlobalEmailController;
                $auctions = Auction::select(
                    DB::Raw('sum("auctions.amount") as totals'),
                    'auctions.share_transaction_id',
                    'auctions.status'
                )->join('shares', 'shares.id', 'auctions.share_id')
                    ->whereHas('share', function ($query) use ($request) {
                        $query->where('buyer_id', $request->buyer_id);
                    })->where('user_id', $request->user_id)
                    ->where('auctions.share_transaction_id', $request->share_transaction_id)
                    ->groupBy('shares.buyer_id', 'auctions.user_id', 'auctions.share_transaction_id', 'auctions.status')
                    ->get();
                foreach ($auctions as $auction) {
                    $shareTransaction = ShareTransaction::find($auction->share_transaction_id);
                    $shareTransaction->status = $auction->status;
                    $shareTransaction->save();
                }
                //send mail to sellers
                $payment_details = "";
                if ($request->payment_method == 'upi') {
                    $upi = UPI::find($request->my_payment_method);
                    if ($upi != null) {
                        $payment_details = "<b>UPI ID:</b> " . $upi->upi_id . ", <b>UPI PHONE:</b> " . $upi->upi_phone;
                    }
                } else {
                    $bank = Bank::find($request->my_payment_method);
                    if ($bank != null) {
                        $payment_details = "<b>Account Number:</b> " . $bank->account_number . ", <b>Account Holder Name:</b> " . $bank->account_holder_name .
                            ", <b>Bank name:</b> " . $bank->bank_name . ", <b>IFSC: </b>" . $bank->ifsc;
                    }
                }
                $emailSubject = "Share Payments from " . auth()->user()->username;
                $emailBody = "User(" . auth()->user()->username . ") has made payments of INR " . number_format($request->amount, 2, '.', ',')
                    . " to the following account " . $payment_details . ". <a href='" . url('/dashboard/shares/sold/view/' . $request->id) . "'>Click here</a> to confirm payments.";
                $globalEmaiController->sendMail($emailSubject, $emailBody, $request->buyer_id);

                return response()->json(['success' => "Payments updated successfully!"]);
            } else {
                unlink(public_path('images/screenshots') . "/" . $fileName);
                return response()->json(['error' => "Unable to update payments!"], 401);
            }
        } else {
            return response()->json(['error' => "Unable to update screenshot!"], 401);
        }
        /*
        $image = $request->file('screenshot');
        $fileInfo = $image->getClientOriginalName();
        $filename = pathinfo($fileInfo, PATHINFO_FILENAME);
        $extension = pathinfo($fileInfo, PATHINFO_EXTENSION);
        $fileName = $filename . '-' . time() . '.' . $extension;
        if ($image->move(public_path('images/screenshots'), $fileName)) {
            if (
                Auction::where('share_id', $request->id)->where('user_id', $request->user_id)
                    ->update(['status' => 'Completed', "screenshot" => $fileName, "updated_at" => Carbon::now()])
            ) {
                return response()->json(['success' => "Payments updated successfully!"]);
            } else {
                unlink(public_path('images/screenshots') . "/" . $fileName);
                return response()->json(['error' => "Unable to update payments!"], 401);
            }
        } else {
            return response()->json(['error' => "Unable to update screenshot!"], 401);
        }*/
    }

}
