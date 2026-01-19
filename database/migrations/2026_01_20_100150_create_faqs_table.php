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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id(); // FAQ ID
            $table->string('title'); // FAQ title or question
            $table->text('subtitle'); // FAQ description or answer
            $table->string('image_url')->nullable(); // Optional image URL
            $table->foreignId('category_id')
                  ->constrained('faq_categories')
                  ->cascadeOnDelete(); // Each FAQ belongs to one category
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
