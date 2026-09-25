<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ErrorResource;
use App\Mail\Auth\ForgetMail;
use App\Mail\Auth\RegisterMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function test()
    {
        return response()->json([
            "success" => true,
            "message" => "test message true"
        ]);
    }

    public  function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
            $user->assignRole('user');
            if ($user) {
                $otpCode = random_int(100000, 999999);

                if (Mail::send(new RegisterMail($otpCode, $user))) {
                    OtpCode::create([
                        'email' => $user->email,
                        'code' => $otpCode
                    ]);
                    DB::commit();
                }

                return response()->json([
                    'sucees' => true,
                    'message' => "user registration successful !",

                ]);
            }
        } catch (\Throwable $th) {

            DB::rollBack();
            return response()->json([
                'sucees' => false,
                'message' => $th->getMessage(),
            ]);


            //throw $th;
        }
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|string|exists:users,email'
        ]);
        try {
            $user = User::where('email', $request->email)->first();
            $otpCode = random_int(100000, 999999);
            if ($user) {

                if (Mail::send(new RegisterMail($otpCode, $user))) {
                    OtpCode::updateOrCreate([
                        'email' => $user->email,

                    ], [
                        'code' => $otpCode
                    ]);
                }
                return response()->json([
                    'success' => true,
                    'message' => "OTP code Resend to your mail"
                ]);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
    public function verifyOtp(OtpRequest $request)
    {

        try {
            $validCode = OtpCode::where('email', $request->email)->where('code', $request->code)->first();
            if ($validCode->updated_at->diffInMinutes(now()) > env('VERIFICATION_CODE_EXPIRATION_MIN', 10)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code has expaired'
                ]);
            } else {
                if ($validCode) {
                    $user = User::where('email', $request->email)->first();
                    $user->update([
                        'email_verified_at' => now()
                    ]);
                    $validCode->delete();

                    return  response()->json([
                        'success' => true,
                        'message' => "Email verified successful"
                    ]);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }



    public function login(LoginRequest $request)
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalide credentials "
                ]);
            } else {
                $user = User::where('email', $request->email)->first();
                $token = $user->createToken('myticket')->plainTextToken;
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'payload' => [
                        'user' => $user,

                        'token' => $token
                    ]
                ]);
            }

            //code...
        } catch (\Throwable $th) {
            return response(new ErrorResource($th));
        }
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min;6|confirmed'
        ]);

        try {
            /***
             * @var User 
             */
            //get the loged user 
            $user = Auth::user();
            if ($user) {
                if (password_verify($request->current_password, $user->password)) {
                    $user->update([
                        'password' => bcrypt($request->new_password)
                    ]);

                    return response()->json([
                        "success" => true,
                        "message" => "Password has changed successfuly"
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => "Somthings worng ,password not match"
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(new ErrorResource($th));
        }
    }



    public function forgetPass(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:256|exists:users,email'
        ]);
        try {
            $user = User::where('email', $request->email)->first();
            $otpCode = random_int(100000, 999999);


            if ($user) {
                if (Mail::send(new ForgetMail($otpCode, $user))) {
                    OtpCode::updateOrCreate([
                        'email' => $user->email,
                        'code' => $otpCode
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => "OTP code Send to you mail!"
                    ]);
                }
            }
            return response()->json([
                'success' => false,
                'message' => "Somthing Worng,"
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(new ErrorResource($th));
        }
    }

    public function verifyForgetOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|string|max:256|exists:users,email',
            'otpCode' => 'required|string|max:6',
            'new_password' => 'required|string|min:6'
        ]);

        try {
            $validCode = OtpCode::where('email', $request->email)->where('code', $request->code)->first();
            if ($validCode->updated_at->diffInMinutes(now()) > env('VERIFICATION_CODE_EXPIRATION_MIN', 10)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code has expaired'
                ]);
            } else {
                if ($validCode) {
                    $user = User::where('email', $request->email)->first();
                    $user->update([
                        'password' => $request->new_password
                    ]);

                    return  response()->json([
                        'success' => true,
                        'message' => "Email verified successful"
                    ]);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function singelDeviceLogout(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => "You are not Authorized....."

                ]);
            }
            // $user = Auth::user();
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json([
                    'success' => true,
                    'message' => 'Log out success!',
                ]);
            }
        } catch (\Throwable $th) {

            return response()->json(new ErrorResource($th));
            //throw $th;
        }
    }
}
