<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->restrictOnDelete();
            $table->enum('minimum_level', [
                'beginner',
                'intermediate',
                'advanced',
                'expert',
            ])->default('beginner');
            $table->timestamps();

            $table->unique(['event_position_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_skills');
    }
};