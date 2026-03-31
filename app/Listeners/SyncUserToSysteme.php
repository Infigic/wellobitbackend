<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SyncUserToSysteme
{
    protected string $invalidEmailFile;

    public function __construct()
    {
        $this->invalidEmailFile = storage_path('logs/invalid_emails.txt');

        if (!File::exists($this->invalidEmailFile)) {
            File::put($this->invalidEmailFile, "");
        }
    }

    public function handle(object $event): void
    {
        $user = $event->user;

        try {
            $response = Http::withHeaders([
                'X-API-Key' => config('services.systeme.api_key'),
            ])->post('https://api.systeme.io/api/contacts', [
                'email' => $user->email,
                'fields' => [
                    ['slug' => 'first_name', 'value' => $user->name],
                ],
            ]);

            if ($response->failed()) {
                $body = $response->json();
                $errorDetail = $body['detail'] ?? '';

                if (stripos($errorDetail, 'Email address is invalid') !== false) {
                    $line = date('Y-m-d H:i:s') . " | user_id={$user->id} | invalid_email_systeme={$user->email}\n";
                    File::append($this->invalidEmailFile, $line);
                    Log::warning("Systeme.io rejected invalid email: {$user->email} (user_id={$user->id})");

                    return; 
                }

                throw new \Exception($response->body());
            }

            $contactId = $response->json('id');
            Log::info('Systeme.io contact id: ' . $contactId);

            $tagResponse = Http::withHeaders([
                'X-API-Key' => config('services.systeme.api_key'),
            ])->post("https://api.systeme.io/api/contacts/{$contactId}/tags", [
                'tagId' => (int) config('services.systeme.tags'),
            ]);

            Log::info('Synced to Systeme.io', [
                'user_id' => $user->id
            ]);

        } catch (\Exception $e) {
            Log::error('Systeme sync failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }
}