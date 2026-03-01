<?php

namespace App\Http\Controllers\Dashboard\Spiritual;

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Http\Request;
use App\Models\Discipleship\Track;
use App\Models\Discipleship\Step;
use App\Models\Discipleship\Enrollment;
use App\Models\Discipleship\Progress;
use App\Models\Discipleship\Mentorship;
use App\Models\Discipleship\MentorshipSession;
use App\Models\Discipleship\Journal;
use Illuminate\Support\Facades\Auth;

class DiscipleshipController extends DashboardController
{
    public function __construct() {
        parent::__construct();
        $this->middleware(['permission:View Spiritual']);
    }
    public function index()
    {
        // My Enrollments
        $myEnrollments = Enrollment::where('user_id', Auth::id())->with('track')->get();
        
        // My Mentorships (as mentor or mentee)
        $asMentor = Mentorship::where('mentor_id', Auth::id())->with('mentee')->get();
        $asMentee = Mentorship::where('mentee_id', Auth::id())->with('mentor')->get();

        // Admin Stats (for those with permission)
        $adminStats = [];
        if (Auth::user()->can('Manage Discipleship')) {
            $adminStats = [
                'total_tracks' => Track::count(),
                'active_enrollments' => Enrollment::whereNull('completed_at')->count(),
                'total_mentorships' => Mentorship::whereNull('ended_at')->count(),
                'latest_enrollments' => Enrollment::with(['user', 'track'])->orderBy('created_at', 'desc')->take(5)->get()
            ];
        }

        return view('dashboard.spiritual.discipleship.index', compact('myEnrollments', 'asMentor', 'asMentee', 'adminStats'));
    }

    // --- TRACKS & LEARNING ---
    public function tracks()
    {
        $tracks = Track::where('is_public', true)->orWhere('created_by', Auth::id())->get();
        return view('dashboard.spiritual.discipleship.tracks.index', compact('tracks'));
    }

    public function showTrack($id)
    {
        $track = Track::with('steps')->findOrFail($id);
        $enrollment = Enrollment::where('user_id', Auth::id())->where('track_id', $id)->first();
        
        // Admin: Get all enrollments
        $enrolledUsers = [];
        if (Auth::user()->can('Manage Discipleship')) {
            $enrolledUsers = Enrollment::where('track_id', $id)->with(['user', 'progress'])->get();
        }
        
        return view('dashboard.spiritual.discipleship.tracks.show', compact('track', 'enrollment', 'enrolledUsers'));
    }

    public function enroll($id)
    {
        return $this->processEnrollment(Auth::id(), $id);
    }
    
    public function assignTrack(Request $request) {
        if (!Auth::user()->can('Manage Discipleship')) abort(403);
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'track_id' => 'required|exists:discipleship_tracks,id'
        ]);
        
        return $this->processEnrollment($request->user_id, $request->track_id, true);
    }

    private function processEnrollment($userId, $trackId, $isAdminAction = false) 
    {
        $track = Track::findOrFail($trackId);
        
        // Check if already enrolled
        $exists = Enrollment::where('user_id', $userId)->where('track_id', $trackId)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'User is already enrolled in this track.');
        }

        Enrollment::create([
            'user_id' => $userId,
            'track_id' => $trackId,
            'started_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Enrolled successfully!');
    }

    public function completeStep(Request $request, $id)
    {
        $progress = Progress::firstOrCreate([
            'enrollment_id' => $request->enrollment_id,
            'step_id' => $id
        ]);
        
        // Check if all steps completed
        $enrollment = Enrollment::find($request->enrollment_id);
        $totalSteps = $enrollment->track->steps->count();
        $completedSteps = $enrollment->progress->count();
        
        if ($totalSteps == $completedSteps) {
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
            return redirect()->back()->with('success', 'Step completed! Track finished! 🎉');
        }

        return redirect()->back()->with('success', 'Step completed!');
    }
    
    // --- ADMIN / CREATOR ---
    public function createTrack(Request $request)
    {
        $request->validate(['title' => 'required']);
        $track = Track::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'is_public' => $request->has('is_public')
        ]);
        return redirect()->back()->with('success', 'Track created successfully.');
    }

    public function addStep(Request $request, $id)
    {
        $track = Track::findOrFail($id);
        Step::create([
            'track_id' => $track->id,
            'title' => $request->title,
            'description' => $request->description,
            'content_type' => $request->content_type,
            'content_url' => $request->content_url,
            'order' => $track->steps->count() + 1
        ]);
         return redirect()->back()->with('success', 'Step added.');
    }

    // --- MENTORSHIP ---
    public function mentorship()
    {
        if (Auth::user()->can('Manage Discipleship')) {
            $mentorships = Mentorship::with(['mentor', 'mentee', 'sessions'])->get();
        } else {
            $mentorships = Mentorship::where('mentor_id', Auth::id())
                ->orWhere('mentee_id', Auth::id())
                ->with(['mentor', 'mentee', 'sessions'])
                ->get();
        }
        
        return view('dashboard.spiritual.discipleship.mentorship.index', compact('mentorships'));
    }
    
    public function matchMentor(Request $request)
    {
        if (!Auth::user()->can('Manage Discipleship')) abort(403);

        $request->validate([
            'mentor_id' => 'required|exists:users,id',
            'mentee_id' => 'required|exists:users,id|different:mentor_id'
        ]);
        
        $exists = Mentorship::where('mentor_id', $request->mentor_id)
            ->where('mentee_id', $request->mentee_id)
            ->whereNull('ended_at')
            ->exists();
            
        if($exists) return redirect()->back()->with('error', 'Mentorship already active.');
        
        Mentorship::create([
            'mentor_id' => $request->mentor_id,
            'mentee_id' => $request->mentee_id,
            'started_at' => now()
        ]);
        
        return redirect()->back()->with('success', 'Mentorship matched successfully.');
    }

    public function logSession(Request $request)
    {
        MentorshipSession::create([
            'mentorship_id' => $request->mentorship_id,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
            'session_date' => now()
        ]);
        return redirect()->back()->with('success', 'Session logged.');
    }

    // --- JOURNAL ---
    public function journal()
    {
        $entries = Journal::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('dashboard.spiritual.discipleship.journal.index', compact('entries'));
    }

    public function saveJournal(Request $request)
    {
        Journal::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'entry' => $request->entry,
            'is_shared_with_mentor' => $request->has('share')
        ]);
        return redirect()->back()->with('success', 'Journal entry saved.');
    }
}
