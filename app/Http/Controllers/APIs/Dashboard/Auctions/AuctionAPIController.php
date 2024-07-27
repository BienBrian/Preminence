<?php

namespace App\Http\Controllers\APIs\Dashboard\Auctions;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAuctions(Request $request){
    $page = $request->has('page') ? intval($request->page) : 1;
        $page--;
        $offset = $page * 20;

        $auctions = Auction::with(['user', 'maturity', 'share', 'share_transaction']);
        if (!auth()->user()->can('View Auctions')) {
            $auctions = $auctions->where('share.user_id', auth()->user()->id);
        }
        $auctions = $auctions->whereHas('user', function ($query) use ($request) {
            $query->where('username', 'LIKE', '%' . $request->search . '%');
        });

        if (!auth()->user()->can('Add Shares')) {
            $auctions = $auctions->where(function ($query) {
                $query->where('hide', false)->orWhere('share_transactions.user_id', auth()->user()->id);
            });
        }
        $auctions = $auctions->orderBy('created_at', 'DESC')->skip($offset)->take(20)->get();
        return response()->json(['auctions'=>$auctions]);
    }
}
