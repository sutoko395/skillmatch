<?php

namespace Tests\Feature;

use App\Services\EventPublicationService;
use Carbon\CarbonImmutable;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class HomeTest extends DatabaseTestCase
{
    use A2Fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2027-01-01 00:00:00', 'UTC'));
    }

    public function test_home_explains_platform_and_handles_no_open_events(): void
    {
        $this->get('/')->assertOk()->assertViewHas('events', fn ($events) => $events->isEmpty())
            ->assertSee('Peluang berikutnya sedang dipersiapkan')->assertSee('UNTUK VOLUNTEER')
            ->assertSee('UNTUK ORGANIZER')->assertSee('masih dalam pengembangan')
            ->assertSee(route('events.index'))->assertSee('name="search"', false);
    }

    public function test_home_only_lists_public_events_with_registration_capacity(): void
    {
        $this->assessmentReady();
        [$owner, $event] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);
        $event = $event->fresh();
        $this->get('/')->assertOk()->assertSee($event->title)
            ->assertViewHas('events', fn ($events) => $events->contains('id', $event->id));
        foreach ([
            ['status' => 'draft'], ['publication_status' => 'suspended'],
            ['lifecycle_status' => 'cancelled'], ['registration_deadline' => now()->subMinute()],
            ['registration_opens_at' => now()->addDay()], ['ends_at' => now()->subMinute()],
        ] as $changes) {
            $original = $event->getAttributes();
            $event->forceFill($changes)->save();
            $this->get('/')->assertOk()->assertDontSee($event->title);
            $event->setRawAttributes($original)->save();
            $this->get('/')->assertOk()->assertSee($event->title);
        }
        $event->entitlement->forceFill(['submitted_applications' => 1])->save();
        $this->get('/')->assertOk()->assertDontSee($event->title);
        $event->entitlement->forceFill(['submitted_applications' => 0])->save();
        $owner->forceFill(['is_active' => false])->save();
        $this->get('/')->assertOk()->assertDontSee($event->title);
    }

    public function test_home_limits_event_preview_and_orders_by_event_start(): void
    {
        $this->assessmentReady();
        $ids = [];
        for ($i = 0; $i < 7; $i++) {
            [$owner, $event] = $this->fixture();
            $event->forceFill(['status' => 'approved'])->save();
            app(EventPublicationService::class)->publish($event, $owner);
            $ids[] = $event->id;
        }
        $this->get('/')->assertOk()->assertViewHas('events', fn ($events) => $events->pluck('id')->all() === array_slice($ids, 0, 6));
    }
}
