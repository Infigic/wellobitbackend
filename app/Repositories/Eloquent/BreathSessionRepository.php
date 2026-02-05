<?php

namespace App\Repositories\Eloquent;

use App\Models\BreathSession;
use App\Repositories\Contracts\BreathSessionRepositoryInterface;
use Carbon\Carbon;

class BreathSessionRepository implements BreathSessionRepositoryInterface
{
    /**
     * Create a breath session record
     */
    public function create(
        int $userId,
        int $durationSeconds,
        Carbon $completedAt
    ): BreathSession {
        return BreathSession::create([
            'user_id' => $userId,
            'session_duration_seconds' => $durationSeconds,
            'completed_at' => $completedAt,
        ]);
    }
}
