<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('organizer_verification_note')->nullable();
            $table->unsignedInteger('organizer_revision')->default(0);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('recruitment_repaired_at')->nullable();
            $table->foreignId('recruitment_repaired_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('recruitment_repair_reason')->nullable();
        });
        Schema::table('notification_outbox', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')->nullable()->change();
            $table->timestamp('read_at')->nullable();
            $table->index(['recipient_id', 'channel', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notification_outbox', function (Blueprint $table) {
            $table->dropIndex(['recipient_id', 'channel', 'read_at']);
            $table->dropColumn('read_at');
        });
        // Keep application_id nullable: database notifications have no application.
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recruitment_repaired_by');
            $table->dropColumn(['recruitment_repaired_at', 'recruitment_repair_reason']);
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['organizer_verification_note', 'organizer_revision']));
    }
};
