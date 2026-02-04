<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\UserTracking;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;

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
}