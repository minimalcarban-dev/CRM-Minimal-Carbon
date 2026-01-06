<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiamondClaritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diamondClarities = [
            ['name' => 'IF'],
            ['name' => 'FL'],
            ['name' => 'VVS1'],
            ['name' => 'VVS2'],
            ['name' => 'VS1'],
            ['name' => 'VS2'],
            ['name' => 'SI1'],
            ['name' => 'SI2'],
            ['name' => 'I1'],
            ['name' => 'I2'],
            ['name' => 'I3'],
            ['name' => 'PK2 ( Lighter )'],
            ['name' => 'PK3 ( Darker )'],
        ];

        foreach ($diamondClarities as $diamondClarity) {
            DB::table('diamond_clarities')->updateOrInsert(
                ['name' => $diamondClarity['name']],
                array_merge($diamondClarity, ['is_active' => 1])
            );
        }
    }
}
