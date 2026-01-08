<?php

namespace Database\Seeders;

use App\Models\Target;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TargetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesManagerRole = Role::where('slug', 'sales_manager')->first();
        $telecallerRole = Role::where('slug', 'telecaller')->first();

        if (!$salesManagerRole || !$telecallerRole) {
            $this->command->error('Roles not found. Please run RoleSeeder first.');
            return;
        }

        // Get current month start date
        $currentMonth = Carbon::now()->startOfMonth();

        // Set targets for all Sales Managers
        $salesManagers = User::where('role_id', $salesManagerRole->id)
            ->where('is_active', true)
            ->get();

        foreach ($salesManagers as $manager) {
            Target::updateOrCreate(
                [
                    'user_id' => $manager->id,
                    'target_month' => $currentMonth,
                ],
                [
                    'target_meetings' => 10,
                    'target_visits' => 10,
                    'target_closers' => 5,
                    'target_prospects_extract' => 0,
                    'target_prospects_verified' => 0,
                    'target_calls' => 0,
                ]
            );
        }

        // Set targets for all Telecallers
        // For telecallers: 200 daily calls = ~6000 monthly (200 * 30 days)
        // 5 daily prospects = ~150 monthly (5 * 30 days)
        $telecallers = User::where('role_id', $telecallerRole->id)
            ->where('is_active', true)
            ->get();

        foreach ($telecallers as $telecaller) {
            Target::updateOrCreate(
                [
                    'user_id' => $telecaller->id,
                    'target_month' => $currentMonth,
                ],
                [
                    'target_meetings' => 0,
                    'target_visits' => 0,
                    'target_closers' => 0,
                    'target_prospects_extract' => 150, // 5 daily * 30 days
                    'target_prospects_verified' => 0,
                    'target_calls' => 6000, // 200 daily * 30 days
                ]
            );
        }

        $this->command->info('Default targets created successfully!');
        $this->command->info('Sales Managers: 10 meetings, 10 visits, 5 closers (monthly)');
        $this->command->info('Telecallers: 6000 calls, 150 prospects (monthly = 200 calls & 5 prospects daily)');
    }
}
