<?php
namespace Tests\Feature;

use App\Models\Event;
use Tests\TestCase;

class OrganizerExecutionPhaseTest extends TestCase
{
    public function test_phase_follows_time_without_persisting_lifecycle(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 15)->startOfDay());
        $event = new Event(['lifecycle_status' => 'upcoming', 'starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2)]);
        $this->assertSame('upcoming', $event->executionPhase());
        $this->travel(1)->hours();
        $this->assertSame('ongoing', $event->executionPhase());
        $this->travel(1)->hours();
        $this->assertSame('awaiting_completion', $event->executionPhase());
        $this->assertSame('upcoming', $event->lifecycle_status);
        foreach (['completed', 'cancelled'] as $terminal) {
            $event->lifecycle_status = $terminal;
            $this->assertSame($terminal, $event->executionPhase());
        }
        $this->travelBack();
    }
}
