<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Atldays\Agent\Data\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DeviceSecurityService
{
    // public function register(Request $request): UserDevice
    // {
    //     $agent = $request->agent();
    //     $deviceId = $request->input('device_id');
    //     $user = Auth::user();
    //     $ownhash = bcrypt($request->ip() . $user->email);
    //     $device = $agent->device();
    //     $os = $agent->os();
    //     $browser = $agent->browser();
    //     $now = now();

    //     return UserDevice::updateOrCreate([
    //         'user_id' => $user->id,
    //         'device_id' => $deviceId

    //     ], [
    //         'device_type' => $this->getDeviceType($agent),
    //         'device_brand' => $this->getDeviceBrand($device),
    //         'device_model' => $this->getDeviceModel($device),

    //         'os_name' => $os?->name(),
    //         'os_version' => $os?->version(),

    //         'browser_name' => $browser?->name(),
    //         'browser_version' => $browser?->version(),

    //         'platform' => $request->input('platform'),
    //         'app_name' => $request->input('app_name'),
    //         'app_version' => $request->input('app_version'),
    //         'build_number' => $request->input('build_number'),

    //         'user_agent' => $agent->userAgent(),
    //         'user_agent_hash' => $agent->hash(),

    //         'ip_address' => $request->ip(),
    //         'ownhash' => $ownhash,

    //         'first_seen_at' => $now,
    //         'last_seen_at' => $now,
    //         'last_login_at' => $now,

    //     ]);
    // }

    // private function getDeviceType($agent): string
    // {
    //     // if ($agent->isPhone()) {
    //     //     return 'phone';
    //     // }
    //     if ($agent->isDesktop()) {
    //         return 'desktop';
    //     }
    //     if ($agent->isTablet()) {
    //         return 'tablet';
    //     }
    //     if ($agent->isRobot()) {
    //         return 'bot';
    //     }

    //     return 'other';
    // }

    // private function getDeviceBrand($device): ?string
    // {
    //     if (!$device) {
    //         return null;
    //     }
    //     return method_exists($device, 'brand') ? $device->brand() : null;
    // }


    // private function getDeviceModel($device): ?string
    // {
    //     if (!$device) {
    //         return null;
    //     }
    //     return method_exists($device, 'model') ? $device->model : null;
    // }

    private function checkDeviceLimit(Request $request): bool
    {
        $auth = Auth::user();
        $user = User::where('email', $auth->email)->first();
        $deviceLimit = UserDevice::where('user_id', $user->id)->where('is_active', true)->count();

        return $deviceLimit > 3;
    }
    private function  checkDeviceId(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $agent = $request->agent();
        $getOwnhash = hash('sha256', implode('|', [$request->email, $request->ip, $agent->userAgent(),]));

        $yesTrustedDevice = UserDevice::where('user_id', $user->id)
            ->where('ownhash', $getOwnhash)
            ->where('is_active', true)
            ->first();
        if ($yesTrustedDevice) {
            return true;
        }
        return false;
    }
}
