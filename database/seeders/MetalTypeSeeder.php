<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetalTypeSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run(): void
        {
            $metalTypes = [
                // Silver
                ['name' => '925 Silver'],
                ['name' => '935 Silver'],
                // White Gold
                ['name' => '10K White Gold'],
                ['name' => '14K White Gold'],
                ['name' => '18K White Gold'],
                // Yellow Gold
                ['name' => '10K Yellow Gold'],
                ['name' => '14K Yellow Gold'],
                ['name' => '18K Yellow Gold'],
                // Rose Gold
                ['name' => '10K Rose Gold'],
                ['name' => '14K Rose Gold'],
                ['name' => '18K Rose Gold'],
                // Platinum
                ['name' => 'Platinum'],
            ];

            foreach ($metalTypes as $metalType) {
                DB::table('metal_types')->updateOrInsert(
                    ['name' => $metalType['name']],
                    $metalType
                );
            }
        }
    }