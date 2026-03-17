<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AppConfigDataTable;
use App\Http\Requests\AppConfigRequest;
use App\Services\AppConfigService;
use App\Models\AppConfig;

class AppConfigController extends Controller
{

    public function __construct(AppConfigService $appConfigService)
    {
        $this->appConfigService = $appConfigService;
    }

    public function index(AppConfigDataTable $dataTable)
    {
        return $dataTable->render('app_configs.index');
    }
    
    public function create()
    {
       $configKeys = config('app_config');
        return view('app_configs.create', compact('configKeys'));
    }

    public function store(AppConfigRequest $request)
    {
        $validate = $request->validated();
        $appConfig = $this->appConfigService->createConfig($validate);

        return redirect()->route('app-configs.index')->with('success', 'App configuration created successfully.');
    }

    public function edit(AppConfig $appConfig)
    {
        $configKey = $appConfig->config_key;
        return view('app_configs.edit', compact('appConfig', 'configKey'));
    }

    public function update(AppConfigRequest $request, AppConfig $appConfig)
    {
        $validate = $request->validated();
        $data = [
            'config_key' => $validate['config_key'] ?? null,
            'value_type' => $validate['value_type'] ?? null,
            'config_value' => $validate['config_value'] ?? null,
            'description' => $validate['description'] ?? null,
        ];
        $this->appConfigService->updateConfig($appConfig->id, $data);
        return redirect()->route('app-configs.index')->with('success', 'App configuration updated successfully.');
    }

    public function destroy(AppConfig $appConfig)
    {
        $this->appConfigService->deleteConfig($appConfig->id);
        return redirect()->route('app-configs.index')->with('success', 'App configuration deleted successfully.');
    }

    public function toggleStatus(AppConfig $appConfig)
    {
        $this->appConfigService->toggleStatus($appConfig->id);
        return redirect()->route('app-configs.index')->with('success', 'Status updated successfully.');
    }
}
