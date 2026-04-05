<?php

namespace App\Http\Controllers\Dashboard\Spiritual;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\Sermon;
use Illuminate\Http\Request;

class SermonController extends DashboardController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Spiritual']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $sermons = Sermon::orderBy('id', 'desc')->paginate(15);
        return view('dashboard.spiritual.sermons')->with('sermons', $sermons);
    }
    public function sermon()
    {
        return view('dashboard.spiritual.sermon');
    }
    public function editsermon(Request $request)
    {
        $sermon = Sermon::find($request->id);
        if ($sermon == null) {
            return redirect()->to('home');
        }
        return view('dashboard.spiritual.editsermon')->with("sermon", $sermon);
    }

    public function editmysermon(Request $request)
    {
        request()->validate([
            'title' => 'required|string|min:4',
            'description' => 'required|string|min:4',
            'audio' => 'mimes:mpga,wav,mp3,ogg,mpeg',
            'video' => 'mimes:mp4',
            'banner' => 'mimes:jpg,jpeg,png| max:1024',
        ]);
        $sermon = Sermon::find($request->id);
        if ($request->id > 0 && $sermon != null) {
            //update

            $banner = "";
            $video = "";
            $audio = "";

            $minutes = intval($request->minutes) + (intval($request->hours) * 60);

            if ($request->hasFile('banner')) {
                if (file_exists(public_path() . "/sermon/" . $sermon->banner)) {
                    \File::delete(public_path() . "/sermon/" . $sermon->banner);
                }
                $extension = strtolower($request->banner->getClientOriginalExtension());
                $banner = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $extension;
                $request->banner->move(public_path('sermon'), $banner);
                Sermon::where('id', '=', $request->id)->update(array('banner' => $banner));
            }
            /*
            if($request->has('video')){
                if(file_exists(public_path()."/sermon/video/".$sermon->video)){
                    \File::delete(public_path()."/sermon/video/".$sermon->video);
                }
                $video = $request->video->getClientOriginalName();
                if(\DB::table('sermons')->where('video', $video)->count() > 0){
                    $video = time()."_".$request->video->getClientOriginalName();
                }
                $request->video->move(public_path('sermon/video'), $video);
                Sermon::where('id', '=', $request->id)->update(array('video' => $request->video->getClientOriginalName()));
            }
            if($request->has('audio')){
                if(file_exists(public_path()."/sermon/audio/".$sermon->audio)){
                    \File::delete(public_path()."/sermon/audio/".$sermon->audio);
                }
                $audio = $request->audio->getClientOriginalName();
                if(\DB::table('sermons')->where('audio', $audio)->count() > 0){
                    $audio = time()."_".$request->audio->getClientOriginalName();
                }
                $request->audio->move(public_path('sermon/audio'), $audio);
                Sermon::where('id', '=', $request->id)->update(array('audio' => $request->audio->getClientOriginalName()));
            }*/
            if ($request->has('date')) {

                $date = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
                $time = \Carbon\Carbon::parse($request->date)->format('h:i A');

                Sermon::where('id', '=', $request->id)->update(array('sermondate' => $date, "time" => $time));
            }

            $update = Sermon::where('id', '=', $request->id)->update(
                array(
                    'title' => $request->title,
                    'description' => $this->purify($request->description),
                    "youtube" => $request->youtube,
                    "audio" => $request->audio_link,
                    "duration" => $minutes
                )
            );
            if ($update) {
                return redirect()->back()->with('success', 'Your testimonial has been updated successfully');
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back()->with('error', 'Invalid request. Try again');
        }
    }

    public function addsermon(Request $request)
    {
        request()->validate([
            'title' => 'required|string|min:4',
            'description' => 'required|string|min:4',
            'date' => 'required|string|min:5',
            'audio' => 'mimes:mpga,wav,mp3,ogg,mpeg',
            'video' => 'mimes:mp4',
            'banner' => 'mimes:jpg,jpeg,png| max:1024',
        ]);

        $description = $this->purify($request->description);
        $dom = new \DomDocument();
        $dom->loadHtml($description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            // if the img source is 'data-url'
            if (preg_match('/data:image/', $src)) {

                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];

                // Generating a random filename
                $filename = uniqid() . time();
                $filepath = "/sermon/$filename.$mimetype";

                // @see http://image.intervention.io/api/
                $image = Image::make($src)
                    // resize if required
                    /* ->resize(300, 200) */
                    ->encode($mimetype, 100) 	// encode file to the specified mimetype
                    ->save(public_path($filepath));

                $new_src = asset($filepath);
                $img->removeAttribute('src');
                $img->setAttribute('src', $new_src);
            } // endif
        }// end foreach

        $sermon = new Sermon();

        $date = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
        $time = \Carbon\Carbon::parse($request->date)->format('h:i A');

        $minutes = intval($request->minutes) + (intval($request->hours) * 60);

        $banner = "";
        $video = "";
        $audio = "";


        if ($request->hasFile('banner')) {
            $extension = strtolower($request->banner->getClientOriginalExtension());
            $banner = time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $extension;
            $request->banner->move(public_path('sermon'), $banner);
        }

        /*
        if($request->has('video')){
            $video = $request->video->getClientOriginalName();
            if(\DB::table('sermons')->where('video', $video)->count() > 0){
                $video = time()."_".$request->video->getClientOriginalName();
            }
            $request->video->move(public_path('sermon/video'), $video);
        }
        if($request->has('audio')){
            $audio = $request->audio->getClientOriginalName();
            if(\DB::table('sermons')->where('audio', $audio)->count() > 0){
                $audio = time()."_".$request->audio->getClientOriginalName();
            }
            $request->audio->move(public_path('sermon/audio'), $audio);
        }*/

        $sermon->title = $request->title;
        $sermon->description = $dom->saveHTML();//$request->description;
        $sermon->banner = $banner;
        $sermon->time = $time;
        $sermon->youtube = $request->youtube;
        $sermon->video = $video;
        $sermon->audio = $request->audio_link;
        $sermon->sermondate = $date;
        $sermon->duration = $minutes;
        $sermon->user_id = \Auth::user()->id;

        if ($sermon->save()) {
            if (\Auth::user()->role == 1) {
                return redirect()->to('dashboard/spiritual/sermons')->with('success', 'Your settings has been created successfully');
            } else {
                return redirect()->to('dashboard/spiritual/sermons')->with('success', 'Your settings has been created successfully');
            }
        } else {
            return redirect()->back()->with('error', 'Unable to save. Try again');
        }
    }

    public function deletesermon(Request $request)
    {
        $sermon = Sermon::find($request->id);
        if (file_exists(public_path() . "/sermon/" . $sermon->banner)) {
            \File::delete(public_path() . "/sermon/" . $sermon->banner);
        }
        if (file_exists(public_path() . "/sermon/video/" . $sermon->video)) {
            \File::delete(public_path() . "/sermon/video/" . $sermon->video);
        }
        if (file_exists(public_path() . "/sermon/audio/" . $sermon->audio)) {
            \File::delete(public_path() . "/sermon/audio/" . $sermon->audio);
        }
        if ($sermon->delete()) {
            return redirect()->back()->with('success', 'sermon removed successfully!');
        } else {
            return redirect()->back()->with('error', 'Unable to remove sermon!');
        }
    }
}
