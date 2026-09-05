<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalEntry;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MedicalEntryTest extends TestCase
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
            'personal_code' => 'RSSMRA75A01H501X',
            'vat' => '12345678901',
            'birthday' => '1975-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Medica 1',
            'phone' => '3339876543',
            'genre' => 'M',
        ]);
    }

    private function createAppointment(Patient $patient, Doctor $doctor): Appointment
    {
        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => AppointmentStatus::Scheduled,
            'notes' => 'Visita di controllo',
        ]);

        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        return $appointment;
    }

    private function createMedicalRecord(Patient $patient): MedicalRecord
    {
        return MedicalRecord::where('patient_id', $patient->id)->firstOrFail();
    }

    private function createMedicalEntry(Patient $patient, Doctor $doctor, Appointment $appointment): MedicalEntry
    {
        $medicalRecord = $this->createMedicalRecord($patient);

        return MedicalEntry::create([
            'medical_record_id' => $medicalRecord->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'cancelled_by' => null,
            'cancelled_at' => null,
        ]);
    }

    public function test_medical_entry_is_created_in_tenant_database(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient, $doctor);

        $medicalEntry = $this->createMedicalEntry($patient, $doctor, $appointment);

        $this->assertSame('tenant', $medicalEntry->getConnectionName());

        $this->assertDatabaseHas('medical_entries', [
            'id' => $medicalEntry->id,
            'medical_record_id' => $medicalEntry->medical_record_id,
            'appointment_id' => $medicalEntry->appointment_id,
            'doctor_id' => $medicalEntry->doctor_id,
        ], 'tenant');
    }

    public function test_medical_entry_belongs_to_medical_record(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient, $doctor);

        $medicalEntry = $this->createMedicalEntry($patient, $doctor, $appointment);

        $this->assertInstanceOf(MedicalRecord::class, $medicalEntry->medicalRecord);
        $this->assertSame($medicalEntry->medical_record_id, $medicalEntry->medicalRecord->id);
        $this->assertSame('tenant', $medicalEntry->medicalRecord->getConnectionName());
    }

    public function test_medical_entry_belongs_to_appointment(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient, $doctor);

        $medicalEntry = $this->createMedicalEntry($patient, $doctor, $appointment);

        $this->assertInstanceOf(Appointment::class, $medicalEntry->appointment);
        $this->assertSame($appointment->id, $medicalEntry->appointment->id);
        $this->assertSame('tenant', $medicalEntry->appointment->getConnectionName());
    }

    public function test_appointment_has_medical_entry(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient, $doctor);

        $medicalEntry = $this->createMedicalEntry($patient, $doctor, $appointment);

        $appointment->load('medicalEntry');

        $this->assertInstanceOf(MedicalEntry::class, $appointment->medicalEntry);
        $this->assertSame($medicalEntry->id, $appointment->medicalEntry->id);
        $this->assertSame('tenant', $appointment->medicalEntry->getConnectionName());
    }

    public function test_medical_entry_versions_are_empty_when_no_versions_exist(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient, $doctor);

        $medicalEntry = $this->createMedicalEntry($patient, $doctor, $appointment);

        $versions = $medicalEntry->versions;

        $this->assertCount(0, $versions);
    }

    public function test_latest_helpers_return_empty_values_without_latest_version(): void
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient, $doctor);

        $medicalEntry = $this->createMedicalEntry($patient, $doctor, $appointment);

        $this->assertEmpty($medicalEntry->latestAttachments());
        $this->assertEmpty($medicalEntry->latestPrescriptions());
        $this->assertNull($medicalEntry->latestVitalParameters());
    }
}
