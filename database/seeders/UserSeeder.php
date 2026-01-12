<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get roles
        $crmRole = Role::where('slug', 'crm')->first();
        $telecallerRole = Role::where('slug', 'telecaller')->first();
        $salesManagerRole = Role::where('slug', 'sales_manager')->first();
        $salesHeadRole = Role::where('slug', 'sales_manager')->first(); // Sales Head uses sales_manager role

        if (!$crmRole || !$telecallerRole || !$salesManagerRole) {
            $this->command->error('Roles not found. Please run RoleSeeder first.');
            return;
        }

        // Create CRM User
        User::firstOrCreate(
            ['email' => 'crm@realtorcrm.com'],
            [
                'name' => 'CRM Manager',
                'password' => Hash::make('crm123'),
                'role_id' => $crmRole->id,
                'is_active' => true,
            ]
        );

        // Create Sales Head (Sales Manager role)
        $salesHead = User::firstOrCreate(
            ['email' => 'saleshead@realtorcrm.com'],
            [
                'name' => 'Sales Head',
                'password' => Hash::make('saleshead123'),
                'role_id' => $salesManagerRole->id,
                'is_active' => true,
            ]
        );

        // Create 2 Sales Managers (under Sales Head)
        $salesManager1 = User::firstOrCreate(
            ['email' => 'salesmanager1@realtorcrm.com'],
            [
                'name' => 'Sales Manager 1',
                'password' => Hash::make('sm123'),
                'role_id' => $salesManagerRole->id,
                'manager_id' => $salesHead->id,
                'is_active' => true,
            ]
        );

        $salesManager2 = User::firstOrCreate(
            ['email' => 'salesmanager2@realtorcrm.com'],
            [
                'name' => 'Sales Manager 2',
                'password' => Hash::make('sm123'),
                'role_id' => $salesManagerRole->id,
                'manager_id' => $salesHead->id,
                'is_active' => true,
            ]
        );

        // Create 4 Telecallers (under Sales Managers)
        // Use updateOrCreate to ensure manager_id is always correct even if user exists
        User::updateOrCreate(
            ['email' => 'telecaller1@realtorcrm.com'],
            [
                'name' => 'Telecaller 1',
                'password' => Hash::make('tc123'),
                'role_id' => $telecallerRole->id,
                'manager_id' => $salesManager1->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'telecaller2@realtorcrm.com'],
            [
                'name' => 'Telecaller 2',
                'password' => Hash::make('tc123'),
                'role_id' => $telecallerRole->id,
                'manager_id' => $salesManager1->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'telecaller3@realtorcrm.com'],
            [
                'name' => 'Telecaller 3',
                'password' => Hash::make('tc123'),
                'role_id' => $telecallerRole->id,
                'manager_id' => $salesManager2->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'telecaller4@realtorcrm.com'],
            [
                'name' => 'Telecaller 4',
                'password' => Hash::make('tc123'),
                'role_id' => $telecallerRole->id,
                'manager_id' => $salesManager2->id,
                'is_active' => true,
            ]
        );

        $this->command->info('Users created successfully!');
    }
}

