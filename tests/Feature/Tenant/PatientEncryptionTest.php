<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class PatientEncryptionTest extends TenantTestCase
{
    public function test_patient_sensitive_data_is_encrypted_at_rest(): void
    {
        $clinic = Clinic::factory()->create([
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
        ]);

        $this->createTenantDatabase($clinic);

        $patient = Patient::factory()->create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'personal_code' => 'RSSMRA80A01H501Z',
            'birth_city' => 'Roma',
            'city' => 'Milano',
            'address' => 'Via Roma 10',
            'phone' => '3331234567',
            'email' => 'mario.rossi@example.com',
            'zip_code' => '20100',
            'genre' => 'M',
        ]);

        $rawPatient = DB::connection('tenant')
            ->table('patients')
            ->where('id', $patient->id)
            ->first();

        $this->assertNotNull($rawPatient);

        $this->assertNotSame('Mario', $rawPatient->name);
        $this->assertNotSame('Rossi', $rawPatient->surname);
        $this->assertNotSame('RSSMRA80A01H501Z', $rawPatient->personal_code);
        $this->assertNotSame('Roma', $rawPatient->birth_city);
        $this->assertNotSame('Milano', $rawPatient->city);
        $this->assertNotSame('Via Roma 10', $rawPatient->address);
        $this->assertNotSame('3331234567', $rawPatient->phone);
        $this->assertNotSame('mario.rossi@example.com', $rawPatient->email);
        $this->assertNotSame('20100', $rawPatient->zip_code);

    }

    public function test_patient_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $clinic = Clinic::factory()->create([
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
        ]);

        $this->createTenantDatabase($clinic);

        $patient = Patient::factory()->create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'personal_code' => 'RSSMRA80A01H501Z',
            'birth_city' => 'Roma',
            'city' => 'Milano',
            'address' => 'Via Roma 10',
            'phone' => '3331234567',
            'email' => 'mario.rossi@example.com',
            'zip_code' => '20100',
            'genre' => 'M',
        ]);

        $reloadedPatient = Patient::findOrFail($patient->id);

        $this->assertSame('Mario', $reloadedPatient->name);
        $this->assertSame('Rossi', $reloadedPatient->surname);
        $this->assertSame('RSSMRA80A01H501Z', $reloadedPatient->personal_code);
        $this->assertSame('Roma', $reloadedPatient->birth_city);
        $this->assertSame('Milano', $reloadedPatient->city);
        $this->assertSame('Via Roma 10', $reloadedPatient->address);
        $this->assertSame('3331234567', $reloadedPatient->phone);
        $this->assertSame('mario.rossi@example.com', $reloadedPatient->email);
        $this->assertSame('20100', $reloadedPatient->zip_code);

    }
}
