<?php

namespace App\Http\Controllers\Dashboard\Websites;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\HomePage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PastorSettingsController extends DashboardController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:View Website Settings']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    /*
    public function index()
    {
        $homepage = HomePage::first();
        return view('dashboard.website.homepage', @compact('homepage'));
    }*/

    public function getMessage()
    {
        $message = \DB::table('pastorsmessage')->first();
        return view('dashboard.website.pastorsmessage', @compact('message', $message));
    }

    public function pastorsmessage()
    {
        $perm1 = \DB::table("permissions")->where("user_id", \Auth::user()->id)->first();
        $perm2 = \DB::table("permissions")->where("role", \Auth::user()->role)->first();
        if ($perm1 != null || $perm2 != null) {
            if ($perm1->websites > 0 || $perm2->websites > 0) {
                $message = \DB::table('pastorsmessage')->first();
                return view('user.permissions.pastorsmessage')->with('message', $message);
            } else {
                return redirect()->back()->with("error", "Access denied");
            }
        } else {
            return redirect()->to("/home");
        }
    }
    public function addPastorMessage(Request $request)
    {
        request()->validate([
            'title' => 'required|min:4',
            'description' => 'required|min:4',
        ]);
        if ($request->id > 0) {
            //update
            if (
                !\DB::table('pastorsmessage')->where("id", $request->id)->update([
                    "title" => $request->title,
                    "description" => $this->purify($request->description)
                ])
            ) {
                return redirect()->back()->with('error', 'Unable to update!');
            }
        } else {
            //insert
            if (!\DB::table('pastorsmessage')->insert(["title" => $request->title, "description" => $this->purify($request->description), "image" => ""])) {
                return redirect()->back()->with('error', 'Unable to save!');
            }
        }
        return redirect()->back()->with("success", "Information updated successfully!");
    }

    public function uploadimage(Request $request)
    {
        $message = \DB::table('pastorsmessage')->first();

        $result = $this->processBase64Image($request->image, public_path('website/pastors'));
        if (isset($result['error'])) {
            return response()->json(['success' => '<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> ' . e($result['error']) . '</p>']);
        }

        $image_name = $result['filename'];

        if ($message->image != "") {
            if (file_exists(public_path() . "/website/pastors/" . $message->image)) {
                unlink(public_path() . "/website/pastors/" . $message->image);
            }
        }

        if ($message != null) {
            $update = \DB::table('pastorsmessage')->where("id", $message->id)->update(["image" => $image_name]);
            if (!$update) {
                return response()->json(['success' => '<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Error Saving image</p>']);
            }
        } else {
            $save = \DB::table('pastorsmessage')->insert(["title" => "", "description" => "", "image" => $image_name]);
            if (!$save) {
                return response()->json(['success' => '<p class="text-center text-danger"><i class="fas fa-exclamation-circle"></i> Error Saving image</p>']);
            }
        }
        return response()->json(['success' => '<p class="text-center text-success"><i class="fas fa-check"></i> Successfully uploaded</p>']);
    }

    /**
     * Process a base64-encoded image string and save it to disk.
     * Validates MIME type and file contents.
     */
    private function processBase64Image(?string $base64String, string $directory): array
    {
        if (empty($base64String)) {
            return ['error' => 'No image provided'];
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            return ['error' => 'Invalid image format'];
        }

        $imageType = strtolower($matches[1]);
        $allowedTypes = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        if (!in_array($imageType, $allowedTypes, true)) {
            return ['error' => 'Invalid image type. Allowed: jpeg, png, gif, webp'];
        }

        $data = substr($base64String, strpos($base64String, ',') + 1);
        $data = base64_decode($data, true);

        if ($data === false) {
            return ['error' => 'Invalid base64 data'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $data);
        finfo_close($finfo);

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        if (!isset($mimeToExt[$mimeType])) {
            return ['error' => 'Invalid image content'];
        }

        $extension = $mimeToExt[$mimeType];
        $filename = time() . '_' . Str::random(8) . '.' . $extension;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = rtrim($directory, '/') . '/' . $filename;
        file_put_contents($path, $data);

        return ['success' => true, 'filename' => $filename];
    }
}
