<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_outbox', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->string('channel', 20)->default('mail');
            $table->string('type', 60);
            $table->string('dedupe_key', 191)->unique();
            $table->json('data');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('available_at');
            $table->dateTime('claimed_at')->nullable();
            $table->uuid('claim_token')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->string('error_code', 60)->nullable();
            $table->timestamps();
            $table->index(['channel', 'status', 'available_at']);
            $table->index(['application_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_outbox');
    }
};
