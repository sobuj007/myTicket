<?php

namespace App\Http\Controllers;

use App\Http\Requests\DevicesSecurityRequest;
use App\Http\Resources\ErrorResource;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\DeviceSecurityService;
use Atldays\Agent\Facades\Agent;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DeviceSecurityController extends Controller
{
    public function registerDevices(DevicesSecurityRequest $request)
    {

        try {

            $user = User::where('email', $request->email)->first();
            $checkDeviceLimit = UserDevice::where('user_id', $user->id)->where('is_active', true)->count();
            if ($checkDeviceLimit > 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum 3 active devices are allowed.',
                    'code' => 'MAX_DEVICE_LIMIT',
                    'active_devices' => $checkDeviceLimit,
                    'maximum_devices' => 3,
                ], 422);
            }

            $device = $this->myfunc($request, $user);

            if ($device) {
                return response()->json([
                    'success' => true,
                    'message' => "Device info store",
                    'payload' => $device

                ]);
            }
        } catch (\Throwable $th) {
            return response()->json(new ErrorResource($th));
        }
    }



    public function myfunc(Request $request, $user): mixed
    {
        $agent = $request->agent();

        $securityFingerprint = hash(
            'sha256',
            implode('|', [
                $user->id,
                $request->ip(),
                $agent->userAgent(),
            ])
        );
        $device = $agent->device();
        $os = $agent->os();
        $browser = $agent->browser();
        $now = now();

        $deviceId =
            $request->header('X-Device-ID')
            ?? $request->input('device_id')
            ?? $request->fingerprint();

        $ownhash = $securityFingerprint;
        //  get exesting devices of user

        $existingDevice = UserDevice::where('user_id', $user->id)->where('device_id', $deviceId)->where('ownhash', $ownhash)->first();
        //  checking devices max 3 ..............
        $activeDevicesCount = UserDevice::where('user_id', $user->id)->where('is_active', true)->count();
        if ($existingDevice && $activeDevicesCount > 3) {

            return response()->json([
                'success' => false,
                'message' => 'Maximum 3 active devices are allowed.',
                'code' => 'MAX_DEVICE_LIMIT',
                'active_devices' => $activeDevicesCount,
                'maximum_devices' => 3,
            ], 422);
        }

        //  checking devices ..............
        if ($existingDevice && !$existingDevice->is_active) {
            $existingDevice->update([
                'is_active' => true,
                'is_blocked' => false,
                'remove_at' => null,
                'last_seen' => now(),
                'last_login' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device activated successfully.',
                'payload' => $existingDevice->fresh(),
            ]);
        }
        //  checking devices max 3 ..............
        $activeDevicesCount = UserDevice::where('user_id', $user->id)->where('is_active', true)->count();
        if ($existingDevice && $activeDevicesCount > 3) {

            return response()->json([
                'success' => false,
                'message' => 'Maximum 3 active devices are allowed.',
                'code' => 'MAX_DEVICE_LIMIT',
                'active_devices' => $activeDevicesCount,
                'maximum_devices' => 3,
            ], 422);
        }




        return UserDevice::updateOrCreate([
            'user_id' => $user->id,
            'device_id' => $deviceId

        ], [
            'device_type' => $this->getDeviceType($agent),
            'device_brand' => $this->getDeviceBrand($device),
            'device_model' => $this->getDeviceModel($device),

            'os_name' => $os?->name(),
            'os_version' => $os?->version(),

            'browser_name' => $browser?->name(),
            'browser_version' => $browser?->version(),

            'platform' => $request->input('platform'),
            'app_name' => $request->input('app_name'),
            'app_version' => $request->input('app_version'),
            'build_number' => $request->input('build_number'),

            'user_agent' => $agent->userAgent(),
            'user_agent_hash' => $agent->hash(),

            'ip_address' => $request->ip(),
            'ownhash' => $ownhash,

            'first_seen_at' => $now,
            'last_seen_at' => $now,
            'last_login_at' => $now,

        ]);
    }

    private function getDeviceType($agent): string
    {
        try {

            if (method_exists($agent, 'deviceType')) {
                return $agent->deviceType();
            }

            if (method_exists($agent, 'isMobile') && $agent->isMobile()) {
                return 'mobile';
            }

            if (method_exists($agent, 'isTablet') && $agent->isTablet()) {
                return 'tablet';
            }

            if (method_exists($agent, 'isDesktop') && $agent->isDesktop()) {
                return 'desktop';
            }

            if (method_exists($agent, 'isBot') && $agent->isBot()) {
                return 'bot';
            }
        } catch (\Throwable $e) {
            // Ignore agent detection errors.
        }

        return 'other';
    }

    private function getDeviceBrand($device): ?string
    {
        if (!$device) {
            return null;
        }
        return method_exists($device, 'brand') ? $device->brand() : null;
    }
    private function getDeviceModel($device): ?string
    {
        if (!$device) {
            return null;
        }
        return method_exists($device, 'model') ? $device->model : null;
    }




    // public function removeDevice($deviceId)
    // {
    //     try {
    //         $user = Auth::user();
    //         $device = UserDevice::where('id', $deviceId)->where('user_id', $user->id)->first();
    //         if (!$device) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Device not found"
    //             ], 404);
    //         }
    //         if (!$device->is_active) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Device is already inactive'
    //             ], 422);
    //         }
    //         $device->update([
    //             'is_active' => false,
    //             'is_trusted' => false,
    //             'remove_at' => now()
    //         ]);
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Device removed successfully',
    //             'device_id' => $device->id,
    //         ]);
    //     } catch (Throwable $th) {
    //         return response()->json(new ErrorResource($th));
    //     }
    // }

    public function getDevices(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User not found !"
            ]);
        }

        try {
            $devices = UserDevice::where('user_id', $user->id)->orderByDesc('last_seen_at')->get();
            if ($devices) {
                return response()->json([
                    'success' => true,
                    'payload' => $devices
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => "User Device is not register"
            ]);
        } catch (\Throwable $th) {
            return response()->json(new ErrorResource($th));
        }
    }
    public function getTrustedDevice(Request $request, int $id)
    {
        $user = $request->user();
        $device = UserDevice::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        if ($device->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Blocked device cannot be trusted.',
            ], 422);
        }
        $device->update([
            'is_trusted' => true,

        ]);
        return response()->json([
            'success' => true,
            'message' => 'Device trusted successfully.',
            'payload' => [
                'device_id' => $device->device_id,
                'is_trusted' => $device->is_trusted,
            ],
        ]);
    }
    public function blockedDevice(Request $request, int $id)
    {
        $user = $request->user();

        $device = UserDevice::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        if ($device->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => "Device already Blocked",
            ]);
        }
        $device->update([
            'is_blocked' => true,
            'is_trusted' => false
        ]);
        return response()->json([
            'success' => true,
            'message' => "device succesfuly blocked",
            'payload' => [
                'device_id' => $device->device_id,
                'is_blocked' => $device->is_blocked

            ]
        ]);
    }


    public function removeDevice(Request $request, int $id)
    {
        $user = $request->user();

        $device = UserDevice::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device removed successfully.',
        ]);
    }
}
