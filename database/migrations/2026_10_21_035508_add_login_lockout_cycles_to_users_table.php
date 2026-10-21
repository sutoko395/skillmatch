<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'login_suspended_until')) {
                $table->timestamp('login_suspended_until')->nullable();
            }

            if (! Schema::hasColumn('users', 'login_lockout_cycles')) {
                $table->unsignedTinyInteger('login_lockout_cycles')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('users', 'login_suspended_until')) {
                $columns[] = 'login_suspended_until';
            }

            if (Schema::hasColumn('users', 'login_lockout_cycles')) {
                $columns[] = 'login_lockout_cycles';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
