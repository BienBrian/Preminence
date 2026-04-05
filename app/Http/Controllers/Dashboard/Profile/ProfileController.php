<?php

namespace App\Http\Controllers\Dashboard\Profile;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ProfileController extends DashboardController
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $profile_images = \DB::table('profiles')->get();
        foreach($profile_images as $profile){
            $user = User::find($profile->user_id);
            if($user != null){
                $user->image = $profile->name;
            }else{
                if (file_exists(public_path('/profile_images/' . $profile->name))) {
                    unlink(public_path() . '/profile_images/' . $profile->name);
                }
                \DB::table('profiles')->where('id', $profile->id)->delete();
            }
        }
        $user = User::with(['roles'])->findOrFail(\Auth::user()->id);
        return view('dashboard.profile.profile', ['user' => $user]);
    }

    public function editProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $user = User::findOrFail(Auth::id());
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        if ($user->save()) {
            return response()->json(['success' => 'User updated successfully!']);
        } else {
            return response()->json(['error' => 'Unable to update user'], 401);
        }
    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|min:8|string|same:confirm_password',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        if (Hash::check($request->current_password, auth()->user()->password)) {
            //save
            $user = User::find(Auth::user()->id);
            $user->password = Hash::make($request->new_password);
            if ($user->save()) {
                return response()->json(['success' => 'Password update successfully!']);
            } else {
                return response()->json(['error' => 'Unable to update password!'], 401);
            }
        } else {
            return response()->json(['error' => 'Current Password is incorrect!'], 401);
        }
    }
    public function uploadProfilePicture(Request $request)
    {
        $result = $this->processBase64Image($request->image, public_path('profile_images'));
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }

        $image_name = $result['filename'];
        $user = User::findOrFail(Auth::user()->id);
        if ($user->image != null) {
            if (file_exists(public_path('/profile_images/' . $user->image))) {
                unlink(public_path() . '/profile_images/' . $user->image);
            }
        }

        $user->image = $image_name;
        $user->save();
        $profile = \DB::table('profiles')->where('user_id', $user->id)->first();
        if($profile != null){
            \DB::table('profiles')->where('id', $profile->id)->update(["name"=>$image_name]);
        }else{
            \DB::table('profiles')->insert(["name"=>$image_name, "user_id"=>$user->id]);
        }

        return response()->json(['success' => 'Image Uploaded Successfully']);
    }

    /**
     * Send email verification OTP
     */
    public function sendEmailVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = User::find(Auth::id());
        $email = $request->email;
        
        // Update email if changed
        if ($user->email !== $email) {
            $user->email = $email;
            $user->email_verified_at = null;
            $user->save();
        }

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in cache for 15 minutes
        $cacheKey = 'email_verification_' . $user->id;
        cache()->put($cacheKey, $otp, Carbon::now()->addMinutes(15));

        // Send email
        $churchName = DB::table('settings')->first()->name ?? 'Church App';
        $message = "Your {$churchName} email verification code is: <strong>{$otp}</strong>.<br>This code is valid for 15 minutes.";
        
        try {
            $data = ['name' => $user->firstname ?? 'New Member', 'mes' => $message];
            \Mail::send('dashboard.communication.mail', $data, function ($mail) use ($email, $churchName, $otp) {
                $mail->to($email)->subject("Email Verification Code — {$churchName}");
            });
            
            return response()->json(['success' => 'Verification code sent to your email.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send email. Please try again.'], 500);
        }
    }

    /**
     * Verify email OTP
     */
    public function verifyEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Please enter a valid 6-digit code.'], 422);
        }

        $user = User::find(Auth::id());
        $cacheKey = 'email_verification_' . $user->id;
        $storedOtp = cache()->get($cacheKey);

        if (!$storedOtp || $storedOtp !== $request->otp) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        // Clear the cache
        cache()->forget($cacheKey);

        return response()->json(['success' => 'Email verified successfully!']);
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
        $filename = Auth::user()->id . '_' . time() . '.' . $extension;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = rtrim($directory, '/') . '/' . $filename;
        file_put_contents($path, $data);

        return ['success' => true, 'filename' => $filename];
    }
}
