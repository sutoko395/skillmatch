<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('event_position_id')->constrained('event_positions')->restrictOnDelete();
            $table->foreignId('volunteer_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 32)->default('draft');
            $table->json('snapshot_json')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->foreignId('decision_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_reason')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->timestamps();

            $table->unique(['volunteer_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
