<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Http\Controllers\Dashboard\DashboardController;
use App\Mail\MyEmail;
use App\Models\Email;
use App\Models\EmailRecipient;
use App\Models\Invitation;
use App\Models\Maturity;
use App\Models\Share;
use App\Models\ShareTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mail;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use DB;

class UsersController extends DashboardController
{
    use \App\Traits\AutoHashesMpesaPhone;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Users']);
    }
    public function index()
    {
        $users = User::doesntHave('roles')->get();
        $role = Role::where('name', 'User')->first();
        foreach($users as $user){
            $user->assignRole($role);
        }
        $communities = DB::table('people')->where('user_group', 1)->orderBy('name', 'ASC')->get();
        $departments = DB::table('people')->where('user_group', 2)->orderBy('name', 'ASC')->get();
        return view('dashboard.users.users', compact('communities', 'departments'));
    }
    public function getUsers(Request $request)
    {
        $users = User::with('roles');
        if (auth()->user()->roles[0]->name != "Super Admin") {
            $users = $users->whereHas('roles', function ($query) {
                $query->where('name', '<>', 'Super Admin');
            });
        }
        return DataTables::of($users->orderBy('firstname', 'ASC'))
            ->filter(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where(DB::Raw('CONCAT(firstname, " ", lastname)'), 'LIKE', '%' . $request->search . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $request->search . '%');
                });
                if ($request->status == 'archived') {
                    $query->whereNotNull('archived_at');
                } else {
                    $query->whereNull('archived_at');
                    if ($request->status != "") {
                        $query->where('status', $request->status);
                    }
                }
                if ($request->role > 0) {
                    $query->whereHas('roles', function ($q) use ($request) {
                        $q->where('id', $request->role);
                    });
                }
            })->addColumn('id', function ($row) {
                return $row->id;
            })->addColumn('name', function ($row) use ($request) {
                $initials = strtoupper(substr($row->firstname, 0, 1) . substr($row->lastname, 0, 1));
                $colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796','#5a5c69','#6f42c1','#e83e8c','#fd7e14'];
                $color = $colors[$row->id % count($colors)];
                return "<div class='d-flex align-items-center'>
                            <div style='width:35px;height:35px;border-radius:50%;background:{$color};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;margin-right:10px;flex-shrink:0;'>{$initials}</div>
                            <span>".$row->firstname." ".$row->lastname."</span>
                        </div>";
            })->addColumn('status', function ($row) {
                return $row->status ? "<span class='badge bg-primary'>Active</span>" : "<span class='badge bg-danger'>In-Active</span>";
            })->addColumn('role', function ($row) {
                return $row->roles->count() > 0 ? $row->roles[0]->name : "-";
            })->addColumn('action', function ($row) {
                $actionBtn = '<div class="text-end">' .
                    '<span class="d-none id">' . $row->id . '</span>' .
                    '<span class="d-none firstname">' . $row->firstname . '</span>' .
                    '<span class="d-none lastname">' . $row->lastname . '</span>' .
                    '<span class="d-none role_id">' . ($row->roles->count() > 0 ? $row->roles[0]->id : 0) . '</span>' .
                    '<span class="d-none role_name">' . ($row->roles->count() > 0 ? $row->roles[0]->name : "") . '</span>' .
                    '<span class="d-none surname">' . $row->surname . '</span>' .
                    '<span class="d-none email">' . $row->email . '</span>' .
                    '<span class="d-none phone">' . $row->phone . '</span>' .
                    '<span class="d-none status">' . $row->status . '</span>' .
                    '<div class="dropdown">' .
                    '<button class="btn btn-sm btn-light border-0" type="button" data-toggle="dropdown" aria-expanded="false">' .
                    '<i class="fas fa-ellipsis-v"></i></button>' .
                    '<div class="dropdown-menu dropdown-menu-right shadow-sm">';
                if ($row->archived_at) {
                    // Archived user actions
                    if (auth()->user()->can('Add Users'))
                        $actionBtn .= '<button class="dropdown-item btn-unarchive">' .
                            '<i class="fas fa-undo text-success me-2"></i> Unarchive</button>';
                    $actionBtn .= '<a href="' . url('dashboard/users/view/' . $row->id) . '" class="dropdown-item">' .
                        '<i class="fas fa-eye text-info me-2"></i> View</a>';
                    if (auth()->user()->can('Delete Users'))
                        $actionBtn .= '<button class="dropdown-item btn-delete-permanent text-danger">' .
                            '<i class="fas fa-trash me-2"></i> Delete Permanently</button>';
                } else {
                    // Active user actions
                    if ($row->roles->count() > 0 && $row->roles[0]->name != 'Super Admin' && $row->id != auth()->user()->id)
                        $actionBtn .= '<button class="dropdown-item btn-edit" data-toggle="modal" data-target="#userModal">' .
                            '<i class="fas fa-edit text-primary me-2"></i> Edit</button>';
                    $actionBtn .= '<a href="' . url('dashboard/users/view/' . $row->id) . '" class="dropdown-item">' .
                        '<i class="fas fa-eye text-info me-2"></i> View</a>';
                    if ($row->phone)
                        $actionBtn .= '<button class="dropdown-item btn-sms" data-toggle="modal" data-target="#smsModal">' .
                            '<i class="fas fa-sms text-success me-2"></i> Send SMS</button>';
                    if (auth()->user()->can('Add Users') && $row->id != auth()->user()->id)
                        $actionBtn .= '<div class="dropdown-divider"></div>' .
                            '<button class="dropdown-item btn-archive text-warning">' .
                            '<i class="fas fa-archive me-2"></i> Archive</button>';
                }
                $actionBtn .= '</div></div></div>';
                return $actionBtn;
            })->addIndexColumn()->escapeColumns([])->make();
    }
    public function addUser(Request $request)
    {
        // Normalize phone number before validation
        if ($request->phone) {
            $phoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            if (strlen($phone) <= 10) {
                $phone = $phoneCode . ltrim($phone, '0');
            }
            $request->merge(['phone' => $phone]);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:0',
            'firstname' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $request->id,
            'phone' => 'required|numeric|digits_between:10,15|unique:users,phone,' . $request->id,
            'status' => 'required|min:0|max:1|integer',
            // Personal
            'gender' => 'nullable|integer',
            'dob' => 'nullable|date',
            'marital' => 'nullable|integer',
            'country' => 'nullable|integer',
            // Contact
            'secondary_phone' => 'nullable|string',
            'town' => 'nullable|string|max:255',
            'postal' => 'nullable|string',
            'resident' => 'nullable|string',
            // Church
            'community' => 'nullable|integer',
            'department' => 'nullable|integer',
            'year_joined' => 'nullable|integer',
            'gifts' => 'nullable|string|max:255',
            'previous_church' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            // Family
            'relationship' => 'nullable|integer',
            'family_name' => 'nullable|string|max:255',
            'family_phone' => 'nullable|string|max:255',
            'family_email' => 'nullable|email|max:255',
            // Work
            'occupation' => 'nullable|integer',
            'specific_job' => 'nullable|string|max:255',
            'work_institution' => 'nullable|string|max:255',
            'work_from' => 'nullable|date',
            'work_to' => 'nullable|date',
            // Education
            'edu_level' => 'nullable|integer',
            'edu_institution' => 'nullable|string|max:255',
            'certification' => 'nullable|string|max:255',
            'edu_from' => 'nullable|date',
            'edu_to' => 'nullable|date',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $user = new User;
        if ($request->id > 0) {
            $user = User::find($request->id);
        } else {
            $user->password = Hash::make(Str::random(16));
            $user->must_change_password = true;
        }
        $user->firstname = $request->firstname;
        $user->surname = $request->surname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;
        if ($user->save()) {
            // Auto-assign "Member" role for new users
            if ($request->id == 0) {
                $role = Role::where('name', 'Member')->first();
                if (!$role) $role = Role::where('name', 'User')->first();
                if ($role) $user->assignRole($role->name);
            }

            // Save personal details to contacts table
            if ($request->filled('gender') || $request->filled('dob') || $request->filled('marital') || $request->filled('country')) {
                $contactData = [];
                if ($request->filled('gender')) $contactData['gender'] = $request->gender;
                if ($request->filled('dob')) $contactData['dob'] = $request->dob;
                if ($request->filled('marital')) $contactData['marital'] = $request->marital;
                if ($request->filled('country')) $contactData['country'] = $request->country;
                DB::table('contacts')->updateOrInsert(['user_id' => $user->id], $contactData);
            }

            // Save secondary contact details
            if ($request->filled('secondary_phone') || $request->filled('town') || $request->filled('postal') || $request->filled('resident')) {
                $scontactData = [];
                if ($request->filled('secondary_phone')) $scontactData['secondary_phone'] = $request->secondary_phone;
                if ($request->filled('town')) $scontactData['town'] = $request->town;
                if ($request->filled('postal')) $scontactData['address'] = $request->postal;
                if ($request->filled('resident')) $scontactData['resident'] = $request->resident;
                DB::table('scontacts')->updateOrInsert(['user_id' => $user->id], $scontactData);
            }

            // Save church details
            if ($request->filled('community') || $request->filled('department') || $request->filled('year_joined') || $request->filled('gifts') || $request->filled('previous_church') || $request->filled('position') || $request->filled('remarks')) {
                $churchData = [];
                if ($request->filled('community')) $churchData['communities'] = $request->community;
                if ($request->filled('department')) $churchData['departments'] = $request->department;
                if ($request->filled('year_joined')) $churchData['joined'] = $request->year_joined;
                if ($request->filled('gifts')) $churchData['gifts'] = $request->gifts;
                if ($request->filled('previous_church')) $churchData['previous_church'] = $request->previous_church;
                if ($request->filled('position')) $churchData['position'] = $request->position;
                if ($request->filled('remarks')) $churchData['remarks'] = $request->remarks;
                DB::table('church')->updateOrInsert(['user_id' => $user->id], $churchData);
            }

            // Save family details
            if ($request->filled('relationship') || $request->filled('family_name') || $request->filled('family_phone') || $request->filled('family_email')) {
                $familyData = [];
                if ($request->filled('relationship')) $familyData['relationship'] = $request->relationship;
                if ($request->filled('family_name')) $familyData['name'] = $request->family_name;
                if ($request->filled('family_phone')) $familyData['phone'] = $request->family_phone;
                if ($request->filled('family_email')) $familyData['email'] = $request->family_email;
                DB::table('families')->updateOrInsert(['user_id' => $user->id], $familyData);
            }

            // Save profession details
            if ($request->filled('occupation') || $request->filled('specific_job') || $request->filled('work_institution') || $request->filled('work_from') || $request->filled('work_to')) {
                $profData = [];
                if ($request->filled('occupation')) $profData['occupation'] = $request->occupation;
                if ($request->filled('specific_job')) $profData['specific'] = $request->specific_job;
                if ($request->filled('work_institution')) $profData['institution'] = $request->work_institution;
                if ($request->filled('work_from')) $profData['from'] = $request->work_from;
                if ($request->filled('work_to')) $profData['to'] = $request->work_to;
                DB::table('professions')->updateOrInsert(['user_id' => $user->id], $profData);
            }

            // Save education details
            if ($request->filled('edu_level') || $request->filled('edu_institution') || $request->filled('certification') || $request->filled('edu_from') || $request->filled('edu_to')) {
                $eduData = [];
                if ($request->filled('edu_level')) $eduData['level'] = $request->edu_level;
                if ($request->filled('edu_institution')) $eduData['institution'] = $request->edu_institution;
                if ($request->filled('certification')) $eduData['certification'] = $request->certification;
                if ($request->filled('edu_from')) $eduData['from'] = $request->edu_from;
                if ($request->filled('edu_to')) $eduData['to'] = $request->edu_to;
                DB::table('education')->updateOrInsert(['user_id' => $user->id], $eduData);
            }

            // Auto-create mpesa hash and retro-match funds
            if ($user->phone) {
                $this->createMpesaHashAndMatch($user->phone, $user->firstname . ' ' . $user->lastname, $user->id);
            }

            return response()->json(['success' => 'User saved successfully!']);
        } else {
            return response()->json(['error' => 'Unable to save user'], 401);
        }
    }

    public function checkInvitePhone(Request $request)
    {
        $phoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
        $phone = preg_replace('/[^0-9]/', '', $request->phone ?? '');
        if (strlen($phone) <= 10) {
            $phone = $phoneCode . ltrim($phone, '0');
        }

        $result = ['status' => 'ok'];

        // Check if user already exists in the system
        $existingUser = User::where('phone', $phone)->first();
        if ($existingUser) {
            $name = trim($existingUser->firstname . ' ' . $existingUser->lastname);
            $verified = (bool) $existingUser->phone_verified_at;
            $result = [
                'status' => 'user_exists',
                'user_name' => $name ?: 'User #' . $existingUser->id,
                'user_id' => $existingUser->id,
                'profile_url' => url('dashboard/users/view/' . $existingUser->id),
                'verified' => $verified,
                'message' => $verified
                    ? "This phone belongs to {$name} who is already verified."
                    : "This phone belongs to {$name} who has not yet verified.",
            ];
            return response()->json($result);
        }

        // Check for previous invitations
        $previousInvite = Invitation::where('phone', $phone)
            ->orderBy('created_at', 'DESC')
            ->first();
        if ($previousInvite) {
            $status = $previousInvite->status;
            if (in_array($status, ['pending', 'started']) && $previousInvite->expires_at < now()) {
                $status = 'expired';
            }
            $result = [
                'status' => 'previously_invited',
                'invite_status' => $status,
                'invite_date' => $previousInvite->created_at->format('d M, Y'),
                'invite_id' => $previousInvite->id,
                'message' => "This number was already invited on {$previousInvite->created_at->format('d M, Y')} (Status: {$status}).",
            ];
        }

        return response()->json($result);
    }

    public function inviteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:sms,email',
            'phone' => 'required_if:method,sms|nullable|string',
            'email' => 'required_if:method,email|nullable|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $phoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
        $token = Str::random(64);
        $onboardingUrl = url('onboarding/' . $token);
        $church_name = $this->site_settings != null ? $this->site_settings->name : 'Our Church';

        // Normalize phone if SMS
        $phone = null;
        if ($request->method === 'sms' && $request->phone) {
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            if (strlen($phone) <= 10) {
                $phone = $phoneCode . ltrim($phone, '0');
            }
        }

        // Check if user is already verified — block re-invitation
        if ($request->method === 'sms' && $phone) {
            $existingUser = User::where('phone', $phone)->first();
            if ($existingUser && $existingUser->phone_verified_at) {
                return response()->json([
                    'error' => 'This user (' . trim($existingUser->firstname . ' ' . $existingUser->lastname) . ') is already verified. No invitation needed.',
                    'profile_url' => url('dashboard/users/view/' . $existingUser->id),
                ], 400);
            }
        }

        // === Rate limiting: 60 seconds between SMS invitations to same number ===
        if ($request->method === 'sms' && $phone) {
            $recentSms = DB::table('sms')
                ->where('category', 'invitation')
                ->where('sent', '>=', Carbon::now()->subSeconds(60))
                ->join('sms_recipients', 'sms_recipients.sms_id', '=', 'sms.id')
                ->where(function ($q) use ($phone) {
                    $q->where('sms_recipients.phone', $phone)
                      ->orWhere(function ($q2) use ($phone) {
                          $q2->where('sms_recipients.recipients', '>', 0)
                             ->whereExists(function ($q3) use ($phone) {
                                 $q3->select(DB::raw(1))->from('users')
                                    ->whereColumn('users.id', 'sms_recipients.recipients')
                                    ->where('users.phone', $phone);
                             });
                      });
                })
                ->exists();
            if ($recentSms) {
                return response()->json(['error' => 'An SMS was recently sent to this number. Please wait 60 seconds before resending.'], 429);
            }
        }

        // Create invitation record
        $invitation = Invitation::create([
            'token' => $token,
            'phone' => $phone,
            'email' => $request->email,
            'via' => $request->method,
            'invited_by' => Auth::id(),
            'status' => 'pending',
            'onboarding_step' => 0,
            'expires_at' => now()->addDays(30),
        ]);

        if ($request->method === 'sms') {
            $message = "You have been invited to join {$church_name}. Click the link to register: {$onboardingUrl}";

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => env('SMS_URL'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => 'apikey=' . env('SMS_API_KEY') . '&partnerID=' . env('SMS_PARTNER_ID') . '&message=' . urlencode($message) . '&shortcode=' . env('SMS_SHORT_CODE') . '&mobile=' . $phone,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);
            curl_exec($curl);
            curl_close($curl);

            // Log SMS to sms table
            $mid = DB::table('sms')->insertGetId([
                'people_id' => 0,
                'message' => $message,
                'category' => 'invitation',
                'sent' => Carbon::now(),
            ]);

            // Always log recipient — use user_id if exists, otherwise store phone number
            $existingUser = DB::table('users')->where('phone', $phone)->first();
            DB::table('sms_recipients')->insert([
                'recipients' => $existingUser ? $existingUser->id : 0,
                'phone' => $phone,
                'sms_id' => $mid,
                'sent' => Carbon::now(),
            ]);

            return response()->json(['success' => 'Invitation SMS sent to ' . $phone]);
        } else {
            $message = "Dear friend,<br><br>" .
                "You have been invited to join {$church_name}.<br><br>" .
                "Please click the link below to create your account:<br>" .
                "<a href='{$onboardingUrl}'>{$onboardingUrl}</a><br><br>" .
                "This link will expire in 30 days.<br><br>" .
                "Thank you,<br>{$church_name}";

            $data = ['name' => 'Friend', 'mes' => $message];

            try {
                \Mail::send('dashboard.communication.mail', $data, function ($mail) use ($request, $church_name) {
                    $mail->to($request->email)->subject("Invitation to Join - {$church_name}");
                });
            } catch (\Exception $e) {
                \Log::error('Invitation email failed: ' . $e->getMessage());
            }

            // Log email
            DB::table('emails')->insertGetId([
                'subject' => "Invitation to Join - {$church_name}",
                'message' => $message,
                'category' => 'invitation',
                'sent' => Carbon::now(),
            ]);

            return response()->json(['success' => 'Invitation email sent to ' . $request->email]);
        }
    }

    public function invitations()
    {
        $unverifiedPhone = User::whereNull('archived_at')
            ->whereNull('phone_verified_at')
            ->whereNotNull('phone')->where('phone', '<>', '')
            ->count();
        $unverifiedEmail = User::whereNull('archived_at')
            ->whereNull('email_verified_at')
            ->whereNotNull('email')->where('email', '<>', '')
            ->count();

        return view('dashboard.users.invitations', compact('unverifiedPhone', 'unverifiedEmail'));
    }

    public function invitationsDataTable()
    {
        $invitations = Invitation::with(['invitedBy', 'user'])
            ->orderBy('created_at', 'DESC');

        return DataTables::of($invitations)
            ->addColumn('contact', function ($row) {
                return $row->via === 'sms' ? $row->phone : $row->email;
            })
            ->addColumn('method', function ($row) {
                return $row->via === 'sms'
                    ? '<span class="badge bg-info">SMS</span>'
                    : '<span class="badge bg-primary">Email</span>';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'pending' => 'bg-secondary',
                    'started' => 'bg-warning',
                    'completed' => 'bg-success',
                    'expired' => 'bg-danger',
                ];
                // Check if expired
                $status = $row->status;
                if (in_array($status, ['pending', 'started']) && $row->expires_at < now()) {
                    $status = 'expired';
                }
                $badge = $badges[$status] ?? 'bg-secondary';
                return '<span class="badge ' . $badge . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('invited_by_name', function ($row) {
                return $row->invitedBy ? $row->invitedBy->firstname . ' ' . $row->invitedBy->lastname : '-';
            })
            ->addColumn('linked_user', function ($row) {
                if ($row->user) {
                    return '<a href="' . url('dashboard/users/view/' . $row->user->id) . '">' .
                        $row->user->firstname . ' ' . $row->user->lastname . '</a>';
                }
                return '-';
            })
            ->addColumn('step', function ($row) {
                return $row->onboarding_step . '/3';
            })
            ->addColumn('date', function ($row) {
                return $row->created_at->format('d M, Y H:i');
            })
            ->rawColumns(['method', 'status_badge', 'linked_user'])
            ->make(true);
    }

    public function viewUser(Request $request)
    {
        $user = User::find($request->id);
        if ($user == null) {
            return redirect()->to('home');
        }
        $roles = Role::where("name", "<>", "Super Admin")->get();
        $scontact = DB::table("scontacts")->where("user_id", $request->id)->first();
        $communities = DB::table('communities')->orderBy("name", "ASC")->get();
        $departments = DB::table('departments')->orderBy("name", "ASC")->get();

        $emergency = DB::table("emergency_contact")->where("user_id", $request->id)->leftJoin("users", "users.id", "emergency_contact.user_id2")->first();
        $user = DB::table("users")->select("users.id", "users.firstname", "users.surname", "users.lastname", "users.email", "users.phone", "users.email_verified_at", "users.phone_verified_at", "users.created_at as member_since", "contacts.gender", "contacts.marital",
            "church.communities", "church.departments", "church.joined as yearjoined", "church.gifts", "church.remarks", "church.previous_church", "church.position",
            "contacts.country", "contacts.dob", "contacts.joined", "profiles.name", "families.name as familyname", "families.relationship", "families.image", "families.email as familyemail",
            "families.phone as familyphone", "professions.occupation", "professions.specific", "professions.institution", "professions.from", "professions.to", "education.level", "education.institution as inst",
            "education.certification", "education.from as fr", "education.to as t")->where("users.id", $request->id)->leftJoin("profiles", "profiles.user_id", "=", "users.id")->
            leftJoin("contacts", "contacts.user_id", "=", "users.id")->leftJoin("church", "church.user_id", "users.id")->
            leftJoin("families", "families.user_id", "=", "users.id")->leftJoin("professions", "professions.user_id", "=", "users.id")
            ->leftJoin("education", "education.user_id", "=", "users.id")->first();
        $userModel = User::with('roles')->find($request->id);
        $userRole = $userModel && $userModel->roles->count() > 0 ? $userModel->roles[0] : null;
        try {
            $userTags = DB::table('tag_user')->where('user_id', $request->id)
                ->join('tags', 'tags.id', '=', 'tag_user.tag_id')
                ->select('tags.id', 'tags.name', 'tags.color')->get();
        } catch (\Exception $e) {
            $userTags = collect();
        }
        return view("dashboard.users.user")->with("roles", $roles)->with("user", $user)->with("scontact", $scontact)->with("emergency", $emergency)
        ->with("communities", $communities)->with("departments", $departments)->with("userRole", $userRole)->with("userTags", $userTags);
    }

    public function updateBasic(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
            'fname' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $request->id,
            'phone' => 'required|string',
            'role' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($request->id);
        $user->firstname = $request->fname;
        $user->surname = $request->surname;
        $user->lastname = $request->lname;
        $user->email = $request->email;

        // Reconstruct full phone with country code
        $phoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (strlen($phone) <= 10) {
            $phone = $phoneCode . ltrim($phone, '0');
        }

        // Check for phone conflict with another user
        $conflicting = User::where('phone', $phone)->where('id', '!=', $user->id)->first();
        if ($conflicting) {
            return back()->withErrors([
                'phone_conflict' => 'Phone number ' . $phone . ' is already assigned to ' . $conflicting->firstname . ' ' . $conflicting->lastname . ' (ID: ' . $conflicting->id . ').',
            ])->with('phone_conflict_user', [
                'id' => $conflicting->id,
                'name' => $conflicting->firstname . ' ' . $conflicting->lastname,
                'phone' => $conflicting->phone,
                'current_user_id' => $user->id,
                'current_user_name' => $user->firstname . ' ' . $user->lastname,
            ])->withInput();
        }

        $user->phone = $phone;
        $user->save();

        $role = Role::find($request->role);
        if ($role) {
            $user->syncRoles([$role->name]);
        }

        // Save contacts data
        $contactData = [
            'gender' => $request->gender,
            'marital' => $request->marital,
            'country' => $request->country,
            'dob' => $request->dob,
            'joined' => $request->joined,
        ];
        DB::table('contacts')->updateOrInsert(
            ['user_id' => $request->id],
            $contactData
        );

        // Handle profile image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('profile_images'), $imageName);
            DB::table('profiles')->updateOrInsert(
                ['user_id' => $request->id],
                ['name' => $imageName]
            );
        }

        return back()->with('success', 'Basic information updated successfully.');
    }

    public function updateContacts(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        // Secondary contact
        $scontactData = [
            'secondary_phone' => $request->phone,
            'town' => $request->town,
            'address' => $request->postal,
            'resident' => $request->resident,
        ];
        DB::table('scontacts')->updateOrInsert(
            ['user_id' => $request->id],
            $scontactData
        );

        // Emergency contact
        if ($request->filled('emergency_user_id')) {
            DB::table('emergency_contact')->updateOrInsert(
                ['user_id' => $request->id],
                ['user_id2' => $request->emergency_user_id]
            );
        }

        return back()->with('success', 'Contact information updated successfully.');
    }

    public function updateChurch(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        DB::table('church')->updateOrInsert(
            ['user_id' => $request->id],
            [
                'communities' => $request->community,
                'departments' => $request->department,
                'joined' => $request->joined,
                'gifts' => $request->gifts,
                'previous_church' => $request->previous,
                'position' => $request->position,
                'remarks' => $request->remarks,
            ]
        );

        return back()->with('success', 'Church information updated successfully.');
    }

    public function updateFamily(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        $familyData = [
            'relationship' => $request->relationship,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ];

        // Handle family photo
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('relatives'), $imageName);
            $familyData['image'] = $imageName;
        }

        DB::table('families')->updateOrInsert(
            ['user_id' => $request->id],
            $familyData
        );

        return back()->with('success', 'Family information updated successfully.');
    }

    public function updateProfession(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        DB::table('professions')->updateOrInsert(
            ['user_id' => $request->id],
            [
                'occupation' => $request->occupation,
                'specific' => $request->specific,
                'institution' => $request->institution,
                'from' => $request->from,
                'to' => $request->to,
            ]
        );

        return back()->with('success', 'Profession information updated successfully.');
    }

    public function updateEducation(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);

        DB::table('education')->updateOrInsert(
            ['user_id' => $request->id],
            [
                'level' => $request->education,
                'institution' => $request->institution,
                'certification' => $request->certification,
                'from' => $request->from,
                'to' => $request->to,
            ]
        );

        return back()->with('success', 'Education information updated successfully.');
    }

    public function sendUserSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1',
            'message' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $user = User::find($request->user_id);
        if (!$user || !$user->phone) {
            return response()->json(['error' => 'User not found or has no phone number'], 400);
        }

        $number = $user->phone;
        if (substr($number, 0, 1) === '0') {
            $number = '254' . substr($number, 1);
        }

        // Send SMS via gateway
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => env('SMS_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => 'apikey=' . env('SMS_API_KEY') . '&partnerID=' . env('SMS_PARTNER_ID') . '&message=' . urlencode($request->message) . '&shortcode=' . env('SMS_SHORT_CODE') . '&mobile=' . $number,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        curl_exec($curl);
        curl_close($curl);

        // Log SMS
        $mid = DB::table('sms')->insertGetId([
            'people_id' => 0,
            'message' => $request->message,
            'sent' => Carbon::now(),
        ]);
        DB::table('sms_recipients')->insert([
            'recipients' => $user->id,
            'sms_id' => $mid,
            'sent' => Carbon::now(),
        ]);

        return response()->json(['success' => 'SMS sent to ' . $user->firstname . ' ' . $user->lastname]);
    }

    public function toggleVerification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|min:1',
            'type' => 'required|in:email,phone',
        ]);

        $user = User::findOrFail($request->user_id);
        $field = $request->type === 'email' ? 'email_verified_at' : 'phone_verified_at';

        if ($user->$field) {
            $user->$field = null;
        } else {
            $user->$field = now();
        }
        $user->save();

        $status = $user->$field ? 'verified' : 'unverified';
        return response()->json([
            'success' => ucfirst($request->type) . ' marked as ' . $status,
            'verified' => (bool) $user->$field,
            'verified_at' => $user->$field ? $user->$field->format('d M, Y') : null,
        ]);
    }

    public function sendVerificationRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|min:1',
            'method' => 'required|in:sms,email',
        ]);

        $user = User::findOrFail($request->user_id);

        // === Rate limiting: 60 seconds between verification SMS to same phone ===
        if ($request->method === 'sms' && $user->phone) {
            $recentSms = DB::table('sms')
                ->where('category', 'verification')
                ->where('sent', '>=', Carbon::now()->subSeconds(60))
                ->join('sms_recipients', 'sms_recipients.sms_id', '=', 'sms.id')
                ->where('sms_recipients.recipients', $user->id)
                ->exists();
            if ($recentSms) {
                return response()->json(['error' => 'A verification SMS was recently sent to this user. Please wait 60 seconds before resending.'], 429);
            }
        }

        // Generate a token
        $token = Str::random(64);
        $user->verification_token = $token;
        $user->verification_token_expires_at = now()->addDays(7);
        $user->save();

        $verifyUrl = url('verify-profile/' . $token);
        $site_settings = $this->site_settings;
        $church_name = $site_settings != null ? $site_settings->name : 'Church App';

        if ($request->method === 'email') {
            if (!$user->email) {
                return response()->json(['error' => 'User does not have an email address.'], 400);
            }

            $message = "Dear {$user->firstname},<br><br>" .
                "You have been requested to verify and complete your profile details for {$church_name}.<br><br>" .
                "Please click the link below to update your profile:<br>" .
                "<a href='{$verifyUrl}'>{$verifyUrl}</a><br><br>" .
                "This link will expire in 7 days.<br><br>" .
                "Thank you,<br>{$church_name}";

            $data = ['name' => $user->firstname . ' ' . $user->lastname, 'mes' => $message];

            try {
                \Mail::send('dashboard.communication.mail', $data, function ($mail) use ($user, $church_name) {
                    $mail->to($user->email, $user->firstname . ' ' . $user->lastname)
                        ->subject('Profile Verification Request');
                    $mail->from('info@happychurchruiru.org', $church_name);
                });

                if (\Mail::failures()) {
                    return response()->json(['error' => 'Failed to send verification email. Please check the mail configuration.'], 500);
                }

                // Log email
                $eid = DB::table('emails')->insertGetId([
                    'subject' => 'Profile Verification Request',
                    'message' => $message,
                    'category' => 'verification',
                    'sent' => Carbon::now(),
                ]);
                DB::table('email_recipients')->insert([
                    'user_id' => $user->id,
                    'email_id' => $eid,
                    'sent' => Carbon::now(),
                ]);

                return response()->json(['success' => 'Verification email sent to ' . $user->email]);
            } catch (\Exception $e) {
                \Log::error('Verification email failed: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
            }
        } else {
            // Send via SMS
            if (!$user->phone) {
                return response()->json(['error' => 'User does not have a phone number.'], 400);
            }

            $number = $user->phone;
            if (substr($number, 0, 1) === '0') {
                $number = '254' . substr($number, 1);
            }

            $smsMessage = "Dear {$user->firstname}, please verify your profile for {$church_name}. Click here: {$verifyUrl} (expires in 7 days)";

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => env('SMS_URL'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => 'apikey=' . env('SMS_API_KEY') . '&partnerID=' . env('SMS_PARTNER_ID') . '&message=' . urlencode($smsMessage) . '&shortcode=' . env('SMS_SHORT_CODE') . '&mobile=' . $number,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);
            $curlResponse = curl_exec($curl);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($curlResponse === false) {
                \Log::error('Verification SMS curl failed: ' . $curlError);
                return response()->json(['error' => 'Failed to connect to SMS gateway: ' . $curlError], 500);
            }

            $response = json_decode($curlResponse, true);
            $apiSuccess = true;
            $apiMessage = '';

            // AdvantaSMS returns response with status codes
            if (is_array($response)) {
                $responseCode = $response['response-code'] ?? $response['response_code'] ?? $response['code'] ?? null;
                $responseDesc = $response['response-description'] ?? $response['response_description'] ?? $response['message'] ?? '';

                if ($responseCode !== null && (int) $responseCode !== 200 && (int) $responseCode !== 0) {
                    $apiSuccess = false;
                    $apiMessage = $responseDesc ?: 'SMS gateway returned error code: ' . $responseCode;
                }
            }

            if (!$apiSuccess) {
                \Log::warning('Verification SMS API error: ' . $apiMessage . ' | Raw: ' . $curlResponse);
                return response()->json(['error' => 'SMS gateway error: ' . $apiMessage], 500);
            }

            // Log SMS with verification category
            $mid = DB::table('sms')->insertGetId([
                'people_id' => 0,
                'message' => $smsMessage,
                'category' => 'verification',
                'sent' => Carbon::now(),
            ]);
            DB::table('sms_recipients')->insert([
                'recipients' => $user->id,
                'sms_id' => $mid,
                'sent' => Carbon::now(),
            ]);

            return response()->json(['success' => 'Verification SMS sent successfully to ' . $user->phone]);
        }
    }

    public function bulkInviteVerification(Request $request)
    {
        $request->validate([
            'type' => 'required|in:phone,email,both',
        ]);

        $type = $request->type;
        $church_name = $this->site_settings != null ? $this->site_settings->name : 'Church App';
        $phoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
        
        $sent = 0;
        $skipped = 0;
        $failed = 0;
        $rateLimited = 0;

        $users = User::whereNull('archived_at');

        if ($type === 'phone') {
            $users->whereNull('phone_verified_at')->whereNotNull('phone')->where('phone', '<>', '');
        } elseif ($type === 'email') {
            $users->whereNull('email_verified_at')->whereNotNull('email')->where('email', '<>', '');
        } else {
            // both: users missing either verification
            $users->where(function($q) {
                $q->whereNull('phone_verified_at')
                  ->orWhereNull('email_verified_at');
            });
        }

        foreach ($users->get() as $user) {
            // Generate token for this user
            $token = Str::random(64);
            $user->verification_token = $token;
            $user->verification_token_expires_at = now()->addDays(7);
            $user->save();

            $verifyUrl = url('verify-profile/' . $token);
            $userSent = false;

            // Send via SMS if phone unverified and user has phone
            if (($type === 'phone' || $type === 'both') && !$user->phone_verified_at && $user->phone) {
                // Rate limit check
                $recentSms = DB::table('sms')
                    ->where('category', 'verification')
                    ->where('sent', '>=', Carbon::now()->subSeconds(60))
                    ->join('sms_recipients', 'sms_recipients.sms_id', '=', 'sms.id')
                    ->where('sms_recipients.recipients', $user->id)
                    ->exists();
                
                if ($recentSms) {
                    $rateLimited++;
                } else {
                    $number = $user->phone;
                    if (substr($number, 0, 1) === '0') {
                        $number = '254' . substr($number, 1);
                    }
                    $smsMessage = "Dear {$user->firstname}, please verify your profile for {$church_name}: {$verifyUrl}";
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => env('SMS_URL'), CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_TIMEOUT => 15, CURLOPT_CUSTOMREQUEST => 'GET',
                        CURLOPT_POSTFIELDS => 'apikey=' . env('SMS_API_KEY') . '&partnerID=' . env('SMS_PARTNER_ID') . '&message=' . urlencode($smsMessage) . '&shortcode=' . env('SMS_SHORT_CODE') . '&mobile=' . $number,
                        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
                    ]);
                    curl_exec($curl);
                    curl_close($curl);
                    $mid = DB::table('sms')->insertGetId(['people_id' => 0, 'message' => $smsMessage, 'category' => 'verification', 'sent' => Carbon::now()]);
                    DB::table('sms_recipients')->insert(['recipients' => $user->id, 'sms_id' => $mid, 'sent' => Carbon::now()]);
                    $userSent = true;
                }
            }

            // Send via email if email unverified and user has email
            if (($type === 'email' || $type === 'both') && !$user->email_verified_at && $user->email) {
                $message = "Dear {$user->firstname},<br><br>Please verify your profile for {$church_name}:<br><a href='{$verifyUrl}'>{$verifyUrl}</a><br><br>This link expires in 7 days.<br><br>Thank you,<br>{$church_name}";
                $data = ['name' => $user->firstname . ' ' . $user->lastname, 'mes' => $message];
                try {
                    \Mail::send('dashboard.communication.mail', $data, function ($mail) use ($user, $church_name) {
                        $mail->to($user->email, $user->firstname . ' ' . $user->lastname)->subject('Profile Verification Request');
                    });
                    $userSent = true;
                } catch (\Exception $e) {
                    \Log::error('Bulk verify email failed for user ' . $user->id . ': ' . $e->getMessage());
                }
            }

            if ($userSent) {
                $sent++;
            } else {
                $skipped++;
            }

            // Small delay to avoid overloading SMS API
            usleep(100000); // 100ms between sends
        }

        return response()->json([
            'success' => "Bulk verification complete. Sent: {$sent}, Skipped/Rate-limited: {$skipped}, Rate-limited: {$rateLimited}.",
            'sent' => $sent,
            'skipped' => $skipped,
            'rate_limited' => $rateLimited,
        ]);
    }

    public function archiveUser(Request $request)
    {
        $this->middleware(['permission:Add Users']);
        $user = User::findOrFail($request->id);
        if ($user->id == auth()->user()->id) {
            return response()->json(['error' => 'You cannot archive yourself'], 400);
        }
        $user->archived_at = now();
        $user->save();
        return response()->json(['success' => $user->firstname . ' ' . $user->lastname . ' has been archived.']);
    }

    public function unarchiveUser(Request $request)
    {
        $this->middleware(['permission:Add Users']);
        $user = User::findOrFail($request->id);
        $user->archived_at = null;
        $user->save();
        return response()->json(['success' => $user->firstname . ' ' . $user->lastname . ' has been unarchived.']);
    }

    public function deleteUser(Request $request)
    {
        $this->middleware(['permission:Delete Users']);
        $user = User::findOrFail($request->id);
        if (!$user->archived_at) {
            return response()->json(['error' => 'User must be archived before permanent deletion.'], 400);
        }
        if ($user->id == auth()->user()->id) {
            return response()->json(['error' => 'You cannot delete yourself.'], 400);
        }
        // Delete related records
        DB::table('contacts')->where('user_id', $user->id)->delete();
        DB::table('scontacts')->where('user_id', $user->id)->delete();
        DB::table('church')->where('user_id', $user->id)->delete();
        DB::table('families')->where('user_id', $user->id)->delete();
        DB::table('professions')->where('user_id', $user->id)->delete();
        DB::table('education')->where('user_id', $user->id)->delete();
        DB::table('tag_user')->where('user_id', $user->id)->delete();
        $user->delete();
        return response()->json(['success' => 'User permanently deleted.']);
    }

    public function importUsers(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new \App\Imports\UsersImport();
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('import_file'));

        $imported = $import->getImported();
        $skipped = $import->getSkipped();
        $errors = $import->getErrors();

        return back()->with('success', "Import complete: {$imported} members imported, {$skipped} duplicates skipped, {$errors} errors.");
    }

    public function duplicates()
    {
        return view('dashboard.users.duplicates');
    }

    public function scanDuplicates()
    {
        $users = User::whereNotNull('phone')
            ->where('phone', '<>', '')
            ->whereNull('archived_at')
            ->select('id', 'firstname', 'lastname', 'phone', 'email')
            ->get();

        $invalidPhones = [];
        $duplicates = [];

        // Flag invalid phone numbers (> 12 digits)
        foreach ($users as $user) {
            $digits = preg_replace('/[^0-9]/', '', $user->phone);
            if (strlen($digits) > 12) {
                $invalidPhones[] = [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'length' => strlen($digits),
                ];
            }
        }

        // Compare phone pairs using longest common substring ratio
        $count = $users->count();
        for ($i = 0; $i < $count; $i++) {
            $phoneA = preg_replace('/[^0-9]/', '', $users[$i]->phone);
            if (strlen($phoneA) < 6) continue;

            for ($j = $i + 1; $j < $count; $j++) {
                $phoneB = preg_replace('/[^0-9]/', '', $users[$j]->phone);
                if (strlen($phoneB) < 6) continue;

                // Quick check: must share at least 6 sequential digits
                $hasCommon = false;
                for ($k = 0; $k <= strlen($phoneA) - 6; $k++) {
                    if (strpos($phoneB, substr($phoneA, $k, 6)) !== false) {
                        $hasCommon = true;
                        break;
                    }
                }
                if (!$hasCommon) continue;

                // Calculate LCS ratio
                $lcsLen = $this->longestCommonSubstring($phoneA, $phoneB);
                $maxLen = max(strlen($phoneA), strlen($phoneB));
                $ratio = $maxLen > 0 ? $lcsLen / $maxLen : 0;

                if ($ratio >= 0.5) {
                    $duplicates[] = [
                        'user_a' => [
                            'id' => $users[$i]->id,
                            'firstname' => $users[$i]->firstname,
                            'lastname' => $users[$i]->lastname,
                            'phone' => $users[$i]->phone,
                            'email' => $users[$i]->email,
                        ],
                        'user_b' => [
                            'id' => $users[$j]->id,
                            'firstname' => $users[$j]->firstname,
                            'lastname' => $users[$j]->lastname,
                            'phone' => $users[$j]->phone,
                            'email' => $users[$j]->email,
                        ],
                        'similarity' => round($ratio * 100),
                    ];
                }
            }
        }

        // Sort by similarity descending
        usort($duplicates, fn($a, $b) => $b['similarity'] - $a['similarity']);

        return response()->json([
            'duplicates' => $duplicates,
            'invalid_phones' => $invalidPhones,
        ]);
    }

    public function mergeUsers(Request $request)
    {
        $request->validate([
            'keep_id' => 'required|integer|min:1',
            'merge_id' => 'required|integer|min:1',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($request->keep_id == $request->merge_id) {
            return response()->json(['error' => 'Cannot merge a user with themselves.'], 400);
        }

        $keepUser = User::findOrFail($request->keep_id);
        $mergeUser = User::findOrFail($request->merge_id);

        if ($mergeUser->id == auth()->user()->id) {
            return response()->json(['error' => 'You cannot merge yourself into another user.'], 400);
        }

        // Apply name/phone overrides to keep user with proper case
        if ($request->filled('firstname')) {
            $keepUser->firstname = ucwords(mb_strtolower(trim($request->firstname)));
        }
        if ($request->filled('lastname')) {
            $keepUser->lastname = ucwords(mb_strtolower(trim($request->lastname)));
        }
        if ($request->filled('phone')) {
            $mergePhoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            if (strlen($phone) <= 10) {
                $phone = $mergePhoneCode . ltrim($phone, '0');
            }
            $keepUser->phone = $phone;
        }
        $keepUser->save();

        try {
            // Tables with single user_id — transfer only if keep user doesn't already have a record
            $singleRecordTables = ['contacts', 'scontacts', 'church', 'families', 'professions', 'education', 'profiles'];
            foreach ($singleRecordTables as $table) {
                try {
                    $keepHas = DB::table($table)->where('user_id', $keepUser->id)->exists();
                    if (!$keepHas) {
                        DB::table($table)->where('user_id', $mergeUser->id)->update(['user_id' => $keepUser->id]);
                    } else {
                        DB::table($table)->where('user_id', $mergeUser->id)->delete();
                    }
                } catch (\Exception $e) {
                    // Table may not exist, skip
                }
            }

            // Tables with multiple records per user — transfer all to keep user
            $multiRecordTables = ['funds', 'pledges', 'participants', 'registration', 'purchases', 'prayers', 'testimonials'];
            foreach ($multiRecordTables as $table) {
                try {
                    DB::table($table)->where('user_id', $mergeUser->id)->update(['user_id' => $keepUser->id]);
                } catch (\Exception $e) {}
            }

            // Tag associations — transfer, skip duplicates
            try {
                $keepTags = DB::table('tag_user')->where('user_id', $keepUser->id)->pluck('tag_id')->toArray();
                DB::table('tag_user')->where('user_id', $mergeUser->id)
                    ->whereNotIn('tag_id', $keepTags)
                    ->update(['user_id' => $keepUser->id]);
                DB::table('tag_user')->where('user_id', $mergeUser->id)->delete();
            } catch (\Exception $e) {}

            // Emergency contacts
            try { DB::table('emergency_contact')->where('user_id', $mergeUser->id)->update(['user_id' => $keepUser->id]); } catch (\Exception $e) {}
            try { DB::table('emergency_contact')->where('user_id2', $mergeUser->id)->update(['user_id2' => $keepUser->id]); } catch (\Exception $e) {}

            // SMS and email recipient logs
            try { DB::table('sms_recipients')->where('recipients', $mergeUser->id)->update(['recipients' => $keepUser->id]); } catch (\Exception $e) {}
            try { DB::table('email_recipients')->where('user_id', $mergeUser->id)->update(['user_id' => $keepUser->id]); } catch (\Exception $e) {}

            // People members, schedules, articles, notices, sermons, emails, assets, communities, pastors
            $otherTables = ['people_members', 'schedules', 'articles', 'notices', 'sermons', 'emails', 'assets', 'communities', 'pastors'];
            foreach ($otherTables as $table) {
                try { DB::table($table)->where('user_id', $mergeUser->id)->update(['user_id' => $keepUser->id]); } catch (\Exception $e) {}
            }

            // Archive the merged user — null out phone/email to avoid unique constraint issues
            $mergeUser->archived_at = now();
            $mergeUser->phone = null;
            $mergeUser->email = null;
            $mergeUser->save();

            return response()->json([
                'success' => $mergeUser->firstname . ' ' . $mergeUser->lastname . ' has been merged into ' . $keepUser->firstname . ' ' . $keepUser->lastname . '. The duplicate user has been archived.',
                'redirect' => url('dashboard/users/view/' . $keepUser->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Merge failed: ' . $e->getMessage()], 500);
        }
    }

    public function quickEditUser(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'required|string',
        ]);

        $user = User::findOrFail($request->id);

        $qePhoneCode = optional(\DB::table('settings')->first())->phone_code ?? '254';
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (strlen($phone) <= 10) {
            $phone = $qePhoneCode . ltrim($phone, '0');
        }

        // Check phone conflict
        $conflict = User::where('phone', $phone)->where('id', '!=', $user->id)->first();
        if ($conflict) {
            return response()->json([
                'error' => 'Phone ' . $phone . ' already belongs to ' . $conflict->firstname . ' ' . $conflict->lastname . ' (ID: ' . $conflict->id . ').',
                'conflict' => [
                    'id' => $conflict->id,
                    'firstname' => $conflict->firstname,
                    'lastname' => $conflict->lastname,
                    'phone' => $conflict->phone,
                    'email' => $conflict->email,
                ],
                'current' => [
                    'id' => $user->id,
                    'firstname' => ucwords(mb_strtolower(trim($request->firstname))),
                    'lastname' => ucwords(mb_strtolower(trim($request->lastname))),
                    'phone' => $phone,
                    'email' => $user->email,
                ],
            ], 409);
        }

        $user->firstname = ucwords(mb_strtolower(trim($request->firstname)));
        $user->lastname = ucwords(mb_strtolower(trim($request->lastname)));
        $user->phone = $phone;
        $user->save();

        return response()->json([
            'success' => 'Updated successfully.',
            'user' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'phone' => $user->phone,
            ]
        ]);
    }

    private function longestCommonSubstring(string $a, string $b): int
    {
        $lenA = strlen($a);
        $lenB = strlen($b);
        $maxLen = 0;
        $prev = array_fill(0, $lenB + 1, 0);

        for ($i = 1; $i <= $lenA; $i++) {
            $curr = array_fill(0, $lenB + 1, 0);
            for ($j = 1; $j <= $lenB; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $curr[$j] = $prev[$j - 1] + 1;
                    if ($curr[$j] > $maxLen) {
                        $maxLen = $curr[$j];
                    }
                }
            }
            $prev = $curr;
        }

        return $maxLen;
    }
}
