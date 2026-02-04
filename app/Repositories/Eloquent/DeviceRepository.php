<?php

namespace App\Repositories\Eloquent;

use App\Models\UserDevice;
use App\Repositories\Contracts\DeviceRepositoryInterface;

class DeviceRepository implements DeviceRepositoryInterface
{
    public function create(array $data)
    {
        return UserDevice::create($data);
    }

    public function findByAnonymousId(string $anonymousId)
    {
        return UserDevice::where('anonymous_id', $anonymousId)->first();
    }

    public function updateRegister(int $id, array $data)
    {
        $device = UserDevice::find($id);
        if (!$device) {
            throw new \Exception('Device not found with id: ' . $id);
        }

        $device->update($data);
        return $device;
    }

    public function updateAppleWatchConnected(int $user_id, array $data)
    {
        $device = UserDevice::where('user_id', $user_id)->first();

        if (!$device) {
            throw new \Exception('Device not found with id: ' . $user_id);
        }

        $device->update($data);
        
        return $device;
    }
}
