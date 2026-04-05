<?php

namespace App\Http\Controllers\APIs\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionTime;
use App\Models\Downline;
use App\Models\GeneralSetting;
use App\Models\Share;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HomeAPIController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getDashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timezone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $timezone = $request->timezone ?: 'Asia/Kolkata';
        if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            return response()->json(['errors' => ['timezone' => 'Invalid timezone provided']], 400);
        }

        $current_date = Carbon::now()->setTimezone($timezone)->format('Y-m-d');

        $auction_time = AuctionTime::selectRaw(
                'auction_times.id, time_zones.name, ' .
                'CASE WHEN CONVERT_TZ(CONCAT(?, " ",to_time), utc_offset, "+00:00") >= UTC_TIMESTAMP() ' .
                'THEN CONCAT(?, " ",from_time) ELSE DATE_ADD(CONCAT(?, " ",from_time), INTERVAL 1 DAY) END as start_time, ' .
                'CASE WHEN CONVERT_TZ(CONCAT(?, " ",to_time), utc_offset, "+00:00") >= UTC_TIMESTAMP() ' .
                'THEN CONCAT(?, " ",to_time) ELSE DATE_ADD(CONCAT(?, " ",to_time), INTERVAL 1 DAY) END as end_time',
                [$current_date, $current_date, $current_date, $current_date, $current_date, $current_date]
            )
            ->join('time_zones', 'time_zones.id', 'auction_times.time_zone_id')
            ->where(function ($query) use ($current_date) {
                $query->whereRaw(
                    'CASE WHEN CONVERT_TZ(CONCAT(?, " ",from_time), utc_offset, "+00:00") >= UTC_TIMESTAMP() ' .
                    'THEN CONVERT_TZ(CONCAT(?, " ",from_time), utc_offset, "+00:00") ' .
                    'ELSE DATE_ADD(CONVERT_TZ(CONCAT(?, " ",from_time), utc_offset, "+00:00"), INTERVAL 1 DAY) END >= UTC_TIMESTAMP()',
                    [$current_date, $current_date, $current_date]
                )->orWhereRaw(
                    'CASE WHEN CONVERT_TZ(CONCAT(?, " ",to_time), utc_offset, "+00:00") >= UTC_TIMESTAMP() ' .
                    'THEN CONVERT_TZ(CONCAT(?, " ",from_time), utc_offset, "+00:00") ' .
                    'ELSE DATE_ADD(CONVERT_TZ(CONCAT(?, " ",from_time), utc_offset, "+00:00"), INTERVAL 1 DAY) END <= UTC_TIMESTAMP()',
                    [$current_date, $current_date, $current_date]
                );
            })
            ->orderBy('start_time', 'ASC')
            ->first();

        $generalSettings = GeneralSetting::first();
        $shares = $generalSettings != null ? ($generalSettings->show_auction_shares ? Share::select(DB::Raw('SUM(selling) as amount'))->where('shares.status', 'Activated')->first() : 0) : 0;
        $returns = Share::where('buyer_id', auth()->user()->id)->sum('balance');
        $invested = Share::where('buyer_id', auth()->user()->id)->sum('amount');
        $downlines = User::where('referrer', auth()->user()->username)->count();
        $earnings = Downline::where('referrer_id', auth()->user()->id)->sum('commission');

        $bids = Auction::select(DB::raw('SUM(amount) as totals'),
            DB::raw('YEAR(created_at) year, MONTH(created_at) month'));
        if (!auth()->user()->can('View Bids')) {
            $bids = $bids->where('user_id', auth()->user()->id);
        }
        if (auth()->user()->can('View Bought Shares')) {
            $returns = Share::sum('balance');
            $invested = Share::sum('amount');
            $downlines = User::count();
        } else {
            $returns = Share::where('buyer_id', auth()->user()->id)->sum('balance');
            $invested = Share::where('buyer_id', auth()->user()->id)->sum('amount');
            $downlines = User::where('referrer', auth()->user()->username)->count();
        }
        $bids = $bids->whereBetween('created_at', [now()->startOfYear(), now()])
            ->groupby(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))->get()->toJson();
            $start_time = "";
            $end_time = "";
            if($auction_time != null){
                $start_time = Carbon::parse($auction_time->start_time, $auction_time->name)->setTimezone("UTC")->format('Y-m-d H:i:s');
                $end_time = Carbon::parse($auction_time->end_time, $auction_time->name)->setTimezone("UTC")->format('Y-m-d H:i:s');
            }

        return response()->json(['auction_time' => $auction_time, 'shares' => number_format($shares != null ? $shares->amount : 0, 0, '.', ','),
            'invested' => number_format($invested, 0, '.', ','), 'returns' => number_format($returns, 0, '.', ','),
            'downlines' => number_format($downlines, 0, '.', ','), 'earnings' => number_format($earnings, 0, '.', ','),
            'bids' => $bids, 'start_time'=>$start_time, 'end_time'=>$end_time
        ]);
    }
}
