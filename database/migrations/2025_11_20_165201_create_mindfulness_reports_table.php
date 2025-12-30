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
        Schema::create('mindfulness_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('session_id');
            $table->timestamp('timestamp');
            $table->decimal('avg_hr', 8, 2);
            $table->decimal('sdnn', 8, 2);
            $table->decimal('rmssd', 8, 2);
            $table->decimal('pnn50', 8, 2);
            $table->decimal('pnn20', 8, 2);
            $table->timestamps();

            $table->index(['user_id', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mindfulness_reports');
    }
};
