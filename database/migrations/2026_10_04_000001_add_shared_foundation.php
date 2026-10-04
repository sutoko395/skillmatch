<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        foreach (['skills', 'categories'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->boolean('is_active')->default(true));
        }
        Schema::table('volunteer_profiles', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained()->restrictOnDelete();
        });
        // Do not guess legacy city mappings, availability intervals or suspended-account intent.
        Schema::table('volunteer_skills', function (Blueprint $table) {
            $table->dropForeign(['skill_id']);
            $table->foreign('skill_id')->references('id')->on('skills')->restrictOnDelete();
        });
        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();
            $table->unique(['user_id', 'starts_at', 'ends_at']);
        });
        DB::statement('ALTER TABLE availability_slots ADD CONSTRAINT availability_positive CHECK (ends_at > starts_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
        Schema::table('volunteer_skills', function (Blueprint $table) {
            $table->dropForeign(['skill_id']);
            $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
        });
        Schema::table('volunteer_profiles', fn (Blueprint $table) => $table->dropConstrainedForeignId('city_id'));
        foreach (['skills', 'categories'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('is_active'));
        }
        Schema::dropIfExists('cities');
    }
};
