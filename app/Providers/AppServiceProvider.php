<?php

namespace App\Providers;

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
        foreach ([\App\Models\Skill::class, \App\Models\Category::class, \App\Models\Event::class] as $model) {
            \Illuminate\Support\Facades\Gate::policy($model, \App\Policies\AdminResourcePolicy::class);
        }
    }
}
