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
        // For telecallers: 200 calls/month and 15 verified prospects/month
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
                    'target_visits' => 8, // 8 visits per month (auto-divided to ~2/week)
                    'target_closers' => 0,
                    'target_prospects_extract' => 0, // Removed, use target_prospects_verified instead
                    'target_prospects_verified' => 150, // 150 verified prospects per month (auto-divided to ~5/day)
                    'target_calls' => 6000, // 6000 calls per month (auto-divided to ~200/day)
                ]
            );
        }

        $this->command->info('Default targets created successfully!');
        $this->command->info('Sales Managers: 10 meetings, 10 visits, 5 closers (monthly)');
        $this->command->info('Telecallers: 6000 calls/month (→ ~200/day), 150 prospects/month (→ ~5/day), 8 visits/month (→ ~2/week)');
    }
}
