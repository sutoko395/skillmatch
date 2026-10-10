<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_position_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->unsignedInteger('revision')->default(0);
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['event_position_id', 'version']);
        });
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order');
            $table->text('text')->nullable();
            $table->unique(['assessment_id', 'sort_order']);
        });
        Schema::create('assessment_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->char('label', 1);
            $table->text('text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unique(['assessment_question_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_options');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }
};
