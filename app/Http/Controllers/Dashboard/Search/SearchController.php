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
        $users = User::select('id', 'firstname', 'lastname', 'email')
            ->where('firstname', 'LIKE', '%' . $request->q . '%')
            ->orWhere('lastname', 'LIKE', '%' . $request->q . '%')
            ->orWhere('email', 'LIKE', '%' . $request->q . '%')
            ->orderBy('firstname', 'asc')
            ->get();

        return response()->json($users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->firstname . ' ' . $user->lastname,
                'email' => $user->email
            ];
        }));
    }
}
