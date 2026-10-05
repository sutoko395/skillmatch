<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo users are local/testing only.');
        }
        $fixtures = [
            ['admin', 'admin', 'active', true, true],
            ['organizer-a', 'organizer', 'active', true, true],
            ['organizer-b', 'organizer', 'active', true, true],
            ['organizer-pending', 'organizer', 'pending', true, true],
            ['organizer-inactive', 'organizer', 'inactive', true, true],
            ['organizer-suspended', 'organizer', 'active', false, true],
            ['organizer-unverified', 'organizer', 'pending', true, false],
            ['volunteer-a', 'volunteer', 'pending', true, true],
            ['volunteer-b', 'volunteer', 'pending', true, true],
            ['volunteer-c', 'volunteer', 'pending', true, true],
            ['volunteer-suspended', 'volunteer', 'pending', false, true],
            ['volunteer-unverified', 'volunteer', 'pending', true, false],
        ];
        foreach ($fixtures as [$key, $role, $status, $active, $verified]) {
            $user = User::firstOrCreate(['email' => $key.'@example.test'], [
                'name' => 'Demo '.ucwords(str_replace('-', ' ', $key)), 'password' => 'SkillMatch-local-2026!',
                'role' => $role, 'is_active' => $active, 'organizer_status' => $status,
            ]);
            if (! $user->wasRecentlyCreated) {
                continue;
            } // Never reset existing accounts/passwords or edits.
            if ($verified) {
                $user->markEmailAsVerified();
            }
            if ($role === 'organizer') {
                $user->organizerProfile()->create([
                    'organization_name' => 'Organisasi Demo '.$key, 'contact_person' => 'Kontak Demo',
                    'phone' => '0800000000', 'email' => $key.'@example.test', 'address' => 'Alamat demo',
                    'city' => 'Malang', 'description' => 'Fixture lokal, bukan organisasi nyata.',
                ]);
            }
            if ($role === 'volunteer') {
                $user->volunteerProfile()->create([
                    'phone' => '0800000000', 'birth_date' => '2000-01-01', 'gender' => 'female',
                    'city_id' => City::where('name', $key === 'volunteer-b' ? 'Surabaya' : 'Malang')->firstOrFail()->id,
                    'address' => 'Alamat demo', 'bio' => 'Fixture volunteer lokal.',
                ]);
                foreach (['Graphic Design', 'Video Editing', 'Public Speaking & MC', 'Event Coordinator'] as $i => $skill) {
                    $user->volunteerSkills()->create(['skill_id' => Skill::where('name', $skill)->firstOrFail()->id, 'level' => ['beginner', 'intermediate', 'advanced', 'expert'][$i]]);
                }
                $user->availabilitySlots()->create(['starts_at' => '2026-11-01 02:00:00', 'ends_at' => '2026-11-01 05:00:00']);
            }
        }
    }
}
