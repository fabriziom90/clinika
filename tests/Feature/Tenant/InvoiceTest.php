<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceTest extends TestCase
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

    private function createPatient(): Patient
    {
        return Patient::create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'personal_code' => 'RSSMRA80A01H501Z',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'email' => 'mario.rossi@example.com',
            'genre' => 'M',
            'zip_code' => '00100',
        ]);
    }

    private function createDoctor(): Doctor
    {
        return Doctor::create([
            'personal_code' => 'RSSMRA75A01H501Z',
            'vat' => '12345678901',
            'birthday' => '1975-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Medica 1',
            'phone' => '3337654321',
            'genre' => 'M',
        ]);
    }

    private function createAppointment(Patient $patient): Appointment
    {
        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => \App\Enums\AppointmentStatus::Scheduled,
            'notes' => 'Visita di controllo',
        ]);

        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        return $appointment;
    }

    private function createInvoice(Patient $patient, Doctor $doctor, Appointment $appointment): Invoice
    {
        return Invoice::create([
            'uuid' => fake()->uuid(),
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'number' => 'FT-001/2026',
            'progressive_number' => 1,
            'year' => 2026,
            'date' => '2026-08-27',
            'subtotal' => 100.00,
            'vat_amount' => 22.00,
            'stamp_duty' => 2.00,
            'discount_amount' => 0.00,
            'total' => 124.00,
            'amount' => 124.00,
            'status' => 'draft',
            'payment_method' => 'card',
            'full_name' => 'Mario Rossi',
            'vat_number' => 'RSSMRA80A01H501Z',
            'address' => 'Via Roma 1',
            'city' => 'Roma',
            'zip_code' => '00100',
            'description' => 'Visita di controllo',
        ]);
    }

    public function test_invoice_is_created_in_tenant_database(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $this->assertSame('tenant', $invoice->getConnectionName());

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'uuid' => $invoice->uuid,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
        ], 'tenant');
    }

    public function test_invoice_route_key_name_is_uuid(): void
    {
        $invoice = new Invoice;

        $this->assertSame('uuid', $invoice->getRouteKeyName());
    }

    public function test_invoice_date_is_cast_to_carbon(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $this->assertInstanceOf(Carbon::class, $invoice->date);
    }

    public function test_invoice_sensitive_data_is_encrypted_at_rest(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $raw = DB::connection('tenant')
            ->table('invoices')
            ->where('id', $invoice->id)
            ->first();

        $this->assertNotSame('Mario Rossi', $raw->full_name);
        $this->assertNotSame('RSSMRA80A01H501Z', $raw->vat_number);
        $this->assertNotSame('Via Roma 1', $raw->address);
        $this->assertNotSame('Roma', $raw->city);
        $this->assertNotSame('00100', $raw->zip_code);
        $this->assertNotSame('Visita di controllo', $raw->description);
    }

    public function test_invoice_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);
        $invoice->refresh();

        $this->assertSame('Mario Rossi', $invoice->full_name);
        $this->assertSame('RSSMRA80A01H501Z', $invoice->vat_number);
        $this->assertSame('Via Roma 1', $invoice->address);
        $this->assertSame('Roma', $invoice->city);
        $this->assertSame('00100', $invoice->zip_code);
        $this->assertSame('Visita di controllo', $invoice->description);
    }

    public function test_invoice_belongs_to_patient(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $this->assertInstanceOf(Patient::class, $invoice->patient);
        $this->assertSame($patient->id, $invoice->patient->id);
        $this->assertSame('tenant', $invoice->patient->getConnectionName());
    }

    public function test_invoice_belongs_to_doctor(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $this->assertInstanceOf(Doctor::class, $invoice->doctor);
        $this->assertSame($doctor->id, $invoice->doctor->id);
        $this->assertSame('tenant', $invoice->doctor->getConnectionName());
    }

    public function test_invoice_belongs_to_appointment(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $this->assertInstanceOf(Appointment::class, $invoice->appointment);
        $this->assertSame($appointment->id, $invoice->appointment->id);
        $this->assertSame('tenant', $invoice->appointment->getConnectionName());
    }

    public function test_invoice_has_invoice_items(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_id' => null,
            'description' => 'Visita di controllo',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_percentage' => 22.00,
            'total' => 122.00,
        ]);

        $invoice->load('invoiceItems');

        $this->assertCount(1, $invoice->invoiceItems);
        $this->assertInstanceOf(InvoiceItem::class, $invoice->invoiceItems->first());
        $this->assertSame($item->id, $invoice->invoiceItems->first()->id);
    }

    public function test_invoice_can_be_soft_deleted(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        $invoice = $this->createInvoice($patient, $doctor, $appointment);

        $invoice->delete();

        $this->assertSoftDeleted('invoices', [
            'id' => $invoice->id,
        ], 'tenant');

        $this->assertNull(Invoice::find($invoice->id));
        $this->assertNotNull(Invoice::withTrashed()->find($invoice->id));
    }
}
