<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->restrictOnDelete();
            // Keep calculation precision; round only when rendering to two decimals.
            $table->double('skill_score');
            $table->double('availability_score');
            $table->double('location_score');
            $table->double('total_score');
            $table->string('rule_version', 32);
            $table->json('breakdown_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
