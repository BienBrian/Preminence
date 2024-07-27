<?php

namespace App\Http\Controllers\Dashboard\Emails;

use App\Http\Controllers\Controller;
use App\Mail\MyEmail;
use App\Models\Email;
use App\Models\EmailRecipient;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EmailController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', 'verified']);
    }


    public function index()
    {
        return view('dashboard.emails.emails');
    }
    public function getEmails(Request $request)
    {
        //\Log::info(Carbon::parse($request->date)->format('Y-m-d'));
        $emails = Email::with(['recipients', 'user'])->where(DB::Raw('DATE(created_at)'), Carbon::parse($request->date)->format('Y-m-d'))
        ->orderBy('id', 'DESC');
        return DataTables::of($emails)
            ->filter(function($query) use($request){
                $query->where(function($q) use($request){
                    $q->whereHas('user', function($query) use($request){
                        $query->where('username', 'LIKE', '%'.$request->search.'%');
                    })->orWhere('subject', 'LIKE', '%'.$request->search.'%');
                });
            })    ->editColumn('recipients', function ($row) {
                    return $row->recipients->count()." Recipient(s)";
                })->editColumn('commission', function ($row) {
            return 'INR ' . number_format($row->commission, 2, '.', ',');
        })->editColumn('created_at', function ($row) use ($request) {
            return Carbon::parse($row->created_at)->setTimezone($request->timezone)->format('d M, Y h:i A');
        })->addColumn('action', function ($row) {
            $actionBtn = '<div style="white-space: nowrap;" class="text-end">'.
                '<a href="'.url('dashboard/downlines/view/'.$row->id).'" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View</a> ';
            $actionBtn .= '</div>';
            return $actionBtn;
        })->addIndexColumn()->escapeColumns([])->make();
    }

    public function email(){
        return view('dashboard.emails.email');
    }

    public function sendMail(Request $request){
        $validator = Validator::make($request->all(), [
            "id"=>"required|integer|min:0",
            "recipients"=>"required",
            "subject"=>"nullable|string",
            "body"=>"required|string",
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $email  = new Email;
        $email->subject = $request->subject;
        $email->body = $request->body;
        $email->user_id = Auth::user()->id;
        if($email->save()){
            foreach($request->recipients as $recipient_id){
                $recipient = EmailRecipient::where('email_id', $email->id)->where('user_id', $recipient_id)->first();
                if($recipient == null){
                    $recipient = new EmailRecipient;
                }
                $recipient->email_id = $email->id;
                $recipient->user_id = $recipient_id;
                $recipient->save();
            }
            Mail::to(User::whereIn('id', $request->recipients)->pluck('email'))->queue(new MyEmail($email));
            return response()->json(["success"=>'Email Sent successfully']);
        }else{
            return response()->json(["error"=>'Unable to send email'], 400);
        }
    }
}
