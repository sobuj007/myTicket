<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\OtpRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\Auth\RegisterMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
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



    public function login(LoginRequest $request) {}
}
