<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('assessment_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('status', 20)->default('in_progress');
            $table->dateTime('started_at');
            $table->dateTime('expires_at');
            $table->dateTime('submitted_at')->nullable();
            $table->double('score')->nullable();
            $table->unsignedSmallInteger('correct')->nullable();
            $table->unsignedSmallInteger('total');
            $table->unsignedInteger('revision')->default(0);
            $table->timestamps();
            $table->index(['status', 'expires_at']);
        });
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained()->restrictOnDelete();
            $table->foreignId('assessment_question_id')->constrained()->restrictOnDelete();
            $table->foreignId('assessment_option_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['assessment_attempt_id', 'assessment_question_id'], 'assessment_answer_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('assessment_attempts');
    }
};
