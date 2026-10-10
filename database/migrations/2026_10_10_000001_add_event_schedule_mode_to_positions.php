<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_positions', function (Blueprint $table) {
            // Existing positions keep their explicit schedules.
            $table->boolean('follows_event_schedule')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('event_positions', function (Blueprint $table) {
            $table->dropColumn('follows_event_schedule');
        });
    }
};
