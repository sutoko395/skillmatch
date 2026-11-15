<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class OrganizerEventMenuVisibilityTest extends DatabaseTestCase
{
    use A2Fixture;

    private function copy(Event $base, string $title, array $attributes = []): Event
    {
        $event = $base->replicate();
        $event->forceFill(array_replace([
            'title' => $title, 'status' => 'approved', 'publication_status' => 'published',
            'published_at' => now()->subDay(), 'lifecycle_status' => 'upcoming',
            'registration_opens_at' => now()->subDay(), 'registration_deadline' => now()->addDay(),
        ], $attributes))->save();

        return $event;
    }

    public function test_applicant_menu_only_includes_publication_history_and_combines_filters(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-15 03:00:00', 'UTC'));
        [$owner, $draft] = $this->fixture();
        $open = $this->copy($draft, 'Cari Acara 100%_!');
        $closed = $this->copy($draft, 'Cari Ditutup', ['registration_deadline' => now()]);
        $scheduled = $this->copy($draft, 'Belum Dibuka', ['registration_opens_at' => now()->addHour()]);
        $cancelled = $this->copy($draft, 'Cari Dibatalkan', ['lifecycle_status' => 'cancelled', 'publication_status' => 'unpublished']);
        $complete = $this->copy($draft, 'Cari Selesai', ['lifecycle_status' => 'completed']);
        $paused = $this->copy($draft, 'Cari Ditangguhkan', ['publication_status' => 'suspended']);
        $this->copy($draft, 'Belum Pernah Publikasi', ['published_at' => null, 'publication_status' => 'unpublished']);
        $foreign = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->copy($draft, 'Cari Milik Lain', ['organizer_id' => $foreign->id]);
        $this->actingAs($owner);
        $this->get(route('organizer.events.index'))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->contains('id', $draft->id));
        $this->get(route('organizer.applications.overview'))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 6 && ! $events->contains('id', $draft->id));
        foreach (['open' => [$open->id], 'closed' => [$closed->id], 'history' => [$cancelled->id, $complete->id, $paused->id]] as $phase => $ids) {
            $this->get(route('organizer.applications.overview', ['search' => '  Cari  ', 'recruitment' => $phase, 'organizer_id' => $foreign->id]))
                ->assertOk()->assertViewHas('search', 'Cari')
                ->assertViewHas('events', function ($events) use ($ids) {
                    $actual = $events->pluck('id')->sort()->values()->all();
                    sort($ids);
                    return $actual === $ids;
                });
        }
        $this->get(route('organizer.applications.overview', ['search' => '%_!']))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 1 && $events->first()->is($open));
        $this->get(route('organizer.applications.overview', ['search' => 'TidakAda']))->assertOk()->assertSee('Kegiatan tidak ditemukan')->assertSee('Reset');
        $this->assertSame('cancelled', $cancelled->fresh()->lifecycle_status);
        $this->assertSame('upcoming', $scheduled->fresh()->lifecycle_status);
    }

    public function test_applicant_filters_survive_pagination_and_invalid_input_is_rejected(): void
    {
        [$owner, $base] = $this->fixture();
        for ($i = 0; $i < 13; $i++) {
            $this->copy($base, 'Cari '.$i);
        }
        $this->actingAs($owner)->get(route('organizer.applications.overview', ['search' => 'Cari', 'recruitment' => 'open']))
            ->assertOk()->assertViewHas('events', function ($events) {
                parse_str(parse_url($events->nextPageUrl(), PHP_URL_QUERY), $query);
                return $events->total() === 13 && $query === ['search' => 'Cari', 'recruitment' => 'open', 'page' => '2'];
            });
        $this->get(route('organizer.applications.overview', ['search' => 'Cari', 'recruitment' => 'open', 'page' => 2]))
            ->assertOk()->assertViewHas('events', fn ($events) => $events->count() === 1);
        foreach ([['recruitment' => 'draft'], ['search' => ['bad']], ['search' => str_repeat('a', 256)]] as $filters) {
            $this->getJson(route('organizer.applications.overview', $filters))->assertUnprocessable();
        }
    }

    public function test_execution_appears_at_midnight_wib_not_start_time_and_retains_history(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-14 16:59:59', 'UTC'));
        [$owner, $draft] = $this->fixture();
        $noon = $this->copy($draft, 'Siang Ini', ['starts_at' => '2026-11-15 05:00:00', 'ends_at' => '2026-11-15 08:00:00']);
        $tomorrow = $this->copy($draft, 'Besok', ['starts_at' => '2026-11-15 17:00:00', 'ends_at' => '2026-11-15 18:00:00']);
        $ongoing = $this->copy($draft, 'Lintas Hari', ['starts_at' => '2026-11-14 01:00:00', 'ends_at' => '2026-11-16 01:00:00']);
        $ended = $this->copy($draft, 'Menunggu Selesai', ['starts_at' => '2026-11-13 01:00:00', 'ends_at' => '2026-11-13 09:00:00']);
        $cancelled = $this->copy($draft, 'Riwayat Batal', ['starts_at' => '2026-11-20 01:00:00', 'lifecycle_status' => 'cancelled', 'publication_status' => 'unpublished']);
        $this->copy($draft, 'Tidak Pernah Publikasi', ['published_at' => null, 'starts_at' => '2026-11-14 01:00:00']);
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->copy($draft, 'Milik Orang Lain', ['organizer_id' => $other->id, 'starts_at' => '2026-11-14 01:00:00']);
        $this->actingAs($owner)->get(route('organizer.execution.index'))->assertOk()
            ->assertViewHas('events', fn ($events) => ! $events->contains('id', $noon->id));
        $this->travelTo(CarbonImmutable::parse('2026-11-14 17:00:00', 'UTC'));
        $this->get(route('organizer.execution.index', ['organizer_id' => $other->id]))->assertOk()
            ->assertSee('Hari ini')->assertSee('Berlangsung')->assertSee('Riwayat')
            ->assertViewHas('events', fn ($events) => $events->total() === 4 && $events->contains('id', $noon->id) && ! $events->contains('id', $tomorrow->id))
            ->assertViewHas('executionGroups', fn ($groups) => $groups->get('today')->first()->is($noon)
                && $groups->get('ongoing')->first()->is($ongoing)
                && $groups->get('history')->contains('id', $ended->id) && $groups->get('history')->contains('id', $cancelled->id));
        $this->assertSame('upcoming', $noon->fresh()->lifecycle_status);
        $this->assertSame('upcoming', $ended->fresh()->lifecycle_status);
        $this->assertSame(0, $noon->applications()->count());
        $this->travelTo(CarbonImmutable::parse('2026-11-15 05:00:00', 'UTC'));
        $this->get(route('organizer.execution.index'))->assertOk()
            ->assertViewHas('executionGroups', fn ($groups) => $groups->get('ongoing')->contains('id', $noon->id));
    }
    public function test_execution_search_phase_filter_and_pagination_keep_visibility_boundaries(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-15 01:00:00', 'UTC'));
        [$owner, $draft] = $this->fixture();
        for ($i = 0; $i < 13; $i++) {
            $this->copy($draft, 'Cari Siang '.$i, ['starts_at' => '2026-11-15 05:00:00', 'ends_at' => '2026-11-15 08:00:00']);
        }
        $ongoing = $this->copy($draft, 'Cari Lintas 100%_!', ['starts_at' => '2026-11-15 00:00:00', 'ends_at' => '2026-11-15 03:00:00']);
        $ended = $this->copy($draft, 'Cari Menunggu', ['starts_at' => '2026-11-14 02:00:00', 'ends_at' => now()]);
        $cancelled = $this->copy($draft, 'Cari Batal', ['lifecycle_status' => 'cancelled', 'publication_status' => 'unpublished']);
        $this->copy($draft, 'Cari Besok', ['starts_at' => '2026-11-15 17:00:00', 'ends_at' => '2026-11-15 18:00:00']);
        $foreign = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->copy($draft, 'Cari Lain', ['organizer_id' => $foreign->id, 'starts_at' => '2026-11-15 00:00:00']);
        $this->actingAs($owner);
        foreach (['today' => 13, 'ongoing' => 1, 'history' => 2] as $phase => $total) {
            $this->get(route('organizer.execution.index', ['search' => '  Cari  ', 'phase' => $phase, 'organizer_id' => $foreign->id]))
                ->assertOk()->assertViewHas('search', 'Cari')
                ->assertDontSee('organizer-event-badges', false)
                ->assertViewHas('events', fn ($events) => $events->total() === $total
                    && $events->every(fn ($event) => $event->organizer_id === $owner->id))
                ->assertViewHas('executionGroups', fn ($groups) => $groups->keys()->all() === [$phase]);
        }
        $this->get(route('organizer.execution.index', ['search' => 'Cari', 'phase' => 'today']))
            ->assertOk()->assertViewHas('events', function ($events) {
                parse_str(parse_url($events->nextPageUrl(), PHP_URL_QUERY), $query);
                return $query === ['search' => 'Cari', 'phase' => 'today', 'page' => '2'];
            });
        $this->get(route('organizer.execution.index', ['search' => 'Cari', 'phase' => 'today', 'page' => 2]))
            ->assertOk()->assertViewHas('events', fn ($events) => $events->count() === 1);
        $this->get(route('organizer.execution.index', ['search' => '%_!']))->assertOk()
            ->assertViewHas('events', fn ($events) => $events->total() === 1 && $events->first()->is($ongoing));
        $this->get(route('organizer.execution.index', ['search' => 'TidakAda']))->assertOk()->assertSee('Kegiatan tidak ditemukan')->assertSee('Reset');
        $this->get(route('organizer.execution.index'))->assertOk()->assertViewHas('events', fn ($events) => $events->total() === 16);
        foreach ([['phase' => 'draft'], ['phase' => ['today']], ['search' => ['bad']], ['search' => str_repeat('a', 256)]] as $filters) {
            $this->getJson(route('organizer.execution.index', $filters))->assertUnprocessable();
        }
        $this->assertSame('upcoming', $ended->fresh()->lifecycle_status);
        $this->assertSame('cancelled', $cancelled->fresh()->lifecycle_status);
    }

}
