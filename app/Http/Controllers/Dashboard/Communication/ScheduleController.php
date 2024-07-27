<?php

namespace App\Http\Controllers\Dashboard\Communication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;

class ScheduleController extends DashboardController
{
    public function __construct(){
        parent::__construct();
    }

    public function schedules(){
        $schedules = \DB::table("schedules")->select("schedules.id", "schedules.message", "schedules.schedule", "users.firstname", "users.lastname", "people.name", "users.email")
            ->leftJoin("contacts", "contacts.id", "=", "schedules.user_id")->leftJoin("users", "users.id", "=", "contacts.user_id")->leftJoin("people", "people.id", "=", "schedules.group_id")->orderBy('schedules.schedule', 'DESC')->paginate(15);

        return view("dashboard.communication.scheduled-sms")->with("schedules", $schedules);
    }

    public function schedule(Request $request){
        $schedule = \DB::table("schedules")->select("schedules.id", "schedules.message", "schedules.schedule", "users.firstname", "users.lastname", "people.name", "users.email", "contacts.phone")->leftJoin("contacts", "contacts.id", "=", "schedules.user_id")
        ->leftJoin("users", "users.id", "=", "contacts.user_id")->leftJoin("people", "people.id", "=", "schedules.group_id")->where("schedules.id", $request->id)->first();


        return view("dashboard.communication.schedule-single-sms")->with("schedule", $schedule);
    }

    public function cancelschedule(Request $request){
        if(\DB::table("schedules")->where("id", $request->id)->delete()){
            return redirect()->to("dashboard/communication/schedule/sms")->with("success", "Scheduled Message has been cancelled!");
        }
        return back()->with("error", "Unable to remove scheduled SMS!");
    }
}
