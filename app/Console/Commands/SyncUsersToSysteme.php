<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SyncUsersToSysteme extends Command
{
    protected $signature = 'users:sync-systeme';
    protected $description = 'Sync existing users to Systeme.io directly, with retry and log invalid emails';

    public function handle()
    {
        $batchSize = 50;
        $delaySeconds = 1;
        $maxRetries = 3;

        $invalidEmailFile = storage_path('logs/invalid_emails.txt');
        if (!File::exists($invalidEmailFile)) {
            File::put($invalidEmailFile, ""); 
        }

        User::chunk($batchSize, function ($users) use ($maxRetries, $delaySeconds, $invalidEmailFile) {
            foreach ($users as $user) {

                $email = trim(strtolower($user->email));

                $success = false;
                $attempt = 0;

                while (!$success && $attempt < $maxRetries) {
                    $attempt++;
                    try {
                        $response = Http::withHeaders([
                            'X-API-Key' => config('services.systeme.api_key'),
                        ])->post('https://api.systeme.io/api/contacts', [
                            'email' => $email,
                            'fields' => [
                                ['slug' => 'first_name', 'value' => $user->name],
                            ],
                        ]);

                        if ($response->failed()) {
                            $body = $response->json();
                            $errorDetail = $body['detail'] ?? '';

                            if (stripos($errorDetail, 'Email address is invalid') !== false) {
                                $line = date('Y-m-d H:i:s') . " | user_id={$user->id} | invalid_email_systeme={$email}\n";
                                File::append($invalidEmailFile, $line);
                                Log::warning("Systeme.io rejected invalid email: {$email} (user_id={$user->id})");
                                break;
                            }

                            throw new \Exception($response->body());
                        }

                        $contactId = $response->json('id');
                        Log::info("Systeme.io contact created: user_id={$user->id}, contact_id={$contactId}");

                        $tagResponse = Http::withHeaders([
                            'X-API-Key' => config('services.systeme.api_key'),
                        ])->post("https://api.systeme.io/api/contacts/{$contactId}/tags", [
                            'tagId' => (int) config('services.systeme.tags'),
                        ]);

                        if ($tagResponse->failed()) {
                            throw new \Exception('Tag API failed: ' . $tagResponse->body());
                        }

                        Log::info("Systeme.io tag added: user_id={$user->id}, contact_id={$contactId}");
                        $success = true;

                    } catch (\Exception $e) {
                        if ($attempt >= $maxRetries) {
                            Log::error("Giving up on user_id={$user->id} after {$maxRetries} attempts: " . $e->getMessage());
                        } else {
                            sleep(1); 
                        }
                    }
                }
            }

            sleep($delaySeconds);
        });

        $this->info('All users synced directly. Invalid emails logged in: ' . $invalidEmailFile);
    }
}