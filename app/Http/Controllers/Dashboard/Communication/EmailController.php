<?php

namespace App\Http\Controllers\Dashboard\Communication;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Carbon;

class EmailController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Communication']);

    }
    public function index()
    {
        $totalEmails = \DB::table("emails")->count();
        $thisMonth = \DB::table("emails")->where("sent", ">=", Carbon::now()->startOfMonth())->count();
        $totalRecipients = \DB::table("email_recipients")->distinct("user_id")->count("user_id");
        $scheduledCount = \DB::table("schedules")->where("type", 1)->count();
        return view("dashboard.communication.emails", compact('totalEmails', 'thisMonth', 'totalRecipients', 'scheduledCount'));
    }

    public function getEmailsDataTable(Request $request)
    {
        $query = \DB::table("emails")
            ->select("emails.id", "emails.subject", "emails.message", "emails.category", "emails.sent", \DB::Raw("count(email_recipients.email_id) as recipients"))
            ->leftJoin("email_recipients", "email_recipients.email_id", "=", "emails.id")
            ->groupBy("emails.id", "emails.message", "emails.subject", "emails.category", "emails.sent")
            ->orderBy("emails.id", "DESC");

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($request->search) {
                    $q->where(function ($sub) use ($request) {
                        $sub->where('emails.subject', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('emails.message', 'LIKE', '%' . $request->search . '%');
                    });
                }
                if ($request->from_date) {
                    $q->where('emails.sent', '>=', $request->from_date);
                }
                if ($request->to_date) {
                    $q->where('emails.sent', '<=', $request->to_date . ' 23:59:59');
                }
                if ($request->category && $request->category != 'all') {
                    $q->where('emails.category', $request->category);
                }
            })
            ->editColumn('sent', function ($row) {
                return Carbon::parse($row->sent)->format('d M, Y h:i A');
            })
            ->addColumn('time_ago', function ($row) {
                return Carbon::parse($row->sent)->diffForHumans();
            })
            ->addColumn('preview', function ($row) {
                $subject = '<strong>' . e($row->subject) . '</strong><br>';
                $body = \Str::limit(strip_tags(html_entity_decode($row->message)), 60, '...');
                return $subject . '<small class="text-muted">' . $body . '</small>';
            })
            ->addColumn('action', function ($row) {
                return '<div class="text-end text-right">' .
                    '<button class="btn btn-info btn-sm btn-view-email" data-id="' . $row->id . '"><i class="fas fa-eye"></i></button>' .
                    '</div>';
            })
            ->addIndexColumn()->escapeColumns([])->make();
    }

    public function getScheduledEmailsDataTable(Request $request)
    {
        $query = \DB::table("schedules")
            ->select(
                "schedules.id",
                "schedules.message",
                "schedules.schedule",
                "schedules.user_id",
                "schedules.group_id",
                \DB::raw("COALESCE(CONCAT(users.firstname, ' ', users.lastname), '') as user_name"),
                \DB::raw("COALESCE(people.name, '') as group_name")
            )
            ->leftJoin("users", "users.id", "=", "schedules.user_id")
            ->leftJoin("people", "people.id", "=", "schedules.group_id")
            ->where("schedules.type", 1)
            ->orderBy("schedules.schedule", "ASC");

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($request->search) {
                    $q->where('schedules.message', 'LIKE', '%' . $request->search . '%');
                }
            })
            ->addColumn('preview', function ($row) {
                return \Str::limit(strip_tags($row->message), 80, '...');
            })
            ->addColumn('recipient_display', function ($row) {
                if ($row->user_id > 0) {
                    return '<span class="badge bg-info">' . e($row->user_name) . '</span>';
                } elseif ($row->group_id > 0) {
                    return '<span class="badge bg-success">' . e($row->group_name) . '</span>';
                }
                return '<span class="badge bg-primary">All Members</span>';
            })
            ->addColumn('scheduled_for', function ($row) {
                return Carbon::parse($row->schedule)->format('d M, Y h:i A');
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-danger btn-sm btn-cancel-email-schedule" data-id="' . $row->id . '"><i class="fas fa-times"></i> Cancel</button>';
            })
            ->addIndexColumn()->escapeColumns([])->make();
    }

    public function cancelScheduledEmail(Request $request)
    {
        \DB::table("schedules")->where("id", $request->id)->where("type", 1)->delete();
        return response()->json(["success" => "Scheduled email cancelled"]);
    }

    public function getEmails(Request $request)
    {
        $start = $request->limit == null ? "0" : intval($request->limit);
        if ($request->groups == 1) {
            if ($request->search == null) {
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))
                    ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                    orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            } else {
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))->where("name", "LIKE", "%" . $request->search . "%")
                    ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                    orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }
        } else {
            if ($request->search == null) {
                return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email")
                    ->whereNotNull('email_verified_at')->whereNotNull('email')->where('email', '<>', '')
                    ->orderBy("firstname", "ASC")->skip($start)->take(10)->get());
            } else {
                return json_encode(\DB::table("users")->select("id", "firstname", "lastname", "email")
                    ->whereNotNull('email_verified_at')->whereNotNull('email')->where('email', '<>', '')
                    ->where(function ($q) use ($request) {
                        $q->where("firstname", "LIKE", "%" . $request->search . "%")
                          ->orWhere("lastname", "LIKE", "%" . $request->search . "%")
                          ->orWhere("email", "LIKE", "%" . $request->search . "%");
                    })
                    ->orderBy("firstname", "ASC")->skip($start)->take(10)->get());
            }
        }
    }

    public function email(Request $request)
    {
        $email = \DB::table("emails")->where("emails.id", $request->id)->select("people.name", "emails.id", "emails.subject", "emails.message", "emails.sent", \DB::Raw("count(email_recipients.email_id) as recipients"))
            ->leftJoin("email_recipients", "email_recipients.email_id", "=", "emails.id")->leftJoin("people", "people.id", "emails.people_id")
            ->groupBy("people.name", "emails.id", "emails.message", "emails.subject", "emails.sent")->first();
        //return json_encode($email);
        $users = \DB::table("email_recipients")->where("email_recipients.email_id", "=", $request->id)->join("users", "users.id", "email_recipients.user_id")->leftJoin("profiles", "profiles.user_id", "=", "users.id")->
            select("users.firstname", "users.lastname", "users.email", "email_recipients.sent", "profiles.name as image")->paginate(15);

        return view("dashboard.communication.email-read", ["email" => $email, "users" => $users]);
    }
    public function emailJson(Request $request)
    {
        $email = \DB::table("emails")->where("emails.id", $request->id)
            ->select("emails.id", "emails.subject", "emails.message", "emails.category", "emails.sent", "people.name as group_name")
            ->leftJoin("people", "people.id", "=", "emails.people_id")->first();

        if ($email == null) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $recipientCount = \DB::table("email_recipients")->where("email_id", $request->id)->count();

        $members = \DB::table("email_recipients")->where("email_recipients.email_id", $request->id)
            ->select("users.id", "users.firstname", "users.lastname", "users.email")
            ->join("users", "users.id", "=", "email_recipients.user_id")
            ->orderBy("users.firstname")->get();

        return response()->json([
            'id' => $email->id,
            'subject' => $email->subject,
            'message' => $email->message,
            'sent' => Carbon::parse($email->sent)->format('d M, Y h:i A'),
            'sent_ago' => Carbon::parse($email->sent)->diffForHumans(),
            'category' => $email->category ?? 'manual',
            'group_name' => $email->group_name,
            'recipient_count' => $recipientCount,
            'members' => $members,
        ]);
    }

    public function removeemail(Request $request)
    {
        $message = \DB::table('emails')->where("id", $request->id)->first();
        if ($message == null) {
            return redirect()->back()->with("error", "Invalid Email ID");
        } else {
            if (\DB::table('emails')->where("id", $request->id)->delete()) {
                \DB::table('email_recipients')->where("email_id", $request->id)->delete();
                return redirect()->to('emails')->with("success", "Email removed successfully");
            } else {
                return redirect()->back()->with("error", "Unable to remove email");
            }
        }
    }

    public function html_email(Request $request)
    {
        $request->validate([
            "message" => "required",
            "subject" => "required",
        ]);

        $request->merge(['message' => $this->purify($request->message)]);

        // Check for scheduling
        if ($request->has('time') && $request->time != null) {
            $date = \Carbon\Carbon::parse($request->time);
            if ($request->choice == 0) {
                if (empty($request['contacts'])) {
                    return back()->with("error", "No recipients selected!");
                }
                foreach ($request['contacts'] as $contact) {
                    \DB::table('schedules')->insert([
                        "message" => $request->subject . '|||' . $request->message,
                        "type" => 1,
                        "user_id" => $contact,
                        "group_id" => 0,
                        "schedule" => $date,
                        "status" => 0,
                        "created_at" => \Carbon\Carbon::now()
                    ]);
                }
            } else if ($request->choice == 1) {
                if (empty($request['groups'])) {
                    return back()->with("error", "No groups selected!");
                }
                foreach ($request['groups'] as $contact) {
                    \DB::table('schedules')->insert([
                        "message" => $request->subject . '|||' . $request->message,
                        "type" => 1,
                        "user_id" => 0,
                        "group_id" => $contact,
                        "schedule" => $date,
                        "status" => 0,
                        "created_at" => \Carbon\Carbon::now()
                    ]);
                }
            } else {
                \DB::table('schedules')->insert([
                    "message" => $request->subject . '|||' . $request->message,
                    "type" => 1,
                    "user_id" => 0,
                    "group_id" => 0,
                    "schedule" => $date,
                    "status" => 0,
                    "created_at" => \Carbon\Carbon::now()
                ]);
            }
            return redirect()->to('dashboard/communication/emails')->with("success", "Email scheduled for " . $date->format('d M, Y h:i A'));
        }

        set_time_limit(0);
        if ($request->choice == 0) {
            //send to individual
            if (empty($request['contacts'])) {
                return back()->with("error", "No recipients selected!");
            } else {
                $mid = \DB::table('emails')->insertGetId(["subject" => $request->subject, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                foreach ($request['contacts'] as $contact) {
                    $user = \DB::table("users")->where("id", $contact)->first();
                    if ($user != null) {
                        \DB::table("email_recipients")->insert(["user_id" => $user->id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                        $this->sendemail($user->email, $request->subject, $user->firstname, $user->lastname, $request->message);
                    }
                }
                return back()->with("success", "Emails successfully sent!");
            }
        } else if ($request->choice == 1) {
            //send to groups
            foreach ($request['groups'] as $contact) {
                $mid = \DB::table('emails')->insertGetId(["subject" => $request->subject, "people_id" => $contact, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                $leader = \DB::table("people")->where("people.id", $contact)->join("users", "users.id", "=", "people.leader")->first();
                if ($leader != null) {
                    \DB::table("email_recipients")->insert(["user_id" => $leader->leader, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                    $this->sendemail($leader->email, $request->subject, $leader->firstname, $leader->lastname, $request->message);
                }
                $members = \DB::table("people_members")->where("people_id", $contact)->where("people_members.status", 1)->join("users", "users.id", "=", "people_members.user_id")->get();
                foreach ($members as $member) {
                    \DB::table("email_recipients")->insert(["user_id" => $member->user_id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                    $this->sendemail($member->email, $request->subject, $member->firstname, $member->lastname, $request->message);
                }
            }
        } else {
            //Send to all (verified emails only)
            $users = \DB::table("users")->whereNotNull('email_verified_at')->whereNotNull('email')->where('email', '<>', '')->get();
            $mid = \DB::table('emails')->insertGetId(["subject" => $request->subject, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
            foreach ($users as $user) {
                \DB::table("email_recipients")->insert(["user_id" => $user->id, "email_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                $this->sendemail($user->email, $request->subject, $user->firstname, $user->lastname, $request->message);
            }
        }
        return redirect()->back()->with("success", "Emails sent successfully!");
    }
    public function sendemail($email, $subject, $firstname, $lastname, $mess)
    {
        $data = array('name' => $firstname . " " . $lastname, 'mes' => $mess);
        $site_settings = $this->site_settings;
        $church_name = $site_settings != null ? "" . $site_settings->name : "CHURCH APP";
        Mail::send('dashboard.communication.mail', $data, function ($message) use ($email, $subject, $firstname, $lastname, $church_name) {
            $message->to($email, $firstname . " " . $lastname)->subject
            ($subject);
            $message->from('info@happychurchruiru.org', $church_name);
        });
    }

}
