<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Swap role display names: Sales Manager -> Senior Manager, Senior Manager -> Manager.
     * Slugs remain unchanged.
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('slug', 'sales_manager')
            ->update([
                'name' => 'Senior Manager',
                'description' => 'View all team leads, assign leads to sales executives, track team performance',
            ]);

        DB::table('roles')
            ->where('slug', 'senior_manager')
            ->update([
                'name' => 'Manager',
                'description' => 'Manager with extended permissions in the hierarchy',
            ]);
    }

    /**
     * Reverse the name swap.
     */
    public function down(): void
    {
        DB::table('roles')
            ->where('slug', 'sales_manager')
            ->update([
                'name' => 'Sales Manager',
                'description' => 'View all team leads, assign leads to sales executives, track team performance',
            ]);

        DB::table('roles')
            ->where('slug', 'senior_manager')
            ->update([
                'name' => 'Senior Manager',
                'description' => 'Senior level manager with extended permissions',
            ]);
    }
};
