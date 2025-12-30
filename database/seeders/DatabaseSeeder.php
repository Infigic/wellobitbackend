<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@aayoo.ai',
            'email_verified_at' => now(),
            'is_active' => true,
            'password' => Hash::make('!Aayoo@321!'),
            'role' => 'admin',
        ]);
        \App\Models\User::factory(20)->create();
        $this->call([
            QuoteSeeder::class,
            CmsPageSeeder::class,
        ]);
    }
}
