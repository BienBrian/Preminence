<?php

namespace App\Http\Controllers\Dashboard\Settings;

use App\Http\Controllers\Controller;
use App\Models\Maturity;
use App\Models\TopupAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use DB;

class TopupAccountSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
        $this->middleware(['permission:View Topup Account Settings']);
    }
    public function index()
    {
        return view('dashboard.settings.topup_account_settings');
    }
    public function getTopupAccounts(Request $request)
    {
        return DataTables::of(TopupAccount::orderBy('created_at', 'DESC'))
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%')->orWhere('account_id', 'LIKE', '%' . $request->search . '%');
                });
            })->editColumn('status', function ($row) {
                return $row->status?"<span class='badge badge-primary'>Active</span>":"<span class='badge badge-secondary'>Active</span>";
            })->editColumn('created_at', function ($row) {
            return Carbon::parse($row->created_at)->diffForHumans();
        })->addColumn('action', function ($row) {
            $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                '<span class="d-none id">' . $row->id . '</span>' .
                '<span class="d-none number_of_days">' . $row->number_of_days . '</span>' .
                '<span class="d-none percentage">' . $row->percentage . '</span>' .
                '<span class="d-none status">' . $row->status . '</span>
                    <button class="btn-edit btn btn-primary btn-sm" data-toggle="modal" data-target="#userModal">'.
                    '<span class="d-none d-sm-block"><i class="fas fa-edit"></i> Edit</span><span class="d-block d-sm-none"><i class="fas fa-edit"></i></span></button> ';
            return $actionBtn;
        })->addIndexColumn()->escapeColumns([])->make();
    }
    public function addTopupAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'account_id' => 'required|string|unique:topup_accounts,account_id,' . $request->id,
            'name' => 'required|string',
            'status' => 'required|integer|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $topupAccount = new TopupAccount;
        if ($request->id > 0) {
            $topupAccount = TopupAccount::findOrFail($request->id);
        }
        $topupAccount->account_id = $request->account_id;
        $topupAccount->name = $request->name;
        $topupAccount->status = $request->status;
        if ($topupAccount->save()) {
            return response()->json(['success' => "Topup Account updated successfully!"]);
        } else {
            return response()->json(['error' => 'Unable to update Topup Account'], 401);
        }
    }
}
