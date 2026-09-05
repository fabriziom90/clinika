<?php

namespace Tests\Feature\Tenant;

use App\Models\Doctor;
use App\Models\MedicalEntry;
use App\Models\MedicalEntryVersion;
use App\Models\Patient;
use App\Models\VitalParameter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VitalParameterTest extends TestCase
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
            'personal_code' => 'RSSMRA70A01H501Z',
            'vat' => '12345678901',
            'birthday' => '1970-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Medica 1',
            'phone' => '3339876543',
            'genre' => 'M',
        ]);
    }

    private function createMedicalEntry(): MedicalEntry
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        return MedicalEntry::create([
            'medical_record_id' => $patient->medicalRecord->id,
            'doctor_id' => $doctor->id,
        ]);
    }

    private function createVersion(): MedicalEntryVersion
    {
        $entry = $this->createMedicalEntry();

        return MedicalEntryVersion::create([
            'medical_entry_id' => $entry->id,
            'version' => 1,
            'type' => 'visita',
            'title' => 'Prima visita',
            'content' => 'Contenuto della visita',
        ]);
    }

    private function createVitalParameter(): VitalParameter
    {
        $version = $this->createVersion();

        return VitalParameter::create([
            'medical_entry_version_id' => $version->id,
            'pressure' => '120/80',
            'heart_rate' => '70',
            'temperature' => '36.5',
            'weight' => '75',
            'height' => '180',
        ]);
    }

    public function test_vital_parameter_is_created_in_tenant_database(): void
    {
        $vitalParameter = $this->createVitalParameter();

        $this->assertSame('tenant', $vitalParameter->getConnectionName());

        $this->assertDatabaseHas('vital_parameters', [
            'id' => $vitalParameter->id,
            'medical_entry_version_id' => $vitalParameter->medical_entry_version_id,
        ], 'tenant');
    }

    public function test_vital_parameter_belongs_to_version(): void
    {
        $vitalParameter = $this->createVitalParameter();

        $this->assertInstanceOf(MedicalEntryVersion::class, $vitalParameter->version);
        $this->assertSame(
            $vitalParameter->medical_entry_version_id,
            $vitalParameter->version->id
        );
        $this->assertSame('tenant', $vitalParameter->version->getConnectionName());
    }

    public function test_vital_parameter_sensitive_data_is_encrypted_at_rest(): void
    {
        $vitalParameter = $this->createVitalParameter();

        $raw = DB::connection('tenant')
            ->table('vital_parameters')
            ->where('id', $vitalParameter->id)
            ->first();

        $this->assertNotSame('120/80', $raw->pressure);
        $this->assertNotSame('70', $raw->heart_rate);
        $this->assertNotSame('36.5', $raw->temperature);
        $this->assertNotSame('75', $raw->weight);
        $this->assertNotSame('180', $raw->height);
    }

    public function test_vital_parameter_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $vitalParameter = $this->createVitalParameter();

        $vitalParameter->refresh();

        $this->assertSame('120/80', $vitalParameter->pressure);
        $this->assertSame('70', $vitalParameter->heart_rate);
        $this->assertSame('36.5', $vitalParameter->temperature);
        $this->assertSame('75', $vitalParameter->weight);
        $this->assertSame('180', $vitalParameter->height);
    }
}
