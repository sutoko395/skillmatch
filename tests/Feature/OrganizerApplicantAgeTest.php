<?php
namespace Tests\Feature;

use App\Models\User;
use Tests\DatabaseTestCase;
use Tests\Support\ParticipantAssessmentFixture;

class OrganizerApplicantAgeTest extends DatabaseTestCase
{
    use ParticipantAssessmentFixture;

    public function test_age_uses_wib_event_date_and_handles_missing_or_invalid_historical_dates(): void
    {
        $application = $this->participantApplication();
        $application->event->forceFill(['starts_at' => '2026-11-14 17:00:00'])->save();
        $this->actingAs(User::findOrFail($application->event->organizer_id));
        foreach (['2000-11-15' => 26, '2000-11-16' => 25, '2027-01-01' => null, '2000-02-30' => null, 'invalid' => null] as $birth => $expected) {
            $snapshot = $application->snapshot_json;
            $snapshot['profile']['birth_date'] = $birth;
            $application->forceFill(['snapshot_json' => $snapshot])->save();
            $this->get(route('organizer.applications.show', $application))->assertOk()->assertViewHas('ageAtEvent', $expected);
        }
        $snapshot = $application->snapshot_json;
        unset($snapshot['profile']['birth_date']);
        $application->forceFill(['snapshot_json' => $snapshot])->save();
        $application->volunteer->volunteerProfile()->create(['birth_date' => '2000-11-15']);
        $this->get(route('organizer.applications.show', $application))->assertOk()->assertViewHas('ageAtEvent', 26)
            ->assertViewHas('ageFromCurrentProfile', true)->assertSee('Berdasarkan tanggal lahir pada profil saat ini.');
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($other)->get(route('organizer.applications.show', $application))->assertForbidden();
    }
}
