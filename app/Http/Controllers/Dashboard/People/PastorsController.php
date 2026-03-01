<?php

namespace App\Http\Controllers\Dashboard\People;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class PastorsController extends DashboardController
{
    public static $titles = [
        'Senior Pastor',
        'Associate Pastor',
        'Youth Pastor',
        "Children's Pastor",
        'Worship Pastor',
        'Assistant Pastor',
        'Elder',
        'Deacon',
        'Pastor',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View People']);
    }

    public function pastors()
    {
        $pastors = \DB::table("pastors")
            ->select("pastors.id", "users.firstname", "users.lastname", "users.email", "pastors.title")
            ->join("users", "users.id", "=", "pastors.user_id")
            ->paginate(15);

        return view("dashboard.people.pastors")
            ->with("pastors", $pastors)
            ->with("titles", self::$titles);
    }

    public function addpastor(Request $request)
    {
        if (empty($request['contacts'])) {
            return back()->with("error", "Please select a user to add");
        } else {
            foreach ($request['contacts'] as $userid) {
                if (\DB::table('pastors')->where("user_id", $userid)->count() == 0) {
                    \DB::table("pastors")->insert(["user_id" => $userid, "title" => "Pastor"]);
                }
            }
            return back()->with("success", "Pastors added successfully");
        }
    }

    public function updateTitle(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:pastors,id',
            'title' => 'required|string|max:50',
        ]);

        \DB::table("pastors")->where("id", $request->id)->update(["title" => $request->title]);

        return back()->with("success", "Title updated successfully!");
    }

    public function removePastor(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:pastors,id',
        ]);

        $pastor = \DB::table("pastors")->where("id", $request->id)->first();
        if (!$pastor) {
            return back()->with("error", "Pastor not found.");
        }

        \DB::table("pastors")->where("id", $request->id)->delete();

        return back()->with("success", "Pastor removed successfully. The user remains a member.");
    }
}
