<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Patient::observe(PatientObserver::class);
        Inertia::share([
            // Condivide la chiave 'toast' (se presente nella sessione)
            'toast' => fn () => session('toast'),
            // (opzionale) condividi eventuali flash legacy:
            'flash' => function () {
                return [
                    'service' => session('service'),
                    'toast' => session('toast'),
                ];
            },
            'currentRouteName' => fn () => Route::currentRouteName(),

        ]);
    }
}
