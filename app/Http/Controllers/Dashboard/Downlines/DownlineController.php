<?php

namespace App\Http\Controllers\Dashboard\Downlines;

use App\Http\Controllers\Controller;
use App\Models\Downline;
use App\Models\Maturity;
use App\Models\Share;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use DB;

class DownlineController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }


    public function index()
    {
        return view('dashboard.downlines.downlines');
    }
    public function getDownlines(Request $request)
    {
        $downlines = User::select('users.id', "users.username", "users.referrer", "users.created_at", DB::Raw('SUM(downlines.commission) as commission'));
        if (!auth()->user()->can('View Downlines')) {
            $downlines = $downlines->where('users.referrer', Auth::user()->username);
        }
        $downlines = $downlines->leftJoin('downlines', 'downlines.user_id', 'users.id')
            ->groupBy('users.id', "users.username", "users.referrer", "users.created_at", )->orderBy('users.created_at', 'DESC');

        return DataTables::of($downlines)
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('username', 'LIKE', '%' . $request->search . '%')
                        ->orWhere(DB::Raw('CONCAT("USR-",LPAD(users.id,3,"0"))'), 'LIKE', '%' . $request->search . '%');
                });
            })->editColumn('user_id', function ($row) {
                return 'USR-' . sprintf("%03d", $row->id);
            })->editColumn('commission', function ($row) {
                return 'INR ' . number_format($row->commission, 2, '.', ',');
            })->editColumn('created_at', function ($row) use ($request) {
                return Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, Y h:i A');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<a href="' . url('dashboard/downlines/view/' . $row->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View</a> ';
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function viewDownline(Request $request)
    {
        $user = User::find($request->id);
        if ($user == null) {
            return redirect()->to('dashboard/home');
        }
        return view('dashboard.downlines.downline', @compact('user'));
    }
    public function getUserDownlines(Request $request)
    {
        $downlines = Downline::with('user', 'referrer')->where('user_id', $request->id);
        if (!auth()->user()->can('View Downlines')) {
            $downlines = $downlines->where('referrer_id', Auth::user()->id);
        }
        $downlines = $downlines->orderBy('created_at', 'DESC');

        return DataTables::of($downlines)
            /*->filter(function($query) use($request){
                $query->where(function($q) use($request){
                    $q->where('activity', 'LIKE', '%'.$request->search.'%');
                });
            })*/    ->editColumn('user_id', function ($row) {
                    return 'USR-' . sprintf("%03d", $row->id);
                })->editColumn('commission', function ($row) {
                    return 'INR ' . number_format($row->commission, 2, '.', ',');
                })->editColumn('created_at', function ($row) use ($request) {
                    return Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, Y h:i A');
                })->addColumn('action', function ($row) {
                    $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                        "<span class='d-none id'>" . $row->id . "</span>" .
                        "<span class='d-none amount'>" . $row->commission . "</span>";

                    if ($row->status == 'pending')
                        $actionBtn .= '<button class="btn btn-primary btn-sm btn-edit" data-toggle="modal" data-target="#routeModal" '.(auth()->user()->id == $row->referrer_id?'':'disabled').'><i class="fas fa-money-check"></i> Redeem</button> ';
                    $actionBtn .= '</div>';
                    return $actionBtn;
                })->addIndexColumn()->escapeColumns([])->make();
    }

    public function redeemDownline(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:downlines,id',
            'maturity' => 'required|exists:maturities,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $downline = Downline::find($request->id);
        $maturity = Maturity::find($request->maturity);
        if ($downline->status == 'pending') {
            $share = new Share;
            $share->amount = $downline->commission;
            $share->balance = ceil($downline->commission * ((100 + $maturity->percentage) / 100));
            $share->seller_id = $downline->user_id;
            $share->buyer_id = $downline->referrer_id;
            $share->maturity_id = $maturity->id;
            $share->status = 'Pending';
            if ($share->save()) {
                $downline->status = 'redeemed';
                $downline->save();
                return response()->json(['success' => "Shares redeemed successfully!"]);
            } else {
                return response()->json(['error' => "Unable to redeem shares!"], 401);
            }
        } else {
            return response()->json(['error' => "Share already redeemed!"], 401);
        }
    }
}
