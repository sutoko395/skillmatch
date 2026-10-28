<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status', 16)->default('unrecorded');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->text('note')->nullable();
            $table->text('correction_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('document_purges', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 32);
            $table->string('storage_key', 255);
            $table->string('reason', 40);
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();
            $table->unique(['disk', 'storage_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_purges');
        Schema::dropIfExists('attendances');
    }
};
