<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\AcquisitionInfoRequest;
use App\Http\Requests\AppInstalledRequest;
use App\Http\Requests\AppleWatchConnectedRequest;
use App\Http\Requests\UserRegisteredRequest;
use App\Services\DeviceService;
use App\Services\UserTrackingService;
use App\Services\AcquisitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventController extends BaseController
{

    public function __construct(
        protected UserTrackingService $userTrackingService,
        protected DeviceService $deviceService,
        protected AcquisitionService $acquisitionService
    ) {}

    public function store(Request $request)
    {
        $eventName = $request->input('event');

        if (!$eventName) {
            return $this->sendError('Event name is required.', [], 400);
        }

        try {
            $result = match ($eventName) {
                'app_installed' => $this->handleAppInstalled($request),
                'user_registered' => $this->handleUserRegistered($request),
                'acquisition_info' => $this->handleAttributionInfo($request),
                'apple_watch_connected' => $this->handleAppleWatchConnected($request),
                'health_connected' => $this->handleHealthConnected($request),
                default => throw new \Exception('Unknown event name.')
            };

            return $this->sendResponse($result, 'Event processed successfully.');
        } catch (ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->sendError('Database error: ' . $e->getMessage(), [], 500);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    private function handleAppInstalled(Request $request)
    {
        $validated = $request->validate((new AppInstalledRequest())->rules());

        return DB::transaction(function () use ($validated) {

            $device = $this->deviceService->handleAppInstalled($validated);

            $tracking = $this->userTrackingService->handleAppInstalled(
                $validated,
                $device->id
            );

            return [
                'device'   => $device,
                'tracking' => $tracking,
            ];
        });
    }

    private function handleUserRegistered(Request $request)
    {
        $device_id = $this->deviceService->getDeviceIdByAnonymousId($request->input('anonymous_id'));
        $request->merge(['device_id' => $device_id]);
        $validated = $request->validate((new UserRegisteredRequest())->rules());

        return DB::transaction(function () use ($validated) {
            $device = $this->userTrackingService->handleRegisteredUser($validated);
            $tracking = $this->deviceService->handleRegisteredUser($validated);

            return [
                'device'   => $device,
                'tracking' => $tracking,
            ];
        });
    }

    private function handleAttributionInfo(Request $request)
    {
        $validated = $request->validate((new AcquisitionInfoRequest())->rules());
        return $this->acquisitionService->handleAttributionInfo($validated);
    }

    private function handleAppleWatchConnected(Request $request)
    {
        $user = $request->user();

        $user_id = $user->id;

        $request->merge(['user_id' => $user_id]);

        $validated = $request->validate((new AppleWatchConnectedRequest())->rules());

        $result = [
            'device' => null,
            'tracking' => null,
        ];
        $hasError = false;

        // Try to handle device service
        try {
            $result['device'] = $this->deviceService->handleAppleWatchConnected($validated);
        } catch (\Exception $e) {
            $result['device'] = ['error' => $e->getMessage()];
            $hasError = true;
        }

        // Try to handle user tracking service
        try {
            $result['tracking'] = $this->userTrackingService->handleAppleWatchConnected($validated);
        } catch (\Exception $e) {
            $result['tracking'] = ['error' => $e->getMessage()];
            $hasError = true;
        }

        // If both failed
        if (isset($result['device']['error']) && isset($result['tracking']['error'])) {
            throw new \Exception('Both device and tracking updates failed.');
        }

        return $result;
    }

    private function handleHealthConnected(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $request->merge(['user_id' => $user_id]);
        $validated = $request->validate((new AppleWatchConnectedRequest())->rules());

        $tracking = $this->userTrackingService->handleHealthConnected($validated);
    }
}
