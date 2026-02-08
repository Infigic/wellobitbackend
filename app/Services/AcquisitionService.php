<?php

namespace App\Services;

use App\Models\UserTracking;
use App\Services\DeviceService;
use App\Repositories\Contracts\AcquisitionAttributionInterface;

class AcquisitionService
{

    public function __construct(
        protected AcquisitionAttributionInterface $attributionRepository,
        protected DeviceService $deviceService
    ) {}

    /**
     * Handle attribution info event.
     * EVENT 3: attribution_info
     */
    public function handleAttributionInfo(array $data)
    {
        $deviceId = $this->deviceService->getDeviceIdByUuid($data['uuid']);
        $user_tracking_id = UserTracking::where('device_id', $deviceId)->value('id');
        return $this->attributionRepository->create([
            'user_tracking_id' => $user_tracking_id,
            'acquisition_channel' => $data['acquisition_channel'] ?? null,
            'acquisition_source' => $data['acquisition_source'] ?? null,
            'campaign_name' => $data['campaign_name'] ?? null,
        ]);
    }
}
