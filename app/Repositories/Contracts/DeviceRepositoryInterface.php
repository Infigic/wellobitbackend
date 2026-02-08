<?php

namespace App\Repositories\Contracts;

interface DeviceRepositoryInterface
{
    public function create(array $data);

    public function updateRegister(int $id, array $data);

    public function updateAppleWatchConnected(int $id, array $data);

    public function findByUuid(string $uuid);
}
