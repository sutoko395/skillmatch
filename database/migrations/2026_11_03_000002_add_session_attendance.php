<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('uses_sessions')->default(false);
            $table->json('legacy_record')->nullable();
        });
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->restrictOnDelete();
            $table->char('session_key', 64);
            $table->date('session_date');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 16)->default('unrecorded');
            $table->unsignedInteger('revision')->default(0);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->text('note')->nullable();
            $table->text('correction_reason')->nullable();
            $table->timestamps();
            $table->unique(['attendance_id', 'session_key']);
            $table->index(['session_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
        Schema::table('attendances', fn (Blueprint $table) => $table->dropColumn(['uses_sessions', 'legacy_record']));
    }
};
