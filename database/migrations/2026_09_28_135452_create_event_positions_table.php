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
        Schema::create('event_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('position_name');
            $table->integer('quota');
            $table->foreignId('required_skill_id')->nullable()->constrained('skills')->onDelete('set null');
            $table->enum('min_skill_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->text('qualifications')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_positions');
    }
};
