<?php

namespace App\Http\Controllers\Dashboard\PrayerRequests;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Services\SendSMSController;
use App\Models\PrayerRequest;
use App\Models\PrayerRequestNote;
use App\Models\PrayerTag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PrayerRequestController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Prayer Requests']);
    }

    public function index()
    {
        $newCount       = PrayerRequest::where('status', 'new')->count();
        $inProgressCount = PrayerRequest::where('status', 'in_progress')->count();
        $urgentCount    = PrayerRequest::where('is_urgent', 1)->whereNotIn('status', ['answered', 'closed'])->count();
        $answeredCount  = PrayerRequest::where('status', 'answered')->count();
        $totalCount     = PrayerRequest::count();
        $tags           = PrayerTag::orderBy('name')->get();

        return view('dashboard.prayer-requests.index', compact(
            'newCount', 'inProgressCount', 'urgentCount', 'answeredCount', 'totalCount', 'tags'
        ));
    }

    public function datatable(Request $request)
    {
        $query = PrayerRequest::select('prayer_requests.*')
            ->with(['submitter', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('is_urgent')) {
            $query->where('is_urgent', $request->is_urgent);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('filter') && $request->filter === 'moderation') {
            $query->where('visibility', 'public')->where('moderation_status', 'pending');
        }

        return DataTables::of($query)
            ->addColumn('submitted_by_name', function ($row) {
                if ($row->is_anonymous) return 'Anonymous';
                if ($row->submitter) return $row->submitter->firstname . ' ' . $row->submitter->lastname;
                return $row->submitted_name ?? 'Guest';
            })
            ->addColumn('assigned_to_name', function ($row) {
                return $row->assignee ? $row->assignee->firstname . ' ' . $row->assignee->lastname : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $colors = [
                    'new'        => 'primary',
                    'in_progress' => 'warning',
                    'prayed_for' => 'info',
                    'follow_up'  => 'secondary',
                    'answered'   => 'success',
                    'closed'     => 'dark',
                ];
                $color = $colors[$row->status] ?? 'secondary';
                $label = ucwords(str_replace('_', ' ', $row->status));
                return '<span class="badge badge-' . $color . '">' . $label . '</span>';
            })
            ->addColumn('urgency', function ($row) {
                return $row->is_urgent
                    ? '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Urgent</span>'
                    : '';
            })
            ->addColumn('date_fmt', function ($row) {
                return $row->created_at->format('M d, Y');
            })
            ->rawColumns(['status_badge', 'urgency'])
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('created_at', $order);
            })
            ->make(true);
    }

    public function show($id)
    {
        $prayer = PrayerRequest::with(['submitter', 'assignee', 'moderator', 'tags', 'notes.user'])->findOrFail($id);
        $tags = PrayerTag::orderBy('name')->get();
        $users = DB::table('users')->select('id', 'firstname', 'lastname')->where('status', 1)->orderBy('firstname')->get();

        return view('dashboard.prayer-requests.show', compact('prayer', 'tags', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'nullable|string|max:50',
            'visibility'  => 'required|in:public,staff_only,confidential',
            'is_urgent'   => 'nullable|boolean',
            'is_anonymous' => 'nullable|boolean',
            'request_type' => 'nullable|in:prayer,praise_report',
        ]);

        $prayer = PrayerRequest::create([
            'title'        => $request->title,
            'description'  => $request->description,
            'category'     => $request->category,
            'request_type' => $request->request_type ?? 'prayer',
            'submitted_by' => Auth::id(),
            'visibility'   => $request->visibility,
            'is_anonymous' => $request->is_anonymous ?? 0,
            'is_urgent'    => $request->is_urgent ?? 0,
            'source'       => 'dashboard',
            'moderation_status' => ($request->visibility === 'public') ? 'pending' : 'approved',
        ]);

        if ($request->filled('tags')) {
            $prayer->tags()->sync($request->tags);
        }

        PrayerRequestNote::create([
            'prayer_request_id' => $prayer->id,
            'user_id'           => Auth::id(),
            'note'              => 'Prayer request created from dashboard.',
            'type'              => 'status_change',
        ]);

        // Urgent SMS alert
        if ($prayer->is_urgent) {
            $this->sendUrgentAlerts($prayer);
        }

        return response()->json(['success' => 'Prayer request created successfully.']);
    }

    public function update(Request $request, $id)
    {
        $prayer = PrayerRequest::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'nullable|string|max:50',
            'visibility'  => 'required|in:public,staff_only,confidential',
            'is_urgent'   => 'nullable|boolean',
            'request_type' => 'nullable|in:prayer,praise_report',
        ]);

        $prayer->update($request->only([
            'title', 'description', 'category', 'visibility', 'is_urgent', 'request_type',
        ]));

        if ($request->filled('tags')) {
            $prayer->tags()->sync($request->tags);
        }

        return response()->json(['success' => 'Prayer request updated successfully.']);
    }

    public function addNote(Request $request, $id)
    {
        $prayer = PrayerRequest::findOrFail($id);

        $request->validate(['note' => 'required|string']);

        PrayerRequestNote::create([
            'prayer_request_id' => $prayer->id,
            'user_id'           => Auth::id(),
            'note'              => $request->note,
            'type'              => 'note',
        ]);

        return response()->json(['success' => 'Note added successfully.']);
    }

    public function assign(Request $request, $id)
    {
        $prayer = PrayerRequest::findOrFail($id);

        $request->validate(['assigned_to' => 'required|exists:users,id']);

        $prayer->update(['assigned_to' => $request->assigned_to]);

        $user = DB::table('users')->where('id', $request->assigned_to)->first();
        PrayerRequestNote::create([
            'prayer_request_id' => $prayer->id,
            'user_id'           => Auth::id(),
            'note'              => 'Assigned to ' . $user->firstname . ' ' . $user->lastname,
            'type'              => 'assignment',
        ]);

        return response()->json(['success' => 'Prayer request assigned.']);
    }

    public function changeStatus(Request $request, $id)
    {
        $prayer = PrayerRequest::findOrFail($id);

        $request->validate(['status' => 'required|in:new,in_progress,prayed_for,follow_up,answered,closed']);

        $old = $prayer->status;
        $prayer->status = $request->status;

        if ($request->status === 'answered') {
            $prayer->answered_at = now();
            
            // Step Up Logic: Suggest Discipleship Track based on tags
            $track = null;
            if($prayer->tags->contains('name', 'Salvation')) {
                 $track = \App\Models\Discipleship\Track::where('title', 'like', '%New Believer%')->first();
            } elseif($prayer->tags->contains('name', 'Addiction')) {
                 $track = \App\Models\Discipleship\Track::where('title', 'like', '%Recovery%')->first();
            }

            if($track && $prayer->submitter) {
                 // Check if not already enrolled
                 $enrolled = \App\Models\Discipleship\Enrollment::where('user_id', $prayer->submitter->id)
                    ->where('track_id', $track->id)->exists();
                 
                 if(!$enrolled) {
                     // Auto-suggest via Note
                     PrayerRequestNote::create([
                        'prayer_request_id' => $prayer->id,
                        'user_id' => Auth::id(),
                        'note' => "System Suggestion: Invite " . $prayer->submitter->firstname . " to the '" . $track->title . "' track.",
                        'type' => 'system_alert'
                     ]);
                 }
            }
        }
        if ($request->status === 'closed') {
            $prayer->closed_at = now();
        }

        $prayer->save();

        PrayerRequestNote::create([
            'prayer_request_id' => $prayer->id,
            'user_id'           => Auth::id(),
            'note'              => 'Status changed from ' . ucwords(str_replace('_', ' ', $old)) . ' to ' . ucwords(str_replace('_', ' ', $request->status)),
            'type'              => 'status_change',
        ]);

        return response()->json(['success' => 'Status updated.']);
    }

    public function moderate(Request $request, $id)
    {
        $prayer = PrayerRequest::findOrFail($id);

        $request->validate(['action' => 'required|in:approved,rejected']);

        $prayer->update([
            'moderation_status' => $request->action,
            'moderated_by'      => Auth::id(),
            'moderated_at'      => now(),
        ]);

        PrayerRequestNote::create([
            'prayer_request_id' => $prayer->id,
            'user_id'           => Auth::id(),
            'note'              => 'Prayer wall moderation: ' . $request->action,
            'type'              => 'status_change',
        ]);

        return response()->json(['success' => 'Moderation action saved.']);
    }

    public function delete($id)
    {
        $prayer = PrayerRequest::findOrFail($id);
        $prayer->delete();

        return redirect()->to('dashboard/prayer-requests')->with('success', 'Prayer request deleted.');
    }

    public function stats()
    {
        return response()->json([
            'new'         => PrayerRequest::where('status', 'new')->count(),
            'in_progress' => PrayerRequest::where('status', 'in_progress')->count(),
            'urgent'      => PrayerRequest::where('is_urgent', 1)->whereNotIn('status', ['answered', 'closed'])->count(),
            'answered'    => PrayerRequest::where('status', 'answered')->count(),
            'total'       => PrayerRequest::count(),
        ]);
    }

    public function prayedFor($id)
    {
        $prayer = PrayerRequest::findOrFail($id);
        $prayer->increment('prayer_count');

        return response()->json(['success' => true, 'count' => $prayer->fresh()->prayer_count]);
    }

    private function sendUrgentAlerts(PrayerRequest $prayer)
    {
        // Fetch pastors + super admins with phones
        $pastors = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereIn('roles.name', ['Pastor', 'Super Admin'])
            ->where('users.status', 1)
            ->whereNotNull('users.phone')
            ->where('users.phone', '!=', '')
            ->select('users.id', 'users.phone')
            ->distinct()
            ->get();

        if ($pastors->isEmpty()) return;

        $desc = mb_substr(strip_tags($prayer->description), 0, 100);
        $message = "URGENT PRAYER: {$prayer->title} - {$desc}. View: " . url("dashboard/prayer-requests/{$prayer->id}");

        $smsService = new SendSMSController();
        $phoneCode = $this->site_settings->phone_code ?? '254';
        $sent = 0;

        $mid = DB::table('sms')->insertGetId([
            'people_id' => 0,
            'message'   => $message,
            'category'  => 'prayer',
            'sent'      => Carbon::now(),
        ]);

        foreach ($pastors as $pastor) {
            $number = $pastor->phone;
            if (substr($number, 0, 1) === '0') {
                $number = $phoneCode . substr($number, 1);
            }
            $smsService->sendSMS($number, $message);
            DB::table('sms_recipients')->insert([
                'recipients' => $pastor->id,
                'sms_id'     => $mid,
                'sent'       => Carbon::now(),
            ]);
            $sent++;
        }

        PrayerRequestNote::create([
            'prayer_request_id' => $prayer->id,
            'user_id'           => Auth::id() ?? $prayer->submitted_by ?? 0,
            'note'              => "Urgent alert sent to {$sent} pastor(s).",
            'type'              => 'sms_sent',
        ]);
    }
}
