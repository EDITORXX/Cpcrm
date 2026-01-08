<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'System Owner - Full system access',
                'is_active' => true,
            ],
            [
                'name' => 'CRM',
                'slug' => 'crm',
                'description' => 'Operations Manager - View all leads, assign leads, manage site visits',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Manager',
                'slug' => 'sales_manager',
                'description' => 'View all team leads, assign leads to sales executives, track team performance',
                'is_active' => true,
            ],
            [
                'name' => 'Sales Executive',
                'slug' => 'sales_executive',
                'description' => 'View assigned leads, update lead status, create site visits, manage telecallers',
                'is_active' => true,
            ],
            [
                'name' => 'Telecaller',
                'slug' => 'telecaller',
                'description' => 'View assigned leads only, update call status, add call remarks',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
