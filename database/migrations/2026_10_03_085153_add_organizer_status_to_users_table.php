<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('organizer_status', [
                'pending',
                'active',
                'inactive',
            ])->default('pending')->after('role');

            $table->index(['role', 'organizer_status']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['users_role_organizer_status_index']);
            $table->dropColumn('organizer_status');
        });
    }
};