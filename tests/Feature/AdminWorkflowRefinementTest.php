<?php
namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\EventService;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class AdminWorkflowRefinementTest extends DatabaseTestCase
{
    use A2Fixture;

    public function test_transaction_search_and_wib_date_boundaries_are_combined_with_status(): void
    {
        [$owner, $event, $position, $package] = $this->fixture(10000);
        $owner->organizerProfile()->create(['organization_name' => 'Komunitas Peduli']);
        $orders = collect();
        foreach (['2026-11-14 16:59:59', '2026-11-14 17:00:00', '2026-11-15 16:59:59', '2026-11-15 17:00:00'] as $i => $created) {
            $order = new Order;
            $order->forceFill(['event_id' => $event->id, 'package_id' => $package->id, 'order_ref' => 'SM-'.\Illuminate\Support\Str::uuid(),
                'package_snapshot' => $package->snapshot(), 'amount' => 10000, 'currency' => 'IDR',
                'status' => $i === 2 ? 'paid' : 'pending', 'created_at' => $created])->save();
            $orders->push($order);
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['Komunitas Peduli', $event->title, $owner->name, $owner->email, $orders[1]->order_ref] as $search) {
            $this->get(route('admin.orders.index', ['search' => $search, 'status' => 'pending', 'date_from' => '2026-11-15', 'date_to' => '2026-11-15']))
                ->assertOk()->assertViewHas('orders', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $orders[1]->id);
        }
        $this->get(route('admin.orders.index', ['search' => '%_']))->assertOk()->assertViewHas('orders', fn ($rows) => $rows->total() === 0);
        $this->getJson(route('admin.orders.index', ['date_from' => '2026-11-16', 'date_to' => '2026-11-15']))->assertUnprocessable()->assertJsonValidationErrors('date_to');
        $this->getJson(route('admin.orders.index', ['date_from' => 'not-a-date']))->assertUnprocessable()->assertJsonValidationErrors('date_from');
        $this->get(route('admin.orders.index', ['date_from' => '2026-11-15', 'date_to' => '2026-11-15']))->assertOk()
            ->assertViewHas('orders', fn ($rows) => $rows->total() === 2);
        $this->actingAs($owner)->get(route('admin.orders.index'))->assertForbidden();
    }

    public function test_rejected_event_note_and_decision_time_remain_visible_after_resubmission(): void
    {
        $this->assessmentReady();
        [$owner, $event] = $this->fixture();
        $admin = User::factory()->create(['role' => 'admin']);
        app(EventService::class)->submit($event, $owner);
        $this->actingAs($admin)->patch(route('admin.event-verification.reject', $event), ['verification_note' => 'Perjelas rincian jadwal volunteer'])->assertRedirect();
        $decided = $event->fresh()->verified_at;
        $this->get(route('admin.event-verification.index', ['status' => 'rejected']))->assertOk()->assertSee($event->title)
            ->assertViewHas('events', fn ($rows) => $rows->contains('id', $event->id));
        $this->get(route('admin.event-verification.show', $event))->assertOk()->assertSee('Keputusan sudah diberikan.');
        app(EventService::class)->submit($event->fresh(), $owner);
        $this->get(route('admin.event-verification.show', $event))->assertOk()->assertSee('Perjelas rincian jadwal volunteer')
            ->assertSee($decided->setTimezone('Asia/Jakarta')->format('d/m/Y H:i'))->assertSee('diperiksa kembali')
            ->assertSee('EO / Organisasi')->assertSee('Paket kegiatan')->assertSee('Jadwal kegiatan dan pendaftaran');
        $this->actingAs($owner)->get(route('admin.event-verification.show', $event))->assertForbidden();
    }

    public function test_dashboard_pending_shortcuts_and_package_explanation(): void
    {
        $pending = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'pending']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.dashboard'))->assertOk()
            ->assertSee(route('admin.organizers.index', ['status' => 'pending']))
            ->assertSee(route('admin.event-verification.index'));
        $this->get(route('admin.organizers.index', ['status' => 'pending']))->assertOk()
            ->assertViewHas('organizers', fn ($rows) => $rows->contains('id', $pending->id) && $rows->every(fn ($row) => $row->organizer_status === 'pending'));
        $this->get(route('admin.packages.index'))->assertOk()->assertSee('Hak dan manfaat dari pembelian sebelumnya tetap berlaku.');
    }
}
