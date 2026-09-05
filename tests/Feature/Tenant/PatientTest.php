<?php

namespace Tests\Feature\Tenant;

use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientTest extends TestCase
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

    public function test_patient_is_created_in_tenant_database(): void
    {
        $patient = $this->createPatient();

        $this->assertSame('tenant', $patient->getConnectionName());

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
        ], 'tenant');
    }

    public function test_patient_sensitive_data_is_encrypted_at_rest(): void
    {
        $patient = $this->createPatient();

        $raw = DB::connection('tenant')
            ->table('patients')
            ->where('id', $patient->id)
            ->first();

        $this->assertNotSame('Mario', $raw->name);
        $this->assertNotSame('Rossi', $raw->surname);
        $this->assertNotSame('RSSMRA80A01H501Z', $raw->personal_code);
        $this->assertNotSame('1980-01-01', $raw->birthday);
        $this->assertNotSame('Roma', $raw->birth_city);
        $this->assertNotSame('Roma', $raw->city);
        $this->assertNotSame('Via Roma 1', $raw->address);
        $this->assertNotSame('3331234567', $raw->phone);
        $this->assertNotSame('mario.rossi@example.com', $raw->email);
        $this->assertNotSame('M', $raw->genre);
        $this->assertNotSame('00100', $raw->zip_code);
    }

    public function test_patient_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $patient = $this->createPatient();

        $patient->refresh();

        $this->assertSame('Mario', $patient->name);
        $this->assertSame('Rossi', $patient->surname);
        $this->assertSame('RSSMRA80A01H501Z', $patient->personal_code);
        $this->assertSame('1980-01-01', $patient->birthday);
        $this->assertSame('Roma', $patient->birth_city);
        $this->assertSame('Roma', $patient->city);
        $this->assertSame('Via Roma 1', $patient->address);
        $this->assertSame('3331234567', $patient->phone);
        $this->assertSame('mario.rossi@example.com', $patient->email);
        $this->assertSame('M', $patient->genre);
        $this->assertSame('00100', $patient->zip_code);
    }

    public function test_patient_automatically_creates_a_medical_record(): void
    {
        $patient = $this->createPatient();

        $this->assertNotNull($patient->medicalRecord);
        $this->assertInstanceOf(MedicalRecord::class, $patient->medicalRecord);
        $this->assertSame('tenant', $patient->medicalRecord->getConnectionName());
        $this->assertSame($patient->id, $patient->medicalRecord->patient_id);
    }

    public function test_patient_and_medical_record_use_tenant_connection(): void
    {
        $patient = $this->createPatient();

        $this->assertSame('tenant', $patient->getConnectionName());
        $this->assertNotNull($patient->medicalRecord);
        $this->assertSame('tenant', $patient->medicalRecord->getConnectionName());
    }
}
