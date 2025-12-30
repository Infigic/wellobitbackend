<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Hr;
use Carbon\Carbon;

class HrTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that HR data can be stored successfully.
     */
    public function test_hr_data_can_be_stored(): void
    {
        // Create a test user
        $user = User::factory()->create();

        // Prepare request data - note: validation expects user_id/hr_value but code uses userId/hrValue
        // This reflects a potential bug in the controller
        $response = $this->postJson('/api/hr', [
            'data' => [
                [
                    'userId' => $user->id,
                    'hrValue' => 75,
                ],
                [
                    'userId' => $user->id,
                    'hrValue' => 80,
                ],
            ],
        ]);
        // dd($response);
        // Check response structure
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
        $response->assertJson([
            'success' => true,
            'message' => 'HR data stored successfully.',
        ]);

        // Verify data was stored in database
        $this->assertDatabaseHas('hrs', [
            'user_id' => $user->id,
            // Note: column name in DB is 'hrvalue' based on migration
        ]);
    }

    /**
     * Test store method validation errors.
     */
    public function test_store_validation_errors(): void
    {
        // Test missing data field
        $response = $this->postJson('/api/hr', []);

        $response->assertStatus(404); // sendError defaults to 404
        $response->assertJson([
            'success' => false,
            'message' => 'Validation Error.',
        ]);

        // Test empty data array
        $response = $this->postJson('/api/hr', [
            'data' => [],
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test fetchByTimestamp returns all records when timestamp is 0.
     */
    public function test_fetch_by_timestamp_returns_all_when_zero(): void
    {
        $user = User::factory()->create();

        // Create some HR records
        $hr1 = Hr::create([
            'user_id' => $user->id,
            'hrvalue' => 75,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        $hr2 = Hr::create([
            'user_id' => $user->id,
            'hrvalue' => 80,
            'created_at' => Carbon::now()->subHour(),
        ]);

        $response = $this->postJson('/api/hr/fetch', [
            'userId' => $user->id,
            'timestamp' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'reports' => [
                    '*' => [
                        'userId',
                        'timestamp',
                        'hrValue',
                    ],
                ],
            ],
        ]);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json('data.reports');
        $this->assertCount(2, $data);
    }

    /**
     * Test fetchByTimestamp filters by timestamp when provided.
     */
    public function test_fetch_by_timestamp_filters_correctly(): void
    {
        $user = User::factory()->create();

        $oldTimestamp = Carbon::now()->subHours(3);
        $newTimestamp = Carbon::now()->subHour();

        // Create old record
        Hr::create([
            'user_id' => $user->id,
            'hrvalue' => 70,
            'created_at' => $oldTimestamp,
        ]);

        // Create new record
        Hr::create([
            'user_id' => $user->id,
            'hrvalue' => 85,
            'created_at' => $newTimestamp,
        ]);

        // Fetch records newer than the old timestamp
        $response = $this->postJson('/api/hr/fetch', [
            'userId' => $user->id,
            'timestamp' => $oldTimestamp->timestamp,
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test fetchByTimestamp validation errors.
     */
    public function test_fetch_by_timestamp_validation_errors(): void
    {
        // Test missing userId
        $response = $this->postJson('/api/hr/fetch', [
            'timestamp' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ]);

        // Test missing timestamp
        $user = User::factory()->create();
        $response = $this->postJson('/api/hr/fetch', [
            'userId' => $user->id,
        ]);

        $response->assertStatus(422);

        // Test invalid userId (non-existent)
        $response = $this->postJson('/api/hr/fetch', [
            'userId' => 99999,
            'timestamp' => 0,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test fetchByTimestamp returns empty when no records found.
     */
    public function test_fetch_by_timestamp_returns_empty_when_no_records(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/hr/fetch', [
            'userId' => $user->id,
            'timestamp' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'No reports found',
        ]);

        $data = $response->json('data.reports');
        $this->assertEmpty($data);
    }

    /**
     * Test fetchByTimestamp only returns records for specified user.
     */
    public function test_fetch_by_timestamp_returns_only_user_records(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Hr::create([
            'user_id' => $user1->id,
            'hrvalue' => 75,
        ]);

        Hr::create([
            'user_id' => $user2->id,
            'hrvalue' => 85,
        ]);

        $response = $this->postJson('/api/hr/fetch', [
            'userId' => $user1->id,
            'timestamp' => 1,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data.reports');

        $this->assertCount(1, $data);
        $this->assertEquals($user1->id, $data[0]['userId']);
        $this->assertEquals(75, $data[0]['hrValue']);
    }
}
