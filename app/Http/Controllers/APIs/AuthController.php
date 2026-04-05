<?php

namespace App\Http\Controllers\APIs;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['login', 'register']]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^\d{9,15}$/', 'unique:users'],
            'referrer' => ['required', 'string', 'exists:users,phone,status,1'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:12', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'terms_and_conditions' => ['required', 'integer', 'min:1', 'max:1'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $role = Role::where('name', 'User')->first();
        if ($role == null) {
            $role = Role::create(['name' => 'User']);
        }
        $user = new User;
        $user->firstname = $request->firstname;
        $user->surname = $request->surname ?? '';
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->referrer = $request->referrer;
        $user->phone = $request->phone;
        $user->status = 1;
        if ($user->save()) {
            $user->assignRole($role);
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Unable to login'
                ], 401);
            }
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;
            /*
            if ($request->firebase_token != "" && $request->device_id != "") {
                $firebaseToken = new FirebaseToken;
                $myToken = FirebaseToken::where("device_id", $request->device_id)->where('user_id', auth()->user()->id)->first();
                if ($myToken != null) {
                    $firebaseToken = $myToken;
                }
                $firebaseToken->device_id = $request->device_id;
                $firebaseToken->user_id = auth()->user()->id;
                $firebaseToken->firebase_token = $request->firebase_token;
                $firebaseToken->save();
            }*/
            return $this->respondWithToken($token);
        } else {
            return response()->json(['error' => 'Unable to create a new user'], 401);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }
        $login = request()->input('email');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        $credentials = array_merge(request([$fieldType, 'password']), ['status' => 1]);
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid username/password'], 401);
        }
        if (auth()->user()->approval_status !== null && auth()->user()->approval_status != 1) {
            Auth::logout();
            return response()->json(['error' => 'Your account is pending approval'], 403);
        }
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->plainTextToken;
        /*if ($request->firebase_token != "" && $request->device_id != "") {
            $firebaseToken = new FirebaseToken;
            $myToken = FirebaseToken::where("device_id", $request->device_id)->where('user_id', auth()->user()->id)->first();
            if ($myToken != null) {
                $firebaseToken = $myToken;
            }
            $firebaseToken->device_id = $request->device_id;
            $firebaseToken->user_id = auth()->user()->id;
            $firebaseToken->firebase_token = $request->firebase_token;
            $firebaseToken->save();
        }*/
        return $this->respondWithToken($token);
    }

    public function user()
    {
        return response()->json([
            'user' => User::with(['roles'])->where('id', auth()->user()->id)->first(),
            'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        //auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'user' => User::where('id', auth()->user()->id)->with(['roles'])->first(),
            'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
            'access_token' => $token,
            'token_type' => 'bearer',
        ]);
    }
}
