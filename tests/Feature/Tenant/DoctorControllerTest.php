<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Nationality;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DoctorControllerTest extends TestCase
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

        Role::on('tenant')->firstOrCreate([
            'name' => 'doctor',
            'guard_name' => 'web',
        ]);

        foreach ([
            'doctor.view',
            'doctor.create',
            'doctor.update',
            'doctor.delete',
        ] as $permission) {
            Permission::on('tenant')->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
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

    private function createSpecialty(): Specialty
    {
        return Specialty::on('tenant')->forceCreate([
            'name' => 'Cardiologia',
        ]);
    }

    private function createService(): Service
    {
        return Service::on('tenant')->forceCreate([
            'name' => 'Visita cardiologica',
            'default_price' => 100,
        ]);
    }

    private function createDoctor(): Doctor
    {
        $user = $this->createUser();

        $user->update([
            'name' => 'Mario',
            'surname' => 'Rossi',
        ]);

        $nationality = $this->createNationality();
        $specialty = $this->createSpecialty();
        $service = $this->createService();

        $doctor = Doctor::on('tenant')->create([
            'user_id' => $user->id,
            'personal_code' => 'RSSMRA80A01H501Z',
            'vat' => '12345678901',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'genre' => 'M',
            'cap' => '00100',
            'pec' => 'mario.rossi@pec.example.com',
            'specialty_id' => $specialty->id,
            'nationality_id' => $nationality->id,
        ]);

        $doctor->services()->attach($service->id, [
            'price' => 100,
            'duration_minutes' => 60,
            'active' => true,
        ]);

        return $doctor;
    }

    private function validDoctorData(?int $nationalityId = null, ?int $specialtyId = null, ?int $serviceId = null): array
    {
        $nationalityId ??= $this->createNationality()->id;
        $specialtyId ??= $this->createSpecialty()->id;
        $serviceId ??= $this->createService()->id;

        return [
            'name' => 'Luigi',
            'surname' => 'Bianchi',
            'personal_code' => 'BNCLGU80A01H501Z',
            'vat' => '98765432109',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Milano 10',
            'phone' => '3339876543',
            'email' => 'luigi.'.Str::lower(Str::random(8)).'@example.com',
            'genre' => 'M',
            'pec' => 'luigi.bianchi@pec.example.com',
            'specialty_id' => $specialtyId,
            'nationality_id' => $nationalityId,
            'zip_code' => '00100',
            'services' => [
                [
                    'service_id' => $serviceId,
                    'price' => 120,
                    'duration' => 45,
                    'active' => 1,
                ],
            ],
        ];
    }

    public function test_user_without_view_permission_cannot_access_doctors(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_doctors(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.view');

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors")
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_access_doctor_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors/create")
            ->assertForbidden();
    }

    public function test_user_with_create_permission_can_access_doctor_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.create');

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors/create")
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_create_doctor(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $data = $this->validDoctorData();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/doctors", $data)
            ->assertForbidden();

        $this->assertDatabaseMissing('doctors', [
            'personal_code' => 'BNCLGU80A01H501Z',
        ], 'tenant');
    }

    public function test_user_with_create_permission_can_create_doctor(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.create');

        $nationality = $this->createNationality();
        $specialty = $this->createSpecialty();
        $service = $this->createService();

        $data = $this->validDoctorData(
            $nationality->id,
            $specialty->id,
            $service->id
        );

        $response = $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/doctors", $data);

        $response
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        /*
         * personal_code, vat, email, name e surname sono campi cifrati.
         *
         * Non possiamo quindi cercare il record direttamente con:
         *
         * where('personal_code', $data['personal_code'])
         *
         * perché nel database il valore è memorizzato cifrato.
         *
         * Recuperiamo invece l'ultimo Doctor creato e verifichiamo
         * i valori attraverso Eloquent, che applica automaticamente
         * la decifratura.
         */
        $doctor = Doctor::on('tenant')
            ->latest('id')
            ->first();

        $this->assertNotNull($doctor);

        $doctor->load('user');

        $this->assertNotNull($doctor->user);

        $this->assertSame('BNCLGU80A01H501Z', $doctor->personal_code);
        $this->assertSame('98765432109', $doctor->vat);

        $this->assertSame('Luigi', $doctor->user->name);
        $this->assertSame('Bianchi', $doctor->user->surname);
        $this->assertSame($data['email'], $doctor->user->email);

        $this->assertDatabaseHas('doctor_service', [
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'price' => 120,
            'duration_minutes' => 45,
            'active' => 1,
        ], 'tenant');

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $doctor->user_id,
        ], 'tenant');

        Mail::assertSent(\App\Mail\PersonSetPasswordMail::class);
    }

    public function test_user_without_view_permission_cannot_view_doctor(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $doctor = $this->createDoctor();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_view_doctor(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.view');

        $doctor = $this->createDoctor();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}")
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_access_doctor_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $doctor = $this->createDoctor();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}/edit")
            ->assertForbidden();
    }

    public function test_user_with_update_permission_can_access_doctor_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.update');

        $doctor = $this->createDoctor();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}/edit")
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_update_doctor(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $doctor = $this->createDoctor();

        $nationality = $this->createNationality();
        $specialty = $this->createSpecialty();
        $service = $this->createService();

        $data = $this->validDoctorData(
            $nationality->id,
            $specialty->id,
            $service->id
        );

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}", $data)
            ->assertForbidden();

        $doctor->refresh();

        $this->assertSame('RSSMRA80A01H501Z', $doctor->personal_code);
        $this->assertSame('Mario', $doctor->user->name);
        $this->assertSame('Rossi', $doctor->user->surname);
    }

    public function test_user_with_update_permission_can_update_doctor(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.update');

        $doctor = $this->createDoctor();

        $nationality = $this->createNationality();
        $specialty = $this->createSpecialty();
        $service = $this->createService();

        $data = $this->validDoctorData(
            $nationality->id,
            $specialty->id,
            $service->id
        );

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}", $data)
            ->assertRedirect();

        $doctor->refresh();
        $doctor->load('user');

        $this->assertSame('BNCLGU80A01H501Z', $doctor->personal_code);
        $this->assertSame('Luigi', $doctor->user->name);
        $this->assertSame('Bianchi', $doctor->user->surname);
        $this->assertSame($data['email'], $doctor->user->email);

        $this->assertDatabaseHas('doctor_service', [
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'price' => 120,
            'duration_minutes' => 45,
            'active' => 1,
        ], 'tenant');
    }

    public function test_user_without_delete_permission_cannot_delete_doctor(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $doctor = $this->createDoctor();

        $userId = $doctor->user_id;

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
        ], 'tenant');

        $this->assertDatabaseHas('users', [
            'id' => $userId,
        ], 'tenant');
    }

    public function test_user_with_delete_permission_can_delete_doctor(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.delete');

        $doctor = $this->createDoctor();

        $doctorId = $doctor->id;
        $userId = $doctor->user_id;

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctorId}")
            ->assertRedirect();

        $this->assertDatabaseMissing('doctors', [
            'id' => $doctorId,
        ], 'tenant');

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ], 'tenant');
    }

    public function test_user_without_update_permission_cannot_send_doctor_reset_email(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUser();
        $doctor = $this->createDoctor();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}/send-reset-email")
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_user_with_update_permission_can_send_doctor_reset_email(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('doctor.update');

        $doctor = $this->createDoctor();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/doctors/{$doctor->id}/send-reset-email")
            ->assertRedirect();

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $doctor->user_id,
        ], 'tenant');

        Mail::assertSent(\App\Mail\PersonSetPasswordMail::class);
    }
}
