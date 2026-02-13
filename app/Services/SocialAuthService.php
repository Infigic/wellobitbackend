<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use App\Services\UserTrackingService;
use Illuminate\Support\Facades\DB;

class SocialAuthService
{
    public function __construct(
        protected UserTrackingService $trackingService
    ) {}

    public function handle(array $data)
    {
        $user = User::where('platform', $data['platform'])
                    ->where('provider_id', $data['token'])
                    ->first();

        if ($user) {
            $type = 'login';
        } else {

            DB::transaction(function () use (&$user, $data) {

                $user = User::create([
                    'platform'    => $data['platform'],
                    'provider_id' => $data['token'],
                    'email'       => $data['email'] ?? null,
                    'name'        => $data['name'] ?? null,
                    'password'    => bcrypt(Str::random(16)),
                ]);

                $this->trackingService->handleRegisteredUser([
                    'email'         => $user->email,
                    'uuid'          => $data['uuid'],
                    'first_name'    => $user->name,
                    'consent_email' => $data['consent_email'],
                    'signup_method' => $data['platform'],
                    'signup_source' => $data['signup_source'],
                ]);
            });

            $type = 'register';
        }

        $user->tokens()->delete();

        $accessToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'type'    => $type,
            'token'   => $accessToken,
            'user'    => $user,
        ]);
    }

}
