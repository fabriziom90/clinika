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
use App\Services\InvoicePdfService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoicePdfServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'tenant');

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

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function test_generate_creates_invoice_pdf_in_expected_path(): void
    {
        Storage::fake('local');

        $clinic = $this->createClinic();
        $invoice = $this->createInvoice();

        app()->instance('currentClinic', $clinic);

        $path = app(InvoicePdfService::class)->generate($invoice);

        $expectedPath = 'invoices/'.$clinic->uuid.'/'.$invoice->year.'/'.$invoice->uuid.'.pdf';

        $this->assertSame($expectedPath, $path);

        $this->assertTrue(Storage::disk('local')->exists($expectedPath));

        $pdf = Storage::disk('local')->get($expectedPath);

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_get_pdf_path_generates_and_saves_pdf_path_when_pdf_does_not_exist(): void
    {
        Storage::fake('local');

        $clinic = $this->createClinic();
        $invoice = $this->createInvoice();

        app()->instance('currentClinic', $clinic);

        $path = app(InvoicePdfService::class)->getPdfPath($invoice);

        $invoice->refresh();

        $expectedPath = 'invoices/'.$clinic->uuid.'/'.$invoice->year.'/'.$invoice->uuid.'.pdf';

        $this->assertSame($expectedPath, $path);
        $this->assertSame($expectedPath, $invoice->pdf_path);

        $this->assertTrue(Storage::disk('local')->exists($expectedPath));
    }

    public function test_get_pdf_path_returns_existing_pdf_without_regenerating_it(): void
    {
        Storage::fake('local');

        $clinic = $this->createClinic();
        $invoice = $this->createInvoice();

        app()->instance('currentClinic', $clinic);

        $path = 'invoices/'.$clinic->uuid.'/'.$invoice->year.'/'.$invoice->uuid.'.pdf';

        $existingPdf = '%PDF-existing-test-content';

        Storage::disk('local')->put($path, $existingPdf);

        $invoice->update([
            'pdf_path' => $path,
        ]);

        $result = app(InvoicePdfService::class)->getPdfPath($invoice);

        $this->assertSame($path, $result);

        $this->assertTrue(Storage::disk('local')->exists($path));
        $this->assertSame(
            $existingPdf,
            Storage::disk('local')->get($path)
        );
    }

    public function test_generated_pdf_contains_invoice_data(): void
    {
        Storage::fake('local');

        $clinic = $this->createClinic();
        $invoice = $this->createInvoice();

        app()->instance('currentClinic', $clinic);

        $path = app(InvoicePdfService::class)->generate($invoice);

        $pdf = Storage::disk('local')->get($path);

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertNotEmpty($pdf);
    }

    private function createClinic(): Clinic
    {
        return Clinic::on('central')->create([
            'uuid' => Str::uuid(),
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

    private function createNationality(): Nationality
    {
        $nationality = new Nationality;

        $nationality->setConnection('tenant');
        $nationality->name = 'Italiana';
        $nationality->state = 'Italia';
        $nationality->save();

        return $nationality;
    }

    private function createUser(): User
    {
        $user = User::factory()->make();

        $user->setConnection('tenant');
        $user->email = 'user.'.Str::lower(Str::random(10)).'@example.com';
        $user->save();

        return $user;
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
        $invoice->uuid = Str::uuid();
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
}
