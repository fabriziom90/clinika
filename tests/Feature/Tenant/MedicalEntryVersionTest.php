<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalEntry;
use App\Models\MedicalEntryVersion;
use App\Models\Patient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MedicalEntryVersionTest extends TestCase
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
            'personal_code' => 'RSSMRA80A01H501X',
            'vat' => '12345678901',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 2',
            'phone' => '3331234568',
            'genre' => 'M',
        ]);
    }

    private function createAppointment(Patient $patient): Appointment
    {
        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => 'scheduled',
            'notes' => 'Visita di controllo',
        ]);

        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        return $appointment;
    }

    private function createMedicalEntry(): MedicalEntry
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();
        $appointment = $this->createAppointment($patient);

        return MedicalEntry::create([
            'medical_record_id' => $patient->medicalRecord->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
        ]);
    }

    private function createVersion(MedicalEntry $medicalEntry, int $version = 1, array $attributes = []): MedicalEntryVersion
    {
        return MedicalEntryVersion::create(array_merge([
            'medical_entry_id' => $medicalEntry->id,
            'version' => $version,
            'type' => 'visit',
            'title' => 'Visita medica',
            'content' => 'Contenuto della visita',
            'uuid' => fake()->uuid(),
        ], $attributes));
    }

    public function test_version_is_created_in_tenant_database(): void
    {
        $medicalEntry = $this->createMedicalEntry();

        $version = $this->createVersion($medicalEntry);

        $this->assertSame('tenant', $version->getConnectionName());

        $this->assertDatabaseHas('medical_entry_versions', [
            'id' => $version->id,
            'medical_entry_id' => $medicalEntry->id,
            'version' => 1,
            'type' => 'visit',
        ], 'tenant');
    }

    public function test_version_belongs_to_medical_entry(): void
    {
        $medicalEntry = $this->createMedicalEntry();

        $version = $this->createVersion($medicalEntry);

        $this->assertInstanceOf(MedicalEntry::class, $version->medicalEntry);
        $this->assertSame($medicalEntry->id, $version->medicalEntry->id);
        $this->assertSame('tenant', $version->medicalEntry->getConnectionName());
    }

    public function test_content_and_title_are_encrypted_at_rest(): void
    {
        $medicalEntry = $this->createMedicalEntry();

        $version = $this->createVersion($medicalEntry, attributes: [
            'title' => 'Titolo riservato',
            'content' => 'Contenuto medico riservato',
        ]);

        $raw = DB::connection('tenant')
            ->table('medical_entry_versions')
            ->where('id', $version->id)
            ->first();

        $this->assertNotSame('Titolo riservato', $raw->title);
        $this->assertNotSame('Contenuto medico riservato', $raw->content);

        $version->refresh();

        $this->assertSame('Titolo riservato', $version->title);
        $this->assertSame('Contenuto medico riservato', $version->content);
    }

    public function test_route_key_name_is_uuid(): void
    {
        $version = new MedicalEntryVersion;

        $this->assertSame('uuid', $version->getRouteKeyName());
    }

    public function test_medical_entry_versions_are_ordered_by_version_descending(): void
    {
        $medicalEntry = $this->createMedicalEntry();

        $this->createVersion($medicalEntry, 1);
        $version3 = $this->createVersion($medicalEntry, 3);
        $version2 = $this->createVersion($medicalEntry, 2);

        $versions = $medicalEntry->versions()->get();

        $this->assertSame([
            3,
            2,
            1,
        ], $versions->pluck('version')->all());

        $this->assertSame($version3->id, $versions->first()->id);
    }

    public function test_latest_version_returns_the_latest_version(): void
    {
        $medicalEntry = $this->createMedicalEntry();

        $this->createVersion($medicalEntry, 1);
        $this->createVersion($medicalEntry, 2);
        $version3 = $this->createVersion($medicalEntry, 3);

        $latest = $medicalEntry->latestVersion;

        $this->assertInstanceOf(MedicalEntryVersion::class, $latest);
        $this->assertSame($version3->id, $latest->id);
    }

    public function test_latest_active_version_excludes_voided_versions(): void
    {
        $medicalEntry = $this->createMedicalEntry();

        $this->createVersion($medicalEntry, 1);
        $this->createVersion($medicalEntry, 2, [
            'voided_at' => now(),
            'is_voided' => true,
        ]);
        $activeVersion = $this->createVersion($medicalEntry, 3);

        $latestActive = $medicalEntry->latestActiveVersion;

        $this->assertInstanceOf(MedicalEntryVersion::class, $latestActive);
        $this->assertSame($activeVersion->id, $latestActive->id);
    }
}
