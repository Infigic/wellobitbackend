<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\BreathSessionRepositoryInterface;
use App\Repositories\Contracts\UserTrackingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BreathSessionService
{
    public function __construct(
        protected BreathSessionRepositoryInterface $breathSessionRepo,
        protected UserTrackingRepositoryInterface $userTrackingRepo
    ) {}

    public function handle(User $user, array $payload): array
    {
        $event = $payload['event'] ?? null;

        return match ($event) {
            'first_breath_session_completed' =>
                $this->handleFirstSession($user, $payload),

            'breath_session_completed' =>
                $this->handleSubsequentSession($user, $payload),

            default =>
                throw new InvalidArgumentException('Unsupported breath session event'),
        };
    }

    /**
     * EVENT 6a – FIRST COMPLETED SESSION
     */
    private function handleFirstSession(User $user, array $payload): array
    {
        if (empty($payload['first_session_at'])) {
            throw new InvalidArgumentException('first_session_at is required');
        }

        $completedAt = Carbon::parse($payload['first_session_at']);

        return DB::transaction(function () use ($user, $completedAt) {

            $tracking = $this->userTrackingRepo->findByUserId($user->id);

            if (!$tracking) {
                throw new InvalidArgumentException('User tracking not found');
            }

            if ($tracking->first_breath_session_at !== null) {
                return [
                    'status' => 'already_exists',
                    'first_session_at' =>
                        Carbon::parse($tracking->first_breath_session_at)
                            ->toIso8601String(),
                ];
            }

            $this->breathSessionRepo->create(
                $user->id,
                0,
                $completedAt
            );

            $this->userTrackingRepo->setFirstBreathSessionAt(
                $tracking,
                $completedAt
            );

            return [
                'status' => 'created',
                'first_session_at' => $completedAt->toIso8601String(),
            ];
        });
    }

    /**
     * EVENT 6b – SUBSEQUENT SESSIONS
     */
    private function handleSubsequentSession(User $user, array $payload): array
    {
        if (!isset($payload['session_duration_seconds'])) {
            throw new InvalidArgumentException('session_duration_seconds is required');
        }

        if (!is_numeric($payload['session_duration_seconds'])) {
            throw new InvalidArgumentException('session_duration_seconds must be a number');
        }

        $duration = (int) $payload['session_duration_seconds'];

        if ($duration <= 0) {
            throw new InvalidArgumentException('session_duration_seconds must be greater than 0');
        }

        $completedAt = now();

        DB::transaction(function () use ($user, $duration, $completedAt) {

            $this->breathSessionRepo->create(
                $user->id,
                $duration,
                $completedAt
            );

            $tracking = $this->userTrackingRepo->findByUserId($user->id);

            if ($tracking) {
                $tracking->update([
                    'last_active_at' => $completedAt,
                ]);
            }
        });

        return [
            'status' => 'recorded',
        ];
    }
}
