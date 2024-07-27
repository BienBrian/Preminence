<?php

namespace App\Http\Controllers\Dashboard\PaymentSettings;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\UPI;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PaymentSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    public function index()
    {
        return view('dashboard.payment_settings.bank_payments_settings');
    }
    public function getBanks(Request $request)
    {
        $banks = Bank::with(['user']) /*
->where(DB::Raw('DATE(created_at)'), Carbon::parse($request->date)->format('Y-m-d'))*/;

        if (!auth()->user()->can('View Bank Settings')) {
            $banks = $banks->where('user_id', auth()->user()->id);
        }
        if ($request->date != "") {
            $banks = $banks->where(DB::Raw('DATE(created_at)'), $request->date);
        }
        $banks = $banks->orderBy('id', 'DESC');
        return DataTables::of($banks)
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('account_number', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('bank_name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('ifsc', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('account_holder_name', 'LIKE', '%' . $request->search . '%')
                        ->orWhereHas('user', function ($query) use ($request) {
                            $query->where('username', 'LIKE', '%' . $request->search . '%');
                        });
                });
            })->addColumn('status', function ($row) use ($request) {
                return $row->status ? "<span class='badge bg-primary'>Active</span>" : "<span class='badge bg-secondary'>Inactive</span>";
            })->addColumn('created_at', function ($row) use ($request) {
                return \Carbon\Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, y h:i A');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>' .
                    '<span class="d-none user_id">' . $row->user_id . '</span>' .
                    '<span class="d-none account_holder_name">' . $row->account_holder_name . '</span>' .
                    '<span class="d-none bank_name">' . $row->bank_name . '</span>' .
                    '<span class="d-none account_number">' . $row->account_number . '</span>' .
                    '<span class="d-none ifsc">' . $row->ifsc . '</span>' .
                    '<span class="d-none status">' . $row->status . '</span>';
                $actionBtn .= '<btn class="btn btn-primary btn-sm btn-edit" data-toggle="modal" data-target="#userModal"' . (auth()->user()->can("Edit Bank Settings") || $row->user_id == auth()->user()->id ? "" : "disabled") . '><i class="fas fa-edit"></i> Edit</button> ';
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function addBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer|min:0",
            "user_id" => "required|exists:users,id",
            "account_number" => "required|string",
            "account_holder_name" => "required|string",
            "bank_name" => "required|string",
            "ifsc" => "required|string",
            "status" => "required|integer|min:0|max:1"
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        if (
            Bank::where('account_number', $request->account_number)->where('bank_name', $request->bank_name)
                ->where('user_id', $request->user_id)->where('id', '<>', $request->id)->count() > 0
        ) {
            return response()->json(["error" => 'Unable to Account Number already exists'], 400);
        }

        $bank = new Bank;
        if ($request->id > 0) {
            $bank = Bank::find($request->id);
        }
        $bank->user_id = $request->user_id;
        $bank->account_number = $request->account_number;
        $bank->account_holder_name = $request->account_holder_name;
        $bank->bank_name = $request->bank_name;
        $bank->ifsc = $request->ifsc;
        $bank->status = $request->status;
        if ($bank->save()) {
            return response()->json(["success" => 'Bank details successfully']);
        } else {
            return response()->json(["error" => 'Unable to add bank detailsl'], 400);
        }
    }

    public function upi()
    {
        return view('dashboard.payment_settings.upi_payments_settings');
    }
    public function getUPIs(Request $request)
    {
        $upis = UPI::with(['user'])
            /*->where(DB::Raw('DATE(created_at)'), Carbon::parse($request->date)->format('Y-m-d'))*/;
        if (!auth()->user()->can('View UPI Settings')) {
            $upis = $upis->where('user_id', auth()->user()->id);
        }
        if ($request->date != "") {
            $upis = $upis->where(DB::Raw('DATE(created_at)'), $request->date);
        }
        $upis = $upis->orderBy('id', 'DESC');
        return DataTables::of($upis)
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where(DB::Raw('CONCAT("+91",upi_phone)'), 'LIKE', '%' . $request->search . '%')
                        ->orWhere('upi_id', 'LIKE', '%' . $request->search . '%')->orWhereHas('user', function ($query) use ($request) {
                            $query->where('username', 'LIKE', '%' . $request->search . '%');
                        });
                });
            })->addColumn('upi_phone', function ($row) use ($request) {
                return "+91" . $row->upi_phone;
            })->addColumn('status', function ($row) use ($request) {
                return $row->status ? "<span class='badge bg-primary'>Active</span>" : "<span class='badge bg-secondary'>Inactive</span>";
            })->addColumn('created_at', function ($row) use ($request) {
                return \Carbon\Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, y h:i A');
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>' .
                    '<span class="d-none user_id">' . $row->user_id . '</span>' .
                    '<span class="d-none upi_id">' . $row->upi_id . '</span>' .
                    '<span class="d-none upi_phone">' . $row->upi_phone . '</span>' .
                    '<span class="d-none status">' . $row->status . '</span>' .
                    '<btn class="btn btn-primary btn-sm btn-edit" data-toggle="modal" data-target="#userModal"><i class="fas fa-edit"></i> Edit</button> ';
                $actionBtn .= '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }

    public function addUPI(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer|min:0",
            "user_id" => "required|exists:users,id",
            "upi_phone" => "required|digits:10",
            "upi_id" => "required|string",
            "status" => "required|integer|min:0|max:1"
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        if (
            UPI::where('upi_id', $request->upi_id)->where('upi_phone', $request->upi_phone)
                ->where('user_id', $request->user_id)->where('id', '<>', $request->id)->count() > 0
        ) {
            return response()->json(["error" => 'Unable to UPI ID already exists'], 400);
        }

        $upi = new UPI;
        if ($request->id > 0) {
            $upi = UPI::find($request->id);
        }
        $upi->user_id = $request->user_id;
        $upi->upi_id = $request->upi_id;
        $upi->upi_phone = $request->upi_phone;
        $upi->status = $request->status;
        if ($upi->save()) {
            return response()->json(["success" => 'UPI details successfully']);
        } else {
            return response()->json(["error" => 'Unable to add UPI details'], 400);
        }
    }
}
