<?php

namespace App\Http\Controllers\APIs\Dashboard\Shares;

use App\Http\Controllers\Dashboard\Emails\GlobalEmailController;
use App\Models\Auction;
use App\Models\Maturity;
use App\Models\MissedAuction;
use App\Models\Share;
use App\Models\ShareTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Auth;
use Validator;
class ShareAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getShares(Request $request){
    $page = $request->has('page') ? intval($request->page) : 1;
        $page--;
        $offset = $page * 20;

        $shares = ShareTransaction::select(
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
            $shares = $shares->where('share_transactions.user_id', auth()->user()->id);
        }
        $shares = $shares->whereHas('user', function ($query) use ($request) {
            $query->where('username', 'LIKE', '%' . $request->search . '%');
        });
        if ($request->status != "" && $request->status != "All") {
            $shares = $shares->where(DB::Raw('CASE WHEN share_transactions.status ="Activated" THEN share_transactions.status ELSE auctions.status END'), $request->status);
        }
        if ($request->date != "") {
            $shares = $shares->where(DB::Raw('DATE(share_transactions.created_at)'), $request->date);
        }

        $shares = $shares->where('share_transactions.amount', '>', 0);
        if (!auth()->user()->can('Add Shares')) {
            $shares = $shares->where(function ($query) {
                $query->where('hide', false)->orWhere('share_transactions.user_id', auth()->user()->id);
            });
        }
        $shares = $shares->groupBy(
            'share_transactions.id',
            "share_transactions.user_id",
            "share_transactions.maturity_id",
            'share_transactions.status',
            'auctions.status',
            'share_transactions.created_at'
        )->orderBy('created_at', 'DESC')->skip($offset)->take(20)->get();
        return response()->json(['shares'=>$shares]);
    }

    public function addShares(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'auction_time_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:1000|max:10000',
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
}
