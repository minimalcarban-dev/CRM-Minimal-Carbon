<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTypeSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run(): void
        {
            $settingTypes = [
                ['name' => 'Halo'],
                ['name' => 'Bazzle'],
                ['name' => 'Solitaire'],
                ['name' => 'Three Stone'],
                ['name' => 'Pave'],
                ['name' => 'Vintage'],
                ['name' => 'Cushion'],
                ['name' => 'Emerald'],
            ];

            foreach ($settingTypes as $settingType) {
                DB::table('setting_types')->updateOrInsert(
                    ['name' => $settingType['name']],
                    $settingType
                );
            }
        }
    }