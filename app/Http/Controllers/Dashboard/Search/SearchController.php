<?php

namespace App\Http\Controllers\Dashboard\Search;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Day;
use App\Models\Maturity;
use App\Models\TimeZone;
use App\Models\UPI;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class SearchController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    }

    public function searchRoles(Request $request)
    {
        return json_encode(Role::where('name', 'LIKE', '%' . $request->q . '%')->where('name', '<>', 'Super Admin')
            ->orderBy('name', 'asc')->get());
    }

    public function searchUsers(Request $request)
    {
        return json_encode(User::select('id', DB::Raw('CONCAT(name, " ", "(", email,")") as name'))
            ->where('name', 'LIKE', '%' . $request->q . '%')->orWhere('email', 'LIKE', '%' . $request->q . '%')
            ->orderBy('name', 'asc')->get());
    }
    public function searchTimezones(Request $request)
    {
        return json_encode(TimeZone::where('name', 'LIKE', '%' . $request->q . '%')
            ->orderBy('name', 'asc')->get());
    }
    public function searchMaturities(Request $request)
    {
        return json_encode(Maturity::select('id', DB::Raw('CONCAT(number_of_days, " day(s)") as name'))
            ->where(DB::Raw('CONCAT(number_of_days, " day(s)")'), 'LIKE', '%' . $request->q . '%')
            ->orderBy('number_of_days', 'asc')->get());
    }

    public function searchDays(Request $request)
    {
        return json_encode(Day::select('value as id', 'name')->where('name', 'LIKE', '%' . $request->q . '%')
        ->orderBy('value', 'asc')->get());
    }
    public function searchPaymentMethods(Request $request)
    {
        if($request->payment_method == 'upi'){
            return json_encode(UPI::select('u_p_i_s.id', DB::Raw('CONCAT(u_p_i_s.upi_id," | ",u_p_i_s.upi_phone, "|", users.username) as name'))
            ->join('users', 'users.id', 'u_p_i_s.user_id')->where('u_p_i_s.user_id', $request->user_id)->where('u_p_i_s.status', true)
            ->where(function($query) use($request){
                $query->where(DB::Raw('CONCAT(u_p_i_s.upi_id," | ",u_p_i_s.upi_phone, "|", users.username)'), 'LIKE', '%' . $request->q . '%');
            })->orderBy('u_p_i_s.upi_phone', 'asc')->get());
        }else{
            return json_encode(Bank::select('banks.id', DB::Raw('CONCAT(banks.account_number," | ",banks.account_holder_name, "|", banks.bank_name, "|", banks.ifsc,"|", users.username) as name'))
            ->join('users', 'users.id', 'banks.user_id')->where('banks.user_id', $request->user_id)
            ->where('banks.status', true)
            ->where(function($query) use($request){
                $query->where(DB::Raw('CONCAT(banks.account_number," | ",banks.account_holder_name, "|", banks.bank_name, "|", banks.ifsc,"|", users.username)'), 'LIKE', '%' . $request->q . '%');
            })->orderBy('banks.account_number', 'asc')->get());
        }
    }
}
