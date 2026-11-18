<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EastJavaRegionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $province = Province::where('code', '35')->orWhere('name', 'Jawa Timur')->first() ?? new Province;
            $province->fill(['code' => '35', 'name' => 'Jawa Timur', 'is_active' => true])->save();

            // Preserve IDs of legacy master rows that were seeded before provinces existed.
            foreach ($this->legacyAliases() as $legacyName => $code) {
                if (City::where('code', $code)->exists()) {
                    continue;
                }
                City::whereNull('code')->where('name', $legacyName)->update(['code' => $code]);
            }

            foreach ($this->regions() as $region) {
                $city = City::where('code', $region['code'])->orWhere('name', $region['name'])->first() ?? new City;
                $city->fill([
                    'province_id' => $province->id,
                    'name' => $region['name'],
                    'code' => $region['code'],
                    'type' => $region['type'],
                    'is_active' => true,
                ])->save();
            }

            // If both a legacy row and its canonical row exist, keep legacy history but hide it from new selections.
            foreach ($this->legacyAliases() as $legacyName => $code) {
                if (City::where('code', $code)->exists()) {
                    City::whereNull('code')->where('name', $legacyName)->update(['is_active' => false]);
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    private function legacyAliases(): array
    {
        return [
            'Malang' => '3573',
            'Surabaya' => '3578',
            'Sidoarjo' => '3515',
        ];
    }

    /**
     * @return array<int, array{code: string, name: string, type: string}>
     */
    public function regions(): array
    {
        return [
            ['code' => '3501', 'name' => 'Kabupaten Pacitan', 'type' => 'kabupaten'],
            ['code' => '3502', 'name' => 'Kabupaten Ponorogo', 'type' => 'kabupaten'],
            ['code' => '3503', 'name' => 'Kabupaten Trenggalek', 'type' => 'kabupaten'],
            ['code' => '3504', 'name' => 'Kabupaten Tulungagung', 'type' => 'kabupaten'],
            ['code' => '3505', 'name' => 'Kabupaten Blitar', 'type' => 'kabupaten'],
            ['code' => '3506', 'name' => 'Kabupaten Kediri', 'type' => 'kabupaten'],
            ['code' => '3507', 'name' => 'Kabupaten Malang', 'type' => 'kabupaten'],
            ['code' => '3508', 'name' => 'Kabupaten Lumajang', 'type' => 'kabupaten'],
            ['code' => '3509', 'name' => 'Kabupaten Jember', 'type' => 'kabupaten'],
            ['code' => '3510', 'name' => 'Kabupaten Banyuwangi', 'type' => 'kabupaten'],
            ['code' => '3511', 'name' => 'Kabupaten Bondowoso', 'type' => 'kabupaten'],
            ['code' => '3512', 'name' => 'Kabupaten Situbondo', 'type' => 'kabupaten'],
            ['code' => '3513', 'name' => 'Kabupaten Probolinggo', 'type' => 'kabupaten'],
            ['code' => '3514', 'name' => 'Kabupaten Pasuruan', 'type' => 'kabupaten'],
            ['code' => '3515', 'name' => 'Kabupaten Sidoarjo', 'type' => 'kabupaten'],
            ['code' => '3516', 'name' => 'Kabupaten Mojokerto', 'type' => 'kabupaten'],
            ['code' => '3517', 'name' => 'Kabupaten Jombang', 'type' => 'kabupaten'],
            ['code' => '3518', 'name' => 'Kabupaten Nganjuk', 'type' => 'kabupaten'],
            ['code' => '3519', 'name' => 'Kabupaten Madiun', 'type' => 'kabupaten'],
            ['code' => '3520', 'name' => 'Kabupaten Magetan', 'type' => 'kabupaten'],
            ['code' => '3521', 'name' => 'Kabupaten Ngawi', 'type' => 'kabupaten'],
            ['code' => '3522', 'name' => 'Kabupaten Bojonegoro', 'type' => 'kabupaten'],
            ['code' => '3523', 'name' => 'Kabupaten Tuban', 'type' => 'kabupaten'],
            ['code' => '3524', 'name' => 'Kabupaten Lamongan', 'type' => 'kabupaten'],
            ['code' => '3525', 'name' => 'Kabupaten Gresik', 'type' => 'kabupaten'],
            ['code' => '3526', 'name' => 'Kabupaten Bangkalan', 'type' => 'kabupaten'],
            ['code' => '3527', 'name' => 'Kabupaten Sampang', 'type' => 'kabupaten'],
            ['code' => '3528', 'name' => 'Kabupaten Pamekasan', 'type' => 'kabupaten'],
            ['code' => '3529', 'name' => 'Kabupaten Sumenep', 'type' => 'kabupaten'],
            ['code' => '3571', 'name' => 'Kota Kediri', 'type' => 'kota'],
            ['code' => '3572', 'name' => 'Kota Blitar', 'type' => 'kota'],
            ['code' => '3573', 'name' => 'Kota Malang', 'type' => 'kota'],
            ['code' => '3574', 'name' => 'Kota Probolinggo', 'type' => 'kota'],
            ['code' => '3575', 'name' => 'Kota Pasuruan', 'type' => 'kota'],
            ['code' => '3576', 'name' => 'Kota Mojokerto', 'type' => 'kota'],
            ['code' => '3577', 'name' => 'Kota Madiun', 'type' => 'kota'],
            ['code' => '3578', 'name' => 'Kota Surabaya', 'type' => 'kota'],
            ['code' => '3579', 'name' => 'Kota Batu', 'type' => 'kota'],
        ];
    }
}
