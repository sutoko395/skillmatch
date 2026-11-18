<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->string('code', 10)->nullable()->unique()->after('name');
            $table->string('type', 16)->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('province_id');
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'type']);
        });

        Schema::dropIfExists('provinces');
    }
};
