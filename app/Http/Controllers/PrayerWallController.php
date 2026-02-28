<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Services\SendSMSController;
use App\Models\PrayerRequest;
use App\Models\PrayerRequestNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrayerWallController extends Controller
{
    public function index()
    {
        $prayers = PrayerRequest::publicApproved()
            ->whereIn('status', ['new', 'in_progress', 'prayed_for', 'follow_up'])
            ->where('request_type', 'prayer')
            ->orderByDesc('created_at')
            ->paginate(12);

        $praiseReports = PrayerRequest::publicApproved()
            ->where(function ($q) {
                $q->where('request_type', 'praise_report')
                  ->orWhere('status', 'answered');
            })
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $settings = DB::table('settings')->first();

        return view('prayer-wall', compact('prayers', 'praiseReports', 'settings'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'required|string|max:2000',
            'category'           => 'nullable|string|max:50',
            'contact_preference' => 'required|in:follow_up,pastor_call,anonymous',
            'name'               => 'nullable|string|max:100',
            'phone'              => 'nullable|string|max:20',
            'alternate_phone'    => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
        ]);

        $pref = $request->contact_preference;
        $isAnonymous = ($pref === 'anonymous') ? 1 : 0;

        // For non-anonymous: try to match against existing user profiles
        $matchedUserId = auth()->id(); // if logged in, use that
        $settings = DB::table('settings')->first();
        $phoneCode = $settings->phone_code ?? '254';

        if (!$isAnonymous && !$matchedUserId && ($request->phone || $request->alternate_phone)) {
            $phonesToCheck = [];
            if ($request->phone) $phonesToCheck[] = $request->phone;
            if ($request->alternate_phone) $phonesToCheck[] = $request->alternate_phone;

            foreach ($phonesToCheck as $pRaw) {
                if ($matchedUserId) break; // already matched

                $pNorm = $pRaw;
                // Normalize: strip leading zero, prepend phone_code
                if (substr($pNorm, 0, 1) === '0') {
                    $pNorm = $phoneCode . substr($pNorm, 1);
                }

                // Try matching by phone in contacts table
                $match = DB::table('contacts')
                    ->join('users', 'users.id', '=', 'contacts.user_id')
                    ->where('contacts.phone', $pNorm)
                    ->select('users.id')
                    ->first();

                if (!$match) {
                    // Try matching directly in users table
                    $match = DB::table('users')->where('phone', $pNorm)->select('id')->first();
                }

                if ($match) {
                    $matchedUserId = $match->id;
                }
            }
        }

        $prayer = PrayerRequest::create([
            'title'              => $request->title,
            'description'        => $request->description,
            'category'           => $request->category,
            'request_type'       => 'prayer',
            'submitted_by'       => $matchedUserId,
            'submitted_name'     => $isAnonymous ? null : $request->name,
            'submitted_phone'    => $isAnonymous ? null : $request->phone,
            'submitted_alternate_phone' => $isAnonymous ? null : $request->alternate_phone,
            'submitted_email'    => $isAnonymous ? null : $request->email,
            'visibility'         => 'public',
            'is_anonymous'       => $isAnonymous,
            'is_urgent'          => 0,
            'source'             => 'web',
            'moderation_status'  => 'pending',
            'contact_preference' => $pref,
        ]);

        if ($matchedUserId) {
            PrayerRequestNote::create([
                'prayer_request_id' => $prayer->id,
                'user_id'           => $matchedUserId,
                'note'              => 'Submitted via public prayer wall. Contact preference: ' . str_replace('_', ' ', $pref) . '.',
                'type'              => 'status_change',
            ]);
        }

        // Alert designated pastors about new submission
        $this->sendPastorAlerts($prayer, $settings);

        return back()->with('success', 'Your prayer request has been submitted. It will appear on the prayer wall after review.');
    }

    public function prayedFor($id)
    {
        $prayer = PrayerRequest::publicApproved()->findOrFail($id);
        $prayer->increment('prayer_count');

        return response()->json(['success' => true, 'count' => $prayer->fresh()->prayer_count]);
    }

    private function sendPastorAlerts(PrayerRequest $prayer, $settings)
    {
        // Fetch pastors with phone numbers (from pastors table + Super Admin role)
        $pastors = DB::table('users')
            ->join('pastors', 'pastors.user_id', '=', 'users.id')
            ->where('users.status', 1)
            ->whereNotNull('users.phone')
            ->where('users.phone', '!=', '')
            ->select('users.id', 'users.phone')
            ->distinct()
            ->get();

        // Also include Super Admins
        $superAdmins = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'Super Admin')
            ->where('users.status', 1)
            ->whereNotNull('users.phone')
            ->where('users.phone', '!=', '')
            ->select('users.id', 'users.phone')
            ->distinct()
            ->get();

        // Merge and deduplicate
        $allRecipients = $pastors->merge($superAdmins)->unique('id');

        if ($allRecipients->isEmpty()) return;

        $desc = mb_substr(strip_tags($prayer->description), 0, 100);
        $pref = str_replace('_', ' ', $prayer->contact_preference);
        $message = "NEW PRAYER REQUEST from website: \"{$prayer->title}\" - {$desc}. Preference: {$pref}. Please check the dashboard to review.";

        $smsService = new SendSMSController();
        $phoneCode = $settings->phone_code ?? '254';
        $sent = 0;

        $mid = DB::table('sms')->insertGetId([
            'people_id' => 0,
            'message'   => $message,
            'category'  => 'prayer',
            'sent'      => Carbon::now(),
        ]);

        foreach ($allRecipients as $recipient) {
            $number = $recipient->phone;
            if (substr($number, 0, 1) === '0') {
                $number = $phoneCode . substr($number, 1);
            }
            $smsService->sendSMS($number, $message);
            DB::table('sms_recipients')->insert([
                'recipients' => $recipient->id,
                'sms_id'     => $mid,
                'sent'       => Carbon::now(),
            ]);
            $sent++;
        }

        if ($prayer->submitted_by) {
            PrayerRequestNote::create([
                'prayer_request_id' => $prayer->id,
                'user_id'           => $prayer->submitted_by,
                'note'              => "New submission alert sent to {$sent} pastor(s)/admin(s).",
                'type'              => 'sms_sent',
            ]);
        }
    }
}
