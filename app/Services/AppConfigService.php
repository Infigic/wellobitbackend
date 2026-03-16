<?php 

namespace App\Services;

use App\Repositories\Contracts\AppConfigInterface;

class AppConfigService
{
    public function __construct(AppConfigInterface $appConfigRepository)
    {
        $this->appConfigRepository = $appConfigRepository;
    }

    public function createConfig(array $data)
    {
        return $this->appConfigRepository->create($data);
    }

    public function updateConfig($id, array $data)
    {
        return $this->appConfigRepository->updateConfig($id, $data);
    }

    public function deleteConfig($id)
    {
        return $this->appConfigRepository->delete($id);
    }

    public function toggleStatus($id)
    {
        return $this->appConfigRepository->toggleStatus($id);
    }

    public function getConfig()
    {
        $configs = $this->appConfigRepository->getConfig();
        return $configs;
    }
}
