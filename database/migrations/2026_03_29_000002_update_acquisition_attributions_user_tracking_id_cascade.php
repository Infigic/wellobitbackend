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
        Schema::table('acquisition_attributions', function (Blueprint $table) {
            $table->dropForeign(['user_tracking_id']);
            $table->foreign('user_tracking_id')
                ->references('id')->on('user_trackings')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('acquisition_attributions', function (Blueprint $table) {
            $table->dropForeign(['user_tracking_id']);
            $table->foreign('user_tracking_id')
                ->references('id')->on('user_trackings');
        });
    }
};
