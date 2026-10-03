<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DeviceSecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        $agent = $request->agent();
        $auth = Auth::user();

        $user = User::where('email', $auth->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'user' => $user,
                'request' => $request,
                'agent' => $agent,
                'ip' => $request->ip()
            ], 401);
        }
        $securityFingerprint = hash(
            'sha256',
            implode('|', [
                $user->email,
                $request->ip(),
                $agent->userAgent(),
            ])
        );
        return response()->json($securityFingerprint);
        $deviceId =
            $request->header('X-Device-ID')
            ?? $request->input('device_id')
            ?? $request->fingerprint();


        if (!$deviceId) {

            return response()->json([
                'success' => false,
                'message' => 'Device ID is required.',
                'code' => 'DEVICE_ID_REQUIRED',
            ], 400);
        }

        // for new device..................
        $device = UserDevice::where('user_id', $user->id)
            ->where('device_id', $deviceId)
            ->first();

        if (!$device) {

            return response()->json([
                'success' => false,
                'message' => 'This device is not registered.',
                'code' => 'NEW_DEVICE',
            ], 403);
        }

        //  blocked device .........
        if ($device->is_blocked) {

            return response()->json([
                'success' => false,
                'message' => 'This device has been blocked.',
                'code' => 'DEVICE_BLOCKED',
            ], 403);
        }


        $device->update([
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
        ]);


        /*
     
        | Continue Request..............
        
        */

        $request->attributes->set(
            'user_device',
            $device
        );







        return $next($request);
    }
}
