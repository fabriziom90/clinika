<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Patient::class => \App\Policies\PatientPolicy::class,
        \App\Models\Doctor::class => \App\Policies\DoctorPolicy::class,
        \App\Models\Nurse::class => \App\Policies\NursePolicy::class,
        \App\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Specialty::class => \App\Policies\SpecialtyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
