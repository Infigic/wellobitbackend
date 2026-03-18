<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\UserTracking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Handle event: trial_started
     */
    public function handleTrialStarted(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {

            $subscription = Subscription::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            $userTracking = $this->getOrCreateUser($userId);

            if ($subscription && $subscription->trial_started_at) {

                if (!$userTracking->trial_started_at) {

                    $this->syncTrialToTracking($userTracking, $subscription);

                    return [
                        'success' => true,
                        'status'  => 'already_started_synced',
                        'synced'  => true,
                        'message' => 'Trial already started, tracking updated',
                    ];
                }

                return [
                    'success' => true,
                    'status'  => 'already_started',
                    'message' => 'Trial already started',
                ];
            }

            $trialStartedAt = Carbon::parse($data['trial_started_at'])->format('Y-m-d H:i:s');
            $trialEndsAt    = Carbon::parse($data['trial_ends_at'])->format('Y-m-d H:i:s');

            if (!$subscription) {
                $subscription = Subscription::create([
                    'user_id' => $userId,
                    'plan_name'        => $data['plan_name'],
                ]);
            }

            $subscription->update([
                'plan_name'        => $data['plan_name'],
                'trial_started_at' => $trialStartedAt,
                'trial_ends_at'    => $trialEndsAt,
            ]);

            $this->syncTrialToTracking($userTracking, $subscription);

            return [
                'success' => true,
                'message' => 'Trial started',
                'data'    => $subscription,
            ];
        });
    }

    /**
     * Handle event: subscription_started
     */
    public function handleSubscriptionStarted(int $userId, array $payload): array
    {
        return DB::transaction(function () use ($userId, $payload) {

            $paidStartedAt = Carbon::parse($payload['paid_started_at'])
                ->toDateTimeString();

            $tracking = $this->getOrCreateUser($userId);

            $subscription = Subscription::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$subscription) {
                $subscription = Subscription::create([
                    'user_id' => $userId,
                    'plan_name' => $payload['plan_name'],
                ]);
            }

           if ($subscription && $subscription->paid_started_at) {

                if (!$tracking->paid_started_at) {
                    $this->syncPaidToTracking($tracking, $subscription);

                    return [
                        'success' => true,
                        'status'  => 'already_paid_synced',
                        'message' => 'Subscription already started, tracking updated',
                    ];
                }

                return [
                    'success' => true,
                    'status'  => 'already_paid',
                    'message' => 'Subscription already started',
                ];
            }

            $subscription->update([
                'plan_name'       => $payload['plan_name'],
                'paid_started_at' => $paidStartedAt,
            ]);

            $this->syncPaidToTracking($tracking, $subscription);

            return [
                'success' => true,
                'message' => 'Subscription started',
                'status'  => 'subscription_started',
                'data' => [
                    'subscription_id' => $subscription?->id,
                    'plan_name'       => $payload['plan_name'],
                    'paid_started_at' => $paidStartedAt,
                ]
            ];

        });
    }


    public function getOrCreateUser(int $userId): UserTracking
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception("User $userId not found");
        }

        $userTracking = UserTracking::where('user_id', $userId)
            ->lockForUpdate()
            ->first();
        
        if (!$userTracking) {
            $userDevice = UserDevice::create([
                'user_id' => $user->id,
                'uuid' => Str::uuid()->toString(),
                'timezone' => config('app.timezone'),
                'locale' => app()->getLocale(),
            ]);

            $userTracking = UserTracking::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'first_name' => $user->name,
                'device_id' => $userDevice->id,
                'installed_at' => $user->created_at,
                'primary_reason_to_use' => $user->reason
            ]);

            return $userTracking;
        }

        return $userTracking;
    }

    private function syncTrialToTracking(UserTracking $tracking, Subscription $subscription): void
    {
        $tracking->update([
            'trial_started_at' => $subscription->trial_started_at,
            'trial_ends_at'    => $subscription->trial_ends_at,
            'current_plan'     => $subscription->plan_name,
        ]);
    }

    private function syncPaidToTracking(UserTracking $tracking, Subscription $subscription): void
    {
        $tracking->update([
            'paid_started_at' => $subscription->paid_started_at,
            'current_plan'    => $subscription->plan_name,
            'is_paid'         => true,
        ]);
    }
}
