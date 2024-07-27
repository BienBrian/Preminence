<?php

namespace App\Http\Controllers\Dashboard\Emails;

use App\Http\Controllers\Controller;
use App\Mail\MyEmail;
use App\Models\Email;
use App\Models\EmailRecipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class GlobalEmailController extends Controller
{
    public function __construct()
    {
    $this->middleware(['auth', /*'verified'*/]);
    }
    public function sendMail($subject, $body, $recipient_id)
    {
        return 'OK';
        $email = new Email;
        $email->subject = $subject;
        $email->body = $body;
        $email->user_id = auth()->user()->id;
        if ($email->save()) {
            $recipient = EmailRecipient::where('email_id', $email->id)->where('user_id', $recipient_id)->first();
            if ($recipient == null) {
                $recipient = new EmailRecipient;
            }
            $recipient->email_id = $email->id;
            $recipient->user_id = $recipient_id;
            $recipient->save();

            Mail::to(User::where('id', $recipient_id)->pluck('email'))->queue(new MyEmail($email));
        }
    }
}
