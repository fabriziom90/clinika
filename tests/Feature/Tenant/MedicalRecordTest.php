<?php

namespace Tests\Feature\Tenant;

use App\Models\Doctor;
use App\Models\MedicalEntry;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MedicalRecordTest extends TestCase
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

    public function test_medical_record_is_created_in_tenant_database(): void
    {
        $patient = $this->createPatient();

        $medicalRecord = $patient->medicalRecord;

        $this->assertNotNull($medicalRecord);
        $this->assertSame('tenant', $medicalRecord->getConnectionName());

        $this->assertDatabaseHas('medical_records', [
            'id' => $medicalRecord->id,
            'patient_id' => $patient->id,
        ], 'tenant');
    }

    public function test_medical_record_belongs_to_patient(): void
    {
        $patient = $this->createPatient();

        $medicalRecord = $patient->medicalRecord;
        $medicalRecord->load('patient');

        $this->assertNotNull($medicalRecord->patient);
        $this->assertInstanceOf(Patient::class, $medicalRecord->patient);
        $this->assertSame($patient->id, $medicalRecord->patient->id);
    }

    public function test_patient_has_medical_record(): void
    {
        $patient = $this->createPatient();

        $this->assertNotNull($patient->medicalRecord);
        $this->assertInstanceOf(MedicalRecord::class, $patient->medicalRecord);
        $this->assertSame($patient->id, $patient->medicalRecord->patient_id);
    }

    public function test_medical_record_has_medical_entries(): void
    {
        $patient = $this->createPatient();

        $medicalRecord = $patient->medicalRecord;
        $doctor = Doctor::factory()->create();

        $medicalEntry = MedicalEntry::create([
            'medical_record_id' => $medicalRecord->id,
            'appointment_id' => null,
            'doctor_id' => $doctor->id,
        ]);

        $medicalRecord->load('medicalEntries');

        $this->assertTrue($medicalRecord->medicalEntries->contains($medicalEntry));
        $this->assertSame($medicalRecord->id, $medicalEntry->medical_record_id);
    }
}
