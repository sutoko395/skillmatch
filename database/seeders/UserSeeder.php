<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin SkillMatch',
                'password' => '12345678',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'volunteer@gmail.com'],
            [
                'name' => 'Cahyo CS Volunteer',
                'password' => '12345678',
                'role' => 'volunteer',
            ]
        );

        User::updateOrCreate(
            ['email' => 'mberot@gmail.com'],
            [
                'name' => 'Event Mberot Malang',
                'password' => '12345678',
                'role' => 'organizer',
            ]
        );
    }
}