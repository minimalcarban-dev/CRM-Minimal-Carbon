<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoneColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stoneColors = [
            ['name' => 'D'],
            ['name' => 'E'],
            ['name' => 'F'],
            ['name' => 'G'],
            ['name' => 'H'],
            ['name' => 'I'],
            ['name' => 'J'],
            ['name' => 'K'],
            ['name' => 'L'],
            ['name' => 'M'],
            ['name' => 'N'],
            ['name' => 'O'],
            ['name' => 'P'],
            ['name' => 'Q'],
            ['name' => 'R'],
            ['name' => 'S'],
            ['name' => 'T'],
            ['name' => 'U'],
            ['name' => 'V'],
            ['name' => 'W'],
            ['name' => 'X'],
            ['name' => 'Y'],
            ['name' => 'Z'],
        ];

        foreach ($stoneColors as $stoneColor) {
            DB::table('stone_colors')->updateOrInsert(
                ['name' => $stoneColor['name']],
                $stoneColor
            );
        }
    }
}
