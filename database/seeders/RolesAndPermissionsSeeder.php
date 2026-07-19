<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Define all permissions grouped by area ──────────────────────
        $permissions = [
            // Dashboard
            ['name' => 'view_dashboard',        'display_name' => 'View Dashboard',          'group' => 'dashboard'],

            // Products
            ['name' => 'view_products',         'display_name' => 'View Products',            'group' => 'products'],
            ['name' => 'create_products',       'display_name' => 'Create Products',          'group' => 'products'],
            ['name' => 'edit_products',         'display_name' => 'Edit Products',            'group' => 'products'],
            ['name' => 'delete_products',       'display_name' => 'Delete Products',          'group' => 'products'],

            // Brands, Categories, Suppliers
            ['name' => 'manage_brands',         'display_name' => 'Manage Brands',            'group' => 'catalog'],
            ['name' => 'manage_categories',     'display_name' => 'Manage Categories',        'group' => 'catalog'],
            ['name' => 'manage_suppliers',      'display_name' => 'Manage Suppliers',         'group' => 'catalog'],

            // Inventory / Stock
            ['name' => 'view_stock',            'display_name' => 'View Stock',               'group' => 'inventory'],
            ['name' => 'adjust_stock',          'display_name' => 'Adjust Stock',             'group' => 'inventory'],

            // Purchases
            ['name' => 'view_purchases',        'display_name' => 'View Purchases',           'group' => 'purchases'],
            ['name' => 'create_purchases',      'display_name' => 'Create Purchases',         'group' => 'purchases'],
            ['name' => 'edit_purchases',        'display_name' => 'Edit Purchases',           'group' => 'purchases'],
            ['name' => 'delete_purchases',      'display_name' => 'Delete Purchases',         'group' => 'purchases'],

            // Sales / POS
            ['name' => 'view_sales',            'display_name' => 'View Sales',               'group' => 'sales'],
            ['name' => 'create_sales',          'display_name' => 'Create Sales (POS)',       'group' => 'sales'],
            ['name' => 'void_sales',            'display_name' => 'Void / Refund Sales',      'group' => 'sales'],

            // Customers
            ['name' => 'view_customers',        'display_name' => 'View Customers',           'group' => 'customers'],
            ['name' => 'manage_customers',      'display_name' => 'Manage Customers',         'group' => 'customers'],

            // Reports
            ['name' => 'view_reports',          'display_name' => 'View Reports',             'group' => 'reports'],
            ['name' => 'export_reports',        'display_name' => 'Export Reports',           'group' => 'reports'],

            // Claims / Warranty
            ['name' => 'view_claims',           'display_name' => 'View Claims',              'group' => 'claims'],
            ['name' => 'manage_claims',         'display_name' => 'Manage Claims',            'group' => 'claims'],

            // User & Role management
            ['name' => 'manage_admins',         'display_name' => 'Manage Admins',            'group' => 'admin'],
            ['name' => 'manage_roles',          'display_name' => 'Manage Roles & Permissions','group' => 'admin'],
            ['name' => 'manage_shops',          'display_name' => 'Manage Shops',             'group' => 'admin'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // ── Define roles and assign permissions ─────────────────────────
        $roles = [
            [
                'name'         => 'super_admin',
                'display_name' => 'Super Admin',
                'description'  => 'Full access to everything',
                'is_system'    => true,
                'permissions'  => Permission::all()->pluck('name')->toArray(), // All permissions
            ],
            [
                'name'         => 'shop_manager',
                'display_name' => 'Shop Manager',
                'description'  => 'Manages all operations within a shop',
                'is_system'    => true,
                'permissions'  => [
                    'view_dashboard', 'view_products', 'create_products', 'edit_products', 'delete_products',
                    'manage_brands', 'manage_categories', 'manage_suppliers',
                    'view_stock', 'adjust_stock',
                    'view_purchases', 'create_purchases', 'edit_purchases',
                    'view_sales', 'create_sales', 'void_sales',
                    'view_customers', 'manage_customers',
                    'view_reports', 'export_reports',
                    'view_claims', 'manage_claims',
                ],
            ],
            [
                'name'         => 'cashier',
                'display_name' => 'Cashier',
                'description'  => 'Can process sales and view products',
                'is_system'    => true,
                'permissions'  => [
                    'view_dashboard', 'view_products',
                    'view_stock',
                    'view_sales', 'create_sales',
                    'view_customers', 'manage_customers',
                    'view_claims',
                ],
            ],
            [
                'name'         => 'inventory_manager',
                'display_name' => 'Inventory Manager',
                'description'  => 'Manages stock, purchases, and product catalog',
                'is_system'    => false,
                'permissions'  => [
                    'view_dashboard',
                    'view_products', 'create_products', 'edit_products',
                    'manage_brands', 'manage_categories', 'manage_suppliers',
                    'view_stock', 'adjust_stock',
                    'view_purchases', 'create_purchases', 'edit_purchases',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissionNames = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(['name' => $roleData['name']], $roleData);
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
