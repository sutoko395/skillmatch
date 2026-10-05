<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $t) {
            $t->enum('publication_status', ['unpublished', 'published', 'suspended'])->default('unpublished')->index();
            $t->enum('lifecycle_status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            $t->foreignId('city_id')->nullable()->constrained()->restrictOnDelete();
            foreach (['starts_at', 'ends_at', 'published_at', 'completed_at', 'cancelled_at', 'submitted_at', 'registration_opens_at'] as $column) {
                $t->dateTime($column)->nullable();
            }
            $t->text('cancellation_reason')->nullable();
            $t->unsignedInteger('revision')->default(0);
            $t->json('package_snapshot')->nullable();
            $t->dropForeign(['organizer_id']);
            $t->foreign('organizer_id')->references('id')->on('users')->restrictOnDelete();
        });
        // Legacy date/time remain untouched. Canonical timestamps use explicit WIB -> UTC.
        DB::statement('UPDATE events SET starts_at = DATE_SUB(TIMESTAMP(start_date, start_time), INTERVAL 7 HOUR), ends_at = CASE WHEN end_date IS NOT NULL AND end_time IS NOT NULL THEN DATE_SUB(TIMESTAMP(end_date, end_time), INTERVAL 7 HOUR) ELSE NULL END');
        // Preserve original deadline for review; legacy values were entered as local wall time.
        Schema::table('events', fn (Blueprint $t) => $t->dateTime('legacy_registration_deadline')->nullable());
        DB::statement('UPDATE events SET legacy_registration_deadline = registration_deadline, registration_deadline = DATE_SUB(registration_deadline, INTERVAL 7 HOUR)');
        Schema::table('event_positions', function (Blueprint $t) {
            $t->boolean('required_full_availability')->default(false);
            $t->boolean('required_same_city')->default(false);
            $t->dropForeign(['event_id']);
            $t->foreign('event_id')->references('id')->on('events')->restrictOnDelete();
            $t->unique(['id', 'event_id']);
        });
        Schema::table('position_skills', fn (Blueprint $t) => $t->boolean('is_required')->default(true));
        Schema::table('position_requirements', function (Blueprint $t) {
            $t->string('kind', 30)->default('manual');
            $t->string('document_type', 50)->nullable();
        });
        Schema::create('position_schedules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_position_id')->constrained()->cascadeOnDelete();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at');
            $t->timestamps();
            $t->unique(['event_position_id', 'starts_at', 'ends_at'], 'position_schedule_unique');
        });
        DB::statement('ALTER TABLE position_schedules ADD CONSTRAINT position_schedule_positive CHECK (ends_at > starts_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('position_schedules');
        Schema::table('position_requirements', fn (Blueprint $t) => $t->dropColumn(['kind', 'document_type']));
        Schema::table('position_skills', fn (Blueprint $t) => $t->dropColumn('is_required'));
        Schema::table('event_positions', function (Blueprint $t) {
            $t->dropUnique(['id', 'event_id']);
            $t->dropColumn(['required_full_availability', 'required_same_city']);
            $t->dropForeign(['event_id']);
            $t->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
        DB::statement('UPDATE events SET registration_deadline = legacy_registration_deadline WHERE legacy_registration_deadline IS NOT NULL');
        Schema::table('events', function (Blueprint $t) {
            $t->dropConstrainedForeignId('city_id');
            $t->dropColumn(['publication_status', 'lifecycle_status', 'starts_at', 'ends_at', 'published_at', 'completed_at', 'cancelled_at', 'submitted_at', 'registration_opens_at', 'cancellation_reason', 'revision', 'package_snapshot', 'legacy_registration_deadline']);
            $t->dropForeign(['organizer_id']);
            $t->foreign('organizer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
