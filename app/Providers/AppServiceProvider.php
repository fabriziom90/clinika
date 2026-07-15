<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Observers\AppointmentObserver;
use App\Observers\PatientObserver;
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
        Appointment::observe(AppointmentObserver::class);
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
            'appointmentEntry' => fn () => session('appointmentEntry'),
            'currentRouteName' => fn () => Route::currentRouteName(),

        ]);
    }
}
