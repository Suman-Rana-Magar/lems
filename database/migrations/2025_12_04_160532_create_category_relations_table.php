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
        Schema::create('category_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_a_id');
            $table->unsignedBigInteger('category_b_id');
            $table->decimal('relatedness', 3, 2); // Score between 0.00 and 1.00
            $table->timestamps();

            // Optional: foreign keys if you have a categories table
            $table->foreign('category_a_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('category_b_id')->references('id')->on('categories')->onDelete('cascade');

            // Unique constraint to avoid duplicate rows
            $table->unique(['category_a_id', 'category_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_relations');
    }
};
