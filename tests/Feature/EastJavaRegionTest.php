<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Province;
use App\Models\User;
use Database\Seeders\EastJavaRegionSeeder;
use Tests\DatabaseTestCase;

class EastJavaRegionTest extends DatabaseTestCase
{
    public function test_seeder_creates_all_east_java_regencies_and_cities_idempotently(): void
    {
        $this->seed(EastJavaRegionSeeder::class);
        $this->seed(EastJavaRegionSeeder::class);

        $province = Province::where('code', '35')->sole();
        $this->assertSame('Jawa Timur', $province->name);
        $this->assertTrue($province->is_active);
        $this->assertSame(38, City::where('province_id', $province->id)->count());
        $this->assertSame(29, City::where('province_id', $province->id)->where('type', 'kabupaten')->count());
        $this->assertSame(9, City::where('province_id', $province->id)->where('type', 'kota')->count());
        $this->assertSame(1, Province::where('code', '35')->count());

        foreach ([
            '3507' => 'Kabupaten Malang',
            '3515' => 'Kabupaten Sidoarjo',
            '3573' => 'Kota Malang',
            '3578' => 'Kota Surabaya',
            '3579' => 'Kota Batu',
        ] as $code => $name) {
            $this->assertDatabaseHas('cities', [
                'code' => $code,
                'name' => $name,
                'province_id' => $province->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_city_selects_group_available_regions_by_province(): void
    {
        $this->seed(EastJavaRegionSeeder::class);

        $this->get('/events')->assertOk()
            ->assertSee('Jawa Timur')
            ->assertSee('Kota Malang')
            ->assertSee('Kabupaten Malang');

        $volunteer = User::factory()->create(['role' => 'volunteer']);
        $this->actingAs($volunteer)->get('/volunteer/profile')->assertOk()
            ->assertSee('Kota/Kabupaten domisili')
            ->assertSee('Jawa Timur')
            ->assertSee('Kota Malang');
    }
}
