<?php

namespace Tests\Feature\Tenant;

use App\Models\Patient;
use App\Models\ReminderType;
use App\Models\ReminderTypePreference;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReminderTypePreferenceTest extends TestCase
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

    private function createReminderType(): ReminderType
    {
        return ReminderType::create([
            'name' => 'Scadenza',
            'code' => 'test_reminder',
            'subject' => 'Promemoria appuntamento',
            'message' => 'Questo è un promemoria di test.',
            'sent_before_value' => 1,
            'sent_before_unit' => 'days',
            'active' => true,
        ]);
    }

    private function createPreference(Patient $patient, ReminderType $reminderType): ReminderTypePreference
    {
        return ReminderTypePreference::create([
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
        ]);
    }

    public function test_preference_is_created_in_tenant_database(): void
    {
        $patient = $this->createPatient();
        $reminderType = $this->createReminderType();

        $preference = $this->createPreference($patient, $reminderType);

        $this->assertSame('tenant', $preference->getConnectionName());

        $this->assertDatabaseHas('reminder_type_preferences', [
            'id' => $preference->id,
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
        ], 'tenant');
    }

    public function test_preference_belongs_to_patient(): void
    {
        $patient = $this->createPatient();
        $reminderType = $this->createReminderType();

        $preference = $this->createPreference($patient, $reminderType);

        $preference->load('patient');

        $this->assertInstanceOf(Patient::class, $preference->patient);
        $this->assertSame($patient->id, $preference->patient->id);
        $this->assertSame('tenant', $preference->patient->getConnectionName());
    }

    public function test_preference_belongs_to_reminder_type(): void
    {
        $patient = $this->createPatient();
        $reminderType = $this->createReminderType();

        $preference = $this->createPreference($patient, $reminderType);

        $preference->load('reminderType');

        $this->assertInstanceOf(ReminderType::class, $preference->reminderType);
        $this->assertSame($reminderType->id, $preference->reminderType->id);
        $this->assertSame('tenant', $preference->reminderType->getConnectionName());
    }
}
