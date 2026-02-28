<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class RolesController extends DashboardController
{
    //
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Roles']);
    }
    public function index()
    {
        return view('dashboard.users.roles');
    }

    public function getRoles(Request $request)
    {
        return DataTables::of(Role::orderBy('name', 'ASC'))
            ->filter(function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            })->editColumn('created_at', function ($row) {
            return Carbon::parse($row->created_at)->diffForHumans();
        })->addColumn('users', function ($row) {
            return number_format(User::whereHas('roles', function ($query) use ($row) {
                $query->where('id', $row->id);
            })->count(), 0, ',', '.');
        })->addColumn('action', function ($row) {
            $actionBtn = '<div style="white-space: nowrap;" class="text-end text-right">' .
                '<span class="d-none id">' . $row->id . '</span>' .
                '<span class="d-none name">' . $row->name . '</span>';
            if (auth()->user()->can('Edit Roles') && $row->name != 'Super Admin')
                $actionBtn .= '<button class="btn-edit btn btn-primary btn-sm" data-toggle="modal" data-target="#userModal">'.
            '<span class="d-none d-sm-block"><i class="fas fa-edit"></i> Edit</span><span class="d-block d-sm-none"><i class="fas fa-edit"></i></span></button> ';
            $actionBtn .= '<a href="' . url('dashboard/users/roles/view/' . $row->id) . '" class="btn btn-outline-primary btn-sm">'.
            '<span class="d-none d-sm-block"><i class="fas fa-eye"></i> View</span> <span class="d-block d-sm-none"><i class="fas fa-eye"></i></span></a>'
                . '</div>';
            return $actionBtn;
        })->addIndexColumn()->escapeColumns([])->make();
    }
    public function addRole(Request $request)
    {
        if (auth()->user()->can("Edit Roles") || auth()->user()->can("Add Roles")) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:0',
                'name' => 'required|string|unique:roles,name,' . $request->id,
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()], 400);
            }
            $role = new Role;
            if ($request->id > 0) {
                $role = Role::findOrFail($request->id);
            }
            $role->name = $request->name;
            if ($role->save()) {
                return response()->json(['success' => 'Role created successfully!']);
            } else {
                return response()->json(['error' => 'Unable to create role'], 401);
            }
        } else {
            return response()->json(['error' => 'Permissions to Add/Edit Role Denied'], 401);
        }
    }
    public function searchRoles(Request $request)
    {
        return json_encode(Role::where('name', 'LIKE', '%' . $request->q . '%')
            ->orderBy('name', 'asc')->get());
    }
    public function viewRole(Request $request)
    {
        $role = Role::where('id', $request->id)->first();
        if ($role == null) {
            return redirect()->to('home');
        }
        $permissions = Permission::with([
            'roles' => function ($query) use ($request) {
                $query->where('id', $request->id);
            }
        ])->where("name", 'NOT LIKE', '%permissions%')->get();
        //return json_encode($permissions);
        return view('dashboard.users.role', @compact('role', 'permissions'));
    }

    public function addPermissions(Request $request)
    {
        if (auth()->user()->can("Edit Roles") || auth()->user()->can("Add Roles")) {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:roles,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()], 400);
            }

            $permissionIds = $request->input('permissions', []);
            $permissions = !empty($permissionIds) ? Permission::whereIn('id', $permissionIds)->pluck("name") : [];
            $role = Role::where('id', $request->id)->first();
            try {
                $role->syncPermissions($permissions);
                return response()->json(['success' => 'Permissions updated successfully!']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unable to update permissions: ' . $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Permissions to Add/Edit Role Denied'], 401);
        }
    }
}
