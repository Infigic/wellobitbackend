<?php

namespace App\Repositories\Contracts;

use App\Models\BreathSession;
use Carbon\Carbon;

interface BreathSessionRepositoryInterface
{
    public function create(
        int $userId,
        int $durationSeconds,
        Carbon $completedAt
    ): BreathSession;
}
