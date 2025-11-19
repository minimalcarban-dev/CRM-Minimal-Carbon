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
            ['name' => 'Diamond'],
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
        ];

        foreach ($stoneTypes as $stoneType) {
            DB::table('stone_types')->updateOrInsert(
                ['name' => $stoneType['name']],
                $stoneType
            );
        }
    }
}
