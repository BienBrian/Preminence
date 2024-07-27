<?php

namespace App\Http\Controllers\Dashboard\EventsAndSeminars;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class NoticesController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Events & Notices']);
    }

    public function index()
    {
        $departments = \DB::table('departments')->select('id', 'name')->orderBy('name', 'desc')->get();
        $communities = \DB::table('communities')->select('id', 'name')->orderBy('name', 'desc')->get();
        $roles = \DB::table('userroles')->select('id', 'name')->orderBy('name', 'desc')->get();

        $notices = \DB::table('notices')->select(
            "notices.id",
            "notices.title",
            "notices.noticedate",
            "notices.description",
            "userroles.name as role",
            "communities.name as community",
            "departments.name as department",
            "notices.status"
        )->leftJoin("userroles", "userroles.id", "=", "notices.user_group")
            ->leftJoin("communities", "communities.id", "=", "notices.com_id")->leftJoin("departments", "departments.id", "=", "notices.dep_id")
            ->orderBy('notices.id', 'desc')->paginate(15);
        return view('dashboard.events_and_notices.notices')->with('notices', $notices)->with("departments", $departments)
            ->with("communities", $communities)->with("roles", $roles);
    }

    public function addnotice(Request $request)
    {
        request()->validate([
            'title' => 'required|string|min:4',
            'description' => 'required|string|min:4',
            'target' => 'required'
        ]);

        $comid = 0;
        $depid = 0;
        $role = 0;
        if ($request->target == 1) {
            $comid = $request->community;
        } else if ($request->target == 2) {
            $depid = $request->department;
        } elseif ($request->target == 3) {
            $role = $request->role;
        }

        if ($request->id > 0) {
            $update = \DB::table('notices')->where('id', '=', $request->id)->update(
                array(
                    'title' => $request->title,
                    'description' => $request->description,
                    "noticedate" => \Carbon\Carbon::now(),
                    "user_id" => \Auth::user()->id,
                    "user_group" => $role,
                    "com_id" => $comid,
                    "dep_id" => $depid,
                    "status" => $request->target
                )
            );
            if ($update) {
                return redirect()->back()->with('success', 'Your notice has been updated successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to update notice');
            }
        } else {
            $update = \DB::table('notices')->insert(
                array(
                    'title' => $request->title,
                    'description' => $request->description,
                    "noticedate" => \Carbon\Carbon::now(),
                    "user_id" => \Auth::user()->id,
                    "user_group" => $role,
                    "com_id" => $comid,
                    "dep_id" => $depid,
                    "status" => $request->target
                )
            );
            if ($update) {
                return redirect()->back()->with('success', 'Your notice has been saved successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to save notice');
            }
        }
    }

    public function getnotice(Request $request)
    {
        return json_encode(\DB::table('notices')->where('id', $request->id)->first());
    }

    public function deletenotice(Request $request)
    {
        if (\DB::table('notices')->where('id', $request->id)->delete()) {
            return redirect()->back()->with("success", "Notice successfully removed!");
        } else {
            return redirect()->back()->with("error", "Unable to remove notice!");
        }
    }
}
