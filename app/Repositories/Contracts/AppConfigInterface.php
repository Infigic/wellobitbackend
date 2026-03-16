<?php

namespace App\Repositories\Contracts;

interface AppConfigInterface
{
    public function create(array $data);

    public function updateConfig(int $id, array $data);

    public function delete(int $id);

    public function toggleStatus(int $id);

    public function getConfig();

}