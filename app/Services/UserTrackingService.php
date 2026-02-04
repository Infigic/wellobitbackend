<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserTracking;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;

class UserTrackingService
{
    public function __construct(
        protected UserTrackingRepositoryInterface $trackingRepository,
        protected DeviceRepositoryInterface $deviceRepository
    ) {}
    
    /**
     * Handle app installed event.
     * EVENT 1: app_installed
     */
    public function handleAppInstalled(array $data)
    {
        $device = $this->deviceRepository->findByAnonymousId($data['anonymous_id']);
        if (!$device) {
            throw new \Exception('Device not found for anonymous_id: ' . $data['anonymous_id']);
        }

        return $this->trackingRepository->create([
            'device_id' => $device->id,
            'installed_at' => $data['installed_at'] ?? null,
        ]);
    }

    /**
     * Handle user registration event. 
     * EVENT 2: user_registered
     */
    public function handleRegisteredUser(array $data)
    {
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            throw new \Exception('User not found with email: ' . $data['email']);
        }

        $device = $this->deviceRepository->findByAnonymousId($data['anonymous_id']);
        if (!$device) {
            throw new \Exception('Device not found with anonymous_id: ' . $data['anonymous_id']);
        }

        $existingTracking = UserTracking::where('device_id', $device->id)->first();
        if ($existingTracking && $existingTracking->user_id !== null) {
            throw new \Exception('This anonymous_id is already registered with another user.');
        }

        return $this->trackingRepository->updateRegister($device->id, [
            'user_id' => $user->id,
            'email' => $data['email'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'consent_email' => $data['consent_email'] ?? null,
            'signup_method' => $data['signup_method'] ?? null,
            'signup_source' => $data['signup_source'] ?? null,
        ]);
    }

    /**
     * Handle apple watch connected event.
     * EVENT 5A: apple_watch_connected
     */
    public function handleAppleWatchConnected(array $data)
    {
        $userTracking = UserTracking::where('user_id', $data['user_id'])->first();

        if ($userTracking->has_apple_watch == $data['has_apple_watch']) {
            throw new \Exception('No changes detected for apple watch connection status.');
        }

        return $this->trackingRepository->updateAppleWatchConnected($data['user_id'], [
            'has_apple_watch' => $data['has_apple_watch'] ?? null,
            'apple_health_connected' => $data['apple_health_connected'] ?? null,
        ]);
    }

    /**
     * Handle health connected event.
     * EVENT 5B: health_connected
     */
    public function handleHealthConnected(array $data)
    {
        $userTracking = UserTracking::where('user_id', $data['user_id'])->first();
        if ($userTracking->apple_health_connected == $data['apple_health_connected']) {
            throw new \Exception('No changes detected for health connection status.');
        }
        return $this->trackingRepository->updateAppleWatchConnected($data['user_id'], [
            'apple_health_connected' => $data['apple_health_connected'] ?? null,
        ]);
    }
}
