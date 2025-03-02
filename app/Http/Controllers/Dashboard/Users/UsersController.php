<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Mail\MyEmail;
use App\Models\Email;
use App\Models\EmailRecipient;
use App\Models\Maturity;
use App\Models\Share;
use App\Models\ShareTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mail;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use DB;

class UsersController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Users']);
    }
    public function index()
    {

        $users = User::doesntHave('roles')->get();
        $role = Role::where('name', 'User')->first();
        foreach($users as $user){
            $user->assignRole($role);
        }
        return view('dashboard.users.users');
    }
    public function getUsers(Request $request)
    {
        /*
        $users = User::without('roles')->get();
        foreach($users as $user){
            $roles = \DB::table("userroles")->where('role', $user->role)->first();
            if($roles != null){
                $user->syncRoles([$roles->name]);
            }
        }*/
        $users = User::with('roles');
        if (auth()->user()->roles[0]->name != "Super Admin") {
            $users = $users->whereHas('roles', function ($query) {
                $query->where('name', '<>', 'Super Admin');
            });
        }
        return DataTables::of($users->orderBy('firstname', 'ASC'))
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where(DB::Raw('CONCAT(firstname, " ", lastname)'), 'LIKE', $request->search . '%')
                        ->orWhere('email', 'LIKE', $request->search . '%')
                        ->orWhere('phone', 'LIKE', $request->search . '%');
                });
                if ($request->status != "") {
                    $query->where('status', $request->status);
                }
                if ($request->role > 0) {
                    $query->whereHas('roles', function ($q) use ($request) {
                        $q->where('id', $request->role);
                    });
                }
            })->addColumn('name', function ($row) use ($request) {
                return "<div class='user-panel d-flex'>
                            <div class='image'>
                                <img src='".($row->image == "" ? asset('profile_images/default.jpg'): asset('profile_images/'.$row->image))."'
                                    class='img-circle'> ".$row->firstname." ".$row->lastname."
                            </div>
                        </div>";
            })->editColumn('created_at', function ($row) use ($request) {
                return Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, Y');
            })->addColumn('status', function ($row) {
                return $row->status ? "<span class='badge bg-primary'>Active</span>" : "<span class='badge bg-danger'>In-Active</span>";
            })->addColumn('role', function ($row) {
                return $row->roles->count() > 0 ? $row->roles[0]->name : "-";
            })->addColumn('action', function ($row) {
                $actionBtn = '<div style="white-space: nowrap;" class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>' .
                    '<span class="d-none firstname">' . $row->firstname . '</span>' .
                    '<span class="d-none lastname">' . $row->lastname . '</span>' .
                    '<span class="d-none role_id">' . ($row->roles->count() > 0 ? $row->roles[0]->id : 0) . '</span>' .
                    '<span class="d-none role_name">' . ($row->roles->count() > 0 ? $row->roles[0]->name : "") . '</span>' .
                    '<span class="d-none middlename">' . $row->middlename . '</span>' .
                    '<span class="d-none email">' . $row->email . '</span>' .
                    '<span class="d-none phone">' . $row->phone . '</span>' .
                    '<span class="d-none status">' . $row->status . '</span>';
                if ($row->roles->count() > 0)
                    if ($row->roles[0]->name != 'Super Admin' && $row->id != auth()->user()->id)
                        $actionBtn .= '<button class="btn-edit btn btn-primary btn-sm" data-toggle="modal" data-target="#userModal">' .
                            '<span class="d-none d-sm-block"><i class="fas fa-edit"></i> Edit</span> <span class="d-block d-sm-none"><i class="fas fa-edit"></i></span></button> ';
                $actionBtn .= '<a href="' . url('dashboard/users/view/' . $row->id) . '" class="btn btn-outline-primary btn-sm"><span class="d-none d-sm-block">' .
                    '<i class="fas fa-eye"></i> View</span><span class="d-block d-sm-none"><i class="fas fa-eye"></i></span></a>'
                    . '</div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }
    public function addUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|unique:users,email,' . $request->id,
            'phone' => 'required|digits:12|unique:users,phone,' . $request->id,
            'role' => 'required|exists:roles,id',
            'status' => 'required|min:0|max:1|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        //$statusChanged = false;
        //$approvalChanged = false;
        $user = new User;
        if ($request->id > 0) {
            $user = User::find($request->id);
            /*if ($user->status != $request->status) {
                $statusChanged = true;
            }
            if ($user->approval_status != $request->approval_status) {
                $approvalChanged = true;
            }*/
        } else {
            $user->password = Hash::make('12345');
        }
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;
        if ($user->save()) {
            $role = Role::find($request->role);
            if ($role != null) {
                $user->syncRoles([$role->name]);
            }
            return response()->json(['success' => 'User updated successfully!']);
        } else {
            return response()->json(['error' => 'Unable to update user'], 401);
        }
    }

    public function viewUser(Request $request)
    {
        $user = User::find($request->id);
        if ($user == null) {
            return redirect()->to('home');
        }
        $roles = Role::where("name", "<>", "Super Admin")->get();
        $scontact = DB::table("scontacts")->where("user_id", $request->id)->first();
        $communities = DB::table('communities')->orderBy("name", "ASC")->get();
        $departments = DB::table('departments')->orderBy("name", "ASC")->get();

        $emergency = DB::table("emergency_contact")->where("user_id", $request->id)->leftJoin("users", "users.id", "emergency_contact.user_id2")->first();
        $user = DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "contacts.phone", "contacts.gender", "contacts.marital",
            "church.communities", "church.departments", "church.joined as yearjoined", "church.gifts", "church.remarks", "church.previous_church", "church.position",
            "contacts.country", "contacts.dob", "contacts.joined", "profiles.name", "families.name as familyname", "families.relationship", "families.image", "families.email as familyemail",
            "families.phone as familyphone", "professions.occupation", "professions.specific", "professions.institution", "professions.from", "professions.to", "education.level", "education.institution as inst",
            "education.certification", "education.from as fr", "education.to as t")->where("users.id", $request->id)->leftJoin("profiles", "profiles.user_id", "=", "users.id")->
            leftJoin("contacts", "contacts.user_id", "=", "users.id")->leftJoin("church", "church.user_id", "users.id")->
            leftJoin("families", "families.user_id", "=", "users.id")->leftJoin("professions", "professions.user_id", "=", "users.id")
            ->leftJoin("education", "education.user_id", "=", "users.id")->first();
        return view("dashboard.users.user")->with("roles", $roles)->with("user", $user)->with("scontact", $scontact)->with("emergency", $emergency)
        ->with("communities", $communities)->with("departments", $departments);
    }

}
