<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NurseTest extends TestCase
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

    private function createNurse(): Nurse
    {
        return Nurse::factory()->create();
    }

    public function test_nurse_is_created_in_tenant_database(): void
    {
        $nurse = $this->createNurse();

        $this->assertSame('tenant', $nurse->getConnectionName());

        $this->assertDatabaseHas('nurses', [
            'id' => $nurse->id,
        ], 'tenant');
    }

    public function test_nurse_sensitive_data_is_encrypted_at_rest(): void
    {
        $nurse = Nurse::factory()->create([
            'personal_code' => 'RSSMRA80A01H501Z',
            'vat' => '12345678901',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Milano',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'pec' => 'infermiere@pec.it',
            'cap' => '00100',
            'genre' => 'M',
        ]);

        $raw = DB::connection('tenant')
            ->table('nurses')
            ->where('id', $nurse->id)
            ->first();

        $this->assertNotNull($raw);

        $this->assertNotSame('RSSMRA80A01H501Z', $raw->personal_code);
        $this->assertNotSame('12345678901', $raw->vat);
        $this->assertNotSame('1980-01-01', $raw->birthday);
        $this->assertNotSame('Roma', $raw->birth_city);
        $this->assertNotSame('Milano', $raw->city);
        $this->assertNotSame('Via Roma 1', $raw->address);
        $this->assertNotSame('3331234567', $raw->phone);
        $this->assertNotSame('infermiere@pec.it', $raw->pec);
        $this->assertNotSame('00100', $raw->cap);
        $this->assertNotSame('M', $raw->genre);
    }

    public function test_nurse_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $nurse = Nurse::factory()->create([
            'personal_code' => 'RSSMRA80A01H501Z',
            'vat' => '12345678901',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Milano',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'pec' => 'infermiere@pec.it',
            'cap' => '00100',
            'genre' => 'M',
        ]);

        $nurse->refresh();

        $this->assertSame('RSSMRA80A01H501Z', $nurse->personal_code);
        $this->assertSame('12345678901', $nurse->vat);
        $this->assertSame('1980-01-01', $nurse->birthday);
        $this->assertSame('Roma', $nurse->birth_city);
        $this->assertSame('Milano', $nurse->city);
        $this->assertSame('Via Roma 1', $nurse->address);
        $this->assertSame('3331234567', $nurse->phone);
        $this->assertSame('infermiere@pec.it', $nurse->pec);
        $this->assertSame('00100', $nurse->cap);
        $this->assertSame('M', $nurse->genre);
    }

    public function test_nurse_belongs_to_user(): void
    {
        $nurse = $this->createNurse();

        $this->assertNotNull($nurse->user);
        $this->assertInstanceOf(User::class, $nurse->user);
        $this->assertSame($nurse->user_id, $nurse->user->id);
    }

    public function test_nurse_user_uses_tenant_connection(): void
    {
        $nurse = $this->createNurse();

        $this->assertSame('tenant', $nurse->getConnectionName());
        $this->assertSame('tenant', $nurse->user->getConnectionName());
    }

    public function test_nurse_has_appointments(): void
    {
        $nurse = $this->createNurse();

        $appointment = Appointment::factory()->create([
            'nurse_id' => $nurse->id,
        ]);

        $nurse->load('appointments');

        $this->assertTrue($nurse->appointments->contains($appointment));
        $this->assertSame($nurse->id, $appointment->nurse_id);
    }

    public function test_nurse_belongs_to_nationality(): void
    {
        $nurse = $this->createNurse();

        $nationalityId = DB::connection('tenant')
            ->table('nationalities')
            ->insertGetId([
                'name' => 'Italiana',
                'state' => 'Italia',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $nurse->update([
            'nationality_id' => $nationalityId,
        ]);

        $nurse->load('nationality');

        $this->assertNotNull($nurse->nationality);
        $this->assertSame($nationalityId, $nurse->nationality->id);
        $this->assertSame('Italiana', $nurse->nationality->name);
    }
}
