<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserTracking;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
        $userTracking = $this->getOrCreateUser($user_id);
        
        if (!$userTracking) {
            throw new \Exception('UserTracking record not found with user_id: ' . $user_id);
        }

        $userTracking->update($data);

        return $userTracking;
    }

    public function updateHealthConnected(int $user_id, array $data)
    {
        $userTracking = $this->getOrCreateUser($user_id);

        if (!$userTracking) {
            throw new \Exception('UserTracking record not found with user_id: ' . $user_id);
        }

        $userTracking->update($data);

        return $userTracking;
    }

    // EVENT 4
    public function getOrCreateUser(int $userId): ?UserTracking
    {

        $userTracking = UserTracking::where('user_id', $userId)->first();
        $user = User::find($userId);
        if (!$userTracking) {
            DB::transaction(function () use ($user, &$userTracking) {
                $userDevice = UserDevice::create([
                    'user_id' => $user->id,
                    'uuid' => Str::uuid()->toString(),
                    'timezone' => config('app.timezone'),
                    'locale' => app()->getLocale(),
                    // 'app_version' => '1.0.0',
                ]);

                $userTracking = UserTracking::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->name,
                    'device_id' => $userDevice->id,
                    'installed_at' => $user->created_at,
                    'primary_reason_to_use' => $user->reason
                ]);
            });

            return $userTracking;
        }

        return $userTracking;
    }

    public function setPrimaryReason(UserTracking $tracking, string $reason): bool
    {
        return $tracking->update([
            'primary_reason_to_use' => $reason
        ]);
    }

    public function updatePrimaryReason(int $userId, string $reason): bool
    {
        $tracking = $this->getOrCreateUser($userId);

        if (!$tracking) {
            return false;
        }

        if ($tracking->primary_reason_to_use !== null) {
            return true;
        }

        return $tracking->update([
            'primary_reason_to_use' => $reason
        ]);
    }

    // EVENT 6
    public function setFirstBreathSessionAt(
        UserTracking $tracking,
        Carbon $time
    ): bool {
        return DB::transaction(function () use ($tracking, $time) {

            $lockedTracking = UserTracking::where('id', $tracking->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedTracking) {
                return false;
            }

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
    public function updateLastActiveAt(
        UserTracking $tracking,
        Carbon $time
    ): bool {
        return $tracking->update([
            'last_active_at' => $time
        ]);
    }
}
