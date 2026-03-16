<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\Http\Requests\AppConfigRequest;
use App\Services\AppConfigService;
class AppConfigController extends BaseController
{
    public function __construct(AppConfigService $appConfigService)
    {
        $this->appConfigService = $appConfigService;
    }
    public function getConfig()
    {
        $configs = $this->appConfigService->getConfig();
        return $this->sendResponse($configs, 'Configurations retrieved successfully.');

    }
}