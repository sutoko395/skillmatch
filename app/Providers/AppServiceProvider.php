<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Skill;
use App\Policies\AdminResourcePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Skill::class, Category::class, Event::class] as $model) {
            Gate::policy($model, AdminResourcePolicy::class);
        }
    }
}
