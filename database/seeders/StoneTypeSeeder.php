<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoneTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stoneTypes = [
            ['name' => 'Natural white diamond'],
            ['name' => 'Natural salt and pepper diamond'],
            ['name' => 'Natural black diamond'],
            ['name' => 'Natural fancy diamond'],
            ['name' => 'Natural brown diamond'],
            ['name' => 'Lab white diamond'],
            ['name' => 'Lab salt and pepper diamond'],
            ['name' => 'Lab fancy diamond'],
            ['name' => 'Lab champagne diamond'],
            ['name' => 'Ruby'],
            ['name' => 'Sapphire'],
            ['name' => 'Emerald'],
            ['name' => 'Topaz'],
            ['name' => 'Amethyst'],
            ['name' => 'Opal'],
            ['name' => 'Garnet'],
            ['name' => 'Pearl'],
            ['name' => 'Aquamarine'],
            ['name' => 'Citrine'],
            ['name' => 'Tourmaline'],
            ['name' => 'Tanzanite'],
            ['name' => 'Zircon'],
            ['name' => 'Spinel'],
            ['name' => 'Peridot'],
            ['name' => 'Kunzite'],
            ['name' => 'Iolite'],
            ['name' => 'Chrysoberyl'],
            ['name' => 'Alexandrite'],
            ['name' => 'Other'],
        ];

        foreach ($stoneTypes as $stoneType) {
            DB::table('stone_types')->updateOrInsert(
                ['name' => $stoneType['name']],
                $stoneType
            );
        }
    }
}
