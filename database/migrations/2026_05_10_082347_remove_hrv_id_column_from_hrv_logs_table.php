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
        Schema::table('hrv_logs', function (Blueprint $table) {
            $table->dropForeign(['hrv_id']);
            $table->dropColumn('hrv_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrv_logs', function (Blueprint $table) {
            //
        });
    }
};
