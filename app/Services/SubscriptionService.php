<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\UserTracking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Str;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;

class SubscriptionService
{

    public function __construct(
        protected UserTrackingRepositoryInterface $userTrackingRepo
    ) {} 


    /**
     * Handle event: trial_started
     */
    public function handleTrialStarted(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {

            $user = User::find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                ];
            }

            $email = $user->email;

            $existingTracking = UserTracking::where('email', $email)
                ->whereNotNull('trial_started_at')
                ->lockForUpdate()
                ->first();

            if ($existingTracking) {
                return [
                    'success' => true,
                    'status'  => 'already_used',
                    'message' => 'This email has already used trial',
                ];
            }

            $userTracking = $this->userTrackingRepo->getOrCreateUser($userId);

            $subscription = Subscription::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            $trialStartedAt = Carbon::parse($data['trial_started_at'])->format('Y-m-d H:i:s');
            $trialEndsAt    = Carbon::parse($data['trial_ends_at'])->format('Y-m-d H:i:s');

            if (!$subscription) {
                $subscription = Subscription::create([
                    'user_id' => $userId,
                    'plan_name' => $data['plan_name'],
                    'trial_started_at' => $trialStartedAt,
                    'trial_ends_at'    => $trialEndsAt,
                ]);
            } else {
                $subscription->update([
                    'plan_name'        => $data['plan_name'],
                    'trial_started_at' => $trialStartedAt,
                    'trial_ends_at'    => $trialEndsAt,
                ]);
            }

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

            $tracking = $this->userTrackingRepo->getOrCreateUser($userId);

            $subscription = Subscription::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            $paidStartedAt = Carbon::parse($payload['paid_started_at'])->toDateTimeString();

            if (!$subscription) {
                $subscription = Subscription::create([
                    'user_id' => $userId,
                    'plan_name' => $payload['plan_name'],
                ]);
            }

            $subscription->update([
                'plan_name'       => $payload['plan_name'],
                'paid_started_at' => $paidStartedAt,
            ]);

            $this->syncPaidToTracking($tracking, $subscription);

            return [
                'success' => true,
                'status'  => 'Subscription started',
                'message' => 'Subscription completed',
                'data' => [
                    'subscription_id' => $subscription->id,
                    'plan_name'       => $payload['plan_name'],
                    'paid_started_at' => $paidStartedAt,
                ]
            ];
        });
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
