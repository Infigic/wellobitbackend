<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserTracking;
use App\Repositories\Contracts\DeviceRepositoryInterface;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;
use Carbon\Carbon;

class UserTrackingService
{
    protected UserTrackingRepositoryInterface $trackingRepo;

    /**
     * Debounce window for app_active (minutes)
     */
    protected int $appActiveDebounceMinutes;

    public function __construct(
        UserTrackingRepositoryInterface $trackingRepo,
        protected DeviceRepositoryInterface $deviceRepository
    ) {
        $this->trackingRepo = $trackingRepo;

        $this->appActiveDebounceMinutes = (int) config(
            'tracking.app_active_debounce_minutes'
        );
    }

    /**
     * Handle app installed event.
     * EVENT 1: app_installed
     */
    public function handleAppInstalled(array $data)
    {
        $device = $this->deviceRepository->findByUuid($data['uuid']);
        if (!$device) {
            throw new \Exception('Device not found for uuid: ' . $data['uuid']);
        }

        return $this->trackingRepo->create([
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

        $device = $this->deviceRepository->findByUuid($data['uuid']);
        if (!$device) {
            throw new \Exception('Device not found with uuid: ' . $data['uuid']);
        }

        $existingTracking = UserTracking::where('device_id', $device->id)->first();
        if ($existingTracking && $existingTracking->user_id !== null) {
            throw new \Exception('This uuid is already registered with another user.');
        }

        return $this->trackingRepo->updateRegister($device->id, [
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

        return $this->trackingRepo->updateAppleWatchConnected($data['user_id'], [
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

        return $this->trackingRepo->updateAppleWatchConnected($data['user_id'], [
            'apple_health_connected' => $data['apple_health_connected'] ?? null,
        ]);
    }

    /**
     * Event 4: Primary Reason Selected
     */
    public function handlePrimaryReasonSelected(
        int $userId,
        ?string $reason
    ): array {

        // Skipped in UI: no-op
        if (!$reason) {
            return [
                'success' => true,
                'status'  => 'skipped',
                'message' => 'Primary reason skipped (no selection).'
            ];
        }

        // Tracking must exist (created at app_installed)
        $tracking = $this->trackingRepo->findByUserId($userId);

        if (!$tracking) {
            return [
                'success' => true,
                'status'  => 'ignored',
                'message' => 'Tracking record not found. Event ignored.'
            ];
        }

        // Save only once
        if ($tracking->primary_reason_to_use !== null) {
            return [
                'success' => true,
                'status'  => 'ignored',
                'message' => 'Primary reason already set.'
            ];
        }

        // Persist
        $this->trackingRepo->setPrimaryReason($tracking, $reason);

        return [
            'success' => true,
            'status'  => 'saved',
            'message' => 'Primary reason saved successfully.'
        ];
    }

    /**
     * Event 7: App Active
     */
    public function handleAppActive(
        int $userId,
        string $lastActiveAt
    ): array {

        $tracking = $this->trackingRepo->findByUserId($userId);

        if (!$tracking) {
            return [
                'success' => true,
                'status'  => 'ignored',
                'message' => 'Tracking record not found. Event ignored.'
            ];
        }

        $incomingTime = Carbon::parse($lastActiveAt);

        // First activity ever → always update
        if (!$tracking->last_active_at) {
            $this->trackingRepo->updateLastActiveAt(
                $tracking,
                $incomingTime
            );

            return [
                'success' => true,
                'status'  => 'updated',
                'message' => 'First app activity recorded.'
            ];
        }

        $diffMinutes = Carbon::parse($tracking->last_active_at)
            ->diffInMinutes($incomingTime);

        // Debounce: ignore if too frequent
        if ($diffMinutes < $this->appActiveDebounceMinutes) {
            return [
                'success' => true,
                'status'  => 'ignored',
                'message' => 'App active event debounced.'
            ];
        }

        // Update last_active_at
        $this->trackingRepo->updateLastActiveAt(
            $tracking,
            $incomingTime
        );

        return [
            'success' => true,
            'status'  => 'updated',
            'message' => 'App activity updated.'
        ];
    }
}
