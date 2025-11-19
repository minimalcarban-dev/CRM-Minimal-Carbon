<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\SuperAdminSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Idempotent test user seeding to avoid duplicate key on re-seed
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                // Provide a password to satisfy non-null constraint
                'password' => bcrypt('password')
            ]
        );

        // Create super admin for admin panel
        $this->call(SuperAdminSeeder::class);
        // Seed permissions
        $this->call(PermissionSeeder::class);
        // Grant chat access to admins
        $this->call(ChatPermissionSeeder::class);
        // Seed default chat channels and attach admins
        $this->call(\Database\Seeders\ChatDefaultSeeder::class);
        
        // Seed attribute data
        $this->call(CompanySeeder::class);
        $this->call(MetalTypeSeeder::class);
        $this->call(SettingTypeSeeder::class);
        $this->call(ClosureTypeSeeder::class);
        $this->call(RingSizeSeeder::class);
        $this->call(StoneTypeSeeder::class);
        $this->call(StoneShapeSeeder::class);
        $this->call(StoneColorSeeder::class);
    }
}
