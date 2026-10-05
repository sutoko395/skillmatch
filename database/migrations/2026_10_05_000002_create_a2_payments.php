<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->unsignedBigInteger('price');
            $t->unsignedInteger('max_positions');
            $t->unsignedInteger('max_applications');
            $t->unsignedInteger('max_registration_days');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained()->restrictOnDelete();
            $t->foreignId('package_id')->constrained()->restrictOnDelete();
            $t->string('order_ref', 64)->unique();
            $t->json('package_snapshot');
            $t->unsignedBigInteger('amount');
            $t->char('currency', 3)->default('IDR');
            $t->enum('status', ['pending', 'paid', 'failed', 'expired', 'cancelled', 'review_required'])->default('pending');
            $t->dateTime('paid_at')->nullable();
            $t->dateTime('activated_at')->nullable();
            $t->boolean('requires_follow_up')->default(false);
            $t->text('checkout_url')->nullable();
            $t->uuid('checkout_claim')->nullable();
            $t->dateTime('checkout_started_at')->nullable();
            $t->dateTime('gateway_checked_at')->nullable();
            $t->unsignedInteger('revision')->default(0);
            $t->timestamps();
        });
        Schema::create('payment_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->restrictOnDelete();
            $t->string('gateway_event_key', 64)->unique();
            $t->string('gateway_status', 40);
            $t->json('verified_summary');
            $t->dateTime('notified_at')->nullable();
            $t->timestamps();
        });
        Schema::create('event_entitlements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->unique()->constrained()->restrictOnDelete();
            $t->foreignId('order_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $t->json('package_snapshot');
            $t->unsignedInteger('max_positions');
            $t->unsignedInteger('max_applications');
            $t->unsignedInteger('max_registration_days');
            $t->unsignedInteger('submitted_applications')->default(0);
            $t->dateTime('activated_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_entitlements');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('packages');
    }
};
