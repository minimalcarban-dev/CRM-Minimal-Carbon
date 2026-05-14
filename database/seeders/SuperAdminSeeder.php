<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     * This seeder only manages the core God Admin (ID 1).
     * Other admins should be managed via the Admin Panel UI.
     */
    public function run(): void
    {
        // God Admin setup
        $godEmail = config('auth.god_admin_email') ?? 'admin@omgems.com';

        Admin::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Ashish (God Admin)',
                'email' => $godEmail,
                'password' => Hash::make('20042004'),
                'is_super' => true,
                'phone_number' => '0000000000',
                'country_code' => '+91',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        echo "✅ SuperAdminSeeder: God Admin (ID 1) has been synchronized.\n";
    }
}