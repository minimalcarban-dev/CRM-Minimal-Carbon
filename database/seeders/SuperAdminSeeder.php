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
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', env('ADMIN_EMAIL', 'superadmin@example.com'));
        $password = env('SUPER_ADMIN_PASSWORD', env('ADMIN_PASSWORD', 'Password123!'));

        // Create or update super admin
        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'phone_number' => '0000000000',
                'country_code' => '+91',
                'is_super' => true,
            ]
        );
    }
}
