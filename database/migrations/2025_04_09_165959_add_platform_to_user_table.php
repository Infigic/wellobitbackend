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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('platform', ['simple', 'google', 'apple'])->default('simple')->after('remember_token');
            $table->string('provider_id')->nullable()->unique()->after('platform'); // For Google/Apple users only
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('platform');
            $table->dropColumn('provider_id');
        });
    }
};
