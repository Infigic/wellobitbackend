<?php 

namespace App\Repositories\Contracts;

use App\Models\UserTracking;
use Carbon\Carbon;

interface UserTrackingRepositoryInterface 

{
    public function create(array $data);

    public function updateRegister(int $id, array $data);
    
    public function updateAppleWatchConnected(int $user_id, array $data);

    public function updateHealthConnected(int $user_id, array $data);

    public function getOrCreateUser(int $userId): ?UserTracking;

    /**
     * Event 4: Primary Reason Selected
     */
    public function setPrimaryReason(
        UserTracking $tracking,
        string $reason
    ): bool;

    /**
     * Event 6: First Breath Session Completed
     */
    public function setFirstBreathSessionAt(
        UserTracking $tracking,
        Carbon $time
    ): bool;

    /**
     * Event 7: App Active
     */
    public function updateLastActiveAt(
        UserTracking $tracking,
        Carbon $time
    ): bool;
}
