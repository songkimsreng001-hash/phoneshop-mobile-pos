<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SuperAdmin::updateOrCreate(
            ['email' => 'admin@dashboard.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123!'),
            ]
        );
    }
}
