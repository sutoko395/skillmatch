<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('organizer_profiles', 'email')) {
            Schema::table('organizer_profiles', function (Blueprint $table) {
                $table->string('email')->nullable()->after('phone');
            });
        }

        if (!Schema::hasColumn('organizer_profiles', 'city')) {
            Schema::table('organizer_profiles', function (Blueprint $table) {
                $table->string('city')->nullable()->after('address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('organizer_profiles', 'email')) {
            Schema::table('organizer_profiles', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }

        if (Schema::hasColumn('organizer_profiles', 'city')) {
            Schema::table('organizer_profiles', function (Blueprint $table) {
                $table->dropColumn('city');
            });
        }
    }
};