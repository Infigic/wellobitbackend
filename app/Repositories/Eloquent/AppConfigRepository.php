<?php

namespace App\Repositories\Eloquent;
use App\Repositories\Contracts\AppConfigInterface;
use App\Models\AppConfig;
class AppConfigRepository Implements AppConfigInterface
{
    public function create(array $data)
    {
        return AppConfig::create($data);
    }

    public function updateConfig(int $id, array $data)
    {
        $appConfig = AppConfig::findOrFail($id);
        $appConfig->update($data);
        return $appConfig;
    }

    public function delete(int $id)
    {
        $appConfig = AppConfig::findOrFail($id);
        return $appConfig->delete();
    }

    public function toggleStatus(int $id)
    {
        $appConfig = AppConfig::findOrFail($id);
        $appConfig->is_active = !$appConfig->is_active;
        $appConfig->save();
        return $appConfig;
    }

    public function getConfig()
    {
        $configs = AppConfig::where('is_active', true)
            ->get()
            ->pluck('parsed_value', 'config_key');
        return $configs;
    }
}