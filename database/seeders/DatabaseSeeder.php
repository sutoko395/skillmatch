<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo seeding is local/testing only.');
        }
        $this->call([
            MasterDataSeeder::class,
            UserSeeder::class,
            DefaultPackageSeeder::class,
        ]);
    }
}
