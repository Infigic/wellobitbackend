<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;

/**
 * Requests (Events 1–3–5)
 */
use App\Http\Requests\AppInstalledRequest;
use App\Http\Requests\UserRegisteredRequest;
use App\Http\Requests\AcquisitionInfoRequest;
use App\Http\Requests\AppleWatchConnectedRequest;

/**
 * Services
 */
use App\Services\UserTrackingService;
use App\Services\DeviceService;
use App\Services\AcquisitionService;
use App\Services\BreathSessionService;
use App\Services\SubscriptionService;

class EventController extends BaseController
{
    /**
     * Inject all services needed for both store() and track()
     */
    public function __construct(
        protected UserTrackingService $trackingService,
        protected DeviceService $deviceService,
        protected AcquisitionService $acquisitionService,
        protected BreathSessionService $breathSessionService,
        protected SubscriptionService $subscriptionService
    ) {}

    public function store(Request $request)
    {
        $eventName = $request->input('event');

        if (!$eventName) {
            return $this->sendError('Event name is required.', [], 400);
        }

        try {
            $result = match ($eventName) {
                'app_installed'          => $this->handleAppInstalled($request),
                'user_registered'        => $this->handleUserRegistered($request),
                'acquisition_info'       => $this->handleAttributionInfo($request),
                'apple_watch_connected'  => $this->handleAppleWatchConnected($request),
                'health_connected'       => $this->handleHealthConnected($request),
                default                 => throw new \Exception('Unknown event name.')
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

            $tracking = $this->trackingService->handleAppInstalled(
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
        $device_id = $this->deviceService
            ->getDeviceIdByAnonymousId($request->input('anonymous_id'));

        $request->merge(['device_id' => $device_id]);

        $validated = $request->validate((new UserRegisteredRequest())->rules());

        return DB::transaction(function () use ($validated) {

            $device = $this->trackingService->handleRegisteredUser($validated);

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
        $request->merge(['user_id' => $user->id]);

        $validated = $request->validate((new AppleWatchConnectedRequest())->rules());

        $result = [
            'device'   => null,
            'tracking' => null,
        ];

        try {
            $result['device'] =
                $this->deviceService->handleAppleWatchConnected($validated);
        } catch (\Exception $e) {
            $result['device'] = ['error' => $e->getMessage()];
        }

        try {
            $result['tracking'] =
                $this->trackingService->handleAppleWatchConnected($validated);
        } catch (\Exception $e) {
            $result['tracking'] = ['error' => $e->getMessage()];
        }

        if (isset($result['device']['error']) && isset($result['tracking']['error'])) {
            throw new \Exception('Both device and tracking updates failed.');
        }

        return $result;
    }

    private function handleHealthConnected(Request $request)
    {
        $user = $request->user();
        $request->merge(['user_id' => $user->id]);

        $validated = $request->validate((new AppleWatchConnectedRequest())->rules());

        $tracking = $this->trackingService->handleHealthConnected($validated);

        return [
            'tracking' => $tracking,
        ];
    }

    public function track(Request $request)
    {
        $event = $request->input('event');

        switch ($event) {

            /**
             * EVENT 4 – PRIMARY REASON
             */
            case 'primary_reason_selected':

                $validated = $request->validate([
                    'primary_reason_to_use' =>
                        'nullable|string|in:stress,sleep,breathwork,hrv,focus,curiosity'
                ]);

                $userId = auth()->id();

                if (!$userId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated.'
                    ], 401);
                }

                Log::info('TRACK EVENT: primary_reason_selected', [
                    'user_id' => $userId,
                    'payload' => $request->all()
                ]);

                return response()->json(
                    $this->trackingService->handlePrimaryReasonSelected(
                        $userId,
                        $validated['primary_reason_to_use'] ?? null
                    )
                );

            /**
             * EVENT 6 – BREATH SESSION
             */
            case 'first_breath_session_completed':
            case 'breath_session_completed':

                if (!$request->user()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated.'
                    ], 401);
                }

                Log::info('TRACK EVENT: breath session', [
                    'event'   => $event,
                    'user_id' => $request->user()->id,
                    'payload' => $request->all()
                ]);

                try {
                    $result = $this->breathSessionService->handle(
                        $request->user(),
                        $request->all()
                    );
                } catch (InvalidArgumentException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                    ], 422);
                }

                $message = match ($result['status'] ?? null) {
                    'created'        => 'First breath session recorded',
                    'already_exists' => 'First breath session already recorded',
                    'recorded'       => 'Breath session recorded',
                    default          => 'Event processed',
                };

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data'    => isset($result['first_session_at'])
                        ? ['first_session_at' => $result['first_session_at']]
                        : null,
                ]);

            /**
             * EVENT 7 – APP ACTIVE
             */
            case 'app_active':

                if (!$request->user()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated.'
                    ], 401);
                }

                $validated = $request->validate([
                    'last_active_at' => 'required|date'
                ]);

                Log::info('TRACK EVENT: app_active', [
                    'user_id' => $request->user()->id,
                    'payload' => $validated
                ]);

                $result = $this->trackingService->handleAppActive(
                    $request->user()->id,
                    $validated['last_active_at']
                );

                return response()->json($result);
            /**
             * EVENT 8 – TRIAL & SUBSCRIPTION
             */
            case 'trial_started':

            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            $validated = $request->validate([
                'trial_started_at' => 'required|date',
                'trial_ends_at'    => 'required|date|after:trial_started_at',
                'plan_name'        => 'required|string',
            ]);

            Log::info('TRACK EVENT: trial_started', [
                'user_id' => $request->user()->id,
                'payload' => $validated
            ]);

            $result = $this->subscriptionService->handleTrialStarted(
                $request->user()->id,
                $validated
            );

            return response()->json($result);


            case 'subscription_started':

                if (!$request->user()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthenticated.'
                    ], 401);
                }

                $validated = $request->validate([
                    'paid_started_at' => 'required|date',
                    'plan_name'       => 'required|string',
                ]);

                Log::info('TRACK EVENT: subscription_started', [
                    'user_id' => $request->user()->id,
                    'payload' => $validated
                ]);

                $result = $this->subscriptionService->handleSubscriptionStarted(
                    $request->user()->id,
                    $validated
                );

                return response()->json($result);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown event type.'
                ], 400);
        }
    }
}
