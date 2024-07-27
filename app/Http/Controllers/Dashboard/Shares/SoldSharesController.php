<?php

namespace App\Http\Controllers\Dashboard\Shares;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Share;
use App\Models\ShareTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use DB;

class SoldSharesController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    }
    public function index()
    {
        return view('dashboard.shares.sold_shares');
    }
    public function getSoldShares(Request $request)
    {
        $auctions = Auction::select('auctions.user_id', 'auctions.status','auctions.share_transaction_id', DB::Raw('sum(auctions.amount) as amount'),
        'users.username', 'shares.buyer_id')
        ->join('shares', 'shares.id', 'auctions.share_id')->join('users', 'users.id', 'shares.buyer_id')->with(['user']);
        if (!auth()->user()->can('View Sold Shares')) {
            $auctions = $auctions->where('shares.buyer_id', auth()->user()->id);
        }

        if(!auth()->user()->can('Add Shares')){
            $auctions = $auctions->where(function($query){
                $query->where('shares.hide', false)->orWhere('shares.buyer_id', auth()->user()->id);
            });
        }
        if($request->search != ""){
            $auctions = $auctions->where(function($query) use($request){
                $query->where('username', 'LIKE', '%'.$request->search.'%')
                ->orWhereHas('user', function($query) use($request){
                    $query->where('username', 'LIKE', '%'.$request->search.'%');
                });
            });
        }
        if($request->date != ""){
            $auctions = $auctions->where(DB::Raw('DATE(auctions.created_at)'), $request->date);
        }
        $auctions = $auctions/*->where(function($query){
            $query->where('status', 'Completed')->orWhere('status', 'Confirmed');
        })*/->orderBy('auctions.share_transaction_id', 'DESC')->groupBy('auctions.user_id', 'auctions.status',
        'auctions.share_transaction_id', 'users.username', 'shares.buyer_id');
        return DataTables::of($auctions)
            ->filter(function ($query) use ($request) {

            })->editColumn('created_at', function ($row) use ($request) {
                $shareTransaction = ShareTransaction::where('id', $row->share_transaction_id)->first();
                return Carbon::parse($shareTransaction->created_at)->setTimezone($request->timezone)->format('d M, y h:i A');
                //return Carbon::parse($row->created_at)->diffForHumans();
            })->editColumn('amount', function ($row) {
                return '<i class="fas fa-rupee-sign"></i> ' . number_format($row->amount, 2, '.', ',');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>';
                if (auth()->user()->can('Edit Queues'))
                    $actionBtn .= '<button class="btn-edit btn btn-primary btn-sm" data-toggle="modal" data-target="#routeModal"><i class="fas fa-edit"></i> Edit</button> ';
                $actionBtn .= '<a href="' . url('/dashboard/shares/sold/view/' . $row->share_transaction_id) . '" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i> View</a>'
                    . '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function soldShare(Request $request)
    {
        $share = ShareTransaction::with('maturity')->where('id', $request->id);
        /*if(!auth()->user()->can('View Sold Shares')){
            $share = $share->where('buyer_id', auth()->user()->id);
        }*/

        $share = $share->first();
        if ($share == null) {
            return redirect()->to('dashboard/home');
        }
        return view('dashboard.shares.sold_share', @compact('share'));
    }
}
