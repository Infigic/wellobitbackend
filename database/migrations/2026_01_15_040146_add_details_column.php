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
            $table->integer('age')->nullable()->after('otp_expires_at');
            $table->enum('gender',[
                'MALE', 
                'FEMALE', 
                'OTHER'
            ])->nullable()->after('age');
            $table->enum('activity_level', [
                'INACTIVE', 
                'LIGHT', 
                'MODERATE', 
                'VERY_ACTIVE'
            ])->nullable()->after('gender');
            $table->json('reason')->nullable()->after('activity_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age', 'gender','activity_level', 'reason']);
        });
    }
};
