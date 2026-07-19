<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Customer;

class SuppliersAndCustomersSeeder extends Seeder
{
    public function run(): void
    {
        // ── Sample Suppliers ───────────────────────────────────────────
        $suppliers = [
            [
                'name'            => 'Tech Distributors Co.',
                'company_name'    => 'Tech Distributors Co. Ltd.',
                'email'           => 'sales@techdist.com',
                'phone'           => '+855-23-456-789',
                'address'         => '123 Business Street',
                'city'            => 'Phnom Penh',
                'country'         => 'Cambodia',
                'opening_balance' => 0,
                'is_active'       => true,
            ],
            [
                'name'            => 'Mobile World Supply',
                'company_name'    => 'Mobile World Supply Pte.',
                'email'           => 'orders@mwsupply.com',
                'phone'           => '+855-12-987-654',
                'address'         => '456 Commerce Ave',
                'city'            => 'Siem Reap',
                'country'         => 'Cambodia',
                'opening_balance' => 500.00,
                'is_active'       => true,
            ],
            [
                'name'            => 'Asia Phone Parts',
                'company_name'    => 'Asia Phone Parts Import',
                'email'           => 'info@asiaparts.com',
                'phone'           => '+86-135-0000-0000',
                'address'         => '789 Trade Zone',
                'city'            => 'Shenzhen',
                'country'         => 'China',
                'opening_balance' => 0,
                'is_active'       => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['email' => $supplier['email']], $supplier);
        }

        // ── Sample Customers ───────────────────────────────────────────
        $customers = [
            [
                'name'      => 'Walk-in Customer',
                'phone'     => null,
                'email'     => null,
                'is_active' => true,
                'notes'     => 'Default anonymous customer for POS',
            ],
            [
                'name'      => 'John Doe',
                'phone'     => '+855-12-345-678',
                'email'     => 'john.doe@example.com',
                'is_active' => true,
            ],
            [
                'name'      => 'Jane Smith',
                'phone'     => '+855-98-765-432',
                'email'     => 'jane.smith@example.com',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(['name' => $customer['name']], $customer);
        }

        $this->command->info('Suppliers and customers seeded successfully.');
    }
}
