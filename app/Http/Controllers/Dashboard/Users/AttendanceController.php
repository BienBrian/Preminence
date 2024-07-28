<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\SMS\SMSController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;

class AttendanceController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $attendance = \DB::table("attendance")->select(
            "attendance.id",
            "attendance.day",
            "attendance.attendance",
            "attendance_groups.name",
            "seminars.title as seminar",
            "events.title as event",
            "orderofservice.description as service",
            "attendance.attended_for as mtype"
        )
            ->join("attendance_groups", "attendance_groups.id", "=", "attendance.attendance_group")
            ->leftJoin("seminars", "seminars.id", "=", "attendance.attendance_id")
            ->leftJoin("orderofservice", "orderofservice.id", "=", "attendance.attendance_id")
            ->leftJoin("events", "events.id", "=", "attendance.attendance_id")
            ->paginate(15);

        //Week Statistics
        $thisweek = \DB::table("attendance")->where("day", ">=", \Carbon\Carbon::today()->startOfWeek())->sum("attendance");
        $lastweek = \DB::table("attendance")->where("day", ">=", \Carbon\Carbon::today()->startOfWeek()->subDays(7))->where("day", "<", \Carbon\Carbon::today()->startOfWeek())->sum("attendance");

        //Month Statistics
        $end = new Carbon('first day of last month');
        $start = new Carbon('first day of this month');

        $thismonth = \DB::table("attendance")->where("day", ">=", $start)->sum("attendance");
        $lastmonth = \DB::table("attendance")->where("day", ">=", $end)->where("day", "<", $start)->sum("attendance");

        //Year Statistics
        $start = \Carbon\Carbon::today()->startOfYear();
        $end = $start->copy()->subYears(1);

        $thisyear = \DB::table("attendance")->where("day", ">=", $start)->sum("attendance");
        $lastyear = \DB::table("attendance")->where("day", ">=", $end)->where("day", "<", $start)->sum("attendance");

        //$endOfYear   = $date->copy()->endOfYear();
        $groups = \DB::table("attendance_groups")->get();

        return view("dashboard.events_and_notices.attendance", [
            "attendance" => $attendance,
            "groups" => $groups,
            "thisyear" => $thisyear,
            "lastyear" => $lastyear,
            "thisweek" => $thisweek,
            "lastweek" => $lastweek,
            "thismonth" => $thismonth,
            "lastmonth" => $lastmonth
        ]);
    }
    public function newAttendance()
    {
        $groups = \DB::table("attendance_groups")->get();
        $services = \DB::table("orderofservice")->get();
        //return json_encode($services);

        return view("dashboard.events_and_notices.attendance-add", ["groups" => $groups, "services" => $services]);
    }

    //children attendance
    public function children()
    {
        return view('dashboard.children.children');
    }
    public function getChildren(Request $request)
    {
        return DataTables::of(\DB::table("children")->select(
            'children.id',
            'children.firstname as fname',
            'children.lastname as lname',
            'children.surname as sname',
            'users.firstname',
            'users.lastname',
            'users.surname',
            'children.gender',
            'children.dob',
            'children.residence',
            'children.sunday_school_class'
        )
            ->leftJoin('guardians', 'guardians.child_id', 'children.id')->leftJoin('users', 'users.id', '=', 'guardians.user_id')->orderBy('children.firstname', 'ASC'))
            ->addColumn("name", function ($item) {
                return $item->fname . ' ' . $item->lname . ' ' . $item->sname;
            })->addColumn("guardian", function ($item) {
                return $item->firstname . ' ' . $item->lastname . ' ' . $item->surname;
            })->addColumn("age", function ($item) {
                return '-';
            })->addColumn("dob", function ($item) {
                return \Carbon\Carbon::parse($item->dob)->format("d M, Y h:i A");
            })->filter(function ($query) use ($request) {
                if ($request->has('search')) {
                    if ($request->search != null) {
                        $query->where("children.firstname", 'like', "%{$request->get('search')}%")->orWhere("children.lastname", 'like', "%{$request->get('search')}%")
                            ->orWhere("children.surname", 'like', "%{$request->get('search')}%")->orWhere("users.firstname", 'like', "%{$request->get('search')}%")
                            ->orWhere("users.lastname", 'like', "%{$request->get('search')}%")->orWhere("users.surname", 'like', "%{$request->get('search')}%")
                            ->orWhere("children.sunday_school_class", 'like', "%{$request->get('search')}%");
                    }
                }
            })->addColumn("action", function ($item) {
                $action = '<div class="text-right">
                        <span class="d-none id">' . $item->id . '</span>' .
                    '<span class="d-none parent">' . $item->firstname . ' ' . $item->lastname . ' ' . $item->surname . '</span>' .
                    '<span class="d-none child">' . $item->fname . ' ' . $item->lname . ' ' . $item->sname . '</span>' .
                    '<a href="' . url("dashboard/children/view/" . $item->id) . '" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="bottom" title="View">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="btn btn-outline-primary btn-sm btnEdit" data-toggle="modal" data-target="#addEventModal" style="border-width: 2px;">
                            <i class="fas fa-calendar-alt"></i> Checkin
                        </button>
                    </div>';
                return $action;
            })->escapeColumns([])->make(true);
    }

    public function child(Request $request)
    {
        $child = \DB::table('children')->where('id', $request->id)->first();
        if ($child == null) {
            return back()->with('error', 'Invalid child ID');
        }
        $parent = \DB::table('users')->where('users.id', $child->user_id)->first();
        return view('dashboard.children.child', ['child' => $child, 'parent' => $parent]);
    }
    public function getChildGuardians(Request $request)
    {
        return DataTables::of(\DB::table("guardians")->select('users.id', 'firstname', 'lastname', 'surname', 'email', 'phone', 'relationship')
            ->join('users', 'users.id', '=', 'guardians.user_id')->where('guardians.child_id', $request->id)->orderBy('users.firstname', 'ASC'))
            ->addColumn("guardian", function ($item) {
                return $item->firstname . ' ' . $item->lastname . ' ' . $item->surname;
            })->filter(function ($query) use ($request) {
                if ($request->has('search')) {
                    if ($request->search != null) {
                        $query->where("users.firstname", 'like', "%{$request->get('search')}%")
                            ->orWhere("users.lastname", 'like', "%{$request->get('search')}%")
                            ->orWhere("users.surname", 'like', "%{$request->get('search')}%");
                    }
                }
            })->addColumn("action", function ($item) {
                $action = '<div class="text-right">
                        <a href="' . url("user/" . $item->id) . '" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>';
                return $action;
            })->escapeColumns([])->make(true);
    }

    public function searchChildrenEvents(Request $request)
    {
        return json_encode(\DB::table('children_checkin')->where('name', 'LIKE', '%' . $request->q . '%')
            ->orderBy('id', 'DESC')->skip(0)->take(10)->get());
    }

    public function searchChildrenParents(Request $request)
    {
        return json_encode(\DB::table('users')->select('id', 'firstname', 'lastname', 'email', 'phone')->where(\DB::Raw('CONCAT(firstname, " ",lastname)'), 'LIKE', '%' . $request->q . '%')
            ->orWhere('email', 'LIKE', '%' . $request->q . '%')->orWhere('phone', 'LIKE', '%' . $request->q . '%')->orderBy('id', 'DESC')->skip(0)->take(10)->get());
    }

    public function importChildren(Request $request)
    {
        $request->validate([
            'import_file' => 'required'
        ]);

        Excel::import(new ChildrenImport, request()->file('import_file'));

        return back()->with('success', 'Insert Record successfully.');
    }
    public function childrenAttendance()
    {
        //Week Statistics
        $thisweek = \DB::table("children_attendance")->where("timein", ">=", \Carbon\Carbon::today()->startOfWeek())->count();
        $lastweek = \DB::table("children_attendance")->where("timein", ">=", \Carbon\Carbon::today()->startOfWeek()->subDays(7))->where("timein", "<", \Carbon\Carbon::today()->startOfWeek())->count();

        //Month Statistics
        $end = new Carbon('first day of last month');
        $start = new Carbon('first day of this month');

        $thismonth = \DB::table("children_attendance")->where("timein", ">=", $start)->count();
        $lastmonth = \DB::table("children_attendance")->where("timein", ">=", $end)->where("timein", "<", $start)->count();

        //Year Statistics
        $start = \Carbon\Carbon::today()->startOfYear();
        $end = $start->copy()->subYears(1);

        $thisyear = \DB::table("children_attendance")->where("timein", ">=", $start)->count();
        $lastyear = \DB::table("children_attendance")->where("timein", ">=", $end)->where("timein", "<", $start)->count();

        return view("dashboard.children.attendance_children", [
            "thisyear" => $thisyear,
            "lastyear" => $lastyear,
            "thisweek" => $thisweek,
            "lastweek" => $lastweek,
            "thismonth" => $thismonth,
            "lastmonth" => $lastmonth
        ]);
    }

    public function getChildrenEvents(Request $request)
    {
        return DataTables::of(\DB::table("children_checkin")->select(
            "children_checkin.id",
            "children_checkin.name",
            "children_checkin.from_date",
            "children_checkin.to_date",
            \DB::Raw("COUNT(children_attendance.id) as children")
        )->leftJoin("children_attendance", "children_attendance.checkin_id", "children_checkin.id")
            ->groupBy("children_checkin.id", "children_checkin.name", "children_checkin.from_date", "children_checkin.to_date")->orderBy('from_date', 'DESC'))
            ->addColumn("from", function ($item) {
                return \Carbon\Carbon::parse($item->from_date)->format("d M, Y h:i A");
            })->addColumn("to", function ($item) {
                return \Carbon\Carbon::parse($item->to_date)->format("d M, Y h:i A");
            })->addColumn("children", function ($item) {
                return $item->children . " Child(ren)";
            })->filter(function ($query) use ($request) {
                if ($request->has('msearch')) {
                    if ($request->msearch != null) {
                        $query->where("name", 'like', "%{$request->get('msearch')}%");
                    }//$query->where('email', 'like', "%{$request->get('email')}%");
                }
            })->addColumn("action", function ($item) {
                $action = '<div class="text-right">
                            <span class="d-none id">' . $item->id . '</span>' .
                    '<a href="' . url("dashboard/children/attendance/" . $item->id) . '" class="btn btn-primary p-1 pr-2 pl-2" data-toggle="tooltip" data-placement="bottom" title="View">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <!--
                            <button class="btn btn-outline-primary p-1 pr-2 pl-2 btnEdit" data-toggle="tooltip" data-placement="bottom" title="Delete" style="border-width: 2px;">
                                <i class="fas fa-edit"></i> Edit
                            </button>-->
                        </div>';
                return $action;
            })->escapeColumns([])->make(true);
    }

    public function saveChildrenCheckin(Request $request)
    {
        request()->validate([
            "name" => "string",
            "from" => "string|required",
            "to" => "string|required",
        ]);

        $from = \Carbon\Carbon::parse($request->from);
        $to = \Carbon\Carbon::parse($request->to);

        if ($request->id == 0) {
            if (
                \DB::table("children_checkin")->insert([
                    "name" => $request->name,
                    "from_date" => $from,
                    "to_date" =>
                        $to,
                    "created_at" => \Carbon\Carbon::now()
                ])
            ) {
                return redirect()->back()->with("success", "New checkin event created");
            } else {
                return redirect()->back()->with("error", "Unable to create checkin event");
            }
        } else {
            if (
                \DB::table("children_checkin")->where("id", $request->id)->update([
                    "name" => $request->name,
                    "from_date" => $from,
                    "to_date" =>
                        $to,
                    "created_at" => \Carbon\Carbon::now()
                ])
            ) {
                return redirect()->back()->with("success", "Checkin event updated!");
            } else {
                return redirect()->back()->with("error", "Unable to update checkin event");
            }
        }
    }

    public function showChildrenAttendance(Request $request)
    {
        //Event Name
        $event = \DB::table("children_checkin")->where("id", $request->id)->first();
        $children = \DB::table("children_attendance")->where("checkin_id", $request->id)->count();

        return view("dashboard.children.attendance_children_checkin", ["event" => $event, "children" => $children]);
    }

    public function saveChildrenAttendance(Request $request)
    {
        request()->validate([
            "id" => "integer|min:1|required",
            "event" => "integer|min:1|required",
        ]);
        $checkin = \Carbon\Carbon::now();
        $child = \DB::table('children')->where('id', $request->id)->first();
        //Check if had checked in
        if (\DB::table("children_attendance")->where('child_id', $request->id)->where('checkin_id', $request->event)->where('timeout', null)->count() == 0) {
            if (\DB::table("children_attendance")->insert(["checkin_id" => $request->event, "child_id" => $request->id, "timein" => \Carbon\Carbon::now(), "created_at" => \Carbon\Carbon::now()])) {
                $parents = \DB::table('guardians')->join('users', 'users.id', 'guardians.user_id')->where('child_id', $request->id)->get();
                $event = \DB::table("children_checkin")->where("id", $request->event)->first();
                if ($event != null && $child != null) {
                    foreach ($parents as $parent) {
                        $message = 'Dear ' . $parent->firstname . ',\n ' . $child->firstname . ' has been checked in for ' . $event->name;
                        $smsController = new SMSController();
                        $smsController->send($parent->phone, $message);
                    }
                }

                return redirect()->back()->with("success", "New Child attendance entered");
            } else {
                return redirect()->back()->with("error", "Unable to create attendance");
            }
        } else {
            return back()->with('error', $request->child . ' has already been checked in for this event!');
        }
    }

    public function saveChild(Request $request)
    {
        request()->validate([
            "id" => "integer|min:0|required",
            "firstname" => "string|nullable",
            "lastname" => "string|nullable",
            "surname" => "string|nullable",
            "dob" => "string|required",
            "gender" => "string|required",
            "residence" => "string|required",
            "sunday_school_class" => "string|nullable",
        ]);
        $dob = \Carbon\Carbon::parse($request->dob);
        if ($request->id > 0) {
            //update
            if (
                \DB::table("children")->where('id', $request->id)->update([
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'surname' => $request->surname,
                    'dob' => $dob,
                    'residence' => $request->residence,
                    'sunday_school_class' => $request->sunday_school_class,
                    'gender' => $request->gender,
                    'updated_at' => \Carbon\Carbon::now()
                ])
            ) {
                return redirect()->back()->with("success", "Child updated");
            } else {
                return back()->with('error', 'Unable to update child');
            }
        } else {
            if (
                \DB::table("children")->insert([
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'surname' => $request->surname,
                    'dob' => $dob,
                    'residence' => $request->residence,
                    'sunday_school_class' => $request->sunday_school_class,
                    'gender' => $request->gender,
                    'created_at' => \Carbon\Carbon::now()
                ])
            ) {
                return redirect()->back()->with("success", "Child Added");
            } else {
                return back()->with('error', 'Unable to add child');
            }

        }
    }

    public function getChildrenAttendance(Request $request)
    {
        return DataTables::of(\DB::table("children_attendance")->select(
            'children_attendance.id',
            'children_attendance.timein',
            'children.firstname',
            'children.lastname',
            'children.surname',
            'users.firstname as fname',
            'users.lastname as lname',
            'users.surname as sname',
            'children_attendance.timeout',
            'users.phone'
        )
            ->join('children', 'children.id', 'children_attendance.child_id')->join('guardians', 'guardians.child_id', 'children.id')
            ->join('users', 'users.id', 'guardians.user_id')->where("checkin_id", $request->id)->orderBy('children.firstname', 'ASC'))
            ->addColumn("timein", function ($item) {
                return \Carbon\Carbon::parse($item->timein)->setTimezone('Africa/Nairobi')->format("d M, Y h:i A");
            })->filter(function ($query) use ($request) {
                if ($request->has('msearch')) {
                    if ($request->msearch != null) {
                        $query->where("name", 'like', "%{$request->get('msearch')}%");
                    }//$query->where('email', 'like', "%{$request->get('email')}%");
                }
            })->addColumn("timeout", function ($item) {
                if ($item->timeout != null) {
                    return \Carbon\Carbon::parse($item->timeout)->setTimezone('Africa/Nairobi')->format("d M, Y h:i A");
                } else {
                    return "-";
                }
            })->addColumn("guardian", function ($item) {
                return '<i class="far fa-user"></i> ' . $item->fname . " " . $item->lname . " " . $item->sname;
            })->addColumn("child", function ($item) {
                return '<i class="fas fa-child"></i> ' . $item->firstname . " " . $item->lastname . " " . $item->surname;
            })->addColumn("action", function ($item) {
                $action = '<div class="text-right">
                <span class="d-none id">' . $item->id . '</span>';
                if ($item->timeout == null) {
                    $action .= '<a href="' . url("dashboard/children/checkout/" . $item->id) . '" class="btn btn-primary p-1 pr-2 pl-2" data-toggle="tooltip" data-placement="bottom" title="View">
                                    <i class="fas fa-redo"></i> Checkout
                                </a>';
                } else {
                    $action .= '<span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Checked out
                                </span>';
                }
                $action .= "</div>";
                return $action;
            })->escapeColumns([])->make(true);
    }

    public function checkOut(Request $request)
    {
        $details = \DB::table('children_attendance')->select('children.firstname as fname', 'users.firstname', 'users.phone', 'children_checkin.name')
            ->where('children_attendance.id', $request->id)
            ->join('children', 'children.id', 'children_attendance.child_id')->join('guardians', 'guardians.child_id', 'children.id')
            ->join('users', 'users.id', 'guardians.user_id')->join('children_checkin', 'children_checkin.id', 'children_attendance.checkin_id')->get();

        if (\DB::table("children_attendance")->where("id", $request->id)->update(["timeout" => \Carbon\Carbon::now()])) {
            foreach ($details as $detail) {
                $message = 'Dear ' . $detail->firstname . ',\n ' . $detail->fname . ' has been checked out for ' . $detail->name;
                $smsController = new SMSController();
                $smsController->send($detail->phone, $message);
            }
            return back()->with("success", "Checkout Successful");
        } else {
            return back()->with("error", "Unable to checkout");
        }
    }

    public function addParent(Request $request)
    {
        $request->validate([
            'parent' => 'required|integer|exists:users,id',
            'child_id' => 'required|min:1',
        ]);
        if (\DB::table('guardians')->where('child_id', $request->child_id)->where('user_id', $request->parent)->count() == 0) {
            if (\DB::table('guardians')->insert(['child_id' => $request->child_id, 'user_id' => $request->parent, 'relationship' => $request->gender, 'created_at' => \Carbon\Carbon::now()])) {
                return back()->with('success', 'Guardian added successfully!');
            }
        } else {
            return back()->with('error', 'Guardian already exists!');
        }
    }

    //attendance
    public function ajaxAttendance(Request $request)
    {
        $from = \Carbon\Carbon::today()->subDays(7);

        if ($request->time == 0) {
            if ($request->group > 0) {
                return json_encode(\DB::table("attendance")->where("day", ">=", $from)->where("attendance_group", $request->group)->select(\DB::Raw("DAYNAME(day) as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
            }
            return json_encode(\DB::table("attendance")->where("day", ">=", $from)->select(\DB::Raw("DAYNAME(day) as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
        } else if ($request->time == 1) {
            $from = \Carbon\Carbon::today()->subMonths(1);

            if ($request->group > 0) {
                return json_encode(\DB::table("attendance")->where("day", ">=", $from)->where("attendance_group", $request->group)->select(\DB::Raw("DATE_FORMAT(day, '%D, %M') as days, SUM(attendance) as att"))->orderBy("day", "ASC")->orderBy("day", "ASC")->groupBy("days")->get());
            }
            return json_encode(\DB::table("attendance")->where("day", ">=", $from)->select(\DB::Raw("DATE_FORMAT(day, '%D, %M') as days, SUM(attendance) as att"))->orderBy("day", "ASC")->orderBy("day", "ASC")->groupBy("days")->get());
        } else if ($request->time == 2) {
            $from = \Carbon\Carbon::today()->subMonths(6);

            if ($request->group > 0) {
                return json_encode(\DB::table("attendance")->where("day", ">=", $from)->where("attendance_group", $request->group)->select(\DB::Raw("MONTHNAME(day) as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
            }
            return json_encode(\DB::table("attendance")->where("day", ">=", $from)->select(\DB::Raw("MONTHNAME(day) as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
        } else if ($request->time == 3) {
            $from = \Carbon\Carbon::today()->subMonths(12);

            if ($request->group > 0) {
                return json_encode(\DB::table("attendance")->where("day", ">=", $from)->where("attendance_group", $request->group)->select(\DB::Raw("MONTHNAME(day) as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
            }
            return json_encode(\DB::table("attendance")->where("day", ">=", $from)->select(\DB::Raw("MONTHNAME(day) as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
        } else if ($request->time == 4) {
            $from = \Carbon\Carbon::today()->subMonths(60);
            if ($request->group > 0) {
                return json_encode(\DB::table("attendance")->where("day", ">=", $from)->where("attendance_group", $request->group)->select(\DB::Raw("DATE_FORMAT(day, '%Y') as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
            }
            return json_encode(\DB::table("attendance")->where("day", ">=", $from)->select(\DB::Raw("DATE_FORMAT(day, '%Y') as days, SUM(attendance) as att"))->groupBy("days")->orderBy("day", "ASC")->get());
        }

    }
    public function ajaxEvents()
    {
        $from = \Carbon\Carbon::today()->subDays(7);

        return json_encode(\DB::table("events")->where("eventdate", ">=", $from)->where("eventdate", "<=", \Carbon\Carbon::today())
            ->select("id", "title as name")->orderBy('eventdate', 'DESC')->get());
    }

    public function ajaxSeminars()
    {
        $from = \Carbon\Carbon::today()->subDays(7);

        return json_encode(\DB::table("seminars")->where("end", ">=", $from)->where("end", "<=", \Carbon\Carbon::today())
            ->select("id", "title as name")->orderBy('end', 'DESC')->get());
    }

    public function ajaxServices()
    {
        return json_encode(\DB::table("orderofservice")->select("id", "description as name")->get());
    }
    public function datatablesAttendance(Request $request)
    {
        return Datatables::of(\DB::table("attendance")->select(
            "attendance.id",
            "attendance.attended_for",
            "attendance.day",
            "attendance.attendance",
            "attendance_groups.name",
            "seminars.title as seminar",
            "events.title as event",
            "orderofservice.description as service",
            "attendance.attended_for as mtype"
        )
            ->join("attendance_groups", "attendance_groups.id", "=", "attendance.attendance_group")
            ->leftJoin("seminars", "seminars.id", "=", "attendance.attendance_id")
            ->leftJoin("orderofservice", "orderofservice.id", "=", "attendance.attendance_id")
            ->leftJoin("events", "events.id", "=", "attendance.attendance_id")->orderBy("attendance.id", "DESC"))
            ->filter(function ($query) use ($request) {
                $from = \Carbon\Carbon::today()->subDays(7);

                if ($request->has('time')) {
                    if ($request->time == 0) {
                        if ($request->group > 0) {
                            $query->where("attendance.day", ">=", $from)->where("attendance.attendance_group", $request->group);
                        } else {
                            $query->where("attendance.day", ">=", $from);
                        }
                    } else if ($request->time == 1) {
                        $from = \Carbon\Carbon::today()->subMonths(1);

                        if ($request->group > 0) {
                            $query->where("attendance.day", ">=", $from)->where("attendance.attendance_group", $request->group);
                        } else {
                            $query->where("attendance.day", ">=", $from);
                        }
                    } else if ($request->time == 2) {
                        $from = \Carbon\Carbon::today()->subMonths(6);

                        if ($request->group > 0) {
                            $query->where("attendance.day", ">=", $from)->where("attendance.attendance_group", $request->group);
                        } else {
                            $query->where("attendance.day", ">=", $from);
                        }
                    } else if ($request->time == 3) {
                        $from = \Carbon\Carbon::today()->subMonths(12);

                        if ($request->group > 0) {
                            $query->where("attendance.day", ">=", $from)->where("attendance.attendance_group", $request->group);
                        } else {
                            $query->where("attendance.day", ">=", $from);
                        }
                    } else if ($request->time == 4) {
                        $from = \Carbon\Carbon::today()->subMonths(60);
                        if ($request->group > 0) {
                            $query->where("attendance.day", ">=", $from)->where("attendance.attendance_group", $request->group);
                        } else {
                            $query->where("attendance.day", ">=", $from);
                        }
                    }
                }
            })->addColumn("attendance_type", function ($item) {
                if ($item->attended_for == 0) {
                    return "Church Service";
                } elseif ($item->attended_for == 1) {
                    return "Events";
                } else {
                    return "Seminars";
                }
            })->addColumn("attendance_name", function ($item) {
                if ($item->attended_for == 0) {
                    return $item->service;
                } elseif ($item->attended_for == 1) {
                    return $item->event;
                } else {
                    return $item->seminar;
                }
            })->addColumn('date', function ($item) {
                return \Carbon\Carbon::parse($item->day)->format('D d M, Y h:i A');
            })->addColumn('action', function ($item) {
                return "<a href='" . url("dashboard/events_and_notices/attendance/remove/" . $item->id) . "' class='btn btn-danger btn-sm'>Delete</a>";
            })->make();
    }


    public function attendancegroups()
    {
        $groups = \DB::table("attendance_groups")->paginate(15);
        return view("dashboard.events_and_notices.attendance_groups")->with("groups", $groups);
    }

    public function addAttendance(Request $request)
    {
        $request->validate([
            "attended_for" => "required",
            "specific" => "required",
            "attendees" => "required|min:0",
            "group_id" => "required",
            "date" => "required",
        ]);

        if ($request->id > 0) {
            if (
                \DB::table('attendance')->where("id", $request->id)->update([
                    'attendance_group' => $request->group_id,
                    "attended_for" => $request->attended_for,
                    "attendance_id" => $request->specific,
                    "attendance" => $request->attendees,
                    "day" => \Carbon\Carbon::parse($request->date)
                ])
            ) {
                return back()->with("success", "Attendance group updated!");
            } else {
                return back()->with("error", "unable to update");
            }
        } else {
            if (
                \DB::table('attendance')->insert([
                    'attendance_group' => $request->group_id,
                    "attended_for" => $request->attended_for,
                    "attendance_id" => $request->specific,
                    "attendance" => $request->attendees,
                    "day" => \Carbon\Carbon::parse($request->date)
                ])
            ) {
                return redirect()->to("dashboard/events_and_notices/attendance")->with("success", "Attendance group created!");
            } else {
                return back()->with("error", "unable to create");
            }
        }
    }

    public function addAttendanceGroup(Request $request)
    {
        $request->validate([
            "name" => "required|unique:attendance_groups",
        ]);

        if ($request->id > 0) {
            if (\DB::table('attendance_groups')->where("id", $request->id)->update(['name' => $request->name])) {
                return back()->with("success", "Attendance group updated!");
            } else {
                return back()->with("error", "unable to update");
            }
        } else {
            if (\DB::table('attendance_groups')->insert(['name' => $request->name])) {
                return back()->with("success", "Attendance group created!");
            } else {
                return back()->with("error", "unable to create");
            }
        }
    }

    public function removeAttendance(Request $request)
    {
        if (\DB::table("attendance")->where("id", $request->id)->count() > 0) {
            if (\DB::table("attendance")->where("id", $request->id)->delete()) {
                return back()->with("success", "Attendance removed successfully");
            } else {
                return back()->with("error", "Unable to remove attendance");
            }
        } else {
            return back()->with("error", "Invalid request");
        }
    }

    public function removeAttendanceGroup(Request $request)
    {
        if (\DB::table("attendance")->where("attendance_group", $request->id)->count() == 0) {
            if (\DB::table("attendance_groups")->where("id", $request->id)->delete()) {
                return back()->with("success", "Attendance Group removed successfully");
            } else {
                return back()->with("error", "Unable to remove attendance group");
            }
        } else {
            return back()->with("error", "Attendance Group already in use");
        }
    }
}
