<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('location')->nullable(); // Malang, Surabaya, dll (untuk matching 20%)[cite: 1]
            $table->string('availability')->nullable(); // Weekday, Weekend, Full-time (untuk matching 30%)[cite: 1]
            $table->string('cv_file')->nullable();
            $table->string('portfolio_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_profiles');
    }
};