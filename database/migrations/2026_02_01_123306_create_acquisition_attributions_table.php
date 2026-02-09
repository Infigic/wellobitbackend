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
        Schema::create('acquisition_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_tracking_id')->constrained('user_trackings');
            $table->string('acquisition_channel')->nullable();
            $table->string('acquisition_source')->nullable();
            $table->string('campaign_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquisition_attributions');
    }
};
