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
        Schema::table('organizer_requests', function (Blueprint $table) {
            $table->dropColumn('phone_no');
            $table->dropColumn('phone_no_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizer_requests', function (Blueprint $table) {
            $table->string('phone_no')->nullable();
            $table->dateTime('phone_no_verified_at')->nullable();
        });
    }
};
