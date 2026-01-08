<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Task;
use App\Models\PricingConfig;
use App\Models\UnitType;
use App\Observers\UserObserver;
use App\Observers\TaskObserver;
use App\Observers\PricingConfigObserver;
use App\Observers\UnitTypeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register User Observer for manager change detection
        User::observe(UserObserver::class);

        // Register Task Observer for activity logging
        Task::observe(TaskObserver::class);

        // Register Pricing and Unit Type Observers
        PricingConfig::observe(PricingConfigObserver::class);
        UnitType::observe(UnitTypeObserver::class);
    }
}
