<?php

namespace App\Http\Controllers\Dashboard\Finance;

use App\Http\Controllers\APIs\MpesaAPIController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Imports\PledgesImport;
use App\Models\Funds;
use App\Models\MissingMpesaPhone;
use App\Models\MpesaPhone;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class FinancialController extends DashboardController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Finances']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request)
    {
        $sources = \DB::table('sources')->get();
        $modes = \DB::table('modeofpayment')->get();
        //$users = \DB::table("users")->select("id", "firstname", "lastname", "email")->get();
/*

            $funds = Funds::select(["funds.id", "funds.amount", "sources.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description", "funds.created_at", "modeofpayment.name as mode"])
            ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")->leftJoin("modeofpayment", "modeofpayment.id", "=", "funds.mode")->orderBy('funds.id', 'desc')->paginate('15');
            if($request->has('from') && $request->has('to')){
                $funds = Funds::select(["funds.id", "funds.amount", "sources.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description", "funds.created_at", "modeofpayment.name as mode"])
                ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")->where("funds.created_at", ">=", \Carbon\Carbon::parse($request->from))
                ->where("funds.created_at", "<=", \Carbon\Carbon::parse($request->to))->leftJoin("modeofpayment", "modeofpayment.id", "=", "funds.mode")->orderBy('funds.id', 'desc')->paginate('15');
            }elseif($request->has('from')){
                $funds = Funds::select(["funds.id", "funds.amount", "sources.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description", "funds.created_at", "modeofpayment.name as mode"])
                ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")->leftJoin("modeofpayment", "modeofpayment.id", "=", "funds.mode")->where("funds.created_at", ">=", \Carbon\Carbon::parse($request->from))
                ->orderBy('funds.id', 'desc')->paginate('15');
            }elseif($request->has("to")){
                $funds = Funds::select(["funds.id", "funds.amount", "sources.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description", "funds.created_at", "modeofpayment.name as mode"])
                ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")->leftJoin("modeofpayment", "modeofpayment.id", "=", "funds.mode")
                ->where("funds.created_at", "<=", \Carbon\Carbon::parse($request->to))->orderBy('funds.id', 'desc')->paginate('15');
            }*/

        return view('dashboard.finance.funds', ['sources' => $sources, 'modes' => $modes]);

    }

    public function overview()
    {
        $tid = config('app.tenant_id');
        $collected = \DB::table('funds')->where('funds.tenant_id', $tid)->where('sources.ftype', 0)->join("sources", "sources.id", "funds.source")->sum('funds.amount')
            + \DB::table('pledges')->where('tenant_id', $tid)->where('status', 1)->sum('amount')
            + \DB::table('purchases')->where('tenant_id', $tid)->where('status', 1)->sum('amount');
        $spent = \DB::table('funds')->where('funds.tenant_id', $tid)->where('sources.ftype', 1)->join("sources", "sources.id", "funds.source")->sum('funds.amount');
        $donation = \DB::table('donations')->where('tenant_id', $tid)->sum('amount');
        $sources = \DB::table("sources")->where('tenant_id', $tid)->get();
        return view('dashboard.finance.overview')->with('collected', $collected)->with("sources", $sources)->with('spent', $spent)->with('donation', $donation);
    }

    public function budgetpdf(Request $request)
    {
        if (!empty($request->id)) {
            $sources = \DB::table('sources')->get();

            $budget = \DB::table('budget')->select(
                "budget.id as bid",
                "sources.ftype",
                "budget_items.id",
                "budget.name as bname",
                "sources.id as source",
                "sources.name",
                "budget_items.choice",
                "budget_items.amount",
                "budget_items.from",
                "budget_items.to",
                "budget.start",
                "budget.end"
            )->where('budget.id', $request->id)
                ->leftJoin("budget_items", "budget_items.budget_id", "=", "budget.id")->leftJoin("sources", "sources.id", "=", "budget_items.source")->get();
            //return json_encode($budget);

            $mybudget = \DB::table("budget")->where("id", $request->id)->first();
            if ($mybudget == null) {
                return back()->with("error", "Invalid budget ID");
            } else {
                $collected = \DB::table("funds")->select("sources.name", \DB::Raw("sum(amount) as collected"))->where("funds.created_at", ">=", $mybudget->start)
                    ->where("funds.created_at", "<=", $mybudget->end)->leftJoin("sources", "sources.id", "=", "funds.source")->groupBy("sources.name")->get();
                //return json_encode($collected->toArray());
            }


            $pdf = PDF::loadView('admin.pdfs.budget', ['sources' => $sources, "budget" => $budget, "collected" => $collected]);

            return $pdf->download($budget->first()->bname . '.pdf');
            //return view('admin.pdfs.budget', ['sources'=>$sources, "budget"=>$budget, "collected"=>$collected]);
        } else {
            return back()->with("error", "Invalid request");
        }
    }
    /*//Will use it later
    public function downloadPDF($id) {
        $show = Disneyplus::find($id);
        $pdf = PDF::loadView('pdf', compact('show'));

        return $pdf->download('disney.pdf');
    }*/
    public function budgets(Request $request)
    {
        $tid = config('app.tenant_id');
        $sources = \DB::table('sources')->where('tenant_id', $tid)->get();
        return view('dashboard.finance.budgets', ['sources' => $sources, "budget" => \DB::table('budget')->where('tenant_id', $tid)->orderBy('id', 'DESC')->paginate(15)]);
    }
    public function budget(Request $request)
    {
        $tid = config('app.tenant_id');
        $sources = \DB::table('sources')->where('tenant_id', $tid)->get();
        $budget = \DB::table("budget")->where("tenant_id", $tid)->where("budget.id", $request->id)->first();
        if ($budget == null) {
            return redirect()->to("budget")->with("error", "Invalid budget ID");
        }
        $budget_items = \DB::table('budget_items')->select("sources.id", "sources.name", "sources.ftype", "budget_items.amount")->where('budget_items.budget_id', $request->id)
            ->leftJoin("sources", "sources.id", "=", "budget_items.source")->get();
        return view('dashboard.finance.budget-edit', ['sources' => $sources, "budget_items" => $budget_items, "budget" => $budget, "bid" => $request->id]);
    }

    public function previewbudget(Request $request)
    {
        $sources = \DB::table('sources')->get();

        $budget = \DB::table('budget')->select(
            "budget.id as bid",
            "sources.ftype",
            "budget_items.id",
            "budget.name as bname",
            "sources.id as source",
            "sources.name",
            "budget_items.choice",
            "budget_items.amount",
            "budget_items.from",
            "budget_items.to",
            "budget.start",
            "budget.end"
        )->where('budget.id', $request->id)
            ->leftJoin("budget_items", "budget_items.budget_id", "=", "budget.id")->leftJoin("sources", "sources.id", "=", "budget_items.source")->get();
        //return json_encode($budget);

        $mybudget = \DB::table("budget")->where("id", $request->id)->first();
        if ($mybudget == null) {
            return back()->with("error", "Invalid budget ID");
        } else {
            $collected = \DB::table("funds")->select("sources.name", \DB::Raw("sum(amount) as collected"))->where("funds.created_at", ">=", $mybudget->start)
                ->where("funds.created_at", "<=", $mybudget->end)->leftJoin("sources", "sources.id", "=", "funds.source")->groupBy("sources.name")->get();
            //return json_encode($collected->toArray());
        }

        return view('dashboard.finance.budget-preview', ['sources' => $sources, "budget" => $budget, "collected" => $collected]);
    }

    public function removebudget(Request $request)
    {
        $tid = config('app.tenant_id');
        if (\DB::table("budget")->where("tenant_id", $tid)->where("id", $request->id)->delete()) {
            \DB::table("budget_items")->where("budget_id", $request->id)->delete();
            return back()->with("success", "Budget removal successful");
        } else {
            return back()->with("error", "Unable to remove budget!");
        }
    }
    public function addbudget(Request $request)
    {
        $input = $request->all();
     /*   foreach ($input as $name => $value) {
            echo $name.' : '.$value.'<br>';
        }
        return json_encode($input);*/
        $start = \Carbon\Carbon::parse($request->month . " " . $request->through);
        $temp = \Carbon\Carbon::parse($request->endmonth . " " . $request->endthrough)->format('Y-m-t');
        $name = $request->name;
        $end = \Carbon\Carbon::parse($temp);

        $tid = config('app.tenant_id');
        if (!empty($request->bid)) {
            \DB::table('budget')->where("tenant_id", $tid)->where("id", $request->bid)->update(["name" => $name, "start" => $start, "end" => $end]);
            \DB::table("budget_items")->where("budget_id", $request->bid)->delete();
            foreach ($input as $name => $value) {
                if (!($name == "through" || $name == "month" || $name == "endthrough" || $name == "endmonth" || $name == "name" || $name == "_token" || $name == "categories" || $name == "bid")) {
                    $item_id = preg_replace("/[^0-9]/", "", $name);
                    \DB::table("budget_items")->insert(["tenant_id" => $tid, "budget_id" => $request->bid, "amount" => $value, "source" => $item_id, "choice" => 0]);
                }
            }
        } else {
            $start = \Carbon\Carbon::parse($request->month . " " . $request->through);
            $temp = \Carbon\Carbon::parse($request->endmonth . " " . $request->endthrough)->format('Y-m-t');
            $name = $request->name;
            $end = \Carbon\Carbon::parse($temp);
            $id = \DB::table('budget')->insertGetId(["tenant_id" => $tid, "name" => $name, "start" => $start, "end" => $end]);
            foreach ($input as $name => $value) {
                if (!($name == "through" || $name == "month" || $name == "endthrough" || $name == "endmonth" || $name == "name" || $name == "_token" || $name == "categories")) {
                    $item_id = preg_replace("/[^0-9]/", "", $name);
                    \DB::table("budget_items")->insertGetId(["tenant_id" => $tid, "budget_id" => $id, "amount" => $value, "source" => $item_id, "choice" => 0]);
                }
            }
        }
        return back()->with("success", "Successfully saved!");
    }

    public function summaries(Request $request)
    {
        $sources = \DB::table("sources")->orderBy("name", "ASC")->get();
        $modes = \DB::table("modeofpayment")->select("id", "name")->get();
        $name = \DB::table("summaries")->select("id", "name")->first();
        //return json_encode($name);
        $operations = \DB::table("modeofpayment")->leftJoin("summaries_operations", "summaries_operations.mode_id", "=", "modeofpayment.id")->get();
        //return json_encode($operations);
        $funds = \DB::table("modeofpayment")->leftJoin("funds", "funds.mode", "=", "modeofpayment.id")->leftJoin("sources", "sources.id", "=", "funds.source")->select(
            "modeofpayment.id",
            "modeofpayment.name",
            "funds.source",
            "sources.ftype",
            \DB::Raw("SUM(funds.amount) as totals")
        )->where("funds.created_at", ">=", \Carbon\Carbon::today())
            ->where("funds.created_at", "<", \Carbon\Carbon::today()->addDays(1))->groupBy("id", "name", "source", "ftype")->orderBy("name", "ASC")->get();

        //return json_encode($funds);
        /*$funds = \DB::table("funds")->select("funds.amount", "modeofpayment.name as mode", "sources.name as source", "sources.ftype")
            ->leftJoin("modeofpayment", "modeofpayment.id", "=", "funds.mode")->
            leftJoin("sources", "sources.id", "=", "funds.source")->where("funds.created_at", ">=", \Carbon\Carbon::today())
            ->where("funds.created_at", "<", \Carbon\Carbon::today()->addDays(1))->get();
        //return json_encode($funds);*/
        //return json_encode($name);
        return view("dashboard.finance.summaries", [
            "funds" => $funds,
            "sources" => $sources,
            "modes" => $modes,
            "operations" => $operations,
            "name" => $name
        ]);
    }

    public function summaries_settings(Request $request)
    {
        $request->validate([
            "summary" => "required",
            "mode" => "required",
            "operation" => "required",
        ]);
        if ($request->id > 0) {
            //update
            \DB::table("summaries")->where("id", $request->id)->update(["name" => $request->summary]);
            if (\DB::table("summaries_operations")->where("mode_id", $request->mode)->count() > 0) {
                //update
                if (\DB::table("summaries_operations")->where("mode_id", $request->mode)->update(["operation" => $request->operation])) {
                    return back()->with("success", "Settings Updated successfully!");
                } else {
                    return back()->with("error", "Unable to update settings");
                }
            } else {
                //insert
                if (\DB::table("summaries_operations")->insert(["mode_id" => $request->mode, "operation" => $request->operation])) {
                    return back()->with("success", "Settings Updated successfully!");
                } else {
                    return back()->with("error", "Unable to update settings");
                }
            }
        } else {
            if (\DB::table("summaries")->insert(["name" => $request->summary])) {
                if (\DB::table("summaries_operations")->where("mode_id", $request->mode)->count() > 0) {
                    //update
                    if (\DB::table("summaries_operations")->where("mode_id", $request->mode)->update(["operation" => $request->operation])) {
                        return back()->with("success", "Settings Updated successfully!");
                    } else {
                        return back()->with("error", "Unable to update settings");
                    }
                } else {
                    //insert
                    if (\DB::table("summaries_operations")->insert(["mode_id" => $request->mode, "operation" => $request->operation])) {
                        return back()->with("success", "Settings Updated successfully!");
                    } else {
                        return back()->with("error", "Unable to update settings");
                    }
                }
            } else {
                return back()->with("error", "Unable to update settings");
            }
        }
    }

    public function fundssearch(Request $request)
    {
        if (\Auth::user()->role == 1) {
            $sources = \DB::table('sources')->get();
            $users = \DB::table("users")->select("id", "firstname", "lastname", "email")->get();
            $start = null;
            $end = null;
            $funds = null;

            if ($request->has('from') && $request->has('to')) {
                $start = $request->from;
                $end = $request->to;

                $funds = Funds::select(["funds.id", "funds.amount", "funds.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description", "funds.created_at"])
                    ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")->where("funds.created_at", ">=", \Carbon\Carbon::parse($request->from))
                    ->where("funds.created_at", "<=", \Carbon\Carbon::parse($request->to))->orderBy('funds.id', 'desc')->get();
            } elseif ($request->has('from')) {
                $start = $request->from;

                $funds = Funds::select(["funds.id", "funds.amount", "funds.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description", "funds.created_at"])
                    ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")->where("funds.created_at", ">=", \Carbon\Carbon::parse($request->from))
                    ->orderBy('funds.id', 'desc')->get();
            } elseif ($request->has("to")) {
                $end = $request->to;

                $funds = Funds::select(["funds.id", "funds.amount", "funds.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description", "funds.created_at"])
                    ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")
                    ->where("funds.created_at", "<=", \Carbon\Carbon::parse($request->to))->orderBy('funds.id', 'desc')->get();
            }
            return view('admin.fundssearch')->with('funds', $funds)->with('sources', $sources)->with("users", $users)->with("from", $start)->with("to", $end);
        }
    }

    public function individualtithing()
    {
        $modes = \DB::table('modeofpayment')->get();
        $sources = \DB::table('sources')->where("ftype", 0)->get();
        $users = \DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->leftJoin("contacts", "contacts.user_id", "=", "users.id")->paginate(15);
        return view("dashboard.finance.individual_tithing", ["users" => $users, "sources" => $sources, "modes" => $modes]);
    }

    public function saveindividualtithe(Request $request)
    {
        $request->validate([
            "id" => "required",
            "amount" => "required|numeric|min:1",
            "note" => "required",
            "source" => "required",
        ]);
        $user = \DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->leftJoin("contacts", "contacts.user_id", "=", "users.id")->where("users.id", "=", $request->id)->first();

        if ($user != null) {
            //insert
            $funds = new Funds();
            $funds->amount = $request->amount;
            $funds->description = $request->note;
            $funds->source = $request->source;
            $funds->user_id = $user->id;
            $funds->mode = $request->mode;

            if ($funds->save()) {
                //send mail
                /*$data = array('name'=>$user->firstname." ".$user->lastname, 'mes'=>$request->note);
                Mail::send('admin.mail', $data, function($message) use ($user, $request){
                    $message->to($user->email, $user->firstname." ".$user->lastname)->subject
                        ("TITHE RECIEVED (KSH ".number_format($request->amount,2).")");
                    $message->from('jaygithiora@gmail.com','Church App');
                });
                \DB::table("emails")->insert(['user_id'=>$user->id, "subject"=>"TITHE RECIEVED (KSH ".number_format($request->amount,2).")", "message"=>$request->note,
                "sent"=>\Carbon\Carbon::now()]);*/

                //send message

                if ($user->phone != "") {

                    $site_settings = $this->site_settings;
                    $appname = $site_settings != null ? "" . $site_settings->name : "CHURCH APP";
                    $number = $user->phone;
                    if(strlen($number) < 12){
                        $number = "254" . substr($user->phone, 1);
                    }
                    //$message =  "TITHE RECIEVED (KSH ".number_format($request->amount,2).")\n".$request->input( 'note' )." ".$appname;
                    $message = $appname . "\n\nYour tithe of KSH " . number_format($request->amount, 2) . " has been RECIEVED.\n" . $request->input('note') . " \n May God bless You abudantly.";

                    $mpesaApiController = new MpesaAPIController;
                    $mpesaApiController->send($number, $message);

                    if ($user != null) {
                        $mid = \DB::table("sms")->insertGetId(["tenant_id" => config('app.tenant_id'), "people_id" => 0, "message" => $message, "category" => "mpesa", "sent" => \Carbon\Carbon::now()]);
                        \DB::table('sms_recipients')->insert(["tenant_id" => config('app.tenant_id'), "recipients" => $user->id, "sms_id" => $mid, "sent" => \Carbon\Carbon::now()]);
                    }
                }
                return redirect()->back()->with('success', "Tithe added successfully!");
            } else {
                return redirect()->back()->with('error', 'Unable to save tithe. Try again');
            }
        } else {
            return redirect()->back()->with("error", "Invalid User");
        }
    }

    public function search(Request $request)
    {
        $users = \DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->where("users.firstname", "LIKE", "%" . $request->search . "%")
            ->orWhere("users.lastname", "LIKE", "%" . $request->search . "%")->orWhere("users.email", "LIKE", "%" . $request->search . "%")->orWhere("contacts.phone", "LIKE", "%" . $request->search . "%")->leftJoin("contacts", "contacts.user_id", "=", "users.id")->limit(15)->get();
        return json_encode($users);
    }

    public function tithes(Request $request)
    {
        $sources = \DB::table('sources')->where("ftype", 0)->get();
        $modes = \DB::table('modeofpayment')->get();

        $tithes = \DB::table("funds")->select("users.id", "users.firstname", "users.lastname", "users.email", "funds.amount", "sources.name", "funds.created_at")->where("funds.user_id", "=", $request->id)->join("users", "funds.user_id", "=", "users.id")
            ->leftJoin("sources", "sources.id", "=", "funds.source")->orderBy("funds.id", "DESC")->paginate(15);
        $user = User::find($request->id);
        if ($user == null) {
            return redirect()->to('dashboard/home');
        }
        return view("dashboard.finance.tithes", @compact("user", "tithes", "sources", 'modes'));

    }

    public function deleteUser(Request $request)
    {
        if (\Auth::user()->role == 1) {
            if (\DB::table("users")->where("status", 0)->where("id", $request->id)->delete()) {
                \DB::table("contacts")->where("user_id", $request->id)->delete();
                return redirect()->back()->with("success", "User removed successfully");
            } else {
                return redirect()->back()->with("error", "unable to remove user");
            }
        } else {
            return redirect()->to('/home');
        }
    }

    public function expenses()
    {
            $sources = \DB::table('sources')->where("ftype", 1)->get();
            $modes = \DB::table('modeofpayment')->get();
            $funds = Funds::select(["funds.id", "funds.amount", "sources.ftype", "sources.name", "users.firstname", "users.lastname", "funds.description"])
                ->join("users", "users.id", "=", "funds.user_id")->join("sources", "sources.id", "=", "funds.source")
                ->orderBy('funds.id', 'desc')->where('sources.ftype', 1)->paginate('15');
            return view('dashboard.finance.expenses', ['funds' => $funds, 'sources' => $sources, "modes" => $modes]);

    }

    public function fundsource()
    {
        $sources = \DB::table('sources')->orderBy('id', 'desc')->paginate(10);
        $payments = \DB::table('modeofpayment')->orderBy('id', 'desc')->paginate(10);

        return view('dashboard.payment_settings.payments-settings', ['sources' => $sources, 'payments' => $payments]);
        //return view('admin.fundsource')->with('sources', $sources);
    }

    public function getsources(Request $request)
    {
        return json_encode(\DB::table('sources')->where('id', $request->id)->first());
    }

    public function getpayments(Request $request)
    {
        return json_encode(\DB::table('modeofpayment')->where('id', $request->id)->first());
    }

    public function savefunds(Request $request)
    {
        request()->validate([
            'amount' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'source' => 'required|integer',
        ]);
        if ($request->id > 0) {
            return redirect()->back()->with('error', 'Transactions cannot be edited!');
        } else {
            //insert
            $funds = new Funds();
            $funds->amount = $request->amount;
            $funds->description = $request->description;
            $funds->source = $request->source;
            $funds->mode = $request->mode;
            $funds->user_id = \Auth::user()->id;

            if ($funds->save()) {
                return redirect()->back()->with('success', "Funds saved successfully!");
            } else {
                return redirect()->back()->with('error', 'Unable to save. Try again');
            }
        }
    }

    public function removefund(Request $request)
    {
        $tid = config('app.tenant_id');
        if (\DB::table('funds')->where('tenant_id', $tid)->where('id', $request->id)->delete()) {
            return redirect()->back()->with('success', 'Selected funds removed!');
        } else {
            return redirect()->back()->with('error', 'Unable to remove. Try again');
        }
    }


    public function savefundsource(Request $request)
    {
        request()->validate([
            'name' => 'required|string|min:3|max:191',
            'description' => 'required|string|min:3',
            'ftype' => 'required|integer',
        ]);
        if (\DB::table("sources")->where("name", $request->name)->where("id", "<>", $request->id)->count() > 0) {
            return back()->with("error", "Name already in use. Choose a different one");
        }
        if ($request->id > 0) {
            //update
            $update = \DB::table('sources')->where('id', '=', $request->id)->update(array('name' => $request->name, 'description' => $request->description, "ftype" => $request->ftype));
            if ($update) {
                return redirect()->back()->with('success', 'Your funds type has been updated successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to update. Try again');
            }
        } else {

            //insert
            $fundstype = \DB::table('sources')->insert(["name" => $request->name, "description" => $request->description, "ftype" => $request->ftype]);
            if ($fundstype) {
                return redirect()->back()->with('success', 'New type successfully inserted');
            } else {
                return redirect()->back()->with('error', 'Unable to save. Try again');
            }
        }
    }

    public function removefundsource(Request $request)
    {
        if (\DB::table('funds')->where('source', $request->id)->count() > 0) {
            return redirect()->back()->with('error', 'Funds Type already in use');
        } else {
            if (\DB::table('sources')->where('id', $request->id)->delete()) {
                return redirect()->back()->with('success', 'Your funds type removed successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to remove. Try again');
            }
        }
    }

    public function removefundmode(Request $request)
    {
        if (\DB::table('funds')->where('mode', $request->id)->count() > 0) {
            return redirect()->back()->with('error', 'Mode of payment already in use');
        } else {
            if (\DB::table('modeofpayment')->where('id', $request->id)->delete()) {
                return redirect()->back()->with('success', 'Your Mode of Payment removed successfully');
            } else {
                return redirect()->back()->with('error', 'Unable to remove. Try again');
            }
        }
    }

    public function saveModeOfPayment(Request $request)
    {
        $request->validate([
            'name' => "required",
        ]);
        if ($request->id > 0) {
            if (\DB::table('modeofpayment')->where('name', $request->name)->where('id', '<>', $request->id)->count() > 0) {
                return back()->with("error", "Mode of Payment already exists");
            } else {
                if (\DB::table('modeofpayment')->where('id', $request->id)->update(["name" => $request->name])) {
                    return back()->with("success", "Update successful");
                } else {
                    return back()->with("error", "Unable to update");
                }
            }
        } else {
            if (\DB::table('modeofpayment')->where('name', $request->name)->count() > 0) {
                return back()->with("error", "Mode of Payment already exists");
            } else {
                if (\DB::table('modeofpayment')->insert(["name" => $request->name])) {
                    return back()->with("success", "Mode of payment successful saved");
                } else {
                    return back()->with("error", "Unable to save mode of payment");
                }
            }
        }
    }
    public function donations()
    {
        $tid = config('app.tenant_id');
        $donations = \DB::table('donations')->where('tenant_id', $tid)->paginate(15);
        return view('dashboard.finance.donations')->with('donations', $donations);
    }

    public function assets()
    {
        $tid = config('app.tenant_id');
        $totals = \DB::table('assets')->where('tenant_id', $tid)->sum('amount');
        $assets = \DB::table('assets')->where('assets.tenant_id', $tid)
            ->select("assets.id", "assets.name", "assets.description", "assets.acquired", "assets.amount", "users.firstname", "users.lastname")
            ->leftJoin("users", "users.id", "=", "assets.user_id")->paginate(15);
        return view('dashboard.finance.assets')->with('assets', $assets)->with('totals', $totals);
    }

    public function saveassets(Request $request)
    {
        request()->validate([
            'name' => 'required|min:2',
            'amount' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'acquired'=>'required|date'
        ]);
        $tid = config('app.tenant_id');
        if ($request->id > 0) {
            if (\DB::table('assets')->where('tenant_id', $tid)->where('id', $request->id)->update([
                "name" => $request->name, "amount" => $request->amount,
                "description" => $this->purify($request->description),
                "user_id" => \Auth::user()->id, "acquired" => $request->acquired,
            ])) {
                return redirect()->back()->with('success', 'Assets update succesfully!');
            } else {
                return redirect()->back()->with('error', 'Unable to save assets!');
            }
        } else {
            if (\DB::table('assets')->insert([
                "tenant_id" => $tid, "name" => $request->name, "amount" => $request->amount,
                "description" => $this->purify($request->description),
                "user_id" => \Auth::user()->id, "acquired" => $request->acquired,
            ])) {
                return redirect()->back()->with('success', 'Assets saved succesfully!');
            } else {
                return redirect()->back()->with('error', 'Unable to save assets!');
            }
        }
    }

    public function removeasset(Request $request)
    {
        $tid = config('app.tenant_id');
        if (\DB::table('assets')->where('tenant_id', $tid)->where('id', $request->id)->delete()) {
            return redirect()->back()->with('success', 'Asset successfully removed');
        } else {
            return redirect()->back()->with('error', 'Unable to update. Try again');
        }
    }

    public function activities()
    {
        $tid = config('app.tenant_id');
        $activities = \DB::table('activities')->where('tenant_id', $tid)->orderBy('id', 'desc')->paginate(15);
        return view("dashboard.finance.activities")->with("activities", $activities);
    }
    public function activitiespermissions()
    {
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if ($perm1 != null || $perm2 != null) {
            if ($perm1->finances > 0 || $perm2->finances > 0) {
                $activities = \DB::table('activities')->orderBy('id', 'desc')->paginate(15);
                return view("user.permissions.activities")->with("activities", $activities);
            } else {
                return redirect()->back()->with("error", "Access denied");
            }
        }
    }

    public function removeactivity(Request $request)
    {
        $tid = config('app.tenant_id');
        $activity = \DB::table("activities")->where("tenant_id", $tid)->where("id", $request->id)->first();
        if ($activity == null) {
            return back()->with("error", "Invalid Choice!");
        } else {
            if ((\DB::table("pledges")->where("tenant_id", $tid)->where("activity", $request->id)->count() > 0) ||
                (\DB::table("groups")->where("tenant_id", $tid)->where("activity", $request->id)->count() > 0)) {
                return back()->with("error", "The item you are deleting is already in use");
            } else {
                if (\DB::table("activities")->where("tenant_id", $tid)->where("id", $request->id)->delete()) {
                    return back()->with("success", "Item has been removed successfully!");
                } else {
                    return back()->with("error", "unable to remove item!");
                }
            }
        }
    }
    public function addactivity(Request $request)
    {
        request()->validate([
            "amount" => "required|numeric|min:1000",
            "name" => "required|string|min:2",
            "description" => "required|string|min:2",
            "date" => "required",
        ]);
        $today = \Carbon\Carbon::now();
        $tid = config('app.tenant_id');
        $to = \Carbon\Carbon::parse($request->date);
        if ($to > $today) {
            if ($request->id > 0) {
                if (\DB::table("activities")->where('tenant_id', $tid)->where("id", "<>", $request->id)->where("name", $request->name)->count() > 0) {
                    return redirect()->back()->with("error", "Duplicate name! Choose a different name!");
                } else {
                    if (\DB::table("activities")->where('tenant_id', $tid)->where("id", $request->id)->update([
                        "name" => $request->name, "description" => $request->description, "amount" => $request->amount, "closes_on" => $to
                    ])) {
                        return redirect()->back()->with("success", "updated successfully!");
                    } else {
                        return redirect()->back()->with("error", "Unable to update");
                    }
                }
            } else {
                if (\DB::table("activities")->where('tenant_id', $tid)->where("name", $request->name)->count() > 0) {
                    return redirect()->back()->with("error", "Duplicate name! Choose a different name!");
                } else {
                    if (\DB::table("activities")->insert([
                        "tenant_id" => $tid, "name" => $request->name, "description" => $request->description, "amount" => $request->amount, "closes_on" => $to
                    ])) {
                        return redirect()->back()->with("success", "Activated Created!");
                    } else {
                        return redirect()->back()->with("error", "Unable to create activity");
                    }
                }
            }
        } else {
            return redirect()->back()->with("error", "Future dates required!");
        }
    }

    public function getactivity(Request $request)
    {
        $tid = config('app.tenant_id');
        return json_encode(\DB::table("activities")->where('tenant_id', $tid)->where("id", $request->id)->first());
    }

    public function pledges(Request $request)
    {
        $tid = config('app.tenant_id');
        $activity = \DB::table('activities')->where('tenant_id', $tid)->where('id', $request->id)->first();
        $pledged  = \DB::table('pledges')->where('tenant_id', $tid)->where("activity", $request->id)->sum("amount");
        $paid     = \DB::table('pledges')->where('tenant_id', $tid)->where("activity", $request->id)->sum("paid");
        $pledges  = \DB::table("pledges")->where('pledges.tenant_id', $tid)
            ->select("pledges.id", "activities.name", "pledges.paid", "pledges.status", "activities.closes_on", "pledges.amount", "users.firstname", "users.lastname", "users.id as user")
            ->where("activity", $request->id)->join("activities", "activities.id", "pledges.activity")->leftJoin("users", "users.id", "pledges.user_id")
            ->paginate(15);
        return view('dashboard.finance.pledges', ["pledges" => $pledges, "activity" => $activity, "pledged" => $pledged, "paid" => $paid]);
    }

    public function importPledges(Request $request)
    {
        //name, number, commited
        $request->validate([
            'activity_id'=>'required|exists:activities,id',
            'message'=>'required|string',
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new PledgesImport($request->activity_id, $request->message), $request->file('file'));

        return back()->with('success', 'Pledges file imported successfully!');
    }
    public function getUsers(Request $request)
    {
        $start = $request->limit == null ? "0" : intval($request->limit);

        if ($request->search == null) {
            return json_encode(\DB::table("contacts")->select("contacts.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->join("users", "users.id", "=", "contacts.user_id")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
        } else {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            return json_encode(\DB::table("contacts")->select("contacts.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->join("users", "users.id", "=", "contacts.user_id")->
                where("firstname", "LIKE", "%" . $search . "%")->orWhere("lastname", "LIKE", "%" . $search . "%")->orWhere("contacts.phone", "LIKE", "%" . $search . "%")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
        }
    }


    public function getAjaxUsers(Request $request)
    {
        $start = $request->limit == null ? "0" : intval($request->limit);

        if (!$request->has('search')) {
            return json_encode(\DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->leftJoin("contacts", "users.id", "=", "contacts.user_id")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
        } else {
            $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
            return json_encode(\DB::table("users")->select("users.id", "users.firstname", "users.lastname", "users.email", "contacts.phone")->join("contacts", "users.id", "=", "contacts.user_id")->
                where("firstname", "LIKE", "%" . $search . "%")->orWhere("lastname", "LIKE", "%" . $search . "%")->orWhere("contacts.phone", "LIKE", "%" . $search . "%")->orderBy("users.firstname", "ASC")->skip($start)->take(10)->get());
        }
    }

    public function getPledges(Request $request)
    {
        return json_encode(\DB::table('pledges')->select("pledges.id", "activities.name", "users.firstname", "users.lastname", "pledges.amount", "pledges.paid")
            ->where("pledges.id", $request->id)->leftJoin("users", "users.id", "=", "pledges.user_id")->leftJoin("activities", "activities.id", "=", "pledges.activity")->first());
    }

    public function pledgespermissions(Request $request)
    {
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if ($perm1 != null || $perm2 != null) {
            if ($perm1->finances > 0 || $perm2->finances > 0) {
                $pledges = \DB::table("pledges")->select(
                    "pledges.id",
                    "activities.name",
                    "activities.closes_on",
                    "pledges.status",
                    "pledges.amount",
                    "users.firstname",
                    "users.lastname"
                )->where("activity", $request->id)->join("activities", "activities.id", "pledges.activity")->leftJoin("users", "users.id", "pledges.user_id")
                    ->paginate(15);
                //return json_encode($pledges);
                return view('user.permissions.pledges')->with("pledges", $pledges)->with("activity", $request->id);
            } else {
                return redirect()->back()->with("error", "Access denied");
            }
        }
    }

    public function groupsparticipants(Request $request)
    {
            $group = \DB::table("groups")->select("groups.id", "groups.name", "activities.name as activity", "groups.amount", "groups.paid")->where("groups.id", $request->id)->leftJoin("activities", "activities.id", "=", "groups.activity")->first();
            $participants = \DB::table('pledges')->select("pledges.id", "users.firstname", "users.lastname", "pledges.amount", "groups.name", "pledges.paid")->join("groups", "groups.id", "=", "pledges.groups")->join("users", "users.id", "=", "pledges.user_id")->paginate(15);
            /*$participants = \DB::table("participants")->where("group_id", $request->id)->join("users", "users.id", "=", "participants.user_id")
            ->join("groups", "groups.id", "participants.group_id")->join("activities", 'activities.id', "groups.activity")->
            select("participants.id", "users.firstname", "users.lastname", "participants.amount", "participants.status", "users.email", "activities.name", "activities.closes_on")->paginate(15);
            */
            return view("dashboard.finance.participants", ["participants" => $participants, "group" => $group]);
    }

    public function recieved(Request $request)
    {
        $pledges = \DB::table("pledges")->where("id", $request->id);
        if ($pledges->count() > 0) {
            if (\DB::table("pledges")->where("id", $request->id)->update(["status" => 1])) {
                return redirect()->back()->with("success", "Successfully updated!");
            } else {
                return redirect()->back() - with("error", "unable to update");
            }
        } else {
            return redirect()->back()->with("error", "Access denied");
        }
    }

    public function recievedgroups(Request $request)
    {
        $pledges = \DB::table("groups")->where("id", $request->id);
        if ($pledges->count() > 0) {
            if (\DB::table("groups")->where("id", $request->id)->update(["status" => 1])) {
                return redirect()->back()->with("success", "Successfully updated!");
            } else {
                return redirect()->back() - with("error", "unable to update");
            }
        } else {
            return redirect()->back()->with("error", "Access denied");
        }
    }

    public function removepledge(Request $request)
    {
            $pledge = \DB::table("pledges")->select("groups.id", "groups.amount")->join("groups", "groups.id", "=", "pledges.groups")->where("pledges.id", $request->id)->first();

            if ($pledge == null) {
                if (\DB::table("pledges")->where("id", $request->id)->where("paid", 0)->delete()) {
                    return redirect()->back()->with("success", "pledge successfully removed");
                } else {
                    return redirect()->back()->with("error", "Pledge contains paid amount");
                }
            } else {
                $people = \DB::table("pledges")->where("pledges.groups", $pledge->id)->count();
                if (\DB::table("pledges")->where("id", $request->id)->where("paid", 0)->delete()) {
                    if ($people > 0) {
                        $amount = round($pledge->amount / $people, 0);
                        \DB::table("pledges")->where("groups", $pledge->id)->update(["amount" => $amount]);
                    }
                    return redirect()->back()->with("success", "pledge successfully removed");
                } else {
                    return redirect()->back()->with("error", "Pledge contains paid amount");
                }
            }
    }

    public function groups(Request $request)
    {
            $activity = \DB::table('activities')->where("id", $request->id)->first();
            $groups = \DB::table("groups")->select("groups.id", "users.firstname", "users.lastname", "groups.name", "groups.paid", "activities.name as activity", "groups.amount", "groups.status", "activities.closes_on")
                ->where("activity", $request->id)->join("activities", "activities.id", "groups.activity")->join("users", "users.id", "=", "groups.creator")->paginate(15);
            return view('dashboard.finance.groups', @compact("groups", "activity"));
    }

    public function groups_by_user()
    {
        $activities = \DB::table("activities")->where("closes_on", ">", \Carbon\Carbon::now())->orderBy("id", "desc")->get();
        $groups = \DB::table("groups")->select(
            "groups.id",
            "groups.amount",
            "groups.name",
            "activities.name as activity",
            "activities.closes_on",
            "groups.status"
        )->where("creator", \Auth::user()->id)->join("activities", "activities.id", "=", "groups.activity")->paginate(15);
        return view("admin.users.groups_by_user")->with("groups", $groups)->with("activities", $activities);
    }

    public function participants(Request $request)
    {
        $users = \DB::table("users")->where("role", "<>", 1)->orderBy("id", "desc")->get();
        $name = \DB::table("groups")->where("id", $request->id)->first();
        $participants = \DB::table("participants")->where("group_id", $request->id)->join("users", "users.id", "=", "participants.user_id")
            ->join("groups", "groups.id", "participants.group_id")->join("activities", 'activities.id', "groups.activity")->
            select("participants.id", "users.firstname", "users.lastname", "participants.amount", "participants.status", "users.email", "activities.name", "activities.closes_on")->paginate(15);
        return view("user.participants")->with("participants", $participants)->with("name", $name)->with("users", $users);
    }

    public function addparticipant(Request $request)
    {
        request()->validate([
            "group_id" => "required|min:1",
            "user_id" => "required|integer|min:1",
            "amount" => "required|numeric|min:100",
        ]);

        if (\DB::table("participants")->where("group_id", $request->group_id)->where("user_id", $request->user_id)->count() > 0) {
            return redirect()->back()->with("error", "User already assigned to this group");
        } else {
            if ($request->id > 0) {
                return "Edit";
            } else {
                if (
                    \DB::table('participants')->insert([
                        "user_id" => $request->user_id,
                        "amount" => $request->amount,
                        "status" => 0,
                        "group_id" => $request->group_id
                    ])
                ) {
                    return redirect()->back()->with("success", "User added!");
                } else {
                    return redirect()->back()->with("error", "unablt to add user");
                }
            }
        }
    }

    public function removeparticipant(Request $request)
    {
        if (\DB::table("participants")->where("id", $request->id)->where("status", 0)->delete()) {
            return redirect()->back()->with("success", "participant successfully removed!");
        } else {
            return redirect()->back()->with("error", "Unable to remove item!");
        }
    }

    public function removegroup(Request $request)
    {
        if (\DB::table("participants")->where("group_id", $request->id)->count() == 0) {
            if (\DB::table("groups")->where("id", $request->id)->delete()) {
                return redirect()->back()->with("success", "Group removed successfully!");
            } else {
                return redirect()->back()->with("error", "Unable to remove group!");
            }
        } else {
            return redirect()->back()->with("error", "Group already in use. Remove participants to continue!");
        }
    }

    public function addpledge(Request $request)
    {
        request()->validate([
            "amount" => "required|numeric|min:100",
            "activity" => "required",
        ]);
        if ($request->id > 0) {
            return "EDIFT";
        } else {
            if (\DB::table('pledges')->where('activity', $request->activity)->count() == 0) {
                if (
                    \DB::table("pledges")->insert([
                        "activity" => $request->activity,
                        "groups" => 0,
                        "user_id" => \Auth::user()->id,
                        "paid" => 0,
                        "amount" => $request->amount,
                        "status" => 0
                    ])
                ) {
                    return redirect()->back()->with("success", "You pledge has been successfully saved");
                } else {
                    return redirect()->back()->with("error", "Unable to save pledge!");
                }
            } else {
                return redirect()->back()->with("error", "You have already pledged for this activity!");
            }
        }
    }

    public function addgroup(Request $request)
    {
        request()->validate([
            "name" => "required|string|min:3",
            "amount" => "required|numeric|min:1000",
            "activity" => "required",
        ]);
        if ($request->id > 0) {
            return "EDIFT";
        } else {
            if (\DB::table('groups')->where('name', $request->name)->where("activity", $request->activity)->count() == 0) {
                if (
                    \DB::table("groups")->insert([
                        "activity" => $request->activity,
                        "name" => $request->name,
                        "creator" => \Auth::user()->id,
                        "amount" => $request->amount,
                        "status" => 0
                    ])
                ) {
                    return redirect()->back()->with("success", "Group successfully created! You can add new members to this group!");
                } else {
                    return redirect()->back()->with("error", "Unable to save group!");
                }
            } else {
                return redirect()->back()->with("error", "You have already created a group for this activity!");
            }
        }
    }

    public function datatablefunds(Request $request)
    {
        return DataTables::of(Funds::select(
            "funds.id",
            "funds.amount",
            "funds.description",
            "funds.source",
            "sources.name",
            "sources.ftype",
            "modeofpayment.name as mode",
            "funds.created_at",
            "users.firstname",
            "users.lastname"
        )->leftJoin("users", "users.id", "funds.user_id")->leftJoin("modeofpayment", "modeofpayment.id", "=", "funds.mode")
            ->join("sources", "funds.source", "=", "sources.id")->orderBy("funds.id", "DESC"))->addColumn("created", function ($item) {
                return "<span class='small font-weight-bold'>" . \Carbon\Carbon::parse($item->created_at)->format("l, d M, Y") . "</span>";
            })->filter(function ($query) use ($request) {
                if ($request->has('from') && $request->from != null) {
                    $query->where(\DB::raw('DATE(funds.created_at)'), '>=', \Carbon\Carbon::parse($request->get('from')));
                }

                if ($request->has('to') && $request->to != null) {
                    $query->where(\DB::raw('DATE(funds.created_at)'), '<=', \Carbon\Carbon::parse($request->get('to')));
                }

                if ($request->has('date') && $request->date != null) {
                    $query->where(\DB::raw('DATE(funds.created_at)'), '=', \Carbon\Carbon::parse($request->get('date')));
                }

                if ($request->has('source') && $request->source != null && $request->source != '0') {
                    $query->where('funds.source', '=', $request->get('source'));
                }

                if ($request->has('msearch') && $request->msearch != "") {
                    $query->where(function($q) use ($request) {
                        $q->where("firstname", 'like', "%{$request->get('msearch')}%")
                          ->orWhere("lastname", 'like', "%{$request->get('msearch')}%")
                          ->orWhere("funds.description", 'like', "%{$request->get('msearch')}%");
                    });
                }
            })->addColumn("action", function ($item) {
                return "";
            })->addColumn("note", function ($item) {
                return \Str::words($item->description, 5);
            })->addColumn("user", function ($item) {
                if ($item->firstname) {
                    return "<span class='small font-weight-bold'>" . $item->firstname . " " . $item->lastname . "</span>";
                }
                // Extract name from mpesa description for unmatched transactions
                $desc = $item->description ?? '';
                if (preg_match('/Dear,?\s+(\w+)/i', $desc, $m)) {
                    return "<span class='small font-weight-bold text-warning'>" . $m[1] . " <i class='fas fa-exclamation-circle' title='Unmatched phone'></i></span>";
                }
                return "<span class='badge badge-warning'>Unknown</span>";
            })->addColumn("ftype", function ($item) {
                if ($item->ftype == 1) {
                    return "<span class='badge badge-danger'>Expenditure</span>";
                } else {
                    return "<span class='badge badge-success'>Collections</span>";
                }
            })->escapeColumns([])->make(true);
    }

    public function datatableexpenses(Request $request)
    {
        return DataTables::of(Funds::where("sources.ftype", 1)->select(
            "funds.id",
            "funds.amount",
            "funds.description",
            "sources.name",
            "sources.ftype",
            "modeofpayment.name as mode",
            "funds.created_at",
            "users.firstname",
            "users.lastname"
        )->join("users", "users.id", "funds.user_id")->leftJoin("modeofpayment", "modeofpayment.id", "=", "funds.mode")
            ->join("sources", "funds.source", "=", "sources.id")->orderBy("funds.id", "DESC"))->addColumn("created", function ($item) {
                return "<span class='small font-weight-bold'>" . \Carbon\Carbon::parse($item->created_at)->format("l, d M, Y") . "</span>";
                //})//->addColumn("amount", function($item){
                //return "<span class='small font-weight-bold'>".number_format($item->amount, 2)."</span>";
            })->filter(function ($query) use ($request) {
                if ($request->has('from')) {
                    if ($request->from != null) {
                        $query->where(\DB::raw('DATE(funds.created_at)'), '>=', \Carbon\Carbon::parse($request->get('from')));
                    }
                }

                if ($request->has('to')) {
                    if ($request->to != null) {
                        $query->where(\DB::raw('DATE(funds.created_at)'), '<=', \Carbon\Carbon::parse($request->get('to')));
                    }//$query->where('email', 'like', "%{$request->get('email')}%");
                }

                if ($request->has('msearch')) {
                    if ($request->msearch != null) {
                        $query->where("sources.ftype", 1)->where("firstname", 'like', "%{$request->get('msearch')}%")->orWhere("lastname", 'like', "%{$request->get('msearch')}%")
                            ->orWhere("email", 'like', "%{$request->get('msearch')}%");
                    }//$query->where('email', 'like', "%{$request->get('email')}%");
                }
            })->addColumn("action", function ($item) {
                return "";
            })->addColumn("note", function ($item) {
                return \Str::words($item->description, 5);
            })->addColumn("user", function ($item) {
                return "<span class='badge badge-primary'>" . $item->firstname . " " . $item->lastname . "</span>";
            })->addColumn("ftype", function ($item) {
                if ($item->ftype == 1) {
                    return "<span class='badge badge-danger'>Expenditure</span>";
                } else {
                    return "<span class='badge badge-success'>Collections</span>";
                }
            })->escapeColumns([])->with('total', Funds::sum('amount'))->make(true);
    }

    public function missingMpesaPhones(Request $request)
    {
        /*$mpesas = MpesaTransaction::get();
        foreach($mpesas as $mpesa){
            /*if(strlen($mpesa->MSISDN) <= 12){
                $hash = hash('sha256', $mpesa->MSISDN);
                $mpesaPhone = MpesaPhone::where('phone', $mpesa->MSISDN)->first();
                if($mpesaPhone == null){
                    $mpesaPhone = new MpesaPhone;
                }
                $mpesaPhone->phone = $mpesa->MSISDN;
                $mpesaPhone->phone_hash = $hash;
                $mpesaPhone->name = $mpesa->FirstName;
                $mpesaPhone->save();
            }
        }*/

        /*$mpesas = MpesaTransaction::where(\DB::Raw('CHAR_LENGTH(MSISDN)'), '>', 12)->get();
        foreach($mpesas as $mpesa){
            $mpesaPhone = MpesaPhone::where('phone_hash', $mpesa->MSISDN)->first();
            if($mpesaPhone == null){
                $missingMpesaPhone = MissingMpesaPhone::where('trans_id', $mpesa->TransID)->first();
                if($missingMpesaPhone == null){
                    $missingMpesaPhone = new MissingMpesaPhone;
                }
                $missingMpesaPhone->name = $mpesa->FirstName;
                $missingMpesaPhone->phone_hash = $mpesa->MSISDN;
                $missingMpesaPhone->trans_id = $mpesa->TransID;
                $missingMpesaPhone->trans_date = \Carbon\Carbon::parse($mpesa->TransTime);
                $missingMpesaPhone->save();
            }
        }*/
        return view("dashboard.finance.missing_mpesa_phones");
    }


    public function getMissingMpesaPhones(Request $request)
    {
        return DataTables::of(MissingMpesaPhone::orderBy("trans_date", "DESC"))->addColumn("trans_date", function ($item) {
            return "<span class='small font-weight-bold'>" . \Carbon\Carbon::parse($item->trans_date)->format("l, d M, Y") . "</span>";
        })->filter(function ($query) use ($request) {
            if ($request->has('from')) {
                if ($request->from != null) {
                    $query->where(\DB::raw('DATE(trans_date)'), '>=', \Carbon\Carbon::parse($request->get('from')));
                }
            }

            if ($request->has('to')) {
                if ($request->to != null) {
                    $query->where(\DB::raw('DATE(trans_date)'), '<=', \Carbon\Carbon::parse($request->get('to')));
                }//$query->where('email', 'like', "%{$request->get('email')}%");
            }

            if ($request->has('msearch')) {
                if ($request->msearch != null) {
                    $query->where(function($q)use($request){
                        $q->where("trans_id", 'like', $request->msearch.'%')
                        ->orWhere("name", 'like', $request->msearch.'%');
                    });
                }
            }
        })->addColumn("action", function ($item) {
            return "<div class='text-right'>
                <span class='d-none id'>" . $item->id . "</span>
                <span class='d-none name'>" . $item->name . "</span>
                <span class='d-none trans_id'>" . $item->trans_id . "</span>
                <button class='btn btn-primary btn-sm btn-edit' data-toggle='modal' data-target='#mpesaMissingModal'><i class='fas fa-edit'></i>&nbsp;Edit</button></button>";
        })->addColumn("note", function ($item) {
            return \Str::words($item->description, 5);
        })->addColumn("user", function ($item) {
            return "<span class='badge badge-primary'>" . $item->firstname . " " . $item->lastname . "</span>";
        })->addColumn("ftype", function ($item) {
            if ($item->ftype == 1) {
                return "<span class='badge badge-danger'>Expenditure</span>";
            } else {
                return "<span class='badge badge-success'>Collections</span>";
            }
        })->escapeColumns([])->with('total', Funds::sum('amount'))->make(true);
    }

    public function addMissingMpesaPhone(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "id" => "required|min:1|integer|exists:missing_mpesa_phones",
            "phone" => "required|digits:12",
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $missingMpesaPhone = MissingMpesaPhone::find($request->id);
        $phone_hash = $hash = hash('sha256', $request->phone);
        if ($phone_hash == $missingMpesaPhone->phone_hash) {
            //valid phone number
            $mpesaPhone = new MpesaPhone;
            $mpesaPhone->name = $missingMpesaPhone->name;
            $mpesaPhone->phone = $request->phone;
            $mpesaPhone->phone_hash = $missingMpesaPhone->phone_hash;
            if ($mpesaPhone->save()) {
                MissingMpesaPhone::where('phone_hash', $missingMpesaPhone->phone_hash)->delete();
            }
            return response()->json(["success" => "Phone Number updated successfully!"]);
        } else {
            return response()->json(["error" => "Phone provided does not match selected user hash!"], 401);
        }
    }

    public function populateMpesaHashes(Request $request)
    {
        $contacts = \DB::table('contacts')
            ->join('users', 'users.id', '=', 'contacts.user_id')
            ->whereNotNull('contacts.phone')
            ->where('contacts.phone', '<>', '')
            ->select('contacts.phone', 'contacts.user_id', 'users.firstname', 'users.lastname')
            ->get();

        $added = 0;
        foreach ($contacts as $contact) {
            $phone = trim($contact->phone);
            // Convert to international format 254...
            if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
                $phone = '254' . substr($phone, 1);
            } elseif (strlen($phone) == 9) {
                $phone = '254' . $phone;
            } elseif (substr($phone, 0, 1) == '+') {
                $phone = substr($phone, 1);
            }

            if (strlen($phone) != 12) {
                continue;
            }

            $hash = hash('sha256', $phone);

            // Skip if hash already exists
            if (MpesaPhone::where('phone_hash', $hash)->exists()) {
                continue;
            }

            MpesaPhone::create([
                'name' => $contact->firstname . ' ' . $contact->lastname,
                'phone' => $phone,
                'phone_hash' => $hash,
            ]);
            $added++;
        }

        return response()->json(['success' => "Populated $added new hash entries."]);
    }

    public function retroMatchFunds(Request $request)
    {
        $unmatched = \DB::table('funds')
            ->where('user_id', 0)
            ->where('source', 1) // mpesa source
            ->get();

        $matched = 0;
        foreach ($unmatched as $fund) {
            // Find corresponding mpesa_transaction by matching description/amount
            $transaction = \DB::table('mpesa_transactions')
                ->where('TransAmount', $fund->amount)
                ->whereDate('created_at', \Carbon\Carbon::parse($fund->created_at)->toDateString())
                ->first();

            if (!$transaction) {
                continue;
            }

            $msisdn = $transaction->MSISDN;

            // Try direct match (unhashed phone)
            if (strlen($msisdn) <= 12) {
                $contact = \DB::table('contacts')->where('phone', $msisdn)
                    ->orWhere('phone', '0' . substr($msisdn, 3))
                    ->first();
                if ($contact) {
                    \DB::table('funds')->where('id', $fund->id)->update(['user_id' => $contact->user_id]);
                    $matched++;
                    continue;
                }
            }

            // Try hash match
            $mpesaPhone = MpesaPhone::where('phone_hash', $msisdn)->first();
            if ($mpesaPhone) {
                // Find user by phone
                $contact = \DB::table('contacts')->where('phone', $mpesaPhone->phone)
                    ->orWhere('phone', '0' . substr($mpesaPhone->phone, 3))
                    ->first();
                if ($contact) {
                    \DB::table('funds')->where('id', $fund->id)->update(['user_id' => $contact->user_id]);
                    $matched++;
                }
            }
        }

        return response()->json(['success' => "Matched $matched fund records to users."]);
    }
}
