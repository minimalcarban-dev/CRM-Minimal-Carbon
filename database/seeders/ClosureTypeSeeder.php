<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClosureTypeSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run(): void
        {
            $closureTypes = [
                ['name' => 'Push Back'],
                ['name' => 'Screw Back'],
            ];

            foreach ($closureTypes as $closureType) {
                DB::table('closure_types')->updateOrInsert(
                    ['name' => $closureType['name']],
                    $closureType
                );
            }
        }
    }