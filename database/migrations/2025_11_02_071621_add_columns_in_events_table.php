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
        Schema::table('events', function (Blueprint $table) {
            $table->string('map_address')->nullable();
            $table->string('map_url')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('cover_image')->nullable();
            $table->string('slug')->nullable();
            $table->json('tags')->nullable();
            $table->renameColumn('max_participants', 'total_seat');
            $table->integer('remaining_seat')->default(0);
            $table->unsignedBigInteger('municipality_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('map_address');
            $table->dropColumn('map_url');
            $table->dropColumn('city');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('cover_image');
            $table->dropColumn('slug');
            $table->renameColumn('total_seat', 'max_participants');
            $table->dropColumn('tags');
            $table->dropColumn('remaining_seat');
        });
    }
};
