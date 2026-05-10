<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hrv_logs', function (Blueprint $table) {
            $table->dropUnique('hrv_logs_hrv_uuid_unique');
            $table->unique(['hrv_uuid', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('hrv_logs', function (Blueprint $table) {
            $table->dropUnique(['hrv_uuid', 'user_id']);
            $table->unique('hrv_uuid');
        });
    }
};