<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\DatabaseTestCase;

class ActivityProfileSummaryTest extends DatabaseTestCase
{
    public function test_volunteer_summary_uses_own_profile_and_real_intervals(): void
    {
        $user = User::factory()->create();
        $user->volunteerProfile()->create(['city' => 'Kota lama sendiri', 'availability' => 'Weekend']);
        $user->availabilitySlots()->create(['starts_at' => '2026-11-01 02:00:00', 'ends_at' => '2026-11-01 05:00:00']);
        $other = User::factory()->create();
        $other->volunteerProfile()->create(['city' => 'Kota akun lain']);
        $this->actingAs($user)->get('/volunteer/aktivitas?user_id='.$other->id)
            ->assertOk()->assertViewIs('volunteer.dashboard')
            ->assertSee('Kota lama sendiri')->assertDontSee('Kota akun lain')
            ->assertSee('01/11/2026 09:00')->assertSee('01/11/2026 12:00')
            ->assertSee('Perlu dilengkapi')->assertSee('Weekend')
            ->assertDontSee('Event Tersedia');
        $this->get('/volunteer/dashboard')->assertRedirect('/volunteer/aktivitas');
    }

    public function test_organizer_summary_has_own_data_without_fake_metrics_or_links(): void
    {
        $user = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $user->organizerProfile()->create(['organization_name' => 'Organisasi sendiri', 'contact_person' => 'Kontak sendiri']);
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $other->organizerProfile()->create(['organization_name' => 'Organisasi rahasia lain']);
        $this->actingAs($user)->get('/organizer/aktivitas?user_id='.$other->id)
            ->assertOk()->assertViewIs('organizer.dashboard')
            ->assertSee('Organisasi sendiri')->assertSee('Kontak sendiri')
            ->assertDontSee('Organisasi rahasia lain')->assertSee('Terverifikasi')
            ->assertSee('Pengelolaan kegiatan belum tersedia')
            ->assertDontSee('Total Event')->assertDontSee('Total Pendaftar')
            ->assertDontSee('href="#"', false)->assertDontSee('Buat Event');
        $this->get('/organizer/dashboard')->assertRedirect('/organizer/aktivitas');
    }
}
