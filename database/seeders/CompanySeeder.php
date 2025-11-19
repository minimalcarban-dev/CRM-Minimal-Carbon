<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanySeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run(): void
        {
            $now = Carbon::now();
            $companies = [
                [
                    'name' => 'OMGems',
                    'email' => 'contact@omgems.com',
                    'phone' => '+91-9876543210',
                    'address' => 'Mumbai, India',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now
                ],
                [
                    'name' => 'Minimal Carbon',
                    'email' => 'info@minimalcarbon.com',
                    'phone' => '+91-9123456789',
                    'address' => 'Delhi, India',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now
                ],
                [
                    'name' => 'Shiva Gems',
                    'email' => 'sales@shivagems.com',
                    'phone' => '+91-8765432109',
                    'address' => 'Bangalore, India',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now
                ],
                [
                    'name' => 'Shreeji',
                    'email' => 'support@shreeji.com',
                    'phone' => '+91-7654321098',
                    'address' => 'Pune, India',
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now
                ],
            ];

            foreach ($companies as $company) {
                DB::table('companies')->updateOrInsert(
                    ['name' => $company['name']],
                    $company
                );
            }
        }
    }