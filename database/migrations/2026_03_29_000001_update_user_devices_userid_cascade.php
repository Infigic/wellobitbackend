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
        // Drop old foreign key
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
    
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop cascade foreign key
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')
                ->references('id')->on('users');
        });
    }
};
