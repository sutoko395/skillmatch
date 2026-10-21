<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        if (! Schema::hasColumn('users', 'login_lockout_cycles')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('login_lockout_cycles')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'login_suspended_until',
                'login_lockout_cycles',
            ]);
        });
    }
};