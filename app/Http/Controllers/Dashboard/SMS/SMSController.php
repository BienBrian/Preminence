<?php

namespace App\Http\Controllers\Dashboard\SMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Services\IntegrationService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Carbon;

class SMSController extends DashboardController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Communication']);
    }

    public function sms(){
        $tid = config('app.tenant_id');
        $totalSms = \DB::table("sms")->where('tenant_id', $tid)->count();
        $thisMonth = \DB::table("sms")->where('tenant_id', $tid)->where("sent", ">=", Carbon::now()->startOfMonth())->count();
        $totalRecipients = \DB::table("sms_recipients")->where('tenant_id', $tid)->distinct("recipients")->count("recipients");
        $scheduledCount = \DB::table("schedules")->where('tenant_id', $tid)->where("type", 0)->count();
        $invitationCount = \DB::table('sms')->where('tenant_id', $tid)->where('category', 'invitation')->count();
        return view("dashboard.communication.sms", compact('totalSms', 'thisMonth', 'totalRecipients', 'scheduledCount', 'invitationCount'));
    }

    public function getCreditsBalance(Request $request)
    {
        // Fetch SMS credits from the provider API
        $integrationService = app(IntegrationService::class);
        $smsCreditsResponse = $integrationService->checkSmsCredits();
        
        $smsCredits = null;
        if (is_array($smsCreditsResponse)) {
            // Try common response formats from SMS providers
            $smsCredits = $smsCreditsResponse['balance'] 
                ?? $smsCreditsResponse['credit'] 
                ?? $smsCreditsResponse['credits'] 
                ?? $smsCreditsResponse['credits_remaining'] 
                ?? $smsCreditsResponse['data']['balance'] 
                ?? null;
        }
        
        // Email credits - still from config or could be from email provider API
        $emailCredits = 9999; // Unlimited for SMTP-based email
        
        return response()->json([
            'sms'   => $smsCredits !== null ? (int) $smsCredits : null,
            'email' => (int) $emailCredits,
            'source' => $smsCredits !== null ? 'api' : 'unknown',
        ]);
    }

    public function getSmsDataTable(Request $request){
        $tid = config('app.tenant_id');
        $query = \DB::table("sms")
            ->where('sms.tenant_id', $tid)
            ->select("sms.id", "sms.message", "sms.category", "sms.sent", \DB::Raw("count(sms_recipients.sms_id) as recipients"))
            ->leftJoin("sms_recipients", "sms_recipients.sms_id", "=", "sms.id")
            ->groupBy("sms.id", "sms.message", "sms.category", "sms.sent")
            ->orderBy("sms.id", "DESC");

        return DataTables::of($query)
            ->filter(function ($q) use ($request) {
                if ($request->search) {
                    $q->where('sms.message', 'LIKE', '%' . $request->search . '%');
                }
                if ($request->from_date) {
                    $q->where('sms.sent', '>=', $request->from_date);
                }
                if ($request->to_date) {
                    $q->where('sms.sent', '<=', $request->to_date . ' 23:59:59');
                }
                if ($request->category && $request->category != 'all') {
                    $q->where('sms.category', $request->category);
                }
            })
            ->editColumn('sent', function ($row) {
                return Carbon::parse($row->sent)->format('d M, Y h:i A');
            })
            ->addColumn('time_ago', function ($row) {
                return Carbon::parse($row->sent)->diffForHumans();
            })
            ->addColumn('preview', function ($row) {
                $categoryBadge = '';
                switch ($row->category ?? 'manual') {
                    case 'mpesa': $categoryBadge = '<span class="badge badge-success mr-1">Mpesa</span>'; break;
                    case 'birthday': $categoryBadge = '<span class="badge badge-info mr-1"><i class="fas fa-birthday-cake"></i> Birthday</span>'; break;
                    case 'pledge': $categoryBadge = '<span class="badge badge-warning mr-1">Pledge</span>'; break;
                    case 'verification': $categoryBadge = '<span class="badge badge-primary mr-1"><i class="fas fa-user-check"></i> Verification</span>'; break;
                }
                return $categoryBadge . \Str::limit(strip_tags($row->message), 80, '...');
            })
            ->addColumn('action', function ($row) {
                return '<div class="text-end text-right">' .
                    '<button class="btn btn-info btn-sm btn-view-sms" data-id="' . $row->id . '"><i class="fas fa-eye"></i></button>' .
                    '</div>';
            })
            ->addIndexColumn()->escapeColumns([])->make();
    }

    public function getSmsSummary(Request $request){
        $period = $request->period ?? 'daily';
        $category = $request->category ?? 'all';
        $categoryFilter = ($category !== 'all') ? $category : null;

        // Pre-aggregate recipient counts per SMS
        $rcSql = "(SELECT sms_id, COUNT(*) AS recipient_count FROM sms_recipients GROUP BY sms_id)";

        // Build query using Query Builder to avoid raw SQL GROUP BY issues with strict mode
        $baseQuery = \DB::table('sms')
            ->leftJoin(\DB::raw("{$rcSql} AS rc"), 'rc.sms_id', '=', 'sms.id');

        if ($categoryFilter) {
            $baseQuery->where('sms.category', $categoryFilter);
        }

        if ($period == 'weekly') {
            $baseQuery->select(
                \DB::raw('YEARWEEK(sms.sent, 1) AS period_key'),
                \DB::raw('MIN(DATE(sms.sent)) AS period_start'),
                \DB::raw('MAX(DATE(sms.sent)) AS period_end'),
                \DB::raw('COUNT(*) AS total_messages'),
                \DB::raw('COALESCE(SUM(rc.recipient_count), 0) AS total_recipients')
            )->groupBy(\DB::raw('YEARWEEK(sms.sent, 1)'))
             ->orderBy(\DB::raw('YEARWEEK(sms.sent, 1)'), 'DESC');
        } elseif ($period == 'monthly') {
            $baseQuery->select(
                \DB::raw("DATE_FORMAT(sms.sent, '%Y-%m') AS period_key"),
                \DB::raw("DATE_FORMAT(sms.sent, '%M %Y') AS period_label"),
                \DB::raw('COUNT(*) AS total_messages'),
                \DB::raw('COALESCE(SUM(rc.recipient_count), 0) AS total_recipients')
            )->groupBy(\DB::raw("DATE_FORMAT(sms.sent, '%Y-%m')"))
             ->orderBy(\DB::raw("DATE_FORMAT(sms.sent, '%Y-%m')"), 'DESC');
        } else {
            $baseQuery->select(
                \DB::raw('DATE(sms.sent) AS period_key'),
                \DB::raw('DATE(sms.sent) AS date'),
                \DB::raw('COUNT(*) AS total_messages'),
                \DB::raw('COALESCE(SUM(rc.recipient_count), 0) AS total_recipients')
            )->groupBy(\DB::raw('DATE(sms.sent)'))
             ->orderBy(\DB::raw('DATE(sms.sent)'), 'DESC');
        }

        // Wrap in a derived table so DataTables' count query works cleanly
        $wrappedQuery = \DB::table(\DB::raw("({$baseQuery->toSql()}) AS grouped_sms"))
            ->mergeBindings($baseQuery);

        return DataTables::of($wrappedQuery)
            ->skipTotalRecords()
            ->addColumn('period', function ($row) use ($period) {
                if ($period == 'weekly') {
                    return Carbon::parse($row->period_start)->format('d M') . ' - ' . Carbon::parse($row->period_end)->format('d M, Y');
                } elseif ($period == 'monthly') {
                    return $row->period_label;
                }
                return Carbon::parse($row->date)->format('l, d M Y');
            })
            ->addColumn('messages', function ($row) {
                return '<span class="badge badge-primary">' . number_format($row->total_messages) . '</span>';
            })
            ->addColumn('recipients', function ($row) {
                return '<span class="badge badge-info">' . number_format($row->total_recipients) . '</span>';
            })
            ->addIndexColumn()->escapeColumns([])->make();
    }

    public function getScheduledDataTable(Request $request){
        $tid = config('app.tenant_id');
        $query = \DB::table("schedules")
            ->where('schedules.tenant_id', $tid)
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
            ->where("schedules.type", 0)
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
                return '<button class="btn btn-danger btn-sm btn-cancel-schedule" data-id="' . $row->id . '"><i class="fas fa-times"></i> Cancel</button>';
            })
            ->addIndexColumn()->escapeColumns([])->make();
    }

    public function cancelSchedule(Request $request){
        $tid = config('app.tenant_id');
        \DB::table("schedules")->where('tenant_id', $tid)->where("id", $request->id)->delete();
        return response()->json(["success" => "Scheduled message cancelled"]);
    }

    public function getBirthdaySettings(){
        $tid = config('app.tenant_id');
        $settings = \DB::table("birthday_settings")->where('tenant_id', $tid)->first();
        return response()->json($settings);
    }

    public function saveBirthdaySettings(Request $request){
        $request->validate(['message' => 'required', 'send_time' => 'required']);
        $tid = config('app.tenant_id');
        $exists = \DB::table("birthday_settings")->where('tenant_id', $tid)->first();
        $data = ['message' => $request->message, 'send_time' => $request->send_time, 'active' => $request->has('active') ? 1 : 0, 'updated_at' => Carbon::now()];
        if ($exists) {
            \DB::table("birthday_settings")->where('tenant_id', $tid)->where("id", $exists->id)->update($data);
        } else {
            \DB::table("birthday_settings")->insert(array_merge($data, ['tenant_id' => $tid, 'created_at' => Carbon::now()]));
        }
        return response()->json(["success" => "Birthday settings saved"]);
    }

    public function getMpesaSettings(){
        $tid = config('app.tenant_id');
        $settings = \DB::table("mpesa_message_settings")->where('tenant_id', $tid)->first();
        return response()->json($settings);
    }

    public function saveMpesaSettings(Request $request){
        $request->validate(['message' => 'required']);
        $tid = config('app.tenant_id');
        $exists = \DB::table("mpesa_message_settings")->where('tenant_id', $tid)->first();
        $data = ['message' => $request->message, 'active' => $request->has('active') ? 1 : 0, 'updated_at' => Carbon::now()];
        if ($exists) {
            \DB::table("mpesa_message_settings")->where('tenant_id', $tid)->where("id", $exists->id)->update($data);
        } else {
            \DB::table("mpesa_message_settings")->insert(array_merge($data, ['tenant_id' => $tid, 'created_at' => Carbon::now()]));
        }
        return response()->json(["success" => "Mpesa message settings saved"]);
    }

    public function sendSms( Request $request )
    {
        $request->validate([
            "message"=>"required",
        ]);
        $sent = 0;
        $failed = 0;
        $tid = config('app.tenant_id');
        $site_settings = $this->site_settings;
        $appname = $site_settings != null ? "" . $site_settings->name : "CHURCH APP";
        $message = $request->message;
        
        if ($request->has('time') && $request->time != null) {
            $date = \Carbon\Carbon::parse($request->time);
            if ($request->choice == 0) {
                if (empty($request['contacts'])) return back()->with("error", "No recipients selected!");
                foreach ($request['contacts'] as $contact) {
                    \DB::table('schedules')->insert(["tenant_id" => $tid, "message" => $message, "type" => 0, "user_id" => $contact, "group_id" => 0, "schedule" => $date, "status" => 0, "created_at" => \Carbon\Carbon::now()]);
                }
            } elseif ($request->choice == 1) {
                if (empty($request['groups'])) return back()->with("error", "No groups selected!");
                foreach ($request['groups'] as $contact) {
                    \DB::table('schedules')->insert(["tenant_id" => $tid, "message" => $message, "type" => 0, "user_id" => 0, "group_id" => $contact, "schedule" => $date, "status" => 0, "created_at" => \Carbon\Carbon::now()]);
                }
            } else {
                \DB::table('schedules')->insert(["tenant_id" => $tid, "message" => $message, "type" => 0, "user_id" => 0, "group_id" => 0, "schedule" => $date, "status" => 0, "created_at" => \Carbon\Carbon::now()]);
            }
            return redirect()->to('dashboard/communication/sms')->with("success", "Message scheduled for " . $date->format('d M, Y h:i A'));
        }

        if ($request->choice == 0) {
            if (empty($request['contacts'])) return back()->with("error", "No recipients selected!");
            $mid = \DB::table('sms')->insertGetId(["tenant_id" => $tid, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
            foreach ($request['contacts'] as $contact) {
                $user = \DB::table('users')->where('tenant_id', $tid)->select("id", "phone")->where('id', '=', $contact)->where("status", 1)->first();
                if ($user != null && $user->phone != null) {
                    $number = $user->phone;
                    if (substr($number, 0, 1) === '0') $number = "254" . substr($number, 1);
                    if ($this->send($number, $message)) {
                        $sent++;
                        \DB::table('sms_recipients')->insert(["tenant_id" => $tid, "recipients" => $user->id, "sms_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                    } else {
                        $failed++;
                        \Log::error("SMS failed to send to {$number} (user: {$user->id})");
                    }
                }
            }
        } else {
            if ($request->choice == 1) {
                if (empty($request['groups'])) return back()->with("error", "No groups selected!");
                foreach ($request['groups'] as $contact) {
                    $members = \DB::table("people_members")->where('people_members.tenant_id', $tid)->select("contacts.phone", "people_members.user_id")->where("people_id", $contact)->where("people_members.status", 1)
                        ->where("users.status", 1)->join("contacts", "contacts.user_id", "=", "people_members.user_id")->join("users", "users.id", "=", "contacts.user_id")->get();
                    $mid = \DB::table('sms')->insertGetId(["tenant_id" => $tid, "people_id" => $contact, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                    foreach ($members as $member) {
                        if ($this->send("254" . substr($member->phone, 1), $message)) {
                            $sent++;
                            \DB::table('sms_recipients')->insert(["tenant_id" => $tid, "recipients" => $member->user_id, "sms_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                        } else {
                            $failed++;
                            \Log::error("SMS failed to send to {$member->phone} (user: {$member->user_id})");
                        }
                    }
                }
            } else {
                $users = \DB::table('users')->where('tenant_id', $tid)->select("id", "phone")->where("status", 1)->whereNotNull("phone")->where("phone", "!=", "")->get();
                $mid = \DB::table('sms')->insertGetId(["tenant_id" => $tid, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                foreach ($users as $user) {
                    $number = $user->phone;
                    if (substr($number, 0, 1) === '0') $number = "254" . substr($number, 1);
                    if ($this->send($number, $message)) {
                        $sent++;
                        \DB::table('sms_recipients')->insert(["tenant_id" => $tid, "recipients" => $user->id, "sms_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                    } else {
                        $failed++;
                        \Log::error("SMS failed to send to {$number} (user: {$user->id})");
                    }
                }
            }
        }
        
        // Build success message
        $msg = "Sent to <strong>{$sent}</strong> Members";
        if ($failed > 0) {
            $msg .= " ({$failed} failed)";
        }
        return back()->with("success", $msg . "!");
   }

   public function sendsinglesms( Request $request )
   {
        $tid = config('app.tenant_id');
        $site_settings = $this->site_settings;
        $appname = $site_settings != null?"\n".$site_settings->name:"\nCHURCH APP";
        $message = $request->message;

        $validator = Validator::make($request->all(), [
            'numbers' => 'required',
            'message' => 'required'
        ]);

        if($validator->passes()){
            $number = "254".substr($request->input( 'numbers' ), 1);
            
            $sendResult = $this->send($number, $message);

            $user = \DB::table("contacts")->where("phone", $request->numbers)->first();

            $mid = \DB::table('sms')->insertGetId(["tenant_id" => $tid, "people_id"=>0, "message"=>$request->message, "sent"=>\Carbon\Carbon::now()]);

            if($user != null){
                \DB::table('sms_recipients')->insert(["tenant_id" => $tid, "recipients"=>$user->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
            }
            
            // Get updated credits
            $creditsResponse = app(IntegrationService::class)->checkSmsCredits();
            $smsCredits = null;
            if (is_array($creditsResponse)) {
                $smsCredits = $creditsResponse['balance'] 
                    ?? $creditsResponse['credit'] 
                    ?? $creditsResponse['credits'] 
                    ?? $creditsResponse['credits_remaining'] 
                    ?? null;
            }
            
            return response()->json([
                'success' => "Message sent to <strong>".$number."</strong>!",
                'credits' => $smsCredits,
                'send_result' => $sendResult !== false
            ]);
        } else {
            return response()->json(["error"=>"Invalid info submitted! Check before sending again"]);
        }
  }

    public function removesms(Request $request)
    {
        $message = \DB::table('sms')->where("id", $request->id)->first();
        if($message == null){
            return redirect()->back()->with("error", "Invalid Message ID");
        }else{
            if(\DB::table('sms')->where("id", $request->id)->delete()){
                \DB::table('sms_recipients')->where("sms_id", $request->id)->delete();
                return redirect()->to('dashboard/communication/sms')->with("success", "Message removed successfully");
            }else{
                return redirect()->to('dashboard/communication/sms')->with("error", "Unable to remove message");
            }
        }
    }

    public function getPhone(Request $request){
        $phone = \DB::table("contacts")->select("phone")->where("user_id", $request->id)->first();
        return json_encode($phone);
    }

    public function phoneNumbers(Request $request){
        $start = $request->limit == null?"0":intval($request->limit);
        if($request->groups == 1){
            if($request->search == null){
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))
                ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }else{
                return json_encode(\DB::table("people")->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))->where("name", "LIKE", "%".$request->search."%")
                ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }
        }else{
            if($request->search == null){
                return json_encode(\DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "users.phone")->where("users.phone", "!=", "")->whereNotNull("users.phone")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
            }else{
                return json_encode(\DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "users.phone")->where("users.phone", "!=", "")->whereNotNull("users.phone")->where(function($query) use ($request){
                    $query->where("firstname", "LIKE", "%".$request->search."%")->orWhere("lastname", "LIKE", "%".$request->search."%")->orWhere("users.phone", "LIKE", "%".$request->search."%");
                })->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
            }
        }
    }

    public function readsms(Request $request){
        $sms = \DB::table("sms")->where("sms.id", "=", $request->id)->select("sms.id", "message", "sent", "name")->leftJoin("people", "people.id", "=", "sms.people_id")->first();

        $members = \DB::table("sms_recipients")->where("sms_id", $request->id)->select("users.id", "users.firstname", "users.lastname", "users.phone", "profiles.name as image")->join("users", "users.id", "sms_recipients.recipients")
        ->join("contacts", "contacts.user_id", "sms_recipients.recipients")->leftJoin("profiles", "profiles.user_id", "=", "sms_recipients.recipients")->paginate(15);
        if($sms == null){
            return redirect()->to('sms')->with("Invalid SMS request");
        }

        return view("dashboard.communication.sms-read", ["sms"=>$sms, "members"=>$members, "recipients"=>\DB::table("sms_recipients")->where("sms_id", $request->id)->count()]);
    }

    public function readsmsJson(Request $request){
        $sms = \DB::table("sms")->where("sms.id", "=", $request->id)
            ->select("sms.id", "sms.message", "sms.sent", "sms.category", "people.name as group_name")
            ->leftJoin("people", "people.id", "=", "sms.people_id")->first();

        if($sms == null){
            return response()->json(['error' => 'SMS not found'], 404);
        }

        $recipientCount = \DB::table("sms_recipients")->where("sms_id", $request->id)->count();

        // Get registered recipients
        $members = \DB::table("sms_recipients")->where("sms_id", $request->id)
            ->where("sms_recipients.recipients", ">", 0)
            ->select("users.id", "users.firstname", "users.lastname", "users.phone")
            ->join("users", "users.id", "=", "sms_recipients.recipients")
            ->orderBy("users.firstname")->get();

        // Get phone-only recipients (not linked to a user)
        $phoneOnlyRecipients = \DB::table("sms_recipients")->where("sms_id", $request->id)
            ->where(function ($q) {
                $q->where("recipients", 0)->orWhereNull("recipients");
            })
            ->whereNotNull("phone")
            ->select("phone")
            ->get()
            ->map(function ($r) {
                return (object) ['id' => 0, 'firstname' => 'Non-member', 'lastname' => '', 'phone' => $r->phone];
            });

        $allMembers = $members->merge($phoneOnlyRecipients);

        return response()->json([
            'id' => $sms->id,
            'message' => $sms->message,
            'sent' => Carbon::parse($sms->sent)->format('d M, Y h:i A'),
            'sent_ago' => Carbon::parse($sms->sent)->diffForHumans(),
            'category' => $sms->category ?? 'manual',
            'group_name' => $sms->group_name,
            'recipient_count' => $recipientCount,
            'members' => $allMembers,
        ]);
    }

    public function pledges(Request $request){
        $request->validate([
            'amount'=>'required|numeric|min:50',
        ]);
        $send = false;
        if($request->has('notify')){
            $send = true;
        }
        if(!empty($request['contacts'])){
            foreach($request['contacts'] as $id){
                $user = \DB::table('contacts')->select("users.id", "users.firstname", "users.lastname", "contacts.phone")->where('contacts.id', $id)->join("users", "contacts.user_id", "=", "users.id")->first();
                if($user != null){
                    $message = "Dear ".strtoupper($user->firstname)." ".strtoupper($user->lastname).",\n".
                        "Thank You for pledging KSH ".number_format($request->amount). " in support of ".$request->activity_name;

                    $number = "254".substr($user->phone, 1);
                    $user->id." ".$user->firstname." ".$user->lastname." ".$user->phone;
                    if(\DB::table("pledges")->where("user_id", $user->id)->where("activity", $request->activity_id)->count() == 0){
                        if(\DB::table("pledges")->insert(["activity"=>$request->activity_id, "groups"=>0, "user_id"=>$user->id,
                            "paid"=>0, "amount"=>$request->amount, "status"=>0])){

                                if($send){
                                    $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                                    \DB::table('sms_recipients')->insert(["recipients"=>$user->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                                    $this->send($number, $message);
                                }
                        }
                    }//not to add user already committed to pledges
                }//user not null end
            }
            return back()->with("success", "Members Pledges Inserted Successfully");
        }else{
            return back()->with("error", "No members selected!");
        }
    }

    public function editPledge(Request $request){
        $request->validate([
            "amount"=>"required|min:50|numeric",
        ]);
        if($request->id > 0){
            $pledge = \DB::table("pledges")->select("pledges.user_id", "pledges.amount", "pledges.paid","activities.name")->where("pledges.id", $request->id)->join("activities",
            "activities.id", "=", "pledges.activity")->first();

            if($pledge == null){
                return back()->with("error", "Invalid request");
            }
            if($pledge->paid > $request->amount){
                return back()->with("error", "The amount is less than amount paid by user");
            }else{
                if(\DB::table("pledges")->where("id", $request->id)->update(["amount"=>$request->amount])){
                    if($request->has('notify')){
                        $user = \DB::table('contacts')->select("firstname", "contacts.phone")->where("user_id", $pledge->user_id)->leftJoin("users",
                        "users.id", "=", "contacts.user_id")->first();
                        if($user != null){
                            $message = "Dear ".strtoupper($user->firstname).",\nWe have adjusted your pledge towards ".strtoupper($pledge->name)." from ".
                            number_format($pledge->amount, 0)." to ".number_format($request->amount, 0);
                            $number = "254".substr($user->phone, 1);
                            $remove[] = "'";
                            $remove[] = '"';

                            $message = str_replace($remove, "", $message);

                            if($this->send($number, $message)){
                                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                                \DB::table('sms_recipients')->insert(["recipients"=>$pledge->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                            }
                        }
                    }
                    return back()->with("success", "Pledge Updated successfully");
                }else{
                    return back()->with("error", "Unable to update");
                }
            }
        }else{
            return back()->with("error", "Invalid pledge");
        }
    }

    public function pledgeReminder(Request $request){
        if($request->id > 0){
            $pledge = \DB::table('pledges')->select("contacts.phone", "pledges.user_id")->where("pledges.id", $request->id)->leftJoin("contacts", "contacts.user_id", "=", "pledges.user_id")
            ->leftJoin("users", "users.id", "=", "pledges.user_id")->first();

            $number = "254".substr($pledge->phone, 1);
            $message = $request->message;

            $remove[] = "'";
            $remove[] = '"';

            $message = str_replace($remove, "", $message);
            if($this->send($number, $message)){
                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                \DB::table('sms_recipients')->insert(["recipients"=>$pledge->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                return back()->with("success", "Reminder has been sent");
            }else{
                return back()->with("error", "Unable to send reminder");
            }
        }else{
            //send to all in this form
            $pledges = \DB::table("pledges")->select("users.id", "users.firstname", "users.lastname", "contacts.phone", "activities.name", "pledges.paid", "pledges.amount")
            ->where("activity", $request->activity_id)->leftJoin("activities", "activities.id", "=", "pledges.activity")->leftJoin("contacts", "contacts.user_id", "=", "pledges.user_id")
            ->leftJoin("users", "users.id", "=", "pledges.user_id")->get();
            $remove[] = "'";
            $remove[] = '"';

            $message = str_replace($remove, "", $request->message);

            foreach($pledges as $pledge){
                $balance = $pledge->amount - $pledge->paid;
                $m = str_replace("{{NAME}}", strtoupper($pledge->firstname), $message);
                $m = str_replace("{{ACTIVITY}}", strtoupper($pledge->name), $m);
                $m = str_replace("{{BALANCE}}", number_format($balance, 0), $m);
                $number = "254".substr($pledge->phone,1);
                if($this->send($number, $m)){
                    $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$m, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                    \DB::table('sms_recipients')->insert(["recipients"=>$pledge->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
                }
            }
            return back()->with("success", "Message sent successfully");
        }
    }

    public function paypledge(Request $request){
        $pledge = \DB::table('pledges')->select("users.id", "users.firstname", "users.lastname", "pledges.paid", "activities.name",
        "pledges.amount", "contacts.phone")->where("pledges.id", $request->id)->leftJoin("users", "users.id", "=", "pledges.user_id")
        ->leftJoin("contacts", "contacts.user_id", "=", "users.id")->leftJoin("activities","pledges.activity","=", "activities.id")->first();

        //return json_encode($pledge->id);

        $paid = $pledge->paid + doubleval($request->payments);
        if(\DB::table("pledges")->where("id", $request->id)->update(["paid"=>$paid])){
            $number = "254".substr($pledge->phone, 1);
            $message = "Dear ".strtoupper($pledge->firstname).",\nThank you for honouring your pledge towards ".strtoupper($pledge->name).". KES ".number_format($request->payments,0)." has been recieved";
            if($paid >= $pledge->amount){
                $message .= ". Your pledge is FULLY settled. May GOD bless and increase you resources.";
            }else{
                $message .= ". Your balance now is KES ".number_format($pledge->amount - $paid).". May GOD bless and increase you resources.";
            }

            if($this->send($number, $message)){
                $mid = \DB::table('sms')->insertGetId(["people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                \DB::table('sms_recipients')->insert(["recipients"=>$pledge->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
            }
            return back()->with("success", "Balance updated!");
        }else{
            return back()->with("error", "unable to update balance");
        }
    }

    public function addpledgegroupmembers(Request $request){
        if(!empty($request['contacts'])){
            $group = \DB::table("groups")->where("groups.id", $request->group_id)->first();

            foreach($request['contacts'] as $contact){
                if(\DB::table("pledges")->where("user_id", $contact)->where("groups", $request->group_id)->count() == 0){
                    \DB::table("pledges")->insert(["activity"=>$group->activity, "groups"=>$request->group_id, "user_id"=>$contact, "paid"=>0, "amount"=>0, "status"=>0]);
                }
            }

            //update amounts;
            $groups = \DB::table("groups")->select("groups.id", "groups.name", "groups.amount", \DB::Raw("sum(pledges.amount) as pledged"))->where("groups.id", $request->group_id)
            ->leftJoin("pledges", "pledges.groups", "=", "groups.id")->groupBy("groups.name", "groups.amount", "groups.id")->first();
            $members = \DB::table("pledges")->where("groups", $request->group_id)->count();

            $amount = round(($groups->amount/$members), 0);
            foreach(\DB::table("pledges")->where("groups", $request->group_id)->get() as $pledge){
                \DB::table("pledges")->where("id", $pledge->id)->update(['amount'=>$amount]);
            }
            return back()->with("success", "Members Updated successfully!");
        }else{
            return back()->with("error", "Choose participants for this group!");
        }

    }

    public function send($number, $message){
        $tid = config('app.tenant_id');
        
        // Send SMS via IntegrationService
        $integrationService = app(\App\Services\IntegrationService::class);
        $result = $integrationService->sendSms($number, $message);
        
        // Log usage for tracking (optional - doesn't affect actual credits)
        if ($result !== false) {
            // Only update usage stats if credits table exists
            try {
                \DB::table('credits')
                    ->where('tenant_id', $tid)
                    ->where('type', 'sms')
                    ->increment('used', 1);
                
                \DB::table('credits')
                    ->where('tenant_id', $tid)
                    ->where('type', 'sms')
                    ->update(['last_used_at' => now()]);
            } catch (\Exception $e) {
                // Ignore errors if table doesn't exist or has issues
            }
        } else {
            \Log::error("SMS send failed for tenant {$tid}, number: {$number}");
        }
        
        return $result;
    }
}
