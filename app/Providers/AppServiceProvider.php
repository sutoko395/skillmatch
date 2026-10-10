<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Skill;
use App\Policies\AdminResourcePolicy;
use App\Policies\EventPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Contracts\AssessmentReadiness::class, \App\Services\OrganizerAssessmentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        foreach ([Skill::class, Category::class] as $model) {
            Gate::policy($model, AdminResourcePolicy::class);
        }
    }
}
