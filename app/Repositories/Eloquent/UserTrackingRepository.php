<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\UserTracking;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserTrackingRepository implements UserTrackingRepositoryInterface 
{
    public function create(array $data)
    {
        return UserTracking::create($data);
    }

    public function updateRegister(int $device_id, array $data)
    {
        $userTracking = UserTracking::where('device_id', $device_id)->first();
        $user = User::find($data['user_id']);

        if (!$userTracking) {
            throw new \Exception('UserTracking record not found with device_id: ' . $device_id);
        }

        if (!$user) {
            throw new \Exception('User not found with id: ' . $data['user_id']);
        }
        
        $userTracking->update($data);

        return $userTracking;
    }

    public function updateAppleWatchConnected(int $user_id, array $data)
    {
        $userTracking = UserTracking::where('user_id', $user_id)->first();
        
        if (!$userTracking) {
            throw new \Exception('UserTracking record not found with user_id: ' . $user_id);
        }

        $userTracking->update($data);

        return $userTracking;
    }

    public function updateHealthConnected(int $user_id, array $data)
    {
        $userTracking = UserTracking::where('user_id', $user_id)->first();
        
        if (!$userTracking) {
            throw new \Exception('UserTracking record not found with user_id: ' . $user_id);
        }

        $userTracking->update($data);

        return $userTracking;
    }

    // EVENT 4
    public function findByUserId(int $userId): ?UserTracking
    {
        return UserTracking::where('user_id', $userId)->first();
    }

    public function setPrimaryReason(UserTracking $tracking, string $reason): bool
    {
        return $tracking->update([
            'primary_reason_to_use' => $reason
        ]);
    }

    public function updatePrimaryReason(int $userId, string $reason): bool
    {
        $tracking = $this->findByUserId($userId);

        if (!$tracking) {
            return false;
        }

        // Do not overwrite if already set
        if ($tracking->primary_reason_to_use !== null) {
            return true;
        }

        return $tracking->update([
            'primary_reason_to_use' => $reason
        ]);
    }

    // EVENT 6
    /**
     * Set first breath session time ONCE (fire-once guarantee)
     */
    public function setFirstBreathSessionAt(
        UserTracking $tracking,
        Carbon $time
    ): bool {
        return DB::transaction(function () use ($tracking, $time) {

            // Lock row to avoid race condition
            $lockedTracking = UserTracking::where('id', $tracking->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedTracking) {
                return false;
            }

            // Fire-once: do not overwrite
            if ($lockedTracking->first_breath_session_at !== null) {
                return false;
            }

            $lockedTracking->update([
                'first_breath_session_at' => $time,
                'last_active_at' => $time,
            ]);

            return true;
        });
    }

    // EVENT 7
    /**
     * Update last active time
     */
    public function updateLastActiveAt(
        UserTracking $tracking,
        Carbon $time
    ): bool {
        return $tracking->update([
            'last_active_at' => $time
        ]);
    }
}
