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
        
        // Handle page length parameter
        if ($request->has('length') && in_array($request->length, [10, 25, 50, 100])) {
            // DataTables handles this automatically, but we validate here
        }

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
                $resendBtn = '';
                if (auth()->user()->hasPermissionTo('Resend SMS')) {
                    $resendBtn = '<button class="btn btn-warning btn-sm btn-resend-sms ml-1" data-id="' . $row->id . '" title="Resend"><i class="fas fa-redo"></i></button>';
                }
                return '<div class="text-end text-right">' .
                    '<button class="btn btn-info btn-sm btn-view-sms" data-id="' . $row->id . '"><i class="fas fa-eye"></i></button>' .
                    $resendBtn .
                    '</div>';
            })
            ->addIndexColumn()->escapeColumns([])->make();
    }

    public function getSmsSummary(Request $request){
        $period = $request->period ?? 'daily';
        $category = $request->category ?? 'all';
        $categoryFilter = ($category !== 'all') ? $category : null;
        $tid = config('app.tenant_id');

        // Use parameterized placeholder to prevent SQL injection
        $categoryWhere = $categoryFilter ? 'AND s.category = ?' : '';

        if ($period == 'weekly') {
            $sql = "
                SELECT 
                    period_key,
                    period_start,
                    period_end,
                    total_messages,
                    total_recipients
                FROM (
                    SELECT 
                        YEARWEEK(s.sent, 1) AS period_key,
                        MIN(DATE(s.sent)) AS period_start,
                        MAX(DATE(s.sent)) AS period_end,
                        COUNT(*) AS total_messages,
                        COALESCE(SUM(rc.recipient_count), 0) AS total_recipients
                    FROM sms s
                    LEFT JOIN (
                        SELECT sms_id, COUNT(*) AS recipient_count 
                        FROM sms_recipients 
                        WHERE tenant_id = ? 
                        GROUP BY sms_id
                    ) rc ON rc.sms_id = s.id
                    WHERE s.tenant_id = ? {$categoryWhere}
                    GROUP BY YEARWEEK(s.sent, 1)
                ) AS grouped_data
                ORDER BY period_start DESC
            ";
            $bindings = $categoryFilter ? [$tid, $tid, $categoryFilter] : [$tid, $tid];
        } elseif ($period == 'monthly') {
            $sql = "
                SELECT 
                    period_key,
                    period_label,
                    total_messages,
                    total_recipients
                FROM (
                    SELECT 
                        DATE_FORMAT(s.sent, '%Y-%m') AS period_key,
                        MIN(DATE_FORMAT(s.sent, '%M %Y')) AS period_label,
                        COUNT(*) AS total_messages,
                        COALESCE(SUM(rc.recipient_count), 0) AS total_recipients
                    FROM sms s
                    LEFT JOIN (
                        SELECT sms_id, COUNT(*) AS recipient_count 
                        FROM sms_recipients 
                        WHERE tenant_id = ? 
                        GROUP BY sms_id
                    ) rc ON rc.sms_id = s.id
                    WHERE s.tenant_id = ? {$categoryWhere}
                    GROUP BY DATE_FORMAT(s.sent, '%Y-%m')
                ) AS grouped_data
                ORDER BY period_key DESC
            ";
            $bindings = $categoryFilter ? [$tid, $tid, $categoryFilter] : [$tid, $tid];
        } else {
            // Daily view - use MIN() to satisfy strict mode
            $sql = "
                SELECT 
                    period_key,
                    period_date AS date,
                    total_messages,
                    total_recipients
                FROM (
                    SELECT 
                        DATE(s.sent) AS period_key,
                        MIN(DATE(s.sent)) AS period_date,
                        COUNT(*) AS total_messages,
                        COALESCE(SUM(rc.recipient_count), 0) AS total_recipients
                    FROM sms s
                    LEFT JOIN (
                        SELECT sms_id, COUNT(*) AS recipient_count 
                        FROM sms_recipients 
                        WHERE tenant_id = ? 
                        GROUP BY sms_id
                    ) rc ON rc.sms_id = s.id
                    WHERE s.tenant_id = ? {$categoryWhere}
                    GROUP BY DATE(s.sent)
                ) AS grouped_data
                ORDER BY period_date DESC
            ";
            $bindings = $categoryFilter ? [$tid, $tid, $categoryFilter] : [$tid, $tid];
        }

        $results = collect(\DB::select($sql, $bindings));

        // Transform results to include the required columns directly
        $transformed = $results->map(function ($row) use ($period) {
            // Format period based on period type
            if ($period == 'weekly') {
                $periodValue = Carbon::parse($row->period_start)->format('d M') . ' - ' . Carbon::parse($row->period_end)->format('d M, Y');
            } elseif ($period == 'monthly') {
                $periodValue = $row->period_label;
            } else {
                $periodValue = Carbon::parse($row->date)->format('l, d M Y');
            }
            
            return [
                'period_key' => $row->period_key,
                'period' => $periodValue,
                'messages' => '<span class="badge badge-primary">' . number_format($row->total_messages) . '</span>',
                'recipients' => '<span class="badge badge-info">' . number_format($row->total_recipients) . '</span>',
                'total_messages' => $row->total_messages,
                'total_recipients' => $row->total_recipients,
            ];
        });

        return DataTables::of($transformed)
            ->skipTotalRecords()
            ->escapeColumns([])
            ->make();
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

    public function getManualTitheSettings(){
        $tid = config('app.tenant_id');
        $settings = \DB::table("manual_tithe_message_settings")->where('tenant_id', $tid)->first();
        return response()->json($settings);
    }

    public function saveManualTitheSettings(Request $request){
        $request->validate(['message' => 'required']);
        $tid = config('app.tenant_id');
        $exists = \DB::table("manual_tithe_message_settings")->where('tenant_id', $tid)->first();
        $data = ['message' => $request->message, 'active' => $request->has('active') ? 1 : 0, 'updated_at' => Carbon::now()];
        if ($exists) {
            \DB::table("manual_tithe_message_settings")->where('tenant_id', $tid)->where("id", $exists->id)->update($data);
        } else {
            \DB::table("manual_tithe_message_settings")->insert(array_merge($data, ['tenant_id' => $tid, 'created_at' => Carbon::now()]));
        }
        return response()->json(["success" => "Manual tithe message settings saved"]);
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
                $scheduleRows = [];
                foreach ($request['contacts'] as $contact) {
                    $scheduleRows[] = ["tenant_id" => $tid, "message" => $message, "type" => 0, "user_id" => intval($contact), "group_id" => 0, "schedule" => $date, "status" => 0, "created_at" => \Carbon\Carbon::now()];
                }
                \DB::table('schedules')->insert($scheduleRows);
            } elseif ($request->choice == 1) {
                if (empty($request['groups'])) return back()->with("error", "No groups selected!");
                $scheduleRows = [];
                foreach ($request['groups'] as $contact) {
                    $scheduleRows[] = ["tenant_id" => $tid, "message" => $message, "type" => 0, "user_id" => 0, "group_id" => intval($contact), "schedule" => $date, "status" => 0, "created_at" => \Carbon\Carbon::now()];
                }
                \DB::table('schedules')->insert($scheduleRows);
            } else {
                \DB::table('schedules')->insert(["tenant_id" => $tid, "message" => $message, "type" => 0, "user_id" => 0, "group_id" => 0, "schedule" => $date, "status" => 0, "created_at" => \Carbon\Carbon::now()]);
            }
            return redirect()->to('dashboard/communication/sms')->with("success", "Message scheduled for " . $date->format('d M, Y h:i A'));
        }

        if ($request->choice == 0) {
            if (empty($request['contacts'])) return back()->with("error", "No recipients selected!");
            // Bulk-fetch all selected users in one query instead of N individual queries
            $users = \DB::table('users')->where('tenant_id', $tid)->whereIn('id', $request['contacts'])
                ->where('status', 1)->select('id', 'phone')->get()->keyBy('id');
            $mid = \DB::table('sms')->insertGetId(["tenant_id" => $tid, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
            $recipientRows = [];
            foreach ($request['contacts'] as $contact) {
                $user = $users->get($contact);
                if ($user && $user->phone) {
                    $number = $user->phone;
                    if (substr($number, 0, 1) === '0') $number = "254" . substr($number, 1);
                    if ($this->send($number, $message)) {
                        $sent++;
                        $recipientRows[] = ["tenant_id" => $tid, "recipients" => $user->id, "sms_id" => $mid, "sent" => \Carbon\Carbon::now()];
                    } else {
                        $failed++;
                        \Log::error("SMS failed to send to {$number} (user: {$user->id})");
                    }
                }
            }
            if (!empty($recipientRows)) \DB::table('sms_recipients')->insert($recipientRows);
        } else {
            if ($request->choice == 1) {
                if (empty($request['groups'])) return back()->with("error", "No groups selected!");
                foreach ($request['groups'] as $contact) {
                    $members = \DB::table("people_members")->where('people_members.tenant_id', $tid)->select("contacts.phone", "people_members.user_id")->where("people_id", $contact)->where("people_members.status", 1)
                        ->where("users.status", 1)->join("contacts", "contacts.user_id", "=", "people_members.user_id")->join("users", "users.id", "=", "contacts.user_id")->get();
                    $mid = \DB::table('sms')->insertGetId(["tenant_id" => $tid, "people_id" => $contact, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                    $recipientRows = [];
                    foreach ($members as $member) {
                        if ($this->send("254" . substr($member->phone, 1), $message)) {
                            $sent++;
                            $recipientRows[] = ["tenant_id" => $tid, "recipients" => $member->user_id, "sms_id" => $mid, "sent" => \Carbon\Carbon::now()];
                        } else {
                            $failed++;
                            \Log::error("SMS failed to send to {$member->phone} (user: {$member->user_id})");
                        }
                    }
                    if (!empty($recipientRows)) \DB::table('sms_recipients')->insert($recipientRows);
                }
            } else {
                $users = \DB::table('users')->where('tenant_id', $tid)->select("id", "phone")->where("status", 1)->whereNotNull("phone")->where("phone", "!=", "")->get();
                $mid = \DB::table('sms')->insertGetId(["tenant_id" => $tid, "people_id" => 0, "message" => $request->message, "sent" => \Carbon\Carbon::now()]);
                $recipientRows = [];
                foreach ($users as $user) {
                    $number = $user->phone;
                    if (substr($number, 0, 1) === '0') $number = "254" . substr($number, 1);
                    if ($this->send($number, $message)) {
                        $sent++;
                        $recipientRows[] = ["tenant_id" => $tid, "recipients" => $user->id, "sms_id" => $mid, "sent" => \Carbon\Carbon::now()];
                    } else {
                        $failed++;
                        \Log::error("SMS failed to send to {$number} (user: {$user->id})");
                    }
                }
                if (!empty($recipientRows)) \DB::table('sms_recipients')->insert($recipientRows);
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
        $tid = config('app.tenant_id');
        $message = \DB::table('sms')->where("tenant_id", $tid)->where("id", $request->id)->first();
        if($message == null){
            return redirect()->back()->with("error", "Invalid Message ID");
        }else{
            if(\DB::table('sms')->where("tenant_id", $tid)->where("id", $request->id)->delete()){
                \DB::table('sms_recipients')->where("tenant_id", $tid)->where("sms_id", $request->id)->delete();
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
        $tid   = config('app.tenant_id');
        $start = $request->limit == null ? "0" : intval($request->limit);
        if($request->groups == 1){
            if($request->search == null){
                return json_encode(\DB::table("people")->where("people.tenant_id", $tid)->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))
                ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }else{
                return json_encode(\DB::table("people")->where("people.tenant_id", $tid)->select("people.id", "people.name", "people.user_group", \DB::Raw("count(people.id) as members"))->where("name", "LIKE", "%".$request->search."%")
                ->leftJoin("people_members", "people_members.people_id", "=", "people.id")->groupBy('people.id')->groupBy('people.name')->groupBy('people.user_group')->
                orderBy("people.name", "ASC")->skip($start)->take(10)->get());
            }
        }else{
            if($request->search == null){
                return json_encode(\DB::table("users")->where("users.tenant_id", $tid)->select("users.id", "users.firstname", "users.lastname", "users.email", "users.phone")->where("users.phone", "!=", "")->whereNotNull("users.phone")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
            }else{
                return json_encode(\DB::table("users")->where("users.tenant_id", $tid)->select("users.id", "users.firstname", "users.lastname", "users.email", "users.phone")->where("users.phone", "!=", "")->whereNotNull("users.phone")->where(function($query) use ($request){
                    $query->where("firstname", "LIKE", "%".$request->search."%")->orWhere("lastname", "LIKE", "%".$request->search."%")->orWhere("users.phone", "LIKE", "%".$request->search."%");
                })->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
            }
        }
    }

    public function readsms(Request $request){
        $tid = config('app.tenant_id');
        $sms = \DB::table("sms")->where("sms.tenant_id", $tid)->where("sms.id", "=", $request->id)->select("sms.id", "message", "sent", "name")->leftJoin("people", "people.id", "=", "sms.people_id")->first();

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
        $tid  = config('app.tenant_id');
        $send = false;
        if($request->has('notify')){
            $send = true;
        }
        if(!empty($request['contacts'])){
            foreach($request['contacts'] as $id){
                $user = \DB::table('contacts')->select("users.id", "users.firstname", "users.lastname", "contacts.phone")->where('contacts.tenant_id', $tid)->where('contacts.id', $id)->join("users", "contacts.user_id", "=", "users.id")->first();
                if($user != null){
                    $message = "Dear ".strtoupper($user->firstname)." ".strtoupper($user->lastname).",\n".
                        "Thank You for pledging KSH ".number_format($request->amount). " in support of ".$request->activity_name;

                    $number = "254".substr($user->phone, 1);
                    if(\DB::table("pledges")->where("tenant_id", $tid)->where("user_id", $user->id)->where("activity", $request->activity_id)->count() == 0){
                        if(\DB::table("pledges")->insert(["tenant_id"=>$tid, "activity"=>$request->activity_id, "groups"=>0, "user_id"=>$user->id,
                            "paid"=>0, "amount"=>$request->amount, "status"=>0])){

                                if($send){
                                    $mid = \DB::table('sms')->insertGetId(["tenant_id"=>$tid, "people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                                    \DB::table('sms_recipients')->insert(["tenant_id"=>$tid, "recipients"=>$user->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
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

                            $tid2 = config('app.tenant_id');
                        if($this->send($number, $message)){
                                $mid = \DB::table('sms')->insertGetId(["tenant_id"=>$tid2, "people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                                \DB::table('sms_recipients')->insert(["tenant_id"=>$tid2, "recipients"=>$pledge->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
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
            $tid = config('app.tenant_id');
            if($this->send($number, $message)){
                $mid = \DB::table('sms')->insertGetId(["tenant_id"=>$tid, "people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                \DB::table('sms_recipients')->insert(["tenant_id"=>$tid, "recipients"=>$pledge->user_id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
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
                $tid = config('app.tenant_id');
                if($this->send($number, $m)){
                    $mid = \DB::table('sms')->insertGetId(["tenant_id"=>$tid, "people_id"=>0, "message"=>$m, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                    \DB::table('sms_recipients')->insert(["tenant_id"=>$tid, "recipients"=>$pledge->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
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

            $tid = config('app.tenant_id');
            if($this->send($number, $message)){
                $mid = \DB::table('sms')->insertGetId(["tenant_id"=>$tid, "people_id"=>0, "message"=>$message, "category"=>"pledge", "sent"=>\Carbon\Carbon::now()]);
                \DB::table('sms_recipients')->insert(["tenant_id"=>$tid, "recipients"=>$pledge->id, "sms_id"=>$mid, "sent"=>\Carbon\Carbon::now()]);
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

    /**
     * Resend an SMS message to the same recipients.
     * Requires 'Resend SMS' permission.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendSms(Request $request)
    {
        try {
            // Check permission
            if (!auth()->user()->hasPermissionTo('Resend SMS')) {
                return response()->json(['error' => 'You do not have permission to resend SMS messages.'], 403);
            }

            $request->validate([
                'sms_id' => 'required|integer|exists:sms,id',
                'message' => 'required|string|min:1|max:1600',
            ]);

            $tid = config('app.tenant_id');
            $userId = auth()->id();

        // Get original SMS details
        $originalSms = \DB::table('sms')
            ->where('id', $request->sms_id)
            ->where('tenant_id', $tid)
            ->first();

        if (!$originalSms) {
            return response()->json(['error' => 'Original SMS not found.'], 404);
        }

        // Get recipients from original SMS
        $recipients = \DB::table('sms_recipients')
            ->where('sms_id', $request->sms_id)
            ->where('tenant_id', $tid)
            ->get();

        if ($recipients->isEmpty()) {
            return response()->json(['error' => 'No recipients found for this SMS.'], 404);
        }

        $sent = 0;
        $failed = 0;
        $message = $request->message;

        // Create new SMS record
        $newSmsId = \DB::table('sms')->insertGetId([
            'tenant_id' => $tid,
            'people_id' => $originalSms->people_id,
            'message' => $message,
            'category' => $originalSms->category ?? 'resend',
            'sent' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Send to each recipient
        foreach ($recipients as $recipient) {
            $phone = null;
            $recipientUserId = $recipient->recipients;

            // If recipient is a user, get their phone
            if ($recipientUserId > 0) {
                $user = \DB::table('users')
                    ->where('id', $recipientUserId)
                    ->where('tenant_id', $tid)
                    ->first();
                
                if ($user && $user->phone) {
                    $phone = $user->phone;
                    if (substr($phone, 0, 1) === '0') {
                        $phone = '254' . substr($phone, 1);
                    }
                }
            } elseif (!empty($recipient->phone)) {
                // Use stored phone number for non-user recipients
                $phone = $recipient->phone;
            }

            if ($phone) {
                $result = $this->send($phone, $message);
                
                if ($result !== false) {
                    $sent++;
                    \DB::table('sms_recipients')->insert([
                        'tenant_id' => $tid,
                        'recipients' => $recipientUserId,
                        'sms_id' => $newSmsId,
                        'phone' => $phone,
                        'sent' => Carbon::now(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                } else {
                    $failed++;
                }
            } else {
                $failed++;
            }
        }

        // Log the resend action
        \Log::info("SMS resent", [
            'tenant_id' => $tid,
            'user_id' => $userId,
            'original_sms_id' => $request->sms_id,
            'new_sms_id' => $newSmsId,
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return response()->json([
            'success' => true,
            'message' => "SMS resent successfully! Sent to {$sent} recipients" . ($failed > 0 ? " ({$failed} failed)" : ''),
            'new_sms_id' => $newSmsId,
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);
        } catch (\Exception $e) {
            \Log::error("SMS resend failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to resend SMS: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get SMS details for the resend modal.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSmsForResend(Request $request, $id)
    {
        $tid = config('app.tenant_id');
        
        $sms = \DB::table('sms')
            ->where('id', $id)
            ->where('tenant_id', $tid)
            ->select('id', 'message', 'category', 'sent')
            ->first();

        if (!$sms) {
            return response()->json(['error' => 'SMS not found.'], 404);
        }

        // Get recipient count
        $recipientCount = \DB::table('sms_recipients')
            ->where('sms_id', $id)
            ->where('tenant_id', $tid)
            ->count();

        return response()->json([
            'id' => $sms->id,
            'message' => $sms->message,
            'category' => $sms->category ?? 'manual',
            'sent' => Carbon::parse($sms->sent)->format('d M, Y h:i A'),
            'recipient_count' => $recipientCount,
        ]);
    }

    /**
     * Bulk resend multiple SMS messages.
     * Requires 'Resend SMS' permission.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkResend(Request $request)
    {
        try {
            // Check permission
            if (!auth()->user()->hasPermissionTo('Resend SMS')) {
                return response()->json(['error' => 'You do not have permission to resend SMS messages.'], 403);
            }

            $request->validate([
                'sms_ids' => 'required|array|min:1|max:50',
                'sms_ids.*' => 'integer|exists:sms,id',
            ]);

            $tid = config('app.tenant_id');
            $userId = auth()->id();
            $smsIds = $request->sms_ids;

            $results = [
                'processed' => 0,
                'sent' => 0,
                'failed' => 0,
                'details' => [],
            ];

            foreach ($smsIds as $smsId) {
                // Get original SMS details
                $originalSms = \DB::table('sms')
                    ->where('id', $smsId)
                    ->where('tenant_id', $tid)
                    ->first();

                if (!$originalSms) {
                    $results['details'][] = [
                        'sms_id' => $smsId,
                        'status' => 'error',
                        'message' => 'SMS not found',
                    ];
                    $results['failed']++;
                    continue;
                }

                // Get recipients from original SMS
                $recipients = \DB::table('sms_recipients')
                    ->where('sms_id', $smsId)
                    ->where('tenant_id', $tid)
                    ->get();

                if ($recipients->isEmpty()) {
                    $results['details'][] = [
                        'sms_id' => $smsId,
                        'status' => 'error',
                        'message' => 'No recipients found',
                    ];
                    $results['failed']++;
                    continue;
                }

                $message = $originalSms->message;
                $smsSent = 0;
                $smsFailed = 0;

                // Create new SMS record
                $newSmsId = \DB::table('sms')->insertGetId([
                    'tenant_id' => $tid,
                    'people_id' => $originalSms->people_id,
                    'message' => $message,
                    'category' => $originalSms->category ?? 'resend',
                    'sent' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Send to each recipient
                foreach ($recipients as $recipient) {
                    $phone = null;
                    $recipientUserId = $recipient->recipients;

                    // If recipient is a user, get their phone
                    if ($recipientUserId > 0) {
                        $user = \DB::table('users')
                            ->where('id', $recipientUserId)
                            ->where('tenant_id', $tid)
                            ->first();
                        
                        if ($user && $user->phone) {
                            $phone = $user->phone;
                            if (substr($phone, 0, 1) === '0') {
                                $phone = '254' . substr($phone, 1);
                            }
                        }
                    } elseif (!empty($recipient->phone)) {
                        // Use stored phone number for non-user recipients
                        $phone = $recipient->phone;
                    }

                    if ($phone) {
                        $result = $this->send($phone, $message);
                        
                        if ($result !== false) {
                            $smsSent++;
                            \DB::table('sms_recipients')->insert([
                                'tenant_id' => $tid,
                                'recipients' => $recipientUserId,
                                'sms_id' => $newSmsId,
                                'phone' => $phone,
                                'sent' => Carbon::now(),
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        } else {
                            $smsFailed++;
                        }
                    } else {
                        $smsFailed++;
                    }
                }

                $results['processed']++;
                $results['sent'] += $smsSent;
                $results['failed'] += $smsFailed;
                $results['details'][] = [
                    'sms_id' => $smsId,
                    'new_sms_id' => $newSmsId,
                    'status' => 'success',
                    'sent' => $smsSent,
                    'failed' => $smsFailed,
                ];
            }

            // Log the bulk resend action
            \Log::info("Bulk SMS resent", [
                'tenant_id' => $tid,
                'user_id' => $userId,
                'sms_count' => count($smsIds),
                'total_sent' => $results['sent'],
                'total_failed' => $results['failed'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "Bulk resend completed! {$results['processed']} SMS processed, {$results['sent']} messages sent" . ($results['failed'] > 0 ? " ({$results['failed']} failed)" : ''),
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            \Log::error("Bulk SMS resend failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to bulk resend SMS: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk delete/archive multiple SMS messages.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'sms_ids' => 'required|array|min:1|max:100',
                'sms_ids.*' => 'integer|exists:sms,id',
            ]);

            $tid = config('app.tenant_id');
            $smsIds = $request->sms_ids;

            // Delete SMS recipients first (foreign key constraint)
            $deletedRecipients = \DB::table('sms_recipients')
                ->where('tenant_id', $tid)
                ->whereIn('sms_id', $smsIds)
                ->delete();

            // Delete SMS records
            $deletedSms = \DB::table('sms')
                ->where('tenant_id', $tid)
                ->whereIn('id', $smsIds)
                ->delete();

            \Log::info("Bulk SMS deleted", [
                'tenant_id' => $tid,
                'user_id' => auth()->id(),
                'sms_count' => $deletedSms,
                'recipient_count' => $deletedRecipients,
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$deletedSms} SMS message(s) deleted successfully",
                'deleted_count' => $deletedSms,
            ]);

        } catch (\Exception $e) {
            \Log::error("Bulk SMS delete failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Failed to delete SMS: ' . $e->getMessage()], 500);
        }
    }
}
