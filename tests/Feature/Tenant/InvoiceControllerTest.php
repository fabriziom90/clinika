<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Nationality;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TenantTestCase;

class InvoiceControllerTest extends TenantTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'clinika_test_tenant',
            'username' => 'root',
            'password' => '',
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        Config::set('database.default', 'tenant');

        DB::purge('tenant');
        DB::reconnect('tenant');

        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake();
        Storage::fake();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'invoices.view',
            'invoices.create',
            'invoices.update',
            'invoices.delete',
            'invoices.change-status',
            'invoices.export',
        ] as $permission) {
            Permission::on('tenant')->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_user_without_view_permission_cannot_access_invoices(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get($this->url($clinic, '/admin/invoices'), $this->host($clinic))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_invoices(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.view');

        $this->actingAs($user)
            ->get($this->url($clinic, '/admin/invoices'), $this->host($clinic))
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_access_invoice_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $appointment = $this->createAppointment();

        $this->actingAs($user)
            ->get(
                $this->url($clinic, "/admin/invoices/create/{$appointment->getRouteKey()}"),
                $this->host($clinic)
            )
            ->assertForbidden();
    }

    public function test_user_with_create_permission_can_access_invoice_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.create');

        $appointment = $this->createAppointment();

        $this->actingAs($user)
            ->get(
                $this->url($clinic, "/admin/invoices/create/{$appointment->getRouteKey()}"),
                $this->host($clinic)
            )
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_create_invoice(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $appointment = $this->createAppointment();

        $this->actingAs($user)
            ->post(
                $this->url($clinic, '/admin/invoices'),
                $this->validInvoiceData($appointment),
                $this->host($clinic)
            )
            ->assertForbidden();
    }

    public function test_user_with_create_permission_can_create_invoice(): void
    {
        $clinic = $this->createClinic();
        $appointment = $this->createAppointment();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.create');

        Invoice::on('tenant')
            ->where('appointment_id', $appointment->id)
            ->delete();

        $data = $this->validInvoiceData($appointment);

        $response = $this->actingAs($user)
            ->post(
                $this->url($clinic, '/admin/invoices'),
                $data,
                $this->host($clinic)
            );

        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'deleted_at' => null,
        ], 'tenant');

        $invoice = Invoice::on('tenant')
            ->where('appointment_id', $appointment->id)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        $this->assertNotNull($invoice);
        $this->assertSame('Mario Rossi', $invoice->full_name);
        $this->assertSame(1, $invoice->invoiceItems()->count());
    }

    public function test_store_validation_rejects_invalid_invoice_data(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.create');

        $appointment = $this->createAppointment();

        $data = $this->validInvoiceData($appointment);
        $data['appointment_id'] = 999999999;
        $data['doctor_id'] = 999999999;
        $data['patient_id'] = 999999999;
        $data['date'] = 'invalid-date';
        $data['full_name'] = '';
        $data['subtotal'] = -1;
        $data['items'] = [];

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $this->actingAs($user)
            ->post(
                $this->url($clinic, '/admin/invoices'),
                $data,
                $this->host($clinic)
            );
    }

    public function test_user_without_update_permission_cannot_access_invoice_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->get(
                $this->url($clinic, "/admin/invoices/{$invoice->getRouteKey()}/edit"),
                $this->host($clinic)
            )
            ->assertForbidden();
    }

    public function test_user_with_update_permission_can_access_invoice_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.update');

        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->get(
                $this->url($clinic, "/admin/invoices/{$invoice->getRouteKey()}/edit"),
                $this->host($clinic)
            )
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_update_invoice(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $invoice = $this->createInvoice();

        $data = $this->validInvoiceData($invoice->appointment);
        $data['full_name'] = 'Nome modificato';

        $this->actingAs($user)
            ->put(
                $this->url($clinic, "/admin/invoices/{$invoice->getRouteKey()}"),
                $data,
                $this->host($clinic)
            )
            ->assertForbidden();

        $invoice->refresh();

        $this->assertNotSame('Nome modificato', $invoice->full_name);
    }

    public function test_user_with_update_permission_can_update_invoice(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.update');

        $invoice = $this->createInvoice();

        $data = $this->validInvoiceData($invoice->appointment);
        $data['full_name'] = 'Luigi Bianchi';
        $data['amount'] = 150;
        $data['total'] = 150;
        $data['items'][0]['unit_price'] = 150;
        $data['items'][0]['total'] = 150;

        $this->actingAs($user)
            ->put(
                $this->url($clinic, "/admin/invoices/{$invoice->getRouteKey()}"),
                $data,
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount' => 150,
        ], 'tenant');

        $this->assertSame('Luigi Bianchi', $invoice->fresh()->full_name);
        $this->assertSame(1, $invoice->fresh()->invoiceItems()->count());
    }

    public function test_update_validation_rejects_invalid_invoice_data(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.update');

        $invoice = $this->createInvoice();

        $data = $this->validInvoiceData($invoice->appointment);
        $data['appointment_id'] = 999999999;
        $data['doctor_id'] = 999999999;
        $data['patient_id'] = 999999999;
        $data['date'] = 'invalid-date';
        $data['full_name'] = '';
        $data['subtotal'] = -1;
        $data['items'] = [];

        $this->withoutExceptionHandling();
        $this->expectException(ValidationException::class);

        $this->actingAs($user)
            ->put(
                $this->url($clinic, "/admin/invoices/{$invoice->getRouteKey()}"),
                $data,
                $this->host($clinic)
            );
    }

    public function test_user_without_delete_permission_cannot_delete_invoice(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->delete(
                $this->url($clinic, "/admin/invoices/{$invoice->getRouteKey()}"),
                [],
                $this->host($clinic)
            )
            ->assertForbidden();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'deleted_at' => null,
        ], 'tenant');
    }

    public function test_user_with_delete_permission_can_delete_invoice(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.delete');

        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->delete(
                $this->url($clinic, "/admin/invoices/{$invoice->getRouteKey()}"),
                [],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertSoftDeleted('invoices', [
            'id' => $invoice->id,
        ], 'tenant');
    }

    public function test_user_without_change_status_permission_cannot_change_invoice_status(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                ['status' => 'issued'],
                $this->host($clinic)
            )
            ->assertForbidden();

        $this->assertSame('draft', $invoice->fresh()->status);
    }

    public function test_user_with_change_status_permission_can_change_invoice_status(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.change-status');

        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                ['status' => 'issued'],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertSame('issued', $invoice->fresh()->status);
    }

    public function test_invoice_status_cannot_skip_allowed_transition(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.change-status');

        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                ['status' => 'paid'],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertSame('draft', $invoice->fresh()->status);
    }

    public function test_user_with_change_status_permission_can_mark_issued_invoice_as_paid(): void
    {
        $this->withoutExceptionHandling();

        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.change-status');

        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                ['status' => 'issued'],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertSame('issued', $invoice->fresh()->status);

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                [
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'payment_date' => now()->toDateString(),
                    'paid_at' => now()->toDateString(),
                    'amount' => 100,
                    'notes' => 'Pagamento effettuato',
                ],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_paid_invoice_cannot_change_status(): void
    {
        $this->withoutExceptionHandling();

        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.change-status');

        $invoice = $this->createInvoice();

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                ['status' => 'issued'],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                [
                    'status' => 'paid',
                    'payment_method' => 'cash',
                    'payment_date' => now()->toDateString(),
                    'paid_at' => now()->toDateString(),
                    'amount' => 100,
                    'notes' => 'Pagamento effettuato',
                ],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);

        $this->actingAs($user)
            ->put(
                $this->invoiceStatusUrl($clinic, $invoice),
                ['status' => 'issued'],
                $this->host($clinic)
            )
            ->assertRedirect();

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_user_without_export_permission_cannot_export_invoices(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get(
                $this->url($clinic, '/admin/invoices/export'),
                $this->host($clinic)
            )
            ->assertForbidden();
    }

    public function test_user_with_export_permission_can_export_invoices_csv(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.export');

        $invoice = $this->createInvoice();

        $response = $this->actingAs($user)
            ->get(
                $this->url($clinic, '/admin/invoices/export'),
                $this->host($clinic)
            );

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('Numero;Data;Paziente;Medico', $content);
        $this->assertStringContainsString($invoice->number, $content);
        $this->assertStringContainsString('Mario Rossi', $content);
    }

    public function test_user_can_export_invoices_csv_with_applied_filters(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $this->grantPermissions($user, 'invoices.export');

        $invoice = $this->createInvoice();

        $response = $this->actingAs($user)
            ->get(
                $this->url($clinic, '/admin/invoices/export?status=draft&search=Mario'),
                $this->host($clinic)
            );

        $response->assertSuccessful();

        $content = $response->streamedContent();

        $this->assertStringContainsString($invoice->number, $content);

        $responseEmpty = $this->actingAs($user)
            ->get(
                $this->url($clinic, '/admin/invoices/export?status=paid'),
                $this->host($clinic)
            );

        $contentEmpty = $responseEmpty->streamedContent();

        $this->assertStringNotContainsString($invoice->number, $contentEmpty);
    }

    public function test_cannot_access_or_edit_invoice_belonging_to_another_clinic_tenant(): void
    {
        $clinicA = $this->createClinic();
        $clinicB = $this->createClinic();

        $user = $this->createUser();
        $this->grantPermissions($user, [
            'invoices.view',
            'invoices.update',
        ]);

        $invoiceB = $this->createInvoice();
        $invoiceB->forceDelete();

        $this->actingAs($user)
            ->get(
                $this->url($clinicA, "/admin/invoices/{$invoiceB->getRouteKey()}/edit"),
                $this->host($clinicA)
            )
            ->assertNotFound();
    }

    private function grantPermissions(User $user, array|string $permissions): void
    {
        $user->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function host(Clinic $clinic): array
    {
        return [
            'HTTP_HOST' => "{$clinic->slug}.clinika.test",
        ];
    }

    private function url(Clinic $clinic, string $path): string
    {
        return "http://{$clinic->slug}.clinika.test{$path}";
    }

    private function invoiceStatusUrl(Clinic $clinic, Invoice $invoice): string
    {
        return $this->url(
            $clinic,
            "/admin/invoices/{$invoice->getRouteKey()}/change-status"
        );
    }

    private function createClinic(): Clinic
    {
        return Clinic::on('central')->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Clinic',
            'slug' => 'test-'.Str::lower(Str::random(8)),
            'email' => 'test@example.com',
            'phone' => '3331234567',
            'address' => 'Via Roma 1',
            'city' => 'Roma',
            'province' => 'RM',
            'zip_code' => '00100',
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
            'active' => true,
        ]);
    }

    private function createUser(): User
    {
        $user = User::factory()->make();

        $user->setConnection('tenant');
        $user->email = 'user.'.Str::lower(Str::random(10)).'@example.com';
        $user->save();

        return $user;
    }

    private function createNationality(): Nationality
    {
        $nationality = new Nationality;

        $nationality->setConnection('tenant');
        $nationality->name = 'Italiana';
        $nationality->state = 'Italia';
        $nationality->save();

        return $nationality;
    }

    private function createPatient(): Patient
    {
        $patient = new Patient;

        $patient->setConnection('tenant');
        $patient->name = 'Mario';
        $patient->surname = 'Rossi';
        $patient->email = 'mario.'.Str::lower(Str::random(10)).'@example.com';
        $patient->personal_code = Str::upper(Str::random(16));
        $patient->birthday = '1990-01-01';
        $patient->birth_city = 'Roma';
        $patient->city = 'Roma';
        $patient->address = 'Via Roma 1';
        $patient->zip_code = '00100';
        $patient->phone = '333'.random_int(1000000, 9999999);
        $patient->genre = 'M';

        $patient->save();

        return $patient;
    }

    private function createService(): Service
    {
        $service = new Service;

        $service->setConnection('tenant');
        $service->name = 'Visita cardiologica '.Str::random(6);
        $service->default_price = 100;
        $service->save();

        return $service;
    }

    private function createDoctor(): Doctor
    {
        $user = $this->createUser();
        $nationality = $this->createNationality();

        $doctor = new Doctor;

        $doctor->setConnection('tenant');
        $doctor->user_id = $user->id;
        $doctor->personal_code = Str::upper(Str::random(16));
        $doctor->birthday = '1980-01-01';
        $doctor->birth_city = 'Roma';
        $doctor->city = 'Roma';
        $doctor->address = 'Via Roma 1';
        $doctor->phone = '333'.random_int(1000000, 9999999);
        $doctor->genre = 'M';
        $doctor->nationality_id = $nationality->id;
        $doctor->cap = '00100';
        $doctor->vat = 'IT12345678901';
        $doctor->save();

        return $doctor;
    }

    private function createAppointment(): Appointment
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $service = $this->createService();

        $doctor->services()->attach($service->id, [
            'price' => 100,
            'duration_minutes' => 60,
            'compensation_type' => 'percentage',
            'compensation_value' => 70,
            'active' => true,
        ]);

        $appointment = new Appointment;

        $appointment->setConnection('tenant');
        $appointment->patient_id = $patient->id;
        $appointment->doctor_id = $doctor->id;
        $appointment->service_id = $service->id;
        $appointment->start_time = now()->addDay();
        $appointment->end_time = now()->addDay()->addHour();
        $appointment->status = 'scheduled';

        $appointment->save();

        return $appointment;
    }

    private function createInvoice(): Invoice
    {
        $appointment = $this->createAppointment();

        $year = now()->year;

        $progressive = (int) Invoice::on('tenant')
            ->where('year', $year)
            ->max('progressive_number') + 1;

        $invoice = new Invoice;

        $invoice->setConnection('tenant');
        $invoice->uuid = (string) Str::uuid();
        $invoice->number = $progressive.'/'.$year;
        $invoice->year = $year;
        $invoice->progressive_number = $progressive;
        $invoice->appointment_id = $appointment->id;
        $invoice->doctor_id = $appointment->doctor_id;
        $invoice->patient_id = $appointment->patient_id;
        $invoice->user_id = $appointment->doctor->user_id;
        $invoice->date = now()->toDateString();
        $invoice->full_name = 'Mario Rossi';
        $invoice->vat_number = 'RSSMRA90A01H501Z';
        $invoice->address = 'Via Roma 1';
        $invoice->city = 'Roma';
        $invoice->zip_code = '00100';
        $invoice->description = 'Visita cardiologica';
        $invoice->subtotal = 100;
        $invoice->vat_amount = 0;
        $invoice->stamp_duty = 0;
        $invoice->discount_amount = 0;
        $invoice->total = 100;
        $invoice->amount = 100;
        $invoice->status = 'draft';

        $invoice->save();

        $invoice->invoiceItems()->create([
            'service_id' => $appointment->service_id,
            'description' => 'Visita cardiologica',
            'quantity' => 1,
            'unit_price' => 100,
            'vat_percentage' => 0,
            'total' => 100,
        ]);

        return $invoice;
    }

    private function validInvoiceData(Appointment $appointment): array
    {
        return [
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'date' => now()->toDateString(),
            'full_name' => 'Mario Rossi',
            'vat_number' => 'RSSMRA90A01H501Z',
            'address' => 'Via Roma 1',
            'city' => 'Roma',
            'zip_code' => '00100',
            'description' => 'Visita cardiologica',
            'subtotal' => 100,
            'vat_amount' => 0,
            'stamp_duty' => 0,
            'discount_amount' => 0,
            'total' => 100,
            'amount' => 100,
            'items' => [
                [
                    'service_id' => $appointment->service_id,
                    'description' => 'Visita cardiologica',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'vat_percentage' => 0,
                    'total' => 100,
                ],
            ],
        ];
    }
}
