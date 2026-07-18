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
        \App\Models\Drug::class => \App\Policies\DrugPolicy::class,
        \App\Models\Product::class => \App\Policies\ProductPolicy::class,
        \App\Models\ClinicRoom::class => \App\Policies\ClinicRoomPolicy::class,
        \App\Models\Specialty::class => \App\Policies\SpecialtyPolicy::class,
        \App\Models\InventoryProduct::class => \App\Policies\InventoryProductPolicy::class,
        \App\Models\InventoryDrug::class => \App\Policies\InventoryDrugPolicy::class,
        \App\Models\MedicalRecord::class => \App\Policies\MedicalRecordPolicy::class,
        \App\Models\MedicalEntry::class => \App\Policies\MedicalEntryPolicy::class,
        \App\Models\Invoice::class => \App\Policies\InvoicePolicy::class,
        \App\Models\ConsentType::class => \App\Policies\ConsentTypePolicy::class,
        \OwenIt\Auditing\Models\Audit::class => \App\Policies\AuditPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
