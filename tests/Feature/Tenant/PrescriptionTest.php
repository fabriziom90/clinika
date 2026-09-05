<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\MedicalEntry;
use App\Models\MedicalEntryVersion;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrescriptionTest extends TestCase
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

    private function createVersion(): MedicalEntryVersion
    {
        $patient = $this->createPatient();

        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => AppointmentStatus::Scheduled,
            'notes' => 'Visita di controllo',
        ]);

        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        $medicalEntry = MedicalEntry::create([
            'medical_record_id' => $patient->medicalRecord->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => 1,
        ]);

        return MedicalEntryVersion::create([
            'medical_entry_id' => $medicalEntry->id,
            'version' => 1,
            'type' => 'prescription',
            'title' => 'Prescrizione',
            'content' => 'Prescrizione medica',
        ]);
    }

    private function createPrescription(MedicalEntryVersion $version): Prescription
    {
        return Prescription::create([
            'medical_entry_version_id' => $version->id,
            'drug_name' => 'Paracetamolo',
            'dosage' => '1000 mg',
            'frequency' => '2 volte al giorno',
            'duration' => '5 giorni',
            'notes' => 'Assumere dopo i pasti',
        ]);
    }

    public function test_prescription_is_created_in_tenant_database(): void
    {
        $version = $this->createVersion();

        $prescription = $this->createPrescription($version);

        $this->assertSame('tenant', $prescription->getConnectionName());

        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'medical_entry_version_id' => $version->id,
        ], 'tenant');
    }

    public function test_prescription_belongs_to_version(): void
    {
        $version = $this->createVersion();

        $prescription = $this->createPrescription($version);

        $this->assertInstanceOf(MedicalEntryVersion::class, $prescription->version);
        $this->assertSame($version->id, $prescription->version->id);
        $this->assertSame('tenant', $prescription->version->getConnectionName());
    }

    public function test_prescription_sensitive_data_is_encrypted_at_rest(): void
    {
        $version = $this->createVersion();

        $prescription = $this->createPrescription($version);

        $row = DB::connection('tenant')
            ->table('prescriptions')
            ->where('id', $prescription->id)
            ->first();

        $this->assertNotSame('Paracetamolo', $row->drug_name);
        $this->assertNotSame('1000 mg', $row->dosage);
        $this->assertNotSame('2 volte al giorno', $row->frequency);
        $this->assertNotSame('5 giorni', $row->duration);
        $this->assertNotSame('Assumere dopo i pasti', $row->notes);
    }

    public function test_prescription_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $version = $this->createVersion();

        $prescription = $this->createPrescription($version);

        $prescription->refresh();

        $this->assertSame('Paracetamolo', $prescription->drug_name);
        $this->assertSame('1000 mg', $prescription->dosage);
        $this->assertSame('2 volte al giorno', $prescription->frequency);
        $this->assertSame('5 giorni', $prescription->duration);
        $this->assertSame('Assumere dopo i pasti', $prescription->notes);
    }
}
