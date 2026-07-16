<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Observers\AppointmentObserver;
use App\Observers\PatientObserver;
use App\Providers\Contracts\EmailProviderInterface;
use App\Providers\Contracts\SmsProviderInterface;
use App\Providers\Contracts\WhatsappProviderInterface;
use App\Providers\Log\LogEmailProvider;
use App\Providers\Log\LogSmsProvider;
use App\Providers\Log\LogWhatsappProvider;
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
        $this->app->bind(
            EmailProviderInterface::class,
            function () {

                return match (config('reminders.email_provider')) {
                    'log' => app(LogEmailProvider::class),
                    default => throw new \Exception('Email provider non supportato'),
                };

            }
        );

        $this->app->bind(
            SmsProviderInterface::class,
            function () {

                return match (config('reminders.sms_provider')) {
                    'log' => app(LogSmsProvider::class),
                    default => throw new \Exception('SMS provider non supportato'),
                };

            }
        );

        $this->app->bind(
            WhatsappProviderInterface::class,
            function () {

                return match (config('reminders.whatsapp_provider')) {
                    'log' => app(LogWhatsappProvider::class),
                    default => throw new \Exception('WhatsApp provider non supportato'),
                };

            }
        );
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
