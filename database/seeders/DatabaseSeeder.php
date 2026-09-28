<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Jalankan Master Data Seeder (Skill & Kategori)
        $this->call([
            MasterDataSeeder::class,
        ]);

        // 2. Akun Admin Default
        User::create([
            'name' => 'Admin cukurukuk SkillMatch',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        // 3. Akun Volunteer Dummy (Bonus untuk testing)
        User::create([
            'name' => 'Vahyo CS Volunteer',
            'email' => 'volunteer@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'volunteer',
        ]);

        // 4. Akun Organizer Dummy (Bonus untuk testing)
        User::create([
            'name' => 'Event Mberot Malang',
            'email' => 'mberot@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'organizer',
        ]);
    }
}