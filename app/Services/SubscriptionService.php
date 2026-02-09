<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\UserTracking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Handle event: trial_started
     */
    public function handleTrialStarted(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {

            $existing = Subscription::where('user_id', $userId)
                ->whereNotNull('trial_started_at')
                ->first();

            if ($existing) {
                return [
                    'success' => true,
                    'status'  => 'already_started',
                    'message' => 'Trial already started',
                ];
            }

            $trialStartedAt = Carbon::parse($data['trial_started_at'])->format('Y-m-d H:i:s');
            $trialEndsAt    = Carbon::parse($data['trial_ends_at'])->format('Y-m-d H:i:s');

            $subscription = Subscription::create([
                'user_id'          => $userId,
                'plan_name'        => $data['plan_name'],
                'trial_started_at' => $trialStartedAt,
                'trial_ends_at'    => $trialEndsAt,
            ]);

            UserTracking::where('user_id', $userId)->update([
                'trial_started_at' => $trialStartedAt,
                'trial_ends_at'    => $trialEndsAt,
                'current_plan'     => $data['plan_name'],
            ]);

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

            $tracking = UserTracking::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$tracking) {
                return [
                    'success' => false,
                    'message' => 'User tracking not found'
                ];
            }

            $subscription = Subscription::where('user_id', $userId)
                ->latest()
                ->first();

            if ($subscription) {
                $subscription->update([
                    'paid_started_at' => $paidStartedAt,
                    'plan_name'       => $payload['plan_name'],
                ]);
            }

            $tracking->update([
                'paid_started_at' => $paidStartedAt,
                'current_plan'    => $payload['plan_name'],
                'is_paid'         => true,
            ]);

            return [
                'success' => true,
                'message' => 'Subscription started',
                'data' => [
                    'subscription_id' => $subscription?->id,
                    'plan_name'       => $payload['plan_name'],
                    'paid_started_at' => $paidStartedAt,
                ]
            ];

        });
    }

}
