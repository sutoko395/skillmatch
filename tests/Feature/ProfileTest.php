<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Skill;
use App\Models\User;
use App\Services\ProfileEligibilityService;
use Tests\DatabaseTestCase;

class ProfileTest extends DatabaseTestCase
{
    private function payload(): array
    {
        return [
            'name' => 'Volunteer Test', 'phone' => '0800000000', 'birth_date' => '2000-01-01',
            'gender' => 'female', 'address' => 'Alamat demo', 'bio' => 'Bio demo',
            'city_id' => City::create(['name' => 'Kota Test'])->id,
            'skills' => [['skill_id' => Skill::create(['name' => 'Skill Test'])->id, 'level' => 'advanced']],
            'availability_slots' => [['starts_at' => '2026-11-01T09:00', 'ends_at' => '2026-11-01T12:00']],
        ];
    }

    public function test_profile_round_trip_and_eligibility_use_utc(): void
    {
        $user = User::factory()->create();
        $data = $this->payload();
        $this->actingAs($user)->post('/volunteer/profile', $data)->assertSessionHasNoErrors()->assertRedirect('/volunteer/profile');
        $this->get('/volunteer/profile')->assertOk()->assertSee('2026-11-01T09:00')->assertSee('advanced')->assertSee('Alamat demo');
        $result = app(ProfileEligibilityService::class)->check($user->fresh());
        $this->assertTrue($result['complete']);
        $this->assertSame([], $result['missing_fields']);
        $this->assertSame('2026-11-01T02:00:00+00:00', $result['availability_slots'][0]['start']);
        $this->assertSame('advanced', $result['skills'][0]['level']);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $user->id, 'action' => 'volunteer.profile.updated']);
        $this->post('/volunteer/profile', $data)->assertSessionHasNoErrors();
        $this->assertSame(1, $user->availabilitySlots()->count());
        $this->assertSame(1, $user->volunteerSkills()->count());
    }

    public function test_legacy_labels_do_not_become_eligible_intervals(): void
    {
        $user = User::factory()->create();
        $user->volunteerProfile()->create(['city' => 'Tidak dikenal', 'availability' => 'Weekend']);
        $result = app(ProfileEligibilityService::class)->check($user);
        $this->assertFalse($result['complete']);
        $this->assertContains('availability_slots', $result['missing_fields']);
        $this->assertContains('city_id', $result['missing_fields']);
        $this->assertNull($result['city_id']);
        $this->actingAs($user)->get('/volunteer/profile')->assertOk()->assertSee('Weekend')->assertSee('Tidak dikenal');
    }

    public function test_invalid_profile_does_not_partially_save(): void
    {
        $user = User::factory()->create();
        $data = $this->payload();
        $data['skills'][] = $data['skills'][0];
        $data['availability_slots'][0]['ends_at'] = '2026-11-01T08:00';
        $this->actingAs($user)->post('/volunteer/profile', $data)->assertSessionHasErrors(['skills.0.skill_id', 'availability_slots.0.ends_at']);
        $this->assertNull($user->volunteerProfile()->first());
        $this->assertSame(0, $user->volunteerSkills()->count());
        array_pop($data['skills']);
        $data['availability_slots'][0]['ends_at'] = '2026-11-01T12:00';
        City::whereKey($data['city_id'])->update(['is_active' => false]);
        Skill::whereKey($data['skills'][0]['skill_id'])->update(['is_active' => false]);
        $this->post('/volunteer/profile', $data)->assertSessionHasErrors(['city_id', 'skills.0.skill_id']);
    }

    public function test_organizer_contact_save_preserves_both_statuses_and_requires_no_documents(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'organizer', 'organizer_status' => 'inactive']);
        $data = ['organization_name' => 'Demo', 'contact_person' => 'Kontak demo', 'phone' => '0800000000', 'email' => 'contact@example.test', 'address' => 'Alamat demo', 'city' => 'Malang', 'description' => 'Organisasi demo'];
        $this->actingAs($user)->post('/organizer/profile', $data)->assertSessionHasNoErrors();
        $this->assertSame('inactive', $user->fresh()->organizer_status);
        $this->assertTrue($user->fresh()->is_active);
        $this->get('/organizer/profile')->assertOk()->assertSee('Kontak demo');
        $user->update(['organizer_status' => 'active']);
        $data['phone'] = '0811111111';
        $this->post('/organizer/profile', $data)->assertSessionHasNoErrors();
        $data['organization_name'] = 'Identity changed';
        $this->post('/organizer/profile', $data)->assertSessionHasErrors('organization_name');
        $this->assertSame('Demo', $user->organizerProfile()->first()->organization_name);
        $this->assertSame('active', $user->fresh()->organizer_status);
    }
}
