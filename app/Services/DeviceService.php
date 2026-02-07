<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Repositories\Contracts\DeviceRepositoryInterface;

class DeviceService
{

    public function __construct(protected DeviceRepositoryInterface $deviceRepository) {}

    /**
     * Handle user app installed event.
     * EVENT 1: app_installed
     */
    public function handleAppInstalled(array $data)
    {
        return $this->deviceRepository->create([
            'anonymous_id' => $data['anonymous_id'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'locale' => $data['locale'] ?? null,
            'app_version' => $data['app_version'] ?? null,
            'os_version' => $data['os_version'] ?? null,
        ]);
    }
    /**
     * Handle user registration event. 
     * EVENT 2: user_registered
     */
    public function handleRegisteredUser(array $data)
    {
        $user = User::where('email', $data['email'])->first();
        $device = $this->deviceRepository->findByAnonymousId($data['anonymous_id']);
        if (!$user) {
            throw new \Exception('User not found with email: ' . $data['email']);
        }

        if (!$device) {
            throw new \Exception('Device not found with anonymous_id: ' . $data['anonymous_id']);
        }

        return $this->deviceRepository->updateRegister($device->id, [
            'user_id' => $user->id,
        ]);
    }
    /**
     * Handle apple watch connected event.
     * EVENT 5A: apple_watch_connected
     */
    public function handleAppleWatchConnected(array $data)
    {
        $userDevice = UserDevice::where('user_id', $data['user_id'])->first();

        if (!$userDevice) {
            throw new \Exception('User device not found for user_id: ' . $data['user_id']);
        }

        // Collect only changed fields
        $updates = [];

        if (isset($data['apple_watch_model']) && $userDevice->apple_watch_model != $data['apple_watch_model']) {
            $updates['apple_watch_model'] = $data['apple_watch_model'];
        }

        if (isset($data['apple_watch_os_version']) && $userDevice->apple_watch_os_version != $data['apple_watch_os_version']) {
            $updates['apple_watch_os_version'] = $data['apple_watch_os_version'];
        }

        if (empty($updates)) {
            throw new \Exception('No changes detected for apple watch connection.');
        }

        return $this->deviceRepository->updateAppleWatchConnected($data['user_id'], $updates);
    }

    public function getDeviceIdByAnonymousId(string $anonymous_id)
    {
        $device = $this->deviceRepository->findByAnonymousId($anonymous_id);
        if (!$device) {
            throw new \Exception('Device not found with anonymous_id: ' . $anonymous_id);
        }
        return $device->id;
    }
}
