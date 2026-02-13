<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialAuthRequest;
use App\Services\SocialAuthService;

class SocialAuthController extends Controller
{
    public function handle(SocialAuthRequest $request, SocialAuthService $service)
    {
        return $service->handle($request->validated());
    }
}
