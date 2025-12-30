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
        Schema::table('hrvs', function (Blueprint $table) {
            $table->string('sample_id')->nullable()->after('user_id');
            $table->bigInteger('device_timestamp')->nullable()->after('datetime');
            $table->integer('sdnn')->nullable()->after('hrv');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hrvs', function (Blueprint $table) {
            $table->dropColumn(['sdnn', 'sample_id', 'device_timestamp']);
        });
    }
};
