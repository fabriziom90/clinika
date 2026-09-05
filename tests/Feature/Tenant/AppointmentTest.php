<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppointmentTest extends TestCase
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

    private function createAppointment(Patient $patient, AppointmentStatus $status = AppointmentStatus::Scheduled): Appointment
    {
        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => $status,
            'notes' => 'Visita di controllo',
        ]);

        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        return $appointment;
    }

    public function test_appointment_is_created_in_tenant_database(): void
    {
        $patient = $this->createPatient();

        $appointment = $this->createAppointment($patient);

        $this->assertSame('tenant', $appointment->getConnectionName());

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_id' => $patient->id,
            'status' => 'scheduled',
            'duration_minutes' => 30,
        ], 'tenant');
    }

    public function test_appointment_status_is_cast_to_enum(): void
    {
        $patient = $this->createPatient();

        $appointment = $this->createAppointment($patient, AppointmentStatus::Completed);

        $this->assertInstanceOf(AppointmentStatus::class, $appointment->status);
        $this->assertSame(AppointmentStatus::Completed, $appointment->status);
    }

    public function test_appointment_status_label_is_generated_correctly(): void
    {
        $patient = $this->createPatient();

        $appointment = $this->createAppointment($patient, AppointmentStatus::Completed);

        $this->assertSame('Completato', $appointment->status_label);
        $this->assertArrayHasKey('status_label', $appointment->toArray());
    }

    public function test_appointment_belongs_to_patient(): void
    {
        $patient = $this->createPatient();

        $appointment = $this->createAppointment($patient);

        $this->assertInstanceOf(Patient::class, $appointment->patient);
        $this->assertSame($patient->id, $appointment->patient->id);
        $this->assertSame('tenant', $appointment->patient->getConnectionName());
    }
}
