<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AppointmentControllerTest extends TestCase
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

        foreach ([
            'appointment.view',
            'appointment.create',
            'appointment.update',
            'appointment.delete',
            'appointment.change-status',
        ] as $permission) {
            Permission::on('tenant')->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    private function createClinic(): Clinic
    {
        return Clinic::on('central')->create([
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
    }

    private function createUser(): User
    {
        $user = User::factory()->make();

        $user->setConnection('tenant');
        $user->save();

        return $user;
    }

    private function createPatient(): Patient
    {
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
        ]);
    }

    private function createAppointment(): Appointment
    {
        $patient = $this->createPatient();

        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => AppointmentStatus::Scheduled,
            'notes' => 'Visita di controllo',
        ]);

        $appointment->setConnection('tenant');
        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        return $appointment;
    }

    public function test_user_without_view_permission_cannot_access_appointments(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/appointments")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_appointments(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('appointment.view');

        DB::connection('tenant')->table('appointments')->delete();
        DB::connection('tenant')->table('patients')->delete();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/appointments")
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_create_appointment(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/appointments", [])
            ->assertForbidden();
    }

    public function test_user_without_update_permission_cannot_update_appointment(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $appointment = $this->createAppointment();

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/appointments/{$appointment->id}", [])
            ->assertForbidden();
    }

    public function test_user_without_delete_permission_cannot_delete_appointment(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $appointment = $this->createAppointment();

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/appointments/{$appointment->id}")
            ->assertForbidden();
    }

    public function test_user_without_change_status_permission_cannot_change_appointment_status(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $appointment = $this->createAppointment();

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/appointments/{$appointment->id}/status", [
                'status' => AppointmentStatus::Completed->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => AppointmentStatus::Scheduled->value,
        ], 'tenant');
    }

    public function test_user_with_change_status_permission_can_change_appointment_status(): void
    {
        $clinic = $this->createClinic();

        $user = $this->createUser();
        $user->givePermissionTo('appointment.change-status');

        $appointment = $this->createAppointment();

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/appointments/{$appointment->id}/status", [
                'status' => AppointmentStatus::Completed->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => AppointmentStatus::Completed->value,
        ], 'tenant');
    }
}
