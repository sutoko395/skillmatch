<?php

namespace Tests\Feature;

use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class OrganizerEventFilterTest extends DatabaseTestCase
{
    use A2Fixture;

    public function test_search_and_moderation_filters_are_combined_and_owner_scoped(): void
    {
        [$owner, $draft] = $this->fixture();
        [, $pending] = $this->fixture();
        [, $foreign] = $this->fixture();
        $draft->forceFill(['title' => 'Mancing Bersama'])->save();
        $pending->forceFill(['organizer_id' => $owner->id, 'title' => 'Mancing Danau', 'status' => 'pending'])->save();
        $foreign->forceFill(['title' => 'Mancing Asing', 'status' => 'pending'])->save();
        $this->actingAs($owner);

        $this->get(route('organizer.events.index', ['search' => '  Mancing  ', 'status' => 'pending', 'organizer_id' => $foreign->organizer_id]))
            ->assertOk()
            ->assertViewHas('search', 'Mancing')
            ->assertViewHas('events', fn ($events) => $events->total() === 1 && $events->first()->is($pending))
            ->assertSee('Mancing Danau')->assertDontSee('Mancing Asing')->assertDontSee('Mancing Bersama');

        foreach (['draft', 'pending', 'approved', 'rejected'] as $status) {
            $pending->forceFill(['status' => $status])->save();
            $this->get(route('organizer.events.index', ['status' => $status]))
                ->assertOk()
                ->assertViewHas('events', fn ($events) => $events->contains('id', $pending->id)
                    && $events->every(fn ($event) => $event->status === $status && $event->organizer_id === $owner->id));
        }
        $this->get(route('organizer.events.index'))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 2);
        $this->get(route('organizer.events.index', ['search' => 'Tidakada']))->assertOk()
            ->assertSee('Kegiatan tidak ditemukan')->assertSee('Reset');
    }

    public function test_search_treats_wildcards_literally_and_filters_survive_pagination(): void
    {
        [$owner, $event] = $this->fixture();
        $event->forceFill(['title' => 'Acara 100%_!', 'status' => 'approved'])->save();
        for ($i = 0; $i < 12; $i++) {
            $copy = $event->replicate();
            $copy->title = 'Acara Danau '.$i;
            $copy->save();
        }
        $this->actingAs($owner);
        $this->get(route('organizer.events.index', ['search' => '%_!']))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 1 && $events->first()->is($event));
        $this->get(route('organizer.events.index', ['search' => 'Acara', 'status' => 'approved']))
            ->assertOk()
            ->assertViewHas('events', function ($events) {
                parse_str(parse_url($events->nextPageUrl(), PHP_URL_QUERY), $query);
                return $events->total() === 13 && $events->count() === 12
                    && $query === ['search' => 'Acara', 'status' => 'approved', 'page' => '2'];
            });
        $this->get(route('organizer.events.index', ['search' => 'Acara', 'status' => 'approved', 'page' => 2]))
            ->assertOk()->assertViewHas('events', fn ($events) => $events->count() === 1);
    }

    public function test_invalid_filters_are_rejected(): void
    {
        [$owner] = $this->fixture();
        $this->actingAs($owner);
        foreach ([['status' => 'ongoing'], ['status' => ['draft']], ['search' => ['invalid']], ['search' => str_repeat('a', 256)]] as $filters) {
            $this->getJson(route('organizer.events.index', $filters))->assertUnprocessable();
        }
    }
}
