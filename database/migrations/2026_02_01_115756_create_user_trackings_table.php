<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_trackings', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name');
            $table->string('signup_method');
            $table->string('signup_source');
            $table->dateTime('installed_at'); 
            $table->dateTime('registered_at');
            $table->boolean('consent_email')->default(false);
            $table->string('primary_reason_to_use')->nullable();
            $table->dateTime('first_breath_session_at')->nullable();
            $table->dateTime('last_active_at')->nullable();
            $table->dateTime('trial_started_at')->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('paid_started_at')->nullable();
            $table->boolean('has_apple_watch')->default(false);
            $table->boolean('apple_health_connected')->default(false);
            $table->string('current_plan')->nullable();
            $table->string('is_paid')->default('no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_trackings');
    }
};
