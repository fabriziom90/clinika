<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\CentralUser;
use App\Models\Clinic;
use App\Models\ClinicRoom;
use App\Models\ConsentType;
use App\Models\ConsentVersion;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\InventoryDrug;
use App\Models\InventoryProduct;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MedicalAttachment;
use App\Models\MedicalEntry;
use App\Models\MedicalEntryVersion;
use App\Models\MedicalRecord;
use App\Models\Nationality;
use App\Models\Patient;
use App\Models\PatientConsent;
use App\Models\PatientHealthHistory;
use App\Models\Permission;
use App\Models\Prescription;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\ReminderType;
use App\Models\ReminderTypePreference;
use App\Models\Role;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ModelConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', env('DB_CONNECTION', 'mysql'));

        if (app()->bound('currentClinic')) {
            app()->forgetInstance('currentClinic');
        }

        Route::middleware('tenant')->get('/__test-model-connections', function () {
            $models = [
                'appointment' => Appointment::class,
                'appointment_reminder' => AppointmentReminder::class,
                'clinic_room' => ClinicRoom::class,
                'consent_type' => ConsentType::class,
                'consent_version' => ConsentVersion::class,
                'doctor' => Doctor::class,
                'drug' => Drug::class,
                'inventory_drug' => InventoryDrug::class,
                'inventory_product' => InventoryProduct::class,
                'invoice' => Invoice::class,
                'invoice_item' => InvoiceItem::class,
                'medical_attachment' => MedicalAttachment::class,
                'medical_entry' => MedicalEntry::class,
                'medical_entry_version' => MedicalEntryVersion::class,
                'medical_record' => MedicalRecord::class,
                'nationality' => Nationality::class,
                'patient' => Patient::class,
                'patient_consent' => PatientConsent::class,
                'patient_health_history' => PatientHealthHistory::class,
                'prescription' => Prescription::class,
                'price_list' => PriceList::class,
                'product' => Product::class,
                'reminder_type' => ReminderType::class,
                'reminder_type_preference' => ReminderTypePreference::class,
                'service' => Service::class,
                'specialty' => Specialty::class,
                'user' => User::class,
                'role' => Role::class,
                'permission' => Permission::class,
            ];

            $connections = [];

            foreach ($models as $name => $model) {
                $connections[$name] = (new $model)->getConnection()->getName();
            }

            return response()->json($connections);
        });
    }

    public function test_central_models_use_central_connection(): void
    {
        $this->assertSame('central', (new Clinic)->getConnectionName());
        $this->assertSame('central', (new CentralUser)->getConnectionName());
    }

    public function test_tenant_models_use_tenant_connection(): void
    {
        $this->assertSame('tenant', (new User)->getConnectionName());
        $this->assertSame('tenant', (new Role)->getConnectionName());
        $this->assertSame('tenant', (new Permission)->getConnectionName());
    }

    public function test_models_without_explicit_connection_use_tenant_connection_in_tenant_context(): void
    {
        $slug = 'models-'.fake()->uuid();

        $clinic = Clinic::factory()->create([
            'slug' => $slug,
            'active' => true,
            'database' => 'clinika_test_middleware',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
        ]);

        $response = $this->get("http://{$slug}.clinika.test/__test-model-connections");

        $response->assertOk();

        $expectedModels = [
            'appointment',
            'appointment_reminder',
            'clinic_room',
            'consent_type',
            'consent_version',
            'doctor',
            'drug',
            'inventory_drug',
            'inventory_product',
            'invoice',
            'invoice_item',
            'medical_attachment',
            'medical_entry',
            'medical_entry_version',
            'medical_record',
            'nationality',
            'patient',
            'patient_consent',
            'patient_health_history',
            'prescription',
            'price_list',
            'product',
            'reminder_type',
            'reminder_type_preference',
            'service',
            'specialty',
            'user',
            'role',
            'permission',
        ];

        foreach ($expectedModels as $model) {
            $this->assertSame(
                'tenant',
                $response->json($model),
                "Il model {$model} non sta usando la connessione tenant."
            );
        }
    }
}
