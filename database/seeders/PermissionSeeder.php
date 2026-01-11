<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perms = [
            // Admins
            ['name' => 'View Admins', 'slug' => 'admins.view', 'description' => 'View admin list and details', 'category' => 'admins'],
            ['name' => 'Create Admins', 'slug' => 'admins.create', 'description' => 'Create new admin records', 'category' => 'admins'],
            ['name' => 'Edit Admins', 'slug' => 'admins.edit', 'description' => 'Edit existing admins', 'category' => 'admins'],
            ['name' => 'Delete Admins', 'slug' => 'admins.delete', 'description' => 'Delete admins', 'category' => 'admins'],
            ['name' => 'Assign Permissions', 'slug' => 'admins.assign_permissions', 'description' => 'Assign/remove permissions to/from admins', 'category' => 'admins'],

            // Chat
            ['name' => 'Access Chat', 'slug' => 'chat.access', 'description' => 'Access and use the chat system', 'category' => 'chat'],

            // Permission management
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'description' => 'View permission list and details', 'category' => 'permissions'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'description' => 'Create new permissions via admin UI', 'category' => 'permissions'],
            ['name' => 'Edit Permissions', 'slug' => 'permissions.edit', 'description' => 'Edit existing permissions via admin UI', 'category' => 'permissions'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'description' => 'Delete permissions via admin UI', 'category' => 'permissions'],

            // Orders
            ['name' => 'View Orders', 'slug' => 'orders.view', 'description' => 'View orders list and details', 'category' => 'orders'],
            ['name' => 'Create Orders', 'slug' => 'orders.create', 'description' => 'Create new orders via admin UI', 'category' => 'orders'],
            ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'description' => 'Edit orders via admin UI', 'category' => 'orders'],
            ['name' => 'Delete Orders', 'slug' => 'orders.delete', 'description' => 'Delete orders via admin UI', 'category' => 'orders'],

            // Attribute types
            ['name' => 'View Metal Types', 'slug' => 'metal_types.view', 'description' => 'View metal types', 'category' => 'metal_types'],
            ['name' => 'Create Metal Types', 'slug' => 'metal_types.create', 'description' => 'Create metal types', 'category' => 'metal_types'],
            ['name' => 'Edit Metal Types', 'slug' => 'metal_types.edit', 'description' => 'Edit metal types', 'category' => 'metal_types'],
            ['name' => 'Delete Metal Types', 'slug' => 'metal_types.delete', 'description' => 'Delete metal types', 'category' => 'metal_types'],

            ['name' => 'View Setting Types', 'slug' => 'setting_types.view', 'description' => 'View setting types', 'category' => 'setting_types'],
            ['name' => 'Create Setting Types', 'slug' => 'setting_types.create', 'description' => 'Create setting types', 'category' => 'setting_types'],
            ['name' => 'Edit Setting Types', 'slug' => 'setting_types.edit', 'description' => 'Edit setting types', 'category' => 'setting_types'],
            ['name' => 'Delete Setting Types', 'slug' => 'setting_types.delete', 'description' => 'Delete setting types', 'category' => 'setting_types'],

            ['name' => 'View Closure Types', 'slug' => 'closure_types.view', 'description' => 'View closure types', 'category' => 'closure_types'],
            ['name' => 'Create Closure Types', 'slug' => 'closure_types.create', 'description' => 'Create closure types', 'category' => 'closure_types'],
            ['name' => 'Edit Closure Types', 'slug' => 'closure_types.edit', 'description' => 'Edit closure types', 'category' => 'closure_types'],
            ['name' => 'Delete Closure Types', 'slug' => 'closure_types.delete', 'description' => 'Delete closure types', 'category' => 'closure_types'],

            ['name' => 'View Ring Sizes', 'slug' => 'ring_sizes.view', 'description' => 'View ring sizes', 'category' => 'ring_sizes'],
            ['name' => 'Create Ring Sizes', 'slug' => 'ring_sizes.create', 'description' => 'Create ring sizes', 'category' => 'ring_sizes'],
            ['name' => 'Edit Ring Sizes', 'slug' => 'ring_sizes.edit', 'description' => 'Edit ring sizes', 'category' => 'ring_sizes'],
            ['name' => 'Delete Ring Sizes', 'slug' => 'ring_sizes.delete', 'description' => 'Delete ring sizes', 'category' => 'ring_sizes'],

            ['name' => 'View Stone Types', 'slug' => 'stone_types.view', 'description' => 'View stone types', 'category' => 'stone_types'],
            ['name' => 'Create Stone Types', 'slug' => 'stone_types.create', 'description' => 'Create stone types', 'category' => 'stone_types'],
            ['name' => 'Edit Stone Types', 'slug' => 'stone_types.edit', 'description' => 'Edit stone types', 'category' => 'stone_types'],
            ['name' => 'Delete Stone Types', 'slug' => 'stone_types.delete', 'description' => 'Delete stone types', 'category' => 'stone_types'],

            ['name' => 'View Stone Shapes', 'slug' => 'stone_shapes.view', 'description' => 'View stone shapes', 'category' => 'stone_shapes'],
            ['name' => 'Create Stone Shapes', 'slug' => 'stone_shapes.create', 'description' => 'Create stone shapes', 'category' => 'stone_shapes'],
            ['name' => 'Edit Stone Shapes', 'slug' => 'stone_shapes.edit', 'description' => 'Edit stone shapes', 'category' => 'stone_shapes'],
            ['name' => 'Delete Stone Shapes', 'slug' => 'stone_shapes.delete', 'description' => 'Delete stone shapes', 'category' => 'stone_shapes'],

            ['name' => 'View Stone Colors', 'slug' => 'stone_colors.view', 'description' => 'View stone colors', 'category' => 'stone_colors'],
            ['name' => 'Create Stone Colors', 'slug' => 'stone_colors.create', 'description' => 'Create stone colors', 'category' => 'stone_colors'],
            ['name' => 'Edit Stone Colors', 'slug' => 'stone_colors.edit', 'description' => 'Edit stone colors', 'category' => 'stone_colors'],
            ['name' => 'Delete Stone Colors', 'slug' => 'stone_colors.delete', 'description' => 'Delete stone colors', 'category' => 'stone_colors'],

            // Diamond Clarities
            ['name' => 'View Diamond Clarities', 'slug' => 'diamond_clarities.view', 'description' => 'View diamond clarities', 'category' => 'diamond_clarities'],
            ['name' => 'Create Diamond Clarities', 'slug' => 'diamond_clarities.create', 'description' => 'Create diamond clarities', 'category' => 'diamond_clarities'],
            ['name' => 'Edit Diamond Clarities', 'slug' => 'diamond_clarities.edit', 'description' => 'Edit diamond clarities', 'category' => 'diamond_clarities'],
            ['name' => 'Delete Diamond Clarities', 'slug' => 'diamond_clarities.delete', 'description' => 'Delete diamond clarities', 'category' => 'diamond_clarities'],

            // Diamond Cuts
            ['name' => 'View Diamond Cuts', 'slug' => 'diamond_cuts.view', 'description' => 'View diamond cuts', 'category' => 'diamond_cuts'],
            ['name' => 'Create Diamond Cuts', 'slug' => 'diamond_cuts.create', 'description' => 'Create diamond cuts', 'category' => 'diamond_cuts'],
            ['name' => 'Edit Diamond Cuts', 'slug' => 'diamond_cuts.edit', 'description' => 'Edit diamond cuts', 'category' => 'diamond_cuts'],
            ['name' => 'Delete Diamond Cuts', 'slug' => 'diamond_cuts.delete', 'description' => 'Delete diamond cuts', 'category' => 'diamond_cuts'],

            // Diamonds
            ['name' => 'View Diamonds', 'slug' => 'diamonds.view', 'description' => 'View diamonds list and details', 'category' => 'diamonds'],
            ['name' => 'Create Diamonds', 'slug' => 'diamonds.create', 'description' => 'Create diamonds', 'category' => 'diamonds'],
            ['name' => 'Edit Diamonds', 'slug' => 'diamonds.edit', 'description' => 'Edit diamonds', 'category' => 'diamonds'],
            ['name' => 'Delete Diamonds', 'slug' => 'diamonds.delete', 'description' => 'Delete diamonds', 'category' => 'diamonds'],
            ['name' => 'Assign Diamonds', 'slug' => 'diamonds.assign', 'description' => 'Assign diamonds to admins', 'category' => 'diamonds'],
            ['name' => 'View Diamond Pricing', 'slug' => 'diamonds.view_pricing', 'description' => 'View diamond pricing details (per_ct, purchase_price, margin)', 'category' => 'diamonds'],

            // Companies
            ['name' => 'View Companies', 'slug' => 'companies.view', 'description' => 'View companies list and details', 'category' => 'companies'],
            ['name' => 'Create Companies', 'slug' => 'companies.create', 'description' => 'Create new companies', 'category' => 'companies'],
            ['name' => 'Edit Companies', 'slug' => 'companies.edit', 'description' => 'Edit existing companies', 'category' => 'companies'],
            ['name' => 'Delete Companies', 'slug' => 'companies.delete', 'description' => 'Delete companies', 'category' => 'companies'],

            // Parties
            ['name' => 'View Parties', 'slug' => 'parties.view', 'description' => 'View parties list and details', 'category' => 'parties'],
            ['name' => 'Create Parties', 'slug' => 'parties.create', 'description' => 'Create new parties', 'category' => 'parties'],
            ['name' => 'Edit Parties', 'slug' => 'parties.edit', 'description' => 'Edit existing parties', 'category' => 'parties'],
            ['name' => 'Delete Parties', 'slug' => 'parties.delete', 'description' => 'Delete parties', 'category' => 'parties'],

            // Invoices
            ['name' => 'View Invoices', 'slug' => 'invoices.view', 'description' => 'View invoices list and details', 'category' => 'invoices'],
            ['name' => 'Create Invoices', 'slug' => 'invoices.create', 'description' => 'Create new invoices', 'category' => 'invoices'],
            ['name' => 'Edit Invoices', 'slug' => 'invoices.edit', 'description' => 'Edit existing invoices', 'category' => 'invoices'],
            ['name' => 'Delete Invoices', 'slug' => 'invoices.delete', 'description' => 'Delete invoices', 'category' => 'invoices'],
            ['name' => 'Download Invoice PDF', 'slug' => 'invoices.pdf', 'description' => 'Download invoice as PDF', 'category' => 'invoices'],

            // Meta Lead Inbox
            ['name' => 'Access Meta Lead Inbox', 'slug' => 'meta_leads.access', 'description' => 'Access Meta Lead Inbox module', 'category' => 'meta_leads'],
            ['name' => 'Meta Settings', 'slug' => 'meta_leads.settings', 'description' => 'Manage Meta/Facebook settings', 'category' => 'meta_leads'],

            // Leads Inbox
            ['name' => 'View Leads', 'slug' => 'leads.view', 'description' => 'View leads inbox and details', 'category' => 'leads'],
            ['name' => 'Create Leads', 'slug' => 'leads.create', 'description' => 'Create new leads', 'category' => 'leads'],
            ['name' => 'Edit Leads', 'slug' => 'leads.edit', 'description' => 'Edit lead details and status', 'category' => 'leads'],
            ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'description' => 'Delete leads', 'category' => 'leads'],
            ['name' => 'Assign Leads', 'slug' => 'leads.assign', 'description' => 'Assign leads to agents', 'category' => 'leads'],
            ['name' => 'Message Leads', 'slug' => 'leads.message', 'description' => 'Send messages to leads', 'category' => 'leads'],

            // Purchase Tracker
            ['name' => 'View Purchases', 'slug' => 'purchases.view', 'description' => 'View purchase list and details', 'category' => 'purchases'],
            ['name' => 'Create Purchases', 'slug' => 'purchases.create', 'description' => 'Create new purchases', 'category' => 'purchases'],
            ['name' => 'Edit Purchases', 'slug' => 'purchases.edit', 'description' => 'Edit existing purchases', 'category' => 'purchases'],
            ['name' => 'Delete Purchases', 'slug' => 'purchases.delete', 'description' => 'Delete purchases', 'category' => 'purchases'],

            // Office Expenses
            ['name' => 'View Expenses', 'slug' => 'expenses.view', 'description' => 'View expense list and details', 'category' => 'expenses'],
            ['name' => 'Create Expenses', 'slug' => 'expenses.create', 'description' => 'Create new expenses', 'category' => 'expenses'],
            ['name' => 'Edit Expenses', 'slug' => 'expenses.edit', 'description' => 'Edit existing expenses', 'category' => 'expenses'],
            ['name' => 'Delete Expenses', 'slug' => 'expenses.delete', 'description' => 'Delete expenses', 'category' => 'expenses'],
            ['name' => 'View Expense Reports', 'slug' => 'expenses.reports', 'description' => 'View monthly and annual expense reports', 'category' => 'expenses'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
