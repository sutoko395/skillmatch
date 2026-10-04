<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Malang', 'Surabaya'] as $city) {
            City::firstOrCreate(['name' => $city]);
        }

        $categories = [
            [
                'name' => 'Konser & Festival Musik',
                'description' => 'Event hiburan, konser musik, dan festival seni budaya.',
            ],
            [
                'name' => 'Seminar & Konferensi',
                'description' => 'Kegiatan akademik, workshop, dan seminar edukasi.',
            ],
            [
                'name' => 'Perlombaan & Olahraga',
                'description' => 'Kompetisi, turnamen olahraga, dan e-sports.',
            ],
            [
                'name' => 'Kegiatan Sosial & Komunitas',
                'description' => 'Aksi relawan, bakti sosial, dan pengabdian masyarakat.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $skills = [
            [
                'name' => 'Graphic Design',
                'category' => 'Creative & Media',
            ],
            [
                'name' => 'Video Editing',
                'category' => 'Creative & Media',
            ],
            [
                'name' => 'Public Speaking & MC',
                'category' => 'Communication',
            ],
            [
                'name' => 'Event Coordinator',
                'category' => 'Operations',
            ],
            [
                'name' => 'Backstage Manager',
                'category' => 'Operations',
            ],
            [
                'name' => 'Social Media Management',
                'category' => 'Marketing',
            ],
            [
                'name' => 'Web Development',
                'category' => 'IT & Tech',
            ],
            [
                'name' => 'First Aid & Medis',
                'category' => 'Healthcare',
            ],
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(
                ['name' => $skill['name']],
                $skill
            );
        }
    }
}
