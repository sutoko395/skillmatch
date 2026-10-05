<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Skill;
use App\Models\User;
use App\Services\AuditService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\DatabaseTestCase;

class FoundationIntegrityTest extends DatabaseTestCase
{
    public function test_demo_seed_is_repeatable_without_overwriting_changes(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('email', 'volunteer-a@example.test')->firstOrFail();
        $user->update(['name' => 'Edited demo', 'is_active' => false]);
        $count = User::count();
        $this->seed(DatabaseSeeder::class);
        $this->assertSame($count, User::count());
        $this->assertSame('Edited demo', $user->fresh()->name);
        $this->assertFalse($user->fresh()->is_active);
        $this->assertSame(4, $user->volunteerSkills()->count());
        $this->assertSame(1, $user->availabilitySlots()->count());
    }

    public function test_referenced_master_cannot_be_deleted_and_account_delete_is_closed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $volunteer = User::factory()->create();
        $skill = Skill::create(['name' => 'Referenced']);
        $volunteer->volunteerSkills()->create(['skill_id' => $skill->id, 'level' => 'beginner']);
        $this->actingAs($admin)->delete('/admin/master-data/skills/'.$skill->id)->assertSessionHasErrors('master');
        $this->delete('/admin/volunteers/'.$volunteer->id)->assertForbidden();
        $this->assertDatabaseHas('volunteer_skills', ['user_id' => $volunteer->id, 'skill_id' => $skill->id]);
        try {
            DB::table('skills')->where('id', $skill->id)->delete();
            $this->fail('FK must protect reference');
        } catch (QueryException $e) {
            $this->assertSame('23000', $e->errorInfo[0]);
        }
    }

    public function test_profile_write_rolls_back_when_audit_fails(): void
    {
        $user = User::factory()->create();
        $city = City::create(['name' => 'Atomic City']);
        $skill = Skill::create(['name' => 'Atomic Skill']);
        $this->mock(AuditService::class, fn ($mock) => $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure')));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($user)->post('/volunteer/profile', [
                'name' => 'Do not persist', 'phone' => '0800000000', 'city_id' => $city->id,
                'birth_date' => '2000-01-01', 'gender' => 'male', 'address' => 'Demo', 'bio' => 'Demo',
                'skills' => [['skill_id' => $skill->id, 'level' => 'expert']],
                'availability_slots' => [['starts_at' => '2026-11-01T09:00', 'ends_at' => '2026-11-01T10:00']],
            ]);
            $this->fail('Audit failure must propagate');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated audit failure', $e->getMessage());
        }
        $this->assertNotSame('Do not persist', $user->fresh()->name);
        $this->assertSame(0, $user->volunteerProfile()->count());
        $this->assertSame(0, $user->volunteerSkills()->count());
        $this->assertSame(0, $user->availabilitySlots()->count());
    }

    public function test_sensitive_profile_input_and_cross_role_admin_actions_are_denied(): void
    {
        $volunteer = User::factory()->create();
        $other = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($volunteer)->post('/volunteer/profile', ['user_id' => $other->id, 'role' => 'admin'])->assertSessionHasErrors(['user_id', 'role']);
        $this->post('/organizer/profile', [])->assertForbidden();
        $this->patch('/admin/organizers/'.$other->id.'/status', ['organizer_status' => 'active'])->assertForbidden();
        $this->get('/admin/organizers')->assertForbidden();
        $this->get('/admin/master-data/skills')->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->patch('/admin/organizers/'.$volunteer->id.'/status', ['organizer_status' => 'active'])->assertForbidden();
    }
}
