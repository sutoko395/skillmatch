<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('uploader_id')->constrained('users')->restrictOnDelete();
            $table->string('document_type', 32); // 'cv', 'supporting'
            $table->string('disk', 32)->default('private');
            $table->string('storage_key', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256', 64);
            $table->string('status', 32)->default('ready'); // 'ready', 'purged', 'missing'
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('purged_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'document_type']);
            $table->index(['application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
