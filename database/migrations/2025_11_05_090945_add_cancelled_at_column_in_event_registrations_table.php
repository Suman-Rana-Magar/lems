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
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dateTime('cancelled_at')->nullable();
            $table->enum('cancellation_reason', [
                'change_of_plans',
                'health_issues',
                'schedule_conflict',
                'event_postponed',
                'transportation_issue',
                'other'
            ])->nullable();
            $table->text('cancellation_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancellation_reason', 'cancellation_note']);
        });
    }
};
