<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoneShapeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stoneShapes = [
            ['name' => 'Round'],
            ['name' => 'Princess'],
            ['name' => 'Emerald'],
            ['name' => 'Asscher'],
            ['name' => 'Cushion'],
            ['name' => 'Radiant'],
            ['name' => 'Oval'],
            ['name' => 'Marquise'],
            ['name' => 'Pear'],
            ['name' => 'Heart'],
            ['name' => 'Brilliant'],
            ['name' => 'Trillion'],
        ];

        foreach ($stoneShapes as $stoneShape) {
            DB::table('stone_shapes')->updateOrInsert(
                ['name' => $stoneShape['name']],
                $stoneShape
            );
        }
    }
}
