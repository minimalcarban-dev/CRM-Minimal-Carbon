<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiamondCutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diamondCuts = [
            ['name' => 'Excellent'],
            ['name' => 'VG Cut'],
            ['name' => 'Good Cut'],
            ['name' => 'Rose Cut'],
            ['name' => 'Tambuli Cut'],
            ['name' => 'Brilliant Cut'],
            ['name' => 'Slice / Polki Cut'],
            ['name' => 'Cabochon Cut'],
            ['name' => 'Step Cut'],
            ['name' => 'Cross / Chokdi Cut'],
            ['name' => 'Old Mine Cut'],
            ['name' => 'Old European Cut'],
            ['name' => 'Portuguse Cut'],
            ['name' => 'French Cut'],
            ['name' => 'Portrait Cut'],
            ['name' => 'Poor Cut'],
            ['name' => 'Fair Cut'],
        ];

        foreach ($diamondCuts as $diamondCut) {
            DB::table('diamond_cuts')->updateOrInsert(
                ['name' => $diamondCut['name']],
                array_merge($diamondCut, ['is_active' => 1])
            );
        }
    }
}
