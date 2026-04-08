<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JewelleryStockPermissionsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perms = [
            ['name' => 'View Jewellery Stock', 'slug' => 'jewellery_stock.view', 'description' => 'View jewellery stock list and details', 'category' => 'jewellery_stock'],
            ['name' => 'Create Jewellery Stock', 'slug' => 'jewellery_stock.create', 'description' => 'Create new jewellery stock items', 'category' => 'jewellery_stock'],
            ['name' => 'Edit Jewellery Stock', 'slug' => 'jewellery_stock.edit', 'description' => 'Edit existing jewellery stock items', 'category' => 'jewellery_stock'],
            ['name' => 'Delete Jewellery Stock', 'slug' => 'jewellery_stock.delete', 'description' => 'Delete jewellery stock items', 'category' => 'jewellery_stock'],
            ['name' => 'View Jewellery Pricing', 'slug' => 'jewellery_stock.view_pricing', 'description' => 'View jewellery pricing details (purchase price and margin)', 'category' => 'jewellery_stock'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
