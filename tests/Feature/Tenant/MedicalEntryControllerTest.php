<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\MedicalEntry;
use App\Models\MedicalRecord;
use App\Models\Nationality;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MedicalEntryControllerTest extends TestCase
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

        $this->cleanMedicalEntries();

        foreach ([
            'medical-entry.view',
            'medical-entry.create',
            'medical-entry.update',
            'medical-entry.delete',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    public function test_user_without_doctor_cannot_create_medical_entry(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();

        Doctor::on('tenant')
            ->where('user_id', $user->id)
            ->delete();

        $user->unsetRelation('doctor');

        $this->assertFalse($user->doctor()->exists());

        $this->assertDatabaseCount('medical_entries', 0, 'tenant');
    }

    public function test_doctor_can_create_medical_entry(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('medical-entry.create');

        $this->createDoctor($user);
        $user = $user->fresh(['doctor']);
        $doctor = $user->doctor;

        $patient = $this->createPatient();
        $medicalRecord = $this->getMedicalRecord($patient);
        $appointment = $this->createAppointment($patient, $doctor);

        $data = $this->validStoreData($medicalRecord, $appointment, $patient);

        $response = $this->actingAs($user, 'web')
            ->post("http://{$clinic->slug}.clinika.test/admin/medical-entries", $data);

        $response->assertRedirect();

        $entry = MedicalEntry::on('tenant')
            ->where('appointment_id', $appointment->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($entry);
        $this->assertSame($medicalRecord->id, $entry->medical_record_id);
        $this->assertSame($appointment->id, $entry->appointment_id);
        $this->assertSame($doctor->id, $entry->doctor_id);

        $version = $entry->latestVersion;

        $this->assertNotNull($version);
        $this->assertSame(1, $version->version);
        $this->assertSame('visit', $version->type);
        $this->assertSame('Visita iniziale', $version->title);
        $this->assertSame('Contenuto della visita', $version->content);
    }

    public function test_doctor_who_does_not_own_medical_entry_cannot_update_it(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('medical-entry.update');

        $this->createDoctor($user);
        $user = $user->fresh(['doctor']);
        $doctor = $user->doctor;

        $otherDoctorUser = $this->createUser();
        $this->createDoctor($otherDoctorUser);
        $otherDoctorUser = $otherDoctorUser->fresh(['doctor']);
        $otherDoctor = $otherDoctorUser->doctor;

        $patient = $this->createPatient();
        $medicalRecord = $this->getMedicalRecord($patient);
        $appointment = $this->createAppointment($patient, $otherDoctor);
        $entry = $this->createMedicalEntry($medicalRecord, $appointment, $otherDoctor);

        $versionsBefore = $entry->versions()->count();

        $data = $this->validUpdateData($patient, $medicalRecord, $appointment);
        $data['title'] = 'Tentativo modifica';

        $this->actingAs($user, 'web')
            ->put("http://{$clinic->slug}.clinika.test/admin/medical-entries/{$entry->id}", $data)
            ->assertForbidden();

        $entry->refresh();

        $this->assertSame($versionsBefore, $entry->versions()->count());
    }

    public function test_doctor_who_owns_medical_entry_can_create_new_version(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('medical-entry.update');

        $this->createDoctor($user);
        $user = $user->fresh(['doctor']);
        $doctor = $user->doctor;

        $patient = $this->createPatient();
        $medicalRecord = $this->getMedicalRecord($patient);
        $appointment = $this->createAppointment($patient, $doctor);
        $entry = $this->createMedicalEntry($medicalRecord, $appointment, $doctor);

        $versionsBefore = $entry->versions()->count();

        $data = $this->validUpdateData($patient, $medicalRecord, $appointment);
        $data['title'] = 'Visita modificata';
        $data['content'] = 'Contenuto modificato';

        $response = $this->actingAs($user, 'web')
            ->put("http://{$clinic->slug}.clinika.test/admin/medical-entries/{$entry->id}", $data);

        $response->assertRedirect();

        $entry->refresh();

        $this->assertSame($versionsBefore + 1, $entry->versions()->count());
        $this->assertSame(2, $entry->latestVersion->version);
        $this->assertSame('Visita modificata', $entry->latestVersion->title);
        $this->assertSame('Contenuto modificato', $entry->latestVersion->content);
    }

    public function test_doctor_who_owns_medical_entry_can_void_latest_version(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('medical-entry.update');

        $this->createDoctor($user);
        $user = $user->fresh(['doctor']);
        $doctor = $user->doctor;

        $patient = $this->createPatient();
        $medicalRecord = $this->getMedicalRecord($patient);
        $appointment = $this->createAppointment($patient, $doctor);
        $entry = $this->createMedicalEntry($medicalRecord, $appointment, $doctor);

        $data = $this->validUpdateData($patient, $medicalRecord, $appointment);
        $data['is_voided'] = true;
        $data['void_reason'] = 'Errore nella compilazione';

        $response = $this->actingAs($user, 'web')
            ->put("http://{$clinic->slug}.clinika.test/admin/medical-entries/{$entry->id}", $data);

        $response->assertRedirect();

        $entry->refresh();

        $this->assertTrue((bool) $entry->latestVersion->is_voided);
        $this->assertNotNull($entry->latestVersion->voided_at);
        $this->assertSame($user->id, $entry->latestVersion->voided_by);
        $this->assertSame('Errore nella compilazione', $entry->latestVersion->void_reason);
    }

    public function test_store_validation_rejects_invalid_medical_entry_data(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('medical-entry.create');

        $doctor = $this->createDoctor($user);

        $patient = $this->createPatient();
        $medicalRecord = $this->getMedicalRecord($patient);
        $appointment = $this->createAppointment($patient, $doctor);

        $data = [
            'patient_id' => $patient->id,
            'medical_record_id' => $medicalRecord->id,
            'appointment_id' => $appointment->id,
            'type' => 'invalid-type',
            'title' => '',
            'content' => '',
        ];

        $this->withoutExceptionHandling();

        $this->expectException(ValidationException::class);

        $this->actingAs($user->fresh(['doctor']), 'web')
            ->post("http://{$clinic->slug}.clinika.test/admin/medical-entries", $data);
    }

    public function test_update_validation_rejects_invalid_medical_entry_data(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('medical-entry.update');

        $this->createDoctor($user);
        $user = $user->fresh(['doctor']);
        $doctor = $user->doctor;

        $patient = $this->createPatient();
        $medicalRecord = $this->getMedicalRecord($patient);
        $appointment = $this->createAppointment($patient, $doctor);
        $entry = $this->createMedicalEntry($medicalRecord, $appointment, $doctor);

        $data = [
            'patient_id' => $patient->id,
            'medical_record_id' => $medicalRecord->id,
            'appointment_id' => $appointment->id,
            'type' => 'invalid-type',
            'title' => '',
            'content' => '',
        ];

        $this->withoutExceptionHandling();

        $this->expectException(ValidationException::class);

        $this->actingAs($user, 'web')
            ->put("http://{$clinic->slug}.clinika.test/admin/medical-entries/{$entry->id}", $data);
    }

    private function cleanMedicalEntries(): void
    {
        $connection = DB::connection('tenant');

        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        if (Schema::connection('tenant')->hasTable('medical_entry_versions')) {
            $connection->table('medical_entry_versions')->truncate();
        }

        if (Schema::connection('tenant')->hasTable('medical_entries')) {
            $connection->table('medical_entries')->truncate();
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createClinic(): Clinic
    {
        $clinic = Clinic::on('central')->create([
            'uuid' => Str::uuid(),
            'name' => 'Test Clinic',
            'slug' => 'test-'.Str::lower(Str::random(8)),
            'email' => 'test-'.Str::lower(Str::random(8)).'@example.com',
            'phone' => '3331234567',
            'address' => 'Via Roma 1',
            'city' => 'Roma',
            'province' => 'RM',
            'zip_code' => '00100',
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
            'active' => true,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        return $clinic;
    }

    private function createUser(): User
    {
        $user = User::factory()->make();
        $user->setConnection('tenant');
        $user->save();

        return $user;
    }

    private function createNationality(): Nationality
    {
        $nationality = new Nationality;
        $nationality->setConnection('tenant');
        $nationality->name = 'Italiana '.Str::lower(Str::random(6));
        $nationality->state = 'Italia';
        $nationality->save();

        return $nationality;
    }

    private function createDoctor(User $user): Doctor
    {
        $nationality = $this->createNationality();

        return Doctor::on('tenant')->create([
            'user_id' => $user->id,
            'personal_code' => Str::upper(Str::random(16)),
            'vat' => (string) random_int(10000000000, 99999999999),
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '333'.random_int(1000000, 9999999),
            'genre' => 'M',
            'cap' => '00100',
            'pec' => Str::lower(Str::random(8)).'@pec.example.com',
            'nationality_id' => $nationality->id,
        ]);
    }

    private function createPatient(): Patient
    {
        $patient = new Patient;
        $patient->setConnection('tenant');
        $patient->name = 'Mario';
        $patient->surname = 'Rossi';
        $patient->email = 'patient-'.Str::lower(Str::random(10)).'@example.com';
        $patient->personal_code = Str::upper(Str::random(16));
        $patient->birthday = '1990-01-01';
        $patient->birth_city = 'Roma';
        $patient->city = 'Roma';
        $patient->address = 'Via Roma 1';
        $patient->zip_code = '00100';
        $patient->phone = '333'.random_int(1000000, 9999999);
        $patient->genre = 'M';
        $patient->save();

        return $patient;
    }

    private function getMedicalRecord(Patient $patient): MedicalRecord
    {
        return MedicalRecord::on('tenant')
            ->where('patient_id', $patient->id)
            ->latest('id')
            ->firstOrFail();
    }

    private function createAppointment(Patient $patient, Doctor $doctor): Appointment
    {
        $appointment = new Appointment;
        $appointment->setConnection('tenant');
        $appointment->patient_id = $patient->id;
        $appointment->doctor_id = $doctor->id;
        $appointment->start_time = now()->addHour();
        $appointment->end_time = now()->addHours(2);
        $appointment->save();

        return $appointment;
    }

    private function createMedicalEntry(MedicalRecord $medicalRecord, Appointment $appointment, Doctor $doctor): MedicalEntry
    {
        $entry = new MedicalEntry;
        $entry->setConnection('tenant');
        $entry->medical_record_id = $medicalRecord->id;
        $entry->appointment_id = $appointment->id;
        $entry->doctor_id = $doctor->id;
        $entry->cancelled_by = null;
        $entry->cancelled_at = null;
        $entry->save();

        $entry->versions()->create([
            'uuid' => Str::uuid(),
            'version' => 1,
            'type' => 'visit',
            'title' => 'Visita iniziale',
            'content' => 'Contenuto della visita',
        ]);

        return $entry->fresh(['latestVersion']);
    }

    private function validStoreData(MedicalRecord $medicalRecord, Appointment $appointment, Patient $patient): array
    {
        return [
            'patient_id' => $patient->id,
            'medical_record_id' => $medicalRecord->id,
            'appointment_id' => $appointment->id,
            'type' => 'visit',
            'title' => 'Visita iniziale',
            'content' => 'Contenuto della visita',
        ];
    }

    private function validUpdateData(Patient $patient, MedicalRecord $medicalRecord, Appointment $appointment): array
    {
        return [
            'patient_id' => $patient->id,
            'medical_record_id' => $medicalRecord->id,
            'appointment_id' => $appointment->id,
            'type' => 'visit',
            'title' => 'Visita aggiornata',
            'content' => 'Contenuto aggiornato',
        ];
    }
}
