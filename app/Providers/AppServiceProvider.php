<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;

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
        // Horizon authorization
        Horizon::auth(function ($request) {
            return Gate::allows('viewHorizon', [$request->user()]);
        });

        Gate::define('viewHorizon', function ($user) {
            if (!$user) {
                return false;
            }
            return method_exists($user, 'isAdmin') ? $user->isAdmin() : true; // ajuste conforme sua regra
        });
    }
}
