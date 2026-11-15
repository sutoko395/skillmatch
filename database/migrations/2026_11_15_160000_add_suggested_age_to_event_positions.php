<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_positions', function (Blueprint $table) {
            $table->unsignedTinyInteger('suggested_min_age')->nullable();
            $table->unsignedTinyInteger('suggested_max_age')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('event_positions', fn (Blueprint $table) => $table->dropColumn(['suggested_min_age', 'suggested_max_age']));
    }
};
