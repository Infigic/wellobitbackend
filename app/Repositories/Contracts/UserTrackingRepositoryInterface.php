<?php 

namespace App\Repositories\Contracts;

interface UserTrackingRepositoryInterface 
{
    public function create(array $data);

    public function updateRegister(int $id, array $data);
    
    public function updateAppleWatchConnected(int $user_id, array $data);

    public function updateHealthConnected(int $user_id, array $data);
}
