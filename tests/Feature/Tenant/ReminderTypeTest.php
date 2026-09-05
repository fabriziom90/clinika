<?php

namespace Tests\Feature\Tenant;

use App\Models\Patient;
use App\Models\ReminderType;
use App\Models\ReminderTypePreference;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReminderTypeTest extends TestCase
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

    public function test_reminder_type_is_created_in_tenant_database(): void
    {
        $reminderType = $this->createReminderType();

        $this->assertSame('tenant', $reminderType->getConnectionName());

        $this->assertDatabaseHas('reminder_types', [
            'id' => $reminderType->id,
            'name' => 'Scadenza',
            'code' => 'test_reminder',
            'subject' => 'Promemoria appuntamento',
            'message' => 'Questo è un promemoria di test.',
            'sent_before_value' => 1,
            'sent_before_unit' => 'days',
            'active' => 1,
        ], 'tenant');
    }

    public function test_reminder_type_has_preferences(): void
    {
        $patient = $this->createPatient();
        $reminderType = $this->createReminderType();

        $preference = ReminderTypePreference::create([
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
        ]);

        $reminderType->load('preferences');

        $this->assertCount(1, $reminderType->preferences);
        $this->assertInstanceOf(ReminderTypePreference::class, $reminderType->preferences->first());
        $this->assertSame($preference->id, $reminderType->preferences->first()->id);
        $this->assertSame('tenant', $reminderType->preferences->first()->getConnectionName());
    }

    public function test_reminder_type_has_patients_through_preferences(): void
    {
        $patient = $this->createPatient();
        $reminderType = $this->createReminderType();

        ReminderTypePreference::create([
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
        ]);

        $reminderType->load('patients');

        $this->assertCount(1, $reminderType->patients);
        $this->assertInstanceOf(Patient::class, $reminderType->patients->first());
        $this->assertSame($patient->id, $reminderType->patients->first()->id);
        $this->assertSame('tenant', $reminderType->patients->first()->getConnectionName());
    }

    public function test_preference_belongs_to_reminder_type(): void
    {
        $patient = $this->createPatient();
        $reminderType = $this->createReminderType();

        $preference = ReminderTypePreference::create([
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
        ]);

        $preference->load('reminderType');

        $this->assertInstanceOf(ReminderType::class, $preference->reminderType);
        $this->assertSame($reminderType->id, $preference->reminderType->id);
        $this->assertSame('tenant', $preference->reminderType->getConnectionName());
    }

    public function test_preference_belongs_to_patient(): void
    {
        $patient = $this->createPatient();
        $reminderType = $this->createReminderType();

        $preference = ReminderTypePreference::create([
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
        ]);

        $preference->load('patient');

        $this->assertInstanceOf(Patient::class, $preference->patient);
        $this->assertSame($patient->id, $preference->patient->id);
        $this->assertSame('tenant', $preference->patient->getConnectionName());
    }

    public function test_reminder_type_can_have_multiple_patients(): void
    {
        $patient1 = $this->createPatient();

        $patient2 = Patient::create([
            'name' => 'Luigi',
            'surname' => 'Verdi',
            'personal_code' => 'VRDLGU81B02H501X',
            'birthday' => '1981-02-02',
            'birth_city' => 'Milano',
            'city' => 'Milano',
            'address' => 'Via Milano 2',
            'phone' => '3337654321',
            'email' => 'luigi.verdi@example.com',
            'genre' => 'M',
            'zip_code' => '20100',
        ]);

        $reminderType = $this->createReminderType();

        ReminderTypePreference::create([
            'patient_id' => $patient1->id,
            'reminder_type_id' => $reminderType->id,
        ]);

        ReminderTypePreference::create([
            'patient_id' => $patient2->id,
            'reminder_type_id' => $reminderType->id,
        ]);

        $reminderType->load('patients');

        $this->assertCount(2, $reminderType->patients);
        $this->assertTrue($reminderType->patients->contains('id', $patient1->id));
        $this->assertTrue($reminderType->patients->contains('id', $patient2->id));
    }
}
