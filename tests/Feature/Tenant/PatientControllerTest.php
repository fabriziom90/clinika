<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\Nationality;
use App\Models\Patient;
use App\Models\ReminderType;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PatientControllerTest extends TestCase
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

        $this->cleanTenantDatabase();

        Permission::on('tenant')->firstOrCreate([
            'name' => 'patient.view',
            'guard_name' => 'web',
        ]);

        Permission::on('tenant')->firstOrCreate([
            'name' => 'patient.create',
            'guard_name' => 'web',
        ]);

        Permission::on('tenant')->firstOrCreate([
            'name' => 'patient.update',
            'guard_name' => 'web',
        ]);

        Permission::on('tenant')->firstOrCreate([
            'name' => 'patient.delete',
            'guard_name' => 'web',
        ]);
    }

    private function cleanTenantDatabase(): void
    {
        $connection = DB::connection('tenant');

        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'audits',
            'appointment_reminders',
            'appointments',
            'medical_entries',
            'medical_records',
            'patient_consents',
            'patient_health_histories',
            'reminder_type_preferences',
            'patients',
            'nationalities',
            'reminder_types',
            'users',
            'permissions',
            'roles',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
        ];

        foreach ($tables as $table) {
            if ($connection->getSchemaBuilder()->hasTable($table)) {
                $connection->table($table)->truncate();
            }
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createClinic(): Clinic
    {
        $clinic = Clinic::on('central')->create([
            'uuid' => Str::uuid(),
            'name' => 'Test Clinic',
            'slug' => 'test-'.Str::lower(Str::random(8)),
            'email' => 'test@example.com',
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
        $nationality->name = 'Italiana';
        $nationality->state = 'Italia';
        $nationality->save();

        return $nationality;
    }

    private function createReminderType(): ReminderType
    {
        return ReminderType::on('tenant')->create([
            'name' => 'SMS',
            'code' => 'test_sms_'.Str::lower(Str::random(8)),
            'subject' => 'Promemoria appuntamento',
            'message' => 'Promemoria di test',
            'sent_before_value' => 1,
            'sent_before_unit' => 'days',
            'active' => true,
        ]);
    }

    private function createPatient(): Patient
    {
        $nationality = $this->createNationality();

        return Patient::on('tenant')->create([
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
            'nationality_id' => $nationality->id,
        ]);
    }

    private function validPatientData(?int $nationalityId = null): array
    {
        if ($nationalityId === null) {
            $nationalityId = $this->createNationality()->id;
        }

        return [
            'name' => 'Luigi',
            'surname' => 'Bianchi',
            'personal_code' => 'BNCLGU80A01H501Z',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Milano 10',
            'phone' => '3339876543',
            'email' => 'luigi.bianchi@example.com',
            'genre' => 'M',
            'zip_code' => '00100',
            'nationality_id' => $nationalityId,
        ];
    }

    public function test_user_without_view_permission_cannot_access_patients(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_patients(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients")
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_access_patient_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients/create")
            ->assertForbidden();
    }

    public function test_user_with_create_permission_can_access_patient_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.create');

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients/create")
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_create_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $nationality = $this->createNationality();

        $this->actingAs($user)
            ->post(
                "http://{$clinic->slug}.clinika.test/admin/patients",
                $this->validPatientData($nationality->id)
            )
            ->assertForbidden();

        $this->assertDatabaseMissing('patients', [
            'personal_code' => 'BNCLGU80A01H501Z',
        ], 'tenant');
    }

    public function test_user_with_create_permission_can_create_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.create');

        $nationality = $this->createNationality();

        $this->actingAs($user)
            ->post(
                "http://{$clinic->slug}.clinika.test/admin/patients",
                $this->validPatientData($nationality->id)
            )
            ->assertRedirect();

        $patient = Patient::on('tenant')
            ->where('nationality_id', $nationality->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($patient);
        $this->assertSame('BNCLGU80A01H501Z', $patient->personal_code);
        $this->assertSame('Luigi', $patient->name);
        $this->assertSame('Bianchi', $patient->surname);
    }

    public function test_user_without_view_permission_cannot_view_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $patient = $this->createPatient();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_view_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        $patient = $this->createPatient();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}")
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_access_patient_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $patient = $this->createPatient();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}/edit")
            ->assertForbidden();
    }

    public function test_user_with_update_permission_can_access_patient_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.update');

        $patient = $this->createPatient();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}/edit")
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_update_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $patient = $this->createPatient();
        $nationality = $this->createNationality();

        $data = $this->validPatientData($nationality->id);

        $this->actingAs($user)
            ->put(
                "http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}",
                $data
            )
            ->assertForbidden();

        $patient->refresh();

        $this->assertSame('Mario', $patient->name);
        $this->assertSame('Rossi', $patient->surname);
    }

    public function test_user_with_update_permission_can_update_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.update');

        $patient = $this->createPatient();
        $nationality = $this->createNationality();

        $data = $this->validPatientData($nationality->id);

        $this->actingAs($user)
            ->put(
                "http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}",
                $data
            )
            ->assertRedirect();

        $patient->refresh();

        $this->assertSame('Luigi', $patient->name);
        $this->assertSame('Bianchi', $patient->surname);
        $this->assertSame('luigi.bianchi@example.com', $patient->email);
    }

    public function test_user_without_delete_permission_cannot_delete_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $patient = $this->createPatient();

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
        ], 'tenant');
    }

    public function test_user_with_delete_permission_can_delete_patient(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.delete');

        $patient = $this->createPatient();

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/patients/{$patient->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('patients', [
            'id' => $patient->id,
        ], 'tenant');
    }

    public function test_user_without_view_permission_cannot_search_patients(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->createPatient();

        $this->actingAs($user)
            ->getJson("http://{$clinic->slug}.clinika.test/admin/patients/search?search=Mario")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_search_patients_by_name(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        $patient = $this->createPatient();

        $this->actingAs($user)
            ->getJson("http://{$clinic->slug}.clinika.test/admin/patients/search?search=Mario")
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $patient->id,
                'name' => 'Mario',
                'surname' => 'Rossi',
                'personal_code' => 'RSSMRA80A01H501Z',
            ]);
    }

    public function test_user_with_view_permission_can_search_patients_by_surname(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        $patient = $this->createPatient();

        $this->actingAs($user)
            ->getJson("http://{$clinic->slug}.clinika.test/admin/patients/search?search=Rossi")
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $patient->id,
                'name' => 'Mario',
                'surname' => 'Rossi',
            ]);
    }

    public function test_user_with_view_permission_can_search_patients_by_personal_code(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        $patient = $this->createPatient();

        $this->actingAs($user)
            ->getJson(
                "http://{$clinic->slug}.clinika.test/admin/patients/search?search=RSSMRA80A01H501Z"
            )
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $patient->id,
                'personal_code' => 'RSSMRA80A01H501Z',
            ]);
    }

    public function test_patient_search_with_empty_search_returns_empty_array(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        $this->createPatient();

        $this->actingAs($user)
            ->getJson("http://{$clinic->slug}.clinika.test/admin/patients/search?search=")
            ->assertSuccessful()
            ->assertExactJson([]);
    }

    public function test_patient_search_is_case_insensitive(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        $patient = $this->createPatient();

        $this->actingAs($user)
            ->getJson("http://{$clinic->slug}.clinika.test/admin/patients/search?search=MARio")
            ->assertSuccessful()
            ->assertJsonFragment([
                'id' => $patient->id,
                'name' => 'Mario',
                'surname' => 'Rossi',
            ]);
    }

    public function test_patient_search_returns_maximum_ten_results(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('patient.view');

        for ($i = 0; $i < 12; $i++) {
            $nationality = $this->createNationality();

            Patient::on('tenant')->create([
                'name' => 'Mario',
                'surname' => 'Rossi'.$i,
                'personal_code' => 'RSSMRA80A'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'H501Z',
                'birthday' => '1980-01-01',
                'birth_city' => 'Roma',
                'city' => 'Roma',
                'address' => 'Via Roma 1',
                'phone' => '3331234567',
                'email' => 'mario.rossi'.$i.'@example.com',
                'genre' => 'M',
                'zip_code' => '00100',
                'nationality_id' => $nationality->id,
            ]);
        }

        $response = $this->actingAs($user)
            ->getJson("http://{$clinic->slug}.clinika.test/admin/patients/search?search=Mario")
            ->assertSuccessful();

        $this->assertCount(10, $response->json());
    }
}
